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

use Tools;
/**
 * ETSHybridauth default Http client
 */

if (!defined('_PS_VERSION_')) { exit; }

class Curl implements HttpClientInterface
{
    /**
    * Default curl options
    *
    * These defaults options can be overwritten when sending requests.
    *
    * See setCurlOptions()
    *
    * @var array
    */
    protected $curlOptions = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_ENCODING       => 'identity',
        CURLOPT_USERAGENT      => 'ETSHybridauth, PHP Social Authentication Library (https://github.com/ETSHybridauth/ETSHybridauth)',
    );

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
    protected $requestHeader = array(
        'Accept'          => '*/*',
        'Cache-Control'   => 'max-age=0',
        'Connection'      => 'keep-alive',
        'Expect'          => '',
        'Pragma'          => '',
    );

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
    * {@inheritdoc}
    */
    public $decode_json           = true;
	public $curl_time_out         = 30;
	public $curl_connect_time_out = 30;
	public $curl_ssl_verifypeer   = false;
	public $curl_auth_header      = true;
	public $curl_useragent        = "OAuth/1 Simple PHP Client v0.1; ETSHybridauth http://ETSHybridauth.sourceforge.net/";
	public $curl_proxy            = null;
    public function request($uri, $method = 'GET', $parameters = array(), $headers = array())
    {
        $this->requestHeader = array_replace($this->requestHeader, (array) $headers);

        $this->requestArguments = array(
            'uri' => $uri,
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $this->requestHeader,
        );

        $curl = curl_init();
        if ('GET' == $method) {
            unset($this->curlOptions[CURLOPT_POST]);
            unset($this->curlOptions[CURLOPT_POSTFIELDS]);

            $uri = $uri . (strpos($uri, '?') ? '&' : '?') . http_build_query($parameters);
        }
        if ('POST' == $method) {
            $body_content = http_build_query($parameters);
            if (isset($this->requestHeader['Content-Type']) && $this->requestHeader['Content-Type'] == 'application/json') {
                $body_content = json_encode($parameters);
            }

            $this->curlOptions[CURLOPT_POST] = true;
            $this->curlOptions[CURLOPT_POSTFIELDS] = $body_content;
        }

        if ('PUT' == $method) {
            $body_content = http_build_query($parameters);
            if (isset($this->requestHeader['Content-Type']) && $this->requestHeader['Content-Type'] == 'application/json') {
                $body_content = json_encode($parameters);
            }

            $this->curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $this->curlOptions[CURLOPT_POSTFIELDS] = $body_content;
        }

        $this->curlOptions[CURLOPT_URL]            = $uri;
        $this->curlOptions[CURLOPT_HTTPHEADER]     = $this->prepareRequestHeaders();
        $this->curlOptions[CURLOPT_HEADERFUNCTION] = array( $this, 'fetchResponseHeader' );

        foreach ($this->curlOptions as $opt => $value) {
            curl_setopt($curl, $opt, $value);
        }
        
        /* Curl settings */

        $response = curl_exec($curl);
        $this->responseBody        = $response;
        $this->responseHttpCode    = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->responseClientError = curl_error($curl);
        $this->responseClientInfo  = curl_getinfo($curl);

        if ($this->logger !== null) {
            $this->logger->debug(sprintf('%s::request( %s, %s ), response:', get_class($this), $uri, $method), $this->getResponse());

            if (false === $response) {
                $this->logger->error(sprintf('%s::request( %s, %s ), error:', get_class($this), $uri, $method), array($this->responseClientError));
            }
        }

        curl_close($curl);
        return $this->responseBody;
    }

    /**
    * {@inheritdoc}
    */
    public function getResponse()
    {
        $curlOptions = $this->curlOptions;

        $curlOptions[CURLOPT_HEADERFUNCTION] = '*omitted';

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
                'opts'  => $curlOptions,
            ),
        );
    }

    /**
    * Reset curl options
    *
    * @param array $curlOptions
    */
    public function setCurlOptions($curlOptions)
    {
        foreach ($curlOptions as $opt => $value) {
            $this->curlOptions[ $opt ] = $value;
        }
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

    /**
    * Fetch server response headers
    *
    * @param mixed  $curl
    * @param string $header
    *
    * @return integer
    */
    protected function fetchResponseHeader($curl, $header)
    {
        $pos = strpos($header, ':');

        if (! empty($pos)) {
            $key   = str_replace('-', '_', Tools::strtolower(Tools::substr($header, 0, $pos)));

            $value = trim(Tools::substr($header, $pos + 2));

            $this->responseHeader[ $key ] = $value;
        }
		unset($curl);
        return Tools::strlen($header);
    }

    /**
    * Convert request headers to the expect curl format
    *
    * @return array
    */
    protected function prepareRequestHeaders()
    {
        $headers = array();

        foreach ($this->requestHeader as $header => $value) {
            $headers[] = trim($header) .': '. trim($value);
        }
        return $headers;
    }
}
