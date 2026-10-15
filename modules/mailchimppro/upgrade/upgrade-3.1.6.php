<?php
/**
 * @author    PrestaChamps <support@prestachamps.com>
 * @copyright PrestaChamps
 * @license   Proprietary
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Clear the FO CDN health state. Shops upgrading from a release whose observer
 * misread every successful MC.js load as a failure carry a latched flag and a
 * log of bogus reports; both would otherwise linger until they decay.
 *
 * Literal keys, not MailchimpProConfig constants — an upgrade script must not
 * depend on which version of a class is in the class table.
 *
 * @return bool
 */
function upgrade_module_3_1_6($object)
{
    Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-broken-until');
    Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-failure-log');
    Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-last-success');

    return true;
}
