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
 * BitBucket OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class BitBucket extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'read:me read:account';//email

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.atlassian.com';//https://api.bitbucket.org/2.0/

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://auth.atlassian.com/authorize';//https://bitbucket.org/site/oauth2/authorize

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://auth.atlassian.com/oauth/token';//https://bitbucket.org/site/oauth2/access_token

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.atlassian.com/bitbucket/concepts/oauth2.html';

    public function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters['audience'] = 'api.atlassian.com';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me');//user

        $data = new Data\Collection($response);

        if (!$data->exists('account_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('account_id');
        $userProfile->displayName = $data->get('name');
        $userProfile->email = $data->get('email');
        $userProfile->webSiteURL = $data->get('website');
        $userProfile->region = $data->get('locale');
        $userProfile->photoURL = $data->get('picture');

        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');

        return $userProfile;
    }

}
