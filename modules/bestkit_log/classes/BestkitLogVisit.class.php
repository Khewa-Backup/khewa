<?php
/**
 * Back-office visit model.
 *
 * @author Best-Kit
 * @copyright 2026 Best-Kit
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    public static function registerNewVisit($context)
    {
        if (!Validate::isLoadedObject($context->employee)) {
            return 0;
        }

        $logObj = new BestkitLogVisit();
        $logObj->id_employee = (int) $context->employee->id;
        $logObj->email = (string) $context->employee->email;
        $logObj->name = $context->employee->firstname . ' ' . $context->employee->lastname;
        $logObj->ip = Tools::getRemoteAddr();
        $logObj->session_start = date('Y-m-d H:i:s');
        $logObj->session_end = $logObj->session_start;

        if ($logObj->add()) {
            return $logObj->id;
        }

        return 0;
    }

    public static function belongsToEmployee($visitId, $employeeId)
    {
        return (bool) Db::getInstance()->getValue(
            'SELECT 1
            FROM `' . _DB_PREFIX_ . 'bestkit_log_visit`
            WHERE `id_bestkit_log_visit` = ' . (int) $visitId . '
                AND `id_employee` = ' . (int) $employeeId
        );
    }

    public static function recordVisitDetails($id)
    {
        $queryString = isset($_SERVER['QUERY_STRING'])
            ? BestkitLog::redactSensitiveQueryValues('?' . $_SERVER['QUERY_STRING'])
            : '';
        $queryString = ltrim($queryString, '?');
        $requestUri = isset($_SERVER['REQUEST_URI'])
            ? BestkitLog::redactSensitiveQueryValues($_SERVER['REQUEST_URI'])
            : '';

        foreach (self::$CLASS_EXCEPTIONS as $exception) {
            if (strpos($queryString, $exception) !== false) {
                return;
            }
        }

        Db::getInstance()->update(
            'bestkit_log_visit',
            array('session_end' => date('Y-m-d H:i:s')),
            '`id_bestkit_log_visit` = ' . (int) $id
        );

        return Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'bestkit_log_visit_detail`
            (
                `id_bestkit_log_visit`,
                `path`,
                `url`,
                `stay_duration`
            )
            VALUES (
                '.(int)$id.',
                "'.pSQL($queryString).'",
                "'.pSQL($requestUri).'",
                0
            )
        ');
    }
}
