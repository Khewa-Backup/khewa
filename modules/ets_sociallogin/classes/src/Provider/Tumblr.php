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

use ETSHybridauth\Adapter\OAuth1;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * Tumblr OAuth1 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class Tumblr extends OAuth1
{
    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://api.tumblr.com/v2/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://www.tumblr.com/oauth/authorize';

    /**
    * {@inheritdoc}
    */
    protected $requestTokenUrl = 'https://www.tumblr.com/oauth/request_token';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://www.tumblr.com/oauth/access_token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://www.tumblr.com/docs/en/api/v2';

    /**
    * {@inheritdoc}
    */
    public function getUserProfile()
    {
        $response = $this->apiRequest('user/info');

        $data = new Data\Collection($response);

        if (! $data->exists('response')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->displayName = $data->filter('response')->filter('user')->get('name');

        foreach ($data->filter('response')->filter('user')->filter('blogs')->toArray() as $blog) {
            $blog = new Data\Collection($blog);

            if ($blog->get('primary') && $blog->exists('url')) {
                $userProfile->identifier  = $blog->get('url');
                $userProfile->profileURL  = $blog->get('url');
                $userProfile->webSiteURL  = $blog->get('url');
                $userProfile->description = strip_tags($blog->get('description'));

                $bloghostname = explode('://', $blog->get('url'));
                $bloghostname = \Tools::substr($bloghostname[1], 0, -1);

                // store user's primary blog which will be used as target by setUserStatus
                $this->storeData('primary_blog', $bloghostname);

                break;
            }
        }

        return $userProfile;
    }

    /**
    * {@inheritdoc}
    */
    public function setUserStatus($status)
    {
        $status = is_string($status)
                    ? array( 'type' => 'text', 'body' => $status )
                    : $status;

        $response = $this->apiRequest('blog/' . $this->getStoredData('primary_blog') . '/post', 'POST', $status);

        return $response;
    }
}
