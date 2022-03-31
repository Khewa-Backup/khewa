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

class AdminEtsyGeneralSettingsController extends ModuleAdminController
{

    //Class Constructor
    public function __construct()
    {
        $this->name = 'EtsyGeneralSettings';
        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();

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

    //Function definition to render a form
    public function initContent()
    {
        $lang_arr = array();
        $etsycurrency = array();
        
        $etsy_carrier_option = $this->getEtsyCarrierList();
        
        $langs = Language::getLanguages();
        $etsy_lang_arr = explode(',', 'de,en,es,fr,it,ja,nl,pt,ru,pl');
        foreach ($langs as $lang) {
            if (in_array($lang['iso_code'], $etsy_lang_arr)) {
                $lang_arr[] = array(
                    'id_lang' => $lang['id_lang'],
                    'lang_name' => $lang['name']
                );
            }
        }
        $desc_type_arr = array(
            array(
                'desc_type' => 'short',
                'desc_name' => $this->l('Short Description')
            ),
            array(
                'desc_type' => 'long',
                'desc_name' => $this->l('Long Descritpion')
            ),
            array(
                'desc_type' => 'both',
                'desc_name' => $this->l('Both Short & Long Descritpion')
            ),
        );

        //Buttons List
        $etsyOAuthAccessToken = Configuration::get('etsy_oauth_access_token');
        if (!empty($etsyOAuthAccessToken)) {
            $buttonsList = array(
                'class' => 'btn btn-default pull-right',
                'name' => 'disconnect_btn',
                'id' => 'disconnect_btn',
                'js' => "disconnect()",
                'title' => $this->l('Disconnect'),
                'icon' => 'process-icon-ban'
            );
        } else {
            $buttonsList = array(
                'class' => 'btn btn-default pull-right',
                'name' => 'submit_gs_btn',
                'id' => 'submit_gs_btn',
                'js' => "validation('configuration_form')",
                'title' => $this->l('Connect'),
                'icon' => 'process-icon-refresh'
            );
        }

        $store_currencies = Currency::getCurrenciesByIdShop(Context::getContext()->shop->id);
        $etsycurrency[] = array("id_option" => "", "name" => $this->l('Select Etsy Default Currency'));
        foreach ($store_currencies as $currency) {
            $etsycurrency[] = array(
                'id_option' => $currency['iso_code'],
                'name' => $currency['name']
            );
        }

        $orderStatuses = array();
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $orderStatuses[] = array(
                'id_option' => $status['id_order_state'],
                'name' => $status['name']
            );
        }
        
        /*
         * cjhanges by rishabh jain for order carrier
         */
        
        $shipping_methods = Carrier::getCarriers($this->context->language->id, true, false, false, null, 5);
        $i = 0;
        $option1 = array();
        foreach ($shipping_methods as $shipping_options) {
            $option1[$i]['id_reference'] = $shipping_options['id_reference'];
            $option1[$i]['name'] = $shipping_options['name'];
            $i++;
        }
        /*
         * changes over
         */
        $desc = '';
        if (Language::getIdByIso('gb')) {
            $desc = '</br>'.$this->l('ETSY does not support ENGLISH(GB).');
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'disconnect_url'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'etsy_api_user_id'
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'savebtn'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'etsy_switch_value',
                        'desc' => $this->l('Toggle to enable or disable module'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'switch_value_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'switch_value_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Etsy Currency', 'AdminEtsyProfileManagementController'),
                        'desc' => $this->module->l('This currency should be same as the currency of your Etsy account. You can check the Etsy currecy in Finances > Payment Settings > Currency. In case this currency is different, Wrong price will be synced on Etsy', 'AdminEtsyProfileManagementController'),
                        'name' => 'etsy_currency',
                        'required' => true,
                        'options' => array(
                            'query' => $etsycurrency,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Etsy API Key String'),
                        'desc' => $this->l('API Key required to setup connection between store and Etsy Marketplace'),
                        'name' => 'etsy_api_key',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Etsy API Shared Secret'),
                        'desc' => $this->l('API Secret required to setup connection between store and Etsy Marketplace'),
                        'name' => 'etsy_api_secret',
                        'required' => true
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Etsy API Host'),
                        'desc' => $this->l('API Host to send request'),
                        'name' => 'etsy_api_host',
                        'required' => true,
                        'value' => 'https://openapi.etsy.com/',
                        'readonly' => true
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Etsy API Version'),
                        'desc' => $this->l('API Version to access Etsy Marketplace library functions'),
                        'name' => 'etsy_api_version',
                        'required' => true,
                        'value' => 'v2',
                        'readonly' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Map Etsy Orders with store carrier'),
                        'name' => 'map_etsy_order_store_carrier',
                        'desc' => $this->l('If enabled then the imported orders will be mapped with the select carrier.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'switch_value_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'switch_value_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'select',
                            'label' => $this->l('Select Shipping Methods for etsy orders'), // The <label> for this <select> tag.
                            'id' => 'KB_SHIPPING_METHOD_ETSY_ORDER',
                            'name' => 'KB_SHIPPING_METHOD_ETSY_ORDER', // The content of the 'id' attribute of the <select> tag.
                            'class' => 'fixed-width-xxl',
                            'options' => array(
                                'query' => $option1,
                                'id' => 'id_reference',
                                'name' => 'name'
                            )
                        ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Etsy Default Language'),
                        'name' => 'etsy_default_lang',
                        'required' => true,
                        'desc' => $this->l('It should be same as the default language of your Etsy store.').$desc,
                        'value' => Configuration::get('etsy_default_lang'),
                        'options' => array(
                            'query' => $lang_arr,
                            'id' => 'id_lang',
                            'name' => 'lang_name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Language(s) to Sync'),
                        'name' => 'etsy_sync_lang[]',
                        'desc' => $this->l('Except the language selected in the above dropdown.').$desc,
                        'required' => true,
                        'multiple' => true,
                        'options' => array(
                            'query' => $lang_arr,
                            'id' => 'id_lang',
                            'name' => 'lang_name'
                        )
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Minimum Threshold Quantity'),
                        'desc' => $this->l('If inventory goes below the threshold value then the inventory status of these products under the products tab will display the Inventory Status as Critical.'),
                        'name' => 'min_threshold_quant',
                        'required' => true
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select Description to Sync On Etsy'),
                        'name' => 'etsy_desc_type',
                        'required' => true,
                        'options' => array(
                            'query' => $desc_type_arr,
                            'id' => 'desc_type',
                            'name' => 'desc_name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Upload Order Tracking Number On ETSY'),
                        'name' => 'upload_tracking_number',
                        'desc' => $this->l('If enabled then tracking number will be uploaded on the etsy.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'switch_value_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'switch_value_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Etsy Carrier Name for Tracking Url', 'AdminEtsyOrderSettingsController'),
                        'desc' => $this->module->l('Choose the carrier used for tracking etsy order', 'AdminEtsyOrderSettingsController'),
                        'name' => 'etsy_selected_shipment_name',
//                        'class' => 'fixed-width-xxl',
                        'required' => true,
                        'class' => 'fixed-width-xxl',
                        'options' => array(
                            'query' => $etsy_carrier_option,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Order Default Status', 'AdminEtsyOrderSettingsController'),
                        'desc' => $this->module->l('Choose the status of the orders imported from Etsy to PrestaShop', 'AdminEtsyOrderSettingsController'),
                        'name' => 'etsy_order_default_status',
                        'required' => true,
                        'options' => array(
                            'query' => $orderStatuses,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Order Unpaid Status', 'AdminEtsyOrderSettingsController'),
                        'desc' => $this->module->l('Choose the status of the unpaid orders imported from Etsy to PrestaShop', 'AdminEtsyOrderSettingsController'),
                        'name' => 'etsy_order_unpaid_status',
                        'required' => true,
                        'options' => array(
                            'query' => $orderStatuses,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Order Shipped Status', 'AdminEtsyOrderSettingsController'),
                        'desc' => $this->module->l('If order status is set to these status on PrestaShop, Order will be marked as Shipped on Etsy', 'AdminEtsyOrderSettingsController'),
                        'name' => 'etsy_order_shipped_status[]',
                        'multiple' => true,
                        'required' => true,
                        'options' => array(
                            'query' => $orderStatuses,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show taxes in Etsy Order Invoice'),
                        'name' => 'etsy_order_tax',
                        'desc' => $this->l('If enabled then the tax breakup will be shown in etsy orders.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'switch_value_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'switch_value_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('No of Item Sync Per CRON Run'),
                        'desc' => $this->l('The number of item will be synced on each CRON run. In case of script timeout while running the CRON URL, Try reducing the number.'),
                        'name' => 'etsy_sync_item',
                        'required' => true
                    ),
                ),
                'buttons' => array(
                    $buttonsList,
                    array(
                        'class' => 'btn btn-default pull-right',
                        'name' => 'submit_gs_btn2',
                        'id' => 'submit_gs_btn2',
                        'js' => "validation('saveonly_configuration_form')",
                        'title' => $this->l('Save'),
                        'icon' => 'process-icon-save'
                    )
                )
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = $this->context->language->id;

        //Load Current Value
        $helper->fields_value['disconnect_url'] = $this->context->link->getAdminlink('AdminEtsyGeneralSettings') . '&action=disconnect';
        $helper->fields_value['etsy_switch_value'] = Configuration::get('etsy_switch_value');
        if (Configuration::get('KBETSY_DEMO')) {
            $helper->fields_value['etsy_api_key'] = 'le3tvh63sd23sd4d8xms7k49';
            $helper->fields_value['etsy_api_secret'] = '5kvsdl1su3lt5';
        } else {
            $helper->fields_value['etsy_api_key'] = Configuration::get('etsy_api_key');
            $helper->fields_value['etsy_api_secret'] = Configuration::get('etsy_api_secret');
        }

        $helper->fields_value['etsy_currency'] = Configuration::get('etsy_currency');
        $helper->fields_value['etsy_api_host'] = 'https://openapi.etsy.com/';
        $helper->fields_value['etsy_api_version'] = 'v2';
        $helper->fields_value['etsy_api_user_id'] = Configuration::get('etsy_api_user_id');
        $helper->fields_value['etsy_default_lang'] = Configuration::get('etsy_default_lang');
        
        $helper->fields_value['KB_SHIPPING_METHOD_ETSY_ORDER'] = Configuration::get('KB_SHIPPING_METHOD_ETSY_ORDER');
        
        
        $helper->fields_value['min_threshold_quant'] = Configuration::get('min_threshold_quant') != "" ? Configuration::get('min_threshold_quant') : 0;
        $helper->fields_value['etsy_desc_type'] = Configuration::get('etsy_desc_type');
        $lang_ids = explode(',', Configuration::get('etsy_sync_lang'));
        $helper->fields_value['etsy_sync_lang[]'] = $lang_ids;

        $helper->fields_value['etsy_order_default_status'] = Configuration::get('etsy_order_default_status');
        
        $helper->fields_value['etsy_selected_shipment_name'] = Configuration::get('etsy_selected_shipment_name');
        
        $helper->fields_value['upload_tracking_number'] = Configuration::get('upload_tracking_number');
        
        $helper->fields_value['map_etsy_order_store_carrier'] = Configuration::get('map_etsy_order_store_carrier');
        
        $helper->fields_value['etsy_order_tax'] = Configuration::get('etsy_order_tax');
        
        $helper->fields_value['etsy_order_unpaid_status'] = Configuration::get('etsy_order_unpaid_status');
        $helper->fields_value['etsy_order_shipped_status[]'] = explode(",", Configuration::get('etsy_order_shipped_status'));
        $helper->fields_value['etsy_sync_item'] = Configuration::get('etsy_sync_item') != "" ? Configuration::get('etsy_sync_item') : 20;
        $helper->fields_value['savebtn'] = '';

        $this->content .= $helper->generateForm(array($fields_form));
        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'kbetsy/views/templates/admin/velovalidation.tpl');

        parent::initContent();
        if (!Tools::isEmpty(trim(Tools::getValue('action'))) && Tools::getValue('action') == 'disconnect') {
            EtsyModule::disconnect();
            Context::getContext()->cookie->__set('redirectAdminLink', '');
            Configuration::updateGlobalValue('etsy_oauth_access_token', null);

            //Audit Log Entry
            $auditLogEntryString = 'Disconnected Successfully';
            $auditMethodName = 'AdminEtsyGeneralSettings::initContent()';
            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

            Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyGeneralSettings') . '&etsyConf=2');
        }
    }

    // function to fetch etsy carrier array list for tracking
    public function getEtsyCarrierList()
    {
        $etsy_carrier_array = array(
            '4PX Worldwide Express' => '4px',
            'ABF Freight' => 'abf',
            'ACS Courier' => 'acscourier',
            'APC Postal Logistics' => 'apc',
            'AeroFlash' => 'aeroflash',
            'An Post' => 'an-post',
            'Aramex' => 'aramex',
            'Asendia UK' => 'asendia-uk',
            'Asendia USA' => 'asendia-usa',
            'Australia Post' => 'australia-post',
            'Austrian Post' => 'austrian-post',
            'Austrian Post Registered' => 'austrian-post-registered',
            'Belgium Post Domestic' => 'bpost',
            'Belgium Post International' => 'bpost-international',
            'Belposhta' => 'belpost',
            'Blue Dart' => 'bluedart',
            'Bulgarian Posts' => 'bgpost',
            'Cambodia Post' => 'cambodia-post',
            'Canada Post' => 'canada-post',
            'Canpar Courier' => 'canpar',
            'Ceska Posta' => 'ceska-posta',
            'China EMS' => 'china-ems',
            'China Post' => 'china-post',
            'Chit Chats' => 'chitchats',
            'Chronopost France' => 'chronopost-france',
            'Chronopost Portugal' => 'chronopost-portugal',
            'Chunghwa Post' => 'taiwan-post',
            'City Link' => 'city-link',
            'Colissimo' => 'colissimo',
            'Collect+' => 'collectplus',
            'Correios de Brasil' => 'brazil-correios',
            'Correios de Portugal (CTT)' => 'portugal-ctt',
            'Correo Argentino Domestic' => 'correo-argentino',
            'Correo Argentino International' => 'correo-argentino-intl',
            'Correos - Espana' => 'spain-correos-es',
            'Correos Chile' => 'correos-chile',
            'Correos De Mexico' => 'correos-de-mexico',
            'Correos de Costa Rica' => 'correos-de-costa-rica',
            'Courier Post' => 'courierpost',
            'Couriers Please' => 'couriers-please',
            'Cyprus Post' => 'cyprus-post',
            'DHL Benelux' => 'dhl-benelux',
            'DHL Express' => 'dhl',
            'DHL Germany' => 'dhl-germany',
            'DHL Global Mail' => 'dhl-global-mail',
            'DHL Global Mail Asia' => 'dhl-global-mail-asia',
            'DHL Netherlands' => 'dhl-nl',
            'DHL Parcel NL' => 'dhlparcel-nl',
            'DHL Polska' => 'dhl-poland',
            'DHL Spain Domestic' => 'dhl-es',
            'DPD' => 'dpd',
            'DPD Germany' => 'dpd-de',
            'DPD Polska' => 'dpd-poland',
            'DPD UK' => 'dpd-uk',
            'DTDC India' => 'dtdc',
            'Deltec Courier' => 'deltec-courier',
            'Deutsche Post' => 'deutsch-post',
            'Direct Link' => 'directlink',
            'EC-Firstclass' => 'ec-firstclass',
            'Elta Courier' => 'elta-courier',
            'Empost' => 'emirates-post',
            'Estafeta' => 'estafeta',
            'Estes' => 'estes',
            'Fastway Australia' => 'fastway-au',
            'Fastway Couriers' => 'fastway-ireland',
            'Fastway New Zealand' => 'fastway-nz',
            'Fastways Couriers South Africa' => 'fastway-za',
            'FedEx' => 'fedex',
            'Fedex UK (Domestic)' => 'fedex-uk',
            'First Flight Couriers' => 'first-flight',
            'Flash Courier' => 'flash-courier',
            'GATI-KWE' => 'gati-kwe',
            'GLS' => 'gls',
            'Globegistics' => 'globegistics',
            'Greyhound' => 'greyhound',
            'Hermes' => 'hermes-de',
            'Hermes Italy' => 'hermes-it',
            'Hermes UK' => 'hermes',
            'Hong Kong Post' => 'hong-kong-post',
            'Hrvatska Posta' => 'hrvatska-posta',
            'India Post' => 'india-post',
            'India Post International' => 'india-post-int',
            'Interlink Express' => 'interlink-express',
            'International Seur' => 'international-seur',
            'Israel Post' => 'israel-post',
            'Israel Post Domestic' => 'israel-post-domestic',
            'Japan Post' => 'japan-post',
            'Korea Post' => 'kpost',
            'Korea Post EMS' => 'korea-post',
            'Kuehne + Nagel' => 'kn',
            'La Poste' => 'la-poste-colissimo',
            'Landmark Global' => 'landmark-global',
            'LaserShip' => 'lasership',
            'Lietuvos Pastas' => 'lietuvos-pastas',
            'MRW' => 'mrw-spain',
            'Magyar Posta' => 'magyar-posta',
            'Malaysia Pos Daftar' => 'malaysia-post-posdaftar',
            'Mondial Relay' => 'mondialrelay',
            'Multipack' => 'mexico-multipack',
            'Nacex' => 'nacex-spain',
            'New Zealand Post' => 'new-zealand-post',
            'Nexive' => 'tntpost-it',
            'Nigerian Postal Service' => 'nipost',
            'Nova Poshta' => 'nova-poshta',
            'OCA' => 'oca-ar',
            'OPEK' => 'opek',
            'OnTrac' => 'ontrac',
            'PTT Posta' => 'ptt-posta',
            'Parcelforce Worldwide' => 'parcel-force',
            'Poczta Polska' => 'poczta-polska',
            'Pos Indonesia' => 'pos-indonesia',
            'Pos Indonesia International' => 'pos-indonesia-int',
            'Pos Malaysia' => 'malaysia-post',
            'PostNL Domestic' => 'postnl',
            'PostNL International' => 'postnl-international',
            'PostNL International 3S' => 'postnl-3s',
            'PostNord' => 'danmark-post',
            'PostNord Logistics' => 'postnord',
            'Posta Romana' => 'posta-romana',
            'Poste Italiane' => 'poste-italiane',
            'Poste Italiane Paccocelere' => 'poste-italiane-paccocelere',
            'Posten AB' => 'sweden-posten',
            'Posten Norge' => 'posten-norge',
            'Posti' => 'posti',
            'Postmates' => 'postmates',
            'Purolator' => 'purolator',
            'RL Carriers' => 'rl-carriers',
            'RPX Indonesia' => 'rpx',
            'Red Express' => 'red-express',
            'Redpack' => 'mexico-redpack',
            'Russian Post' => 'russian-post',
            'S.F International' => 'sfb2c',
            'SDA Express Courier' => 'italy-sda',
            'SEUR Espana (Domestico)' => 'spanish-seur',
            'SEUR Portugal (Domestico)' => 'portugal-seur',
            'SF Express' => 'sf-express',
            'Safexpress' => 'safexpress',
            'Sagawa' => 'sagawa',
            'Saudi Post' => 'saudi-post',
            'Selektvracht' => 'selektvracht',
            'Senda Express' => 'mexico-senda-express',
            'Sendle' => 'sendle',
            'Singapore Post' => 'singapore-post',
            'Singapore SpeedPost' => 'singapore-speedpost',
            'Siodemka' => 'siodemka',
            'SkyNet Wordwide Express' => 'skynetworldwide',
            'Skynet Malaysia' => 'skynet-malaysia',
            'Skynet Worldwide Express' => 'skynetworldwide',
            'South Africa Post Office' => 'sapo',
            'StarTrack' => 'star-track',
            'Swiss Post' => 'swiss-post',
            'TA-Q-BIN Hong Kong' => 'taqbin-hk',
            'TA-Q-BIN Japan' => 'taqbin-jp',
            'TA-Q-BIN Malaysia' => 'taqbin-my',
            'TA-Q-BIN Singapore' => 'taqbin-sg',
            'TGX' => 'tgx',
            'TNT' => 'tnt',
            'TNT Australia' => 'tnt-au',
            'TNT France' => 'tnt-fr',
            'TNT Italia' => 'tnt-it',
            'TNT UK' => 'tnt-uk',
            'Thailand Post' => 'thailand-post',
            'Toll Priority' => 'toll-priority',
            'UK Mail' => 'uk-mail',
            'UPS' => 'ups',
            'UPS Freight' => 'ups-freight',
            'USPS' => 'usps',
            'UkrPoshta' => 'ukrposhta',
            'Vietnam Post' => 'vnpost',
            'Vietnam Post EMS' => 'vnpost-ems',
            'Whistl' => 'whistl',
            'Xend' => 'xend',
            'YRC Freight' => 'yrc',
            'Yakit' => 'yakit',
            'Yanwen' => 'yanwen',
            'Yodel' => 'yodel',
            'Yodel International' => 'yodel-international',
            'i-parcel' => 'i-parcel',
        );
        
        $etsy_carrier_option = array();
        foreach ($etsy_carrier_array as $carrier_name => $carrier_value) {
            $etsy_carrier_option[] = array(
                'id_option' => $carrier_value,
                'name' => $carrier_name
            );
        }
        return $etsy_carrier_option;
    }
    
    //Function definition to handle Form Submission
    public function postProcess()
    {
        parent::postProcess();
        //Handle form submission
        if (Tools::isSubmit('submitAddconfiguration')) {
            Configuration::updateGlobalValue('etsy_switch_value', Tools::getValue('etsy_switch_value'));
            if (Configuration::get('KBETSY_DEMO')) {
                Configuration::updateGlobalValue('etsy_api_key', Configuration::get('etsy_api_key'));
                Configuration::updateGlobalValue('etsy_api_secret', Configuration::get('etsy_api_secret'));
            } else {
                Configuration::updateGlobalValue('etsy_api_key', Tools::getValue('etsy_api_key'));
                Configuration::updateGlobalValue('etsy_api_secret', Tools::getValue('etsy_api_secret'));
            }
            Configuration::updateGlobalValue('etsy_currency', Tools::getValue('etsy_currency'));
            
            Configuration::updateGlobalValue('etsy_selected_shipment_name', Tools::getValue('etsy_selected_shipment_name'));
            
            Configuration::updateGlobalValue('upload_tracking_number', Tools::getValue('upload_tracking_number'));
            Configuration::updateGlobalValue('map_etsy_order_store_carrier', Tools::getValue('map_etsy_order_store_carrier'));
            Configuration::updateGlobalValue('etsy_order_tax', Tools::getValue('etsy_order_tax'));
            
            
            Configuration::updateGlobalValue('etsy_api_host', Tools::getValue('etsy_api_host'));
            Configuration::updateGlobalValue('etsy_api_version', Tools::getValue('etsy_api_version'));
            Configuration::updateGlobalValue('etsy_api_user_id', Tools::getValue('etsy_api_user_id'));
            Configuration::updateGlobalValue('min_threshold_quant', Tools::getValue('min_threshold_quant'));
            Configuration::updateGlobalValue('etsy_desc_type', Tools::getValue('etsy_desc_type'));

            Configuration::updateGlobalValue('etsy_order_default_status', Tools::getValue('etsy_order_default_status'));
            Configuration::updateGlobalValue('etsy_order_shipped_status', implode(",", Tools::getValue('etsy_order_shipped_status')));
            Configuration::updateGlobalValue('etsy_order_unpaid_status', Tools::getValue('etsy_order_unpaid_status'));


            Configuration::updateGlobalValue('etsy_store_lang', 'de,en,es,fr,it,ja,nl,pt,ru,pl');
            if (is_array(Tools::getValue('etsy_sync_lang'))) {
                $lang_ids = implode(',', Tools::getValue('etsy_sync_lang'));
                Configuration::updateGlobalValue('etsy_sync_lang', $lang_ids);
            } else {
                Configuration::updateGlobalValue('etsy_sync_lang', Tools::getValue('etsy_sync_lang'));
            }

            if (Tools::getValue('etsy_default_lang') != Configuration::get('etsy_default_lang')) {
                if (Db::getInstance()->execute("TRUNCATE TABLE " . _DB_PREFIX_ . "etsy_categories")) {
                    $lang_data = new Language(Tools::getValue('etsy_default_lang'));
                    $iso_code = Tools::strtolower($lang_data->iso_code);
                    $importFile = _PS_MODULE_DIR_ . 'kbetsy/ps_etsy_categories_' . $iso_code . '.sql';
                    if (!file_exists($importFile)) {
                        $importFile = _PS_MODULE_DIR_ . 'kbetsy/ps_etsy_categories_en.sql';
                    }
                    if (file_exists($importFile)) {
                        $queryData = '';
                        $lines = file($importFile);
                        foreach ($lines as $line) {
                            if (Tools::substr($line, 0, 2) == '--' || $line == '') { //This IF Remove Comment Inside SQL FILE
                                continue;
                            }
                            $queryData .= $line;
                            if (Tools::substr(trim($line), -1, 1) == ';') { //Breack Line Upto ';' NEW QUERY
                                Db::getInstance()->execute(str_replace('_PREFIX_', _DB_PREFIX_, $queryData));
                                $queryData = '';
                            }
                        }
                    }
                }
            }
            Configuration::updateGlobalValue('etsy_default_lang', Tools::getValue('etsy_default_lang'));
            
            Configuration::updateGlobalValue('KB_SHIPPING_METHOD_ETSY_ORDER', Tools::getValue('KB_SHIPPING_METHOD_ETSY_ORDER'));
            
            Configuration::updateGlobalValue('etsy_sync_item', Tools::getValue('etsy_sync_item'));

            //Audit Log Entry
            $auditLogEntryString = 'General Settings updated for Etsy Marketplace Integration Module. Updated Values are as - <br>Module Enabled: ' . Tools::getValue('etsy_switch_value') . '<br>Api Key: ' . Tools::getValue('etsy_api_key') . '<br>API Secret: ' . Tools::getValue('etsy_api_secret') . '<br>API Host: ' . Tools::getValue('etsy_api_host') . '<br>API Version: ' . Tools::getValue('etsy_api_version');
            $auditMethodName = 'AdminEtsyGeneralSettings::postProcess()';
            EtsyModule::auditLogEntry($auditLogEntryString, $auditMethodName);

            if (!Tools::isEmpty(trim(Tools::getValue('savebtn'))) && (Tools::getValue('savebtn') == 'saveonly')) {
                Tools::redirectAdmin($this->context->link->getAdminlink('AdminEtsyGeneralSettings') . '&etsyConf=19');
            } else {
                //Connection Request
                if ($this->checkSecureUrl()) {
                    $module_base = _PS_BASE_URL_SSL_ . __PS_BASE_URI__;
                } else {
                    $module_base = _PS_BASE_URL_ . __PS_BASE_URI__;
                }
                $redirect_url = $this->context->link->getAdminlink('AdminEtsyGeneralSettings');
                Configuration::updateGlobalValue('etsy_redirect_url', $redirect_url);
                Tools::redirect($this->context->link->getModuleLink('kbetsy', 'cron', array('action' => 'testConnection')));
            }
        }
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
}
