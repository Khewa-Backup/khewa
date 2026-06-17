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

class SteemConnect extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'login,vote';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://v2.steemconnect.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://v2.steemconnect.com/oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://v2.steemconnect.com/api/oauth2/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://steemconnect.com/';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('api/me');

        $data = new Data\Collection($response);

        if (! $data->exists('result')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $data = $data->filter('result');

        $userProfile->identifier  = $data->get('id');
        $userProfile->description = $data->get('about');
        $userProfile->photoURL    = $data->get('profile_image');
        $userProfile->webSiteURL  = $data->get('website');
        $userProfile->displayName = $data->get('name');

        return $userProfile;
    }
}
