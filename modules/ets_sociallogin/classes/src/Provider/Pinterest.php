<?php
/**
 * 2007-2017 ETSHybridauth
 *
 * @author Hybridauth <https://hybridauth.github.io>
 * @copyright  2009-2017 Hybridauth
 * @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Provider;

use ETSHybridauth\Adapter\OAuth2;
use ETSHybridauth\Data;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\User;

/**
 * GitLab OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Pinterest extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'user_accounts:read';//user_accounts:read, read_public

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.pinterest.com/v5/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.pinterest.com/oauth/';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.pinterest.com/v5/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.pinterest.com/docs/api/v5/';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();
        $this->tokenExchangeParameters = array(
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callback,
        );
        $this->tokenExchangeHeaders = array(
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
        );
        $this->tokenRefreshParameters = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getStoredData('refresh_token'),
        );
        $this->tokenRefreshHeaders = $this->tokenExchangeHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user_account');

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $username = $data->get('username');
        $userProfile->identifier = $data->get('id');
        $userProfile->description = $data->get('about');
        $userProfile->photoURL = $data->get('profile_image');
        $display = $data->get('business_name');
        if ($display === null || $display === '') {
            $display = $username;
        }
        $userProfile->displayName = $display;
        $userProfile->firstName = $data->get('first_name');
        $userProfile->lastName = $data->get('last_name');
        $userProfile->profileURL = 'https://www.pinterest.com/' . rawurlencode($username) . '/';

        $userProfile->data = array(
            'follower_count' => $data->get('follower_count'),
            'following_count' => $data->get('following_count'),
            'pin_count' => $data->get('pin_count'),
            'board_count' => $data->get('board_count'),
            'account_type' => $data->get('account_type'),
            'monthly_views' => $data->get('monthly_views'),
        );

        return $userProfile;
    }
}
