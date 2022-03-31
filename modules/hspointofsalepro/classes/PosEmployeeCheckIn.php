<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PosEmployeeCheckIn extends ObjectModel
{
    public $id_pos_employee_checkin;
    public $id_employee;
    public $employee_ip;
    public $action;
    public $id_shop;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'pos_employee_checkin',
        'primary' => 'id_pos_employee_checkin',
        'multilang' => false,
        'fields' => array(
            'id_employee' => array('type' => self::TYPE_INT),
            'employee_ip' => array('type' => self::TYPE_STRING),
            'action' => array('type' => self::TYPE_STRING),
            'id_shop' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * PosEmployeeCheckIn constructor.
     * @param $id
     * @param $id_employee
     * @param $employee_ip
     * @param $action
     */
    public function __construct($id, $id_employee, $employee_ip, $action, $id_shop)
    {
        $this->id_employee = $id_employee;
        $this->id = $id;
        $this->employee_ip = $employee_ip;
        $this->action = $action;
        $this->id_shop = $id_shop;

        parent::__construct($id);
    }
}
