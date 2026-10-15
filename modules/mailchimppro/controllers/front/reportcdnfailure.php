<?php
/**
 * 2007-2026 PrestaChamps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 *  @author    PrestaChamps <support@prestachamps.com>
 *  @copyright 2007-2026 PrestaChamps
 *  @license   https://opensource.org/licenses/AFL-3.0  Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\FoCdnHealth;

/**
 * Public FO endpoint receiving CDN-observation beacons from mc-cdn-health.js.
 * Reachable as:
 *
 *   POST /module/mailchimppro/reportcdnfailure
 *
 * Expected request body (form-urlencoded or JSON):
 *   {
 *     "kind":   "failure" | "success",   // required
 *     "status": <int>,                   // required for kind=failure; ignored for kind=success
 *     "vh":     "a1b2c3d4"               // 8-hex-char short visitor hash
 *   }
 *
 * Always returns HTTP 204 (no content) so the beacon is fire-and-forget cheap.
 * Never tells the caller WHY a report was dropped — an attacker probing the
 * endpoint learns nothing.
 *
 * Threshold + rolling-window logic lives in FoCdnHealth (unit-tested there);
 * this controller is a thin validation + dispatch layer.
 */
class MailchimpproReportcdnfailureModuleFrontController extends ModuleFrontController
{
    /** Hard cap on request-body bytes. Payload is tiny (~50 bytes) — 2 KB is 40x headroom. */
    public const MAX_PAYLOAD_BYTES = 2048;

    /** @var bool no auth — public FO endpoint by design */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    public function displayAjax()
    {
        $this->handlePost();
        $this->respond204();
    }

    public function display()
    {
        $this->handlePost();
        $this->respond204();
    }

    private function handlePost()
    {
        $payload = $this->readPayload();
        if (!is_array($payload)) {
            return;
        }

        $kind = isset($payload['kind']) ? (string) $payload['kind'] : '';
        $vh = isset($payload['vh']) ? (string) $payload['vh'] : '';
        // Cheap client-side shape check — FoCdnHealth also validates.
        if (!preg_match('/^[a-f0-9]{8}$/', $vh)) {
            return;
        }

        if ('failure' === $kind) {
            $status = isset($payload['status']) ? (int) $payload['status'] : 0;
            FoCdnHealth::recordFailure($status, $vh);
        } elseif ('success' === $kind) {
            FoCdnHealth::recordSuccess($vh);
        }
    }

    /**
     * Parse request body — supports JSON, form-encoded POST, and sendBeacon
     * Blob (which arrives as text/plain but often carries either JSON or an
     * URL-encoded string).
     *
     * @return array|null
     */
    private function readPayload()
    {
        if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > self::MAX_PAYLOAD_BYTES) {
            return null;
        }

        $contentType = isset($_SERVER['CONTENT_TYPE']) ? (string) $_SERVER['CONTENT_TYPE'] : '';
        if (false !== stripos($contentType, 'application/json')) {
            $raw = $this->readCappedBody();
            if (null === $raw || '' === $raw) {
                return null;
            }
            $data = json_decode($raw, true);

            return is_array($data) ? $data : null;
        }
        if (!empty($_POST)) {
            return $_POST;
        }
        // sendBeacon default Blob path — try JSON, then URL-encoded parse
        $raw = $this->readCappedBody();
        if (null === $raw || '' === $raw) {
            return null;
        }
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
        parse_str($raw, $parsed);

        return is_array($parsed) && !empty($parsed) ? $parsed : null;
    }

    private function readCappedBody()
    {
        $fp = @fopen('php://input', 'r');
        if (false === $fp) {
            return null;
        }
        $raw = @stream_get_contents($fp, self::MAX_PAYLOAD_BYTES);
        @fclose($fp);

        return is_string($raw) ? $raw : null;
    }

    private function respond204()
    {
        if (!headers_sent()) {
            http_response_code(204);
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }
        exit;
    }
}
