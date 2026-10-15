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

use ETSHybridauth\Adapter\OpenID;

/**
 * Yahoo OpenID provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class YahooOpenID extends OpenID
{
    /**
    * {@inheritdoc}
    */
    protected $openidIdentifier = 'https://open.login.yahooapis.com/openid20/www.yahoo.com/xrds';

    /**
    * {@inheritdoc}
    */
    public function authenticateFinish()
    {
        parent::authenticateFinish();

        $userProfile = $this->storage->get($this->providerId . '.user');

        $userProfile->identifier    = $userProfile->email;
        $userProfile->emailVerified = $userProfile->email;

        // re store the user profile
        $this->storage->set($this->providerId . '.user', $userProfile);
    }
}
