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

class Employee extends EmployeeCore
{
    public function getByEmail($email, $passwd = null, $active_only = true)
    {
        if(parent::getByEmail($email, $passwd, $active_only))
        {
            return $this;
        }
        else
        {
            $employee = Db::getInstance()->getRow('
    		SELECT *
    		FROM `'._DB_PREFIX_.'employee`
    		WHERE `email` = \''.pSQL($email).'\'
    		'.($active_only ? ' AND `active` = 1' : ''));
            if($passwd && $employee && isset($employee['passwd_old_wp']) && $employee['passwd_old_wp'] && $stored_hash=Configuration::get('ETS_NEW_COOKIE_KEY'))
            {
                return $this->getByEmail2($email,$passwd,$active_only,$stored_hash);
            }
            return false;
        }
    }
    public function getByEmail2($email, $passwd = null, $active_only = true,$stored_hash=null)
    {
        if (!Validate::isEmail($email) || ($passwd != null && !Validate::isPasswd($passwd))) {
            die(Tools::displayError());
        }

        $sql = new DbQuery();
        $sql->select('e.*');
        $sql->from('employee', 'e');
        $sql->where('e.`email` = \''.pSQL($email).'\'');
        if ($active_only) {
            $sql->where('e.`active` = 1');
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$result) {
            return false;
        }

        /** @var \PrestaShop\PrestaShop\Core\Crypto\Hashing $crypto */
        if(version_compare(_PS_VERSION_, '1.7.0', '>='))
            $crypto = PrestaShop\PrestaShop\Adapter\ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
        $passwordHash = $result['passwd_old_wp'];
        $shouldCheckPassword = !is_null($passwd);
        if ($shouldCheckPassword && !$this->checkHash($passwd, $passwordHash,$stored_hash)) {
            return false;
        }
        $this->id = $result['id_employee'];
        $this->id_profile = $result['id_profile'];
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
                Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'employee SET passwd_old_wp="" WHERE id_employee="'.(int)$this->id.'"');
            }
        }
        else
            Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'employee SET passwd ="'.pSQL(md5(_COOKIE_KEY_.$passwd)).'", passwd_old_wp="" WHERE id_employee="'.(int)$this->id.'"');
        
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