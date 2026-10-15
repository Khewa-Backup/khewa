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
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * GitLab OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Paypal extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'openid profile email';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.sandbox.paypal.com';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.sandbox.paypal.com/connect';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.sandbox.paypal.com/v1/oauth2/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.paypal.com/docs/api/overview/#';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();
        $this->AuthorizeUrlParameters += [
            'flowEntry' => 'static'
        ];

        $this->tokenExchangeHeaders = [
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret)
        ];

        $this->tokenRefreshHeaders = [
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret)
        ];
        if (!(int)\Configuration::get('ETS_SOLO_PAYPAL_SANDBOX_MODE')) {
            $this->apiBaseUrl = 'https://api.paypal.com/';
            $this->authorizeUrl = 'https://www.paypal.com/signin/authorize';//'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
            $this->accessTokenUrl = 'https://api.paypal.com/v1/oauth2/token';//'https://api.paypal.com/v1/identity/openidconnect/tokenservice';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $parameters = [
            'schema' => 'paypalv1.1'
        ];

        $response = $this->apiRequest('v1/identity/oauth2/userinfo', 'GET', $parameters, $headers);
        $data = new Data\Collection($response);

        if (!$data->exists('user_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $email = $data->get('email');
        if (!$email) {
            $emails = $data->get('emails');
            if ($emails && is_array($emails) && isset($emails[0]) && isset($emails[0]->value)) {
                $email = $emails[0]->value;
            }
        }
        $userProfile = new User\Profile();
        $userProfile->identifier = $this->getIdentifier($data->get('user_id'));
        $userProfile->displayName = $data->get('name');
        $userProfile->email = $email;
        $userProfile->emailVerified = $email;
        $userProfile->language = $data->get('language');
        $userProfile->birthDay = $data->get('birthday');
        $userProfile->profileURL = $data->get('user_id');
        return $userProfile;
    }

    public function getIdentifier($identifier)
    {
        if ($identifier && \Validate::isUrl($identifier) && preg_match('(([^/]*)/*$)', $identifier, $result)) {
            return trim($result[1]);
        }
        return $identifier;
    }
}
