<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewareports extends Module
{
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
            $this->installTabs();
    }

    /**
     * Uninstall module
     */
    public function uninstall()
    {
        return $this->uninstallTabs() &&
            parent::uninstall();
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
            $quick_export_period = Tools::getValue('KHEWA_QUICK_EXPORT_PERIOD');
            if (in_array($quick_export_period, array('daily', 'weekly', 'monthly'))) {
                Configuration::updateValue('KHEWA_QUICK_EXPORT_PERIOD', $quick_export_period);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            } else {
                $output .= $this->displayError($this->l('Invalid period selected'));
            }
        }

        return $output . $this->displayForm();
    }

    /**
     * Display settings form
     */
    protected function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Quick Export Date Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Export Period'),
                    'name' => 'KHEWA_QUICK_EXPORT_PERIOD',
                    'required' => true,
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
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $helper->fields_value = array(
            'KHEWA_QUICK_EXPORT_PERIOD' => Configuration::get('KHEWA_QUICK_EXPORT_PERIOD', 'daily')
        );

        return $helper->generateForm($fields_form);
    }
}

