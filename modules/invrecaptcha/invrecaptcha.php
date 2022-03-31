<?php
/**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2019 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */

defined('_PS_VERSION_') or exit;

define('IRC_PS16', version_compare(_PS_VERSION_, '1.7', '<'));

class InvReCaptcha extends Module
{
    protected $defaults = array(
        'sitekey' => '',
        'secretkey' => '',
        'theme' => '',
        'pos' => '',
        'offset' => 14,
    );
    protected $config;
    protected $isActive;
    protected $forms;

    public function __construct()
    {
        $this->name = 'invrecaptcha';
        $this->tab = 'administration';
        $this->version = '1.0.2';
        $this->author = 'WebshopWorks';
        $this->module_key = '589d8377a3df8b4f597826ea5430d9da';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7');
        $this->bootstrap = true;
        $this->displayName = $this->l('Spam Protection - Invisible reCaptcha');
        $this->description = $this->l('Protect your site against spam and abuse, while letting your real customers pass through with ease.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->_html = '';
        parent::__construct();

        $json = Configuration::get('irc_config');
        $this->config = empty($json) ? $this->defaults : array_merge($this->defaults, json_decode($json, true));
        $this->isActive = !empty($this->config['sitekey']) && !empty($this->config['secretkey']) && !empty($this->config['forms']);
        $this->submitProcess();
    }

    public function install()
    {
        Configuration::updateValue('irc_config', json_encode($this->defaults));
        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName('irc_config');
        return parent::uninstall();
    }

    public function registerHook($hook_name, $shop_list = null)
    {
        if ($res = parent::registerHook($hook_name, $shop_list)) {
            $this->updatePosition((int)Hook::getIdByName($hook_name), 0, 1);
        }
        return $res;
    }

    public function enable($force_all = false)
    {
        if ($res = parent::enable($force_all)) {
            $this->registerHook(IRC_PS16 ? 'actionBeforeSubmitAccount' : 'actionSubmitAccountBefore');
            $this->registerHook('displayHeader');
            $this->registerHook(IRC_PS16 ? 'displayTop' : 'displayAfterBodyOpeningTag');
        }
        return $res;
    }

    public function disable($force_all = false)
    {
        $this->unregisterHook(IRC_PS16 ? 'actionBeforeSubmitAccount' : 'actionSubmitAccountBefore');
        $this->unregisterHook('displayHeader');
        $this->unregisterHook(IRC_PS16 ? 'displayTop' : 'displayAfterBodyOpeningTag');
        return parent::disable($force_all);
    }

    protected function submitProcess()
    {
        if ($this->isActive && $this->context->controller instanceof FrontController) {
            $submits = array(
                'contact' => array('ctrl' => 'contact', 'key' => 'submitMessage'),
                'review' => array('ctrl' => '', 'key' => 'action', 'val' => 'add_comment'),
                'newsletter' => array('ctrl' => '', 'key' => 'submitNewsletter'),
                'register' => array('ctrl' => 'authentication', 'key' => IRC_PS16 ? 'submitAccount' : 'submitCreate'),
                'login' => array('ctrl' => 'authentication', 'key' => IRC_PS16 ? 'SubmitLogin' : 'submitLogin'),
                'resetpass' => array('ctrl' => 'password', 'key' => 'email'),
                'jmsBlogComment' => array('ctrl' => 'post', 'key' => 'action', 'val' => 'submitComment'),
                'ybcBlogComment' => array('ctrl' => '', 'key' => 'bcsubmit'),
            );
            foreach ($this->config['forms'] as $form) {
                $submit = $submits[$form];
                if (Tools::isSubmit($submit['key']) &&
                    (empty($submit['val']) || Tools::getValue($submit['key']) == $submit['val']) &&
                    (empty($submit['ctrl']) || Tools::getValue('controller') == $submit['ctrl'])
                ) {
                    $this->handleCaptchaResponse($this->verifyCaptcha());
                }
            }
        }
    }

    protected function verifyCaptcha()
    {
        if ($resp = Tools::getValue('inv-recaptcha-response')) {
            static $res;

            if ($res === null) {
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $ctx = array('http' => array(
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'method' => 'POST',
                    'timeout' => 5,
                    'content' => http_build_query(array(
                        'secret' => $this->config['secretkey'],
                        'response' => $resp,
                        'remoteip' => Tools::getRemoteAddr()
                    ))
                ));
                $res = Tools::file_get_contents($url, false, stream_context_create($ctx), $ctx['http']['timeout']);
            }
            return $res ? json_decode($res, true) : array(
                'success' => false,
                'error-codes' => array('connection-failed')
            );
        }
        return array(
            'success' => false,
            'error-codes' => array('robot-detected')
        );
    }

    protected function handleCaptchaResponse($res)
    {
        if (empty($res['success'])) {
            $errorMsg = 'Invisible reCaptcha error: '.implode(', ', $res['error-codes']);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strToLower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                die(json_encode(array('errors' => array($errorMsg), 'hasError' => true)));
            }
            $this->context->cookie->irc_error = $errorMsg;
            $domain = Tools::usingSecureMode() ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true);
            Tools::redirect($domain.$_SERVER['REQUEST_URI'].'#recaptcha');
        }
    }

    public function hookActionSubmitAccountBefore($params)
    {
        if ($this->isActive && $this->context->controller instanceof FrontController && in_array('register', $this->config['forms'])) {
            $this->handleCaptchaResponse($this->verifyCaptcha());
        }
        return true;
    }

    public function hookActionBeforeSubmitAccount($params)
    {
        // PS 1.6.x compatibility
        return $this->hookActionSubmitAccountBefore($params);
    }

    protected function postValidation()
    {
        $errors = array();
        if (Tools::isSubmit('submitInvReCaptcha')) {
            if (!Validate::isTableOrIdentifier(Tools::getValue('sitekey'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Site key'));
            }
            if (!Validate::isTableOrIdentifier(Tools::getValue('secretkey'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Secret key'));
            }
            if (!in_array(Tools::getValue('theme'), array('light', 'dark'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Theme'));
            }
            if (!in_array(Tools::getValue('pos'), array('right', 'left'))) {
                $errors[] = sprintf($this->l('Invalid value for field: %s'), $this->l('Position'));
            }
            if (!Validate::isInt(Tools::getValue('offset'))) {
                $errors[] = $this->l('Invalid value: Distance from bottom should be integer!');
            }
            foreach ($this->forms as $form) {
                $value = Tools::getValue('form_'.$form['name']);
                if (!empty($value) && $value != 'on') {
                    $errors[] = sprintf($this->l('Invalid value for field: %s - %s'), $this->l('Protected forms'), $form['label']);
                }
            }
        }
        if (count($errors)) {
            $this->_html .= $this->displayError(version_compare(_PS_VERSION_, '1.6.1', '<') ? implode(",\n", $errors) : $errors);
            return false;
        }
        return true;
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitInvReCaptcha')) {
            $forms = array();
            foreach ($this->forms as $form) {
                if (Tools::isSubmit('form_'.$form['name'])) {
                    $forms[] = $form['name'];
                }
            }
            $this->config = array(
                'sitekey' => Tools::getValue('sitekey'),
                'secretkey' => Tools::getValue('secretkey'),
                'theme' => Tools::getValue('theme'),
                'pos' => Tools::getValue('pos'),
                'offset' => Tools::getValue('offset'),
                'forms' => $forms
            );
            Configuration::updateValue('irc_config', json_encode($this->config));
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }
        if (Tools::isSubmit('checkSecretKey')) {
            $this->config['secretkey'] = Tools::getValue('checkSecretKey');
            $json = json_encode($this->verifyCaptcha());
            die(")]}'\n$json");
        }
    }

    public function getContent()
    {
        $this->forms = array(
            array('name' => 'contact', 'label' => $this->l('Contact us')),
            array('name' => 'review', 'label' => $this->l('Write a review')),
            array('name' => 'newsletter', 'label' => $this->l('Signup for newsletter')),
            array('name' => 'register', 'label' => $this->l('Registration')),
            array('name' => 'resetpass', 'label' => $this->l('Reset password')),
            array('name' => 'login', 'label' => $this->l('Log in')),
        );

        if (Module::isEnabled('jmsblog')) {
            $this->forms[] = array('name' => 'jmsBlogComment', 'label' => 'JMS Blog - Submit comment');
        }
        if (Module::isEnabled('ybc_blog')) {
            $this->forms[] = array('name' => 'ybcBlogComment', 'label' => 'ETS Blog - Send a comment');
        }

        if ($this->postValidation()) {
            $this->postProcess();
        }
        $this->renderForm();

        return $this->_html;
    }

    protected function renderForm()
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $hf = new HelperForm();
        $hf->show_toolbar = false;
        $hf->default_form_language = $lang->id;
        $hf->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $hf->id = 'inv-recaptcha';
        $hf->identifier = $this->identifier;
        $hf->submit_action = 'submitInvReCaptcha';
        $hf->currentIndex = $this->context->link->getAdminLink('AdminModules', false)."&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $hf->token = Tools::getAdminTokenLite('AdminModules');
        $hf->tpl_vars = array(
            'fields_value' => $this->getConfigValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        $this->_html .= $hf->generateForm(array(
            array('form' => array(
                'legend' => array('icon' => 'icon-cog', 'title' => $this->l('ReCaptcha Settings')),
                'description' => $this->display(__FILE__, 'views/templates/admin/info.tpl'),
                'input' => array(
                    array(
                        'name' => 'sitekey',
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Site key')
                    ),
                    array(
                        'name' => 'secretkey',
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Secret key'),
                    ),
                    array(
                        'name' => 'theme',
                        'type' => 'select',
                        'label' => $this->l('Theme'),
                        'options' => array(
                            'id' => 'value',
                            'name' => 'label',
                            'query' => array(
                                array('value' => 'light', 'label' => $this->l('Light')),
                                array('value' => 'dark', 'label' => $this->l('Dark')),
                            )
                        )
                    ),
                    array(
                        'name' => 'pos',
                        'type' => 'select',
                        'label' => $this->l('Position'),
                        'options' => array(
                            'id' => 'value',
                            'name' => 'label',
                            'query' => array(
                                array('value' => 'right', 'label' => $this->l('Bottom right')),
                                array('value' => 'left', 'label' => $this->l('Bottom left')),
                            )
                        )
                    ),
                    array(
                        'name' => 'offset',
                        'type' => 'text',
                        'class' => 'fixed-width-sm',
                        'suffix' => 'px',
                        'label' => $this->l('Distance from bottom'),
                        'placeholder' => '14',
                    ),
                    array(
                        'name' => 'form',
                        'type' => 'checkbox',
                        'label' => $this->l('Protected forms'),
                        'values' => array(
                            'id' => 'name',
                            'name' => 'label',
                            'query' => $this->forms
                        )
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ))
        ));
    }

    protected function getConfigValues()
    {
        $config = $this->config;
        if (!empty($config['forms'])) {
            foreach ($config['forms'] as $form) {
                $config['form_'.$form] = 'on';
            }
        }
        return $config;
    }

    public function hookdisplayHeader($params)
    {
        if ($this->isActive) {
            unset($this->config['secretkey']);
            Media::addJsDef(array('ircConfig' => $this->config));
            $this->registerJavascript('invrecaptcha', 'modules/invrecaptcha/views/js/invrecaptcha.js', array('version' => $this->version));
        }
    }

    protected function registerJavascript($hander, $path, $attrs = array())
    {
        if (method_exists($controller = $this->context->controller, 'registerJavascript')) {
            if (isset($attrs['version']) && !Configuration::get('PS_JS_THEME_CACHE')) {
                $attrs['server'] = 'remote';
                $path = __PS_BASE_URI__ . "$path?v={$attrs['version']}";
            }
            $controller->registerJavascript($hander, $path, $attrs);
        } else {
            $path = __PS_BASE_URI__ . $path . (isset($attrs['version']) ? "?v={$attrs['version']}" : '');
            $controller->js_files[] = $path;
        }
    }

    public function __call($method, $args)
    {
        if (stripos($method, 'hookdisplay') === 0 && !empty($this->context->cookie->irc_error)) {
            $res = $this->displayError($this->context->cookie->irc_error);
            unset($this->context->cookie->irc_error);
            return $res;
        }
    }
}
