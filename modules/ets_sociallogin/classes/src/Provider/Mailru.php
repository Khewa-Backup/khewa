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
use ETSHybridauth\Data\Collection;
use ETSHybridauth\User\Profile;

/**
 * Mailru provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback'  => ETSHybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys'      => ['id' => '', 'secret' => ''],
 *   ];
 *
 *   $adapter = new ETSHybridauth\Provider\Mailru($config);
 *
 *   try {
 *       if (!$adapter->isConnected()) {
 *           $adapter->authenticate();
 *       }
 *
 *       $userProfile = $adapter->getUserProfile();
 *   }
 *   catch(\Exception $e) {
 *       print $e->getMessage() ;
 *   }
 */

if (!defined('_PS_VERSION_')) { exit; }

class Mailru extends OAuth2
{
    protected $scope = 'userinfo';
    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://oauth.mail.ru';//http://www.appsmail.ru/platform/api

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://oauth.mail.ru/login';//https://connect.mail.ru/oauth/authorize

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://oauth.mail.ru/token';//https://connect.mail.ru/oauth/token


    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $param = array(
            'access_token' => $this->getStoredData('access_token'),
        );
        $response = $this->apiRequest('userinfo', 'GET', $param);
        $data = new Collection($response);
        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->email = $data->get('email');
        $userProfile->firstName = $data->get('first_name');
        $userProfile->lastName = $data->get('last_name');
        $userProfile->displayName = $data->get('name');
        $userProfile->photoURL = $data->get('image');
        $userProfile->profileURL = $data->get('link');
        $userProfile->gender = $data->get('gender');

        return $userProfile;
    }
}
