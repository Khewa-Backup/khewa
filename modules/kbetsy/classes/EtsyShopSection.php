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
class EtsyShopSection extends ObjectModel
{

    public $id;
    public $shop_section_title;
    public $shop_section_date_added;
    public $shop_section_date_update;
    /*
     * Updated 'fields' definition to include all table columns for PrestaShop 9.0 compatibility
     * 27-12-2024
     */
    public static $definition = array(
        'table' => 'etsy_shop_section',
        'primary' => 'id_etsy_shop_section',
        'fields' => array(
            'shop_section_title' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 25, 'required' => true),
            'delete_flag' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'renew_flag' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'shop_section_date_added' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'shop_section_date_update' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'shop_section_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 20, 'required' => true),
        )
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public static function getShopSectionDetails($id_etsy_shop_section = '')
    {
        if (!empty($id_etsy_shop_section)) {
            $getDetailsSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE id_etsy_shop_section = '" . (int) $id_etsy_shop_section . "'";
            $getShopSectionDetails = Db::getInstance()->getRow($getDetailsSQL, true, false);
        } else {
            $getDetailsSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_shop_section";
            $getShopSectionDetails = Db::getInstance()->executeS($getDetailsSQL, true, false);
        }

        return $getShopSectionDetails;
    }

    public static function getTotalShopSections()
    {
        $query = "SELECT count(*) FROM " . _DB_PREFIX_ . "etsy_shop_section WHERE delete_flag = '0'";
        $result = Db::getInstance()->getValue($query, true, false);
        return $result;
    }
}
