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
 * GitLab OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class GitLab extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    public $scope = 'api';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://gitlab.com/api/v3/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://gitlab.com/oauth/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://gitlab.com/oauth/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://docs.gitlab.com/ee/api/oauth2.html';

    /**
    * {@inheritdoc}
    */
      protected function initialize()
    {
        parent::initialize();
    }
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
        $userProfile->profileURL  = $data->get('web_url');
        $userProfile->email       = $data->get('email');
        $userProfile->webSiteURL  = $data->get('website_url');

        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');

        return $userProfile;
    }
}
