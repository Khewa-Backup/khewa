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

use PrestaShop\PrestaShop\Adapter\Entity\CartRule;

/**
 * Class OrderFormatter
 */
class OrderFormatter
{
    protected $context;
    protected $order;
    protected $customer;
    protected $shipping_address;
    protected $billing_address;
    protected $campaignId;
    protected $idOrderStates = [];

    protected $idStore;

    /**
     * ProductFormatter constructor.
     *
     * @todo Refactor this spaghetti
     *
     * @param string $campaignId
     */
    public function __construct(\Order $order, \Customer $customer, \Address $billing_address, \Address $shipping_address, \Context $context, $campaignId = null, $idStore = null)
    {
        $this->order = $order;
        $this->customer = $customer;
        $this->billing_address = $billing_address;
        $this->shipping_address = $shipping_address;
        $this->context = $context;
        $this->campaignId = $campaignId;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set campaignId feature.
     *
     * @param string $campaign_id
     */
    public function setCampaignId($campaign_id = null)
    {
        $this->campaignId = $campaign_id;
    }

    /**
     * Set idOrderStates.
     *
     * @param array $id_order_states
     */
    public function setIdOrderStates($id_order_states = [])
    {
        $this->idOrderStates = $id_order_states;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    public function format()
    {
        if ($this->order->id_currency) {
            $order_currency = new \Currency($this->order->id_currency);
            $store_currency_format = \Tools::strtoupper($order_currency->iso_code);
        } else {
            $store_currency_format = \Tools::strtoupper(\Currency::getDefaultCurrency()->iso_code);
        }

        $data = [
            'id' => (string) $this->order->id,
            'currency_code' => $store_currency_format,
            'order_total' => $this->order->total_paid,
            'tax_total' => $this->order->total_paid_tax_incl - $this->order->total_paid_tax_excl,
            'shipping_total' => $this->order->total_shipping,
            'lines' => [],
            'customer' => ['id' => (string) $this->customer->id],
        ];

        // Encrypted-cookie mode isn't usable here — the cookie value must be
        // readable on both the PS side and the Mailchimp side, and Mailchimp
        // has no access to the PS encryption key.
        if ($this->campaignId) {
            $data['campaign_id'] = $this->campaignId;
        } elseif (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController') && !is_a($this->context->controller, 'MailchimpproCronjobModuleFrontController')) {
            $data['campaign_id'] = $_COOKIE['mc_cid'];
        }

        $data['shipping_address'] = (new AddressFormatter($this->shipping_address, $this->context))->format();
        $data['billing_address'] = (new AddressFormatter($this->billing_address, $this->context))->format();

        // landing_site is stored in Cookie's magic-method content array, not
        // a declared property — read via __get to stay PHPStan-clean.
        $landingSite = $this->context->cookie->__get('landing_site');
        if (!empty($landingSite) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController') && !is_a($this->context->controller, 'MailchimpproCronjobModuleFrontController')) {
            $data['landing_site'] = $landingSite;
        }

        $data['processed_at_foreign'] = date('Y-m-d H:i:s', strtotime($this->order->date_add));
        $state = $this->order->getCurrentOrderState();

        if ($this->idOrderStates && !empty($this->idOrderStates[$this->order->id]) && $this->idOrderStates[$this->order->id] != $state->id) {
            $state = new \OrderState($this->idOrderStates[$this->order->id]);
        }

        if ($state) {
            $financialState = (new OrderStateFormatter($state, $this->context))->format();
            if ($financialState) {
                $data['financial_status'] = $financialState;
            }
            $data['fulfillment_status'] = $state->shipped ? 'shipped' : '';
        } else {
            $data['financial_status'] = 'pending';
        }
        $lines = $this->order->getProductsDetail();
        foreach ($lines as $line) {
            $data['lines'][] = [
                'id' => (string) $line['id_order_detail'],
                'product_id' => (string) $line['product_id'],
                'product_variant_id' => 0 == $line['product_attribute_id'] ? (string) $line['product_id'] : (string) $line['product_attribute_id'],
                'quantity' => (int) $line['product_quantity'],
                'price' => $line['unit_price_tax_incl'],
            ];
        }

        $data['promos'] = $this->getCartPromos();

        return $data;
    }

    /**
     * @return CartRule[]
     */
    protected function getCartRules()
    {
        $cart = \Cart::getCartByOrderId($this->order->id);
        // False when id_cart = 0, unloaded when the cart row was purged;
        // getCartRules() on either throws \Error, which catch (Exception) misses.
        if (!\Validate::isLoadedObject($cart)) {
            return [];
        }

        $array = $cart->getCartRules();

        if (empty($array) || !is_array($array)) {
            return [];
        }

        return array_column($array, 'obj');
    }

    /**
     * @return array
     */
    protected function getCartPromos()
    {
        $cartRules = $this->getCartRules();
        if (empty($cartRules)) {
            return [];
        }

        $data = [];

        foreach ($cartRules as $cartRule) {
            if (\Validate::isLoadedObject($cartRule)) {
                $data[] = (new OrderPromoFormatter($cartRule, $this->order, $this->context))->format();
            }
        }

        return $data;
    }
}
