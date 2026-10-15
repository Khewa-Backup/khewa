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
 * WeChat International OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Weibo extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'all';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.weibo.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://api.weibo.com/oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.weibo.com/oauth2/access_token';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $params = array(
            'access_token' => $this->getStoredData('access_token')
        );
        $token_info = $this->apiRequest('oauth2/get_token_info', 'POST', $params);
        if ($token_info && isset($token_info->uid) && $token_info->uid)
        {
            $params['uid'] = $token_info->uid;
        }
        $response = $this->apiRequest('2/users/show.json', 'GET', $params);
        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('id');
        $userProfile->displayName = $data->get('name');
        $userProfile->photoURL    = $data->get('profile_image_url');
        $userProfile->profileURL  = 'https://www.weibo.com'.$data->get('profile_url');
        $userProfile->language    = $data->get('lang');
        $userProfile->gender      = $data->get('gender');

        return $userProfile;
    }

}
