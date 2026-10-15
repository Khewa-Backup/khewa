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

namespace PrestaChamps\MailchimpPro\Hooks\Action;
if (!defined('_PS_VERSION_')) {
    exit;
}
use DrewM\MailChimp\MailChimp;
use PrestaChamps\MailchimpPro\Formatters\ListMemberFormatter;

class NewsletterRegistrationAfter
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var MailChimp
     */
    private $mailChimp;

    /**
     * @var \Context
     */
    private $context;
    private $email;

    protected function __construct($params, MailChimp $mailChimp, \Context $context, $email)
    {
        $this->params = $params;
        $this->mailChimp = $mailChimp;
        $this->context = $context;

        $this->newsletterBlockRegistration();
        $this->email = $email;
    }

    public static function run($params, MailChimp $mailchimp, \Context $context, $email)
    {
        return new static($params, $mailchimp, $context, $email);
    }

    public function newsletterBlockRegistration()
    {

        $subscriberHash = md5(\Tools::strtolower($this->email));
        $listId = $this->getListIdFromStore();

        $subscriber_iso_code = \MailchimpPro::getCustomerLanguageIsoCode($this->context->language->iso_code);

        $this->mailChimp->put(
            "/lists/{$listId}/members/{$subscriberHash}",
            [
                'email_address' => $this->email,
                'status' => $this->getListRequiresDOI($listId)
                    ? ListMemberFormatter::STATUS_PENDING
                    : ListMemberFormatter::STATUS_SUBSCRIBED,
                'language'      => $subscriber_iso_code
            ]
        );
    }

    /**
     * Get list ID from store
     *
     * @return string
     */
    protected function getListIdFromStore()
    {
        $shopId = \Mailchimppro::shopIdTransformer($this->context->shop);
        $listId = $this->mailChimp->get("/ecommerce/stores/{$shopId}", ['fields' => 'list_id']);

        if (isset($listId['list_id']) && $this->mailChimp->success()) {
            return $listId['list_id'];
        }

        throw new UnexpectedValueException("Can't determine LIST id from store");
    }

    /**
     * Decide if a list requires the Double Opt In feature
     *
     * @param $listId
     *
     * @return bool
     */
    protected function getListRequiresDOI($listId)
    {
        $list = $this->mailChimp->get("/lists/{$listId}", ['fields' => 'double_optin']);

        if (isset($list['double_optin']) && $this->mailChimp->success()) {
            return (bool)$list['double_optin'];
        }

        throw new UnexpectedValueException("Can't determine if the value requires double optin or not");
    }
}
