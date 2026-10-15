<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class StockTakeLog extends ObjectModel
{
    /** @var int Log id */
    public $id_wkinventory_log;

    /** @var int Log severity */
    public $severity;

    /** @var int Error code */
    public $error_code;

    /** @var string Message */
    public $message;

    /** @var string Object type (eg. Order, Customer...) */
    public $object_type;

    /** @var int Object ID */
    public $object_id;

    /** @var int Object ID */
    public $id_employee;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'wkinventory_log',
        'primary' => 'id_wkinventory_log',
        'fields' => array(
            'severity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'error_code' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'message' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'object_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'object_type' => array('type' => self::TYPE_STRING, 'validate' => 'isName'),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    protected static $is_present = array();

    /**
     * add a log item to the database
     *
     * @param string $message the log message
     * @param int $severity
     * @param int $error_code
     * @param string $object_type
     * @param int $object_id
     * @param bool $allow_duplicate if set to true, can log several time the same information (not recommended)
     * @return bool true if succeed
     */
    public static function addLog(
        $message,
        $severity = 1,
        $error_code = null,
        $object_type = null,
        $object_id = null,
        $allow_duplicate = false,
        $id_employee = null
    ) {
        $log = new StockTakeLog();
        $log->severity = (int)$severity;
        $log->error_code = (int)$error_code;
        $log->message = pSQL($message);
        $log->date_add = date('Y-m-d H:i:s');
        $log->date_upd = date('Y-m-d H:i:s');

        if ($id_employee === null && isset(Context::getContext()->employee) &&
            Validate::isLoadedObject(Context::getContext()->employee)) {
            $id_employee = Context::getContext()->employee->id;
        }
        if ($id_employee !== null) {
            $log->id_employee = (int)$id_employee;
        }
        if (!empty($object_type)) {
            $log->object_type = pSQL($object_type);
        }
        if (!empty($object_id)) {
            $log->object_id = (int)$object_id;
        }
        if ($allow_duplicate || !$log->isPresent()) {
            $res = $log->add();
            if ($res) {
                self::$is_present[$log->getHash()] = isset(self::$is_present[$log->getHash()])?self::$is_present[$log->getHash()] + 1:1;
                return true;
            }
        }
        return false;
    }

    /**
     * this function md5($this->message.$this->severity.$this->error_code.$this->object_type.$this->object_id)
     *
     * @return string hash
     */
    public function getHash()
    {
        if (empty($this->hash)) {
            $this->hash = md5($this->message.$this->severity.$this->error_code.$this->object_type.$this->object_id);
        }

        return $this->hash;
    }

    public static function eraseAllLogs()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'wkinventory_log');
    }

    /**
     * check if this log message already exists in database.
     *
     * @return true if exists
     */
    protected function isPresent()
    {
        if (!isset(self::$is_present[md5($this->message)])) {
            self::$is_present[$this->getHash()] = Db::getInstance()->getValue(
                'SELECT COUNT(*)
                 FROM `'._DB_PREFIX_.'wkinventory_log`
                 WHERE
                    `message` = \''.$this->message.'\' AND 
                    `severity` = \''.$this->severity.'\' AND 
                    `error_code` = \''.$this->error_code.'\' AND 
                    `object_type` = \''.$this->object_type.'\' AND 
                    `object_id` = \''.$this->object_id.'\''
            );
        }
        return self::$is_present[$this->getHash()];
    }
}
