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

class AdminByProdEwRelatedProductsProController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->addJquery();
    }

    public function ajaxProcessFindAttributeGroupValues()
    {
        $attributes_value = RelatedProductsByProd::getAttributeGroupValues(
            $this->context->language->id,
            $this->context->shop->id,
            Tools::getValue('id_attribute_group')
        );

        $id_relatedproducts = Tools::getValue('id_relatedproducts');
        if ($id_relatedproducts > 0) {
            $related_product = RelatedProductsByProd::getRelatedProductBlockById($id_relatedproducts);
        }

        if (sizeof($attributes_value) > 0) {
            $results = '';

            foreach ($attributes_value as $attribute_value) {
                if (isset($related_product) &&
                    $related_product['id_attribute_value'] == $attribute_value['id_attribute']) {
                    $results .= '<option value="' . $attribute_value['id_attribute'] . '" selected="selected">' .
                        $attribute_value['name'] . '</option>';
                } else {
                    $results .= '<option value="' . $attribute_value['id_attribute'] . '">' .
                        $attribute_value['name'] . '</option>';
                }
            }

            echo $results;
        }
    }

    public function ajaxProcessFindFeatureValues()
    {
        $features_value = FeatureValue::getFeatureValuesWithLang(
            $this->context->language->id,
            Tools::getValue('id_feature')
        );

        $id_relatedproducts = Tools::getValue('id_relatedproducts');
        if ($id_relatedproducts > 0) {
            $related_product = RelatedProductsByProd::getRelatedProductBlockById($id_relatedproducts);
        }

        if (sizeof($features_value) > 0) {
            $results = '';

            foreach ($features_value as $feature_value) {
                if (isset($related_product) &&
                    $related_product['id_feature_value'] == $feature_value['id_feature_value']) {
                    $results .= '<option value="' . $feature_value['id_feature_value'] . '" selected="selected">' .
                        $feature_value['value'] . '</option>';
                } else {
                    $results .= '<option value="' . $feature_value['id_feature_value'] . '">' .
                        $feature_value['value'] . '</option>';
                }
            }
            echo $results;
        }
    }
}
