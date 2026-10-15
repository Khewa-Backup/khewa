<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
*/
class WkDeletedCustomer extends ObjectModel
{
    public $id_customer;
    public $id_shop_group;
    public $id_shop;
    public $id_gender;
    public $id_default_group;
    public $id_lang;
    public $id_risk;
    public $company;
    public $siret;
    public $ape;
    public $firstname;
    public $lastname;
    public $email;
    public $passwd;
    public $last_passwd_gen;
    public $birthday;
    public $newsletter;
    public $ip_registration_newsletter;
    public $newsletter_date_add;
    public $optin;
    public $website;
    public $outstanding_allow_amount;
    public $show_public_prices;
    public $max_payment_days;
    public $secure_key;
    public $note;
    public $active;
    public $is_guest;
    public $deleted;
    public $reset_password_token;
    public $reset_password_validity;
    public $customer_group;
    public $customer_address;
    public $customer_cart_rule;
    public $customer_message;
    public $guest;
    public $specific_price;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_customer',
        'primary' => 'id_wk_deleted_customer',
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'id_gender' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_default_group' => ['type' => self::TYPE_INT, 'copy_post' => false],
            'id_lang' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'id_risk' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false],
            'company' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'siret' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'ape' => ['type' => self::TYPE_STRING, 'validate' => 'isApe'],
            'firstname' => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 255],
            'lastname' => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 255],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255],
            'passwd' => ['type' => self::TYPE_STRING, 'validate' => 'isPlaintextPassword', 'required' => true, 'size' => 255],
            'last_passwd_gen' => ['type' => self::TYPE_STRING, 'copy_post' => false],
            'birthday' => ['type' => self::TYPE_DATE, 'validate' => 'isBirthDate'],
            'newsletter' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'ip_registration_newsletter' => ['type' => self::TYPE_STRING, 'copy_post' => false],
            'newsletter_date_add' => ['type' => self::TYPE_DATE, 'copy_post' => false],
            'optin' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'website' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
            'outstanding_allow_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'copy_post' => false],
            'show_public_prices' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
            'max_payment_days' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false],
            'secure_key' => ['type' => self::TYPE_STRING, 'validate' => 'isMd5', 'copy_post' => false],
            'note' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 65000, 'copy_post' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
            'is_guest' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
            'deleted' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
            'reset_password_token' => ['type' => self::TYPE_STRING, 'validate' => 'isSha1', 'size' => 40, 'copy_post' => false],
            'reset_password_validity' => ['type' => self::TYPE_DATE, 'validate' => 'isDateOrNull', 'copy_post' => false],
            'customer_group' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'customer_address' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'customer_cart_rule' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'customer_message' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'guest' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'specific_price' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function getCustomerDetailBeforeDelete($idCustomer)
    {
        if ($idCustomer) {
            $customerInfo = $this->getCustomer($idCustomer);
            if ($customerInfo) {
                $customerGroup = $this->getCustomerGroupsByCustomerId($idCustomer);
                if ($customerGroup) {
                    $customerInfo['customer_group'] = json_encode($customerGroup);
                } else {
                    $customerInfo['customer_group'] = null;
                }
                $customerAddress = $this->getCustomerAddressById($idCustomer);
                if ($customerAddress) {
                    $customerInfo['customer_address'] = json_encode($customerAddress);
                } else {
                    $customerInfo['customer_address'] = null;
                }
                $customerCartRule = $this->getCartRuleByCustomerId($idCustomer);
                if ($customerCartRule) {
                    $customerInfo['customer_cart_rule'] = json_encode($customerCartRule);
                } else {
                    $customerInfo['customer_cart_rule'] = null;
                }
                $specificPrice = $this->getSpecificPriceByCustomerId($idCustomer);
                if ($specificPrice) {
                    $customerInfo['specific_price'] = json_encode($specificPrice);
                } else {
                    $customerInfo['specific_price'] = null;
                }
                $guest = Guest::getFromCustomer($idCustomer);
                if ($guest) {
                    $customerInfo['guest'] = json_encode($guest);
                } else {
                    $customerInfo['guest'] = null;
                }
                $customerMessage = $this->getCustomerMessageByCustomerId($idCustomer);
                if ($customerMessage) {
                    $customerInfo['customer_message'] = json_encode($customerMessage);
                } else {
                    $customerInfo['customer_message'] = null;
                }
                $this->saveDeletedCustomer($customerInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(5, $idCustomer);
        }
    }

    public function getCustomer($idCustomer)
    {
        if ($idCustomer) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'customer`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
        }

        return false;
    }

    public function getCustomerGroupsByCustomerId($idCustomer)
    {
        if ($idCustomer) {
            return Db::getInstance()->executeS(
                'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'customer_group`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
        }

        return false;
    }

    public function getCustomerAddressById($idCustomer)
    {
        if ($idCustomer) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'address`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
        }

        return false;
    }

    public function getCartRuleByCustomerId($idCustomer)
    {
        if ($idCustomer) {
            $cartRules = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'cart_rule`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
            if ($cartRules) {
                $allCartRules = [];
                foreach ($cartRules as $cartRule) {
                    if ($cartRule['id_cart_rule']) {
                        $cartRule['name'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'cart_rule_lang`
                            WHERE `id_cart_rule` = ' . (int) $cartRule['id_cart_rule']
                        );
                    }
                    array_push($allCartRules, $cartRule);
                }

                return $allCartRules;
            }
        }

        return false;
    }

    public function getSpecificPriceByCustomerId($idCustomer)
    {
        if ($idCustomer) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'specific_price`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
        }

        return false;
    }

    public function getCustomerMessageByCustomerId($idCustomer)
    {
        if ($idCustomer) {
            $threads = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'customer_thread`
                WHERE `id_customer` = ' . (int) $idCustomer
            );
            if ($threads) {
                $allThread = [];
                foreach ($threads as $thread) {
                    if ($thread['id_customer_thread']) {
                        $thread['msg'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'customer_message`
                            WHERE `id_customer_thread` = ' . (int) $thread['id_customer_thread']
                        );
                    }
                    array_push($allThread, $thread);
                }

                return $allThread;
            }
        }

        return false;
    }

    public function saveDeletedCustomer($customerInfo)
    {
        if (!empty($customerInfo) && $customerInfo) {
            $objDeletedCustomer = new WkDeletedCustomer();
            $objDeletedCustomer->id_customer = $customerInfo['id_customer'];
            $objDeletedCustomer->id_shop_group = $customerInfo['id_shop_group'];
            $objDeletedCustomer->id_shop = $customerInfo['id_shop'];
            $objDeletedCustomer->id_gender = $customerInfo['id_gender'];
            $objDeletedCustomer->id_default_group = $customerInfo['id_default_group'];
            $objDeletedCustomer->id_lang = $customerInfo['id_lang'];
            $objDeletedCustomer->id_risk = $customerInfo['id_risk'];
            $objDeletedCustomer->company = $customerInfo['company'];
            $objDeletedCustomer->siret = $customerInfo['siret'];
            $objDeletedCustomer->ape = $customerInfo['ape'];
            $objDeletedCustomer->firstname = $customerInfo['firstname'];
            $objDeletedCustomer->lastname = $customerInfo['lastname'];
            $objDeletedCustomer->email = $customerInfo['email'];
            $objDeletedCustomer->passwd = $customerInfo['passwd'];
            $objDeletedCustomer->last_passwd_gen = $customerInfo['last_passwd_gen'];
            $objDeletedCustomer->birthday = $customerInfo['birthday'];
            $objDeletedCustomer->newsletter = $customerInfo['newsletter'];
            $objDeletedCustomer->ip_registration_newsletter = $customerInfo['ip_registration_newsletter'];
            $objDeletedCustomer->newsletter_date_add = $customerInfo['newsletter_date_add'];
            $objDeletedCustomer->optin = $customerInfo['optin'];
            $objDeletedCustomer->website = $customerInfo['website'];
            $objDeletedCustomer->outstanding_allow_amount = $customerInfo['outstanding_allow_amount'];
            $objDeletedCustomer->show_public_prices = $customerInfo['show_public_prices'];
            $objDeletedCustomer->max_payment_days = $customerInfo['max_payment_days'];
            $objDeletedCustomer->secure_key = $customerInfo['secure_key'];
            $objDeletedCustomer->note = $customerInfo['note'];
            $objDeletedCustomer->active = $customerInfo['active'];
            $objDeletedCustomer->is_guest = $customerInfo['is_guest'];
            $objDeletedCustomer->deleted = $customerInfo['deleted'];
            $objDeletedCustomer->reset_password_token = $customerInfo['reset_password_token'];
            $objDeletedCustomer->reset_password_validity = $customerInfo['reset_password_validity'];
            $objDeletedCustomer->customer_group = $customerInfo['customer_group'];
            $objDeletedCustomer->customer_address = $customerInfo['customer_address'];
            $objDeletedCustomer->customer_cart_rule = $customerInfo['customer_cart_rule'];
            $objDeletedCustomer->guest = $customerInfo['guest'];
            $objDeletedCustomer->customer_message = $customerInfo['customer_message'];
            $objDeletedCustomer->specific_price = $customerInfo['specific_price'];
            $objDeletedCustomer->save();
        }
    }

    public function getDeletedCustomerDetail($idDeletedCustomer)
    {
        if ($idDeletedCustomer) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_customer`
                WHERE `id_wk_deleted_customer` = ' . (int) $idDeletedCustomer
            );
        }

        return false;
    }

    public static function deleteGDPRCustomer($idCustomer)
    {
        $result = Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'wk_deleted_customer` WHERE `id_customer` = ' . (int) $idCustomer
        );
        if ($result) {
            return true;
        }

        return false;
    }

    public static function getGDPRCustomer($idCustomer)
    {
        $result = Db::getInstance()->execute(
            'SELECT `firstname`, `lastname`, `email`, `birthday` FROM `' . _DB_PREFIX_ . 'wk_deleted_customer`
            WHERE `id_customer` = ' . (int) $idCustomer
        );
        if ($result) {
            return true;
        }

        return false;
    }

    public static function insertDataInPrimaryTable($customerInfo)
    {
        if ($customerInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'customer` (`id_customer`, `id_shop_group`, `id_shop`, `id_gender`,
                `id_default_group`, `id_lang`, `id_risk`, `company`, `siret`, `ape`, `firstname`, `lastname`, `email`,
                `passwd`, `last_passwd_gen`, `birthday`, `newsletter`, `ip_registration_newsletter`,
                `newsletter_date_add`, `optin`, `website`, `outstanding_allow_amount`, `show_public_prices`,
                `max_payment_days`, `secure_key`, `note`, `active`, `is_guest`, `deleted`, `date_add`, `date_upd`,
                `reset_password_token`, `reset_password_validity`) VALUES (' . (int) $customerInfo['id_customer'] . ", '1',
                '1', '', '1', NULL, '1', NULL, NULL, NULL, '', '', '', '',
                '" . pSQL($customerInfo['last_passwd_gen']) . "', NULL, '0', NULL, NULL, '0', NULL, '0.000000', '0',
                '60', '-1', NULL, '0', '0', '0', '" . pSQL($customerInfo['date_add']) . "',
                '" . pSQL($customerInfo['date_upd']) . "', NULL, NULL)"
            );
        }

        return false;
    }
}
