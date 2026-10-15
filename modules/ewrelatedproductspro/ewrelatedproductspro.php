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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once _PS_MODULE_DIR_.'ewrelatedproductspro/classes/relatedproductsbyprod.php';

class EwRelatedProductsPro extends Module implements WidgetInterface
{
    protected $config_form = false;
    protected $type_visualization = 1;
    protected $show_title = 1;
    protected $lazyload_images = 1;
    protected $custom_title = '';

    public function __construct()
    {
        $this->name = 'ewrelatedproductspro';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Elina Webs';
        $this->need_instance = 1;
        $this->module_key = '6c1719cda6a484d0538326603e85fd3c';
        $this->controllers = array(
            'adminByProd' => 'AdminByProdEwRelatedProductsPro'
        );

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName =
            $this->l('Related Products Pro');
        $this->description =
            $this->l('Module to generate blocks and sliders of related products by attributes, features, name, reference... You choose whether to configure it at the product level or at the category level.');

        $this->confirmUninstall = $this->l(
            'Are you sure to uninstall the module?'
        );

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        if (parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayFooterProduct') &&
            $this->registerHook('displayHeader')) {
            $shops = Shop::getContextListShopID();
            $shop_groups_list = array();

            /* Setup each shop */
            foreach ($shops as $shop_id) {
                $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                /* Sets up configuration */
                $res = Configuration::updateValue(
                    'ewrelatedproducts_type_visualization',
                    $this->type_visualization,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_show_title',
                    $this->show_title,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_lazyload_images',
                    $this->lazyload_images,
                    false,
                    $shop_group_id,
                    $shop_id
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_custom_title',
                    $this->custom_title,
                    false,
                    $shop_group_id,
                    $shop_id
                );
            }

            /* Sets up Shop Group configuration */
            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    $res &= Configuration::updateValue(
                        'ewrelatedproducts_type_visualization',
                        $this->type_visualization,
                        false,
                        $shop_group_id
                    );
                    $res &= Configuration::updateValue(
                        'ewrelatedproducts_show_title',
                        $this->show_title,
                        false,
                        $shop_group_id
                    );
                    $res &= Configuration::updateValue(
                        'ewrelatedproducts_lazyload_images',
                        $this->lazyload_images,
                        false,
                        $shop_group_id
                    );
                    $res &= Configuration::updateValue(
                        'ewrelatedproducts_custom_title',
                        $this->custom_title,
                        false,
                        $shop_group_id
                    );
                }
            }

            /* Sets up Global configuration */
            $res &= Configuration::updateValue('ewrelatedproducts_type_visualization', $this->type_visualization);
            $res &= Configuration::updateValue('ewrelatedproducts_show_title', $this->show_title);
            $res &= Configuration::updateValue('ewrelatedproducts_lazyload_images', $this->lazyload_images);
            $res &= Configuration::updateValue('ewrelatedproducts_custom_title', $this->custom_title);

            return (bool)$res;
        }

        return false;
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        if (parent::uninstall()) {
            $res = Configuration::deleteByName('ewrelatedproducts_type_visualization');
            $res &= Configuration::deleteByName('ewrelatedproducts_show_title');
            $res &= Configuration::deleteByName('ewrelatedproducts_lazyload_images');
            $res &= Configuration::deleteByName('ewrelatedproducts_custom_title');

            return (bool)$res;
        }

        return false;
    }

    public function renderWidget($hookName, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch($this->templateFile);
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $widgetVariables = array(
            'search_controller_url' => $this->context->link->getPageLink(
                'search',
                null,
                null,
                null,
                false,
                null,
                true
            ),
        );

        if (!array_key_exists('search_string', $this->context->smarty->getTemplateVars())) {
            $widgetVariables['search_string'] = '';
        }

        return $widgetVariables;
    }

    public function getContent()
    {
        $output = '';
        $this->context->smarty->assign('module_dir', $this->_path);

        if ((bool)Tools::isSubmit('addrelatedproduct') ||
            (bool)Tools::isSubmit('updateewrelatedproductsbyprod')) {
            $output .= $this->renderFormByProducts();
        } elseif ((bool)Tools::isSubmit('deleteewrelatedproductsbyprod')) {
            $id_relatedproductsbyprod = (int)Tools::getValue('id_relatedproductsbyprod');

            $res = true;
            $shops = Shop::getContextListShopID();
            foreach ($shops as $id_shop) {
                $res &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop` 
                    WHERE `id_relatedproductsbyprod` = '.$id_relatedproductsbyprod.' AND `id_shop` = '.(int)$id_shop);
            }

            if (!$res) {
                $this->displayError($this->l(
                    'Error deleting related product block'
                ));
            } else {
                $sql = 'SELECT id_shop FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop` 
                    WHERE id_relatedproductsbyprod = '.$id_relatedproductsbyprod;

                if (!Db::getInstance()->getValue($sql)) {
                    $related_product_block = new RelatedProductsByProd($id_relatedproductsbyprod);
                    $related_product_block->delete();
                }

                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&relatedproductsbyproddeleted=true');
            }
        } elseif ((bool)Tools::isSubmit('submitRelatedproductsByProd')) {
            $output .= $this->postProcess();

            if (count($this->_errors)) {
                foreach ($this->_errors as $err) {
                    $output .= $err;
                }
                $output .= $this->renderFormByProducts();
            } else {
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&relatedproductsbyprodadded=true');
            }
        } elseif ((bool)Tools::isSubmit('statusewrelatedproductsbyprod')) {
            $id_relatedproductsbyprod = Tools::getValue('id_relatedproductsbyprod');
            $relatedproductsbyprod = new RelatedProductsByProd($id_relatedproductsbyprod);
            $value = $relatedproductsbyprod->active;
            $valueChange = $value ? 0 : 1;
            $relatedproductsbyprod->updateActive($id_relatedproductsbyprod, $valueChange);

            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        } elseif ((bool)Tools::isSubmit('submitRelatedproductsSettings')) {
            $output .= $this->postProcessSettings();

            if (count($this->_errors)) {
                foreach ($this->_errors as $err) {
                    $output .= $err;
                }
                $output .= $this->renderListByProducts();
                $output .= $this->renderFormSettings();
            }
        } else {
            if (Tools::getValue('relatedproductsbyprodadded')) {
                $output .= $this->displayConfirmation($this->l(
                    'Related products block by product add successfully.'
                ));
            } elseif (Tools::getValue('relatedproductssettingsadded')) {
                $output .= $this->displayConfirmation($this->l(
                    'Related products block settings save successfully.'
                ));
            } elseif (Tools::getValue('relatedproductsbyproddeleted')) {
                $output .= $this->displayConfirmation($this->l(
                    'Related products block delete successfully.'
                ));
            }

            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
            $output .= $this->renderListByProducts();
            $output .= $this->renderFormSettings();
            $output .= $this->context->smarty->fetch(
                $this->local_path.'views/templates/admin/configure_close.tpl'
            );
        }

        return $output;
    }

    protected function renderListByProducts()
    {

        $related_products = new RelatedProductsByProd();
        $id_shop = Shop::getContextListShopID();
        $relatedproducts_byproduct = $related_products->getRelatedProductsByProduct($id_shop);

        $fields_list = array(
            'id_relatedproductsbyprod' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
            ),
            'id_product' => array(
                'title' => $this->l('ID product'),
                'type' => 'text',
            ),
            'id_feature' => array(
                'title' => $this->l('Feature'),
                'type' => 'text',
            ),
            'id_feature_value' => array(
                'title' => $this->l('Feature Value'),
                'type' => 'text',
            ),
            'id_attribute' => array(
                'title' => $this->l('Attribute'),
                'type' => 'text',
            ),
            'id_attribute_value' => array(
                'title' => $this->l('Attribute Value'),
                'type' => 'text',
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'type' => 'text',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'type' => 'bool',
                'align' => 'center',
                'active' => 'status',
            )
        );

        $helper = new HelperList();
        $helper->listTotal = count($relatedproducts_byproduct);
        $helper->simple_header = false;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_relatedproductsbyprod';
        $helper->table = 'ewrelatedproductsbyprod';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->title = $this->l(
            'Related products by product'
        );
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->toolbar_btn['new'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&module_name='.$this->name.'&addrelatedproduct=1',
            'desc' => $this->l(
                'Add new related product'
            ),
        );

        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($relatedproducts_byproduct, $fields_list);
    }

    protected function renderFormByProducts()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRelatedproductsByProd';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormByProductsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormByProducts()));
    }

    protected function renderFormSettings()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRelatedproductsSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormSettingsValues(),
        );

        return $helper->generateForm(array($this->getConfigFormSettings()));
    }

    protected function getConfigFormByProducts()
    {
        $link = new Link();
        Media::addJsDefL('admin_related_products_url', $link->getAdminLink($this->controllers['adminByProd']));
        Media::addJsDefL('search_controller_url', $this->context->link->getPageLink(
            'search',
            null,
            null,
            null,
            false,
            null,
            true
        ));

        $features = Feature::getFeatures($this->context->language->id, true);
        $attributes = AttributeGroup::getAttributesGroups($this->context->language->id);

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l(
                        'Add new related products by product'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_relatedproduct'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l(
                            'Product ID'
                        ),
                        'name' => 'id_product',
                        'required' => true,
                        'hint'  => $this->l(
                            'Start filling the product name and you can select it to get your ID automatically'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l(
                            'Type related products'
                        ),
                        'name' => 'type_related_products',
                        'default_value' => (int)$this->context->language->id,
                        'options' => array(
                            'query' => array(
                                array('id' => 0, 'name' => $this->l(
                                    'Select an option...'
                                )),
                                array('id' => 1, 'name' => $this->l(
                                    'By feature'
                                )),
                                array('id' => 2, 'name' => $this->l(
                                    'By attribute'
                                )),
                                array('id' => 3, 'name' => $this->l(
                                    'By reference'
                                ))
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'class' => 'type_related_products',
                        'label' => $this->l('Feature'),
                        'desc' => '',
                        'name' => 'feature',
                        'default_value' => (int)$this->context->language->id,
                        'options' => array(
                            'query' => $features,
                            'id' => 'id_feature',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'class' => 'type_related_products',
                        'label' => $this->l(
                            'Feature Values'
                        ),
                        'default_value' => (int)$this->context->language->id,
                        'name' => 'feature_value',
                        'options' => array(
                            'query' => array(
                                array('id_feature_value' => 0, 'name' => '-')
                            ),
                            'id' => 'id_feature_value',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'class' => 'type_related_products',
                        'label' => $this->l('Attribute'),
                        'name' => 'attribute',
                        'default_value' => (int)$this->context->language->id,
                        'options' => array(
                            'query' => $attributes,
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'class' => 'type_related_products',
                        'label' => $this->l(
                            'Attribute Values'
                        ),
                        'default_value' => (int)$this->context->language->id,
                        'name' => 'attribute_value',
                        'options' => array(
                            'query' => array(
                                array('id_attribute' => 0, 'name' => '-')
                            ),
                            'id' => 'id_attribute',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'text',
                        'class' => 'type_related_products',
                        'label' => $this->l(
                            'Product Reference'
                        ),
                        'name' => 'reference',
                        'hint'  => $this->l(
                            'Enter the begging of the reference to find related products by reference'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l(
                                    'Enabled'
                                )
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l(
                                    'Disabled'
                                )
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                ),
            ),
        );

        if (Shop::isFeatureActive()) {
            $fields_form['form']['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso_module'
            );
        }

        return $fields_form;
    }

    protected function getConfigFormByProductsValues()
    {
        if ((bool)Tools::isSubmit('updateewrelatedproductsbyprod') &&
            RelatedProductsByProd::isRelatedProductsByProductExists((int)Tools::getValue('id_relatedproductsbyprod'))) {
            $relatedproductsbyprod = new RelatedProductsByProd(Tools::getValue('id_relatedproductsbyprod'));
            $product = new Product($relatedproductsbyprod->id_product);

            $id_relatedproduct = (int)Tools::getValue('id_relatedproductsbyprod');
            $id_product =
                (int)$relatedproductsbyprod->id_product . ' (# '.$product->name[$this->context->language->id].' #)';

            if ($relatedproductsbyprod->id_feature > 0) {
                $type_related_products = 1;
            } elseif ($relatedproductsbyprod->id_attribute > 0) {
                $type_related_products = 2;
            } else {
                $type_related_products = 3;
            }
            $feature = (int)$relatedproductsbyprod->id_feature;
            $feature_value = (int)$relatedproductsbyprod->id_feature_value;
            $attribute = (int)$relatedproductsbyprod->id_attribute;
            $attribute_value = (int)$relatedproductsbyprod->id_attribute_value;
            $reference = pSQL($relatedproductsbyprod->reference);
            $active = (int)$relatedproductsbyprod->active;
        } else {
            if (Tools::getValue('id_relatedproduct') && Tools::getValue('id_relatedproduct') > 0) {
                $id_relatedproduct = Tools::getValue('id_relatedproduct');
            } else {
                $id_relatedproduct = 0;
            }

            if (Tools::getValue('id_product') && Tools::getValue('id_product') > 0) {
                $id_product = Tools::getValue('id_product');
                $product = new Product($id_product);
                $id_product .= ' ('.$product->name[$this->context->language->id].')';
            } else {
                $id_product = '';
            }

            if (Tools::getValue('type_related_products') && Tools::getValue('type_related_products') > 0) {
                $type_related_products = Tools::getValue('type_related_products');
            } else {
                $type_related_products = 0;
            }

            if (Tools::getValue('feature') && Tools::getValue('feature') > 0) {
                $feature = Tools::getValue('feature');
            } else {
                $feature = 0;
            }

            if (Tools::getValue('feature_value') && Tools::getValue('feature_value') > 0) {
                $feature_value = Tools::getValue('feature_value');
            } else {
                $feature_value = '';
            }

            if (Tools::getValue('attribute') && Tools::getValue('attribute') > 0) {
                $attribute = Tools::getValue('attribute');
            } else {
                $attribute = 0;
            }

            if (Tools::getValue('attribute_value') && Tools::getValue('attribute_value') > 0) {
                $attribute_value = Tools::getValue('attribute_value');
            } else {
                $attribute_value = '';
            }

            if (Tools::getValue('reference') && Tools::getValue('reference') > 0) {
                $reference = Tools::getValue('reference');
            } else {
                $reference = '';
            }

            if (Tools::getValue('active') != '') {
                $active = Tools::getValue('active');
            } else {
                $active = 0;
            }
        }

        return array(
            'id_relatedproduct' => $id_relatedproduct,
            'id_product' => $id_product,
            'type_related_products' => $type_related_products,
            'feature' => $feature,
            'feature_value' => $feature_value,
            'attribute' => $attribute,
            'attribute_value' => $attribute_value,
            'reference' => $reference,
            'active' => $active
        );
    }

    protected function getConfigFormSettings()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l(
                        'Settings related products blocks'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l(
                            'Type of visualization'
                        ),
                        'name' => 'ewrelatedproducts_type_visualization',
                        'hint'  => $this->l(
                            'The way to show the products in your store'
                        ),
                        'values' => array(
                            array(
                                'id' => 'slider',
                                'value' => 1,
                                'label' => $this->l(
                                    'Slider'
                                )
                            ),
                            array(
                                'id' => 'grid',
                                'value' => 0,
                                'label' => $this->l(
                                    'Grid'
                                )
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(
                            'Show title'
                        ),
                        'name' => 'ewrelatedproducts_show_title',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'ewrelatedproducts_show_title_on',
                                'value' => 1,
                                'label' => $this->l(
                                    'Yes'
                                )
                            ),
                            array(
                                'id' => 'ewrelatedproducts_show_title_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l(
                            'Load images with lazyload'
                        ),
                        'name' => 'ewrelatedproducts_lazyload_images',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'ewrelatedproducts_lazyload_images_on',
                                'value' => 1,
                                'label' => $this->l(
                                    'Yes'
                                )
                            ),
                            array(
                                'id' => 'ewrelatedproducts_lazyload_images_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l(
                            'Custom title related products blocks'
                        ),
                        'name' => 'ewrelatedproducts_custom_title',
                        'hint'  => $this->l(
                            'Leave empty if you want to show the default title'
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        return $fields_form;
    }

    protected function getConfigFormSettingsValues()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = Shop::getContextShopID();

        return array(
            'ewrelatedproducts_type_visualization' => Tools::getValue(
                'ewrelatedproducts_type_visualization',
                Configuration::get('ewrelatedproducts_type_visualization', null, $id_shop_group, $id_shop)
            ),
            'ewrelatedproducts_show_title' => Tools::getValue(
                'ewrelatedproducts_show_title',
                Configuration::get('ewrelatedproducts_show_title', null, $id_shop_group, $id_shop)
            ),
            'ewrelatedproducts_lazyload_images' => Tools::getValue(
                'ewrelatedproducts_lazyload_images',
                Configuration::get('ewrelatedproducts_lazyload_images', null, $id_shop_group, $id_shop)
            ),
            'ewrelatedproducts_custom_title' => Tools::getValue(
                'ewrelatedproducts_custom_title',
                Configuration::get('ewrelatedproducts_custom_title', null, $id_shop_group, $id_shop)
            ),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('id_relatedproduct') > 0) {
            $relatedproductsbyprod = new RelatedProductsByProd((int)Tools::getValue('id_relatedproduct'));
            if (!Validate::isLoadedObject($relatedproductsbyprod)) {
                $this->_errors[] = $this->displayError($this->l(
                    'Invalid id_relatedproduct'
                ));
            }
        } else {
            $relatedproductsbyprod = new RelatedProductsByProd();
        }

        if (Shop::isFeatureActive()) {
            $shop_ids = Tools::getValue('checkBoxShopAsso_module');
            if (!$shop_ids) {
                $this->_errors[] = $this->displayError($this->l(
                    'You have to select at least one shop.'
                ));
                return false;
            }
        }

        $type_related_products = Tools::getValue('type_related_products');

        $id_product = explode('(#', Tools::getValue('id_product'));
        $id_product = (int)trim($id_product[0]);
        $relatedproductsbyprod->id_product = (int)$id_product;

        $relatedproductsbyprod->active = (int)Tools::getValue('active');

        switch ($type_related_products) {
            case 1:
                if (!(int)Tools::getValue('feature') || (int)Tools::getValue('feature') <= 0 ||
                    !(int)Tools::getValue('feature_value') || (int)Tools::getValue('feature_value') <= 0) {
                    $this->_errors[] = $this->displayError($this->l(
                        'The fields Feature and Feature Value is required for this type of related products block.'
                    ));
                    return false;
                } else {
                    $relatedproductsbyprod->id_feature = (int)Tools::getValue('feature');
                    $relatedproductsbyprod->id_feature_value = (int)Tools::getValue('feature_value');
                    $relatedproductsbyprod->id_attribute = 0;
                    $relatedproductsbyprod->id_attribute_value = 0;
                    $relatedproductsbyprod->reference = "";
                }
                break;
            case 2:
                if (!(int)Tools::getValue('attribute') || (int)Tools::getValue('attribute') <= 0 ||
                    !(int)Tools::getValue('attribute_value')
                    || (int)Tools::getValue('attribute_value') <= 0) {
                    $this->_errors[] = $this->displayError($this->l(
                        'The fields Attribute and Attribute Value is required for this type of related products block.'
                    ));
                    return false;
                } else {
                    $relatedproductsbyprod->id_feature = 0;
                    $relatedproductsbyprod->id_feature_value = 0;
                    $relatedproductsbyprod->id_attribute = (int)Tools::getValue('attribute');
                    $relatedproductsbyprod->id_attribute_value = (int)Tools::getValue('attribute_value');
                    $relatedproductsbyprod->reference = "";
                }
                break;
            case 3:
                if (!pSQL(Tools::getValue('reference')) || pSQL(Tools::getValue('reference')) == '') {
                    $this->_errors[] = $this->displayError($this->l(
                        'The fields Reference is required for this type of related products block.'
                    ));
                    return false;
                } else {
                    $relatedproductsbyprod->id_feature = 0;
                    $relatedproductsbyprod->id_feature_value = 0;
                    $relatedproductsbyprod->id_attribute = 0;
                    $relatedproductsbyprod->id_attribute_value = 0;
                    $relatedproductsbyprod->reference = pSQL(Tools::getValue('reference'));
                }
                break;
            default:
                $this->_errors[] = $this->displayError($this->l(
                    'You need to select one type of related products block.'
                ));
                return false;
        }

        if ((int)Tools::getValue('id_relatedproduct') > 0) {
            $saved = $relatedproductsbyprod->update();
        } else {
            $saved = $relatedproductsbyprod->add();
        }

        if ($saved) {
            $sql_find = 'SELECT `id_relatedproductsbyprod` FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop` 
                WHERE `id_relatedproductsbyprod` = '.(int)$relatedproductsbyprod->id;
            if ($result = Db::getInstance()->executeS($sql_find)) {
                foreach ($result as $related_product_shop) {
                    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop` 
                        WHERE `id_relatedproductsbyprod` = '.(int)$related_product_shop['id_relatedproductsbyprod']);
                }
            }

            $shops = Tools::getValue('checkBoxShopAsso_module');
            $saved = true;

            if ($shops && count($shops) > 0) {
                foreach ($shops as $shop) {
                    $saved = Db::getInstance()->execute(
                        'INSERT INTO `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop`
                    (`id_relatedproductsbyprod`, `id_shop` )
                    VALUES('.(int)$relatedproductsbyprod->id.','.(int)$shop.')'
                    );

                    if (!$saved) {
                        $saved = false;
                    }
                }
            } else {
                $saved = Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'ewrelatedproductsbyprod_shop`
                    (`id_relatedproductsbyprod`, `id_shop` )
                    VALUES('.(int)$relatedproductsbyprod->id.', 1)'
                );

                if (!$saved) {
                    $saved = false;
                }
            }
            
            if (!$saved) {
                $this->_errors[] = $this->displayError($this->l(
                    'An error occurred while attempting to save related products by product into shop table.'
                ));
            }
        } else {
            $this->_errors[] = $this->displayError($this->l(
                'An error occurred while attempting to save related products by product.'
            ));
        }

        return false;
    }

    protected function postProcessSettings()
    {
        $shop_context = Shop::getContext();
        $res = true;

        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();

        $ewrelatedproducts_custom_title = pSQL(Tools::getValue('ewrelatedproducts_custom_title'));
        $ewrelatedproducts_custom_title = Tools::stripslashes($ewrelatedproducts_custom_title);

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            $res = Configuration::updateValue(
                'ewrelatedproducts_type_visualization',
                (int)Tools::getValue('ewrelatedproducts_type_visualization'),
                false,
                $shop_group_id,
                $shop_id
            );
            $res &= Configuration::updateValue(
                'ewrelatedproducts_show_title',
                (int)Tools::getValue('ewrelatedproducts_show_title'),
                false,
                $shop_group_id,
                $shop_id
            );
            $res &= Configuration::updateValue(
                'ewrelatedproducts_lazyload_images',
                (int)Tools::getValue('ewrelatedproducts_lazyload_images'),
                false,
                $shop_group_id,
                $shop_id
            );
            $res &= Configuration::updateValue(
                'ewrelatedproducts_custom_title',
                $ewrelatedproducts_custom_title,
                false,
                $shop_group_id,
                $shop_id
            );
        }

        /* Update global shop context if needed*/
        switch ($shop_context) {
            case Shop::CONTEXT_ALL:
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_type_visualization',
                    (int)Tools::getValue('ewrelatedproducts_type_visualization')
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_show_title',
                    (int)Tools::getValue('ewrelatedproducts_show_title')
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_lazyload_images',
                    (int)Tools::getValue('ewrelatedproducts_lazyload_images')
                );
                $res &= Configuration::updateValue(
                    'ewrelatedproducts_custom_title',
                    $ewrelatedproducts_custom_title
                );
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_type_visualization',
                            (int)Tools::getValue('ewrelatedproducts_type_visualization'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_show_title',
                            (int)Tools::getValue('ewrelatedproducts_show_title'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_lazyload_images',
                            (int)Tools::getValue('ewrelatedproducts_lazyload_images'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_custom_title',
                            $ewrelatedproducts_custom_title,
                            false,
                            $shop_group_id
                        );
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_type_visualization',
                            (int)Tools::getValue('ewrelatedproducts_type_visualization'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_show_title',
                            (int)Tools::getValue('ewrelatedproducts_show_title'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_lazyload_images',
                            (int)Tools::getValue('ewrelatedproducts_lazyload_images'),
                            false,
                            $shop_group_id
                        );
                        $res &= Configuration::updateValue(
                            'ewrelatedproducts_custom_title',
                            $ewrelatedproducts_custom_title,
                            false,
                            $shop_group_id
                        );
                    }
                }
                break;
        }

        if (!$res) {
            $this->_errors[] = $this->displayError($this->getTranslator()->trans(
                'The settings could not be updated.',
                array(),
                'Modules.Imageslider.Admin'
            ));
            return false;
        } else {
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&relatedproductssettingsadded=true');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();

            $this->context->controller->addJqueryUI('ui.autocomplete');
            $this->context->controller->addJS($this->_path.'views/js/back_relatedproducts.js');
            $this->context->controller->addCSS($this->_path.'views/css/back_relatedproducts.css');
        }
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/front_relatedproducts.js');
        $this->context->controller->addJS($this->_path.'views/js/owl_carousel/owl.carousel.min.js');
        $this->context->controller->addCSS($this->_path.'views/css/front_relatedproducts.css');
        $this->context->controller->addCSS($this->_path.'views/css/owl_carousel/owl.carousel.min.css');
        $this->context->controller->addCSS($this->_path.'views/css/owl_carousel/owl.theme.default.min.css');
    }

    public function hookDisplayFooterProduct()
    {
        if (!method_exists($this->context->controller, 'getProduct')) {
            return;
        }

        $product = $this->context->controller->getProduct();
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        if (!Validate::isLoadedObject($product)) {
            return;
        }

        $relatedproductsbybrod = new RelatedProductsByProd();
        $related_products = $relatedproductsbybrod->getRelatedProductsByIdProduct($product->id, $id_lang, $id_shop);

        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->getTranslator()
        );
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        $final_related_products = array();

        foreach ($related_products as $key => $related_product) {
            if (is_array($related_product)) {
                $array_products = array();
                $type_products = '';
                $type_value_products = '';
                foreach ($related_product as $related_prod) {
                    $type_products = $related_prod['type_related'];
                    $type_value_products = $related_prod['type_value_related'];
                    $array_products[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct($related_prod),
                        $this->context->language
                    );
                }
                $final_related_products[$key]['products'] = $array_products;
                $final_related_products[$key]['type_products'] = $type_products;
                $final_related_products[$key]['type_value_products'] = $type_value_products;
            }
        }

        $this->context->smarty->assign(array(
            'related_product_blocks' => $final_related_products,
            'ewrelatedproducts_type_visualization' => Configuration::get('ewrelatedproducts_type_visualization'),
            'ewrelatedproducts_show_title' => Configuration::get('ewrelatedproducts_show_title'),
            'ewrelatedproducts_lazyload_images' => Configuration::get('ewrelatedproducts_lazyload_images'),
            'ewrelatedproducts_custom_title' => Configuration::get('ewrelatedproducts_custom_title'),

        ));

        return $this->fetch('module:'.$this->name.'/views/templates/hook/relatedproductsbyprod.tpl');
    }
}
