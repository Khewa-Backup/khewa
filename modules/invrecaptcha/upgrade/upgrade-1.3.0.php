<?php
/**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2025 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0($module)
{
    Shop::isFeatureActive() && Shop::setContext(Shop::CONTEXT_ALL);

    $module->unregisterHook('displayAfterBodyOpeningTag');
    $module->unregisterHook('displayHeader');
    $module->unregisterHook('displayTop');
    $module->unregisterHook('displayFooter');

    return $module->registerHook('actionFrontControllerSetMedia');
}
