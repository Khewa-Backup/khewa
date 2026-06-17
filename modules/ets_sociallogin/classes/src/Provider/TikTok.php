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
use ETSHybridauth\Data;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\User;

if (!defined('_PS_VERSION_')) { exit; }

class TikTok extends OAuth2
{
    public $scope = 'user.info.basic';
    protected $apiBaseUrl = 'https://open.tiktokapis.com/v2/';
    protected $authorizeUrl = 'https://www.tiktok.com/v2/auth/authorize/';
    protected $accessTokenUrl = 'https://open.tiktokapis.com/v2/oauth/token/';
    protected $refreshTokenUrl = 'https://open.tiktokapis.com/v2/oauth/token/';
    protected $apiDocumentation = 'https://developers.tiktok.com/doc/overview/';

    public function initialize()
    {
        // Don't call parent::initialize() to avoid parameter contamination
        // TikTok requires specific parameters and doesn't accept extra ones

        // Make sure we're only requesting basic scope that doesn't need approval
        $this->scope = 'user.info.basic';

        $this->AuthorizeUrlParameters = array(
            'response_type' => 'code',
            'client_key' => $this->clientId,     // TikTok uses client_key instead of client_id
            'redirect_uri' => $this->callback,
            'scope' => $this->scope,
            'state' => $this->generateRandomString(32) // Add state for security
        );

        // TikTok-specific token exchange parameters (NO client_id allowed)
        $this->tokenExchangeParameters = array(
            'client_key' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->callback
        );

        // Token refresh parameters
        $this->tokenRefreshParameters = array(
            'client_key' => $this->clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getStoredData('refresh_token'),
        );
    }

    /**
     * Generate random string for state parameter
     */
    private function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Override apiRequest để handle TikTok API đúng cách
     */
    public function apiRequest($url, $method = 'GET', $parameters = array(), $headers = array())
    {
        $accessToken = $this->getStoredData('access_token');
        if ($accessToken) {
            $defaultHeaders = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ];
            $headers = array_merge($defaultHeaders, $headers);
        }

        return parent::apiRequest($url, $method, $parameters, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $accessToken = $this->getStoredData('access_token');
        if (!$accessToken) {
            throw new UnexpectedApiResponseException('No access token available for TikTok API request.');
        }

        try {
            // TikTok API v2 chính thức: GET với query parameters
            // Theo docs: https://open.tiktokapis.com/v2/user/info/?fields=open_id,avatar_url,display_name

            $fields = ['open_id', 'avatar_url', 'display_name'];
            $queryString = 'fields=' . implode(',', $fields);

            // Sử dụng full URL với query string - QUAN TRỌNG: phải có dấu / cuối
            $endpoint = 'user/info/?' . $queryString;

            $response = $this->apiRequest($endpoint, 'GET', array());

            // Check for common TikTok API errors
            if (!$response) {
                throw new UnexpectedApiResponseException('TikTok API returned empty response.');
            }

            if (!is_object($response)) {
                throw new UnexpectedApiResponseException('TikTok API returned invalid response format. Response: ' . json_encode($response));
            }

            // Check for Janus error specifically
            if (property_exists($response, 'Unsupported_path(Janus)')) {
                throw new UnexpectedApiResponseException('TikTok API returned an "Unsupported_path(Janus)" error. This typically indicates an issue with the API endpoint, incorrect permissions, or an invalid/expired access token. Please verify your TikTok app configuration, API call details, and ensure your access token is valid and refreshed.');
            }

            // Check for standard API error format
            if (property_exists($response, 'error') && property_exists($response->error, 'code') && $response->error->code !== 'ok') {
                $errorMsg = 'TikTok API Error: ';
                if (property_exists($response->error, 'message')) {
                    $errorMsg .= $response->error->message;
                }
                if (property_exists($response->error, 'code')) {
                    $errorMsg .= ' (Code: ' . $response->error->code . ')';
                }
                throw new UnexpectedApiResponseException($errorMsg);
            }

            // Parse user data from response according to TikTok API v2 structure
            $userData = null;
            if (property_exists($response, 'data') && property_exists($response->data, 'user')) {
                $userData = $response->data->user;
            } elseif (property_exists($response, 'data')) {
                $userData = $response->data;
            } else {
                throw new UnexpectedApiResponseException('TikTok API response structure not recognized. Response: ' . json_encode($response));
            }

            if (!property_exists($userData, 'open_id') || !$userData->open_id) {
                throw new UnexpectedApiResponseException('TikTok API response missing required "open_id" field. User data: ' . json_encode($userData));
            }

            // Build user profile
            $data = new Data\Collection($userData);
            $userProfile = new User\Profile();

            $userProfile->identifier = $data->get('open_id');
            $userProfile->displayName = $data->get('display_name') ?: 'TikTok User';
            $userProfile->description = $data->get('bio_description') ?: '';
            $userProfile->profileURL = $data->get('profile_deep_link') ?: '';
            $userProfile->photoURL = $data->get('avatar_url') ?: $data->get('avatar_large_url');

            return $userProfile;

        } catch (\Exception $e) {
            throw new UnexpectedApiResponseException('TikTok getUserProfile Error: ' . $e->getMessage());
        }
    }
}
