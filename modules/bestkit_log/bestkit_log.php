<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

class bestkit_log extends Module
{
    const PREFIX = 'bestkit_log_';
    const COOKIE_LOG_VISIT_PARAM_NAME = 'bestkit_log_visit';

    protected $_hooks = array(
		'header',
		'actionObjectAddBefore',
		'actionObjectAddAfter',
		'actionObjectUpdateBefore',
		'actionObjectUpdateAfter',
		'actionObjectDeleteBefore',
		'actionObjectDeleteAfter',
		'actionAdminControllerSetMedia',
    );

    protected $_moduleParams = array();
    protected $_moduleParamsLang = array();

    protected $_tabs = array(
        array(
            'class_name' => 'AdminBestkitLogs',
            'parent' => false,
            'name' => 'Bestkit Admin log'
        ),
        array(
            'class_name' => 'AdminBestkitLog',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Actions Log '
        ),
        array(
            'class_name' => 'AdminBestkitLogLoginAttempts',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Login Attempts '
        ),
        array(
            'class_name' => 'AdminBestkitLogVisitHistory',
            'parent' => 'AdminBestkitLogs',
            'name' => 'Visit History '
        ),
    );

    public function __construct()
    {
        $this->name = 'bestkit_log';
        $this->tab = 'payments_gateways';
        $this->version = '1.7.4'; //Update for Prestashop 1.7.8
        $this->author = 'best-kit';
        $this->need_instance = 0;
        $this->module_key = '8a8e23d4093aa682ad4dd8dac1878c8b';
        $this->bootstrap = TRUE;

        parent::__construct();

        $this->displayName = $this->l('Admin/Employee actions log');
        $this->description = $this->l('Log all actions performed in store backend by admin users, view log history on the grid and keep the log records for as long as you need. View login activity and block malicious login attempts. Know what have been done in your store admin panel and by whom.');
    }

    public function getDir($file = '')
    {
        return _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . $file;
    }

    public function install()
    {
        if (parent::install()) {
            $sql = array();
            include ($this->getDir('sql/install.php'));
            foreach ($sql as $_sql) {
                Db::getInstance()->Execute($_sql);
            }
			
            foreach ($this->_hooks as $hook) {
                if (!$this->registerHook($hook)) {
                    return FALSE;
                }
            }

            $languages = Language::getLanguages();
            foreach ($this->_tabs as $tab) {
                $_tab = new Tab();
                $_tab->class_name = $tab['class_name'];
                $_tab->id_parent = Tab::getIdFromClassName($tab['parent']);
                if (empty($_tab->id_parent)) {
                    $_tab->id_parent = 0;
                }

                $_tab->module = $this->name;
                foreach ($languages as $language) {
                    $_tab->name[$language['id_lang']] = $this->l($tab['name']);
                }

                $_tab->add();
            }

            if (!$this->installConfiguration()) {
                return FALSE;
            }

            return TRUE;
        }

        return FALSE;
    }

    public function uninstall()
    {
        if ($return = parent::uninstall()) {
            $sql = array();
            include ($this->getDir('sql/uninstall.php'));
            foreach ($sql as $_sql) {
                Db::getInstance()->Execute($_sql);
            }

            foreach ($this->_tabs as $tab) {
                $_tab_id = Tab::getIdFromClassName($tab['class_name']);
                $_tab = new Tab($_tab_id);
                $_tab->delete();
            }
        }

        return $return;
    }

    public function installConfiguration()
    {
        foreach ($this->_moduleParams as $param => $value) {
            if (!$this->setConfig($param, $value)) {
                return FALSE;
            }
        }

        foreach ($this->_moduleParamsLang as $param => $value) {
            $values = array();
            foreach (Language::getLanguages(FALSE) as $lang) {
                $values[$lang['id_lang']] = $value;
            }

            if (!$this->setConfig($param, $values)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function getConfig($name)
    {
        if (array_key_exists($name, $this->_moduleParamsLang)) {
            $values = array();
            foreach (Language::getLanguages(FALSE) as $lang) {
                $values[$lang['id_lang']] = Configuration::get(self::PREFIX . $name, $lang['id_lang']);
            }

            return $values;
        } else {
            return Configuration::get(self::PREFIX . $name);
        }
    }

    public function setConfig($name, $value)
    {
        return Configuration::updateValue(self::PREFIX . $name, $value, TRUE);
    }

    protected function postProcess() {
        if (Tools::isSubmit('submitUpdate')) {
            if (is_array($_POST)) {
                foreach ($_POST as $key => $value) {
                    if (array_key_exists($key, $this->_moduleParams)) {
                        $validation_method = $this->_moduleParams[$key]['validate'];
                        if (Validate::{$validation_method}($value) || empty($value)) {
                            $this->setConfig($key, $value);
                        } else {
                            $this->errors[] = sprintf($this->l('%s `%s`, sorry but value `%s` does not correct'), $this->_moduleParams[$key]['validateErrMsg'], $this->_moduleParams[$key]['validateErrField'], $value);
                        }
                    }
                }

                $languages = Language::getLanguages(FALSE);
                foreach (array_keys($this->_moduleParamsLang) as $key) {
                    $values = array();
                    foreach ($languages as $lang) {
                        $values[$lang['id_lang']] = Tools::getValue($key . '_' . $lang['id_lang']);
                    }

                    $this->setConfig($key, $values);
                }
            }

            if (!count($this->errors))
                Tools::redirectAdmin('index.php?tab=AdminModules&conf=4&configure=' . $this->name . '&token=' . Tools::getAdminToken('AdminModules' . (int)Tab::getIdFromClassName('AdminModules') . (int)$this->context->employee->id));
        }
    }

    private function initToolbar() {
        $this->toolbar_btn['save'] = array(
            'href' => '#',
            'desc' => $this->l('Save')
        );

        return $this->toolbar_btn;
    }

    public function _renderHelp() {
        $this->context->smarty->assign(array(
            'bestkit_logs' => array(
                'links' => array(
                    array(
                        'title' => $this->l('Actions Log'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLog'),
                    ),
                    array(
                        'title' => $this->l('Login Attempts'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLogLoginAttempts'),
                    ),
                    array(
                        'title' => $this->l('Visit History'),
                        'href' => $this->context->link->getAdminLink('AdminBestkitLogVisitHistory'),
                    ),
                )
            )
        ));

        return $this->display(__FILE__, 'help.tpl');
    }

    private function initForm() {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->toolbar_scroll = TRUE;
        $helper->toolbar_btn = $this->initToolbar();
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitUpdate';

        $languages = Language::getLanguages(FALSE);
        $availableLanguages = array();
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)($language['id_lang'] == Configuration::get('PS_LANG_DEFAULT'));
            $availableLanguages[] = array(
                'id' => $language['id_lang'],
                'id_language' => $language['name'],
            );
        }

        $helper->languages = $languages;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        $this->fields_form[0]['form'] = array(
            'tinymce' => TRUE,
            'legend' => array(
                'title' => $this->l('General settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'col' => '9',
                    'type' => 'categories_select',
                    'category_tree' => $this->_renderHelp(),
                    'name' => '__links_help',
                ),
            ),
            'submit' => array(
                'name' => 'submitUpdate',
                'title' => $this->l('   Save   '),
            ),
        );

        return $helper;
    }

    public function getContent() {
        $this->postProcess();
        $helper = $this->initForm();
        foreach ($this->fields_form as $fieldset) {
            foreach ($fieldset['form']['input'] as $input) {
                $helper->fields_value[$input['name']] = $this->getConfig($input['name']);
            }
        }

        return $helper->generateForm($this->fields_form);
    }

    public function getLogExceptions()
    {
        return BestkitLog::getLogExceptions();
    }

    public function setStatusCallback($status)
    {
        $this->context->smarty->assign([
            'bestkit_points' => [
                'status' => [
                    'name' => $status,
                    'color' => $status == 'update' ? 'orange' : ($status == 'add' ? 'green' : 'red'),
                ]
            ]
        ]);

        return $this->display(__FILE__, 'views/templates/admin/status_renderer.tpl');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (!isset($this->context->cookie->{self::COOKIE_LOG_VISIT_PARAM_NAME})) {
            $this->context->cookie->{self::COOKIE_LOG_VISIT_PARAM_NAME} = BestkitLogVisit::registerNewVisit();
        }

        BestkitLogVisit::recordVisitDetails($this->context->cookie->{self::COOKIE_LOG_VISIT_PARAM_NAME});
    }
	
/*
	public function hookActionObjectAddBefore($params) {
		//print_r($params); die('hookActionObjectAddBefore');
		
		BestkitLog::saveLog($this->context, $params['object'], 'actionObject'.get_class($params['object']).'Add', 'before'); //Before
	}
    public function hookActionObjectUpdateBefore($params) {
        if (in_array(get_class($params['object']), $this->exceptions))
            return;

        $this->context->bestkitlog_data_before = $params['object'];
    }
	public function hookActionObjectDeleteBefore($params) {
		//print_r($params); die('hookActionObjectDeleteBefore');

		BestkitLog::saveLog($this->context, $params['object'], 'actionObject'.get_class($params['object']).'Delete', 'before'); //Before
	}
*/
	public function hookActionObjectAddAfter($params) {
		if (in_array(get_class($params['object']), $this->getLogExceptions()))
		    return;

        if (is_object(Context::getContext()->employee) && Context::getContext()->employee->id) {
            BestkitLog::saveLog(
                $params['object'],
                BestkitLog::getFromRegistry(BestkitLog::getRegistryName($params['object'], true)),
                'add',
                $this->context,
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
            );
	    }
	}

	public function hookActionObjectUpdateAfter($params) {
        if (in_array(get_class($params['object']), $this->getLogExceptions()))
            return;

        if (is_object(Context::getContext()->employee) && Context::getContext()->employee->id) {
		    BestkitLog::saveLog(
		        $params['object'],
                BestkitLog::getFromRegistry(BestkitLog::getRegistryName($params['object'])),
                'update',
                $this->context,
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
            );
	    }
	}

	public function hookActionObjectDeleteAfter($params) {
        if (in_array(get_class($params['object']), $this->getLogExceptions()))
            return;

        if (is_object(Context::getContext()->employee) && Context::getContext()->employee->id) {
            BestkitLog::saveLog(
                $params['object'],
                BestkitLog::getFromRegistry(BestkitLog::getRegistryName($params['object'])),
                'delete',
                $this->context,
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? 'ajax action' : ''
            );
	    }
	}

}
