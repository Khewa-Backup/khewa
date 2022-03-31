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
class EtsyAttributeMappings extends ObjectModel
{

    public $id_attribute_mapping;
    public $property_id;
    public $id_attribute_group;
    public $date_added;
    public $date_updated;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'etsy_attribute_mapping1',
        'primary' => 'id_attribute_group',
    );

    public function __construct($id = null)
    {
        /* To avoid PS error, Blank entry added into the table */
        $result = self::getAttributeMappingDetails($id);
        if (empty($result)) {
            self::addAttributeMapping(0, $id);
        }
        parent::__construct($id);
    }

    public static function checkAttributeMappingExist($id_attribute_group)
    {
        $result = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'etsy_attribute_mapping1 WHERE  id_attribute_group = ' . (int) $id_attribute_group, false);
        return $result;
    }

    public static function addAttributeMapping($property_id, $id_attribute_group)
    {
        if ($id_attribute_group != "" && $id_attribute_group != 0) {
            $result = Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_attribute_mapping1 (property_id, property_title, id_attribute_group, date_added, date_updated) VALUES (" . (int) $property_id . " , '' ," . (int) $id_attribute_group . " , now(), now())" . false);
            return $result;
        }
    }

    public static function updateAttributeMapping($property_id, $id_attribute_group)
    {
        if ($id_attribute_group != "" && $id_attribute_group != 0) {
            $result = Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_attribute_mapping1 SET property_id = " . (int) $property_id . ", property_title = '',  date_updated = now() WHERE id_attribute_group = " . (int) $id_attribute_group, false);
            return $result;
        } else {
            return false;
        }
    }

    public static function getAttributeMappingDetails($id_attribute_group)
    {
        $selectSQL = "SELECT * FROM " . _DB_PREFIX_ . "etsy_attribute_mapping1 WHERE id_attribute_group = " . (int) $id_attribute_group;
        $getResult = Db::getInstance()->getRow($selectSQL, false);
        return $getResult;
    }
}
