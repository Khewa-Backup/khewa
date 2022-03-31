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

require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyModule.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplates.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplatesEntries.php');
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingUpgrades.php');

class AdminEtsyShippingTemplatesController extends ModuleAdminController
{

    public $country_sync = false;
    
    public function __construct()
    {
        $this->name = 'EtsyShippingTemplates';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_shipping_templates';
        $this->className = 'EtsyShippingTemplates';
        $this->identifier = 'id_etsy_shipping_templates';

        parent::__construct();
        $this->fields_list = array(
            'id_etsy_shipping_templates' => array(
                'title' => $this->module->l('Template ID', 'AdminEtsyShippingTemplatesController'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'shipping_template_title' => array(
                'title' => $this->module->l('Shipping Template Title', 'AdminEtsyShippingTemplatesController'),
            ),
            'shipping_origin_country' => array(
                'title' => $this->module->l('Shipping Origin Country', 'AdminEtsyShippingTemplatesController')
            ),
            'shipping_min_process_days' => array(
                'title' => $this->module->l('Min. Processing Days', 'AdminEtsyShippingTemplatesController'),
                'align' => 'center',
                'callback' => 'showMinDays'
            ),
            'shipping_max_process_days' => array(
                'title' => $this->module->l('Max. Processing Days', 'AdminEtsyShippingTemplatesController'),
                'align' => 'center',
                'callback' => 'showMaxDays'
            )
        );

        $this->_where = " = 1 AND delete_flag = '0'";

        //Line added to remove link from list row
        $this->module->list_no_link = true;
        
        if (EtsyShippingTemplates::getTotalCountries() > 0) {
            $this->country_sync = true;
        }

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

    public function showMinDays($id_row, $row_data)
    {
        $output = '';
        if (!empty($row_data['shipping_min_process_days']) && $row_data['shipping_min_process_days'] > 0) {
            $output = $row_data['shipping_min_process_days'] . ' days';
        } else {
            $output = 'NA';
        }

        return $output;
    }

    public function showMaxDays($id_row, $row_data)
    {
        $output = '';
        if (!empty($row_data['shipping_max_process_days']) && $row_data['shipping_max_process_days'] > 0) {
            $output = $row_data['shipping_max_process_days'] . ' days';
        } else {
            $output = 'NA';
        }

        return $output;
    }

    public function renderList()
    {
        //$this->addRowAction('view');
        //$this->addRowAction('viewUpgrade');
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        if ($this->country_sync == false) {
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            $sync_countries_regions_link = $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncCountriesRegions', 'secure_key' => $secure_key));
                    
            $this->context->smarty->assign("message", html_entity_decode(sprintf($this->module->l('Etsy countries not found in the system. <a href="%s" target="_blank">Click here</a> to sync the etsy countries to continue.', 'AdminEtsyShopSectionController'), $sync_countries_regions_link)));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", $sync_countries_regions_link);

            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs;
        } else if (EtsyShippingTemplates::getTotalTeamplates() <= 0) {
            $this->context->smarty->assign("message", $this->module->l('Shipping template has not been added yet. Click on the "Add new" icon to add the same OR click on the "Sync Shipping Templates" icon to download the existing shipping templates from the Etsy account.', 'AdminEtsyShopSectionController'));
            $this->context->smarty->assign("type", "alert-info");
            $this->context->smarty->assign("KbMessageLink", '');
            $msgs = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/msgs.tpl");
            return $msgs;
        } else {
            return parent::renderList();
        }
        return parent::renderList();
    }

    /** Render a form */
    public function renderForm()
    {
        $countriesList = array();
        $etsyCountriesList = array();
        $regionsList = array();
        $etsyRegionsList = array();
        $countriesList[] = array('id_option' => '', 'name' => $this->l('Select Country'));
        $etsyCountriesList = EtsyModule::etsyGetAllCountriesFromDB();
        if (isset($etsyCountriesList)) {
            foreach ($etsyCountriesList as $etsyCountry) {
                $countriesList[] = array(
                    'id_option' => $etsyCountry['country_id'],
                    'name' => $etsyCountry['country_name']
                );
            }
        }
        
        $regionsList[] = array(
            'id_option' => '',
            'name' => $this->module->l('Select Region', 'AdminEtsyShippingTemplatesEntriesController')
        );
        $etsyRegionsList = EtsyModule::etsyGetAllRegionsFromDB();
        if (isset($etsyRegionsList)) {
            foreach ($etsyRegionsList as $etsyRegion) {
                $regionsList[] = array(
                    'id_option' => $etsyRegion['region_id'],
                    'name' => $etsyRegion['region_name']
                );
            }
        }
        /*
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates'))) ? $this->module->l('Update Shipping Template', 'AdminEtsyShippingTemplatesController') : $this->module->l('Add New Shipping Template', 'AdminEtsyShippingTemplatesController'),
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
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Shipping Template Title', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Provide Shipping Template Title', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_template_title',
                    'maxlength' => 255,
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Origin Country', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Choose a country as an origin country of Shipment', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_origin_country_id',
                    'required' => true,
                    'options' => array(
                        'query' => $countriesList,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'onchange' => 'setOriginCountry()'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'shipping_origin_country'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Primary Cost', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Provide shipping primary cost for a shipment. You can set it only once.', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_primary_cost',
                    'required' => true,
                    'readonly' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates'))) ? true : false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Secondary Cost', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Provide shipping cost for each additional item. You can set it only once.', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_secondary_cost',
                    'required' => true,
                    'readonly' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates'))) ? true : false
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Min. Processing Days', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Provide minimum number of days of a shipment', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_min_process_days'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Max. Processing Days', 'AdminEtsyShippingTemplatesController'),
                    'desc' => $this->module->l('Provide maximum number of days of a shipment', 'AdminEtsyShippingTemplatesController'),
                    'name' => 'shipping_max_process_days'
                )
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_shipping_templates_form')",
                    'title' => $this->module->l('Save', 'AdminEtsyShippingTemplatesController'),
                    'icon' => 'process-icon-save'
                )
            )
        );
        */
        $this->context->smarty->assign('countries_list', $countriesList);
        $this->context->smarty->assign('regions_list', $regionsList);

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails(Tools::getValue('id_etsy_shipping_templates'));

            if (isset($getShippingTemplateDetails)) {
                $this->fields_value = array(
                    'id_etsy_shipping_templates' => Tools::getValue('id_etsy_shipping_templates'),
                    'shipping_template_title' => $getShippingTemplateDetails[0]['shipping_template_title'],
                    'shipping_origin_country_id' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                    'shipping_origin_country' => $getShippingTemplateDetails[0]['shipping_origin_country'],
                    'shipping_primary_cost' => $getShippingTemplateDetails[0]['shipping_primary_cost'],
                    'shipping_secondary_cost' => $getShippingTemplateDetails[0]['shipping_secondary_cost'],
                    'shipping_min_process_days' => $getShippingTemplateDetails[0]['shipping_min_process_days'],
                    'shipping_max_process_days' => $getShippingTemplateDetails[0]['shipping_max_process_days']
                );
            }
            
            $template_entries_html = '';
            $template_entries = EtsyShippingTemplatesEntries::getShippingTemplateEntryDetails(Tools::getValue('id_etsy_shipping_templates'));
            if (!empty($template_entries)) {
                foreach ($template_entries as $template_entry) {
                    $this->context->smarty->assign('template_entry', array_merge(array('existing_entry' => true), $template_entry));
                    $template_entries_html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/template_entry.tpl");
                }
            }
            
            $this->context->smarty->assign('fields_value', $this->fields_value);
            $this->context->smarty->assign('template_entries_html', $template_entries_html);
            
            $template_upgrades_html = '';
            $template_upgrades = EtsyShippingUpgrades::getShippingUpgradeDetails(Tools::getValue('id_etsy_shipping_templates'));
            if (!empty($template_upgrades)) {
                foreach ($template_upgrades as $template_upgrade) {
                    $this->context->smarty->assign('template_upgrade', array_merge(array('existing_entry' => true), $template_upgrade));
                    $template_upgrades_html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/template_upgrade.tpl");
                }
            }
            $this->context->smarty->assign('template_upgrades_html', $template_upgrades_html);
            
            $this->context->smarty->assign('form_action', $this->context->link->getAdminlink('AdminEtsyShippingTemplates') . "&updateetsy_shipping_templates&id_etsy_shipping_templates=" . Tools::getValue('id_etsy_shipping_templates'));
        } else {
            $this->fields_value = array(
                'id_etsy_shipping_templates' => '',
                'shipping_template_title' => '',
                'shipping_origin_country_id' => '',
                'shipping_origin_country' => '',
                'shipping_primary_cost' => '',
                'shipping_secondary_cost' => '',
                'shipping_min_process_days' => '',
                'shipping_max_process_days' => ''
            );
            $this->context->smarty->assign('template_entry', array(
                'existing_entry' => false,
                'id_etsy_shipping_templates_entries' => time().  rand(1000, 10000),
                'shipping_entry_destination_country_id' => '',
                'shipping_entry_destination_region_id' => '',
                'shipping_entry_primary_cost' => '',
                'shipping_entry_secondary_cost' => ''
            ));
            $this->context->smarty->assign('fields_value', $this->fields_value);

            $template_entries_html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/template_entry.tpl");
            $this->context->smarty->assign('template_entries_html', $template_entries_html);

            $this->context->smarty->assign('template_upgrades_html', '');
            $this->context->smarty->assign('form_action', $this->context->link->getAdminlink('AdminEtsyShippingTemplates')."&addetsy_shipping_templates");
        }
        //return parent::renderForm();
        $this->context->smarty->assign('controller_url', $this->context->link->getAdminlink('AdminEtsyShippingTemplates'));
       
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/shipping_template_form.tpl");
    }

    public function postProcess()
    {
        $method_name = 'AdminEtsyShippingTemplates::postProcess()';
        $countriesList = array();
        $etsyCountriesList = array();
        $regionsList = array();
        $etsyRegionsList = array();
        $countriesList[] = array('id_option' => '', 'name' => $this->l('Select Country'));
        $etsyCountriesList = EtsyModule::etsyGetAllCountriesFromDB();
        if (isset($etsyCountriesList)) {
            foreach ($etsyCountriesList as $etsyCountry) {
                $countriesList[] = array(
                    'id_option' => $etsyCountry['country_id'],
                    'name' => $etsyCountry['country_name']
                );
            }
        }
        
        $regionsList[] = array(
            'id_option' => '',
            'name' => $this->module->l('Select Region', 'AdminEtsyShippingTemplatesEntriesController')
        );
        $etsyRegionsList = EtsyModule::etsyGetAllRegionsFromDB();
        if (isset($etsyRegionsList)) {
            foreach ($etsyRegionsList as $etsyRegion) {
                $regionsList[] = array(
                    'id_option' => $etsyRegion['region_id'],
                    'name' => $etsyRegion['region_name']
                );
            }
        }
        
        /** Ajax requests handling */
        if (!empty(Tools::getValue('type')) && Tools::getValue('type') == 'entry') {
            $this->context->smarty->assign('countries_list', $countriesList);
            $this->context->smarty->assign('regions_list', $regionsList);
            $this->context->smarty->assign('template_entry', array(
                'existing_entry' => false,
                'id_etsy_shipping_templates_entries' => time().  rand(1000, 10000),
                'shipping_entry_destination_country_id' => '',
                'shipping_entry_destination_region_id' => '',
                'shipping_entry_primary_cost' => '',
                'shipping_entry_secondary_cost' => ''
            ));
            echo $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/template_entry.tpl");
            die();
        } else if (!empty(Tools::getValue('type')) && Tools::getValue('type') == 'upgrade') {
            $this->context->smarty->assign('countries_list', $countriesList);
            $this->context->smarty->assign('regions_list', $regionsList);
            $this->context->smarty->assign('template_upgrade', array(
                'existing_entry' => false,
                'id_etsy_shipping_upgrades' => time().  rand(1000, 10000),
                'shipping_upgrade_title' => '',
                'shipping_upgrade_destination' => 0,
                'shipping_upgrade_primary_cost' => '',
                'shipping_upgrade_secondary_cost' => ''
            ));
            echo $this->context->smarty->fetch(_PS_MODULE_DIR_ . "kbetsy/views/templates/admin/template_upgrade.tpl");
            die();
        } else if (!empty(Tools::getValue('type')) && Tools::getValue('type') == 'deleteentry') {
            /* If shipping template entry id is set then set the delete flag OR directly delete the same for DB */
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET "
                . "delete_flag = '1' "
                . "WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('entry_id') . "' AND shipping_template_entry_id IS NOT NULL");
            
            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('entry_id') . "' AND shipping_template_entry_id IS NULL");

            echo "true";
            die();
        } else if (!empty(Tools::getValue('type')) && Tools::getValue('type') == 'deleteupgrade') {
            /* If shipping template entry id is set then set the delete flag OR directly delete the same for DB */
            Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET "
                . "delete_flag = '1' "
                . "WHERE id_etsy_shipping_upgrades = '" . (int) Tools::getValue('upgrade_id') . "' AND shipping_upgrade_id IS NOT NULL");
            
            Db::getInstance()->execute("DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_upgrades = '" . (int) Tools::getValue('upgrade_id') . "' AND shipping_upgrade_id IS NULL");

            echo "true";
            die();
        }

        if (Tools::isSubmit('submitAddetsy_shipping_templates')) {
            $formError = 0;
            $customErrors = array();

            //Prepare variables holding  post values
            $shippingTemplateTitle = pSQL(Tools::getValue('shipping_template_title'));

            $shippingOriginCountryID = Tools::getValue('shipping_origin_country_id');
            $shippingOriginCountryName = EtsyModule::etsyGetCountryNameByCountryId(Tools::getValue('shipping_origin_country_id'));

            $shippingMinProcessDays = !Tools::isEmpty(trim(Tools::getValue('shipping_min_process_days'))) ? Tools::getValue('shipping_min_process_days') : 0;
            $shippingMaxProcessDays = !Tools::isEmpty(trim(Tools::getValue('shipping_max_process_days'))) ? Tools::getValue('shipping_max_process_days') : 0;

            //Validate Shipping Template Title
            if (empty($shippingTemplateTitle)) {
                $formError = 1;
                $customErrors[] = 10;
            }
            //Validate Origin Country
            if (empty($shippingOriginCountryID)) {
                $formError = 1;
                $customErrors[] = 11;
            }

            if (!empty($shippingMinProcessDays) && !empty($shippingMaxProcessDays) && $shippingMinProcessDays != 0 && $shippingMaxProcessDays != 0) {
                if ($shippingMinProcessDays >= $shippingMaxProcessDays) {
                    $formError = 1;
                    $customErrors[] = 16;
                }
            }
            if (!$formError) {
                if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
                    $title_exist = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_title = '" . pSQL($shippingTemplateTitle) . "' AND delete_flag = '0' AND id_etsy_shipping_templates != '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'", true, false);
                    if ($title_exist == 0) {
                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET "
                                . "shipping_template_title = '" . pSQL($shippingTemplateTitle) . "', "
                                . "shipping_origin_country_id = '" . (int) $shippingOriginCountryID . "', "
                                . "shipping_origin_country = '" . pSQL($shippingOriginCountryName) . "', "
                                . "shipping_min_process_days = '" . (int) $shippingMinProcessDays . "', "
                                . "shipping_max_process_days = '" . (int) $shippingMaxProcessDays . "', "
                                . "renew_flag = '1' "
                                . "WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'");
                        
                        /* Tempate entry Add & Update */
                        if (!Tools::isEmpty(Tools::getValue('template_entry'))) {
                            $entires = Tools::getValue('template_entry');
                            foreach ($entires as $entry) {
                                if (!empty($entry['existing_entry']) && $entry['existing_entry'] == 1) {
                                    $shipping_desination_country = EtsyModule::etsyGetCountryNameByCountryId($entry['shipping_desination_country_id']);
                                    $shipping_desination_region = EtsyModule::etsyGetRegionNameByRegionId($entry['shipping_destination_region_id']);
                                } else {
                                    $shipping_desination_country = EtsyModule::etsyGetCountryNameByCountryId($entry['shipping_desination_country']);
                                    $shipping_desination_region = EtsyModule::etsyGetRegionNameByRegionId($entry['shipping_destination_region']);
                                }
                                if (!empty($entry['existing_entry']) && $entry['existing_entry'] == 1) {
                                    Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET "
                                        . "shipping_entry_primary_cost = '" . (float) $entry['shipping_primary_cost'] . "',"
                                        . "shipping_entry_secondary_cost = '" . (float) $entry['shipping_secondary_cost'] . "',"
                                        . "renew_flag = '1',"
                                        . "shipping_entry_date_update = NOW() "
                                        . "WHERE id_etsy_shipping_templates_entries = '" . (int) $entry['id_etsy_shipping_templates_entries'] . "'");
                                } else {
                                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET "
                                        . "id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "',"
                                        . "shipping_entry_destination_country_id = '" . (int) $entry['shipping_desination_country'] . "',"
                                        . "shipping_entry_destination_country = '" . pSQL($shipping_desination_country) . "',"
                                        . "shipping_entry_primary_cost = '" . (float) $entry['shipping_primary_cost'] . "',"
                                        . "shipping_entry_secondary_cost = '" . (float) $entry['shipping_secondary_cost'] . "',"
                                        . "shipping_entry_destination_region_id = '" . (int) $entry['shipping_destination_region'] . "',"
                                        . "shipping_entry_destination_region = '" . pSQL($shipping_desination_region) . "',"
                                        . "shipping_entry_date_added = NOW(),"
                                        . "shipping_entry_date_update = NOW()");
                                }
                            }
                        }
                        
                        /* Tempate Upgrades Add & Update */
                        if (!Tools::isEmpty(Tools::getValue('template_upgrade'))) {
                            $upgrades = Tools::getValue('template_upgrade');
                            if (!empty($upgrades)) {
                                foreach ($upgrades as $upgrade) {
                                    if (!empty($upgrade['existing_entry']) && $upgrade['existing_entry'] == 1) {
                                        Db::getInstance()->execute("UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET "
                                            . "shipping_upgrade_title = '" . pSQL($upgrade['shipping_upgrade_title']) . "',"
                                            . "shipping_upgrade_primary_cost = '" . (float) $upgrade['shipping_upgrade_primary_cost'] . "',"
                                            . "shipping_upgrade_secondary_cost = '" . (float) $upgrade['shipping_upgrade_secondary_cost'] . "',"
                                            . "renew_flag = '1',"
                                            . "shipping_upgrade_date_update = NOW() "
                                            . "WHERE id_etsy_shipping_upgrades = '" . (int) $upgrade['id_etsy_shipping_upgrades'] . "'");
                                    } else {
                                        Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_upgrades SET "
                                            . "id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "',"
                                            . "shipping_upgrade_title = '" . pSQL($upgrade['shipping_upgrade_title']) . "',"
                                            . "shipping_upgrade_destination = '" . pSQL($upgrade['shipping_upgrade_destination']) . "',"
                                            . "shipping_upgrade_primary_cost = '" . (float) $upgrade['shipping_upgrade_primary_cost'] . "',"
                                            . "shipping_upgrade_secondary_cost = '" . (float) $upgrade['shipping_upgrade_secondary_cost'] . "',"
                                            . "shipping_upgrade_date_added = NOW(),"
                                            . "shipping_upgrade_date_update = NOW()");
                                    }
                                }
                            }
                        }
                        
                        
                        EtsyModule::auditLogEntry('Shipping template updated. Updated value is<br>Shipping Template Title: ' . $shippingTemplateTitle, $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyConf=13');
                    } else {
                        EtsyModule::auditLogEntry('Shipping template could not be updated due to duplicate title.', $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyError=17');
                    }
                } else {
                    $title_exist = Db::getInstance()->getValue("SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE shipping_template_title = '" . pSQL($shippingTemplateTitle) . "' AND delete_flag = '0'", true, false);
                    if ($title_exist == 0) {
                        Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates SET "
                                . "shipping_template_title = '" . pSQL($shippingTemplateTitle) . "', "
                                . "shipping_origin_country_id = '" . (int) $shippingOriginCountryID . "', "
                                . "shipping_origin_country = '" . pSQL($shippingOriginCountryName) . "', "
                                . "shipping_min_process_days = '" . (int) $shippingMinProcessDays . "', "
                                . "shipping_max_process_days = '" . (int) $shippingMaxProcessDays . "', "
                                . "shipping_date_added = NOW(),"
                                . "shipping_date_update = NOW()");
                        $id_etsy_shipping_templates = DB::getInstance()->Insert_ID();
                        if (!Tools::isEmpty(Tools::getValue('template_entry'))) {
                            $entires = Tools::getValue('template_entry');
                            foreach ($entires as $entry) {
                                $shipping_desination_country = EtsyModule::etsyGetCountryNameByCountryId($entry['shipping_desination_country']);
                                $shipping_desination_region = EtsyModule::etsyGetRegionNameByRegionId($entry['shipping_destination_region']);
                                Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET "
                                    . "id_etsy_shipping_templates = '" . (int) $id_etsy_shipping_templates . "',"
                                    . "shipping_entry_destination_country_id = '" . (int) $entry['shipping_desination_country'] . "',"
                                    . "shipping_entry_destination_country = '" . pSQL($shipping_desination_country) . "',"
                                    . "shipping_entry_primary_cost = '" . (float) $entry['shipping_primary_cost'] . "',"
                                    . "shipping_entry_secondary_cost = '" . (float) $entry['shipping_secondary_cost'] . "',"
                                    . "shipping_entry_destination_region_id = '" . (int) $entry['shipping_destination_region'] . "',"
                                    . "shipping_entry_destination_region = '" . pSQL($shipping_desination_region) . "',"
                                    . "shipping_entry_date_added = NOW(),"
                                    . "shipping_entry_date_update = NOW()");
                            }
                        }
                        
                        EtsyModule::auditLogEntry('Shipping template added. Added template:<br>Shipping Template Title: ' . $shippingTemplateTitle, $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyConf=14');
                    } else {
                        EtsyModule::auditLogEntry('Shipping template could not be updated due to duplicate title.', $method_name);
                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyError=17');
                    }
                }
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
            }
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    //Function definition to delete Shipping Template
    public function processDelete()
    {
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            //Get Shipping Template Details
            $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails(Tools::getValue('id_etsy_shipping_templates'));

            //Check if shipping template is mapped to profile
            $profileMappingSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_profiles WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";
            $profileMappingExistence = Db::getInstance()->executeS($profileMappingSQL, true, false);

            if ($profileMappingExistence[0]['count'] == 0) {
                if (is_null($getShippingTemplateDetails[0]['shipping_template_id'])) {
                    //SQL Query to delete Shipping Template
                    $deleteShippingTemplateSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";

                    if (Db::getInstance()->execute($deleteShippingTemplateSQL)) {
                        $deleteShippingTemplateEntriesSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";
                        Db::getInstance()->execute($deleteShippingTemplateEntriesSQL);

                        $deleteShippingTemplateUpgradesSQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_upgrades WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";
                        Db::getInstance()->execute($deleteShippingTemplateUpgradesSQL);

                        //Audit Log Entry
                        $auditLogEntryString = 'Shipping Template - <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> Deleted';
                        $auditMethodName = 'AdminEtsyShippingTemplates::processDelete()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyConf=15');
                    } else {
                        //Audit Log Entry
                        $auditLogEntryString = 'Deletion of Shipping Template - <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> Failed';
                        $auditMethodName = 'AdminEtsyShippingTemplates::processDelete()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyError=18');
                    }
                } else {
                    //SQL Query to delete Shipping Template
                    $deleteShippingTemplateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates SET delete_flag = '1' WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";

                    if (Db::getInstance()->execute($deleteShippingTemplateSQL)) {
                        $deleteShippingTemplateEntriesSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET delete_flag = '1' WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";
                        Db::getInstance()->execute($deleteShippingTemplateEntriesSQL);

                        $deleteShippingTemplateUpgradesSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_upgrades SET delete_flag = '1' WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "'";
                        Db::getInstance()->execute($deleteShippingTemplateUpgradesSQL);

                        //Audit Log Entry
                        $auditLogEntryString = 'Shipping Template - <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> Deleted';
                        $auditMethodName = 'AdminEtsyShippingTemplates::processDelete()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyConf=15');
                    } else {
                        //Audit Log Entry
                        $auditLogEntryString = 'Deletion of Shipping Template - <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> Failed';
                        $auditMethodName = 'AdminEtsyShippingTemplates::processDelete()';
                        EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                        Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyError=18');
                    }
                }
            } else {
                //Audit Log Entry
                $auditLogEntryString = 'Deletion of Shipping Template - <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> Failed';
                $auditMethodName = 'AdminEtsyShippingTemplates::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplates') . '&etsyError=27');
            }
        }
    }

    /** Display view action link */
    public function displayViewLink($token = null, $id = null, $name = null)
    {

        if (!array_key_exists('View', self::$cache_lang)) {
            self::$cache_lang['View'] = $this->module->l('View Shipping Entries', 'Helper');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . $id,
            'action' => $this->l('View Shipping Entries'),
            'icon' => 'search-plus'
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/list/list_action.tpl');
    }

    public function displayViewUpgradeLink($token = null, $id = null, $name = null)
    {

        if (!array_key_exists('ViewUpgrade', self::$cache_lang)) {
            self::$cache_lang['ViewUpgrade'] = $this->l('View Shipping Upgrades', 'Helper');
        }

        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminlink('AdminEtsyShippingUpgrades') . '&id_etsy_shipping_templates=' . $id,
            'action' => $this->l('View Shipping Upgrades'),
            'icon' => 'search-plus'
        ));

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
        if (!Tools::getValue('id_etsy_shipping_templates') && !Tools::isSubmit('addetsy_shipping_templates')) {
            $this->page_header_toolbar_btn['new_template'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add new'),
                'icon' => 'process-icon-new'
            );
            $secure_key = Configuration::get('KBETSY_SECURE_KEY');
            $this->page_header_toolbar_btn['kb_sync_templates'] = array(
                'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncShippingTemplates', 'secure_key' => $secure_key)),
                'target'=> '_blank',
                'desc' => $this->l('Sync Shipping Templates'),
                'icon' => 'process-icon-update'
            );
            /*
            $this->page_header_toolbar_btn['kb_sync_country_region'] = array(
                'href' => $this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'syncCountriesRegions', 'secure_key' => $secure_key)),
                'target'=> '_blank',
                'desc' => $this->l('Sync Country/Region'),
                'icon' => 'process-icon-update'
            );
            */
        }
        if (Tools::getValue('id_etsy_shipping_templates') || Tools::isSubmit('id_etsy_shipping_templates') || Tools::isSubmit('addetsy_shipping_templates')) {
            $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Cancel'),
                'icon' => 'process-icon-cancel'
            );
        }

        parent::initPageHeaderToolbar();
    }
}
