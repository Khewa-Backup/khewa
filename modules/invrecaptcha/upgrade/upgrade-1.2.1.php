<?php
/**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_1($module)
{
    Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

    $config = $module->getConfig();
    $config['version'] = 2;

    return Configuration::updateValue('irc_config', json_encode($config))
        && $module->registerHook('actionFrontControllerAfterInit')
        && $module->registerHook('actionFrontControllerInitAfter');
}
