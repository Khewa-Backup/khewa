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

use ETSHybridauth\Adapter\OAuth1;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * Reddit OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Skyrock extends OAuth1
{

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://api.skyrock.com/v2/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://api.skyrock.com/v2/oauth/authenticate';

    /**
    * {@inheritdoc}
    */
    protected $requestTokenUrl = 'https://api.skyrock.com/v2/oauth/initiate';
    
    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://api.skyrock.com/v2/oauth/token';
    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://www.skyrock.com/developer/documentation/';

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizeUrl($parameters = array())
    {
        if ($this->config->get('authorize') === true) {
            $this->authorizeUrl = 'https://api.skyrock.com/v2/oauth/authorize';
        }

        return parent::getAuthorizeUrl($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user/get.json');

        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier    = $data->get('id');
        $userProfile->displayName   = $data->get('screen_name');
        $userProfile->description   = $data->get('description');
        $userProfile->firstName     = $data->get('name');
        $userProfile->email         = $data->get('email');
        $userProfile->emailVerified = $data->get('email');
        $userProfile->webSiteURL    = $data->get('url');
        $userProfile->region        = $data->get('location');

        $userProfile->profileURL    = $data->exists('screen_name')
                                        ? ('http://twitter.com/' . $data->get('screen_name'))
                                        : '';

        $userProfile->photoURL      = $data->exists('profile_image_url')
                                        ? str_replace('_normal', '', $data->get('profile_image_url'))
                                        : '';
        return $userProfile;
    }
}
