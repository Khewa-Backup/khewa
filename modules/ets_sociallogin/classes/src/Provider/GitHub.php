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
 * Github OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class GitHub extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    public $scope = 'user:email';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://api.github.com/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://github.com/login/oauth/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://github.com/login/oauth/access_token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://developer.github.com/v3/oauth/';

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
        $userProfile->displayName = $data->get('name');
        $userProfile->description = $data->get('bio');
        $userProfile->photoURL    = $data->get('avatar_url');
        $userProfile->profileURL  = $data->get('html_url');
        $userProfile->email       = $data->get('email');
        $userProfile->webSiteURL  = $data->get('blog');
        $userProfile->region      = $data->get('location');

        $userProfile->displayName = $userProfile->displayName ?: $data->get('login');

        if (empty($userProfile->email) && strpos($this->scope, 'user:email') !== false) {
            try {
                $userProfile = $this->requestUserEmail($userProfile);
            }
            // user email is not mandatory so keep it quite
            catch (\Exception $e) {
            }
        }

        return $userProfile;
    }

    /**
    * Request connected user email
    *
    * https://developer.github.com/v3/users/emails/
    */
    protected function requestUserEmail(User\Profile $userProfile)
    {
        $response = $this->apiRequest('user/emails');

        foreach ($response as $item) {//$idx =>
            if (! empty($item->primary) && $item->primary == 1) {
                if (isset($item->email)) {
                    $userProfile->email = $item->email;
                }

                if (! empty($item->verified) && $item->verified == 1 && isset($item->email)) {
                    $userProfile->emailVerified = $item->email;
                }

                break;
            }
        }

        return $userProfile;
    }
}
