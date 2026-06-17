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

namespace StripeOfficial\Classes\services\MainGetContent\Actions;

if (!defined('_PS_VERSION_')) {
    exit;
}

use StripeOfficial\Classes\StripeProcessLogger;

class RefundAction extends BaseAction
{
    public function execute()
    {
        /* Do Refund */
        if (\Tools::isSubmit('submit_refund_id')) {
            $refund_id = \Tools::getValue(\Stripe_official::REFUND_ID);
            StripeProcessLogger::debugLog('RefundAction::execute starting refund with submit_refund_id ' . $refund_id);
            $refund = null;
            if (!empty($refund_id)) {
                $query = new \DbQuery();
                $query->select('*');

                $query->from('stripe_payment');
                $query->where('id_stripe = "' . pSQL($refund_id) . '"');
                $refund = \Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query->build());
            } else {
                StripeProcessLogger::debugLog("RefundAction::execute The Stripe Payment ID can\'t be empty " . json_encode($refund_id));
                $this->module->errors[] = $this->translationService->translate('The Stripe Payment ID can\'t be empty.');
            }

            if ($refund) {
                $this->module->refund = 1;
                \Configuration::updateValue(\Stripe_official::REFUND_ID, \Tools::getValue(\Stripe_official::REFUND_ID), false, $this->getShopGroupId(), $this->getShopId());
                StripeProcessLogger::debugLog('RefundAction::execute Refund found and configuration updated ' . \Tools::getValue(\Stripe_official::REFUND_ID));
            } else {
                $this->module->refund = 0;
                $this->module->errors[] = $this->translationService->translate('Unknown Stripe Payment ID.');
                \Configuration::updateValue(\Stripe_official::REFUND_ID, '', false, $this->getShopGroupId(), $this->getShopId());
                StripeProcessLogger::debugLog('RefundAction::execute Refund NOT found and configuration updated');
            }

            $amount = null;
            $mode = (int) \Tools::getValue(\Stripe_official::REFUND_MODE);
            if ($mode === \Stripe_official::REFUND_MODE_PARTIAL) {
                $amount = \Tools::getValue(\Stripe_official::REFUND_AMOUNT);
                $amount = str_replace(',', '.', $amount);
            }

            StripeProcessLogger::debugLog("RefundAction::execute Execute refund with $mode amount $amount");

            $this->apiRefund($refund[0]['id_stripe'], $refund[0]['currency'], $mode, $refund[0]['id_cart'], $amount);

            if (!count($this->module->errors)) {
                StripeProcessLogger::debugLog('RefundAction::execute Processed successfully');
                $this->module->success = $this->translationService->translate('Refunds processed successfully');
            }
        }

        return true;
    }

    /*
 ** @Method: apiRefund
 ** @description: Make a Refund (charge) with Stripe
 **
 ** @arg: amount, id_stripe
 ** @amount: if null total refund
 ** @currency: "USD", "EUR", etc..
 ** @mode: (boolean) ? total : partial
 ** @return: (none)
 */
    public function apiRefund($refund_id, $currency, $mode, $id_card, $amount = null)
    {
        $stripeAccount = $this->module->checkApiConnection($this->module->getSecretKey());
        if (!empty($stripeAccount) && !empty($refund_id)) {
            $query = new \DbQuery();
            $query->select('*');
            $query->from('stripe_payment');
            $query->where('id_stripe = "' . pSQL($refund_id) . '"');
            $refund = \Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query->build());
            if ($mode === \Stripe_official::REFUND_MODE_FULL) { /* Total refund */
                try {
                    $stripeClient = new \Stripe\StripeClient($this->module->getSecretKey());
                    $stripeClient->refunds->create(['charge' => $refund_id]);
                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    $this->module->errors[] = $e->getMessage();
                    StripeProcessLogger::debugLog('RefundAction::apiRefund Failed to execute full refund: ' . $e->getMessage());

                    return false;
                }

                \Db::getInstance()->Execute(
                    'UPDATE `' . _DB_PREFIX_ . 'stripe_payment` SET `result` = 2, `date_add` = NOW(), `refund` = "'
                    . pSQL($refund[0]['amount']) . '" WHERE `id_stripe` = "' . pSQL($refund_id) . '"'
                );
                StripeProcessLogger::debugLog('RefundAction::apiRefund Full refund executed');
            } elseif ($mode === \Stripe_official::REFUND_MODE_PARTIAL) { /* Partial refund */
                if (!\Stripe_official::isZeroDecimalCurrency($currency)) {
                    $ref_amount = $amount * 100;
                }
                try {
                    $stripeClient = new \Stripe\StripeClient($this->module->getSecretKey());
                    $stripeClient->refunds->create([
                        'charge' => $refund_id,
                        'amount' => isset($ref_amount) ? $ref_amount : 0,
                    ]);
                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    $this->module->errors[] = $e->getMessage();
                    StripeProcessLogger::debugLog('RefundAction::apiRefund Failed to execute partial refund: ' . $e->getMessage());

                    return false;
                }

                $amount += $refund[0]['refund'];
                if ($amount == $refund[0]['amount']) {
                    $result = 2;
                } else {
                    $result = 3;
                }
                if ($amount <= $refund[0]['amount']) {
                    \Db::getInstance()->Execute(
                        'UPDATE `' . _DB_PREFIX_ . 'stripe_payment`
                        SET `result` = ' . (int) $result . ',
                            `date_add` = NOW(),
                            `refund` = "' . pSQL((string) $amount) . '"
                        WHERE `id_stripe` = "' . pSQL($refund_id) . '"'
                    );
                    StripeProcessLogger::debugLog('RefundAction::apiRefund partial refund executed with DB update');
                }
            }

            StripeProcessLogger::debugLog('RefundAction::apiRefund Refunds processed successfully');
            $this->module->success = $this->translationService->translate('Refunds processed successfully');
        } elseif (empty($stripeAccount)) {
            StripeProcessLogger::debugLog('RefundAction::apiRefund Invalid Stripe credentials, please check your configuration.');
            $this->module->errors[] = $this->translationService->translate('Invalid Stripe credentials, please check your configuration.');
        }
    }
}
