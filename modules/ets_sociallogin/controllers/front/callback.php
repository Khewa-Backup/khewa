<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class Ets_socialloginCallbackModuleFrontController extends ModuleFrontController
{
    public $errors = [];

    /** @var Ets_sociallogin $module */
    public $module;

    /** @var Context $context */
    public $context;

    public $ssl = false;

    public function __construct()
    {
        parent::__construct();
        $this->ssl = (bool)Configuration::get('PS_SSL_ENABLED');
    }

    public function initContent()
    {
        parent::initContent();

        try {
            $configs = $this->module->getConfigs();
            if (!is_array($configs) || empty($configs)) {
                $this->module->popup_exit();
            }

            $storage = new ETSHybridauth\Storage\Session($this->context);
            $auth = new ETSHybridauth\ETSHybridauth($configs, null, $storage, null, $this->context);
            $provider = $storage->get('provider');
            $currentUrl = $storage->get('currentUrl');
            if ($provider) {
                $adapter = $auth->getAdapter($provider);
                $adapter->authenticate();
                /** @var \ETSHybridauth\User\Profile $userProfile */
                $userProfile = $adapter->getUserProfile();

                if ($this->context->customer->isLogged()) {
                    $id_customer = $this->context->customer->id;
                    if (!$userProfile->email || trim($userProfile->email) == '' || !Validate::isEmail($userProfile->email)) {
                        $userProfile->email = $this->context->customer->email;
                    } elseif (trim($this->context->customer->email) !== trim($userProfile->email)) {
                        $this->context->cookie->__set('ets_solo_email_error', $userProfile->email);
                        Tools::redirect($currentUrl);
                    }
                    $this->processComplete($id_customer, $storage, $provider, $userProfile, $currentUrl);
                    return;
                }

                $id_customer = 0;
                $customerId = 0;
                if ($userProfile->email) {
                    $customerId = (int)Solo_user::getIdCustomerByEmail($userProfile->email);
                    if ($customerId > 0) {
                        $id_customer = $customerId;
                    }
                }
                $customerIdentifier = 0;
                // Check if userProfile has identifier property (int type, non-nullable)
                if ($userProfile->identifier !== null) {
                    $customerIdentifier = (int)Solo_user::getIdCustomerByIdentifier($userProfile->identifier) ?: Solo_connect::getIdCustomerByIdentifier($userProfile->identifier);
                    if ($id_customer <= 0 && $customerIdentifier > 0) {
                        $id_customer = $customerIdentifier;
                    }
                }
                // Update user profile with existing customer data if identifier exists
                if ($userProfile->identifier && $id_customer > 0) {
                    $c = new Customer($id_customer);
                    $userProfile->email = $c->email;
                    $userProfile->firstName = $c->firstname;
                    $userProfile->lastName = $c->lastname;
                }
                if (Tools::isSubmit('submitLogin')) {
                    $this->submitLogin($userProfile);
                }
                $idCustomerRegister = null;
                if (Tools::isSubmit('submitRegister')) {
                    $this->setNewProfile($userProfile, $idCustomerRegister);
                }
                if (count($this->errors) > 0 || $customerIdentifier <= 0 && $customerId > 0 && !Tools::isSubmit('submitRegister') && !Tools::isSubmit('submitLogin') || !$id_customer &&
                    (
                        !property_exists($userProfile, 'email') ||
                        !$userProfile->email ||
                        !$userProfile->firstName ||
                        !$userProfile->lastName
                    )
                ) {
                    $this->context->smarty->assign(array(
                        'idCustomerRegister' => $idCustomerRegister,
                        'action' => $this->context->link->getModuleLink($this->module->name, 'callback', ['provider' => $provider], $this->ssl),
                        'errors' => $this->errors ? Tools::nl2br(implode(PHP_EOL, $this->errors)) : false,
                        'profile' => $userProfile,
                        'css' => $this->module->getPathUri() . 'views/css/register.css',
                        'home_url' => $this->context->link->getPageLink('index', $this->ssl),
                        'login_url' => $this->context->link->getPageLink('authentication', $this->ssl),
                        'forgot_password_url' => $this->context->link->getPageLink('password', $this->ssl),
                        'currentUrl' => $currentUrl,
                    ));
                    $template = 'register.tpl';
                    if ($customerIdentifier <= 0 && $customerId > 0)
                        $template = 'login.tpl';
                    $this->setTemplate(($this->module->is17 ? 'module:' . $this->module->name . '/views/templates/front/' : '') . $template);
                    return;
                }
                if (empty($this->errors)) {
                    $this->processComplete($id_customer, $storage, $provider, $userProfile, $currentUrl, $idCustomerRegister);
                }
            }
        } catch (ETSHybridauth\Exception\Exception $e) {
            PrestaShopLogger::addLog($e->getMessage());
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog($e->getMessage());
        }

        if (!empty($currentUrl)) {
            Tools::redirect($currentUrl);
        }

        Tools::redirect($this->context->link->getPageLink('my-account', $this->ssl));
    }

    /**
     * @param $id_customer int
     * @param $storage ETSHybridauth\Storage\Session
     * @param $provider string
     * @param $userProfile \ETSHybridauth\User\Profile
     */
    public function processComplete($id_customer, $storage, $provider, $userProfile, $currentUrl, $idCustomerRegister = null)
    {
        if (!$this->context->cookie->__isset('soloProvider') || !$this->context->cookie->__get('soloProvider') || trim($this->context->cookie->__get('soloProvider')) !== trim($provider)) {
            $this->context->cookie->__set('soloProvider', $provider);
        }
        $storage->clear();
        if (!$id_customer && $idCustomerRegister !== null && $idCustomerRegister > 0) {
            $id_customer = $idCustomerRegister;
        }
        if ($id_customer) {
            $customer = new Customer($id_customer);
            if (!Solo_user::getUserByEmail($userProfile->email)) {
                $discountCode = $this->module->firstConnects($provider, $customer);
                $this->module->trackingUser($userProfile, $customer->id, $provider, $discountCode);
            }
            $this->module->trackingLogin($userProfile, $provider);
            $this->module->updateContext($customer);
            $customer->active = (bool)true;
            $customer->deleted = (bool)false;
            $customer->save();
        } else {
            $customerId = (int)Solo_user::getIdCustomerByIdentifier($userProfile->identifier);
            $this->module->createUser($userProfile, $provider);
        }
        if (!$this->errors) {
            if (!empty($currentUrl)) {
                Tools::redirect($currentUrl);
            }
            $this->module->popup_exit();
        }
    }

    /**
     * @param $userProfile \ETSHybridauth\User\Profile
     */
    public function submitLogin(&$userProfile)
    {
        if (trim($userProfile->email) == '') {
            $email = Tools::getValue('email');
            if (trim($email) == '') {
                $this->errors[] = $this->module->l('Email is required', 'callback');
            } elseif (!Validate::isEmail($email)) {
                $this->errors[] = $this->module->l('Email is invalid', 'callback');
            } else {
                $userProfile->email = $email;
            }
        }
        if (trim($userProfile->email) !== '') {
            $password = trim(Tools::getValue('password', ''));
            if (trim($password) == '') {
                $this->errors[] = $this->module->l('Password is required', 'callback');
            } else {
                if (!(new Customer())->getByEmail($userProfile->email, $password)) {
                    $this->errors[] = $this->module->l('Password is incorrect', 'callback');
                }
            }
        }
    }

    /**
     * @param $userProfile \ETSHybridauth\User\Profile
     * @return bool
     */
    public function setNewProfile(&$userProfile, &$idCustomerRegister = null)
    {
        if (trim($userProfile->email) == '') {
            $email = Tools::getValue('email');
            if (trim($email) == '') {
                $this->errors[] = $this->module->l('Email is required', 'callback');
            } elseif (!Validate::isEmail($email)) {
                $this->errors[] = $this->module->l('Email is invalid', 'callback');
            } else {
                $userProfile->email = $email;
                $idCustomerRegister = (int)Solo_user::getIdCustomerByEmail($userProfile->email);
                if ($idCustomerRegister > 0 && !Tools::getIsset('password')) {
                    $this->errors[] = $this->module->l('Email is already used', 'callback');
                }
            }
        }

        $first_name = Tools::getValue('first_name');
        if (trim($first_name) == '') {
            $this->errors[] = $this->module->l('First name is required', 'callback');
        } elseif (!Validate::isName($first_name)) {
            $this->errors[] = $this->module->l('First name is invalid', 'callback');
        } else {
            $userProfile->firstName = $first_name;
        }

        $last_name = Tools::getValue('last_name');
        if (trim($last_name) == '') {
            $this->errors[] = $this->module->l('Last name is required', 'callback');
        } elseif (!Validate::isName($last_name)) {
            $this->errors[] = $this->module->l('Last name is invalid', 'callback');
        } else {
            $userProfile->lastName = $last_name;
        }

        if (empty($this->errors) && $idCustomerRegister !== null && $idCustomerRegister > 0) {
            $password = Tools::getValue('password');
            if (!$password || trim($password) == '') {
                $this->errors[] = $this->module->l('Password is required', 'callback');
            } else {
                if (!(new Customer())->getByEmail($userProfile->email, $password)) {
                    $this->errors[] = $this->module->l('Password is incorrect', 'callback');
                }
            }
        }

        return count($this->errors) > 0;
    }
}
