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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Public FO endpoint that returns the current customer's identify value
 * (lowercased+trimmed email or its SHA-256 hash, depending on the configured
 * MAILCHIMP_PIXEL_IDENTIFY_FORMAT). Used by the pixel bridge ONLY when the
 * "Cache-safe identify mode" toggle is enabled in BO Pixel tab — otherwise
 * the identify value is emitted inline in MC_PIXEL JsDef directly (faster,
 * no extra HTTP request).
 *
 * Reachable as:
 *   POST /module/mailchimppro/customerinfo
 *
 * Why POST for a read?
 *   Cache-everything Varnish / Cloudflare page rules treat GET as cacheable
 *   even with `Cache-Control: no-store`. POST is treated as "always fresh"
 *   by basically every cache layer (HTTP semantics: POST changes state).
 *   We prefer POST here so per-customer responses can never be served from
 *   a shared cache to a different visitor. Verb-semantic abuse is acceptable
 *   when needed for cache-busting (Mailchimp / Shopify / Stripe APIs all do
 *   this in places).
 *
 * Response shape (always HTTP 200, JSON):
 *   - Logged-in customer OR guest mid-checkout: {"value": "<email-or-sha256>",
 *                                                 "phoneValue": "<phone-or-sha256>" (optional)}
 *   - Truly anonymous visitor:                  {}
 *
 * `phoneValue` is included only when the customer's resolved primary
 * address (per CustomerAddressSelector) has a non-empty phone. Hashed
 * with the SAME format as `value` (EMAIL_SHA256 → PHONE_SHA256;
 * EMAIL → PHONE). Bridge fires a SECOND api.identify call when
 * present — see mc-pixel-bridge.js identifyOrReset().
 *
 * Gate matches the inline-mode path in mailchimppro::hookDisplayHeader —
 * emit when $context->customer->email is non-empty, which PS populates
 * for both logged-in customers AND guests post-personal-info-form-submit
 * on stock checkout. The previous isLogged()-only gate silently dropped
 * every guest order from the pixel's identify graph.
 *
 * Defenses:
 *   - Endpoint always returns HTTP 200 + JSON regardless of session state
 *     (no oracle for "is this user identified" — both states return identical
 *     200 + JSON shape, attackers can't distinguish).
 *   - Cache-Control: no-store, no-cache, must-revalidate, max-age=0
 *   - Server-side hashing when EMAIL_SHA256 is configured — the plain email
 *     never leaves the server.
 *   - Auth: PS session cookie. Bridge sends `credentials: 'same-origin'` so
 *     the cookie travels with the fetch.
 */
class MailchimpproCustomerinfoModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    /**
     * PS dispatch routes here when Controller::isAjax() returns true (i.e. the
     * caller sets `?ajax=1`, posts `ajax=1`, or sends `Accept: application/json`).
     * Without this method, PS short-circuits to an empty response — display()
     * is NOT called as a fallback when $this->ajax is true.
     */
    public function displayAjax()
    {
        $this->respond($this->buildPayload());
    }

    /**
     * PS dispatch routes here when Controller::isAjax() returns false (the
     * common case for sendBeacon/keepalive-fetch — neither sets the special
     * Accept header). Same logic as displayAjax — both paths must produce
     * identical output.
     */
    public function display()
    {
        $this->respond($this->buildPayload());
    }

    /**
     * @return array
     */
    private function buildPayload()
    {
        if (!isset($this->context->customer)) {
            return [];
        }

        $email = strtolower(trim((string) $this->context->customer->email));
        if ('' === $email) {
            return [];
        }

        $format = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT);
        $value = ('EMAIL_SHA256' === $format) ? hash('sha256', $email) : $email;

        $payload = ['value' => $value];

        // Phone enrichment — Mailchimp asked for PHONE_SHA256 alongside
        // EMAIL_SHA256. Same format toggle as email. Address selection
        // via CustomerAddressSelector (cart delivery → most-recent →
        // fallback), normalization via PhoneNormalizer (trim() today,
        // extensible to E.164 later via the same helper).
        $primaryAddress = PrestaChamps\MailchimpPro\Helpers\CustomerAddressSelector::pickPrimary(
            $this->context->customer,
            isset($this->context->cart) ? $this->context->cart : null
        );
        if ($primaryAddress) {
            $mobile = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone_mobile);
            $landline = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone);
            $phoneRaw = '' !== $mobile ? $mobile : $landline;
            if ('' !== $phoneRaw) {
                $payload['phoneValue'] = ('EMAIL_SHA256' === $format) ? hash('sha256', $phoneRaw) : $phoneRaw;
            }
        }

        return $payload;
    }

    private function respond(array $data)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            // No caching anywhere — per-customer data must be per-request.
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }
        echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
}
