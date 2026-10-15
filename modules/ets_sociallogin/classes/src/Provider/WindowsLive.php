<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Provider;

use ETSHybridauth\Adapter\OAuth2;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * Windows Live OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class WindowsLive extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    public $scope = 'openid profile email User.Read Contacts.Read';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://graph.microsoft.com/v1.0/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow';

    /**
    * {@inheritdoc}
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me');

        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier    = $data->get('id');
        $userProfile->displayName   = $data->get('displayName');
        $userProfile->firstName     = $data->get('givenName');
        $userProfile->lastName      = $data->get('surname');
        $userProfile->email         = $data->get('mail') ?: $data->get('userPrincipalName');
        $userProfile->language      = $data->get('preferredLanguage');

        return $userProfile;
    }

    /**
    * {@inheritdoc}
    */
    public function getUserContacts()
    {
        $response = $this->apiRequest('me/contacts');

        $data = new Data\Collection($response);

        if (! $data->exists('value')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $contacts = array();

        foreach ($data->filter('value')->toArray() as $entry) {
            $userContact = new User\Contact();

            $userContact->identifier  = $entry->get('id');
            $userContact->displayName = $entry->get('displayName');
            
            $emailAddresses = $entry->get('emailAddresses');
            if (!empty($emailAddresses) && is_array($emailAddresses)) {
                if (isset($emailAddresses[0]->address)) {
                    $userContact->email = $emailAddresses[0]->address;
                } elseif (isset($emailAddresses[0]['address'])) {
                    $userContact->email = $emailAddresses[0]['address'];
                }
            }

            $contacts[] = $userContact;
        }

        return $contacts;
    }
}
