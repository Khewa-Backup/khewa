<?php
/**
 * MailChimp
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

namespace PrestaChamps\MailchimpPro\Hooks\Display;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand;
use PrestaChamps\MailchimpPro\Helpers\PsContext;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\Queue\Queue;

/**
 * Class FooterBefore
 */
class FooterBefore
{
    /**
     * @var \Context
     */
    private $_context;

    protected function __construct($params, MailChimpAPI $mailChimp, \Context $context)
    {
        // $params and $mailChimp accepted for API parity with the public run()
        // signature (called from hook handlers that pass standard PS args);
        // unused since the inline sync code path was extracted from this class.
        unset($params, $mailChimp);

        $this->_context = $context;
    }

    public static function run($params, MailChimpAPI $mailchimp, \Context $context)
    {
        return new FooterBefore($params, $mailchimp, $context);
    }

    public function newsletterBlockRegistration()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
            $subscriptionIsEnabled = call_user_func(['Module', 'isEnabled'], 'Ps_Emailsubscription')
                || call_user_func(['Module', 'isEnabled'], 'blocknewsletter');
            if (\Tools::isSubmit('submitNewsletter') && $subscriptionIsEnabled) {
                // If PrestaShop Double Opt-In is enabled, skip sync here —
                // actionNewsletterRegistrationAfter will handle it after email verification
                if ($this->isPrestaShopDoubleOptInEnabled()) {
                    return false;
                }

                $newsletterSubscriber['email'] = \Tools::getValue('email');
                if (isset($this->_context->language->id) && $this->_context->language->id) {
                    $newsletterSubscriber['id_lang'] = $this->_context->language->id;
                }
                if (!\Configuration::get(\MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    /** @var \Mailchimppro $module */
                    $module = \Module::getInstanceByName('mailchimppro');
                    $command = new NewsletterSubscriberSyncCommand(
                        PsContext::getContext(),
                        $module->getApiClient(),
                        [$newsletterSubscriber]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PUT);

                    return $command->execute();
                }
                $job = new NewsletterSubscriberSyncJob();
                $job->newsletterSubscriber = $newsletterSubscriber;
                $job->setSyncMode(NewsletterSubscriberSyncCommand::SYNC_MODE_REGULAR);
                $job->setMethod(NewsletterSubscriberSyncCommand::SYNC_METHOD_PUT);
                $queue = new Queue();
                $queue->push($job, 'hook-footer-before', $this->_context->shop->id);

                return true;
            }
        }

        return false;
    }

    /**
     * Check if PrestaShop's ps_emailsubscription Double Opt-In is enabled.
     *
     * @return bool
     */
    protected function isPrestaShopDoubleOptInEnabled()
    {
        return (bool) \Configuration::get('NW_VERIFICATION_EMAIL');
    }
}
