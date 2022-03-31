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

require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyAttributeMappings.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');

class AdminEtsyAttributeMappingController extends ModuleAdminController
{

    private $mappedAttributeCount = 0;

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyAttributeMapping';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'attribute_group_lang';
        $this->className = 'EtsyAttributeMappings';
        $this->identifier = 'id_attribute_group';

        parent::__construct();
        $this->fields_list = array(
            'name' => array(
                'title' => $this->module->l('Store Attribute Name', 'AdminEtsyAttributeMappingController'),
                'filter_key' => 'a!name'
            ),
            'etsy_property_title' => array(
                'title' => $this->module->l('Etsy Attribute Name', 'AdminEtsyAttributeMappingController')
            ),
            'date_added' => array(
                'title' => $this->module->l('Added On', 'AdminEtsyAttributeMappingController'),
                'type' => 'date',
            ),
            'date_updated' => array(
                'title' => $this->module->l('Updated On', 'AdminEtsyAttributeMappingController'),
                'type' => 'date',
            )
        );
        $this->_select = 'a.name, etsy_property_title, eam.date_added, eam.date_updated';

        /** Join product attribute with attribute table to get avaliable atrributes & then join with etsy attribute mapping to get corrospnding mapped etsy attribute */
        $this->_join = "INNER JOIN " . _DB_PREFIX_ . "attribute attr ON a.id_attribute_group = attr.id_attribute_group "
                . "INNER JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON attr.id_attribute = pac.id_attribute "
                . "INNER JOIN " . _DB_PREFIX_ . "product_attribute pa ON pa.id_product_attribute = pac.id_product_attribute "
                . "LEFT JOIN " . _DB_PREFIX_ . "etsy_attribute_mapping1 eam ON a.id_attribute_group = eam.id_attribute_group "
                . "LEFT JOIN " . _DB_PREFIX_ . "etsy_attributes ea ON ea.attribute_id = eam.property_id";

        $this->_where = " = 1 AND a.id_lang = '" . $this->context->language->id . "'";
        $this->_group = "GROUP BY  a.id_attribute_group";
        $this->module->list_no_link = true;

        /** Query to check if there any attribute which are mapped to products */
        $mappedPrestashopAttribute = Db::getInstance()->getRow("SELECT count(*) as total FROM `" . _DB_PREFIX_ . "product_attribute` pa "
                . "INNER JOIN " . _DB_PREFIX_ . "product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute "
                . "INNER JOIN " . _DB_PREFIX_ . "attribute a ON pac.id_attribute = a.id_attribute "
                . "INNER JOIN " . _DB_PREFIX_ . "attribute_group_lang agl ON agl.id_attribute_group = a.id_attribute_group "
                . "WHERE agl.id_lang = " . (int) $this->context->language->id . " "
                . "GROUP BY agl.id_attribute_group");
        $this->mappedAttributeCount = $mappedPrestashopAttribute['total'];

        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function renderList()
    {
        $this->addRowAction('edit');

        if ($this->mappedAttributeCount > 0) {
            $this->context->smarty->assign("message", $this->module->l('Following PrestaShop attributes needs to be mapped with Etsy attributes to sync product combinations on Etsy.', 'AdminEtsyAttributeMappingController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs . parent::renderList();
        } else {
            $this->context->smarty->assign("message", $this->module->l('You are not using any attributes with products on Prestashop so there is no need of attribute mapping.', 'AdminEtsyAttributeMappingController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs;
        }
    }

    /** Render a form  */
    public function renderForm()
    {
        $etsyAttributes = array();
        $etsyAttributes[] = array('id_option' => '', 'name' => $this->module->l('Select Etsy Attribute', 'AdminEtsyAttributeMappingController'));
        $etsy_attribute_data = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "etsy_attributes` ORDER BY etsy_property_title ASC");
        foreach ($etsy_attribute_data as $etsy_attribute) {
            $etsyAttributes[] = array('id_option' => $etsy_attribute['attribute_id'], 'name' => $etsy_attribute['etsy_property_title']);
        }

        $psAttributes = array();
        $attributeGroups = AttributeGroup::getAttributesGroups($this->context->language->id);
        foreach ($attributeGroups as $value) {
            $psAttributes[$value['id_attribute_group']] = $value['name'];
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_attribute_mapping'))) ? $this->module->l('Update Attribute Mapping', 'AdminEtsyAttributeMappingController') : $this->module->l('Update Attribute Mapping', 'AdminEtsyAttributeMappingController'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Prestashop Attribute', 'AdminEtsyAttributeMappingController'),
                    'name' => 'id_attribute_group_text',
                    'required' => true,
                    'col' => 3,
                    'readonly' => 'readonly'
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->module->l('Prestashop Attribute', 'AdminEtsyAttributeMappingController'),
                    'name' => 'id_attribute_group',
                    'required' => true,
                    'col' => 3,
                    'readonly' => 'readonly'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Etsy Attribute', 'AdminEtsyAttributeMappingController'),
                    'desc' => $this->module->l('Choose an Etsy atribute from the list', 'AdminEtsyAttributeMappingController'),
                    'name' => 'property_id',
                    'required' => true,
                    'col' => 3,
                    'options' => array(
                        'query' => $etsyAttributes,
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                )
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('attribute_group_lang_form')",
                    'title' => $this->module->l('Save', 'AdminEtsyAttributeMappingController'),
                    'icon' => 'process-icon-save'
                )
            )
        );

        //Code for Form Editing
        if (!Tools::isEmpty(trim(Tools::getValue('id_attribute_group')))) {
            $getAttributeMappingDetails = EtsyAttributeMappings::getAttributeMappingDetails(Tools::getValue('id_attribute_group'));
            if (isset($getAttributeMappingDetails)) {
                $this->fields_value = array(
                    'id_attribute_group' => $getAttributeMappingDetails['id_attribute_group'],
                    'id_attribute_group_text' => $psAttributes[$getAttributeMappingDetails['id_attribute_group']],
                    'property_id' => $getAttributeMappingDetails['property_id'],
                );
            }
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddattribute_group_lang')) {
            $property_id = pSQL(Tools::getValue('property_id'));
            $id_attribute_group = Tools::getValue('id_attribute_group');

            if (Tools::isSubmit('id_attribute_group')) {
                $checkAttributeMappingExist = EtsyAttributeMappings::checkAttributeMappingExist($id_attribute_group);
                if (!$checkAttributeMappingExist) {
                    Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyAttributeMapping') . '&etsyError=57');
                } else {
                    $addAttributeMapping = EtsyAttributeMappings::updateAttributeMapping($property_id, $id_attribute_group);
                    if ($addAttributeMapping) {
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyAttributeMapping') . '&etsyConf=58');
                    }
                }
            } else {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyAttributeMapping') . '&etsyError=57');
            }
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    private function getModuleDirUrl()
    {
        $module_dir = '';
        if ($this->checkSecureUrl()) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }
        return $module_dir;
    }

    private function checkSecureUrl()
    {
        $custom_ssl_var = 0;
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == 'on') {
                $custom_ssl_var = 1;
            }
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $custom_ssl_var = 1;
        }
        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }
}
