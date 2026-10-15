<?php

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class BestkitLogVisit extends ObjectModel
{
    public static $CLASS_EXCEPTIONS = array(
        'AdminBestkitLogVisitHistory',
        'AdminGamification',
    );

    public $id_employee;
    public $email;
    public $name;
    public $ip;
    public $session_start;
    public $session_end;

    public static $definition = array(
        'table' => 'bestkit_log_visit',
        'primary' => 'id_bestkit_log_visit',
        'multilang' => FALSE,
        'fields' => array(
            'id_employee' 		=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'email' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'name' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ip' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'session_start' 				=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'session_end' 				=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function registerNewVisit()
    {
        $context = Context::getContext();

        $logObj = new BestkitLogVisit();
        $logObj->id_employee = $context->employee->id;
        $logObj->email = $context->employee->email;
        $logObj->name = $context->employee->firstname . ' ' . $context->employee->lastname;
        $logObj->ip = Tools::getRemoteAddr();
        $logObj->session_start = date('Y-m-d H:i:s');

        if ($logObj->add()) {
            return $logObj->id;
        }

        return 0;
    }

    public static function recordVisitDetails($id)
    {
        foreach (self::$CLASS_EXCEPTIONS as $exception) {
            if (strpos($_SERVER['QUERY_STRING'], $exception) !== false)
                return;
        }
        unset($exception);

        Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'bestkit_log_visit_detail`
            (
                `id_bestkit_log_visit`,
                `path`,
                `url`
            )
            VALUES (
                '.(int)$id.',
                "'.pSQL($_SERVER['QUERY_STRING']).'",
                "'.pSQL($_SERVER['REQUEST_URI']).'"
            )
        ');
    }
}