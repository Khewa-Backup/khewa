<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class GiftcardHistory extends ObjectModel
{
    public $id;
    public $id_giftcard;
    public $id_product;
    public $amount;
    public $id_customer;
    public $id_order;
    public $type;
    public $buyerName;
    public $recipientName;
    public $recipientMail;
    public $send_date;
    public $id_state;
    public $text;
    public $image;
    public $sendLater;
    public $id_cartRule;
    public $validity_begin;
    public $validity_end;
    public $isUse;
    public $id_shop;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'psgiftcards_history',
        'primary' => 'id_giftcard_history',
        'fields' => [
            // Config fields
            'id_giftcard' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'amount' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'type' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'buyerName' => ['type' => self::TYPE_STRING, 'validate' => 'isMailName', 'required' => false],
            'recipientName' => ['type' => self::TYPE_STRING, 'validate' => 'isMailName', 'required' => false],
            'recipientMail' => ['type' => self::TYPE_STRING, 'validate' => 'isMailName', 'required' => false],
            'send_date' => ['type' => self::TYPE_DATE, 'required' => false],
            'id_state' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'text' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 3999999999999],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false],
            'sendLater' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'id_cartRule' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'validity_begin' => ['type' => self::TYPE_DATE, 'required' => false],
            'validity_end' => ['type' => self::TYPE_DATE, 'required' => false],
            'isUse' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
        ],
    ];

    public static function getGiftcardHistoryByOrder($id_order, $id_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_order =' . (int) $id_order . ' and psgch.id_shop =' . (int) $id_shop . ';';
        $result = Db::getInstance()->executeS($sql);

        return $result;
    }

    public static function getCustomerById($id_giftcard)
    {
        $sql = 'SELECT `id_customer` as id_customer FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_giftcard_history =' . (int) $id_giftcard . ';';
        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public static function getGiftcardsHistoryByCustomer($id_customer, $id_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_customer =' . (int) $id_customer . ' and psgch.id_shop =' . (int) $id_shop . ';';
        $result = Db::getInstance()->executeS($sql);

        return $result;
    }

    public static function getGiftcardsHistory()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch;';
        $giftcardsHistory = (array) Db::getInstance()->executeS($sql);

        $context = Context::getContext();
        $giftcards = [];

        foreach ($giftcardsHistory as $giftcard) {
            $order = new Order($giftcard['id_order']);
            $orderDate = $order->date_add;

            $customer = new Customer($giftcard['id_customer']);
            $customerLastname = $customer->lastname;
            $customerFirstname = $customer->firstname;
            // $customerMail = $customer->email;

            $currencySymbol = $context->currency->sign;

            $cartRule = new CartRule((int) $giftcard['id_cartRule']);

            $module = Module::getInstanceByName('psgiftcards');
            $state = '';

            switch ($giftcard['id_state']) {
                case '1':
                    $state = $module->l('Awaiting validation');
                    break;
                case '2':
                case '6':
                    $state = $module->l('To be configured');
                    break;
                case '3':
                    $state = $module->l('Scheduled');
                    break;
                case '4':
                    $state = $module->l('Downloaded');
                    break;
                case '5':
                    $state = $module->l('Sent');
                    break;
            }

            $giftcards[] = [
                'id_giftcard' => $giftcard['id_giftcard'],
                'id_order' => $giftcard['id_order'],
                'id_cartRule' => $giftcard['id_cartRule'],
                'name' => $customerLastname . ' ' . $customerFirstname,
                'code' => $cartRule->code,
                'price' => number_format(Product::getPriceStatic($giftcard['id_product'], true), 2) . ' ' . $currencySymbol,
                'purchase_date' => $orderDate,
                'status' => $state,
            ];

            unset($order, $customer, $cartRule);
        }

        return $giftcards;
    }

    public static function setStatus($id_giftcard_history, $id_state)
    {
        $giftcard = new GiftcardHistory((int) $id_giftcard_history);
        $giftcard->id_state = $id_state;
        if ($id_state == 5) {
            $giftcard->sendLater = 0;
            $giftcard->type = 0;
        }
        $giftcard->save();

        return;
    }

    public static function getGiftcardById($id_giftcard_history)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_giftcard_history =' . (int) $id_giftcard_history . ';';
        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    public static function getGiftcards($is_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_shop =' . (int) $is_shop . ';';
        $result = Db::getInstance()->executeS($sql);

        return $result;
    }

    public static function getGiftcardsTotal($is_shop)
    {
        $sql = 'SELECT count(*) as total FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.id_shop =' . (int) $is_shop . ';';
        $result = Db::getInstance()->getRow($sql);
        $result = $result['total'];

        return $result;
    }

    public static function getGiftcardsUsed()
    {
        $sql = 'SELECT count(*) as total FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch WHERE psgch.isUse = 1;';
        $result = Db::getInstance()->getRow($sql);
        $result = $result['total'];

        return $result;
    }

    public static function getGiftcardTotalAmount()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch;';
        $giftcards = (array) Db::getInstance()->executeS($sql);

        $amount = 0;
        foreach ($giftcards as $giftcard) {
            $giftcard_amount = Product::getPriceStatic($giftcard['id_product'], true);
            $amount = $amount + $giftcard_amount;
        }
        $default_currency = Currency::getCurrency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        $currency = new Currency((int) $default_currency);
        $sign = $currency->sign;

        $amount = number_format($amount, 2) . ' ' . $sign;

        return $amount;
    }

    public static function getCartRule($id_giftcard_history, $id_shop)
    {
        $sql = 'SELECT `id_cartRule`
                FROM `' . _DB_PREFIX_ . 'psgiftcards_history` psgch
                WHERE psgch.id_giftcard_history = ' . (int) $id_giftcard_history . '
                AND psgch.id_shop = ' . (int) $id_shop;
        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public static function getCartRules()
    {
        $sql = 'SELECT `id_cartRule` FROM `' . _DB_PREFIX_ . 'psgiftcards_history`';
        $result = Db::getInstance()->executeS($sql);

        return $result;
    }

    /**
     * prepareContentDatas
     *
     * @param int $templateId
     * @param array $data
     *
     * @return array $data
     */
    private function prepareContentDatas($templateId, $data)
    {
        foreach ($data as $key => $aData) {
            $iLangId = (int) $aData['id_lang'];

            // we save the data only if email_content and subject aren't empty
            if (!empty($aData['email_content_' . $iLangId]) && !empty($aData['email_subject'])) {
                // We escape all the datas
                $data[$key] = array_map('pSQL', $data[$key]);

                // remove all  "_idlang" from the data key (we must do that because of ckeditor instances names)
                // These following datas must keep their HTML tags
                $data[$key]['email_content'] = htmlentities($aData['email_content_' . $iLangId], ENT_QUOTES);
                $data[$key]['email_discount'] = htmlentities($aData['email_discount_' . $iLangId], ENT_QUOTES);
                $data[$key]['email_unsubscribe'] = htmlentities($aData['email_unsubscribe_' . $iLangId], ENT_QUOTES);
                unset($data[$key]['email_content_' . $iLangId]);
                unset($data[$key]['email_discount_' . $iLangId]);
                unset($data[$key]['email_unsubscribe_' . $iLangId]);

                // we add id_template and lang_iso values to the array (very important)
                $data[$key]['id_template'] = $templateId;
                $data[$key]['lang_iso'] = Language::getIsoById($iLangId);
            } else {
                // We won't save or update this array
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Save the Content datas
     *
     * @return bool
     */
    private function saveContentConf($data)
    {
        foreach ($data as $key => $aData) {
            if (!Db::getInstance()->insert('cart_abandonment_template_lang', $aData)) {
                return false;
            }
        }

        return true;
    }

    public static function getGiftcardsMailsLang()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_mail_lang`;';

        return Db::getInstance()->executeS($sql);
    }

    public static function getGiftcardsMailsLangbyLang($id_lang, $id_template = null)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards_mail_lang` WHERE id_lang =' . $id_lang;
        if ($id_template != null) {
            $sql .= ' AND id_template = ' . $id_template;
        }

        return Db::getInstance()->executeS($sql);
    }
}
