<?php

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class BestkitLogLoginAttempt extends ObjectModel
{
    public $id_employee;
    public $email;
    public $name;
    public $ip;
    public $status = 0;
    public $user_agent;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'bestkit_log_login_attempt',
        'primary' => 'id_bestkit_log_login_attempt',
        'multilang' => FALSE,
        'fields' => array(
            'id_employee' 		=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'email' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'name' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'ip' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt'),
            'user_agent' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function recordLoginAttempt($email, $passwd) {
        Context::getContext()->employee = new Employee();
        $employeeObj = Context::getContext()->employee->getByEmail($email, $passwd);

        $obj = new BestkitLogLoginAttempt();
        if ($employeeObj) {
            $obj->id_employee = $employeeObj->id;
            $obj->email = $employeeObj->email;
            $obj->name = $employeeObj->firstname . ' ' . $employeeObj->lastname;
            $obj->status = 1;
        }
        $obj->ip = Tools::getRemoteAddr();
        $obj->user_agent = $_SERVER['HTTP_USER_AGENT'];

        $obj->save();
    }
}