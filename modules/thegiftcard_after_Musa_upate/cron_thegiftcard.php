<?php
/**
* 2022 - Keyrnel
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
* @copyright 2022 - Keyrnel
* @license   commercial
* International Registered Trademark & Property of Keyrnel
*/
include __DIR__ . '/../../config/config.inc.php';

require_once __DIR__ . '/thegiftcard.php';

if (!isset($_GET['secure_key']) || empty($_GET['secure_key'])) {
    exit('Secure key is mandatory');
}

$secureKey = Configuration::get('GIFTCARD_CRON_TOKEN');
if ($secureKey !== $_GET['secure_key']) {
    exit('Secure key is not valid');
}

if (!($moduleInstance = Module::getInstanceByName('thegiftcard'))
    || !$moduleInstance instanceof Thegiftcard
) {
    exit('Module is not installed');
}

try {
    if (version_compare(_PS_VERSION_, '1.7', '>')) {
        require_once __DIR__ . '/../../app/AppKernel.php';

        $kernel = new AppKernel(_PS_MODE_DEV_ ? 'dev' : 'prod', _PS_MODE_DEV_);
        $kernel->boot();
        // Context::getContext()->container = $kernel->getContainer();
    }

    $moduleInstance->runCronTask();
} catch (PrestaShopException $e) {
    exit($e->getMessage());
}

exit('Cron task executed');
