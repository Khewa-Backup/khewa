<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/*
 * Created EtsyReturnPolicy model class for return policy management similar to EtsyShopSection
 * @modifier Himanshu Vishwakarma
 * @date 15-12-2025
 */
//Class and its methods to handle
class EtsyReturnPolicy extends ObjectModel
{

    public $id;
    public $return_policy_id;
    public $shop_id;
    public $accepts_returns;
    public $accepts_exchanges;
    public $return_deadline;
    /*
     * Updated 'fields' definition to include only required fields as per Etsy API: return_policy_id, shop_id, accepts_returns, accepts_exchanges, return_deadline
     * @modifier Himanshu Vishwakarma
     * @date 15-12-2025
     */
    public static $definition = array(
        'table' => 'etsy_return_policy',
        'primary' => 'id_etsy_return_policy',
        'fields' => array(
            'return_policy_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20, 'required' => true),
            'shop_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20, 'required' => true),
            'accepts_returns' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'accepts_exchanges' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'return_deadline' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        )
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public static function getReturnPolicyDetails($id_etsy_return_policy = '')
    {
        if (!empty($id_etsy_return_policy)) {
            $getDetailsSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_return_policy WHERE id_etsy_return_policy = '" . (int) $id_etsy_return_policy . "'";
            $getReturnPolicyDetails = Db::getInstance()->getRow($getDetailsSQL, true, false);
        } else {
            $getDetailsSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_return_policy";
            $getReturnPolicyDetails = Db::getInstance()->executeS($getDetailsSQL, true, false);
        }

        return $getReturnPolicyDetails;
    }

    public static function getTotalReturnPolicies()
    {
        $query = "SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_return_policy";
        $result = Db::getInstance()->getValue($query, true, false);
        return $result;
    }
}

