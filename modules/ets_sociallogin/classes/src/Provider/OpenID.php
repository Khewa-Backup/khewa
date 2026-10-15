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

use ETSHybridauth\Adapter;

/**
 * Generic OpenID providers adapter.
 *
 * Example:
 *
 *   $config = [
 *       'callback' => ETSHybridauth\HttpClient\Util::getCurrentUrl(),
 *
 *       //  authenticate with Yahoo openid
 *       'openid_identifier' => 'https://open.login.yahooapis.com/openid20/www.yahoo.com/xrds' 
 *
 *       //  authenticate with stackexchange network openid
 *       // 'openid_identifier' => 'https://openid.stackexchange.com/',
 *
 *       //  authenticate with Steam openid 
 *       // 'openid_identifier' => 'http://steamcommunity.com/openid',
 *
 *       // etc.
 *   ];
 *
 *   $adapter = new ETSHybridauth\Provider\OpenID( $config );
 *
 *   try {
 *       $adapter->authenticate();
 *
 *       $userProfile = $adapter->getUserProfile();
 *   }
 *   catch( \Exception $e ){
 *       echo $e->getMessage() ;
 *   }
 */

if (!defined('_PS_VERSION_')) { exit; }

class OpenID extends Adapter\OpenID
{
}
