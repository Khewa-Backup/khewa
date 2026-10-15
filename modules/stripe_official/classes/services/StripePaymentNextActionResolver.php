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
if (!defined('_PS_VERSION_')) {
    exit;
}

class StripePaymentNextActionResolver
{
    public function resolveNextAction($confirmedPaymentIntent, $cartId, $redirectUrl, $context)
    {
        $returnUrl = $redirectUrl;

        if ($confirmedPaymentIntent && isset($confirmedPaymentIntent->next_action) && $confirmedPaymentIntent->next_action->count()) {
            if (!empty($confirmedPaymentIntent->next_action['alipay_handle_redirect']['url'])) {
                $returnUrl = $confirmedPaymentIntent->next_action['alipay_handle_redirect']['url'];
            } elseif (!empty($confirmedPaymentIntent->next_action['redirect_to_url']['url'])) {
                $returnUrl = $confirmedPaymentIntent->next_action['redirect_to_url']['url'];
            } else {
                $returnUrl = $context->link->getModuleLink(
                    'stripe_official',
                    'handleNextAction',
                    [
                        'paymentIntentId' => $confirmedPaymentIntent->id,
                        'cartId' => $cartId,
                        'key' => PrestashopCartService::getCartUniqueKey($cartId),
                    ],
                    true
                );
            }
        }

        return $returnUrl;
    }
}
