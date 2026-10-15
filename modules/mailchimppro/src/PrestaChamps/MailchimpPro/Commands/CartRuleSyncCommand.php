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
use PrestaChamps\MailchimpPro\Formatters\PromoRuleFormatter;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class CartRuleSyncCommand
 */
class CartRuleSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $cartRules;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];

    protected $idStore;

    /**
     * CartRuleSyncService constructor.
     *
     * @param \CartRule[] $cartRules
     */
    public function __construct(\Context $context, MailChimpAPI $mailchimp, $cartRules = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('CART_RULE_SYNC_', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->cartRules = $cartRules;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * @return array|false
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_CART_RULES) || self::SYNC_METHOD_DELETE === $this->method) {
            $this->responses = [];

            $this->buildCartRules();

            if (self::SYNC_MODE_BATCH === $this->syncMode) {
                $this->responses['batch'] = $this->batch->execute();
            }

            $allRequestsSuccess = true;
            $requestErrors = [];
            if (self::SYNC_MODE_REGULAR === $this->syncMode) {
                $method = \Tools::strtolower($this->method);
                foreach ($this->commands as $entityId => $params) {
                    foreach ($params as $key => $param) {
                        try {
                            // $this->responses[$entityId] = $this->mailchimp->$method($params['route'], $params['data']);
                            $this->mailchimp->$method($param['route'], $param['data']);
                            /* \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_PROMO_ID, $entityId); */
                        } catch (\Exception $exception) {
                            \PrestaShopLogger::addLog("[MAILCHIMP-CARTRULE]: {$exception->getMessage()}");
                            continue;
                        }

                        if (!$this->mailchimp->success()) {
                            $allRequestsSuccess = false;
                            $requestErrors[$entityId] = $this->mailchimp->getLastError();
                        } else {
                            \Configuration::updateValue(\MailchimpProConfig::LAST_SYNCED_PROMO_ID, $entityId);
                        }

                        $this->responses['entities'][$entityId][$key]['requestSuccess'] = $this->mailchimp->success();
                        $this->responses['entities'][$entityId][$key]['requestLastResponse'] = $this->mailchimp->getLastResponse();
                        $this->responses['entities'][$entityId][$key]['requestLastError'] = $this->mailchimp->getLastError();
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
     *
     * @todo Implement batch functionality
     */
    protected function buildCartRules()
    {
        foreach ($this->cartRules as $cartRule) {
            $promoRuleFormatter = new PromoRuleFormatter($cartRule, $this->context, $this->idStore);

            if (self::SYNC_METHOD_POST === $this->method) {
                if (self::SYNC_MODE_BATCH == $this->syncMode) {
                    $this->batch->post(
                        "{$this->batchPrefix}_{$cartRule->id}_pr",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules",
                        $promoRuleFormatter->format()
                    );
                    $this->batch->post(
                        "{$this->batchPrefix}_{$cartRule->id}_pc",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes",
                        $promoRuleFormatter->formatPromoCode()
                    );
                } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                    $this->commands[$cartRule->id]['pr'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules",
                        'data' => $promoRuleFormatter->format(),
                    ];
                    $this->commands[$cartRule->id]['pc'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes",
                        'data' => $promoRuleFormatter->formatPromoCode(),
                    ];
                }
            } elseif (self::SYNC_METHOD_PATCH === $this->method) {
                if (self::SYNC_MODE_BATCH == $this->syncMode) {
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$cartRule->id}_pr",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}",
                        $promoRuleFormatter->format()
                    );
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$cartRule->id}_pc",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes/{$cartRule->id}",
                        $promoRuleFormatter->formatPromoCode()
                    );
                } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                    $this->commands[$cartRule->id]['pr'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}",
                        'data' => $promoRuleFormatter->format(),
                    ];
                    $this->commands[$cartRule->id]['pc'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes/{$cartRule->id}",
                        'data' => $promoRuleFormatter->formatPromoCode(),
                    ];
                }
            } elseif (self::SYNC_METHOD_DELETE === $this->method) {
                // Delete promo codes first, then the promo rule (child before parent)
                if (self::SYNC_MODE_BATCH == $this->syncMode) {
                    $this->batch->delete(
                        "{$this->batchPrefix}_{$cartRule->id}_pc",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes/{$cartRule->id}"
                    );
                    $this->batch->delete(
                        "{$this->batchPrefix}_{$cartRule->id}_pr",
                        "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}"
                    );
                } elseif (self::SYNC_MODE_REGULAR === $this->syncMode) {
                    $this->commands[$cartRule->id]['pc'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}/promo-codes/{$cartRule->id}",
                        'data' => [],
                    ];
                    $this->commands[$cartRule->id]['pr'] = [
                        'route' => "ecommerce/stores/{$this->getShopId($this->idStore)}/promo-rules/{$cartRule->id}",
                        'data' => [],
                    ];
                }
            }
        }
    }

    /**
     * @param int $cartRuleId
     * @param string $shopId
     * @param bool $returnFields
     *
     * @return bool|array
     */
    public function getCartRuleExists($cartRuleId, $shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId($this->idStore);
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}/promo-rules/{$cartRuleId}/promo-codes/{$cartRuleId}",
            ['fields' => ['id']]
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
