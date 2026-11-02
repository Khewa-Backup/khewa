<?php
/**
 * Action audit model.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class BestkitLog extends ObjectModel
{
    const REGISTRY_PREFIX = 'bestkitlog_';

    private static $registry = array();

    public static $CLASS_EXCEPTIONS = array(
        'PrestaShopLogger',
        'BestkitLog',
        'Db',
        'Condition',
        'BestkitLogVisit',
        'BestkitLogLoginAttempt',
        'Advice',
        'EmployeeSession',
    );

    public static $GLOBAL_FIELDS_EXCEPTIONS = array(
        'date_upd',
        'passwd',
        'password',
        'reset_password_token',
        'secure_key',
        'private_key',
        'secret',
    );

    protected $_log_details = [];

    public $id_employee;
    public $email;
    public $path;
    public $action_type;
    public $object;
    public $id_object;
    public $description;
    public $id_shop;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'bestkit_log',
        'primary' => 'id_bestkit_log',
        'multilang' => FALSE,
        'fields' => array(
            'id_employee' 		=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'email' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'path' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'action_type' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'object' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_object' 		=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'description' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_shop' 				=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function getLogExceptions()
    {
        return BestkitLog::$CLASS_EXCEPTIONS;
    }

    public static function getGlobalFieldsExceptions()
    {
        return BestkitLog::$GLOBAL_FIELDS_EXCEPTIONS;
    }

    private static function isSensitiveField($field)
    {
        $field = Tools::strtolower((string) $field);

        foreach (array('passwd', 'password', 'token', 'secret', 'private_key', 'secure_key') as $fragment) {
            if (strpos($field, $fragment) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function getRegistryName($object, $skip_id = false)
    {
        $name = BestkitLog::REGISTRY_PREFIX . get_class($object) . '_';
        if (!$skip_id) {
            $name .= (int) $object->id;
        }

        return $name;
    }

    public static function convertObjectToArray($object)
    {
        return get_object_vars($object);
    }

    private static function stringifyValue($value)
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encodedValue === false ? '[unserializable value]' : $encodedValue;
    }

    public static function redactSensitiveQueryValues($value)
    {
        return (string) preg_replace(
            '/([?&](?:_?token|passwd|password|secret|private_key|secure_key)=)[^&]*/i',
            '$1[redacted]',
            (string) $value
        );
    }

    public static function getArrayDiff($objNew, $objOld = null)
    {
        $diff = array();

        if (!($objNew instanceof ObjectModel)
            || !isset($objNew::$definition['fields'])
            || !is_array($objNew::$definition['fields'])
        ) {
            return $diff;
        }

        $newData = self::convertObjectToArray($objNew);
        $oldData = $objOld instanceof ObjectModel ? self::convertObjectToArray($objOld) : array();

        foreach (array_keys($objNew::$definition['fields']) as $field) {
            if (in_array(Tools::strtolower($field), self::getGlobalFieldsExceptions(), true)
                || self::isSensitiveField($field)
            ) {
                continue;
            }

            if (!array_key_exists($field, $newData)) {
                continue;
            }

            $newValue = $newData[$field];
            $oldValue = array_key_exists($field, $oldData) ? $oldData[$field] : null;

            if (is_array($newValue)) {
                foreach ($newValue as $languageId => $localizedValue) {
                    $oldLocalizedValue = is_array($oldValue) && array_key_exists($languageId, $oldValue)
                        ? $oldValue[$languageId]
                        : null;

                    if ($localizedValue != $oldLocalizedValue) {
                        $diff[$field . '[' . (int) $languageId . ']'] = array(
                            'old' => self::stringifyValue($oldLocalizedValue),
                            'new' => self::stringifyValue($localizedValue),
                        );
                    }
                }

                continue;
            }

            if ($newValue != $oldValue) {
                $diff[$field] = array(
                    'old' => self::stringifyValue($oldValue),
                    'new' => self::stringifyValue($newValue),
                );
            }
        }

        return $diff;
    }

    public function writeDiffLine($diffField, $diffValues)
    {
        return Db::getInstance()->insert('bestkit_log_detail', array(
            'id_bestkit_log' => (int) $this->id,
            'name' => pSQL($diffField),
            'old_value' => pSQL($diffValues['old'], true),
            'new_value' => pSQL($diffValues['new'], true),
            'class' => pSQL($this->object),
        ));
    }

    public static function saveLog($object, $dataBefore, $action, $context, $actionDescription = '')
    {
        if (!($object instanceof ObjectModel)
            || in_array(get_class($object), self::getLogExceptions(), true)
        ) {
            return;
        }

        if ($action !== 'add' && !($dataBefore instanceof ObjectModel)) {
            return;
        }

        if ($dataBefore instanceof ObjectModel && get_class($object) !== get_class($dataBefore)) {
            return;
        }

        $diff = self::getArrayDiff($object, $dataBefore);
        if (empty($diff) && $action !== 'delete') {
            return;
        }

        if (!Validate::isLoadedObject($context->employee)) {
            return;
        }

        $logObj = new BestkitLog();
        $logObj->id_employee = (int) $context->employee->id;
        $logObj->email = (string) $context->employee->email;
        $logObj->path = isset($_SERVER['REQUEST_URI'])
            ? self::redactSensitiveQueryValues($_SERVER['REQUEST_URI'])
            : '';
        $logObj->action_type = $action;
        $logObj->object = get_class($object);
        $logObj->id_object = (int) $object->id;
        $logObj->description = $actionDescription;
        $logObj->id_shop = Validate::isLoadedObject($context->shop) ? (int) $context->shop->id : 0;

        if ($logObj->save()) {
            foreach ($diff as $diffField => $diffValues) {
                $logObj->writeDiffLine($diffField, $diffValues);
            }
        }
    }

    public static function putToRegistry($name, $data)
    {
        self::$registry[$name] = clone $data;
    }

    public static function getFromRegistry($name)
    {
        return isset(self::$registry[$name]) ? self::$registry[$name] : null;
    }

    public function loadLogDetails()
    {
        if (empty($this->_log_details)) {
            $this->_log_details = Db::getInstance()->executeS('
                SELECT * FROM `'._DB_PREFIX_.'bestkit_log_detail`
                WHERE `id_bestkit_log` = '.(int)$this->id.'
            ');

            if (!is_array($this->_log_details)) {
                $this->_log_details = array();
            }

            foreach ($this->_log_details as &$log_detail) {
                $log_detail['opcodes'] = FineDiff::getDiffOpcodes($log_detail['old_value'], $log_detail['new_value']);
                $log_detail['diff_parts'] = self::getDiffParts(
                    $log_detail['old_value'],
                    $log_detail['opcodes']
                );
            }
            unset($log_detail);
        }
    }

    public function getLogDetails()
    {
        if (empty($this->_log_details)) {
            $this->loadLogDetails();
        }

        return $this->_log_details;
    }

    private static function getDiffParts($originalValue, $opcodes)
    {
        $parts = array();
        $originalOffset = 0;

        foreach (FineDiff::getDiffOpsFromOpcodes($opcodes) as $operation) {
            if ($operation instanceof FineDiffInsertOp) {
                $parts[] = array(
                    'type' => 'insert',
                    'value' => $operation->getText(),
                );

                continue;
            }

            $length = $operation->getFromLen();
            $parts[] = array(
                'type' => $operation instanceof FineDiffDeleteOp ? 'delete' : 'copy',
                'value' => substr($originalValue, $originalOffset, $length),
            );
            $originalOffset += $length;
        }

        return $parts;
    }
}
