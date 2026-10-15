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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\Queue\Queue;

class AdminMailchimpProQueueController extends ModuleAdminController
{
    public $bootstrap = true;
    /**
     * @var Queue
     */
    protected $queue;

    public function init()
    {
        parent::init();
        $this->queue = new Queue();
    }

    public function initContent()
    {
        $this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/configuration.css');
        $this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/queue.css');
        $multistore_on_store = false;

        if ((Shop::isFeatureActive() && null != Shop::getContextShopID(true)) || !Shop::isFeatureActive()) {
            $multistore_on_store = true;
        } elseif (null == Shop::getContextShopID(true)) {
            $multistore_on_store = false;
        }

        if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) || !$multistore_on_store) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
        } else {
            $configValues = MailchimpProConfig::getConfigurationValues();

            Media::addJsDef([
                'queueUrl' => $this->context->link->getAdminLink($this->controller_name),
                'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
                'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
                'numberOfJobsAvailablePerType' => $this->queue->getNumberOfAvailableJobsPerType(),
                'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
                'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
                'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
                'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
                'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
                'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
                'healthStateFlag' => (string) PrestaChamps\MailchimpPro\HealthState::getFlag(),
                'queueBreakerActive' => PrestaChamps\MailchimpPro\HealthState::isBreakerActive()
                    || !Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED),
                'mailchimpConfigUrl' => $this->context->link->getAdminLink('AdminMailchimpProConfiguration') . '#advanced-settings',
            ]);
            $this->context->smarty->assign([
                'mainJsPath' => Media::getJSPath(
                    $this->module->getLocalPath() . 'views/js/queue/main.js'
                ) . '?v=' . $this->module->version,
                'JsLybraryPath' => Media::getJSPath(
                    $this->module->getLocalPath() . 'views/js/'
                ),
            ]);
            $this->content = $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/queue/main.tpl'
            );
        }
        parent::initContent();
    }

    public function ajaxProcessGetQueueStats()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
            'perType' => $this->queue->getNumberOfAvailableJobsPerType(),
        ]);
    }

    public function ajaxProcessRunJob()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'message' => $this->queue->runJob(),
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
            'perType' => $this->queue->getNumberOfAvailableJobsPerType(),
        ]);
    }

    public function ajaxProcessClearJobs()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'message' => $this->queue->clearChannel(),
            'numberOfJobsAvailable' => $this->queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $this->queue->getNumberOfJobsInFlight(),
        ]);
    }

    /**
     * @param mixed $value
     * @param string|null $controller
     * @param string|null $method
     * @param int $statusCode
     *
     * @throws PrestaShopException
     */
    public function ajaxDie($value = null, $controller = null, $method = null, $statusCode = 200)
    {
        // Auto-inject health state so the Vue app can react on every response.
        if (is_array($value)) {
            $value = array_merge([
                'healthStateFlag' => (string) PrestaChamps\MailchimpPro\HealthState::getFlag(),
                'queueBreakerActive' => PrestaChamps\MailchimpPro\HealthState::isBreakerActive()
                    || !Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED),
            ], $value);
        }

        header('Content-Type: application/json');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        http_response_code($statusCode);
        if ((bool) version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            exit;
        }
        // Legacy fallback for PS 1.7.0.6 → 1.7.4.x (pre-ajaxRender). Controller::ajaxDie
        // was removed in PS 9.0 — dynamic call hides it from PHPStan's static surface.
        call_user_func(['parent', 'ajaxDie'], $value, $controller, $method);
    }
}
