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

namespace PrestaChamps\MailchimpPro\Formatters;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PromoRuleFormatter
 */
class PromoRuleFormatter
{
    /**
     * @var \Context
     */
    public $context;

    /**
     * @var \CartRule
     */
    public $cartRule;

    protected $idStore;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENTAGE = 'percentage';

    public const TARGET_PER_ITEM = 'per_item';
    public const TARGET_TOTAL = 'total';
    public const TARGET_SHIPPING = 'shipping';

    /**
     * CartRuleFormatter constructor.
     */
    public function __construct(\CartRule $cartRule, \Context $context, $idStore = null)
    {
        $this->cartRule = $cartRule;
        $this->context = $context;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    public function format()
    {
        $data = [
            'id' => (string) $this->cartRule->id,
            'title' => $this->cartRule->name,
            'description' => $this->cartRule->description,
            'starts_at' => $this->cartRule->date_from,
            'ends_at' => $this->cartRule->date_to,
            'enabled' => (bool) $this->cartRule->active,
        ];

        if ($this->cartRule->free_shipping) {
            $data['type'] = self::TYPE_FIXED;
            $data['target'] = self::TARGET_SHIPPING;
            $data['amount'] = 0;
        } elseif (isset($this->cartRule->reduction_percent) && 0 != $this->cartRule->reduction_percent) {
            $data['type'] = self::TYPE_PERCENTAGE;
            $data['amount'] = $this->cartRule->reduction_percent / 100.0;
            $data['target'] = ($this->cartRule->product_restriction ? self::TARGET_PER_ITEM : self::TARGET_TOTAL);
        } elseif (isset($this->cartRule->reduction_amount) && 0 != $this->cartRule->reduction_amount) {
            $data['type'] = self::TYPE_FIXED;
            $data['amount'] = $this->cartRule->reduction_amount;
            $data['target'] = ($this->cartRule->product_restriction ? self::TARGET_PER_ITEM : self::TARGET_TOTAL);
        } else {
            $data['type'] = self::TYPE_FIXED;
            $data['amount'] = 0;
            $data['target'] = ($this->cartRule->product_restriction ? self::TARGET_PER_ITEM : self::TARGET_TOTAL);
        }

        return $data;
    }

    public function formatPromoCode()
    {
        return [
            'id' => (string) $this->cartRule->id,
            'code' => $this->cartRule->code,
            'redemption_url' => $this->context->shop->getBaseURL(true, true),
            'enabled' => (bool) $this->cartRule->active,
        ];
    }
}
