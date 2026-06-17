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
 * InvalidArgumentException
 *
 * Exception thrown if an argument is not of the expected type.
 */

if (!defined('_PS_VERSION_')) { exit; }

class InvalidArgumentException extends RuntimeException implements ExceptionInterface
{
}
