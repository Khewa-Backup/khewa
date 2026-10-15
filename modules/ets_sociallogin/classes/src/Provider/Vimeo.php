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

class Vimeo extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $scope = 'public,private';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.vimeo.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://api.vimeo.com/oauth/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.vimeo.com/oauth/access_token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://developer.vimeo.com/api/guides/start';

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('me');

        $data = new Data\Collection($response);
        if (!$data->exists('uri')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
        $userProfile = new User\Profile();

        $userProfile->identifier  = $this->getIdentifier($data->get('link'));
        $userProfile->displayName = $data->get('name');
        $userProfile->profileURL  = $data->get('link');
        if (($pictures = $data->get('pictures')) && !empty($pictures->sizes)) {
            foreach ($pictures->sizes as $size) {
                if (isset($size->link) && $size->link)
                {
                    $userProfile->photoURL = $size->link;
                    break;
                }
            }
        }
        return $userProfile;
    }

    public function getIdentifier($identifier)
    {
        if ($identifier && \Validate::isUrl($identifier) && preg_match('(([^/]*)/*$)', $identifier, $result))
        {
            return trim($result[1]);
        }
        return $identifier;
    }
}
