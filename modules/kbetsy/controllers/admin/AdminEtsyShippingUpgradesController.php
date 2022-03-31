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

//Include Etsy Module Class to inherit some common functions and callbacks
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplates.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingUpgrades.php');

class AdminEtsyShippingUpgradesController extends ModuleAdminController
{
    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyShippingUpgrades';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_shipping_upgrades';
        $this->className = 'EtsyShippingUpgrades';
        $this->identifier = 'id_etsy_shipping_upgrades';
        parent::__construct();
        $this->fields_list = array(
            'id_etsy_shipping_upgrades' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'shipping_template_title' => array(
                'title' => $this->l('Shipping Template Title'),
            ),
            'shipping_origin_country' => array(
                'title' => $this->l('Shipping Origin Country'),
            ),
            'shipping_upgrade_title' => array(
                'title' => $this->l('Shipping Upgrade Title'),
            ),
            'shipping_upgrade_destination' => array(
                'type' => 'select',
                'filter_key' => 'shipping_upgrade_destination',
                'list' => array('1'=>$this->l('International') ,  '0' => $this->l('Domestic')),
                'filter_type' => 'int',
                'callback' => 'getUpgradeDestination',
                'order_key' => 'shipping_upgrade_destination',
                'title' => $this->l('Shipping Upgrade Destination'),
            ),
            'shipping_upgrade_primary_cost' => array(
                'title' => $this->l('Primary Cost'),
                'search' => false
            ),
            'shipping_upgrade_secondary_cost' => array(
                'title' => $this->l('Secondary Cost'),
                'search' => false
            ),
        );

        $this->_select = 'st.shipping_template_title, st.shipping_origin_country';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'etsy_shipping_templates` st ON (a.id_etsy_shipping_templates = st.id_etsy_shipping_templates)';
        $this->_where = " = 1 AND a.delete_flag = '0'";
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $this->_where .= " AND a.id_etsy_shipping_templates = " . Tools::getValue('id_etsy_shipping_templates');
            $this->list_simple_header = true;
        }
        $this->_orderBy = 'id_etsy_shipping_upgrades';
        $this->_orderWay = 'ASC';

        //This is to show notification messages to admin
        if (!Tools::isEmpty(trim(Tools::getValue('etsyConf')))) {
            new EtsyModule(Tools::getValue('etsyConf'), 'conf');
        }

        if (!Tools::isEmpty(trim(Tools::getValue('etsyError')))) {
            new EtsyModule(Tools::getValue('etsyError'), 'error');
        }
    }

    public function getUpgradeDestination($row_data, $tr)
    {
        $arr = array('1'=>$this->l('International') ,  '0' => $this->l('Domestic'));
        return $arr[$row_data];
    }

    //Set JS and CSS
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/script.js');
        $this->addJS($this->getModuleDirUrl() . 'kbetsy/views/js/velovalidation.js');
        $this->addCSS($this->getModuleDirUrl() . 'kbetsy/views/css/style.css');
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $this->toolbar_btn['new'] = array(
                'desc' => $this->l('Add New'),
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&addetsy_shipping_upgrades&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates')
            );
            return $this->toolbar_btn;
        } else {
            unset($this->toolbar_btn['new']);
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * Render a form
     */
    public function renderForm()
    {
        //Prepare array of Countries
        $countriesList = array();
        $countriesList[] = array(
            'id_option' => '',
            'name' => 'Select Country'
        );
        $etsyCountriesList = EtsyModule::etsyGetAllCountriesFromDB();
        if (isset($etsyCountriesList)) {
            foreach ($etsyCountriesList as $etsyCountry) {
                $countriesList[] = array(
                    'id_option' => $etsyCountry['country_id'],
                    'name' => $etsyCountry['country_name']
                );
            }
        }

        //Prepare array of detination types
        $destinationTypes = array(
            array(
                'id_option' => '1',
                'name' => $this->l('International')
            ),
            array(
                'id_option' => '0',
                'name' => $this->l('Domestic')
            )
        );
        $disabled = false;
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_upgrades')))) {
            $disabled = true;
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_upgrades'))) ? $this->l('Update Shipping Upgrade') : $this->l('Add New Shipping Upgrade'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'ps_version'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_etsy_shipping_templates'
                ),
//                array(
//                    'type' => 'hidden',
//                    'name' => 'shipping_template_title_hidden'
//                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Shipping Template Title'),
                    'desc' => $this->l('This is Shipping Template Title'),
                    'name' => 'shipping_template_title',
                    'maxlength' => 255,
//                    'required' => true,
                    'disabled' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Origin Country'),
                    'desc' => $this->l('This is an origin country of Shipment'),
                    'name' => 'shipping_origin_country_id',
//                    'required' => true,
                    'options' => array(
                        'query' => $countriesList,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'disabled' => true
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'shipping_origin_country_id_hidden'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'shipping_origin_country'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Shipping Upgrade Title'),
                    'desc' => $this->l('This is Shipping Upgrade Title'),
                    'name' => 'shipping_upgrade_title',
                    'maxlength' => 50,
                    'required' => true,
                    'disabled' => $disabled
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Destination Type'),
                    'desc' => $this->l('Choose a destination type as domestic or international'),
                    'name' => 'destination_type',
                    'required' => true,
                    'options' => array(
                        'query' => $destinationTypes,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
//                    'onchange' => 'switchEntryDestinationTypes(this.value)',
                    'disabled' => $disabled
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Primary Cost'),
                    'desc' => $this->l('Provide shipping primary cost for a shipment'),
                    'name' => 'shipping_upgrade_primary_cost',
                    'class' => 'velsof_number_field',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Secondary Cost'),
                    'desc' => $this->l('Provide shipping cost for each additional item'),
                    'name' => 'shipping_upgrade_secondary_cost',
                    'class' => 'velsof_number_field',
                    'required' => true
                )
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_shipping_upgrades_form')",
                    'title' => $this->l('Save'),
                    'icon' => 'process-icon-save'
                )
            )
        );

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails(Tools::getValue('id_etsy_shipping_templates'));
            $this->fields_value = array(
                'id_etsy_shipping_templates' => $getShippingTemplateDetails[0]['id_etsy_shipping_templates'],
                'shipping_template_title' => $getShippingTemplateDetails[0]['shipping_template_title'],
                'shipping_origin_country_id' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                'shipping_origin_country' => $getShippingTemplateDetails[0]['shipping_origin_country'],
                'shipping_origin_country_id_hidden' => $getShippingTemplateDetails[0]['shipping_origin_country_id']
            );
        }

        //Code for Form Editing
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_upgrades')))) {
            $getShippingUpgradeDetails = EtsyShippingUpgrades::getShippingUpgradeDetails(Tools::getValue('id_etsy_shipping_upgrades'));

            if (isset($getShippingUpgradeDetails)) {
                $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails($getShippingUpgradeDetails[0]['id_etsy_shipping_templates']);
                $this->fields_value = array(
                    'id_etsy_shipping_upgrades' => Tools::getValue('id_etsy_shipping_upgrades'),
                    'id_etsy_shipping_templates' => $getShippingTemplateDetails[0]['id_etsy_shipping_templates'],
                    'shipping_template_title' => $getShippingTemplateDetails[0]['shipping_template_title'],
                    'shipping_origin_country_id' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                    'shipping_origin_country' => $getShippingTemplateDetails[0]['shipping_origin_country'],
                    'shipping_origin_country_id_hidden' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                    'shipping_upgrade_title' => $getShippingUpgradeDetails[0]['shipping_upgrade_title'],
                    'shipping_upgrade_primary_cost' => $getShippingUpgradeDetails[0]['shipping_upgrade_primary_cost'],
                    'shipping_upgrade_secondary_cost' => $getShippingUpgradeDetails[0]['shipping_upgrade_secondary_cost'],
                    'destination_type' => $getShippingUpgradeDetails[0]['shipping_upgrade_destination'],
                );
            }
        }
        return parent::renderForm();
    }

    public function postProcess()
    {
        //Handle Form Submission
        if (Tools::isSubmit('submitAddetsy_shipping_upgrades')) {
            $formError = 0;
            $customErrors = array();

            //Prepare variables holding  post values
            $shippingPrimaryCost = Tools::getValue('shipping_upgrade_primary_cost');
            $shippingSecondaryCost = Tools::getValue('shipping_upgrade_secondary_cost');
            $shippingUpgradeTitle = trim(Tools::getValue('shipping_upgrade_title'));
            $destinationType = Tools::getValue('destination_type');
            $shippingDestinationCountryName ='';

            //Validate Primary Cost
            if (($shippingPrimaryCost != 0) && empty($shippingPrimaryCost)) {
                $formError = 1;
                $customErrors[] = 23;
            }
            if (($shippingSecondaryCost != 0) && empty($shippingSecondaryCost)) {
                $formError = 1;
                $customErrors[] = 24;
            }
            if (!$formError && !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
                if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_upgrades')))) {
                    $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'  AND id_etsy_shipping_upgrades = '".(int)Tools::getValue('id_etsy_shipping_upgrades')."' AND delete_flag = '0'";
                    $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                    if ($dataExistenceResult[0]['count'] > 0) {
                        $updateShippingTemplateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET  shipping_upgrade_primary_cost = '" . (float) $shippingPrimaryCost . "', shipping_upgrade_secondary_cost = '" . (float) $shippingSecondaryCost . "',  renew_flag = '1' WHERE id_etsy_shipping_upgrades = '" . (int) Tools::getValue('id_etsy_shipping_upgrades') . "'";
                        if (Db::getInstance()->execute($updateShippingTemplateSQL)) {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Upgrade Updated. Updated values are - <br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                            $auditMethodName = 'AdminEtsyShippingUpgrades::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=51');
                        }
                    } else {
                        //Audit Log Entry
                        $auditLogEntryString = 'Shipping Upgrade Could not be updated as shipping details not found';
                        $auditMethodName = 'AdminEtsyShippingUpgrades::postProcess()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=53');
                    }
                } else {
                    //SQL to check details existence
                    $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "' AND shipping_upgrade_title = '" . pSQL(Tools::getValue('shipping_upgrade_title')) . "' AND shipping_upgrade_destination = '".(int)$destinationType."' AND delete_flag = '0'";
                    $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
                    if ($dataExistenceResult[0]['count'] == 0) {
                        //Insert data SQL
                        $addShippingTemplateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_upgrades VALUES (NULL, '" . (int) Tools::getValue('id_etsy_shipping_templates') . "', NULL, '" . pSQL($shippingUpgradeTitle) . "', '" . pSQL($destinationType) . "', '" . (float) $shippingPrimaryCost . "', '" . (float) $shippingSecondaryCost . "','0', '0', NOW(), NOW())";
                        if (Db::getInstance()->execute($addShippingTemplateSQL)) {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Template Entry Added. Added values are - <br>Shipping Destination Country: ' . $shippingDestinationCountryName . '<br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                            $auditMethodName = 'AdminEtsyShippingUpgrades::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=52');
                        }
                    } else {
                        //Audit Log Entry
                        $auditLogEntryString = 'Shipping Upgrade Could not be Added as already exists with same title';
                        $auditMethodName = 'AdminEtsyShippingUpgrades::postProcess()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=51');
                    }
                }
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=' . implode(",", $customErrors));
            }
        }
        parent::postProcess();
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    //Function definition to delete Shipping Template Entry
    public function processDelete()
    {
        //parent::processDelete();

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_upgrades')))) {
            //Get Shipping Template Entry Details
            $getShippingUpgradeDetails = EtsyShippingUpgrades::getShippingUpgradeDetails(Tools::getValue('id_etsy_shipping_upgrades'));
            $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails($getShippingUpgradeDetails[0]['id_etsy_shipping_templates']);
            if (is_null($getShippingUpgradeDetails[0]['shipping_upgrade_id'])) {
                $deleteShippingUpgradeSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_upgrades = '" . (int) Tools::getValue('id_etsy_shipping_upgrades') . "'";
            } else {
                $deleteShippingUpgradeSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET delete_flag = '1' WHERE id_etsy_shipping_upgrades = '" . (int) Tools::getValue('id_etsy_shipping_upgrades') . "'";
            }

            if (Db::getInstance()->execute($deleteShippingUpgradeSQL)) {
                //Audit Log Entry
                $auditLogEntryString = 'Shipping Upgrade for <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . ' Deleted';
                $auditMethodName = 'AdminEtsyShippingUpgrades::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=53');
            } else {
                //Audit Log Entry
                $auditLogEntryString = 'Deletion of Shipping Upgrade for <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . ' Failed';
                $auditMethodName = 'AdminEtsyShippingUpgrades::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=52');
            }
        }
    }

    /**
     * Display delete action link
     */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {

        if (!array_key_exists('Delete', self::$cache_lang)) {
            self::$cache_lang['Delete'] = $this->l('Delete', 'Helper');
        }

        if (!empty($id)) {
            $getShippingUpgradeDetails = EtsyShippingUpgrades::getShippingUpgradeDetails($id);

            $this->context->smarty->assign(array(
                'confirm' => 'Arey you sure to delete selected object ?',
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . $getShippingUpgradeDetails[0]['id_etsy_shipping_templates'] . '&id_etsy_shipping_upgrades=' . $id . '&deleteetsy_shipping_upgrades',
                'action' => self::$cache_lang['Delete'],
                'icon' => 'trash'
            ));
        }

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
    }

    /**
     * Display edit action link
     */
    public function displayEditLink($token = null, $id = null, $name = null)
    {

        if (!array_key_exists('Edit', self::$cache_lang)) {
            self::$cache_lang['Edit'] = $this->l('Edit', 'Helper');
        }

        if (!empty($id)) {
            $getShippingTemplateEntryDetails = EtsyShippingUpgrades::getShippingUpgradeDetails($id);

            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . $getShippingTemplateEntryDetails[0]['id_etsy_shipping_templates'] . '&id_etsy_shipping_upgrades=' . $id . '&updateetsy_shipping_upgrades',
                'action' => self::$cache_lang['Edit'],
                'icon' => 'pencil'
            ));
        }

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
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
        if (!Tools::getValue('id_etsy_shipping_upgrades') && !Tools::isSubmit('addetsy_shipping_upgrades')) {
            if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
                $this->page_header_toolbar_btn['new_template'] = array(
                    'href' => $this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&addetsy_shipping_upgrades&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates'),
                    'desc' => $this->l('Add new'),
                    'icon' => 'process-icon-new'
                );
            }
        }

        if (Tools::getValue('id_etsy_shipping_upgrades') || Tools::isSubmit('id_etsy_shipping_upgrades') || Tools::isSubmit('addetsy_shipping_upgrades')) {
            $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Cancel'),
                'icon' => 'process-icon-cancel'
            );
        }

        parent::initPageHeaderToolbar();
    }
}
