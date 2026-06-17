<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

namespace ETSHybridauth\HttpClient;

/**
 * ETSHybridauth Http clients interface
 */

if (!defined('_PS_VERSION_')) { exit; }

interface HttpClientInterface
{
    /**
    * Send request to the remote server
    *
    * Returns the result (Raw response from the server) on success, FALSE on failure
    *
    * @param string $uri
    * @param string $method
    * @param array  $parameters
    * @param array  $headers
    *
    * @return mixed
    */
    public function request($uri, $method = 'GET', $parameters = array(), $headers = array());

    /**
    * Returns raw response from the server on success, FALSE on failure
    *
    * @return mixed
    */
    public function getResponseBody();

    /**
    * Retriever the headers returned in the response
    *
    * @return array
    */
    public function getResponseHeader();

    /**
    * Returns latest request HTTP status code
    *
    * @return integer
    */
    public function getResponseHttpCode();

    /**
    * Returns latest error encountered by the client
    * This can be either a code or error message
    *
    * @return mixed
    */
    public function getResponseClientError();
}
