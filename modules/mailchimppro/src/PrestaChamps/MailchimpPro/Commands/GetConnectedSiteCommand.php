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
 * GET /connected-sites/{connected_site_id} — authoritative read of a
 * connected_site's full record, including `is_pixel_enabled` (the pixel
 * activation flag) and `site_script.fragment` (loader to embed in <head>).
 *
 * Used to refresh MAILCHIMP_PIXEL_AVAILABLE locally without the legacy
 * cURL-based signature-match probe.
 */
class GetConnectedSiteCommand extends BaseApiCommand
{
    public $mailchimp;
    public $siteId;

    public function __construct(MailChimpAPI $mailchimp, $siteId)
    {
        $this->mailchimp = $mailchimp;
        $this->siteId = $siteId;
    }

    /**
     * @return array parsed response body (foreign_id, site_script.{url,fragment},
     *               is_pixel_enabled, created_at, updated_at, _links)
     *
     * @throws MailChimpException
     */
    public function execute()
    {
        $formattedId = \Mailchimppro::shopIdTransformer($this->siteId);
        $response = $this->mailchimp->get('connected-sites/' . rawurlencode($formattedId));
        if ($this->mailchimp->success()) {
            return $response;
        }

        throw new MailChimpException($this->mailchimp->getLastError());
    }
}
