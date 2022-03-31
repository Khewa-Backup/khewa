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
class EtsyShippingTemplatesEntries extends ObjectModel
{

    public $id;
    public $id_etsy_shipping_templates;
    public $shipping_entry_destination_country_id;
    public $shipping_entry_destination_country;
    public $shipping_entry_primary_cost;
    public $shipping_entry_secondary_cost;
    public $shipping_entry_destination_region_id;
    public $shipping_entry_destination_region;
    public $shipping_entry_date_added;
    public $shipping_entry_date_update;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'etsy_shipping_templates_entries',
        'primary' => 'id_etsy_shipping_templates_entries',
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public static function getShippingTemplateEntryDetails($id_etsy_shipping_templates = '')
    {
        if (!empty($id_etsy_shipping_templates)) {
            return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = " . (int) $id_etsy_shipping_templates. " AND delete_flag != '1'");
        } else {
            return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE delete_flag != '1'", true, false);
        }
    }
}
