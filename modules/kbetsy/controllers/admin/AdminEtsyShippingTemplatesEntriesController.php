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
require_once(_PS_MODULE_DIR_ . 'kbetsy/classes/EtsyShippingTemplatesEntries.php');

class AdminEtsyShippingTemplatesEntriesController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyShippingTemplatesEntries';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'etsy_shipping_templates_entries';
        $this->className = 'EtsyShippingTemplatesEntries';
        $this->identifier = 'id_etsy_shipping_templates_entries';

        parent::__construct();
        $this->fields_list = array(
            'id_etsy_shipping_templates_entries' => array(
                'title' => $this->module->l('ID', 'AdminEtsyShippingTemplatesEntriesController'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'shipping_template_title' => array(
                'title' => $this->module->l('Shipping Template Title', 'AdminEtsyShippingTemplatesEntriesController'),
            ),
            'shipping_origin_country' => array(
                'title' => $this->module->l('Shipping Origin Country', 'AdminEtsyShippingTemplatesEntriesController'),
            ),
            'country_name' => array(
                'title' => $this->module->l('Shipping Destination Country', 'AdminEtsyShippingTemplatesEntriesController'),
            ),
            'region_name' => array(
                'title' => $this->module->l('Shipping Destination Region', 'AdminEtsyShippingTemplatesEntriesController'),
            )
        );

        $this->_select = 'st.shipping_template_title, st.shipping_origin_country, r.region_name, c.country_name ';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'etsy_shipping_templates` st ON (a.id_etsy_shipping_templates = st.id_etsy_shipping_templates)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'etsy_regions` r ON (a.shipping_entry_destination_region_id = r.region_id)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'etsy_countries` c ON (a.shipping_entry_destination_country_id = c.country_id) ';
        $this->_where = " = 1 AND a.delete_flag = '0'";
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $this->_where .= " AND a.id_etsy_shipping_templates = " . Tools::getValue('id_etsy_shipping_templates');
            $this->list_simple_header = true;
        }
        $this->_orderBy = 'id_etsy_shipping_templates_entries';
        $this->_orderWay = 'ASC';
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

    public function initToolbar()
    {
        parent::initToolbar();

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
            $this->toolbar_btn['new'] = array(
                'desc' => $this->module->l('Add New', 'AdminEtsyShippingTemplatesEntriesController'),
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&addetsy_shipping_templates_entries&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates')
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

        //Prepare array of Regions
        $regionsList = array();
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

        //Prepare array of detination types
        $destinationTypes = array(
            array(
                'id_option' => '1',
                'name' => 'Country'
            ),
            array(
                'id_option' => '2',
                'name' => 'Region'
            )
        );
        $disabled = false;
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
            $disabled = true;
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries'))) ? $this->module->l('Update Shipping Template Entry', 'AdminEtsyShippingTemplatesEntriesController') : $this->module->l('Add New Shipping Template Entry', 'AdminEtsyShippingTemplatesEntriesController'),
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
                    'type' => 'hidden',
                    'name' => 'id_etsy_shipping_templates_entries'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Shipping Template Title', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('This is Shipping Template Title', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_template_title',
                    'maxlength' => 255,
                    'required' => true,
                    'disabled' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Origin Country', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('This is an origin country of Shipment', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_origin_country_id',
                    'required' => true,
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
                    'type' => 'select',
                    'label' => $this->module->l('Destination Type', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('Choose a destination type as country or region', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'destination_type',
                    'required' => true,
                    'options' => array(
                        'query' => $destinationTypes,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'onchange' => 'switchEntryDestinationTypes(this.value)',
                    'disabled' => $disabled
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Destination Country', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('Choose a country as a destination country of Shipment', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_entry_destination_country_id',
                    'required' => true,
                    'options' => array(
                        'query' => $countriesList,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'onchange' => 'setEntryDestinationCountry()',
                    'disabled' => $disabled
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'shipping_entry_destination_country'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Destination Region', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('Choose a region as a destination region of Shipment', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_entry_destination_region_id',
                    'required' => true,
                    'options' => array(
                        'query' => $regionsList,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'onchange' => 'setEntryDestinationRegion()',
                    'disabled' => $disabled
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'shipping_entry_destination_region'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Primary Cost', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('Provide shipping primary cost for a shipment', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_entry_primary_cost',
                    'class' => 'velsof_number_field',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Secondary Cost', 'AdminEtsyShippingTemplatesEntriesController'),
                    'desc' => $this->module->l('Provide shipping cost for each additional item', 'AdminEtsyShippingTemplatesEntriesController'),
                    'name' => 'shipping_entry_secondary_cost',
                    'class' => 'velsof_number_field',
                    'required' => true
                )
            ),
            'buttons' => array(
                array(
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submit' . $this->name,
                    'js' => "validation('etsy_shipping_templates_entries_form')",
                    'title' => $this->module->l('Save', 'AdminEtsyShippingTemplatesEntriesController'),
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
        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
            $getShippingTemplateEntriesDetails = EtsyShippingTemplatesEntries::getShippingTemplateEntryDetails(Tools::getValue('id_etsy_shipping_templates_entries'));

            if (isset($getShippingTemplateEntriesDetails)) {
                $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails($getShippingTemplateEntriesDetails[0]['id_etsy_shipping_templates']);

                $this->fields_value = array(
                    'id_etsy_shipping_templates_entries' => Tools::getValue('id_etsy_shipping_templates_entries'),
                    'id_etsy_shipping_templates' => $getShippingTemplateDetails[0]['id_etsy_shipping_templates'],
                    'shipping_template_title' => $getShippingTemplateDetails[0]['shipping_template_title'],
                    'shipping_origin_country_id' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                    'shipping_origin_country' => $getShippingTemplateDetails[0]['shipping_origin_country'],
                    'shipping_origin_country_id_hidden' => $getShippingTemplateDetails[0]['shipping_origin_country_id'],
                    'shipping_entry_destination_country_id' => $getShippingTemplateEntriesDetails[0]['shipping_entry_destination_country_id'],
                    'shipping_entry_destination_country' => $getShippingTemplateEntriesDetails[0]['shipping_entry_destination_country'],
                    'shipping_entry_destination_region_id' => $getShippingTemplateEntriesDetails[0]['shipping_entry_destination_region_id'],
                    'shipping_entry_destination_region' => $getShippingTemplateEntriesDetails[0]['shipping_entry_destination_region'],
                    'shipping_entry_primary_cost' => $getShippingTemplateEntriesDetails[0]['shipping_entry_primary_cost'],
                    'shipping_entry_secondary_cost' => $getShippingTemplateEntriesDetails[0]['shipping_entry_secondary_cost'],
                    'destination_type' => 1
                );

                if ($getShippingTemplateEntriesDetails[0]['shipping_entry_destination_country_id'] == null || $getShippingTemplateEntriesDetails[0]['shipping_entry_destination_country_id'] == 0) {
                    $this->fields_value['destination_type'] = '2';
                }
            }
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        //Handle Form Submission
        if (Tools::isSubmit('submitAddetsy_shipping_templates_entries')) {
            $formError = 0;
            $customErrors = array();

            //Prepare variables holding  post values
            $shippingOriginCountryID = Tools::getValue('shipping_origin_country_id_hidden');
            $shippingPrimaryCost = Tools::getValue('shipping_entry_primary_cost');
            $shippingSecondaryCost = Tools::getValue('shipping_entry_secondary_cost');

            if (Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
//            if (true) {
                $shippingDestinationCountryID = '';
                $shippingDestinationCountryName = '';
                $shippingDestinationRegionID = '';
                $shippingDestinationRegionName = '';


                if (!Tools::isEmpty(trim(Tools::getValue('destination_type')))) {
                    if (Tools::getValue('destination_type') == '1') {
                        $shippingDestinationCountryID = Tools::getValue('shipping_entry_destination_country_id');
                        $shippingDestinationCountryName = Tools::getValue('shipping_entry_destination_country');
                    } else {
                        $shippingDestinationRegionID = Tools::getValue('shipping_entry_destination_region_id');
                        $shippingDestinationRegionName = Tools::getValue('shipping_entry_destination_region');
                    }
                }
                //Validate Origin Country
                if (empty($shippingOriginCountryID)) {
                    $formError = 1;
                    $customErrors[] = 19;
                }
                //Validate Destination Country
                if (empty($shippingDestinationCountryID) && Tools::getValue('destination_type') == '1') {
                    $formError = 1;
                    $customErrors[] = 20;
                }
                //Validate Destination Region
                if (empty($shippingDestinationRegionID) && Tools::getValue('destination_type') == '2') {
                    $formError = 1;
                    $customErrors[] = 22;
                }
            }
            //Validate Primary Cost
            if (($shippingPrimaryCost != 0) && empty($shippingPrimaryCost)) {
                $formError = 1;
                $customErrors[] = 23;
            }
            //Validate Secondary Cost
            if (($shippingSecondaryCost != 0) && empty($shippingSecondaryCost)) {
                $formError = 1;
                $customErrors[] = 24;
            }

            if (!$formError && !Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
                if (Tools::getValue('destination_type') == '1') {
                    if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
                        //SQL to check details existence
//                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "' AND shipping_entry_destination_country_id = '" . (int) $shippingDestinationCountryID . "' AND delete_flag = '0' AND id_etsy_shipping_templates_entries != '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
//                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
//
//                        if ($dataExistenceResult[0]['count'] == 0) {
                            //Update data SQL
                        $updateShippingTemplateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET  shipping_entry_primary_cost = '" . (float) $shippingPrimaryCost . "', shipping_entry_secondary_cost = '" . (float) $shippingSecondaryCost . "',  renew_flag = '1' WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
                        if (Db::getInstance()->execute($updateShippingTemplateSQL)) {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Template Entry Updated. Updated values are - <br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=16');
                        }
//                        } else {
//                            //Audit Log Entry
//                            $auditLogEntryString = 'Shipping Template Entry Could not be Updated as already exists';
//                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
//                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
//
//                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=25');
//                        }
                    } else {
                        //SQL to check details existence
                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "' AND shipping_entry_destination_country_id = '" . (int) $shippingDestinationCountryID . "' AND delete_flag = '0'";
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);

                        if ($dataExistenceResult[0]['count'] == 0) {
                            //Insert data SQL
                            $addShippingTemplateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES (NULL, '" . (int) Tools::getValue('id_etsy_shipping_templates') . "', NULL, '" . (int) $shippingDestinationCountryID . "', '" . pSQL($shippingDestinationCountryName) . "', '" . (float) $shippingPrimaryCost . "', '" . (float) $shippingSecondaryCost . "', NULL, NULL, '0', '0', NOW(), NOW())";
                            if (Db::getInstance()->execute($addShippingTemplateSQL)) {
                                //Audit Log Entry
                                $auditLogEntryString = 'Shipping Template Entry Added. Added values are - <br>Shipping Destination Country: ' . $shippingDestinationCountryName . '<br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                                $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=17');
                            }
                        } else {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Template Entry Could not be Added as already exists';
                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=25');
                        }
                    }
                } else {
                    if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
                        //SQL to check details existence
//                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "' AND shipping_entry_destination_region_id = '" . (int) $shippingDestinationRegionID . "' AND delete_flag = '0' AND id_etsy_shipping_templates_entries != '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
//                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);
//
//                        if ($dataExistenceResult[0]['count'] == 0) {
                            //Update data SQL
                        $updateShippingTemplateSQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET  shipping_entry_primary_cost = '" . (float) $shippingPrimaryCost . "', shipping_entry_secondary_cost = '" . (float) $shippingSecondaryCost . "',  renew_flag = '1' WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
                        if (Db::getInstance()->execute($updateShippingTemplateSQL)) {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Template Entry Updated. Updated values are - <br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=16');
                        }
//                        } else {
//                            //Audit Log Entry
//                            $auditLogEntryString = 'Shipping Template Entry Could not be Updated as already exists';
//                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
//                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);
//
//                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=25');
//                        }
                    } else {
                        //SQL to check details existence
                        $selectSQL = "SELECT count(*) as count FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates = '" . (int) Tools::getValue('id_etsy_shipping_templates') . "' AND shipping_entry_destination_region_id = '" . (int) $shippingDestinationRegionID . "' AND delete_flag = '0'";
                        $dataExistenceResult = Db::getInstance()->executeS($selectSQL, true, false);

                        if ($dataExistenceResult[0]['count'] == 0) {
                            //Insert data SQL
                            $addShippingTemplateSQL = "INSERT INTO " . _DB_PREFIX_ . "etsy_shipping_templates_entries VALUES (NULL, '" . (int) Tools::getValue('id_etsy_shipping_templates') . "', NULL, NULL, NULL, '" . (float) $shippingPrimaryCost . "', '" . (float) $shippingSecondaryCost . "', '" . (int) $shippingDestinationRegionID . "', '" . pSQL($shippingDestinationRegionName) . "', '0', '0', NOW(), NOW())";
                            if (Db::getInstance()->execute($addShippingTemplateSQL)) {
                                //Audit Log Entry
                                $auditLogEntryString = 'Shipping Template Entry Added. Added values are - <br>Shipping Destination Region: ' . $shippingDestinationRegionName . '<br>Shipping Primary Cost: ' . $shippingPrimaryCost . '<br>Shipping Secondary Cost: ' . $shippingSecondaryCost;
                                $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=17');
                            }
                        } else {
                            //Audit Log Entry
                            $auditLogEntryString = 'Shipping Template Entry Could not be Added as already exists';
                            $auditMethodName = 'AdminEtsyShippingTemplatesEntries::postProcess()';
                            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=25');
                        }
                    }
                }
            } else {
                if (empty($customErrors)) {
                    $customErrors[] = 9;
                }
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=' . implode(",", $customErrors));
            }
        } else {
            parent::postProcess();
        }
        $this->content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');
    }

    //Function definition to delete Shipping Template Entry
    public function processDelete()
    {
        //parent::processDelete();

        if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates_entries')))) {
            //Get Shipping Template Entry Details
            $getShippingTemplateEntryDetails = EtsyShippingTemplatesEntries::getShippingTemplateEntryDetails(Tools::getValue('id_etsy_shipping_templates_entries'));

            //Get Shipping Template Details
            $getShippingTemplateDetails = EtsyShippingTemplates::getShippingTemplateDetails($getShippingTemplateEntryDetails[0]['id_etsy_shipping_templates']);

            $destination = !empty($getShippingTemplateEntryDetails[0]['shipping_entry_destination_country']) ? $getShippingTemplateEntryDetails[0]['shipping_entry_destination_country'] : $getShippingTemplateEntryDetails[0]['shipping_entry_destination_region'];

            if (is_null($getShippingTemplateEntryDetails[0]['shipping_template_entry_id'])) {
                $deleteShippingTemplateEntrySQL = "DELETE FROM " . _DB_PREFIX_ . "etsy_shipping_templates_entries WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
            } else {
                $deleteShippingTemplateEntrySQL = "UPDATE " . _DB_PREFIX_ . "etsy_shipping_templates_entries SET delete_flag = '1' WHERE id_etsy_shipping_templates_entries = '" . (int) Tools::getValue('id_etsy_shipping_templates_entries') . "'";
            }

            if (Db::getInstance()->execute($deleteShippingTemplateEntrySQL)) {
                //Audit Log Entry
                $auditLogEntryString = 'Shipping Template Entry of <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> for destination <b>' . $destination . '</b> Deleted';
                $auditMethodName = 'AdminEtsyShippingTemplatesEntries::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyConf=18');
            } else {
                //Audit Log Entry
                $auditLogEntryString = 'Deletion of Shipping Template Entry of <b>' . $getShippingTemplateDetails[0]['shipping_template_title'] . '</b> for destination <b>' . $destination . '</b> Failed';
                $auditMethodName = 'AdminEtsyShippingTemplatesEntries::processDelete()';
                EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates') . '&etsyError=26');
            }
        }
    }

    /**
     * Display delete action link
     */
    public function displayDeleteLink($token = null, $id = null, $name = null)
    {

        if (!array_key_exists('Delete', self::$cache_lang)) {
            self::$cache_lang['Delete'] = $this->module->l('Delete', 'Helper');
        }

        if (!empty($id)) {
            $getShippingTemplateEntryDetails = EtsyShippingTemplatesEntries::getShippingTemplateEntryDetails($id);

            $this->context->smarty->assign(array(
                'confirm' => 'Arey you sure to delete selected object ?',
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . $getShippingTemplateEntryDetails[0]['id_etsy_shipping_templates'] . '&id_etsy_shipping_templates_entries=' . $id . '&deleteetsy_shipping_templates_entries',
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
            self::$cache_lang['Edit'] = $this->module->l('Edit', 'Helper');
        }

        if (!empty($id)) {
            $getShippingTemplateEntryDetails = EtsyShippingTemplatesEntries::getShippingTemplateEntryDetails($id);

            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&id_etsy_shipping_templates=' . $getShippingTemplateEntryDetails[0]['id_etsy_shipping_templates'] . '&id_etsy_shipping_templates_entries=' . $id . '&updateetsy_shipping_templates_entries',
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
        if (!Tools::getValue('id_etsy_shipping_templates_entries') && !Tools::isSubmit('addetsy_shipping_templates_entries')) {
            if (!Tools::isEmpty(trim(Tools::getValue('id_etsy_shipping_templates')))) {
                $this->page_header_toolbar_btn['new_template'] = array(
                    'href' => $this->context->link->getAdminlink('AdminEtsyShippingTemplatesEntries') . '&addetsy_shipping_templates_entries&id_etsy_shipping_templates=' . Tools::getValue('id_etsy_shipping_templates'),
                    'desc' => $this->l('Add new'),
                    'icon' => 'process-icon-new'
                );
            }
        }

        if (Tools::getValue('id_etsy_shipping_templates_entries') || Tools::isSubmit('id_etsy_shipping_templates_entries') || Tools::isSubmit('addetsy_shipping_templates_entries')) {
            $this->page_header_toolbar_btn['kb_cancel_action'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Cancel'),
                'icon' => 'process-icon-cancel'
            );
        }

        parent::initPageHeaderToolbar();
    }
}
