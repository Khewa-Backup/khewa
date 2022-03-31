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

class EtsyProfileCategory extends ObjectModel
{
    public $id_profile_category;
    public $etsy_category_code;
    public $id_etsy_profiles;
    public $prestashop_category;
    public $date_add;
    public $date_upd;
    
    public static $definition = array(
        'table' => 'etsy_category_mapping',
        'primary' => 'id_profile_category',
        'fields' => array(
            'id_profile_category' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_etsy_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'etsy_category_code' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHTML'
            ),
            'prestashop_category' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHTML'
            ),
            
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            
        ),
    );
    
    public function __construct($id_profile_category = null)
    {
        parent::__construct($id_profile_category);
    }
    
    
    public static function getProfileCategory($id_etsy_profiles)
    {
        $data = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'etsy_category_mapping WHERE id_etsy_profiles='.(int)$id_etsy_profiles);
        return $data;
    }
}
