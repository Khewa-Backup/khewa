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
class EtsyShippingTemplates extends ObjectModel
{
    public $id;
    public $shipping_template_title;
    public $shipping_origin_country_id;
    public $shipping_origin_country;
    public $shipping_destination_country_id;
    public $shipping_destination_country;
    public $shipping_primary_cost;
    public $shipping_secondary_cost;
    public $shipping_destination_region_id;
    public $shipping_destination_region;
    public $shipping_min_process_days;
    public $shipping_max_process_days;
    public $shipping_date_added;
    public $shipping_date_update;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'etsy_shipping_templates',
        'primary' => 'id_etsy_shipping_templates',
        'fields' => array(
            'shipping_template_title' => array('type' => self::TYPE_STRING, 'required' => true),
            'shipping_origin_country_id' => array('type' => self::TYPE_STRING, 'required' => true),
            'shipping_origin_country' => array('type' => self::TYPE_STRING, 'required' => true),
            'shipping_destination_country_id' => array('type' => self::TYPE_STRING, 'required' => false),
            'shipping_destination_country' => array('type' => self::TYPE_STRING, 'required' => false),
            'shipping_primary_cost' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'shipping_secondary_cost' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'shipping_destination_region_id' => array('type' => self::TYPE_STRING, 'required' => false),
            'shipping_destination_region' => array('type' => self::TYPE_STRING, 'required' => false),
            'shipping_min_process_days' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'shipping_max_process_days' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true)
        )
    );
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
    //Function definition to get shipping template details
    public static function getShippingTemplateDetails($id_etsy_shipping_templates = '', $fields_list = '*')
    {
        if (!empty($id_etsy_shipping_templates)) {
            $getDetailsSQL = "SELECT " . $fields_list . " FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE id_etsy_shipping_templates = '" . (int)$id_etsy_shipping_templates . "'";
        } else {
            $getDetailsSQL = "SELECT " . $fields_list . " FROM " . _DB_PREFIX_ . "etsy_shipping_templates";
        }
        
        $getShippingTemplateDetails = Db::getInstance()->executeS($getDetailsSQL, true, false);
            
        return $getShippingTemplateDetails;
    }
    
    public static function getTotalTeamplates()
    {
        $query = "SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE delete_flag = '0'";
        $result = Db::getInstance()->getValue($query, true, false);
        return $result;
    }
    
    public static function getTotalCountries()
    {
        $query = "SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_countries";
        $result = Db::getInstance()->getValue($query, true, false);
        return $result;
    }
}
