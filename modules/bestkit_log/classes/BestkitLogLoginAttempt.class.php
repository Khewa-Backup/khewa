<?php
/**
 * Employee authentication attempt model.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            'status' 				=> array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'user_agent' 				=> array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' 			=> array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function recordAuthenticationAttempt(
        $email,
        $successful,
        $idEmployee = 0,
        $employeeName = '',
        $ipAddress = '',
        $userAgent = ''
    ) {
        $email = trim((string) $email);
        $idEmployee = (int) $idEmployee;

        if ($idEmployee === 0 && Validate::isEmail($email)) {
            $employee = Db::getInstance()->getRow(
                'SELECT `id_employee`, `firstname`, `lastname`
                FROM `' . _DB_PREFIX_ . 'employee`
                WHERE `email` = \'' . pSQL($email) . '\''
            );

            if (is_array($employee)) {
                $idEmployee = (int) $employee['id_employee'];
                $employeeName = trim($employee['firstname'] . ' ' . $employee['lastname']);
            }
        }

        $obj = new BestkitLogLoginAttempt();
        $obj->id_employee = $idEmployee;
        $obj->email = $email;
        $obj->name = (string) $employeeName;
        $obj->status = $successful ? 1 : 0;
        $obj->ip = $ipAddress !== '' ? (string) $ipAddress : (string) Tools::getRemoteAddr();
        $obj->user_agent = $userAgent !== ''
            ? (string) $userAgent
            : (isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '');

        return $obj->save();
    }
}
