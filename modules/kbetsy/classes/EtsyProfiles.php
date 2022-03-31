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

//Class and its methods to handle
class EtsyProfiles extends ObjectModel
{
    public $id;
    public $profile_title;
    public $etsy_category_code;
    public $store_categories;
    public $id_etsy_shipping_templates;
    public $is_customizable;
    public $who_made;
    public $when_made;
    public $is_supply;
    public $recipient;
    public $occassion;
    /*Start- MK made changes on 22-11-2017 to add field in model*/
    public $customize_product_title;
    public $enable_max_qty;
    public $should_auto_renew;
    public $max_qty;
    public $enable_min_qty;
    public $min_qty;
    public $property;
    /*End- MK made changes on 22-11-2017 to add field in model*/
    public $active;
    public $date_added;
    public $date_updated;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'etsy_profiles',
        'primary' => 'id_etsy_profiles',
    );
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
    //Function definition to get profile details
    public static function getProfileDetails($id_etsy_profiles = '', $fields_list = '*')
    {
        if (!empty($id_etsy_profiles)) {
            $getDetailsSQL = "SELECT " . $fields_list . " FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_profiles = '" . (int)$id_etsy_profiles . "'";
        } else {
            $getDetailsSQL = "SELECT " . $fields_list . " FROM " . _DB_PREFIX_ . "etsy_profiles";
        }
        
        $getProfileDetails = Db::getInstance()->executeS($getDetailsSQL, true, false);
            
        return $getProfileDetails;
    }
}
