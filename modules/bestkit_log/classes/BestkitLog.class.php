<?php

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class BestkitLog extends ObjectModel
{
    const REGISTRY_PREFIX = 'bestkitlog_';

    public static $CLASS_EXCEPTIONS = array(
        'PrestaShopLogger',
        'BestkitLog',
        'Db',
'Condition',
        'BestkitLogVisit',
        'BestkitLogLoginAttempt',
        'Advice',
    );

    public static $GLOBAL_FIELDS_EXCEPTIONS = array(
        'date_upd',
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
            'id_shop' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt'),
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

    public static function getRegistryName($object, $skip_id = false)
    {
        $name = BestkitLog::REGISTRY_PREFIX . get_class($object). '_';
        if (!$skip_id) //can be useful for cases like: add/delete
            $name .= $object->id;

        return $name;
    }

    public static function convertObjectToArray($object) {
        return get_object_vars($object);
    }

    public static function getArrayDiff($objNew, $objOld) {
        $diff = [];

        $new_data = self::convertObjectToArray($objNew);
        $old_data = self::convertObjectToArray($objOld);

        if (!is_array($new_data) ||
            !is_array($old_data) ||
            !($objNew instanceof ObjectModel) ||
            !($objOld instanceof ObjectModel) ||
            !isset($objNew::$definition['fields']) ||
            !is_array($objNew::$definition['fields']) ||
            !isset($objOld::$definition['fields']) ||
            !is_array($objOld::$definition['fields']) )
            return [];

        foreach (array_keys($objNew::$definition['fields']) as $_field) {
            if (in_array($_field, self::getGlobalFieldsExceptions()))
                continue;

            if (isset($new_data[$_field])) {
                if (is_array($new_data[$_field])) {
                    foreach (array_keys($new_data[$_field]) as $lang_id) {
                        if ($new_data[$_field][$lang_id] != $old_data[$_field][$lang_id]) {
                            $diff[$_field] = [
                                'old' => $old_data[$_field][$lang_id],
                                'new' => $new_data[$_field][$lang_id],
                            ];
                        }
                    }
                    unset($lang_id);
                } else {
                    if ($new_data[$_field] != $old_data[$_field]) {
                        $diff[$_field] = [
                            'old' => $old_data[$_field],
                            'new' => $new_data[$_field],
                        ];
                    }
                }
            }
        }
        unset($_field);

        return $diff;
    }

    public function writeDiffLine($diff_field, $diff_values) {
        Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'bestkit_log_detail`
            (
                `id_bestkit_log`,
                `name`,
                `old_value`,
                `new_value`
            )
            VALUES (
                '.(int)$this->id.',
                "'.pSQL($diff_field).'",
                "'.pSQL($diff_values['old']).'",
                "'.pSQL($diff_values['new']).'"
            )
        ');
    }
	
	public static function saveLog($object, $data_before, $action, Context $context = null, $action_desc = '') {
        if (in_array(get_class($object), self::getLogExceptions()))
            return;

        if (get_class($object) != get_class($data_before))
            return;

        $diff = self::getArrayDiff($object, $data_before);
        if (!count($diff) && $action != 'delete')
            return;

        if ($context == null)
            $context = Context::getContext();

        $logObj = new BestkitLog();
        $logObj->id_employee = $context->employee->id;
        $logObj->email = $context->employee->email;
        $logObj->path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $logObj->action_type = $action;
        $logObj->object = get_class($object);
        $logObj->id_object = $object->id;
        $logObj->description = $action_desc;
        $logObj->id_shop = $context->shop->id;
        if ($logObj->save()) {
            foreach ($diff as $diff_field => $diff_values) {
                $logObj->writeDiffLine($diff_field, $diff_values);
            }
            unset($diff_values);
            unset($diff_field);
        }
	}

    public static function putToRegistry($name, $data) {
	    Context::getContext()->$name = clone $data;
    }

    public static function getFromRegistry($name) {
        return Context::getContext()->$name;
    }

    public function loadLogDetails() {
        if (empty($this->_log_details)) {
            $this->_log_details = Db::getInstance()->executeS('
                SELECT * FROM `'._DB_PREFIX_.'bestkit_log_detail`
                WHERE `id_bestkit_log` = '.(int)$this->id.'
            ');

            foreach ($this->_log_details as &$log_detail) {
                $log_detail['opcodes'] = FineDiff::getDiffOpcodes($log_detail['old_value'], $log_detail['new_value']);
                $log_detail['diff'] = FineDiff::renderDiffToHTMLFromOpcodes($log_detail['old_value'], $log_detail['opcodes']);
            }
//print_r($log_detail); die;
            unset($log_detail);
        }
    }

    public function getLogDetails() {
        if (empty($this->_log_details)) {
            $this->loadLogDetails();
        }

        return $this->_log_details;
    }
}