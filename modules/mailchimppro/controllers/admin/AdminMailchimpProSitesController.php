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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminMailchimpProListsController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProSitesController extends PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural = 'sites';
    public $entitySingular = 'connected-sites';

    protected function getListApiEndpointUrl()
    {
        return '/connected-sites';
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "connected-sites/{$entityId}";
    }

    protected function deleteEntity($id)
    {
        $this->mailchimp->delete("/connected-sites/{$id}");

        if ($this->mailchimp->success()) {
            return true;
        }

        return false;
    }

    /**
     * Override to opportunistically sync local config from the API response.
     * Every time admin lands on the Sites tab the list endpoint returns full
     * connected_site objects (is_pixel_enabled, site_script.fragment, etc.) —
     * use that data to refresh local config instead of letting it drift.
     *
     * Only touches the current shop's entry (matched on foreign_id). Other
     * shops' / legacy connected_sites in the same Mailchimp account are
     * displayed but not synced.
     *
     * @return array entities (unchanged — pass-through)
     *
     * @throws PrestaChamps\MailchimpPro\Exceptions\MailChimpException
     */
    protected function getEntities()
    {
        $entities = parent::getEntities();
        if (is_array($entities)) {
            $this->syncLocalConfigFromSites($entities);
        }

        return $entities;
    }

    /**
     * Walks the sites list, finds this shop's entry, and refreshes:
     *   - MAILCHIMP_PIXEL_AVAILABLE (new JSON shape: enabled / last_checked_at / source)
     *   - MAILCHIMP_SCRIPT_CACHED   (only if fragment changed)
     *   - MAILCHIMP_SCRIPT_CACHED_DATE (only if fragment changed)
     *
     * Each meaningful change emits a PixelLog entry so the audit trail shows
     * "sync happened during Sites tab visit". Steady-state visits (no change)
     * stay silent.
     */
    protected function syncLocalConfigFromSites(array $sites)
    {
        $myForeignId = (string) Mailchimppro::shopIdTransformer($this->context->shop);
        foreach ($sites as $site) {
            if (!is_array($site)) {
                continue;
            }
            $foreignId = isset($site['foreign_id']) ? (string) $site['foreign_id'] : '';
            if ($foreignId !== $myForeignId) {
                continue;
            }

            // 1. Pixel-enabled state — write canonical JSON shape, matching
            //    refreshPixelEnabledState in the config controller.
            if (array_key_exists('is_pixel_enabled', $site)) {
                $enabled = (bool) $site['is_pixel_enabled'];
                $payload = [
                    'enabled' => $enabled,
                    'last_checked_at' => date('Y-m-d H:i:s'),
                    'source' => 'api',
                ];
                $previousRaw = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE);
                $previousDecoded = '' === $previousRaw ? null : json_decode($previousRaw, true);
                $previousEnabled = (is_array($previousDecoded) && isset($previousDecoded['enabled']))
                    ? (bool) $previousDecoded['enabled']
                    : null;
                Configuration::updateValue(
                    MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE,
                    json_encode($payload, JSON_UNESCAPED_SLASHES)
                );
                if ($previousEnabled !== $enabled) {
                    PrestaChamps\MailchimpPro\Models\PixelLog::insert(
                        PrestaChamps\MailchimpPro\Models\PixelLog::SEVERITY_INFO,
                        PrestaChamps\MailchimpPro\Models\PixelLog::SOURCE_BO,
                        'PIXEL_STATE_REFRESH',
                        'Pixel ' . ($enabled ? 'enabled' : 'disabled')
                            . ' (synced from Sites tab list)'
                    );
                }
            }

            // 2. Script fragment — only update if it actually changed (avoids
            //    bumping last-fetched-date on every tab visit).
            if (isset($site['site_script']['fragment'])
                && is_string($site['site_script']['fragment'])
                && '' !== $site['site_script']['fragment']) {
                $newFragment = $site['site_script']['fragment'];
                $oldFragment = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED);
                if ($oldFragment !== $newFragment) {
                    Configuration::updateValue(
                        MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED,
                        $newFragment
                    );
                    Configuration::updateValue(
                        MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE,
                        date('Y-m-d H:i:s')
                    );
                    PrestaChamps\MailchimpPro\Models\PixelLog::insert(
                        PrestaChamps\MailchimpPro\Models\PixelLog::SEVERITY_INFO,
                        PrestaChamps\MailchimpPro\Models\PixelLog::SOURCE_BO,
                        'SCRIPT_CACHED',
                        'Pixel script refreshed (' . strlen($newFragment)
                            . ' bytes, synced from Sites tab list)'
                    );
                }
            }

            break;
        }
    }
}
