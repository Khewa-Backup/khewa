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

namespace PrestaChamps\MailchimpPro\Commands;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Formatters\ListMemberFormatter;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class NewsletterSubscriberSyncCommand
 */
class NewsletterSubscriberSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $newsletterSubscribers;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];
    protected $updateSubscriptionStatus = true;

    protected $idStore;

    /**
     * NewsletterSubscriberSyncService constructor.
     *
     * @param array $newsletterSubscribers
     */
    public function __construct(\Context $context, MailChimpAPI $mailchimp, $newsletterSubscribers = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('NEWSLETTER_SUBSCRIBER_SYNC_', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->newsletterSubscribers = $newsletterSubscribers;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set update subscription status.
     *
     * @param bool $update
     */
    public function setUpdateSubscriptionStatus($update = true)
    {
        $this->updateSubscriptionStatus = (bool) $update;
    }

    /**
     * @return array|false
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS) || self::SYNC_METHOD_DELETE === $this->method) {
            $this->responses = [];

            $this->buildNewsletterSubscribers();

            if (self::SYNC_MODE_BATCH === $this->syncMode) {
                $this->responses['batch'] = $this->batch->execute();
            }

            $allRequestsSuccess = true;
            $requestErrors = [];
            if (self::SYNC_MODE_REGULAR === $this->syncMode) {
                $method = \Tools::strtolower($this->method);

                // First process member commands
                foreach ($this->commands as $entityId => $params) {
                    if (isset($params['members'])) {
                        $param = $params['members'];
                        try {
                            $this->mailchimp->$method($param['route'] . '?skip_merge_validation=true', $param['data']);

                            if (!$this->mailchimp->success()) {
                                $allRequestsSuccess = false;
                                $requestErrors[$entityId] = $this->mailchimp->getLastError();
                            } else {
                                \Configuration::updateValue(\MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID, $entityId);
                            }

                            $this->responses['entities'][$entityId]['members']['requestSuccess'] = $this->mailchimp->success();
                            $this->responses['entities'][$entityId]['members']['requestLastResponse'] = $this->mailchimp->getLastResponse();
                            $this->responses['entities'][$entityId]['members']['requestLastError'] = $this->mailchimp->getLastError();
                        } catch (\Exception $exception) {
                            \PrestaShopLogger::addLog("[MAILCHIMP-NEWSLETTER]: {$exception->getMessage()}");
                            continue;
                        }
                    }
                }

                // Then process tag commands
                foreach ($this->commands as $entityId => $params) {
                    if (isset($params['tags'])) {
                        $param = $params['tags'];
                        try {
                            $this->mailchimp->post($param['route'], $param['data']);

                            if (!$this->mailchimp->success()) {
                                $allRequestsSuccess = false;
                                $requestErrors[$entityId] = $this->mailchimp->getLastError();
                            }

                            $this->responses['entities'][$entityId]['tags']['requestSuccess'] = $this->mailchimp->success();
                            $this->responses['entities'][$entityId]['tags']['requestLastResponse'] = $this->mailchimp->getLastResponse();
                            $this->responses['entities'][$entityId]['tags']['requestLastError'] = $this->mailchimp->getLastError();
                        } catch (\Exception $exception) {
                            \PrestaShopLogger::addLog("[MAILCHIMP-NEWSLETTER]: {$exception->getMessage()}");
                            continue;
                        }
                    }
                }
            }

            $this->responses['requestMethod'] = $this->method;
            if (!array_key_exists('requestSuccess', $this->responses) || !$this->responses['requestSuccess']) {
                $this->responses['requestSuccess'] = self::SYNC_MODE_REGULAR === $this->syncMode ? $allRequestsSuccess : $this->mailchimp->success();
            }
            if (!$this->responses['requestSuccess']) {
                $this->responses['requestLastErrors'] = self::SYNC_MODE_REGULAR === $this->syncMode ? $requestErrors : $this->mailchimp->getLastError();
            }
            $this->responses['requestLastResponse'] = $this->mailchimp->getLastResponse();
            $this->responses['requestSyncMode'] = self::SYNC_MODE_REGULAR === $this->syncMode ? 'regular' : 'batch';

            return $this->responses;
        }

        return ['requestSuccess' => true];
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function buildNewsletterSubscribers()
    {
        $listId = $this->getListIdFromStore($this->idStore);
        $listRequiresDoi = $this->getListRequiresDOI($listId);

        foreach ($this->newsletterSubscribers as $subscriber) {
            $subscriberHash = $this->mailchimp->subscriberHash($subscriber['email']);

            if (isset($subscriber['id'])) {
                $subscriberId = $subscriber['id'];
            } else {
                $subscriberId = $subscriberHash;
            }

            $promo_campaigns = Campaign::getActiveCampaignsWithPromoCodes($subscriber['email']);

            $extra_fields = [];
            if (count($promo_campaigns) > 0) {
                foreach ($promo_campaigns as $promo_campaign) {
                    if (!(is_null($promo_campaign['campaign']['id_merge_field_mc']) || 0 == $promo_campaign['campaign']['id_merge_field_mc'])) {
                        $extra_fields[$promo_campaign['campaign']['tag_merge_field_mc']] = $promo_campaign['promo_code']->code;
                    }
                }
            }

            $data = [
                'email_address' => $subscriber['email'],
            ];

            if (count($extra_fields) > 0) {
                $data['merge_fields'] = $extra_fields;
            }

            $memberExists = self::getMemberExists($subscriber['email'], $listId, true);
            if (($memberExists && isset($memberExists['status']) && ListMemberFormatter::STATUS_TRANSACTIONAL === $memberExists['status']) || !$memberExists) {
                $data['status'] = $listRequiresDoi ? ListMemberFormatter::STATUS_PENDING : ListMemberFormatter::STATUS_SUBSCRIBED;
            }

            if (isset($subscriber['id_lang']) && $subscriber['id_lang']) {
                $lang = new \Language($subscriber['id_lang']);
                $subscriber_iso_code = \Mailchimppro::getCustomerLanguageIsoCode($lang->iso_code);
                $data['language'] = $subscriber_iso_code;

                // Add language tag
                if (\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_LANGUAGE) && \Validate::isLoadedObject($lang)) {
                    $tags = [
                        [
                            'name' => 'Language: ' . $subscriber_iso_code,
                            'status' => 'active',
                        ],
                    ];

                    $listMemberTagFormatter = new \PrestaChamps\MailchimpPro\Formatters\ListMemberTagFormatter($tags);

                    if (self::SYNC_MODE_BATCH == $this->syncMode) {
                        $this->batch->post(
                            "{$this->batchPrefix}_{$subscriberId}_tags",
                            "/lists/{$listId}/members/{$subscriberHash}/tags",
                            $listMemberTagFormatter->format()
                        );
                    } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                        if (!isset($this->commands[$subscriberId])) {
                            $this->commands[$subscriberId] = [];
                        }
                        $this->commands[$subscriberId]['tags'] = [
                            'route' => "/lists/{$listId}/members/{$subscriberHash}/tags",
                            'data' => $listMemberTagFormatter->format(),
                        ];
                    }
                }
            }

            if (self::SYNC_METHOD_PATCH === $this->method) {
                if (self::SYNC_MODE_BATCH == $this->syncMode) {
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$subscriberId}",
                        "/lists/{$listId}/members/{$subscriberHash}",
                        $data
                    );
                } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                    if (!isset($this->commands[$subscriberId])) {
                        $this->commands[$subscriberId] = [];
                    }
                    $this->commands[$subscriberId]['members'] = [
                        'route' => "/lists/{$listId}/members/{$subscriberHash}",
                        'data' => $data,
                    ];
                }
            } elseif (self::SYNC_METHOD_PUT === $this->method) {
                if (self::SYNC_MODE_BATCH == $this->syncMode) {
                    $this->batch->put(
                        "{$this->batchPrefix}_{$subscriberId}",
                        "/lists/{$listId}/members/{$subscriberHash}",
                        $data
                    );
                } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                    if (!isset($this->commands[$subscriberId])) {
                        $this->commands[$subscriberId] = [];
                    }
                    $this->commands[$subscriberId]['members'] = [
                        'route' => "/lists/{$listId}/members/{$subscriberHash}",
                        'data' => $data,
                    ];
                }
            }
        }
    }

    /**
     * @param string $customerEmail
     * @param string $listId
     * @param bool $returnFields
     *
     * @return bool|array
     */
    public function getMemberExists($customerEmail, $listId = null, $returnFields = null)
    {
        if (!$listId) {
            $listId = $this->getListIdFromStore($this->idStore);
        }

        $hash = $this->mailchimp->subscriberHash($customerEmail);

        $result = $this->mailchimp->get(
            "/lists/{$listId}/members/{$hash}",
            ['fields' => 'status,email_address']
        );

        if ($this->mailchimp->success()) {
            if ($returnFields) {
                return $result;
            }

            return true;
        }

        return false;
    }
}
