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
 * Instagram OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Instagram extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'basic';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.instagram.com/v1/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://api.instagram.com/oauth/authorize/';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.instagram.com/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://www.instagram.com/developer/authentication/';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();

        // The Instagram API requires an access_token from authenticated users
        // for each endpoint, see https://www.instagram.com/developer/endpoints.
        $accessToken = $this->getStoredData($this->accessTokenName);
        $this->apiRequestParameters[$this->accessTokenName] = $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('users/self/');

        $data = new Data\Collection($response);

        if (! $data->exists('data')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $data = $data->filter('data');

        $userProfile->identifier  = $data->get('id');
        $userProfile->description = $data->get('bio');
        $userProfile->photoURL    = $data->get('profile_picture');
        $userProfile->webSiteURL  = $data->get('website');
        $userProfile->displayName = $data->get('full_name');
        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');
	    $userProfile->profileURL = "https://instagram.com/{$data->get('username')}";

        $userProfile->data = (array) $data->get('counts');

        return $userProfile;
    }
}
