<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

class an_wish extends ObjectModel
{
    /**
     * @var int
     */
    public $id_wishlist;
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $id_shop;

    public $id_customer;
    public $is_guest;
	
    /** @var string Object last modification date */
    public $date_upd;		

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'an_wishlist',
        'primary' => 'id_wishlist',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_customer' => array('type' =>self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false ),
            'is_guest' => array('type' =>self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false ),
            'date_upd' => array('type' => self::TYPE_DATE),
        ),
    );
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if (Shop::isFeatureActive()) {
            $this->id_shop = Context::getContext()->shop->id;
        } else {
            $this->id_shop = 1;
        }
    }



	public static function findWishlistByCustomer($idCustomer, $is_guest = 0){
		
        if (!$idCustomer) {
            return false;
        }

        return Db::getInstance()->getValue('
            SELECT `id_wishlist`
            FROM `' . _DB_PREFIX_ . 'an_wishlist`
            WHERE `id_customer` = ' . (int)$idCustomer . '
            AND `is_guest` = ' . (int)$is_guest . '
            AND `id_shop` = ' . (int) Context::getContext()->shop->id);
	}
	
	
	
	public static function getCustomers($limit = 0, $start = 0){
/*
SELECT * FROM `ps_customer` WHERE 1
`id_customer`, `id_shop_group`, `id_shop`, `id_gender`, `id_default_group`, `id_lang`, `id_risk`, `company`, `siret`, `ape`, `firstname`, `lastname`, `email`, `passwd`, `last_passwd_gen`, `birthday`, `newsletter`, `ip_registration_newsletter`, `newsletter_date_add`, `optin`, `website`, `outstanding_allow_amount`, `show_public_prices`, `max_payment_days`, `secure_key`, `note`, `active`, `is_guest`, `deleted`, `date_add`, `date_upd`, `reset_password_token`, `reset_password_validity`

*/
		$context = Context::getContext();
		
		$sql = '
		SELECT c.id_customer, c.email, c.firstname, c.lastname, anw.`date_upd` as anw_date_upd 	
		FROM `' . _DB_PREFIX_ . 'an_wishlist` anw, `' . _DB_PREFIX_ . 'customer` c
		WHERE anw.`id_customer`=c.`id_customer` AND anw.`id_shop`=' . (int) $context->shop->id . '
		ORDER BY anw_date_upd
		';
	//	echo $sql . '<br>'; die;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		
/* 		echo '<pre>';
		var_dump($result);
		
		die; */
		
        if (!$result) {
            return array();
        } else {
			return $result;
		}		
	}

























}
