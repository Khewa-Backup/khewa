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
 * ETSHybridauth Exceptions Interface
 */

if (!defined('_PS_VERSION_')) { exit; }

interface ExceptionInterface
{
    /*
    ExceptionInterface
    Exception                                             extends \Exception implements ExceptionInterface
    |   RuntimeException                                  extends Exception
    |   |    UnexpectedValueException                     extends RuntimeException
    |   |    |    AuthorizationDeniedException            extends UnexpectedValueException
    |   |    |    HttpClientFailureException              extends UnexpectedValueException
    |   |    |    HttpRequestFailedException              extends UnexpectedValueException
    |   |    |    InvalidAuthorizationCodeException       extends UnexpectedValueException
    |   |    |    InvalidAuthorizationStateException      extends UnexpectedValueException
    |   |    |    InvalidOauthTokenException              extends UnexpectedValueException
    |   |    |    InvalidAccessTokenException             extends UnexpectedValueException
    |   |    |    UnexpectedApiResponseException          extends UnexpectedValueException
    |   |
    |   |    BadMethodCallException                       extends RuntimeException
    |   |    |   NotImplementedException                  extends BadMethodCallException
    |   |
    |   |    InvalidArgumentException                     extends RuntimeException
    |   |    |   InvalidApplicationCredentialsException   extends InvalidArgumentException
    |   |    |   InvalidOpenidIdentifierException         extends InvalidArgumentException
*/
}
