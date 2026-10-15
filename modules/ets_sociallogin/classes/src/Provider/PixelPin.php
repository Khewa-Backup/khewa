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

class PixelPin extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'openid email profile address';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://login.pixelpin.io/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://login.pixelpin.io/connect/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://login.pixelpin.io/connect/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.pixelpin.io';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('connect/userinfo');
        $data = new Data\Collection($response);

        if (!$data->count()) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('sub');
        $userProfile->firstName = $data->get('given_name');
        $userProfile->lastName = $data->get('family_name');
        $userProfile->displayName = $data->get('nickname');

        return $userProfile;
    }
}
