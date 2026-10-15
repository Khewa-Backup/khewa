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

class Amazon extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'profile';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.amazon.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.amazon.com/ap/oa';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.amazon.com/auth/o2/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://docs.gitlab.com/ee/api/oauth2.html';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user/profile');

        $data = new Data\Collection($response);

        if (!$data->exists('user_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('user_id');
        $userProfile->displayName = $data->get('name');
        $userProfile->email = $data->get('email');

        // Extract firstName and lastName if available, otherwise split displayName
        $firstName = $data->get('first_name');
        $lastName = $data->get('last_name');
        if (!$firstName || !$lastName) {
            $nameParts = preg_split('/\s+/', trim($userProfile->displayName));
            $firstName = $firstName ?: (isset($nameParts[0]) ? $nameParts[0] : '');
            $lastName = $lastName ?: (count($nameParts) > 1 ? array_pop($nameParts) : '');
        }
        $userProfile->firstName = $firstName;
        $userProfile->lastName = $lastName;

        return $userProfile;
    }
}
