<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewamails extends Module
{
    public function __construct()
    {
        $this->name = 'khewamails';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Rushad Mahrez';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Khewa Mails');
        $this->description = $this->l('Collects emails from visitors.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->installDB() &&
            $this->installTab();
    }


    private function installDB()
    {
        return Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."khewamails` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NULL,
                `email` VARCHAR(255) NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;");
    }


    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab() &&
            Db::getInstance()->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_."khewamails`");
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminKhewaMails';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Khewa Emails';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentModulesSf'); // Parent under "Modules"
        $tab->module = $this->name;
        return $tab->add();
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitKhewamails')) {
            Configuration::updateValue('KHEWA_WELCOME', Tools::getValue('KHEWA_WELCOME'));
            Configuration::updateValue('KHEWA_DESCRIPTION', Tools::getValue('KHEWA_DESCRIPTION'));
            Configuration::updateValue('KHEWA_EMAIL_HTML', Tools::getValue('KHEWA_EMAIL_HTML'), true);
            Configuration::updateValue('KHEWA_ALLOW_EMAIL_RPLY', (int)Tools::getValue('KHEWA_ALLOW_EMAIL_RPLY'));

            $this->_clearCache('*');
            
            $this->context->controller->confirmations[] = $this->l('Settings updated successfully.');
        }

        return $this->renderForm();
    }

    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminKhewaMails');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }
    private function renderForm()
    {
        $link = new $this->context->link;
        $controllerUrl = $link->getModuleLink('khewamails', 'submitemail');

        $fields_form = [
            'form' => [
                'legend' => ['title' => 'Khewa Mails Settings'],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Allow Email Reply'),
                        'name' => 'KHEWA_ALLOW_EMAIL_RPLY',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ]
                        ],
                        'desc' => $this->l('Enable or disable email reply functionality.')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Welcome Message'),
                        'name' => 'KHEWA_WELCOME',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Description Text'),
                        'name' => 'KHEWA_DESCRIPTION',
                        'autoload_rte' => false,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Email HTML Content'),
                        'name' => 'KHEWA_EMAIL_HTML',
                        'autoload_rte' => true,
                        'lang' => false,
//                        'cols' => 40,
//                        'rows' => 10,
                        'class' => 'rte',
                        'desc' => $this->l('Enter the HTML content for the email template.')
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? true : false;
        $helper->submit_action = 'submitKhewamails';

        $helper->fields_value['KHEWA_WELCOME'] = Configuration::get('KHEWA_WELCOME');
        $helper->fields_value['KHEWA_DESCRIPTION'] = Configuration::get('KHEWA_DESCRIPTION');
        $helper->fields_value['KHEWA_EMAIL_HTML'] = Configuration::get('KHEWA_EMAIL_HTML');
        $helper->fields_value['KHEWA_ALLOW_EMAIL_RPLY'] = Configuration::get('KHEWA_ALLOW_EMAIL_RPLY');

//        $languages = Language::getLanguages(false);
//        foreach ($languages as $lang) {
//            $helper->fields_value['KHEWA_EMAIL_HTML'][$lang['id_lang']] = Configuration::get('KHEWA_EMAIL_HTML', $lang['id_lang']);
//        }

        return $helper->generateForm([$fields_form]).'<br>'.$controllerUrl;
    }

}
