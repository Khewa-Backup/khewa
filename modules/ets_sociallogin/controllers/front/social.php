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


class Ets_socialloginSocialModuleFrontController extends ModuleFrontController
{
    public $errors = array();

    /** @var Ets_sociallogin $module */
    public $module;

    /** @var Context $context */
    public $context;

    public function initContent()
    {
        parent::initContent();
        $url = $this->context->link->getModuleLink($this->module->name, 'social', array(), true);
        if ($this->context->customer->isLogged()) {
            $disconnect = Tools::getValue('disconnect');
            if ($disconnect != '' && Validate::isCleanHtml($disconnect)) {
                if (Solo_connect::disconnect($this->context->customer->id, $disconnect)) {
                    unset($this->context->cookie->soloProvider);
                    Tools::redirect($url);
                } else
                    $this->errors[] = $this->module->l('An error occurred while disconnecting. Please try again.');
            }
            if (!empty($this->context->cookie->ets_solo_email_error)) {
                $this->errors[] = sprintf($this->module->l('Email "%s" is not match with your account. Please try again.', 'social'), $this->context->cookie->ets_solo_email_error);
                unset($this->context->cookie->ets_solo_email_error);
            }
            $breadcrumb = $this->module->getBreadcrumb();
            $this->context->smarty->assign(array(
                'path' => $breadcrumb,
                'breadcrumb' => $breadcrumb,
                'is15' => (bool)version_compare(_PS_VERSION_, '1.6', '<'),
            ));
            $this->setTemplate(($this->module->is17 ? 'module:' . $this->module->name . '/views/templates/front/' : '') . 'social' . ($this->module->is17 ? '' : '16') . '.tpl');
        } else
            Tools::redirect($this->context->link->getPageLink('authentication') . '?back=' . $url);
    }
}
