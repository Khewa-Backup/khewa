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

class EtsyProductListing extends ObjectModel
{

    public $id_etsy_products_list;
    public $id_etsy_profiles;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $active;
    public $date_added;
    public static $definition = array(
        'table' => 'etsy_products_list',
        'primary' => 'id_etsy_products_list',
        'multilang' => false,
        'fields' => array(
            'id_etsy_products_list' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_etsy_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'active' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'date_added' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            )
        ),
    );

    public function __construct($id_etsy_products_list = null)
    {
        parent::__construct($id_etsy_products_list);
    }
}
