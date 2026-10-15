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
 * @author    PrestaChamps <leo@prestachamps.com>
 * @copyright PrestaChamps
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Formatters;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Helpers\CustomerAddressSelector;
use PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer;
use PrestaChamps\MailchimpPro\Models\Campaign;

/**
 * Class ListMemberFormatter
 */
class ListMemberFormatter
{
    public const EMAIL_TYPE_HTML = 'html';
    public const EMAIL_TYPE_TEXT = 'text';

    public const STATUS_SUBSCRIBED = 'subscribed';
    public const STATUS_TRANSACTIONAL = 'transactional';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';
    public const STATUS_CLEANED = 'cleaned';
    public const STATUS_PENDING = 'pending';

    public $customer;
    public $address;
    public $context;
    public $status;
    public $emailType;
    protected $updateSubscriptionStatus = true;

    protected $idStore;

    /**
     * ListMemberFormatter constructor.
     *
     * @param bool $updateSubscriptionStatus
     */
    public function __construct(\Customer $customer, \Context $context, $status, $emailType, ?\Address $address = null, $updateSubscriptionStatus = true, $idStore = null)
    {
        $this->customer = $customer;
        $this->address = $address;
        $this->context = $context;
        $this->status = $status;
        $this->emailType = $emailType;
        $this->updateSubscriptionStatus = $updateSubscriptionStatus;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set updateSubscriptionStatus feature.
     *
     * @param bool $update
     */
    public function setUpdateSubscriptionStatus($update = true)
    {
        $this->updateSubscriptionStatus = (bool) $update;
    }

    /**
     * @return array
     */
    public function format()
    {
        /* $customer = $this->customer;
        $addresses = $customer->getAddresses((int) \Configuration::get('PS_LANG_DEFAULT'));
        if (!empty($addresses)) {
            $address = reset($addresses);
            $address = new \Address($address['id_address'], (int) \Configuration::get('PS_LANG_DEFAULT'));

        } else {
            $address = null;
        } */

        if (!$this->address) {
            $this->clearCustomerAddressesCache();
            // Prefer the most-recently-updated address (date_upd DESC)
            // over the legacy "oldest by id_address ASC" reset() pick —
            // see CustomerAddressSelector for the rationale. No cart
            // context here (audience sync runs in cron / event handlers,
            // not in the checkout request lifecycle), so path 2 of the
            // selector applies.
            $picked = CustomerAddressSelector::pickPrimary($this->customer);
            if ($picked) {
                $this->address = $picked;
            }
        }

        $language = new \Language($this->customer->id_lang);
        $customer_iso_code = \Mailchimppro::getCustomerLanguageIsoCode($language->iso_code);

        if ($this->address) {
            $state = $this->address->id_state ? \State::getNameById($this->address->id_state) : '';
            $country_iso = $this->address->id_country ? \Country::getIsoById($this->address->id_country) : '';

            if (!$this->address->id_country) {
                $shop_country = (new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT')))->iso_code;

                if ('' == $country_iso) {
                    $country_iso = $shop_country;
                }
                // need to check on multi store
            }

            $customer_address = [
                'addr1' => $this->address->address1 ?: '-',
                'addr2' => $this->address->address2 ?: '',
                'city' => $this->address->city ?: '-',
                'state' => $this->address->id_state && $state ? $state : '-',
                'zip' => $this->address->postcode ?: '-',
                'country' => $this->address->id_country && $country_iso ? $country_iso : '',
            ];
        } else {
            $customer_address = '';
        }

        if ($this->customer->birthday && '0000-00-00' != $this->customer->birthday) {
            $customer_birthday = date('m/d', strtotime($this->customer->birthday));
        } else {
            $customer_birthday = '';
        }

        $promo_campaigns = Campaign::getActiveCampaignsWithPromoCodes($this->customer->email);

        $extra_fields = [];
        if (count($promo_campaigns) > 0) {
            foreach ($promo_campaigns as $promo_campaign) {
                if (!(is_null($promo_campaign['campaign']['id_merge_field_mc']) || 0 == $promo_campaign['campaign']['id_merge_field_mc'])) {
                    $extra_fields[$promo_campaign['campaign']['tag_merge_field_mc']] = $promo_campaign['promo_code']->code;
                }
            }
        }

        $data = [
            'email_address' => $this->customer->email,
            'email_type' => $this->emailType,
            'language' => $customer_iso_code,
            'timestamp_signup' => gmdate('Y-m-d H:i:s', time()),
            'merge_fields' => [
                'FNAME' => $this->customer->firstname,
                'LNAME' => $this->customer->lastname,
                'BIRTHDAY' => $customer_birthday,
                'ADDRESS' => $customer_address,
                /* 'GROUP' => (\Group::isFeatureActive())
                    ? (new \Group($this->customer->id_default_group, (int) \Configuration::get('PS_LANG_DEFAULT')))->name
                    : 'Default group',
                'CITY'     => ($this->address) ? $this->address->city : '',
                'STATE'    => ($this->address) ? \State::getNameById($this->address->id_state) : '',
                'ZIP'      => ($this->address) ? $this->address->postcode : '',
                'COUNTRY'  => ($this->address) ? $this->address->country : '',
                'COMPANY'  => $this->customer->company ? $this->customer->company : '', */
            ],
        ];

        if (count($extra_fields) > 0) {
            $data['merge_fields'] = array_merge($data['merge_fields'], $extra_fields);
        }

        // COMPANY merge field: the customer's own company, and only optionally
        // the company on their address (off by default — a delivery-address
        // company doesn't make the customer a business, and shops segment their
        // professional customers on this field).
        //
        // Omitted when empty so an existing Mailchimp-side value is never wiped
        // by a company-less sync. No gate on whether the audience HAS a COMPANY
        // field — Mailchimp drops unknown merge tags (skip_merge_validation).
        //
        // Trimmed before testing: a whitespace-only company is truthy in PHP,
        // so an untrimmed check would both ship "   " to Mailchimp and skip the
        // address fallback for that customer.
        $company = trim((string) $this->customer->company);
        if ('' === $company
            && $this->address
            && (bool) \Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_COMPANY_FROM_ADDRESS)) {
            $company = trim((string) $this->address->company);
        }
        if ('' !== $company) {
            $data['merge_fields']['COMPANY'] = $company;
        }

        // Phone — mobile wins over landline (preserved from legacy
        // behavior; matches user expectations for "primary contact").
        // All values funnel through PhoneNormalizer so the string we
        // send to merge_fields.PHONE matches what the pixel later
        // hashes for api.identify({type:'PHONE_SHA256'}) — same
        // normalization on both surfaces is what lets Mailchimp's
        // server-side PHONE_SHA256 matching correlate pixel events
        // back to this audience subscriber.
        $landline = $this->address ? PhoneNormalizer::normalize($this->address->phone) : '';
        $mobile = $this->address ? PhoneNormalizer::normalize($this->address->phone_mobile) : '';
        if ('' !== $mobile) {
            $data['merge_fields']['PHONE'] = $mobile;
        } elseif ('' !== $landline) {
            $data['merge_fields']['PHONE'] = $landline;
        } else {
            $data['merge_fields']['PHONE'] = '';
        }

        if ($this->updateSubscriptionStatus) {
            $data['status'] = $this->status;
        }

        return $data;
    }

    /**
     * Clears the customer addresses cache.
     */
    protected function clearCustomerAddressesCache()
    {
        $group = $this->context->shop->getGroup();
        $shareOrder = isset($group->share_order) ? (bool) $group->share_order : false;
        $cacheId = 'Customer::getAddresses'
            . '-' . (int) $this->customer->id
            . '-' . (int) \Configuration::get('PS_LANG_DEFAULT')
            . '-' . ($shareOrder ? 1 : 0);

        \Cache::clean($cacheId);
    }
}
