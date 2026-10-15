<?php
/**
 * 2007-2019 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Provider;

use ETSHybridauth\Adapter\OAuth2;
use ETSHybridauth\Exception\Exception;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data\Collection;
use ETSHybridauth\User\Profile;


/**
 * Yandex provider adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback'  => ETSHybridauth\HttpClient\Util::getCurrentUrl(),
 *       'keys'      => ['id' => '', 'secret' => ''],
 *   ];
 *
 *   $adapter = new ETSHybridauth\Provider\Yandex($config);
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

class Yandex extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://login.yandex.ru/info';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://oauth.yandex.ru/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://oauth.yandex.ru/token';

    /**
     * load the user profile from the IDp api client
     *
     * @throws Exception
     */
    public function getUserProfile()
    {

        $this->scope = implode(',', array());

        $response = $this->apiRequest($this->apiBaseUrl . "?format=json");

        if (!isset($response->id)) {
            throw new UnexpectedApiResponseException("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }

        $data = new Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->firstName = $data->get('real_name');
        $userProfile->lastName = $data->get('family_name');
        $userProfile->displayName = $data->get('display_name');
        $userProfile->photoURL = 'http://upics.yandex.net/' . $userProfile->identifier . '/normal';
        $userProfile->profileURL = "";
        $userProfile->gender = $data->get('sex');
        $userProfile->email = $data->get('default_email');
        $userProfile->emailVerified = $data->get('default_email');

        if ($data->get('birthday')) {
            list($birthday_year, $birthday_month, $birthday_day) = explode('-', $data->get('birthday'));
            $userProfile->birthDay = (int)$birthday_day;
            $userProfile->birthMonth = (int)$birthday_month;
            $userProfile->birthYear = (int)$birthday_year;
        }

        return $userProfile;
    }
}
