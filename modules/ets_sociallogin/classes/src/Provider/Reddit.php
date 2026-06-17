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
 * Reddit OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Reddit extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    protected $scope = 'identity';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://oauth.reddit.com/api/v1/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://ssl.reddit.com/api/v1/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://github.com/reddit/reddit/wiki/OAuth2';

    /**
    * {@inheritdoc}
    */
    protected function initialize()
    {
        parent::initialize();
        $this->AuthorizeUrlParameters += array(
            'duration' => 'permanent'
        );

        $this->tokenExchangeParameters = array(
            'client_id'    => $this->clientId,
            'grant_type'   => 'authorization_code',
            'redirect_uri' => $this->callback
        );

        $this->tokenExchangeHeaders = array(
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret)
        );

        $this->tokenRefreshHeaders = $this->tokenExchangeHeaders;
        
    }

    /**
    * {@inheritdoc}
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me.json');

        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('id');
        $userProfile->displayName = $data->get('name');
        $userProfile->profileURL  = 'https://www.reddit.com/user/' . $data->get('name') . '/';

        return $userProfile;
    }
}
