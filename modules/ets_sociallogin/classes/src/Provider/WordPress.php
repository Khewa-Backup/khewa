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
 * WordPress OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class WordPress extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://public-api.wordpress.com/rest/v1/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://public-api.wordpress.com/oauth2/authenticate';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://public-api.wordpress.com/oauth2/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://developer.wordpress.com/docs/api/';

    /**
    * {@inheritdoc}
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me/');

        $data = new Data\Collection($response);

        if (! $data->exists('ID')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('ID');
        $userProfile->displayName = $data->get('display_name');
        $userProfile->photoURL    = $data->get('avatar_URL');
        $userProfile->profileURL  = $data->get('profile_URL');
        $userProfile->email       = $data->get('email');
        $userProfile->language    = $data->get('language');

        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');

        $userProfile->emailVerified = $data->get('email_verified') ? $data->get('email') : '';

        return $userProfile;
    }
}
