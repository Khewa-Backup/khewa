<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class PosOrderReturn extends ObjectModel
{
    /** @var int */
    public $id_returned_order;

    /** @var int */
    public $id_new_order;

    /** @var string Object creation date */
    public $date_add;

    public static $definition = array(
        'table' => 'pos_returned_order',
        'primary' => 'id_returned_order',
        'fields' => array(
            'id_returned_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_new_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
}
