<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostPs;

use \ObjectModel;
use \Db;

class RateDiscount extends \ObjectModel
{
    public $id_method;
    public $apply_discount;
    public $discount_value;
    public $id_discount_currency;
    public $order_value;
    public $id_order_currency;
    public $include_tax;
    public $include_discounts;
    public $include_shipping;
    public $active;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public static $discountTypes = array(
        'percent' => 'Percentage (%)',
        'amount'  => 'Amount ($)',
        'free_shipping' => 'Free Shipping'
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'cpl_rate_discount',
        'primary' => 'id_cpl_rate_discount',
        'multilang_shop' => true,
        'fields' => array(
            'id_method' => array('type' => self::TYPE_INT,  'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'apply_discount' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'discount_value' => array('type' => self::TYPE_FLOAT,  'validate' => 'isFloat', 'required' => false, 'size' => 10),
            'id_discount_currency' => array('type' => self::TYPE_INT,  'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'order_value' => array('type' => self::TYPE_FLOAT,  'validate' => 'isPrice', 'required' => true, 'size' => 10),
            'id_order_currency' => array('type' => self::TYPE_INT,  'validate' => 'isUnsignedInt', 'required' => true, 'size' => 10),
            'include_tax' => array('type' => self::TYPE_INT,  'validate' => 'isBool', 'required' => true, 'size' => 10),
            'include_discounts' => array('type' => self::TYPE_INT,  'validate' => 'isBool', 'required' => true, 'size' => 10),
            'include_shipping' => array('type' => self::TYPE_INT,  'validate' => 'isBool', 'required' => true, 'size' => 10),
            'active' => array('type' => self::TYPE_INT,  'validate' => 'isBool', 'required' => true, 'size' => 10),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    public static function getRateDiscounts($where = false, $id_shop = null)
    {
        return \Db::getInstance()->ExecuteS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' rd' .
            ($id_shop ? \Shop::addSqlAssociation(self::$definition['table'], 'rd') : '') .
            ($where ? Tools::sanitizeWhere($where) : '')
        );
    }

    public static function getRateDiscount($id)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' WHERE `'.self::$definition['primary'].'` = ' . (int)$id
        );
    }

    public static function getActiveRateDiscountByMethodId($id_method, $id_shop = null)
    {
        return \Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . self::$definition['table'] . ' rd
            '. ($id_shop ? \Shop::addSqlAssociation(self::$definition['table'], 'rd') : '').'
            WHERE `id_method` = ' . (int)$id_method.'
            AND `active` = 1'
        );
    }

    /**
     * @var $Rate CacheRate
     * @var $convertedCost float
     * @return float
     * */
    public static function applyDiscountToRate($Rate, $convertedCost)
    {
        $methodArr = Method::getMethodByCode($Rate->code);
        $Method = new Method($methodArr['id_method']);
        $Cache = new Cache($Rate->id_cache);
        $Cart = new \Cart($Cache->id_cart);

        $rateDiscountArr = self::getActiveRateDiscountByMethodId($Method->id, $Cart->id_shop);
        if ($rateDiscountArr) {
            $RateDiscount = new RateDiscount($rateDiscountArr[RateDiscount::$definition['primary']]);
            $Currency = new \Currency($Cart->id_currency);

            $orderTotal = 0;
            $applyDiscount = false;

            if ($RateDiscount->order_value > 0) {
                // Convert order minimum to cart currency
                $orderTotalCurrency = new \Currency($RateDiscount->id_order_currency);
                $convertedOrderMin = \Tools::convertPriceFull(
                    $RateDiscount->order_value,
                    $orderTotalCurrency,
                    $Currency
                );

                $orderTotal = $Cart->getOrderTotal(
                    (int)$RateDiscount->include_tax,
                    \Cart::ONLY_PRODUCTS,
                    null,
                    null
                );

                // Subtract discounts
                if ($RateDiscount->include_discounts) {
                    $discountTotal = 0;
                    $cartRules     = $Cart->getCartRules(\CartRule::FILTER_ACTION_REDUCTION);

                    foreach ($cartRules as $cartRule) {
                        $CartRule = new \CartRule($cartRule['id_cart_rule']);
                        if ($CartRule->reduction_percent > 0 || $CartRule->reduction_amount > 0) {
                            $discountTotal += Tools::ps_round(
                                $CartRule->getContextualValue(
                                    (int)$RateDiscount->include_tax,
                                    null,
                                    \CartRule::FILTER_ACTION_REDUCTION,
                                    null,
                                    false
                                ),
                                2
                            );
                        }
                    }

                    $orderTotal -= $discountTotal;
                }

                // Add Shipping
                if ($RateDiscount->include_shipping) {
                    $convertedRate = \Tools::convertPriceFull(
                        $convertedCost,
                        $orderTotalCurrency,
                        $Currency
                    );
                    $orderTotal += $convertedRate;
                }

                // If order meets the total requirement, apply discount
                if ($orderTotal >= $convertedOrderMin) {
                    $applyDiscount = true;
                }
            } else {
                $applyDiscount = true;
            }

            if ($applyDiscount) {
                $discountValueCurrency = new \Currency($RateDiscount->id_discount_currency);

                $reducedRate = (float)$convertedCost;
                switch ($RateDiscount->apply_discount) {
                    case 'free_shipping':
                        $reducedRate = 0;
                        break;
                    case 'amount':
                        $convertedAmount = \Tools::convertPriceFull(
                            $RateDiscount->discount_value,
                            $discountValueCurrency,
                            $Currency
                        );
                        $reducedRate -= $convertedAmount;
                        break;
                    case 'percent':
                        $reducedRate = $reducedRate - ($reducedRate * ($RateDiscount->discount_value / 100));
                        break;
                    default:
                        $reducedRate = (float)$convertedCost;
                }

                // If reduced rate is negative, shipping is free
                if ($reducedRate <= 0) {
                    return 0;
                } else {
                    return (float)Tools::ps_round($reducedRate, 2);
                }
            } else {
                return (float)$convertedCost;
            }
        } else {
            return (float)$convertedCost;
        }
    }
}
