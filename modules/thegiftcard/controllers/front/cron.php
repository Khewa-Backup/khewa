<?php

/**
 * 2023 - Keyrnel
 *
 * NOTICE OF LICENSE
 *
 * The source code of this module is under a commercial license.
 * Each license is unique and can be installed and used on only one shop.
 * Any reproduction or representation total or partial of the module, one or more of its components,
 * by any means whatsoever, without express permission from us is prohibited.
 * If you have not received this module from us, thank you for contacting us.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @author    Keyrnel
 * @copyright 2023 - Keyrnel
 * @license   commercial
 * International Registered Trademark & Property of Keyrnel
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ThegiftcardCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax;

    /**
     * @var Thegiftcard
     */
    public $module;

    public function display()
    {
        $this->ajax = true;

        if (Configuration::get('GIFTCARD_CRON_TOKEN') !== Tools::getValue('secure_key')) {
            exit('Secure key is not valid');
        }

        try {
            $this->module->runCronTask();
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        exit('Cron task executed');
    }
}
