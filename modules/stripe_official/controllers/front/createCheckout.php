<?php
/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use StripeOfficial\Classes\StripeProcessLogger;
use StripeOfficial\Controllers\Traits\GeneralTrait;
use StripeOfficial\Controllers\Traits\StripeTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

class stripe_officialCreateCheckoutModuleFrontController extends ModuleFrontController
{
    use GeneralTrait;
    use StripeTrait;
    const SESSION_CREATE = '_SESSION_CREATE';

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $checkoutData = [];

        try {
            $cart = $this->context->cart;
            StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Checkout Session Creation Beginning ', $this->context->cart->id), 'initContent - constructCheckoutData');

            $language = new Language();
            $currency = new Currency($cart->id_currency);
            $shippingAddress = new Address($this->context->cart->id_address_delivery);
            $shippingAddressState = new State();
            $country = Country::getIsoById($shippingAddress->id_country);
            $shippingAddress = $this->getShippingDetails($shippingAddress, $shippingAddressState, $country, $this->context->customer);

            $intentData = $this->constructIntentData($this->context, true, Configuration::get(Stripe_official::CATCHANDAUTHORIZE));
            $intentData['shipping'] = $shippingAddress;
            $finalPrice = Stripe_official::isZeroDecimalCurrency($currency->iso_code) ? $cart->getOrderTotal() : $cart->getOrderTotal() * 100;

            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency->iso_code,
                    'unit_amount_decimal' => round($finalPrice, 2),
                    'product_data' => [
                        'name' => $this->context->shop->name,
                    ],
                ],
                'quantity' => 1,
            ];

            $customer = $this->getCustomerDetails($this->context->customer);
            $checkoutData = $this->constructCheckoutData($intentData, $lineItems, $customer, $language);

            $stripeIdempotencyKeyObject = new \StripeIdempotencyKey();
            $stripeIdempotencyKeyObject->updateIdempotencyKey($checkoutData, $this->context->cart->id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Stripe Account Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'initContent - constructCheckoutData');
        } catch (PrestaShopDatabaseException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Prestashop State Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'initContent - constructCheckoutData');
        } catch (PrestaShopException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Prestashop State Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'initContent - constructCheckoutData');
        }

        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Checkout Session Creation Ending => ' . json_encode($checkoutData), $this->context->cart->id, $checkoutData->id), 'initContent - constructCheckoutData');

        echo json_encode([
            'checkout' => $checkoutData,
        ]);
        exit;
    }

    private function constructCheckoutData($intent, $lineItems, $customer = null, $locale = null)
    {
        $checkoutSession = [];
        try {
            $stripeIdempotencyKey = $this->getOrCreateIdempotencyKey($this->context->cart->id);
            $stripeOrderFailureReturnUrl = $this->context->link->getModuleLink(
                'stripe_official',
                'orderFailure',
                [],
                true
            );

            $stripeOrderSuccessReturnUrl = $this->context->link->getModuleLink(
                'stripe_official',
                'orderConfirmationReturn',
                ['cartId' => $this->context->cart->id],
                true
            );

            $checkoutParams = [
                'line_items' => $lineItems,
                'payment_intent_data' => $intent,
                'mode' => Session::MODE_PAYMENT,
                'locale' => ($locale->iso_code ?: 'auto'),
                'metadata' => [
                    'id_cart' => $this->context->cart->id,
                ],
                'success_url' => $stripeOrderSuccessReturnUrl,
                'cancel_url' => $stripeOrderFailureReturnUrl,
            ];
            if ($customer) {
                $checkoutParams['customer'] = $customer;
            }
            $checkoutSession = Session::create($checkoutParams, [
                'idempotency_key' => $stripeIdempotencyKey->idempotency_key . self::SESSION_CREATE,
            ]);
        } catch (ApiErrorException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Stripe Account Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'constructCheckoutData');
        } catch (PrestaShopDatabaseException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Prestashop State Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'constructCheckoutData');
        } catch (PrestaShopException $e) {
            StripeProcessLogger::exceptionErrorLogger(StripeProcessLogger::getFormattedMessageLogs('Retrieve Prestashop State Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), $this->context->cart->id), 'constructCheckoutData');
        }
        StripeProcessLogger::logInfo(StripeProcessLogger::getFormattedMessageLogs('Construct Checkout Data Ending ' . json_encode($checkoutSession), $this->context->cart->id, $checkoutSession->id), 'constructCheckoutData');

        return $checkoutSession;
    }
}
