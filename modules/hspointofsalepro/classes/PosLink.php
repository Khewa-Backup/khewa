<?php
/**
 * PosLink for RockPos
 *
 * @author Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosLink extends Link
{
    public function posGetBaseLink($id_shop = null, $ssl = null, $relative_protocol = false)
    {
        if (null === $ssl) {
            $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        }
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }
        if ($relative_protocol) {
            $base = '//' . ($ssl && $this->ssl_enable ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && $this->ssl_enable) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        }
        return $base . $shop->getBaseURI();
    }
}
