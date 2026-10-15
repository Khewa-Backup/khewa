<?php
/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Register the Merge fields BO tab. Existing tabs are left as-is by the
 * installer, so re-running is a no-op. The COMPANY merge field itself is
 * created lazily on the next Configuration page load — deliberately no
 * Mailchimp API call during the upgrade.
 *
 * @return bool
 */
function upgrade_module_3_1_5(Mailchimppro $object)
{
    // Not $object->menus: on a BO upload that instance predates extraction,
    // so it still carries the previous version's menu list.
    $menus = [
        [
            'is_root' => false,
            'name' => 'Merge fields',
            'class_name' => 'AdminMailchimpProMergeFields',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
    ];

    try {
        $tabRepository = new PrestaChamps\PrestaShop\Tab\TabRepository($menus, 'mailchimppro');
        $tabRepository->install();
    } catch (Exception $exception) {
        PrestaShopLogger::addLog(
            '[MAILCHIMP] upgrade 3.1.5 tab install failed: ' . $exception->getMessage(),
            2,
            null,
            'mailchimppro'
        );

        return false;
    }

    return true;
}
