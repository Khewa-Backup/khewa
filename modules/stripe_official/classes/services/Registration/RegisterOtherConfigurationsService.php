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

namespace StripeOfficial\Classes\services\Registration;

use StripeOfficial\Classes\services\PrestashopTranslationService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RegisterOtherConfigurationsService
{
    /**
     * @var PrestashopTranslationService
     */
    protected $translationService;
    protected $module;

    public function __construct($module, PrestashopTranslationService $translationService)
    {
        $this->module = $module;
        $this->translationService = $translationService;
    }

    public function registerOtherConfigurations()
    {
        $shopGroupId = \Stripe_official::getShopGroupIdContext();
        $shopId = \Stripe_official::getShopIdContext();

        $neverCollectPostcode = 0;
        $expressCheckout = (int) \Tools::getValue('express_checkout');

        $postedLocations = \Tools::getValue('stripe_locations');
        if ($expressCheckout && empty($postedLocations)) {
            $postedLocations = ['product', 'cart'];
        }

        if (gettype($postedLocations) === 'boolean') {
            $locations = $postedLocations;
        } else {
            $locations = implode(', ', (array) $postedLocations);
        }

        $applePayButtonTheme = \Tools::getValue('apple_pay_button_theme') ?: 'black';
        $applePayButtonType = \Tools::getValue('apple_pay_button_type') ?: 'plain';
        $googlePayButtonTheme = \Tools::getValue('google_pay_button_theme') ?: 'black';
        $googlePayButtonType = \Tools::getValue('google_pay_button_type') ?: 'plain';
        $payPalButtonTheme = \Tools::getValue('pay_pal_button_theme') ?: 'black';
        $payPalButtonType = \Tools::getValue('pay_pal_button_type') ?: 'paypal';
        $klarnaButtonTheme = \Tools::getValue('klarna_button_theme') ?: 'dark';
        $klarnaButtonType = \Tools::getValue('klarna_button_type') ?: 'continue';

        $enableApplePay = \Tools::getIsset('enable_apple_pay') ? 1 : 0;
        $enableGooglePay = \Tools::getIsset('enable_google_pay') ? 1 : 0;
        $enablePayPal = \Tools::getIsset('enable_pay_pal') ? 1 : 0;
        $enableKlarnaExpress = \Tools::getIsset('enable_klarna_express') ? 1 : 0;
        $enableAmazonPay = \Tools::getIsset('enable_amazon_pay') ? 1 : 0;
        $enableLinkExpress = \Tools::getIsset('enable_link_express') ? 1 : 0;
        $enablePaymentMethodMessaging = \Tools::getIsset('enable_payment_method_messaging') ? 1 : 0;

        \Configuration::updateValue(\Stripe_official::POSTCODE, $neverCollectPostcode, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_PAYMENT_ELEMENTS, (int) \Tools::getValue('payment_element'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::THEME, \Tools::getValue('stripe_theme'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::POSITION, \Tools::getValue('stripe_position'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::LAYOUT, \Tools::getValue('stripe_layout'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_EXPRESS_CHECKOUT, $expressCheckout, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::EXPRESS_CHECKOUT_LOCATIONS, $locations, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::APPLE_PAY_BUTTON_THEME, $applePayButtonTheme, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::APPLE_PAY_BUTTON_TYPE, $applePayButtonType, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::GOOGLE_PAY_BUTTON_THEME, $googlePayButtonTheme, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::GOOGLE_PAY_BUTTON_TYPE, $googlePayButtonType, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::PAY_PAL_BUTTON_THEME, $payPalButtonTheme, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::PAY_PAL_BUTTON_TYPE, $payPalButtonType, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::KLARNA_BUTTON_THEME, $klarnaButtonTheme, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::KLARNA_BUTTON_TYPE, $klarnaButtonType, false, $shopGroupId, $shopId);

        \Configuration::updateValue(\Stripe_official::ENABLE_APPLE_PAY, $enableApplePay, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_GOOGLE_PAY, $enableGooglePay, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_PAY_PAL, $enablePayPal, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_KLARNA_EXPRESS, $enableKlarnaExpress, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_AMAZON_PAY, $enableAmazonPay, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_LINK_EXPRESS, $enableLinkExpress, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_PAYMENT_METHOD_MESSAGING, $enablePaymentMethodMessaging, false, $shopGroupId, $shopId);

        \Configuration::updateValue(\Stripe_official::ORDER_FLOW, (int) \Tools::getValue('stripe_order_flow'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_SAVE_PAYMENT_METHOD, \Tools::getValue('save_payment_method'), false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_EXTENDED_LOGGING, \Tools::getValue('extended_logging') ? 1 : 0, false, $shopGroupId, $shopId);
        \Configuration::updateValue(\Stripe_official::ENABLE_TESTING_ASSISTANT, \Tools::getIsset('enable_testing_assistant') ? 1 : 0, false, $shopGroupId, $shopId);

        if (!count($this->module->errors)) {
            $this->module->success = $this->translationService->trans('Settings updated successfully.', [], 'Modules.Stripeofficial.ConfigurationActions');
        }

        return true;
    }
}
