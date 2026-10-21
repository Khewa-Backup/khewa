<?php
/**
 * RockPOS PosRewardModule
 *
 * @author Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
if (!class_exists('LoyaltyPoint')) {
    require_once _PS_MODULE_DIR_ . '/loyaltyeditpoints/classes/LoyaltyEditPointsPoint.php';
    require_once _PS_MODULE_DIR_ . '/loyaltyeditpoints/classes/LoyaltyEditPointsAssignment.php';
}

class PosLoyaltyEditPointsModule extends PosLoyaltyAbstract
{
    public function __construct()
    {
        $this->voucher_code = PosConstants::LOYALTY_VOUCHER_CODE_PREFIX . '-' . rand(1000, 100000);
        $this->module_name = 'loyaltyeditpoints';
    }

    /**
     *
     * @param float $points
     * @return float
     */
    public function convertPoints($points)
    {
        return (float)LoyaltyEditPointsPoint::getVoucherValue($points);
    }

    /**
     *
     * @param PosCustomer $customer
     * @return float
     */
    public function getLoyaltyPoints($customer)
    {
        return (float)LoyaltyEditPointsPoint::getPointsByCustomer($customer->id);
    }

    /**
     *
     * @param PosCartRule $cart_rule
     * @return boolean
     */
    public function registerDiscount($cart_rule)
    {
        $customerPoints = LoyaltyEditPointsPoint::getPointsByCustomer((int) $cart_rule->id_customer);
        if ((int) Configuration::get('LEP_MAXIMUM_POINTS') && ($customerPoints > (int) Configuration::get('LEP_MAXIMUM_POINTS'))) {
            $maxPoints = Configuration::get('LEP_MAXIMUM_POINTS');
        } else {
            $maxPoints = $customerPoints;
        }
        return LoyaltyEditPointsPoint::registerDiscount($cart_rule, $maxPoints);
    }

    /**
     * Return the date when the last loyalty is recorded, in timestamp
     * @param int $id_customer
     * @return int
     */
    public function getLatestLoyaltyDate($id_customer)
    {
        $date_add = '';
        if ($this->isValidModule()) {
            $query = new DbQuery();
            $query->select('UNIX_TIMESTAMP(`date_add`)');
            $query->from('loyaltyeditpoints');
            $query->where('`id_cart_rule` = 0');
            $query->where('`id_customer` = ' . (int)$id_customer);
            $query->orderBy('`date_add` DESC');
            $date_add = Db::getInstance()->getValue($query);
        }
        return $date_add;
    }

    /**
     * @param string $name
     * @return array
     * <pre>
     * array(
     *  int => string,//id_lang => cart rule's name
     *  int => string,
     *  ...
     * )
     */
    public function generateName($name = null)
    {
        if (is_null($name)) {
            $name = 'LEP_VOUCHER_DETAILS';
        }
        return parent::generateName($name);
    }

    /**
     *
     * @return array
     */
    public function getRestrictedIdCategories()
    {
        $id_categories = array();
        $categories = Configuration::get('LEP_VOUCHER_CATEGORY');
        if ($categories) {
            $id_categories = explode(',', $categories);
        }
        return $id_categories;
    }

    /**
     *
     * @return int
     */
    public function getLoyaltyTax()
    {
        return Configuration::get('LEP_TAX');
    }

    /**
     *
     * @return int
     */
    public function getMinimumAmount()
    {
        return Configuration::get('LEP_MINIMAL');
    }
    public function getMinimumVoucherValue()
    {
        return Configuration::get('LEP_MINIMAL');
    }
}
