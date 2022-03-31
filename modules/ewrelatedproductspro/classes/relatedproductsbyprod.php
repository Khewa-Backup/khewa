<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class RelatedProductsByProd extends ObjectModel
{
    public $id_product;
    public $id_feature;
    public $id_feature_value;
    public $id_attribute;
    public $id_attribute_value;
    public $reference;
    public $active;

    public static $definition = array(
        'table' => 'ewrelatedproductsbyprod',
        'primary' => 'id_relatedproductsbyprod',
        'multishop' => true,
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_feature' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_feature_value' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_attribute_value' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
        )
    );

    public function getRelatedProductsByProduct($id_shop)
    {
        $sql = 'SELECT rp.*
                FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod` rp 
                LEFT JOIN `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop` rps 
                    ON (rp.`id_relatedproductsbyprod` = rps.`id_relatedproductsbyprod`) 
                WHERE rps.`id_shop` IN (' . implode(', ', $id_shop).') 
                GROUP BY rp.`id_relatedproductsbyprod`';

        $related_products_by_product = array();
        $count = 0;

        if ($result = Db::getInstance()->executeS($sql)) {
            foreach ($result as $related_product_block) {
                $product = new Product($related_product_block['id_product']);

                $related_products_by_product[$count]['id_relatedproductsbyprod'] =
                    $related_product_block['id_relatedproductsbyprod'];
                $related_products_by_product[$count]['id_product'] =
                    $related_product_block['id_product'].' ('.
                    $product->name[(int)Context::getContext()->language->id].')';
                $related_products_by_product[$count]['id_feature'] = $related_product_block['id_feature'];
                $related_products_by_product[$count]['id_feature_value'] = $related_product_block['id_feature_value'];
                $related_products_by_product[$count]['id_attribute'] = $related_product_block['id_attribute'];
                $related_products_by_product[$count]['id_attribute_value'] =
                    $related_product_block['id_attribute_value'];
                $related_products_by_product[$count]['reference'] = $related_product_block['reference'];
                $related_products_by_product[$count]['active'] = $related_product_block['active'];

                if ($related_product_block['id_feature'] > 0) {
                    $sql_feature = 'SELECT fl.`name`, fvl.`value` 
                      FROM `'._DB_PREFIX_.'feature_lang` fl, `'._DB_PREFIX_.'feature_value_lang` fvl 
                      WHERE fl.`id_feature` = '.(int)$related_product_block['id_feature'].' AND 
                      fvl.`id_feature_value` = '.(int)$related_product_block['id_feature_value'].' AND 
                      fl.`id_lang` = '.(int)Context::getContext()->language->id.' AND 
                      fvl.`id_lang` = '.(int)Context::getContext()->language->id;

                    if ($result_feature = Db::getInstance()->getRow($sql_feature)) {
                        $related_products_by_product[$count]['id_feature'] .= ' ('.$result_feature['name'].')';
                        $related_products_by_product[$count]['id_feature_value'] .= ' ('.$result_feature['value'].')';
                    }
                } elseif ($related_product_block['id_attribute'] > 0) {
                    $sql_attribute = 'SELECT agl.`name` as attribute_group_name, al.`name` as attribute_name 
                      FROM `'._DB_PREFIX_.'attribute_group_lang` agl, `'._DB_PREFIX_.'attribute_lang` al 
                      WHERE agl.`id_attribute_group` = '.(int)$related_product_block['id_attribute'].' AND 
                      al.`id_attribute` = '.(int)$related_product_block['id_attribute_value'].' AND 
                      agl.`id_lang` = '.(int)Context::getContext()->language->id.' 
                      AND al.`id_lang` = '.(int)Context::getContext()->language->id;

                    if ($result_attribute = Db::getInstance()->getRow($sql_attribute)) {
                        $related_products_by_product[$count]['id_attribute'] .=
                            ' ('.$result_attribute['attribute_group_name'].')';
                        $related_products_by_product[$count]['id_attribute_value'] .=
                            ' ('.$result_attribute['attribute_name'].')';
                    }
                }
                $count++;
            }
        }

        return $related_products_by_product;
    }

    public static function getAttributeGroupValues($id_lang, $id_shop, $id_attribute_group)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        return Db::getInstance()->executeS('
            SELECT a.`id_attribute`, a.`id_attribute_group`, a.`color`, a.`position`, attribute_shop.`id_shop`, 
            al.`id_lang`, al.`name`
            FROM `' . _DB_PREFIX_ . 'attribute` a
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_shop` attribute_shop
                ON (a.`id_attribute` = attribute_shop.`id_attribute` AND 
                attribute_shop.`id_shop` = ' . (int) $id_shop . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
            WHERE a.`id_attribute_group` = ' . (int) $id_attribute_group . '
            ORDER BY `position` ASC
        ');
    }

    public static function isRelatedProductsByProductExists($id_relatedproduct)
    {
        $sql = 'SELECT id_relatedproductsbyprod 
          FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod` 
          WHERE id_relatedproductsbyprod = '.(int)$id_relatedproduct;

        $result = Db::getInstance()->getValue($sql);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function getRelatedProductBlockById($id_relatedproducts)
    {
        $sql = 'SELECT * 
                    FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod` 
                    WHERE id_relatedproductsbyprod  = '.(int)$id_relatedproducts;

        if ($return = Db::getInstance()->getRow($sql)) {
            return $return;
        } else {
            return false;
        }
    }

    public function getRelatedProductsByIdProduct($id_product, $id_lang, $id_shop)
    {
        $sql_pre = 'SELECT rp.* 
                    FROM `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod` rp 
                    LEFT JOIN `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod_shop` rps 
                        ON (rps.`id_relatedproductsbyprod` = rp.`id_relatedproductsbyprod`)
                    WHERE rp.`id_product` = '.(int)$id_product.' AND rps.`id_shop` = '.(int)$id_shop;

        $related_product_blocks = array();

        if ($result_pre = Db::getInstance()->executeS($sql_pre)) {
            foreach ($result_pre as $related_product_block) {
                $select = '';
                $leftjoin = '';
                $where = '';

                if ($related_product_block['id_feature'] > 0) {
                    $select = ', fl.`name` as type_related, fvl.`value` as type_value_related ';

                    $leftjoin = ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` fp
                    ON (p.`id_product` = fp.`id_product`)';

                    $leftjoin .= ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl
                    ON (fl.`id_feature` = fp.`id_feature`)';

                    $leftjoin .= ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl
                    ON (fvl.`id_feature_value` = fp.`id_feature_value`)';

                    $where = ' AND fp.`id_feature` = ' . (int)$related_product_block['id_feature'] . ' AND
                        fp.`id_feature_value` = ' . (int)$related_product_block['id_feature_value'] . ' AND 
                        fl.`id_lang` = '.(int)$id_lang.' AND fvl.`id_lang` = '.(int)$id_lang;
                } elseif ($related_product_block['id_attribute'] > 0) {
                    $select = ', agl.`name` as type_related, al.`name` as type_value_related ';

                    $leftjoin = ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON (
                        pac.`id_product_attribute` = product_attribute_shop.`id_product_attribute`)';

                    $leftjoin .= ' LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON (
                        a.`id_attribute` = pac.`id_attribute`)';

                    $leftjoin .= ' LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (
                        al.`id_attribute` = a.`id_attribute`)';

                    $leftjoin .= ' LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (
                        agl.`id_attribute_group` = a.`id_attribute_group`)';

                    $where = ' AND a.`id_attribute` = '.(int)$related_product_block['id_attribute_value'].' AND
                        a.`id_attribute_group` = '.(int)$related_product_block['id_attribute'].' AND 
                        al.`id_lang` = '.(int)$id_lang.' AND agl.`id_lang` = '.(int)$id_lang;
                } else {
                    $where = ' AND p.`reference` LIKE "%'.pSQL($related_product_block['reference']).'%"';
                }

                $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, 
                    pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, 
                    pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`,
                    image_shop.`id_image` id_image, il.`legend`, m.`name` as manufacturer_name, 
                    cl.`name` AS category_default, 
                    IFNULL(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
                    DATEDIFF(
                        p.`date_add`,
                        DATE_SUB(
                            "' . date('Y-m-d') . ' 00:00:00",
                            INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ?
                            Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
                        )
                    ) > 0 AS new';

                $sql .= $select;

                $sql .= ' FROM `' . _DB_PREFIX_ . 'product` p 
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
                    ON (p.`id_product` = product_attribute_shop.`id_product` AND 
                    product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int)$id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
                    product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                )
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 
                        AND image_shop.id_shop=' . (int)$id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` 
                    AND il.`id_lang` = ' . (int)$id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
                ' . Product::sqlStock('p', 0);

                $sql .= $leftjoin;

                $sql .= 'WHERE p.`id_product` != '.(int)$id_product.' AND product_shop.`active` = 1 AND 
                    product_shop.`visibility` != \'none\'';

                $sql .= $where;

                if ($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
                    foreach ($result as $k => &$row) {
                        if ($select == '') {
                            $row['type_related'] = 'Reference';
                            $row['type_value_related'] = pSQL($related_product_block['reference']);
                        }

                        if (!Product::checkAccessStatic((int)$row['id_product'], false)) {
                            unset($result[$k]);

                            continue;
                        } else {
                            $row['id_product_attribute'] = Product::getDefaultAttribute((int)$row['id_product']);
                        }
                    }

                    $related_product_blocks[] = $result;
                }
            }
        }
        /* Elina Webs --> If the product does not have any related blocks, we look to see if there are any related
                          products that go by reference and this is part of the current product reference */
        else {
            $product = new Product($id_product);
            $product_reference = $product->reference;

            $sql_pre = 'SELECT rp.* 
                    FROM `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod` rp 
                    LEFT JOIN `' . _DB_PREFIX_ . 'ewrelatedproductsbyprod_shop` rps 
                        ON (rps.`id_relatedproductsbyprod` = rp.`id_relatedproductsbyprod`)
                    WHERE rp.`reference` != "" AND rps.`id_shop` = '.(int)$id_shop;

            if ($result_pre = Db::getInstance()->executeS($sql_pre)) {
                foreach ($result_pre as $related_product_block) {
                    if(strpos($product_reference, $related_product_block['reference']) !== false) {

                        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, 
                            pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, 
                            pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`,
                            image_shop.`id_image` id_image, il.`legend`, m.`name` as manufacturer_name, 
                            cl.`name` AS category_default, 
                            IFNULL(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
                            DATEDIFF(
                                p.`date_add`,
                                DATE_SUB(
                                    "' . date('Y-m-d') . ' 00:00:00",
                                    INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ?
                                        Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
                                )
                            ) > 0 AS new';

                        $sql .= ' FROM `' . _DB_PREFIX_ . 'product` p 
                            ' . Shop::addSqlAssociation('product', 'p') . '
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
                                ON (p.`id_product` = product_attribute_shop.`id_product` AND 
                                product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int)$id_shop . ')
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
                                p.`id_product` = pl.`id_product`
                                AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . '
                            )
                            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
                                product_shop.`id_category_default` = cl.`id_category`
                                AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . '
                            )
                            LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                                ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 
                                    AND image_shop.id_shop=' . (int)$id_shop . ')
                            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` 
                                AND il.`id_lang` = ' . (int)$id_lang . ')
                            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
                            ' . Product::sqlStock('p', 0);


                        $sql .= 'WHERE p.`id_product` != '.(int)$id_product.' AND product_shop.`active` = 1 AND 
                            product_shop.`visibility` != \'none\'';

                        $sql .= ' AND p.`reference` LIKE "%'.pSQL($related_product_block['reference']).'%"';

                        if ($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
                            foreach ($result as $k => &$row) {
                                $row['type_related'] = 'Reference';
                                $row['type_value_related'] = pSQL($related_product_block['reference']);

                                if (!Product::checkAccessStatic((int)$row['id_product'], false)) {
                                    unset($result[$k]);

                                    continue;
                                } else {
                                    $row['id_product_attribute'] = Product::getDefaultAttribute((int)$row['id_product']);
                                }
                            }

                            $related_product_blocks[] = $result;
                        }
                        break;
                    }
                }
            }
        }
        /* End Elina Webs */

        return $related_product_blocks;
    }

    public static function getProductsProperties($id_lang, $query_result)
    {
        $results_array = array();

        if (is_array($query_result)) {
            foreach ($query_result as $row) {
                if ($row2 = Product::getProductProperties($id_lang, $row)) {
                    $results_array[] = $row2;
                }
            }
        }

        return $results_array;
    }

    public function updateActive($id_relatedproductsbyprod, $value)
    {
        Db::getInstance()->update(
            'ewrelatedproductsbyprod',
            array('active' => (int)$value),
            'id_relatedproductsbyprod = '.(int)$id_relatedproductsbyprod
        );
    }
}
