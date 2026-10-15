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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;

/**
 * ETSHybridauth Guzzle Http client
 *
 * Note: This is just a proof of concept. Feel free to improve it.
 *
 * Example:
 *
 * <code>
 *  $guzzle = new ETSHybridauth\HttpClient\Guzzle( new GuzzleHttp\Client(), [
 *      'verify'  => '/path/to/your/certificate.crt',
 *      'headers' => [ 'User-Agent' => '..' ]
 *      // 'proxy' => ...
 *  ]);
 *
 *  $adapter = new ETSHybridauth\Provider\Github( $config, $guzzle );
 *
 *  $adapter->authenticate();
 * </code>
 */

if (!defined('_PS_VERSION_')) { exit; }

class Guzzle implements HttpClientInterface
{
    /**
    * Method request() arguments
    *
    * This is used for debugging.
    *
    * @var array
    */
    protected $requestArguments = array();

    /**
    * Default request headers
    *
    * @var array
    */
    protected $requestHeader = array();

    /**
    * Raw response returned by server
    *
    * @var string
    */
    protected $responseBody = '';

    /**
    * Headers returned in the response
    *
    * @var array
    */
    protected $responseHeader = array();

    /**
    * Response HTTP status code
    *
    * @var integer
    */
    protected $responseHttpCode = 0;

    /**
    * Last curl error number
    *
    * @var mixed
    */
    protected $responseClientError = null;

    /**
    * Information about the last transfer
    *
    * @var mixed
    */
    protected $responseClientInfo = array();

    /**
    * ETSHybridauth logger instance
    *
    * @var object
    */
    protected $logger = null;

    /**
    * GuzzleHttp client
    *
    * @var object
    */
    protected $client = null;

    /**
    * ..
    */
    public function __construct($client = null, $config = array())
    {
        $this->client = $client ? $client : new Client($config);
    }

    /**
    * {@inheritdoc}
    */
    public function request($uri, $method = 'GET', $parameters = array(), $headers = array())
    {
        $this->requestHeader = array_replace($this->requestHeader, (array) $headers);

        $this->requestArguments = array(
            'uri' => $uri,
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $this->requestHeader,
        );

        $response = null;

        try {
            if ('GET' == $method) {
                $response = $this->client->get($uri, array('query' => $parameters, 'headers' => $this->requestHeader));
            }

            if ('POST' == $method) {
                $body_content = 'form_params';

                if (isset($this->requestHeader['Content-Type']) && $this->requestHeader['Content-Type'] == 'application/json') {
                    $body_content = 'json';
                }

                $response = $this->client->post($uri, array($body_content => $parameters, 'headers' => $this->requestHeader));
            }

            if ('PUT' == $method) {
                $body_content = 'form_params';

                if (isset($this->requestHeader['Content-Type']) && $this->requestHeader['Content-Type'] == 'application/json') {
                    $body_content = 'json';
                }

                $response = $this->client->put($uri, array($body_content => $parameters, 'headers' => $this->requestHeader));
            }
        }

        // guess this will do it for now
        catch (RequestException $e) {
            $response = $e->getResponse();

            $this->responseClientError = $e->getMessage();
        }
        catch (\Exception $e) {
            $response = null;

            $this->responseClientError = $e->getMessage();
        }

        if (! $this->responseClientError && $response !== null) {
            $this->responseBody     = $response->getBody();
            $this->responseHttpCode = $response->getStatusCode();
            $this->responseHeader   = $response->getHeaders();
        }

        if ($this->logger !== null) {
            $this->logger->debug(sprintf('%s::request( %s, %s ), response:', get_class($this), $uri, $method), $this->getResponse());

            if ($this->responseClientError) {
                $this->logger->error(sprintf('%s::request( %s, %s ), error:', get_class($this), $uri, $method), array($this->responseClientError));
            }
        }

        return $this->responseBody;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponse()
    {
        return array(
            'request' => $this->getRequestArguments(),
            'response' => array(
                'code'    => $this->getResponseHttpCode(),
                'headers' => $this->getResponseHeader(),
                'body'    => $this->getResponseBody(),
            ),
            'client' => array(
                'error' => $this->getResponseClientError(),
                'info'  => $this->getResponseClientInfo(),
                'opts'  => null,
            ),
        );
    }

    /**
    * Set logger instance
    *
    * @param object $logger
    */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponseHttpCode()
    {
        return $this->responseHttpCode;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponseClientError()
    {
        return $this->responseClientError;
    }

    /**
    * @return array
    */
    protected function getResponseClientInfo()
    {
        return $this->responseClientInfo;
    }

    /**
    * Returns method request() arguments
    *
    * This is used for debugging.
    *
    * @return array
    */
    protected function getRequestArguments()
    {
        return $this->requestArguments;
    }
}
