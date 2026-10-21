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
class PosOrderPayment extends OrderPayment
{
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'order_payment',
        'primary' => 'id_order_payment',
        'fields' => [
            'order_reference' => ['type' => self::TYPE_STRING, 'size' => 25],
            'id_currency' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'required' => true],
            'payment_method' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'conversion_rate' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'transaction_id' => ['type' => self::TYPE_STRING, 'size' => 254],
            'card_number' => ['type' => self::TYPE_STRING, 'size' => 254],
            'card_brand' => ['type' => self::TYPE_STRING, 'size' => 254],
            'card_expiration' => ['type' => self::TYPE_STRING, 'size' => 254],
            'card_holder' => ['type' => self::TYPE_STRING, 'size' => 254],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
