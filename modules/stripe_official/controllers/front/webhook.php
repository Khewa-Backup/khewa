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

use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\RateLimitException;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

// ToDo rewrite this abomination
class stripe_officialWebhookModuleFrontController extends ModuleFrontController
{
    /**
     * Override displayMaintenancePage to prevent the maintenance page to be displayed.
     *
     * @see FrontController::displayMaintenancePage()
     */
    protected function displayMaintenancePage()
    {
    }

    /**
     * Override displayRestrictedCountryPage to prevent page country is not allowed.
     *
     * @see FrontController::displayRestrictedCountryPage()
     */
    protected function displayRestrictedCountryPage()
    {
    }

    /**
     * Override geolocationManagement to prevent country GEOIP blocking.
     *
     * @see FrontController::geolocationManagement()
     *
     * @param Country $defaultCountry
     *
     * @return false
     */
    protected function geolocationManagement($defaultCountry)
    {
        return false;
    }

    /**
     * Override sslRedirection to prevent redirection.
     *
     * @see FrontController::sslRedirection()
     */
    protected function sslRedirection()
    {
    }

    /**
     * Override canonicalRedirection to prevent redirection.
     *
     * @see FrontController::canonicalRedirection()
     *
     * @param string $canonical_url
     */
    protected function canonicalRedirection($canonical_url = '')
    {
    }

    public function postProcess()
    {
        StripeProcessLogger::debugLog('webhook request arrived: ' . json_encode($_SERVER));

        try {
            // Retrieve payload
            $eventPayload = @Tools::file_get_contents('php://input');

            // Retrieve http signature
            $signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : null;
            if (!$signature) {
                StripeProcessLogger::logError('Missing Stripe signature header', 'WebhookController');
                $this->respondAndExit(400, 'Missing signature header');
            }

            // set context
            $secret = WebhookEventHandler::getWebhookSecret($eventPayload, $signature);

            // Process event
            $this->module->updateContext();
            $webhookHandler = new WebhookEventHandler(Context::getContext(), $this->module);
            $webhookHandler->handleRequest($eventPayload, $signature, $secret);

            $this->respondAndExit(200, 'Webhook handled successfully!');
        } catch (ApiConnectionException $e) {
            StripeProcessLogger::logError('Webhook retryable Stripe connection error: ' . $e->getMessage(), 'WebhookController');
            $this->respondAndExit(500, 'Temporary Stripe API error');
        } catch (RateLimitException $e) {
            StripeProcessLogger::logError('Webhook retryable Stripe rate limit error: ' . $e->getMessage(), 'WebhookController');
            $this->respondAndExit(500, 'Stripe rate limit exceeded');
        } catch (Throwable $e) {
            StripeProcessLogger::logError('Webhook non-retryable error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'WebhookController');
            $this->respondAndExit(200, 'Webhook processing error logged');
        }
    }

    /**
     * @param int $statusCode
     * @param string $message
     */
    private function respondAndExit($statusCode, $message)
    {
        http_response_code($statusCode);
        echo $message;
        exit;
    }
}
