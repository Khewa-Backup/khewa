<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Commands;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Exceptions\MailChimpException;

/**
 * Verify that the connected sites script has been installed
 */
class SiteVerifyCommand extends BaseApiCommand
{
    public $mailchimp;
    public $siteId;

    public function __construct(MailChimpAPI $mailchimp, $siteId)
    {
        $this->mailchimp = $mailchimp;
        $this->siteId = $siteId;
    }

    /**
     * @return bool
     *
     * @throws MailChimpException
     */
    public function execute()
    {
        $formattedId = \Mailchimppro::shopIdTransformer($this->siteId);

        $this->mailchimp->post('connected-sites/' . rawurlencode($formattedId) . '/actions/verify-script-installation');

        if ($this->mailchimp->success()) {
            return true;
        }

        // HTTP 404 = connected_site missing on Mailchimp — the site can't be
        // verified because it no longer exists. Common causes: site deleted
        // from Mailchimp's UI, from module BO Sites tab, or via API.
        $lastResponse = $this->mailchimp->getLastResponse();
        $httpCode = isset($lastResponse['headers']['http_code']) ? (int) $lastResponse['headers']['http_code'] : 0;
        if (404 === $httpCode) {
            throw new MailChimpException($lastResponse, 'Mailchimp connected site not found (HTTP 404). The site was likely deleted on the Mailchimp side. Re-sync your store from the Store sync tab to restore the connection.');
        }

        $fallback = $this->mailchimp->getLastError() ?: ('Unknown Mailchimp API error (HTTP ' . $httpCode . ')');
        throw new MailChimpException($lastResponse, $fallback);
    }
}
