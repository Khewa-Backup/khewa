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

if (!defined('_PS_VERSION_')) { exit; }


class Ets_socialloginOauthModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ets_sociallogin
     */
    public $module;
    public $context;

    public function initContent()
    {
        parent::initContent();
        if ((bool)Tools::getValue('ajax', false) && Tools::isSubmit('solo_submitLogin')) {
            if ($this->context->customer->isLogged()) {
                die(json_encode(array(
                    'hasError' => false,
                )));
            } else {
                if (!($email = Tools::getValue('email', false)))
                    $this->errors[] = Ets_sociallogin::$trans['email_required'];
                elseif (!Validate::isEmail($email))
                    $this->errors[] = Ets_sociallogin::$trans['email_invalid'];
                if (!($password = Tools::getValue('password', false)))
                    $this->errors[] = Ets_sociallogin::$trans['password_required'];
                if (!$this->errors) {
                    Hook::exec(($this->module->is17 ? 'actionAuthenticationBefore' : 'actionBeforeAuthentication'));
                    $customer = new Customer();
                    $authentication = $customer->getByEmail($email, $password);
                    if (isset($authentication->active) && !$authentication->active) {
                        $this->errors[] = Ets_sociallogin::$trans['account_available'];
                    } elseif (!$authentication || !$customer->id || $customer->is_guest) {
                        $this->errors[] = Ets_sociallogin::$trans['authentication_failed'];
                    } else {
                        $this->module->updateContext($customer);
                    }
                }
            }
            die(json_encode(array(
                'hasError' => !empty($this->errors),
                'errors' => $this->module->displayError($this->errors)
            )));
        }
        try {            $configs = $this->module->getConfigs();
            if (!is_array($configs) || empty($configs))
                $this->module->popup_exit();
            $storage = new ETSHybridauth\Storage\Session($this->context);
            $ETSHybridauth = new ETSHybridauth\ETSHybridauth($configs, null, $storage, null, $this->context);
            $storage->clear();
            if (!$ETSHybridauth->getProviders())
                $this->module->popup_exit();
            if (isset($this->context->cookie->soloProvider))
                unset($this->context->cookie->soloProvider);
            if (($provider = trim(Tools::getValue('provider'))) !== '' && Validate::isCleanHtml($provider)) {
                $storage->set('provider', $provider);
                if (($currentUrl = trim(Tools::getValue('currentUrl'))) != '' && Validate::isUrl($currentUrl)) {
                    $storage->set('currentUrl', $currentUrl);
                }
                $ETSHybridauth->authenticate($provider);
            }
        } catch (ETSHybridauth\Exception\Exception $exception) {
            die(json_encode($exception->getMessage()));
        }
        die(json_encode(array('msg' => $this->module->l('404 error!'))));
    }
}
