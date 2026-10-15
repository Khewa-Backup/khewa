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
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * Twitter OAuth2 provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback'  => ETSHybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys'      => [ 'key' => '', 'secret' => '' ], // OAuth2 uses 'key' not 'id'
 *       'authorize' => true
 *   ];
 *
 *   $adapter = new ETSHybridauth\Provider\Twitter( $config );
 *
 *   try {
 *       $adapter->authenticate();
 *
 *       $userProfile = $adapter->getUserProfile();
 *       $tokens = $adapter->getAccessToken();
 *       $contacts = $adapter->getUserContacts(['screen_name' =>'andypiper']); // get those of @andypiper
 *       $activity = $adapter->getUserActivity('me');
 *   }
 *   catch( Exception $e ){
 *       echo $e->getMessage() ;
 *   }
 */

if (!defined('_PS_VERSION_')) { exit; }

class Twitter extends OAuth2
{
    public $scope = 'tweet.read users.read offline.access';
    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.twitter.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://twitter.com/i/oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.twitter.com/2/oauth2/token';//&code_challenge=challenge&code_challenge_method=plain

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.twitter.com/en/docs/twitter-api';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += array(
            'code_challenge' => 'challenge',
            'code_challenge_method' => 'plain',
        );

        $this->tokenExchangeHeaders = [
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret)
        ];

        $this->tokenRefreshHeaders = [
            'Authorization' => 'Basic ' . call_user_func('base64_encode', $this->clientId . ':' . $this->clientSecret)
        ];

        $this->tokenExchangeParameters['code_verifier'] = 'challenge';
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('2/users/me?user.fields=created_at,description,id,location,name,profile_image_url,url,username');
        $data = new Data\Collection($response->data);
        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->displayName = $data->get('name');
        $userProfile->description = $data->get('description');
        $userProfile->firstName = $data->get('name');
        $userProfile->email = $data->get('email');
        $userProfile->emailVerified = $data->get('email');
        $userProfile->webSiteURL = $data->get('url');
        $userProfile->region = $data->get('location');

        $userProfile->profileURL = $data->exists('name')
            ? ('http://twitter.com/' . $data->get('name'))
            : '';

        $userProfile->photoURL = $data->exists('profile_image_url')
            ? str_replace('_normal', '', $data->get('profile_image_url'))
            : '';

        return $userProfile;
    }
}
