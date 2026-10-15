<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\Exception;

/**
 * RuntimeException
 *
 * Exception thrown if an error which can only be found on runtime occurs.
 */

if (!defined('_PS_VERSION_')) { exit; }

class RuntimeException extends Exception implements ExceptionInterface
{
}
