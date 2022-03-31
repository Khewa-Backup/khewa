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
        if (!Validate::isEmail($email) || ($passwd && !Validate::isPasswd($passwd))) {
            die(Tools::displayError());
        }
        $result = Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'customer`
		WHERE `email` = \''.pSQL($email).'\'
		'.(isset($passwd) ? 'AND `passwd` = \''.md5(pSQL(_COOKIE_KEY_.$passwd)).'\'' : '').'
		AND `deleted` = 0
		'.($ignore_guest ? ' AND `is_guest` = 0' : ''));
        
        if (!$result) {
            $customer=  Db::getInstance()->getRow('
    		SELECT *
    		FROM `'._DB_PREFIX_.'customer`
    		WHERE `email` = \''.pSQL($email).'\'
    		AND `deleted` = 0
    		'.($ignore_guest ? ' AND `is_guest` = 0' : ''));
            if($passwd && $customer && isset($customer['passwd_old_wp']) && $customer['passwd_old_wp'] && $stored_hash=Configuration::get('ETS_NEW_COOKIE_KEY'))
            {
                return $this->getByEmail2($email,$passwd,$ignore_guest,$stored_hash);
            }
            return false;
        }
        $this->id = $result['id_customer'];
        foreach ($result as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }
    public function getByEmail2($email, $passwd = null, $ignore_guest = true,$stored_hash='')
    {
        if (!Validate::isEmail($email) || ($passwd && !Validate::isPasswd($passwd))) {
            die(Tools::displayError());
        }
        $sql = 'select c.`passwd_old_wp` from '._DB_PREFIX_.'customer c where c.email="'.pSQL($email).'" AND `deleted` = 0 ';
        $passwordHash = Db::getInstance()->getValue($sql);
        $shouldCheckPassword = !is_null($passwd);
        if ($shouldCheckPassword && !$this->checkHash($passwd, $passwordHash,$stored_hash)) {
            return false;
        }
        $sql='SELECT * FROM '._DB_PREFIX_.'customer c where c.`email` = \''.pSQL($email).'\' '.($ignore_guest ? ' AND c.`is_guest` = 0':'').' AND c.deleted=0';
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
                    unset($option);
                    unset($staticSalt);
                    return password_hash($passwd, PASSWORD_BCRYPT);
                },
                'verify' => function ($passwd, $hash, $staticSalt) {
                    unset($staticSalt);
                    return password_verify($passwd, $hash);
                },
            ),
            'md5' => array(
                'option' => array(),
                'hash' => function ($passwd, $staticSalt, $option) {
                    unset($option);
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