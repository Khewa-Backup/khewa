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

function upgrade_module_1_0_1($module)
{
    return $module->registerHook(IRC_PS16 ? 'actionBeforeSubmitAccount' : 'actionSubmitAccountBefore');
}
