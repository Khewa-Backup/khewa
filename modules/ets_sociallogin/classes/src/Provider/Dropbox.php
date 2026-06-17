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

class Dropbox extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = '';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.dropboxapi.com/2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://www.dropbox.com/oauth2/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.dropboxapi.com/oauth2/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://www.dropbox.com/developers/documentation/http/documentation';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('users/get_current_account','POST', array(), array('Content-Type' => ''));
        $data = new Data\Collection($response);

        if (!$data->exists('account_id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('account_id');
        $userProfile->email = $data->get('email');
        $userProfile->language = $data->get('locale');
        if (($name = $data->get('name'))) {
            $userProfile->displayName = $name->display_name;
        }

        return $userProfile;
    }
}
