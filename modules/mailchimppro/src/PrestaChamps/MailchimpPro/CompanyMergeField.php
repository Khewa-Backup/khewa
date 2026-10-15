<?php
/**
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 */

namespace PrestaChamps\MailchimpPro;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Creates the COMPANY merge field so customer company values are visible and
 * segmentable in the audience.
 *
 * Create-only by design: the field is list-scoped and may hold values synced
 * by other stores sharing the audience, so the module never deletes it.
 */
class CompanyMergeField
{
    public const TAG = 'COMPANY';

    public const STATUS_EXISTS = 'exists';
    public const STATUS_MISSING = 'missing';
    public const STATUS_UNKNOWN = 'unknown';

    /** Audience is at Mailchimp's 30-field cap; merchant must free a slot. */
    public const ERROR_CAP_REACHED = 'merge-field-cap-reached';

    public static function getStatus()
    {
        $status = \Configuration::get(\MailchimpProConfig::COMPANY_FIELD_STATUS);

        return $status ?: self::STATUS_UNKNOWN;
    }

    /**
     * Ensure the field exists, in a single POST: Mailchimp answers 400
     * "already exists" when it does, which is the same end state as creating
     * it. Only genuine failures (cap reached, auth, network) come back false.
     *
     * @param \PrestaChamps\MailChimpAPI $mailchimp
     * @param string $listId
     *
     * @return array ['success' => bool, 'status' => string, 'error' => string]
     */
    public static function ensure($mailchimp, $listId)
    {
        if (empty($listId)) {
            return ['success' => false, 'status' => self::getStatus(), 'error' => 'no-list-id'];
        }

        $response = $mailchimp->post("lists/{$listId}/merge-fields", [
            'name' => 'Company',
            'type' => 'text',
            'tag' => self::TAG,
        ]);

        if ($mailchimp->success() && isset($response['merge_id'])) {
            self::saveStatus(self::STATUS_EXISTS);

            return ['success' => true, 'status' => self::STATUS_EXISTS, 'error' => ''];
        }

        $error = (string) $mailchimp->getLastError();

        if (preg_match('/already exists/i', $error)) {
            self::saveStatus(self::STATUS_EXISTS);

            return ['success' => true, 'status' => self::STATUS_EXISTS, 'error' => ''];
        }

        if (preg_match('/maximum|too many|limit/i', $error)) {
            self::saveStatus(self::STATUS_MISSING);

            return ['success' => false, 'status' => self::STATUS_MISSING, 'error' => self::ERROR_CAP_REACHED];
        }

        // Auth / network / unexpected: no evidence about the field either way,
        // so the cached status is left as-is.
        return ['success' => false, 'status' => self::getStatus(), 'error' => $error];
    }

    /**
     * Refresh the cached status from an already-fetched merge-fields array —
     * lets BO views that fetched the list anyway keep the cache honest for free.
     */
    public static function updateStatusFromFields(array $fields)
    {
        foreach ($fields as $field) {
            if (isset($field['tag']) && self::TAG === $field['tag']) {
                self::saveStatus(self::STATUS_EXISTS);

                return self::STATUS_EXISTS;
            }
        }
        self::saveStatus(self::STATUS_MISSING);

        return self::STATUS_MISSING;
    }

    protected static function saveStatus($status)
    {
        \MailchimpProConfig::saveValue(\MailchimpProConfig::COMPANY_FIELD_STATUS, $status);
    }
}
