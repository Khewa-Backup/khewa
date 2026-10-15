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


class Ets_socialloginDocumentModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ets_sociallogin
     */
    public $module;
    public function __construct()
    {
        parent::__construct();
        // Constructor completed - layout settings handled by parent
    }

    public function initContent()
    {
        parent::initContent();
        if (($doc = Tools::getValue('doc', false)) && Validate::isCleanHtml($doc)) {
            Tools::redirect(Ets_sociallogin::$document_link . $doc . '.pdf');
            exit;
        } else
            die($this->module->l('Not found!', 'document'));
    }
}
