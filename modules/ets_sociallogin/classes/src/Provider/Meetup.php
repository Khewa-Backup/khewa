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

class Meetup extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'basic';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.meetup.com/2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://secure.meetup.com/oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://secure.meetup.com/oauth2/access';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://www.meetup.com/meetup_api/auth/#oauth2';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('member/self');

        $data = new Data\Collection($response);
        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->displayName = $data->get('name');
        $userProfile->language = $data->get('lang');
        $userProfile->city = $data->get('city');
        $userProfile->country = $data->get('country');
        $userProfile->profileURL = $data->get('link');

        if (($photo = $data->get('photo')) && isset($photo->thumb_link) && $photo->thumb_link)
        {
            $userProfile->photoURL = $photo->thumb_link;
        }

        return $userProfile;
    }
}
