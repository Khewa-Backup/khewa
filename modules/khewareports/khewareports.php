<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewareports extends Module
{
    // Default order state IDs (can be overridden in settings)
    const DEFAULT_STATE_CANCELED = 6;
    const DEFAULT_STATE_REFUNDED = 56;
    const DEFAULT_STATE_REFUNDED_OLD = 7;
    const DEFAULT_STATE_PARTIAL_REFUND = 25;
    const DEFAULT_STATE_PAYMENT_ERROR = 8;
    
    // Canadian Tax IDs
    const TAX_ID_CANADA_GST = 1;  // 5% GST
    const TAX_IDS_QUEBEC_QST = array(25, 34, 32, 31, 28);  // 9.975% QST
    
    public function __construct()
    {
        $this->name = 'khewareports';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Khewa';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Khewa Reports');
        $this->description = $this->l('Reports and quick actions module for Khewa');
    }

    /**
     * Install module
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('backOfficeHeader') &&
            $this->installTabs() &&
            $this->installDefaultConfig();
    }
    
    /**
     * Install default configuration values
     */
    protected function installDefaultConfig()
    {
        Configuration::updateValue('KHEWA_QUICK_EXPORT_PERIOD', 'daily');
        Configuration::updateValue('KHEWA_STATE_CANCELED', self::DEFAULT_STATE_CANCELED);
        Configuration::updateValue('KHEWA_STATE_REFUNDED', self::DEFAULT_STATE_REFUNDED);
        Configuration::updateValue('KHEWA_STATE_REFUNDED_OLD', self::DEFAULT_STATE_REFUNDED_OLD);
        Configuration::updateValue('KHEWA_STATE_PARTIAL_REFUND', self::DEFAULT_STATE_PARTIAL_REFUND);
        Configuration::updateValue('KHEWA_STATE_PAYMENT_ERROR', self::DEFAULT_STATE_PAYMENT_ERROR);
        return true;
    }

    /**
     * Uninstall module
     */
    public function uninstall()
    {
        return $this->uninstallTabs() &&
            $this->uninstallConfig() &&
            parent::uninstall();
    }
    
    /**
     * Remove configuration values
     */
    protected function uninstallConfig()
    {
        Configuration::deleteByName('KHEWA_QUICK_EXPORT_PERIOD');
        Configuration::deleteByName('KHEWA_STATE_CANCELED');
        Configuration::deleteByName('KHEWA_STATE_REFUNDED');
        Configuration::deleteByName('KHEWA_STATE_REFUNDED_OLD');
        Configuration::deleteByName('KHEWA_STATE_PARTIAL_REFUND');
        Configuration::deleteByName('KHEWA_STATE_PAYMENT_ERROR');
        return true;
    }

    /**
     * Install admin tabs
     */
    public function installTabs()
    {
        // Main Reports tab - "Khewa Reports"
        $tabReports = new Tab();
        $tabReports->active = 1;
        $tabReports->class_name = 'AdminKhewaReportsReports';
        $tabReports->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabReports->name[$lang['id_lang']] = 'Khewa Reports';
        }
        $tabReports->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tabReports->module = $this->name;
        if (!$tabReports->add()) {
            return false;
        }

        // QuickAction tab - "Export Quick"
        $tabQuickAction = new Tab();
        $tabQuickAction->active = 1;
        $tabQuickAction->class_name = 'AdminKhewaReportsQuickAction';
        $tabQuickAction->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabQuickAction->name[$lang['id_lang']] = 'Export Quick';
        }
        $tabQuickAction->id_parent = (int)Tab::getIdFromClassName('SELL');
        $tabQuickAction->module = $this->name;
        if (!$tabQuickAction->add()) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall admin tabs
     */
    public function uninstallTabs()
    {
        $tabs = array(
            'AdminKhewaReportsReports',
            'AdminKhewaReportsQuickAction'
        );

        foreach ($tabs as $className) {
            $idTab = Tab::getIdFromClassName($className);
            if ($idTab) {
                $tab = new Tab($idTab);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        return true;
    }

    /**
     * Load the configuration form (settings page)
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitKhewaReportsModule')) {
            // Quick export period
            $quick_export_period = Tools::getValue('KHEWA_QUICK_EXPORT_PERIOD');
            if (in_array($quick_export_period, array('daily', 'weekly', 'monthly'))) {
                Configuration::updateValue('KHEWA_QUICK_EXPORT_PERIOD', $quick_export_period);
            }
            
            // Order state mappings
            Configuration::updateValue('KHEWA_STATE_CANCELED', (int)Tools::getValue('KHEWA_STATE_CANCELED'));
            Configuration::updateValue('KHEWA_STATE_REFUNDED', (int)Tools::getValue('KHEWA_STATE_REFUNDED'));
            Configuration::updateValue('KHEWA_STATE_REFUNDED_OLD', (int)Tools::getValue('KHEWA_STATE_REFUNDED_OLD'));
            Configuration::updateValue('KHEWA_STATE_PARTIAL_REFUND', (int)Tools::getValue('KHEWA_STATE_PARTIAL_REFUND'));
            Configuration::updateValue('KHEWA_STATE_PAYMENT_ERROR', (int)Tools::getValue('KHEWA_STATE_PAYMENT_ERROR'));
            
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Get all order states for dropdown
     */
    protected function getOrderStates()
    {
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $states = OrderState::getOrderStates($id_lang);
        $options = array();
        $options[] = array('id' => 0, 'name' => $this->l('-- Select --'));
        foreach ($states as $state) {
            $options[] = array(
                'id' => $state['id_order_state'],
                'name' => $state['id_order_state'] . ' - ' . $state['name']
            );
        }
        return $options;
    }

    
    /**
     * Display settings form
     */
    protected function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $order_states = $this->getOrderStates();

        $fields_form = array();
        
        // Quick Export Settings Form
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Quick Export Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Export Period'),
                    'name' => 'KHEWA_QUICK_EXPORT_PERIOD',
                    'desc' => $this->l('Date range for quick export: Daily (today), Weekly (last 7 days), Monthly (last 30 days)'),
                    'options' => array(
                        'query' => array(
                            array('id' => 'daily', 'name' => $this->l('Daily')),
                            array('id' => 'weekly', 'name' => $this->l('Weekly')),
                            array('id' => 'monthly', 'name' => $this->l('Monthly')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
        );
        
        // Order State Mapping Form
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Order State Mapping'),
                'icon' => 'icon-tags'
            ),
            'description' => $this->l('Map order states for accurate report calculations. Select which order states correspond to each type.'),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Canceled Order State'),
                    'name' => 'KHEWA_STATE_CANCELED',
                    'desc' => $this->l('Orders with this state are considered canceled'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Refunded Order State'),
                    'name' => 'KHEWA_STATE_REFUNDED',
                    'desc' => $this->l('Orders with this state are considered fully refunded'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Refunded (Old) Order State'),
                    'name' => 'KHEWA_STATE_REFUNDED_OLD',
                    'desc' => $this->l('Legacy refunded state (if applicable)'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Partial Refund Order State'),
                    'name' => 'KHEWA_STATE_PARTIAL_REFUND',
                    'desc' => $this->l('Orders with this state have partial refunds'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment Error Order State'),
                    'name' => 'KHEWA_STATE_PAYMENT_ERROR',
                    'desc' => $this->l('Orders with payment errors'),
                    'options' => array(
                        'query' => $order_states,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitKhewaReportsModule';
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Set current values
        $helper->fields_value = array(
            'KHEWA_QUICK_EXPORT_PERIOD' => Configuration::get('KHEWA_QUICK_EXPORT_PERIOD', 'daily'),
            'KHEWA_STATE_CANCELED' => Configuration::get('KHEWA_STATE_CANCELED', self::DEFAULT_STATE_CANCELED),
            'KHEWA_STATE_REFUNDED' => Configuration::get('KHEWA_STATE_REFUNDED', self::DEFAULT_STATE_REFUNDED),
            'KHEWA_STATE_REFUNDED_OLD' => Configuration::get('KHEWA_STATE_REFUNDED_OLD', self::DEFAULT_STATE_REFUNDED_OLD),
            'KHEWA_STATE_PARTIAL_REFUND' => Configuration::get('KHEWA_STATE_PARTIAL_REFUND', self::DEFAULT_STATE_PARTIAL_REFUND),
            'KHEWA_STATE_PAYMENT_ERROR' => Configuration::get('KHEWA_STATE_PAYMENT_ERROR', self::DEFAULT_STATE_PAYMENT_ERROR),
        );

        return $helper->generateForm($fields_form);
    }
    
    /**
     * Get configured order states
     * @return array
     */
    public static function getConfiguredStates()
    {
        return array(
            'canceled' => (int)Configuration::get('KHEWA_STATE_CANCELED', self::DEFAULT_STATE_CANCELED),
            'refunded' => (int)Configuration::get('KHEWA_STATE_REFUNDED', self::DEFAULT_STATE_REFUNDED),
            'refunded_old' => (int)Configuration::get('KHEWA_STATE_REFUNDED_OLD', self::DEFAULT_STATE_REFUNDED_OLD),
            'partial_refund' => (int)Configuration::get('KHEWA_STATE_PARTIAL_REFUND', self::DEFAULT_STATE_PARTIAL_REFUND),
            'payment_error' => (int)Configuration::get('KHEWA_STATE_PAYMENT_ERROR', self::DEFAULT_STATE_PAYMENT_ERROR),
        );
    }
    
    /**
     * Get refund state IDs as array
     * @return array
     */
    public static function getRefundStateIds()
    {
        $states = self::getConfiguredStates();
        return array(
            $states['refunded'],
            $states['refunded_old'],
            $states['partial_refund']
        );
    }
    
    /**
     * Get excluded state IDs (canceled, payment error)
     * @return array
     */
    public static function getExcludedStateIds()
    {
        $states = self::getConfiguredStates();
        return array(
            $states['canceled'],
            $states['payment_error']
        );
    }
}

