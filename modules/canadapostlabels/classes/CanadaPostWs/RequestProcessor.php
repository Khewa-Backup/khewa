<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostWs;

use CanadaPost\Symfony\Component\OptionsResolver\OptionsResolver;

class RequestProcessor
{
    private static $retriableErrorCodes = array(
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    );
    protected $options;

    /**
     * RequestProcessor constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'request' => null,
            'ssl' => true,
        ));

        $resolver->setRequired(array(
            'api_key',
            'request_url',
            'headers',
        ));

        $resolver->setAllowedTypes('request', array('string', 'null'));
        $resolver->setAllowedTypes('request_url', 'string');
        $resolver->setAllowedTypes('headers', 'array');
        $resolver->setAllowedTypes('api_key', 'string');
        $resolver->setAllowedTypes('ssl', 'bool');
    }

    /**
     * @return mixed
     */
    public function process()
    {
        // Connection
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->options['request_url']);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        // SSL
        if ($this->options['ssl']) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, realpath(dirname(__FILE__)) . '/../../cert/cacert.pem'); // Mozilla cacerts
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        // Headers
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['PHP_SELF'].' VERSION:'.PHP_VERSION.' (PHP-'.phpversion().')');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);

        // Auth
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->options['api_key']);

        // Request
        if ($this->options['request']) {
            switch ($this->options['request']) {
                case 'delete':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                default:
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->options['request']);
                    break;
            }
        }

        // Response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = '';

        $delaySeconds = 0;
        $retries = 5;
        while ($retries--) {
            $response = curl_exec($ch);

            if ($this->options['request'] == 'delete' && curl_getinfo($ch, CURLINFO_HTTP_CODE) == '204') {
                return true;
            }

            if ($response === false) {
                if (false === in_array(curl_errno($ch), self::$retriableErrorCodes, true) || !$retries) {
                    curl_close($ch);

                    throw new \RuntimeException(sprintf('Curl error (code %s): %s', curl_errno($ch), curl_error($ch)));
                }

                continue;
            }

            // If nothing was received and status wasn't 200 or 204 and retries is not 0, try again, but with a delay
            if (!$response && !in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), array('200', '204')) && $retries) {
                $delaySeconds += 2;
                sleep($delaySeconds);

                continue;
            }

            break;
        }

        return $response;
    }
}
