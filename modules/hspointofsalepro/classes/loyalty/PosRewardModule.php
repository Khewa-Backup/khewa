<?php
/**
 * RockPOS PosRewardModule
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
if (!class_exists('RewardsStateModel')) {
    require_once _PS_MODULE_DIR_ . '/allinone_rewards/models/RewardsStateModel.php';
    require_once _PS_MODULE_DIR_ . '/allinone_rewards/models/RewardsTemplateModel.php';
}

class PosRewardModule extends PosLoyaltyAbstract
{
    public function __construct()
    {
        $this->voucher_code = PosConstants::LOYALTY_VOUCHER_CODE_PREFIX . '-' . rand(1000, 100000);
        $this->module_name = 'allinone_rewards';
    }

    /**
     *
     * @param int $reward
     * @return int
     */
    public function convertPoints($reward)
    {
        $context = Context::getContext();
        $virtual_value = MyConf::get('REWARDS_VIRTUAL_VALUE_' .  $context->currency->id);
        if ($virtual_value > 0) {
            $points = (float) $reward / $virtual_value;
        } else {
            $points = (float) $reward;
        }
        return $points;
    }
    
    /**
     * @see getRewardReadyForDisplay() of module allinone_rewards(allinone_rewards.php)
     * @param float $reward
     * @param int $id_currency
     * @param int $id_lang
     * @param boolean $convert
     * @param int $id_template
     * @return float
     */
    public function getRewardReadyForDisplay($reward, $id_currency = null, $id_lang = null, $convert = true, $id_template = null)
    {
        $context = Context::getContext();
        if (is_null($id_currency)) {
            $id_currency = $context->currency->id;
        }
        $currency = Currency::getCurrency((int) $id_currency);
        if (isset($context->customer)) {
            if (is_null($id_template)) {
                $id_template = (int) MyConf::getIdTemplate('core', $context->customer->id);
            }
            if (is_null($id_lang) && version_compare(_PS_VERSION_, '1.5.4.0', '>=') && !empty($context->customer->id_lang)) {
                $id_lang = $context->customer->id_lang;
            }
        }
        if (is_null($id_template)) {
            $id_template = 0;
        }
        if (is_null($id_lang)) {
            $id_lang = $context->language->id;
        }
        if (MyConf::get('REWARDS_VIRTUAL', null, $id_template)) {
            $reward = round($reward * (float) MyConf::get('REWARDS_VIRTUAL_VALUE_' . $currency['id_currency'], null, $id_template), 2);
            if ($reward != (int) $reward) {
                $reward = number_format($reward, 2, '.', ' ');
            }
            return $reward;
        } else {
            if ($convert) {
                return PosTools::posDisplayPrice(round(Tools::convertPrice((float) $reward, $currency), 2), (int) $currency['id_currency']);
            } else {
                return PosTools::posDisplayPrice(round((float) $reward, 2), (int) $currency['id_currency']);
            }
        }
    }
    
    /**
     *
     * @param PosCustomer $customer
     * @return float
     */
    public function getLoyaltyPoints($customer)
    {
        $points = 0;
        if ($this->isValidModule()) {
            $query = new DbQuery();
            $query->select('SUM(`credits`)');
            $query->from('rewards');
            $query->where('`id_cart_rule` = 0');
            $query->where('`plugin` = \'loyalty\' OR `plugin` = \'free\' OR `plugin` = \'sponsorship\' ');
            $query->where('`id_customer` = ' . (int)$customer->id);
            $query->where('`id_reward_state` = ' . (int)RewardsStateModel::getValidationId());
            $points = Db::getInstance()->getValue($query);
        }
        return $this->getRewardReadyForDisplay($points);
    }

    /**
     *
     * @param PosCartRule $cart_rule
     * @return bool
     */
    public function registerDiscount($cart_rule)
    {
        if ($this->isValidModule()) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'rewards`
                SET `id_reward_state` = ' . (int)RewardsStateModel::getConvertId() . ',
                    `id_cart_rule` = ' . (int)$cart_rule->id . '
                WHERE
                    `id_customer` = ' . (int)$cart_rule->id_customer . ' AND
                    `id_reward_state` = ' . (int)RewardsStateModel::getValidationId() . ' AND
                    (`plugin` = \'loyalty\' OR `plugin` = \'sponsorship\') AND
                    `id_cart_rule` = 0
                '
            );
        }
        return false;
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
            $name = 'REWARDS_VOUCHER_DETAILS';
        }
        return parent::generateName($name);
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
            $query->from('rewards');
            $query->where('`id_cart_rule` = 0');
            $query->where('`id_customer` = ' . (int)$id_customer);
            $query->where('`id_reward_state` = 2');
            $query->orderBy('`date_add` DESC');
            $date_add = Db::getInstance()->getValue($query);
        }
        return $date_add;
    }

    /**
     *
     * @return array
     */
    public function getRestrictedIdCategories()
    {
        $id_categories = array();
        $context = Context::getContext();
        $id_template = (int)MyConf::getIdTemplate('core', (int)$context->customer->id);
        $all_categories = (int)MyConf::get('RLOYALTY_ALL_CATEGORIES', null, $id_template);
        $categories = explode(',', MyConf::get('RLOYALTY_CATEGORIES', null, $id_template));
        if (!$all_categories && is_array($categories) && count($categories) > 0) {
            $id_categories = $categories;
        }
        return $id_categories;
    }

    /**
     *
     * @return int
     */
    public function getLoyaltyTax()
    {
        $context = Context::getContext();
        $id_template = (int)MyConf::getIdTemplate('core', (int)$context->customer->id);
        return (int)MyConf::get('REWARDS_VOUCHER_TAX', null, $id_template);
    }

    /**
     *
     * @return int
     */
    public function getMinimumAmount()
    {
        $context = Context::getContext();
        $id_template = (int)MyConf::getIdTemplate('core', (int)$context->customer->id);
        return (int)MyConf::get('REWARDS_VOUCHER_MIN_ORDER_' . $context->currency->id, null, $id_template);
    }
    
    public function getMinimumVoucherValue()
    {
        $context = Context::getContext();
        $id_template = (int)MyConf::getIdTemplate('core', (int)$context->customer->id);
        return (int)MyConf::get('REWARDS_VOUCHER_MIN_VALUE_' . $context->currency->id, null, $id_template);
    }
}
