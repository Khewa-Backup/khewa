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

use CanadaPostWs\Type\Messages\MessagesType;
use CanadaPostWs\Type\Messages\MessageType;
use CanadaPost\Symfony\Component\OptionsResolver\OptionsResolver;

abstract class WebService
{
    const ENV_DEV = 'env.dev';
    const ENV_PROD = 'env.prod';

    const SHIPPING_CODE_DOMESTIC_REGULAR = 1010;
    const SHIPPING_CODE_DOMESTIC_EXPEDITED = 1020;
    const SHIPPING_CODE_DOMESTIC_XPRESSPOST = 1030;
    const SHIPPING_CODE_DOMESTIC_PRIORITY = 1040;

    const SHIPPING_CODE_USA_TRACKED_PACKET = 2000;
    const SHIPPING_CODE_USA_SMALL_PACKETS_AIR = 2015;
    const SHIPPING_CODE_USA_EXPEDITED_BUSINESS_CONTRACT = 2020;
    const SHIPPING_CODE_USA_XPRESSPOST = 2030;
    const SHIPPING_CODE_USA_PRIORITY_WORLDWIDE = 2040;
    const SHIPPING_CODE_USA_PRIORITY_WORLDWIDE_PAK = 2050;

    const SHIPPING_CODE_INTERNATIONAL_TRACKED_PACKET = 3000;
    const SHIPPING_CODE_INTERNATIONAL_SMALL_PACKETS_SURFACE = 3005;
    const SHIPPING_CODE_INTERNATIONAL_SURFACE = 3010;
    const SHIPPING_CODE_INTERNATIONAL_SMALL_PACKETS_AIR = 3015;
    const SHIPPING_CODE_INTERNATIONAL_AIR = 3020;
    const SHIPPING_CODE_INTERNATIONAL_XPRESSPOST = 3025;
    const SHIPPING_CODE_INTERNATIONAL_PRIORITY_WORLDWIDE = 3040;
    const SHIPPING_CODE_INTERNATIONAL_PRIORITY_WORLDWIDE_PAK = 3050;

    protected $contractId;
    protected $options;

    protected $requestUrl;

    public static $serviceCodes = array(
        self::SHIPPING_CODE_DOMESTIC_REGULAR => 'DOM.RP',
        self::SHIPPING_CODE_DOMESTIC_EXPEDITED => 'DOM.EP',
        self::SHIPPING_CODE_DOMESTIC_XPRESSPOST => 'DOM.XP',
        self::SHIPPING_CODE_DOMESTIC_PRIORITY => 'DOM.PC',
        self::SHIPPING_CODE_USA_TRACKED_PACKET => 'USA.TP',
        self::SHIPPING_CODE_USA_SMALL_PACKETS_AIR => 'USA.SP.AIR',
        self::SHIPPING_CODE_USA_EXPEDITED_BUSINESS_CONTRACT => 'USA.EP',
        self::SHIPPING_CODE_USA_XPRESSPOST => 'USA.XP',
        self::SHIPPING_CODE_USA_PRIORITY_WORLDWIDE => 'USA.PW.ENV',
        self::SHIPPING_CODE_USA_PRIORITY_WORLDWIDE_PAK => 'USA.PW.PAK',
        self::SHIPPING_CODE_INTERNATIONAL_TRACKED_PACKET => 'INT.TP',
        self::SHIPPING_CODE_INTERNATIONAL_SMALL_PACKETS_SURFACE => 'INT.SP.SURF',
        self::SHIPPING_CODE_INTERNATIONAL_SURFACE => 'INT.IP.SURF',
        self::SHIPPING_CODE_INTERNATIONAL_SMALL_PACKETS_AIR => 'INT.SP.AIR',
        self::SHIPPING_CODE_INTERNATIONAL_AIR => 'INT.IP.AIR',
        self::SHIPPING_CODE_INTERNATIONAL_XPRESSPOST => 'INT.XP',
        self::SHIPPING_CODE_INTERNATIONAL_PRIORITY_WORLDWIDE => 'INT.PW.PARCEL',
        self::SHIPPING_CODE_INTERNATIONAL_PRIORITY_WORLDWIDE_PAK => 'INT.PW.PAK',
    );

    /**
     *  the error code if one exists.
     *
     * @var int
     */
    protected $errorCode = 0;
    /**
     * the error message if one exists.
     *
     * @var string
     */
    protected $errorMessage = '';
    /**
     *  the response message.
     *
     * @var string
     */
    protected $response = '';
    /**
     *  the headers returned from the call made.
     *
     * @var array
     */
    protected $headers = '';
    /**
     * The response represented as an array.
     *
     * @var array
     */
    protected $arrayResponse = array();

    /**
     * WebService constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);

        switch ($this->options['env']) {
            case WebService::ENV_DEV:
                $this->requestUrl = 'https://ct.soa-gw.canadapost.ca';
                break;
            case WebService::ENV_PROD:
                $this->requestUrl = 'https://soa-gw.canadapost.ca';
                break;
        }
    }

    /**
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    protected function processRequest(array $options = array())
    {
        if ($options['request_url']) {
            $options['request_url'] = $this->requestUrl.$options['request_url'];
        }

        $options = array_merge(
            $options,
            array(
                'api_key' => $this->options['api_key'],
                'ssl' => $this->options['ssl'],
            )
        );
        if (array_key_exists('platform_id', $this->options)) {
            $options['headers'][] = 'Platform-id: ' . $this->options['platform_id'];
        }
        
        try {
            $RequestProcessor = new RequestProcessor($options);
            $this->setResponse($RequestProcessor->process());

            if (strpos($this->response, '<?xml') === 0) {
                $responseXML = new \SimpleXMLElement($this->response);

                // If API error, get the first error message
                if ($responseXML->getName() == 'messages') {
                    if (!empty($responseXML)) {
                        $MessagesType = WebService::getMessagesType($responseXML);
                        $messages     = $MessagesType->getMessages();
                        $this->setErrorCode($messages[0]->getCode());
                        $this->setErrorMessage($messages[0]->getDescription());
//                    $this->setResponse($messages);
                    } else {
                        $this->setErrorCode(1);
                        $this->setErrorMessage('An Error Occurred.');
                    }
                }
            }
        } catch (\Exception $e) {
            $this->setErrorCode($e->getCode());
            $this->setErrorMessage($e->getMessage());
            return false;
        }
        return $this->getResponse();
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'env' => WebService::ENV_DEV,
            'ssl' => true,
            'platform_id',
        ));

        $resolver->setRequired(array(
            'api_customer_number',
            'api_key',
            'env',
        ));
        $resolver->setDefined(array(
            'platform_id',
            'platform_url',
        ));

        $resolver->setAllowedTypes('api_customer_number', 'string');
        $resolver->setAllowedTypes('api_key', 'string');
        $resolver->setAllowedTypes('platform_id', 'string');
        $resolver->setAllowedTypes('ssl', 'bool');

        $resolver->setAllowedValues('env', array(WebService::ENV_DEV, WebService::ENV_PROD));
    }

    /**
     * @param \SimpleXMLElement $responseXML
     * @return MessagesType
     */
    public static function getMessagesType(\SimpleXMLElement $responseXML)
    {
        $MessagesType = new MessagesType();

        if ($responseXML->message) {
            foreach ($responseXML->message as $message) {
                $MessageType = new MessageType();

                $MessageType->setCode((string)$message->code);
                $MessageType->setDescription((string)$message->description);

                $MessagesType->addMessage($MessageType);
            }
        }
        return $MessagesType;
    }

    /**
     * Set the response.
     *
     * @param mixed $response The response returned from the call
     *
     * @return self
     */
    public function setResponse($response = '')
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response data.
     *
     * @return mixed the response data
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the error code number.
     *
     * @param int $code the error code number
     *
     * @return self
     */
    public function setErrorCode($code = 0)
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Get the error code number.
     *
     * @return int error code number
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set the error message.
     *
     * @param string $message the error message
     *
     * @return self
     */
    public function setErrorMessage($message = '')
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Get the error code message.
     *
     * @return string error code message
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Did we encounter an error?
     *
     * @return bool
     * */
    public function isError()
    {
        return !empty($this->errorMessage) || !empty($this->errorCode) ? true : false;
    }

    /**
     * Was the last call successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return !$this->isError() ? true : false;
    }
}
