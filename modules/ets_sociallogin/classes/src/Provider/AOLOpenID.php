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
 * AOL OpenID provider adapter.
 */

if (!defined('_PS_VERSION_')) { exit; }

class AOLOpenID extends OpenID
{
    /**
    * {@inheritdoc}
    */
    protected $openidIdentifier = 'http://openid.aol.com/';
}
