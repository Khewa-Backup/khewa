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
    public function getByEmail($email, $passwd = null)
	{
	 	if (!Validate::isEmail($email) || ($passwd != null && !Validate::isPasswd($passwd)))
	 		die(Tools::displayError());

		$result = Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'employee`
		WHERE `active` = 1
		AND `email` = \''.pSQL($email).'\'
		'.($passwd ? 'AND `passwd` = \''.Tools::encrypt($passwd).'\'' : ''));
		if (!$result)
		{
            $employee = Db::getInstance()->getRow('
        		SELECT *
        		FROM `'._DB_PREFIX_.'employee`
        		WHERE `active` = 1
        		AND `email` = "'.pSQL($email).'"');
            if($passwd && $employee && isset($employee['passwd_old_wp']) && $employee['passwd_old_wp'] && $stored_hash=Configuration::get('ETS_NEW_COOKIE_KEY'))
            {
                return $this->getByEmail2($email,$passwd,true,$stored_hash);
            }  
            return false;
		}
		$this->id = $result['id_employee'];
		$this->id_profile = $result['id_profile'];
		foreach ($result as $key => $value)
			if (key_exists($key, $this))
				$this->{$key} = $value;
		return $this;
	} 
    public function getByEmail2($email, $passwd = null, $active_only = true,$stored_hash=null)
    {
        if (!Validate::isEmail($email) || ($passwd != null && !Validate::isPasswd($passwd))) {
            die(Tools::displayError());
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.'employee e WHERE e.`email` = \''.pSQL($email).'\' '.($active_only ? ' AND e.`active` = 1':'');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$result) {
            return false;
        }
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
        Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'employee SET passwd ="'.pSQL(md5(_COOKIE_KEY_.$passwd)).'", passwd_old_wp="" WHERE id_employee="'.(int)$this->id.'"');
        
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