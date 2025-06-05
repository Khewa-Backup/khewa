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
        $fields_form = [
            'form' => [
                'legend' => ['title' => 'Khewa Mails Settings'],
                'input' => [
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
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->submit_action = 'submitKhewamails';
        $helper->fields_value['KHEWA_WELCOME'] = Configuration::get('KHEWA_WELCOME');
        $helper->fields_value['KHEWA_DESCRIPTION'] = Configuration::get('KHEWA_DESCRIPTION');

        return $helper->generateForm([$fields_form]);
    }

}
