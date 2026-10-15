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
 * Dribbble OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Dribbble extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://api.dribbble.com/v2/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://dribbble.com/oauth/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://dribbble.com/oauth/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'http://developer.dribbble.com/v2/oauth/';

    /**
    * {@inheritdoc}
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user');

        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('id');
        $userProfile->profileURL  = $data->get('html_url');
        $userProfile->photoURL    = $data->get('avatar_url');
        $userProfile->description = $data->get('bio');
        $userProfile->region      = $data->get('location');
        $userProfile->displayName = $data->get('name');

        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');

        $userProfile->webSiteURL  = $data->filter('links')->get('web');

        return $userProfile;
    }
}
