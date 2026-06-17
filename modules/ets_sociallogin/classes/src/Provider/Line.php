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

class Line extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'email openid profile';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.line.me/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://access.line.me/oauth2/v2.1/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.line.me/oauth2/v2.1/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developers.line.biz/en/reference/social-api-v2/';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('profile');

        $data = new Data\Collection($response);

        if (!$data->exists('userId')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('userId');
        $userProfile->displayName = $data->get('displayName');
        $userProfile->email = $data->get('email');

        return $userProfile;
    }
}
