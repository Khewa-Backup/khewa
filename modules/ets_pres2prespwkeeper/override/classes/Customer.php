<?php
/**
 * 2007-2018 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2018 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class Customer extends CustomerCore
{
    public function getByEmail($email, $passwd = null, $ignore_guest = true)
    {
        if(parent::getByEmail($email, $passwd, $ignore_guest))
        {
            return $this;
        }
        else
        {
            $customer= $result = Db::getInstance()->getRow('
    		SELECT *
    		FROM `'._DB_PREFIX_.'customer`
    		WHERE `email` = \''.pSQL($email).'\'
    		'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER).'
    		AND `deleted` = 0
    		'.($ignore_guest ? ' AND `is_guest` = 0' : ''));
            
            if($passwd && $customer && isset($customer['passwd_old_wp']) && $customer['passwd_old_wp'] && $stored_hash=Configuration::get('ETS_NEW_COOKIE_KEY'))
            {                
                return $this->getByEmail2($email,$passwd,$ignore_guest,$stored_hash);
            }
            return false;
        }
    }
    public function getByEmail2($email, $passwd = null, $ignore_guest = true,$stored_hash='')
    {
        if (!Validate::isEmail($email) || ($passwd && !Validate::isPasswd($passwd))) {
            die(Tools::displayError());
        }
        $shopGroup = Shop::getGroupFromShop(Shop::getContextShopID(), false);
        $sql = new DbQuery();
        $sql->select('c.`passwd_old_wp`');
        $sql->from('customer', 'c');
        $sql->where('c.`email` = \''.pSQL($email).'\'');
        if (Shop::getContext() == Shop::CONTEXT_SHOP && $shopGroup['share_customer']) {
            $sql->where('c.`id_shop_group` = '.(int) Shop::getContextShopGroupID());
        } else {
            $sql->where('c.`id_shop` IN ('.implode(', ', Shop::getContextListShopID(Shop::SHARE_CUSTOMER)).')');
        }

        if ($ignore_guest) {
            $sql->where('c.`is_guest` = 0');
        }
        $sql->where('c.`deleted` = 0');

        $passwordHash = Db::getInstance()->getValue($sql);
        if(version_compare(_PS_VERSION_, '1.7.0', '>='))
        {
            try {
                $crypto = PrestaShop\PrestaShop\Adapter\ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
            } catch (CoreException $e) {
                return false;
            }
        }
        $shouldCheckPassword = !is_null($passwd);
        if ($shouldCheckPassword && !$this->checkHash($passwd, $passwordHash,$stored_hash)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('c.*');
        $sql->from('customer', 'c');
        $sql->where('c.`email` = \''.pSQL($email).'\'');
        if (Shop::getContext() == Shop::CONTEXT_SHOP && $shopGroup['share_customer']) {
            $sql->where('c.`id_shop_group` = '.(int) Shop::getContextShopGroupID());
        } else {
            $sql->where('c.`id_shop` IN ('.implode(', ', Shop::getContextListShopID(Shop::SHARE_CUSTOMER)).')');
        }
        if ($ignore_guest) {
            $sql->where('c.`is_guest` = 0');
        }
        $sql->where('c.`deleted` = 0');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!$result) {
            return false;
        }

        $this->id = $result['id_customer'];
        foreach ($result as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        if(version_compare(_PS_VERSION_, '1.7.0', '>='))
        {
            if ($shouldCheckPassword) {
                $this->passwd = $crypto->hash($passwd);
                $this->update();
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'customer set passwd_old_wp ="" WHERE id_customer='.(int)$this->id);
            }
        }
        else
        {
            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'customer set passwd ="'.pSQL(md5(_COOKIE_KEY_.$passwd)).'",passwd_old_wp="" WHERE id_customer='.(int)$this->id);
        }
        return $this;
    }
    public function checkHash($passwd, $hash, $staticSalt = _COOKIE_KEY_)
    {
        $hashMethods = array(
            'bcrypt' => array(
                'option' => array(),
                'hash' => function ($passwd, $staticSalt, $option) {
                    return password_hash($passwd, PASSWORD_BCRYPT);
                },
                'verify' => function ($passwd, $hash, $staticSalt) {
                    return password_verify($passwd, $hash);
                },
            ),
            'md5' => array(
                'option' => array(),
                'hash' => function ($passwd, $staticSalt, $option) {
                    return md5($staticSalt.$passwd);
                },
                'verify' => function ($passwd, $hash, $staticSalt) {
                    return md5($staticSalt.$passwd) === $hash;
                },
            ),
        );
        foreach ($hashMethods as $closure) {
            if ($closure['verify']($passwd, $hash, $staticSalt)) {
                return true;
            }
        }
        return false;
    }
}