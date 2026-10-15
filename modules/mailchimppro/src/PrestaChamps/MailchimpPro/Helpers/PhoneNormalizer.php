<?php
/**
 * PrestaChamps
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

namespace PrestaChamps\MailchimpPro\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Single normalization choke point for outbound phone values, shared by
 * pixel identify, audience sync (ListMemberFormatter), and ecommerce
 * sync (AddressFormatter). Keeping these surfaces string-identical is
 * what lets Mailchimp's PHONE_SHA256 matching correlate pixel events
 * back to the synced subscriber — any drift breaks the hash match.
 */
class PhoneNormalizer
{
    /**
     * @return string trimmed string, or '' for non-scalar / empty input
     */
    public static function normalize($raw)
    {
        if (!is_scalar($raw)) {
            return '';
        }

        return trim((string) $raw);
    }
}
