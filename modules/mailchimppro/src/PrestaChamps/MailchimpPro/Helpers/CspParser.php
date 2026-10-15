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
 * Stateless Content-Security-Policy header parser used by the Pixel CSP probe
 * to decide whether `chimpstatic.com` is allowed in `script-src` / `connect-src`.
 *
 * Spec reference: https://www.w3.org/TR/CSP3/#framework-directive-source-list
 *
 * Recognized source-list tokens:
 *   *                       — any host
 *   https:                  — any host over HTTPS (we treat this as allowed)
 *   example.com             — exact host match
 *   *.example.com           — subdomain wildcard (does NOT match example.com itself)
 *   'self' / 'none' / ...   — keyword tokens (ignored for cross-origin host checks)
 *
 * What this parser intentionally does NOT do:
 *   - Strict-dynamic / nonce / hash matching (irrelevant for an externally-loaded SDK)
 *   - Report-Only vs enforced header distinction (caller passes whichever it has)
 *   - Port-specific matching (chimpstatic.com is always 443)
 */
class CspParser
{
    /**
     * Pull a directive's source-list from a CSP header string.
     * CSP shape: "directive1 src1 src2; directive2 src3; ...".
     *
     * @param string $csp
     * @param string $directive
     *
     * @return string|null source-list value, or null if directive absent
     */
    public static function extractDirective($csp, $directive)
    {
        $parts = array_map('trim', explode(';', (string) $csp));
        foreach ($parts as $part) {
            if ('' === $part) {
                continue;
            }
            if (preg_match('/^' . preg_quote($directive, '/') . '\s+(.+)$/i', $part, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    /**
     * Tokenize a CSP source-list and check whether any token grants permission
     * to load from the given host.
     *
     * @param string $sourceList e.g. "'self' https: 'unsafe-inline' chimpstatic.com"
     * @param string $host e.g. "chimpstatic.com"
     *
     * @return bool
     */
    public static function sourceListAllowsHost($sourceList, $host)
    {
        $tokens = preg_split('/\s+/', trim((string) $sourceList));
        $needle = strtolower($host);
        foreach ($tokens as $token) {
            if ('' === $token || "'" === $token[0]) {
                continue;
            }
            if ('*' === $token) {
                return true;
            }
            if (preg_match('/^[a-z][a-z0-9+.\-]*:$/i', $token)) {
                if (0 === strcasecmp($token, 'https:')) {
                    return true;
                }

                continue;
            }
            $tokenHost = preg_replace('#^[a-z][a-z0-9+.\-]*://#i', '', $token);
            $tokenHost = preg_replace('#[/\?].*$#', '', $tokenHost);
            $tokenHost = strtolower(rtrim($tokenHost, '/.'));
            if ($tokenHost === $needle) {
                return true;
            }
            if (0 === strpos($tokenHost, '*.')) {
                $base = substr($tokenHost, 2);
                if ('' !== $base && strlen($needle) > strlen($base)
                    && substr($needle, -strlen('.' . $base)) === '.' . $base
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Does this CSP header allow $host in $directive? Falls back to default-src
     * per CSP spec when $directive is absent; if both are absent the directive
     * is unrestricted.
     *
     * @param string $csp
     * @param string $directive e.g. "script-src", "connect-src"
     * @param string $host e.g. "chimpstatic.com"
     *
     * @return bool
     */
    public static function allowsHost($csp, $directive, $host)
    {
        $value = self::extractDirective($csp, $directive);
        if (null === $value) {
            $value = self::extractDirective($csp, 'default-src');
            if (null === $value) {
                return true;
            }
        }

        return self::sourceListAllowsHost($value, $host);
    }
}
