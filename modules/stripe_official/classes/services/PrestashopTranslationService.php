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

namespace StripeOfficial\Classes\services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopTranslationService
{
    /**
     * @var \PaymentModule
     */
    private $paymentModule;

    private $oldTranslationSpecific;

    public function __construct(\PaymentModule $paymentModule, $oldTranslationSpecific = false)
    {
        $this->paymentModule = $paymentModule;
        $this->oldTranslationSpecific = $oldTranslationSpecific;
    }

    public function trans(string $string, array $params, string $module)
    {
        if ($this->hasNewTranslationSystem()) {
            $parameters = [
                'legacy' => 'htmlspecialchars',
            ];
            $parameters = $parameters + $params;

            return $this->paymentModule->getTranslator()->trans($string, $parameters, $module);
        } else {
            return $this->paymentModule->l($string, $this->paymentModule->name, $this->oldTranslationSpecific);
        }
    }

    public function hasNewTranslationSystem(): bool
    {
        return self::useNewTranslationSystem();
    }

    public static function useNewTranslationSystem(): bool
    {
        if (version_compare(_PS_VERSION_, '1.7.6', '>=')) {
            return true;
        }

        return false;
    }

    /**
     * Because of old style translation system, in twig files a translation group is generated for each twig file name
     * To prevent this the translation keys with the correct group name are listed here
     * It must use the ->trans( function, otherwise prestashop will not detect the translation keys and they will not be available in the translation interface
     * This is why the keys are there twice and not resolved in a foreach
     */
    public function getTemplateTranslationMap()
    {
        return [
            'Order processed' => $this->trans('Order processed', [], 'Modules.Stripeofficial.Handlenextaction'),
            'Please check your order’s payment status in [a @href1@]My Account -> Order history and details[/a].' => $this->trans('Please check your order’s payment status in [a @href1@]My Account -> Order history and details[/a].', [], 'Modules.Stripeofficial.Handlenextaction'),
            'Confirm your transaction' => $this->trans('Confirm your transaction', [], 'Modules.Stripeofficial.Handlenextaction'),
            'To authorize the transaction please follow the steps indicated by your payment method.' => $this->trans('To authorize the transaction please follow the steps indicated by your payment method.', [], 'Modules.Stripeofficial.Handlenextaction'),
            'Congratulations, your order has been placed and will be processed soon.' => $this->trans('Congratulations, your order has been placed and will be processed soon.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'Your order reference is [b]@target@[/b], you should receive a confirmation e-mail shortly.' => $this->trans('Your order reference is [b]@target@[/b], you should receive a confirmation e-mail shortly.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'Your can see your OXXO voucher [a @href1@]here[/a].' => $this->trans('Your can see your OXXO voucher [a @href1@]here[/a].', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'We appreciate your business.' => $this->trans('We appreciate your business.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'An error occured during your payment.' => $this->trans('An error occured during your payment.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'Please [a @href1@]try again[/a] or contact the website owner.' => $this->trans('Please [a @href1@]try again[/a] or contact the website owner.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'Processing your order...' => $this->trans('Processing your order...', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'You will be redirected to an external payment page.' => $this->trans('You will be redirected to an external payment page.', [], 'Modules.Stripeofficial.Orderconfirmation'),
            'Payment ID' => $this->trans('Payment ID', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Payment method' => $this->trans('Payment method', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Authorize date' => $this->trans('Authorize date', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Capture date' => $this->trans('Capture date', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Expired' => $this->trans('Expired', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Payment dispute' => $this->trans('Payment dispute', [], 'Modules.Stripeofficial.Admincontentorder'),
            'No dispute' => $this->trans('No dispute', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Voucher Validation' => $this->trans('Voucher Validation', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Voucher validate on:' => $this->trans('Voucher validate on:', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Voucher will expire on:' => $this->trans('Voucher will expire on:', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Stripe Customer ID' => $this->trans('Stripe Customer ID', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Risk evaluation' => $this->trans('Risk evaluation', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Score:' => $this->trans('Score:', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Level:' => $this->trans('Level:', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Amount Refunded:' => $this->trans('Amount Refunded:', [], 'Modules.Stripeofficial.Admincontentorder'),
            'check your dispute here' => $this->trans('check your dispute here', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Stripe' => $this->trans('Stripe', [], 'Modules.Stripeofficial.Admincontentorder'),
            'Enable Payment Method Messaging' => $this->trans('Enable Payment Method Messaging', [], 'Modules.Stripeofficial.ConfigurationActions'),
            'Shows BNPL promotional messaging (Klarna, Afterpay, Affirm) on product and cart pages. Only enable if BNPL payment methods are active on your Stripe account.' => $this->trans('Shows BNPL promotional messaging (Klarna, Afterpay, Affirm) on product and cart pages. Only enable if BNPL payment methods are active on your Stripe account.', [], 'Modules.Stripeofficial.ConfigurationActions'),
            'Enable Stripe testing assistant - Shows an interactive assistant on checkout pages in test mode to autofill cards and debug your integration.' => $this->trans('Enable Stripe testing assistant - Shows an interactive assistant on checkout pages in test mode to autofill cards and debug your integration.', [], 'Modules.Stripeofficial.Configuration'),
        ];
    }
}
