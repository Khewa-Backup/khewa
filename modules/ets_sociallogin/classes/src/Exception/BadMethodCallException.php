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
 * BadMethodCallException
 *
 * Exception thrown if a callback refers to an undefined method or if some arguments are missing.
 */

if (!defined('_PS_VERSION_')) { exit; }

class BadMethodCallException extends RuntimeException implements ExceptionInterface
{
}
