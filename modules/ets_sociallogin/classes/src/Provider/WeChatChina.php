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
use ETSHybridauth\Provider\WeChat;
use ETSHybridauth\Exception\UnexpectedApiResponseException;
use ETSHybridauth\Data;
use ETSHybridauth\User;

/**
 * WeChat China OAuth2 provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class WeChatChina extends WeChat
{

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://api.weixin.qq.com/sns/';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * {@inheritdoc}
     */
    protected $tokenRefreshUrl = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * {@ịnheritdoc}
     */
    protected $accessTokenInfoUrl = 'https://api.weixin.qq.com/sns/auth';

}
