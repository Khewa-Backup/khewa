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
namespace CanadaPostPs;

use CanadaPostWs;
use DateTime;
use DoctrineExtensions\Query\Mysql\Date;
use CanadaPostPs\FPDI;
use \Cart;
use \Configuration;
use \AdminController;
use \Context;
use \Country;
use \HelperForm;
use \HelperList;
use \Language;
use \State;
use \Validate;
use SimpleXMLElement;

class API extends \CanadaPostLabels
{
    const SYNC_SHIPMENT = 1;
    const SYNC_MANIFEST = 2;

    /*
     * Get array containing API credentials and environment
     * @return array
     * */
    public function getApiParams()
    {
        return array(
            'api_customer_number' => self::getConfig('CUSTOMER_NUMBER'),
            'api_key'             =>
                self::getConfig('PROD_API_USER') . ':' . self::getConfig('PROD_API_PASS'),
            'env'                 => (
                self::getConfig('MODE') == 1
                ? CanadaPostWs\WebService::ENV_PROD
                : CanadaPostWs\WebService::ENV_DEV
            ),
            'ssl'                 => true,
            'platform_url'        => 'https://zhmedia.ca/canadapost/platform',
            'platform_id'        => self::getConfig('PLATFORM_ID'),
        );
    }

    /*
     * Retrieve Canada Post token from Platform (ZH Media)
     *
     * @return bool|CanadaPostWs\Type\Platform\TokenType|CanadaPostWs\Type\Messages\MessagesType
     * @throws PrestaShopException
     * */
    public function getToken()
    {
        $request = array(
            self::getConfig('TOKEN_REQUEST') => "1",
            "email" => self::getConfig('VE'),
            "serial" => self::getConfig('VS'),
            "mode" => "1",
        );

        try {
            $RequestProcessor = new CanadaPostWs\RequestProcessor(array(
                'request_url' => 'https://zhmedia.ca/canadapost/platform',
                'headers'     => array(),
                'request'     => http_build_query($request),
                'api_key'     => 'false',
                'ssl'         => true,
            ));
            $response         = $RequestProcessor->process();

            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'token':
                    $TokenType = new CanadaPostWs\Type\Platform\TokenType();
                    $TokenType->setTokenId((string)$responseXML->{'token-id'});
                    $TokenType->setPlatformId((string)$responseXML->{'platform-id'});

                    return $TokenType;
                case 'messages':
                    return CanadaPostWs\WebService::getMessagesType($responseXML);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            throw new \PrestaShopException(sprintf($this->l(Tools::$error_messages['TOKEN_ERROR']), $e->getMessage()));
        }
    }

    /*
     * Retrieve Canada Post customer information from Platform Provider (zhmedia.ca)
     *
     * @return bool|CanadaPostWs\Type\Platform\MerchantInfo|CanadaPostWs\Type\Messages\MessagesType
     * @throws PrestaShopException
     * */
    public function getMerchantInformation($tokenId)
    {
        $request = array(
            "customer_request" => "1",
            "token_id" => $tokenId,
            "mode" => "1",
        );

        try {
            $RequestProcessor = new CanadaPostWs\RequestProcessor(array(
                'request_url' => 'https://zhmedia.ca/canadapost/platform',
                'headers'     => array(),
                'request'     => http_build_query($request),
                'api_key'     => 'false',
                'ssl'         => true,
            ));
            $response         = $RequestProcessor->process();

            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'merchant-info':
                    $MerchantInfo = new CanadaPostWs\Type\Platform\MerchantInfoType();
                    $MerchantInfo->setCustomerNumber((string)$responseXML->{'customer-number'});
                    if (null !== $responseXML->{'contract-number'}) {
                        $MerchantInfo->setContractNumber((string)$responseXML->{'contract-number'});
                    }
                    $MerchantInfo->setMerchantUsername((string)$responseXML->{'merchant-username'});
                    $MerchantInfo->setMerchantPassword((string)$responseXML->{'merchant-password'});
                    $MerchantInfo->setHasDefaultCreditCard((string)$responseXML->{'has-default-credit-card'});
                    $MerchantInfo->setHasDefaultSupplierAccount((string)$responseXML->{'has-default-supplier-account'});

                    return $MerchantInfo;
                case 'messages':
                    return CanadaPostWs\WebService::getMessagesType($responseXML);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            throw new \PrestaShopException(sprintf($this->l(Tools::$error_messages['MERCHANT_INFO_ERROR']), $e->getMessage()));
        }
    }

    /**
     * @param Cart $Cart
     * @param array $serviceCodes
     * @param object $senderAddress CanadaPostPs\Address
     * @param object $destinationAddress \\Address
     * @param object $box \BoxPacker\PackedBox
     * @param float $weight
     * @param array $optionsArr
     *
     * @return bool|CanadaPostWs\Rating
     */
    public function getRates($serviceCodes, $senderAddress, $destinationAddress, $Box, $weight, $optionsArr = array())
    {
        $parcel = new CanadaPostWs\Type\Rating\RatingType();

        $DeliverySpec = new CanadaPostWs\Type\Rating\DeliverySpecType();
        $DeliverySpec->setServiceCodes($serviceCodes);

        // Origin Address
        $Sender         = new CanadaPostWs\Type\Rating\SenderType();
        $AddressDetails = new CanadaPostWs\Type\Rating\DomesticAddressDetailsType();
        $AddressDetails->setPostalZipCode($senderAddress->postcode);
        $Sender->setAddressDetails($AddressDetails);
        $DeliverySpec->setSender($Sender);

        // Destination Address
        $countryCode = \Country::getIsoById($destinationAddress->id_country);
        $postcode = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper($destinationAddress->postcode));
        $Destination               = new CanadaPostWs\Type\Rating\DestinationType();
        $DestinationAddressDetails = new CanadaPostWs\Type\Rating\DestinationAddressDetailsType();
        $DestinationAddressDetails->setPostalZipCode($postcode);
        $DestinationAddressDetails->setCountryCode($countryCode);
        $Destination->setAddressDetails($DestinationAddressDetails);
        $DeliverySpec->setDestination($Destination);

        // Get service details for each requested service
//        $supportedOptionCodes = array();
//        $mandatoryOptionCodes = array();
//        $services = array();
//        foreach ($serviceCodes as $serviceCode) {
//            if ($Service = $this->getService($serviceCode, $countryCode)) {
//                $services[] = $Service;
//                // Get the option codes that all services have in common (array_intersect)
//                if (empty($supportedOptionCodes)) {
//                    $supportedOptionCodes = $Service->getSupportedOptionsArray();
//                } else {
//                    $supportedOptionCodes = array_intersect($supportedOptionCodes, $Service->getSupportedOptionsArray());
//                }
//                // Get mandatory option codes
//                $mandatoryOptionCodes = array_merge($mandatoryOptionCodes, $Service->getMandatoryOptionsArray());
//            }
//        }

        // TODO: add Options preference in module config to allow user
        // TODO: to retrieve rates one at a time with requested options
        // Options
        if (!empty($optionsArr)) {
            $Options = new CanadaPostWs\Type\Rating\OptionsType();
            foreach ($optionsArr as $optionCode => $optionValue) {
                $Option = new CanadaPostWs\Type\Rating\OptionType();
                $Option->setOptionCode($optionCode);

                if ($optionCode == 'COV') {
                    $Option->setOptionAmount((float)$optionValue['COV-option-amount']);
                }

                if ($optionCode == 'COD') {
                    $Option->setOptionAmount((float)$optionValue['COD-option-amount']);
                    $Option->setOptionQualifier1((bool)$optionValue['COD-option-qualifier-1']);
                }

                if ($optionCode == 'D2PO') {
                    $Option->setOptionQualifier2((string)$optionValue['D2PO-option-qualifier-2']);
                }

                $Options->addOption($Option);
            }
            $DeliverySpec->setOptions($Options);
        }

        // Dimensions
        $ParcelCharacteristics = new CanadaPostWs\Type\Rating\ParcelCharacteristicsType();
        $ParcelCharacteristics->setWeight(number_format($weight, 3, '.', ''));
        $Dimension = new CanadaPostWs\Type\Rating\DimensionType();
        $Dimension->setLength(number_format($Box->length, 2, '.', ''));
        $Dimension->setWidth(number_format($Box->width, 2, '.', ''));
        $Dimension->setHeight(number_format($Box->height, 2, '.', ''));
        $ParcelCharacteristics->setDimensions($Dimension);
        $DeliverySpec->setParcelCharacteristics($ParcelCharacteristics);

        // Contract ID
        $SettlementInfo = new CanadaPostWs\Type\Rating\SettlementInfoType();
        if (self::getConfig('CONTRACT')) {
            $SettlementInfo->setContractId(self::getConfig('CONTRACT'));
        }
        $DeliverySpec->setSettlementInfo($SettlementInfo);
        $parcel->setDeliverySpec($DeliverySpec);

        // Call API
        try {
            $rating = new CanadaPostWs\Rating($this->getApiParams());

            $rating->getRate($parcel);

            return $rating;
        } catch (\Exception $e) {
            $this->log('Error fetching rates: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /**
     * @param string $serviceCode
     * @param string $countryCode
     *
     * @return bool|Service
     */
    public function getService($serviceCode, $countryCode)
    {
        try {
            if ($Service = Service::getServiceByCodeAndCountry($serviceCode, $countryCode)) {
                return $Service;
            } else {
                $rating = new CanadaPostWs\Rating($this->getApiParams());
                $rating->getService($serviceCode, $countryCode);
                if ($rating->isSuccess()) {
                    /* @var CanadaPostWs\Type\Rating\ServiceInfoType $ServiceInfoType */
                    $ServiceInfoType = $rating->getResponse();
                    $Service = new Service();

                    $Service->name = $ServiceInfoType->getServiceName();
                    $Service->serviceCode = $ServiceInfoType->getServiceCode();
                    $Service->countryCode = $countryCode;
                    $Service->maxWeight = $ServiceInfoType->getRestrictions()->getWeightRestriction()->getMax();
                    $Service->maxLength = $ServiceInfoType->getRestrictions()->getDimensionalRestrictions()->getLength()->getMax();
                    $Service->maxWidth = $ServiceInfoType->getRestrictions()->getDimensionalRestrictions()->getWidth()->getMax();
                    $Service->maxHeight = $ServiceInfoType->getRestrictions()->getDimensionalRestrictions()->getHeight()->getMax();

                    $supportedOptionArray = array();
                    $mandatoryOptionArray = array();
                    /* @var CanadaPostWs\Type\Rating\OptionInfoType $OptionInfoType */
                    foreach ($ServiceInfoType->getOptions()->getOptions() as $OptionInfoType) {
                        $supportedOptionArray[] = $OptionInfoType->getOptionCode();
                        if ($OptionInfoType->getMandatory() == "true") {
                            $mandatoryOptionArray[] = $OptionInfoType->getOptionCode();
                        }
                    }
                    $Service->setSupportedOptionsArray($supportedOptionArray);
                    $Service->setMandatoryOptionsArray($mandatoryOptionArray);
                    $Service->save();

                    return $Service;
                } else {
                    $this->log('Error fetching service: "' . $rating->getErrorMessage() . '"');
                    return false;
                }
            }
        } catch (\Exception $e) {
            $this->log('Error fetching service: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /**
     * Create shipment
     * @var array $values
     * @return bool|CanadaPostWs\Shipping|CanadaPostWs\NcShipping
     * */
    public function createShipment($values)
    {
        // Determine whether to use Shipment or NcShipment
        $contract = $this->isContract();

        // Shipment object to create
        if ($contract) {
            $Shipment = new CanadaPostWs\Type\Shipment\ShipmentType();
            $namespace = 'CanadaPostWs\Type\Shipment\\';

            if (self::getConfig('CONTRACT') && null !== $values['group-id']) {
                $Group = new Group($values['group-id']);
                $Shipment->setGroupId($Group->name);
            } else {
                $Shipment->setTransmitShipment(true);
            }

            if (self::getConfig('PICKUP')) {
                $requestedShippingPoint = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper(self::getConfig('REQUESTED_SHIPPING_POINT')));
                $Shipment->setRequestedShippingPoint($requestedShippingPoint);
                $Shipment->setCpcPickupIndicator(true);
            } else {
                $Shipment->setShippingPointId(self::getConfig('SHIPPING_POINT'));
            }
            $Shipment->setExpectedMailingDate(new \DateTime());
        } else {
            $Shipment = new CanadaPostWs\Type\NcShipment\NonContractShipmentType();
            $namespace = 'CanadaPostWs\Type\NcShipment\\';

            $requestedShippingPoint = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper(self::getConfig('REQUESTED_SHIPPING_POINT')));
            $Shipment->setRequestedShippingPoint($requestedShippingPoint);
        }

        /* @var $DeliverySpec CanadaPostWs\Type\Shipment\DeliverySpecType|CanadaPostWs\Type\NcShipment\DeliverySpecType */
        $DeliverySpecClassName = $namespace . 'DeliverySpecType';
        $DeliverySpec = new $DeliverySpecClassName;
        $DeliverySpec->setServiceCode($values['service-code']);

        /* @var $Sender CanadaPostWs\Type\Shipment\SenderType|CanadaPostWs\Type\NcShipment\SenderType */
        $SenderClassName = $namespace . 'SenderType';
        $Sender = new $SenderClassName;

        $SenderObj = new Address($values['sender']);
        $Sender->setName($SenderObj->name);
        $Sender->setCompany($SenderObj->company);
        $Sender->setContactPhone($SenderObj->phone);

        /* @var $AddressDetails CanadaPostWs\Type\Shipment\AddressDetailsType|CanadaPostWs\Type\NcShipment\DomesticAddressDetailsType */
        if ($contract) {
            $AddressDetailsTypeClassName = $namespace . 'AddressDetailsType';
        } else {
            $AddressDetailsTypeClassName = $namespace . 'DomesticAddressDetailsType';
        }
        $AddressDetails = new $AddressDetailsTypeClassName;

        $AddressDetails->setAddressLine1($SenderObj->address1);
        if ($SenderObj->address2) {
            $AddressDetails->setAddressLine2($SenderObj->address2);
        }
        $AddressDetails->setCity($SenderObj->city);
        $State = new State($SenderObj->id_state);
        $AddressDetails->setProvState($State->iso_code);
        if ($contract) {
            $AddressDetails->setCountryCode(\Country::getIsoById($SenderObj->id_country));
        }
        $AddressDetails->setPostalZipCode($SenderObj->postcode);

        $Sender->setAddressDetails($AddressDetails);

        $DeliverySpec->setSender($Sender);

        /* @var $Destination CanadaPostWs\Type\Shipment\DestinationType|CanadaPostWs\Type\NcShipment\DestinationType */
        $DestinationClassName = $namespace . 'DestinationType';
        $Destination = new $DestinationClassName;

        if (isset($values['name']) && !Tools::isEmpty($values['name'])) {
            $Destination->setName($values['name']);
        }
        if (isset($values['company']) && !Tools::isEmpty($values['company'])) {
            $Destination->setCompany($values['company']);
        }
        if (isset($values['client-voice-number']) && !Tools::isEmpty($values['client-voice-number'])) {
            $Destination->setClientVoiceNumber($values['client-voice-number']);
        }
        if (isset($values['additional-address-info']) && !Tools::isEmpty($values['additional-address-info'])) {
            $Destination->setAdditionalAddressInfo($values['additional-address-info']);
        }

        /* @var $DestinationAddressDetails CanadaPostWs\Type\Shipment\DestinationAddressDetailsType|CanadaPostWs\Type\NcShipment\DestinationAddressDetailsType */
        $DestinationAddressDetailsClassName = $namespace . 'DestinationAddressDetailsType';
        $DestinationAddressDetails = new $DestinationAddressDetailsClassName;

        $DestinationAddressDetails->setAddressLine1($values['address-line-1']);
        if (isset($values['address-line-2']) && !Tools::isEmpty($values['address-line-2'])) {
            $DestinationAddressDetails->setAddressLine2($values['address-line-2']);
        }
        $DestinationAddressDetails->setCity($values['city']);
         if (isset($values['prov-state']) && !empty($values['prov-state'])) {
            $DestinationAddressDetails->setProvState($values['prov-state']);
        }
        $DestinationAddressDetails->setCountryCode($values['country-code']);
        $DestinationAddressDetails->setPostalZipCode($values['postal-zip-code']);

        $Destination->setAddressDetails($DestinationAddressDetails);

        $DeliverySpec->setDestination($Destination);

        /* @var $Options CanadaPostWs\Type\Shipment\OptionsType|CanadaPostWs\Type\NcShipment\OptionsType */
        $OptionsClassName = $namespace . 'OptionsType';
        $Options = new $OptionsClassName;

        foreach (Method::$options as $option_code => $name) {
            if (isset($values['options_'.$option_code]) && $values['options_'.$option_code] == true && !Tools::isEmpty($values['options_'.$option_code])) {

                /* @var $Option CanadaPostWs\Type\Shipment\OptionType|CanadaPostWs\Type\NcShipment\OptionType */
                $OptionClassName = $namespace . 'OptionType';
                $Option = new $OptionClassName;

                $Option->setOptionCode($option_code);

                if ($option_code == 'COV') {
                    $Option->setOptionAmount((float)$values['COV-option-amount']);
//                    $Option->setOptionQualifier1($values['COV-option-qualifier-1']);
                }

                if ($option_code == 'COD') {
                    $Option->setOptionAmount($values['COD-option-amount']);
                    $Option->setOptionQualifier1((bool)$values['COD-option-qualifier-1']);
                }

                if ($option_code == 'D2PO') {
                    $Option->setOptionQualifier2((float)$values['D2PO-option-qualifier-2']);
                }

                $Options->addOption($Option);
            }
        }

        if ($values['country-code'] != 'CA' && isset($values['non_delivery_options']) && !Tools::isEmpty($values['non_delivery_options'])) {

            // Get proper non_delivery_options code since they are not all supported by all carriers
            if ($values['non_delivery_options'] != 'ABAN') {
                $Service = $this->getService($values['service-code'], $values['country-code']);
                if ($Service) {
                    if (!in_array($values['non_delivery_options'], $Service->getSupportedOptionsArray())) {
                        if (in_array('RASE', $Service->getSupportedOptionsArray())) {
                            $values['non_delivery_options'] = 'RASE';
                        } elseif (in_array('RTS', $Service->getSupportedOptionsArray())) {
                            $values['non_delivery_options'] = 'RTS';
                        } else {
                            $values['non_delivery_options'] = 'ABAN';
                        }
                    }
                }
            }

            /* @var $Option CanadaPostWs\Type\Shipment\OptionType|CanadaPostWs\Type\NcShipment\OptionType */
            $OptionClassName = $namespace . 'OptionType';
            $Option = new $OptionClassName;

            $Option->setOptionCode($values['non_delivery_options']);
            $Options->addOption($Option);
        }

        $optionArr = $Options->getOptions();
        if (!empty($optionArr)) {
            $DeliverySpec->setOptions($Options);
        }

        /* @var $ParcelCharacteristics CanadaPostWs\Type\Shipment\ParcelCharacteristicsType|CanadaPostWs\Type\NcShipment\ParcelCharacteristicsType */
        $ParcelCharacteristicsClassName = $namespace . 'ParcelCharacteristicsType';
        $ParcelCharacteristics = new $ParcelCharacteristicsClassName;

        $ParcelCharacteristics->setWeight($values['weight']);


        /* @var $Dimension CanadaPostWs\Type\Shipment\DimensionType|CanadaPostWs\Type\NcShipment\DimensionType */
        $DimensionClassName = $namespace . 'DimensionType';
        $Dimension = new $DimensionClassName;

        $Dimension->setLength($values['length']);
        $Dimension->setWidth($values['width']);
        $Dimension->setHeight($values['height']);

        $ParcelCharacteristics->setDimensions($Dimension);
        $ParcelCharacteristics->setUnpackaged($values['unpackaged']);
        $ParcelCharacteristics->setMailingTube($values['mailing-tube']);
        if ($contract) {
            $ParcelCharacteristics->setOversized($values['oversized']);
        }

        $DeliverySpec->setParcelCharacteristics($ParcelCharacteristics);

        if (isset($values['email']) && !Tools::isEmpty($values['email'])) {

            /* @var $Notification CanadaPostWs\Type\Shipment\NotificationType|CanadaPostWs\Type\NcShipment\NotificationType */
            $NotificationClassName = $namespace . 'NotificationType';
            $Notification = new $NotificationClassName;

            $Notification->setEmail($values['email']);
            $Notification->setOnShipment($values['notification_on-shipment']);
            $Notification->setOnException($values['notification_on-exception']);
            $Notification->setOnDelivery($values['notification_on-delivery']);

            $DeliverySpec->setNotification($Notification);
        }

        if ($contract) {
            $PrintPreferences = new CanadaPostWs\Type\Shipment\PrintPreferencesType();
            $PrintPreferences->setOutputFormat($values['output-format']);

            $DeliverySpec->setPrintPreferences($PrintPreferences);
        }

        /* @var $Preferences CanadaPostWs\Type\Shipment\PreferencesType|CanadaPostWs\Type\NcShipment\PreferencesType */
        $PreferencesClassName = $namespace . 'PreferencesType';
        $Preferences = new $PreferencesClassName;

        $Preferences->setShowPackingInstructions($values['show-packing-instructions']);
        $Preferences->setShowPostageRate($values['show-postage-rate']);
        $Preferences->setShowInsuredValue($values['show-insured-value']);

        $DeliverySpec->setPreferences($Preferences);

        if ($values['country-code'] != 'CA') {

            /* @var $Customs CanadaPostWs\Type\Shipment\CustomsType|CanadaPostWs\Type\NcShipment\CustomsType */
            $CustomsClassName = $namespace . 'CustomsType';
            $Customs = new $CustomsClassName;
            $Customs->setCurrency($values['currency']);
            $Customs->setConversionFromCad($values['conversion-rate-from-cad']);
            $Customs->setReasonForExport($values['reason-for-export']);
            if (isset($values['other-reason']) && !Tools::isEmpty($values['other-reason'])) {
                $Customs->setOtherReason($values['other-reason']);
            }
            $Customs->setCertificateNumber($values['certificate-number']);
            $Customs->setLicenceNumber($values['licence-number']);
            $Customs->setInvoiceNumber($values['invoice-number']);

            /* @var $SkuList CanadaPostWs\Type\Shipment\SkuListType|CanadaPostWs\Type\NcShipment\SkuListType */
            $SkuListClassName = $namespace . 'SkuListType';
            $SkuList = new $SkuListClassName;

            foreach ($values['items'] as $item) {
                /* @var $Sku CanadaPostWs\Type\Shipment\SkuType|CanadaPostWs\Type\NcShipment\SkuType */
                $SkuClassName = $namespace . 'SkuType';
                $Sku = new $SkuClassName;

                $Sku->setCustomsDescription($item['customs-description']);
                $Sku->setCustomsNumberOfUnits($item['customs-number-of-units']);
                $Sku->setHsTariffCode($item['hs-tariff-code']);
                $Sku->setSku($item['sku']);
                $Sku->setUnitWeight($item['unit-weight']);
                $Sku->setCustomsValuePerUnit($item['customs-value-per-unit']);
                if (array_key_exists('country-of-origin', $item)) {
                    $Sku->setCountryOfOrigin($item['country-of-origin']);
                }
                if (array_key_exists('province-of-origin', $item)) {
                    $Sku->setProvinceOfOrigin($item['province-of-origin']);
                }

                $SkuList->addItem($Sku);
            }
            $Customs->setSkuList($SkuList);

            $DeliverySpec->setCustoms($Customs);
        }

        /* @var $References CanadaPostWs\Type\Shipment\ReferencesType|CanadaPostWs\Type\NcShipment\ReferencesType */
        $ReferencesClassName = $namespace . 'ReferencesType';
        $References = new $ReferencesClassName;

        $References->setCostCentre($values['cost-centre']);
        $References->setCustomerRef1($values['customer-ref-1']);
        $References->setCustomerRef2($values['customer-ref-2']);

        $DeliverySpec->setReferences($References);

        if ($contract) {
            $SettlementInfo = new CanadaPostWs\Type\Shipment\SettlementInfoType();
            if (self::getConfig('CONTRACT')) {
                $SettlementInfo->setContractId(self::getConfig('CONTRACT'));
            }

            if (self::getConfig('CONTRACT')) {
                $SettlementInfo->setIntendedMethodOfPayment($values['intended-method-of-payment']);
            } else {
                $SettlementInfo->setIntendedMethodOfPayment('CreditCard');
            }

            $DeliverySpec->setSettlementInfo($SettlementInfo);
        }

        $Shipment->setDeliverySpec($DeliverySpec);

        if ($values['return-spec'] == true &&
            $contract &&
            $values['country-code'] == 'CA'
        ) {
            $ReturnAddress = new Address($values['return-recipient']);
            $ReturnSpec = new CanadaPostWs\Type\Shipment\ReturnSpecType();
            $ReturnSpec->setServiceCode($values['return-service-code']);

            $ReturnRecipient = new CanadaPostWs\Type\Shipment\ReturnRecipientType();
            $ReturnRecipient->setName($ReturnAddress->name);
            if ($ReturnAddress->company) {
                $ReturnRecipient->setCompany($ReturnAddress->company);
            }

            $ReturnAddressDetails = new CanadaPostWs\Type\Shipment\DomesticAddressDetailsType();
            $ReturnAddressDetails->setAddressLine1($ReturnAddress->address1);
            if ($ReturnAddress->address2) {
                $ReturnAddressDetails->setAddressLine2($ReturnAddress->address2);
            }
            $ReturnAddressDetails->setCity($ReturnAddress->city);
            $ReturnAddressDetails->setProvState($ReturnAddress->city);

            $State = new State($ReturnAddress->id_state);
            $ReturnAddressDetails->setProvState($State->iso_code);
            $ReturnAddressDetails->setPostalZipCode($ReturnAddress->postcode);

            $ReturnRecipient->setAddressDetails($ReturnAddressDetails);
            $ReturnSpec->setReturnRecipient($ReturnRecipient);

            $Shipment->setReturnSpec($ReturnSpec);
        }

        // Send request to create shipment
        try {
            if ($contract) {
                $Shipping = new CanadaPostWs\Shipping($this->getApiParams());
                $Shipping->createShipment($Shipment);
            } else {
                $Shipping = new CanadaPostWs\NcShipping($this->getApiParams());
                $Shipping->createNcShipment($Shipment);
            }

            return $Shipping;
        } catch (\Exception $e) {
            $this->log('Error creating shipment: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /**
     * Create return shipment
     * @return bool|CanadaPostWs\AuthorizedReturn
     * */
    public function createReturnShipment()
    {
        // Shipment object to create
        $AuthorizedReturnType = new CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnType();

        $AuthorizedReturnType->setServiceCode(Tools::getValue('service-code'));

        $Receiver = new CanadaPostWs\Type\Shipment\ReturnRecipientType();
        $ReceiverObj = new Address(Tools::getValue('receiver'));
        $Receiver->setName($ReceiverObj->name);
        $Receiver->setCompany($ReceiverObj->company);
        $Receiver->setReceiverVoiceNumber($ReceiverObj->phone);

        $AddressDetails = new CanadaPostWs\Type\Shipment\DomesticAddressDetailsType();
        $AddressDetails->setAddressLine1($ReceiverObj->address1);
        if ($ReceiverObj->address2) {
            $AddressDetails->setAddressLine2($ReceiverObj->address2);
        }
        $AddressDetails->setCity($ReceiverObj->city);
        $State = new State($ReceiverObj->id_state);
        $AddressDetails->setProvState($State->iso_code);
        $AddressDetails->setPostalZipCode($ReceiverObj->postcode);

        $Receiver->setAddressDetails($AddressDetails);

        $AuthorizedReturnType->setReceiver($Receiver);

        $Returner = new CanadaPostWs\Type\Shipment\ReturnRecipientType();
        if (Tools::getIsset('name') && !Tools::isEmpty(Tools::getValue('name'))) {
            $Returner->setName(Tools::getValue('name'));
        }
        if (Tools::getIsset('company') && !Tools::isEmpty(Tools::getValue('company'))) {
            $Returner->setCompany(Tools::getValue('company'));
        }

        $DomesticAddressDetails = new CanadaPostWs\Type\Shipment\DomesticAddressDetailsType();
        $DomesticAddressDetails->setAddressLine1(Tools::getValue('address-line-1'));
        if (Tools::getIsset('address-line-2') && !Tools::isEmpty(Tools::getValue('address-line-2'))) {
            $DomesticAddressDetails->setAddressLine2(Tools::getValue('address-line-2'));
        }
        $DomesticAddressDetails->setCity(Tools::getValue('city'));
        $DomesticAddressDetails->setProvState(Tools::getValue('prov-state'));
        $postcode = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper(Tools::getValue('postal-zip-code')));
        $DomesticAddressDetails->setPostalZipCode($postcode);

        $Returner->setAddressDetails($DomesticAddressDetails);

        $AuthorizedReturnType->setReturner($Returner);

        $ParcelCharacteristics = new CanadaPostWs\Type\Shipment\ParcelCharacteristicsType();

        $ParcelCharacteristics->setWeight(Tools::getValue('weight'));

        $Dimension = new CanadaPostWs\Type\Shipment\DimensionType();
        $Dimension->setLength(Tools::getValue('length'));
        $Dimension->setWidth(Tools::getValue('width'));
        $Dimension->setHeight(Tools::getValue('height'));

        $ParcelCharacteristics->setDimensions($Dimension);

        $AuthorizedReturnType->setParcelCharacteristics($ParcelCharacteristics);

        if (Tools::getIsset('email') && !Tools::isEmpty(Tools::getValue('email'))) {
            $Notifications = new CanadaPostWs\Type\Shipment\NotificationsType();
            $Notification = new CanadaPostWs\Type\Shipment\NotificationType();

            $Notification->setEmail(Tools::getValue('email'));
            $Notification->setOnShipment(Tools::getValue('notification_on-shipment'));
            $Notification->setOnException(Tools::getValue('notification_on-exception'));
            $Notification->setOnDelivery(Tools::getValue('notification_on-delivery'));

            $Notifications->addNotification($Notification);

            $AuthorizedReturnType->setNotifications($Notifications);
        }

        $PrintPreferences = new CanadaPostWs\Type\Shipment\PrintPreferencesType();
        $PrintPreferences->setOutputFormat(Tools::getValue('output-format'));

        $AuthorizedReturnType->setPrintPreferences($PrintPreferences);

        $References = new CanadaPostWs\Type\Shipment\ReferencesType();

        $References->setCustomerRef1(Tools::getValue('customer-ref-1'));
        $References->setCustomerRef2(Tools::getValue('customer-ref-2'));

        $AuthorizedReturnType->setReferences($References);

        $SettlementInfo = new CanadaPostWs\Type\Shipment\SettlementInfoType();

        $AuthorizedReturnType->setSettlementInfo($SettlementInfo);

        // Send request to create shipment
        try {
            $AuthorizedReturn = new CanadaPostWs\AuthorizedReturn($this->getApiParams());
            $AuthorizedReturn->createAuthorizedReturn($AuthorizedReturnType);

            return $AuthorizedReturn;
        } catch (\Exception $e) {
            $this->log('Error creating return: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /*
     * Transmit shipments to generate a manifest.
     * @return ManifestsType
     * */
    public function transmitShipments()
    {
        $ShipmentTransmitSetType = new CanadaPostWs\Type\Manifest\ShipmentTransmitSetType();

        $Group = new Group(Tools::getValue('group-id'));
        $GroupIdListType = new CanadaPostWs\Type\Manifest\GroupIdListType();
        $GroupIdListType->setGroupId($Group->name);
        $ShipmentTransmitSetType->setGroupIds(array($GroupIdListType));
        $ShipmentTransmitSetType->setCpcPickupIndicator(Tools::getValue('PICKUP'));
        $ShipmentTransmitSetType->setMethodOfPayment(Tools::getValue('intended-method-of-payment'));
        if (Tools::getValue('PICKUP') == 1 && Tools::getIsset('REQUESTED_SHIPPING_POINT') && !Tools::isEmpty(Tools::getValue('REQUESTED_SHIPPING_POINT'))) {
            $requestedShippingPoint = preg_replace('/[^A-Za-z0-9]/', '', Tools::strtoupper(Tools::getValue('REQUESTED_SHIPPING_POINT')));
            $ShipmentTransmitSetType->setRequestedShippingPoint($requestedShippingPoint);
        } elseif (Tools::getIsset('SHIPPING_POINT') && !Tools::isEmpty(Tools::getValue('SHIPPING_POINT'))) {
            $ShipmentTransmitSetType->setShippingPointId(Tools::getValue('SHIPPING_POINT'));
        }

        $Sender = new Address(Tools::getValue('sender'));
        $ManifestAddressType = new CanadaPostWs\Type\Manifest\ManifestAddressType();
        $ManifestAddressType->setManifestName($Sender->name);
        $ManifestAddressType->setManifestCompany($Sender->company);
        $ManifestAddressType->setPhoneNumber($Sender->phone);

        $AddressDetailsType = new CanadaPostWs\Type\Manifest\AddressDetailsType();
        $AddressDetailsType->setAddressLine1($Sender->address1);
        $AddressDetailsType->setAddressLine2($Sender->address2);
        $AddressDetailsType->setCity($Sender->city);
        $AddressDetailsType->setProvState($Sender->id_state);
        if ($Sender->address2) {
            $AddressDetailsType->setAddressLine2($Sender->address2);
        }
        $State = new State($Sender->id_state);
        $AddressDetailsType->setProvState($State->iso_code);
        $AddressDetailsType->setCountryCode(\Country::getIsoById($Sender->id_country));
        $AddressDetailsType->setPostalZipCode($Sender->postcode);

        $ManifestAddressType->setAddressDetails($AddressDetailsType);

        $ShipmentTransmitSetType->setManifestAddress($ManifestAddressType);
        $ShipmentTransmitSetType->setDetailedManifests(true);

        // Send request to transmit shipments
        try {
            $Shipping = new CanadaPostWs\Shipping($this->getApiParams());
            $Shipping->transmitShipments($ShipmentTransmitSetType);

            return $Shipping;
        } catch (\Exception $e) {
            $this->log('Error transmitting shipments: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /**
     * @var bool $redirect
     * */
    public function processTransmitShipments($redirect = false)
    {
        $Shipping = $this->transmitShipments();

        // Wait for the transmit process to complete
        sleep(10);

        $error = false;
        $DateTime = new DateTime();
        $filePath = false;
        $response = array();

        /* @var $ManifestsType CanadaPostWs\Type\Manifest\ManifestsType */
        if ($Shipping instanceof \CanadaPostWs\Shipping && $Shipping->isSuccess()) {
            $ManifestsType = $Shipping->getResponse();
            if ($ManifestsType instanceof CanadaPostWs\Type\Manifest\ManifestsType) {
                try {
                    $files = array();

                    // Get each manifest details
                    foreach ($ManifestsType->getLinks() as $link) {
                        /* @var $ManifestType CanadaPostWs\Type\Manifest\ManifestType */
                        $Shipping->getManifest($link->getHref());

                        // Get Manifest Links
                        if ($Shipping && $Shipping->isSuccess() && $Shipping->getResponse() instanceof CanadaPostWs\Type\Manifest\ManifestType) {
                            $ManifestType = $Shipping->getResponse();


                            /* @var $ManifestDetailsType CanadaPostWs\Type\Manifest\ManifestDetailsType */
                            $Shipping->getManifestDetails($ManifestType->getDetailsLink()->getHref());

                            // Get Manifest Details
                            if ($Shipping && $Shipping->isSuccess()) {
                                $ManifestDetailsType = $Shipping->getResponse();
                                // Create each manifest object in DB
                                $this->createManifestObject($ManifestType, $ManifestDetailsType);

                                // Setup dir hierarchy (Y/m/d)
                                $this->makeLabelDirectoryForDate($DateTime, $this->getManifestsPathLocal());

                                // Get PDF manifest and store it
                                $Shipping->getArtifact(
                                    $ManifestType->getArtifactLink()->getHref(),
                                    $ManifestType->getPoNumber(),
                                    $this->getManifestsPathLocal() . $DateTime->format('Y/m/d')
                                );

                                // Set all shipments in group to Transmitted
                                \Db::getInstance()->update(
                                    Shipment::$definition['table'],
                                    array('transmitted' => 1),
                                    '`id_group` = '.Tools::getValue('group-id').' AND `voided` = 0'
                                );

                                $files[] = $this->getManifestsPathLocal() . $DateTime->format('Y/m/d') . '/' . $ManifestType->getPoNumber() . '.pdf';
                                $filePath = $this->getManifestsPathUri() . $DateTime->format('Y/m/d') . '/' . $ManifestType->getPoNumber() . '.pdf';
                            } else {
                                $error = sprintf(
                                    'Error getting manifest details: "%s"',
                                    \CanadaPostPs\Tools::formatErrorMessage($Shipping->getErrorMessage())
                                );
                            }
                        } else {
                            $error = sprintf(
                                'Error getting manifest: "%s"',
                                \CanadaPostPs\Tools::formatErrorMessage($Shipping->getErrorMessage())
                            );
                        }
                    }
                    if (!$error) {
                        // If there's multiple labels and including returns/invoices is enabled, merge PDFs
                        if (count($files) > 1) {
                            $fileName = 'merged_' . $DateTime->format('Y-m-d_H:i:s') . '.pdf';
                            $filePath = $this->getManifestsPathUri() . $DateTime->format('Y/m/d') . '/' . $fileName;
                            MergePdf::merge(
                                $files,
                                MergePdf::DESTINATION__DISK,
                                $this->getManifestsPathLocal() . $DateTime->format('Y/m/d') . '/' . $fileName
                            );
                        }

                        if (self::getConfig('OPEN_LABEL_ON_CREATION')) {
                            // Output src path if ajax request
                            if (Tools::isSubmit('ajaxCreateLabel')) {
                                $response['src'] = $filePath;
                                die(json_encode($response));
                            }

                            // Redirect to label
                            Tools::redirectAdmin($filePath);
                        } elseif ($redirect) {
                            Tools::redirectAdmin($redirect);
                        }
                    }
                } catch (\Exception $e) {
                    $error = sprintf('Error creating manifest object: "%s"', Tools::formatException($e));
                }
            }
        } else {
            $error = sprintf(
                'Error creating manifest: "%s"',
                \CanadaPostPs\Tools::formatErrorMessage($Shipping->getErrorMessage())
            );
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);

            if (Tools::isSubmit('ajaxCreateLabel')) {
                $response['error'] = $error;
                die(json_encode($response));
            }
        } else {
            $this->context->controller->confirmations[] = $this->l('Successfully transmitted shipments.');
        }
    }

    /**
     * @return CanadaPostWs\Type\Tracking\TrackingDetailsType
     * */
    public function processTracking($trackingPin)
    {
        $error = false;
        try {
            $Tracking = new CanadaPostWs\Tracking($this->getApiParams());
            $Tracking->getTrackingDetails($trackingPin);
            /* @var $TrackingDetailsType CanadaPostWs\Type\Tracking\TrackingDetailsType */
            $TrackingDetailsType = $Tracking->getResponse();
            if ($TrackingDetailsType instanceof \CanadaPostWs\Type\Tracking\TrackingDetailsType && $Tracking->isSuccess()) {
                return $TrackingDetailsType;
            } else {
                $error = sprintf(
                    'API Error tracking parcel: "%s"',
                    \CanadaPostPs\Tools::formatErrorMessage($Tracking->getErrorMessage())
                );
            }
        } catch (\Exception $e) {
            $error = sprintf('Error tracking parcel: "%s"', Tools::formatException($e));
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);
        }
    }

    public function cacheCart($id_cart)
    {
        try {
            $Cart = new Cart($id_cart);
            $Cache = \CanadaPostPs\Cache::getByCartId($id_cart);
            if (!\Validate::isLoadedObject($Cache)) {
                $Cache = new \CanadaPostPs\Cache();
            }

            $Cache->id_cart = $id_cart;
            $Cache->cart_quantity = \CanadaPostPs\Cache::getTotalCartQty($id_cart);
            $Cache->id_address = $Cart->id_address_delivery;
            $Cache->save();

            return $Cache;
        } catch (\Exception $e) {
            $this->log('Error Caching Rates: "' . Tools::formatException($e) . '"');
            return false;
        }
    }

    /*
     * Pack the cart products into the module boxes
     *
     * @return array
     * */
    public function packProducts($products)
    {
        // Pack all products in the module's boxes to determine the dimensions for the rates.
        $packer = new \CanadaPost\BoxPacker\Packer();

        foreach (Box::getBoxes(array('active' => 1)) as $box) {
            $packer->addBox(new Box($box['id_box']));
        }

        foreach ($products as $product) {
            if ($product['is_virtual']) {
                continue;
            }
            // If this is from an Order, get Cart Quantity instead
            if (array_key_exists('cart_quantity', $product)) {
                $quantity = $product['cart_quantity'];
            }

            $Item = new Item($product['id_product']);
            // If item dimensions are zero, set to 0.1
            foreach (array('depth', 'width', 'height') as $dimension) {
                if ($Item->{$dimension} <= 0) {
                    $Item->{$dimension} = 0.1;
                }
            }
            $Item->weight = $product['weight'];

            // If product_weight exists, it incorporates attribute weight, so use it instead
            if (isset($product['product_weight']) && $product['product_weight'] > 0) {
                $Item->weight = $product['product_weight'];
            }
            $packer->addItem($Item, $quantity);
        }

        try {
            $packedBoxes = $packer->pack();
        } catch (\CanadaPost\BoxPacker\ItemTooLargeException $e) {
            // If the products are too large for the box, use the largest box.
            $packedBoxes = new \CanadaPost\BoxPacker\PackedBoxList();
            $largestBox = Box::getLargestBox(true);
            $largestBox = new Box($largestBox['id_box']);
            $packedBoxes->insert(new \CanadaPost\BoxPacker\PackedBox(
                $largestBox,
                new \CanadaPost\BoxPacker\ItemList(),
                0,
                0,
                0,
                0,
                0,
                0,
                0
            ));
            if (isset($this->context->cart->id)) {
                $this->log('Error. Cart ID: ' . $this->context->cart->id . ' Error: "' . $e->getMessage() . '. The module used the largest active box instead."');
            }
        }

        $boxes = array();
        foreach ($packedBoxes as $packedBox) {
            $boxes[] = $packedBox;
        }

        // Make sure we are using the maximum allowed boxes
        // Only use the largest packed boxes until we reach the max, the boxes are already sorted ASC by size
        if (count($boxes) > self::getConfig('MAX_BOXES')) {
            $boxes = array_slice(
                $boxes,
                -(self::getConfig('MAX_BOXES')),
                self::getConfig('MAX_BOXES'),
                true
            );
        }

        return $boxes;
    }

    /*
     * @return bool
     * */
    public function processSubmitCreateLabel($values, $redirect = false, $id_batch = false)
    {
        $Shipping = $this->createShipment($values);

        $error = false;
        $response = array();

        /* @var $ShipmentInfoType \CanadaPostWs\Type\Shipment\ShipmentInfoType */
        if (($Shipping instanceof \CanadaPostWs\Shipping || $Shipping instanceof CanadaPostWs\NcShipping) && $Shipping->isSuccess()) {
            $ShipmentInfoType = $Shipping->getResponse();
            if ($ShipmentInfoType instanceof \CanadaPostWs\Type\Shipment\ShipmentInfoType || $ShipmentInfoType instanceof CanadaPostWs\Type\NcShipment\NonContractShipmentInfoType) {
                try {

                    // Add address details to ShipmentInfoType
                    $DeliverySpec = new \CanadaPostWs\Type\Shipment\DeliverySpecType();
                    $DeliverySpec->setServiceCode($values['service-code']);
                    $Destination = new \CanadaPostWs\Type\Shipment\DestinationType();
                    $Destination->setName($values['name']);
                    $DestinationAddress = new \CanadaPostWs\Type\Shipment\DestinationAddressDetailsType();
                    $DestinationAddress->setAddressLine1($values['address-line-1']);
                    $DestinationAddress->setAddressLine2($values['address-line-2']);
                    $DestinationAddress->setCity($values['city']);
                    $DestinationAddress->setProvState($values['prov-state']);
                    $DestinationAddress->setCountryCode($values['country-code']);
                    $DestinationAddress->setPostalZipCode($values['postal-zip-code']);
                    $Destination->setAddressDetails($DestinationAddress);
                    $DeliverySpec->setDestination($Destination);
                    $ShipmentInfoType->setDeliverySpec($DeliverySpec);

                    // Create Shipment obj in DB
                    $groupId = isset($values['group-id']) ? $values['group-id'] : false;
                    $Shipment = $this->createShipmentObject($ShipmentInfoType, $values['id_order'], $groupId, $id_batch);
                    $DateTime = new DateTime($Shipment->date_add);
                    $files = array();

                    // Setup dir hierarchy (Y/m/d)
                    $this->makeLabelDirectoryForDate($DateTime, $this->getLabelsShippingPathLocal());

                    // Get PDF label and store it
                    $Shipping->getArtifact(
                        $ShipmentInfoType->getLabelLink()->getHref(),
                        $ShipmentInfoType->getShipmentId(),
                        $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d')
                    );
                    $files[] = $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d') . '/' . $ShipmentInfoType->getShipmentId() . '.pdf';

                    // Create Return label if available
                    if ($this->isContract() && null !== $ShipmentInfoType->getReturnLabelLink()) {
                        $AuthorizedReturnInfoType = new CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnInfoType();
                        $AuthorizedReturnInfoType->setTrackingPin($ShipmentInfoType->getReturnTrackingPin());
                        $AuthorizedReturnInfoType->setReturnLabelLink($ShipmentInfoType->getReturnLabelLink());

                        $ReturnDeliverySpec = clone $DeliverySpec;
                        $ReturnDeliverySpec->setServiceCode($values['return-service-code']);

                        $ReturnShipment = $this->createReturnShipmentObject($AuthorizedReturnInfoType, $ReturnDeliverySpec, $values['id_order']);
                        $ReturnDateTime = new DateTime($ReturnShipment->date_add);

                        // Setup dir hierarchy (Y/m/d)
                        $this->makeLabelDirectoryForDate($ReturnDateTime, $this->getLabelsReturnsPathLocal());

                        // Get PDF label and store it
                        $Shipping->getArtifact(
                            $AuthorizedReturnInfoType->getReturnLabelLink()->getHref(),
                            $AuthorizedReturnInfoType->getTrackingPin(),
                            $this->getLabelsReturnsPathLocal() . $ReturnDateTime->format('Y/m/d')
                        );
                        if (self::getConfig('INCLUDE_RETURN_LABEL')) {
                            $files[] = $this->getLabelsReturnsPathLocal() . $ReturnDateTime->format('Y/m/d') . '/' . $AuthorizedReturnInfoType->getTrackingPin() . '.pdf';
                        }
                    }

                    // Create Commercial Invoice if available
                    if (null !== $ShipmentInfoType->getCommercialInvoiceLink()) {
                        // Get PDF label and store it
                        $Shipping->getArtifact(
                            $ShipmentInfoType->getCommercialInvoiceLink()->getHref(),
                            $ShipmentInfoType->getShipmentId().'_invoice',
                            $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d')
                        );
                        if (self::getConfig('INCLUDE_INVOICE')) {
                            $files[] = $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d') . '/' . $ShipmentInfoType->getShipmentId() . '_invoice.pdf';
                        }
                    }

                    // Update tracking number
                    if (self::getConfig('UPDATE_TRACKING_NUMBER') && isset($values['id_order'])) {
                        $Order = new \Order($values['id_order']);
                        if (\Validate::isLoadedObject($Order)) {
                            $OrderCarrier = new \OrderCarrier($Order->getIdOrderCarrier());
                            $OrderCarrier->tracking_number = $ShipmentInfoType->getTrackingPin();
                            $OrderCarrier->save();

                            if (self::getConfig('SEND_IN_TRANSIT_EMAIL')) {
                                if (version_compare(_PS_VERSION_, '1.7.1.0') >= 0) {
                                    $OrderCarrier->sendInTransitEmail($Order);
                                } else {
                                    $Customer = new \Customer((int)$Order->id_customer);
                                    $Carrier = new \Carrier((int)$Order->id_carrier, $Order->id_lang);
                                    $templateVars = array(
                                        '{followup}' => str_replace('@', $OrderCarrier->tracking_number, $Carrier->url),
                                        '{firstname}' => $Customer->firstname,
                                        '{lastname}' => $Customer->lastname,
                                        '{id_order}' => $Order->id,
                                        '{shipping_number}' => $OrderCarrier->tracking_number,
                                        '{order_name}' => $Order->getUniqReference()
                                    );
                                    @\Mail::Send(
                                        (int)$Order->id_lang,
                                        'in_transit',
                                        \Mail::l('Package in transit', (int)$Order->id_lang),
                                        $templateVars,
                                        $Customer->email,
                                        $Customer->firstname.' '.$Customer->lastname,
                                        null,
                                        null,
                                        null,
                                        null,
                                        _PS_MAIL_DIR_,
                                        true,
                                        (int)$Order->id_shop
                                    );
                                }
                            }
                        }
                    }

                    // Update order status
                    if (self::getConfig('UPDATE_ORDER_STATUS') && isset($values['id_order'])) {
                        $Order = new \Order($values['id_order']);
                        if (\Validate::isLoadedObject($Order)) {
                            $currentState = $Order->getCurrentOrderState();
                            if (\Validate::isLoadedObject($currentState)) {
                                if ($currentState->id != self::getConfig('ORDER_STATUS')) {
                                    $Order->setCurrentState(self::getConfig('ORDER_STATUS'), $this->context->employee->id);
                                }
                            }
                        }
                    }

                    $filePath = $this->getLabelsShippingPathUri() . $DateTime->format('Y/m/d') . '/' . $ShipmentInfoType->getShipmentId() . '.pdf';

                    // If there's multiple labels and including returns/invoices is enabled, merge PDFs
                    if (count($files) > 1 && (self::getConfig('INCLUDE_RETURN_LABEL') || self::getConfig('INCLUDE_INVOICE'))) {
                        MergePdf::merge(
                            $files,
                            MergePdf::DESTINATION__DISK,
                            $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d') . '/' . $ShipmentInfoType->getShipmentId() . '.pdf'
                        );
                    }

                    // Output src path if ajax request
                    if (Tools::isSubmit('ajaxCreateLabel')) {
                        $response['src'] = $filePath;
                        die(json_encode($response));
                    }

                    // Delete any stored error messages
                    if (isset($values['id_order'])) {
                        $orderErrorArr = OrderError::getOrderErrorByOrderId($values['id_order']);
                        if ($orderErrorArr) {
                            $OrderError = new OrderError($orderErrorArr['id_order_error']);
                            $OrderError->delete();
                        }
                    }

                    $confirmation = $this->l('Successfully created shipment #') . $ShipmentInfoType->getShipmentId() . \CanadaPostPs\Tools::renderHtmlTag('br');
                    $this->context->controller->confirmations[] = $confirmation;
                    $this->addFlash('success', $confirmation);

                    if ($redirect && self::getConfig('OPEN_LABEL_ON_CREATION')) {
                        // Redirect to label
                        Tools::redirectAdmin($filePath);
                    } elseif ($redirect) {
                        // This will redirect on 1.7.7+
                        $this->redirectToRequestUri();

                        Tools::redirectAdmin($redirect);
                    }

                    return true;
                } catch (\Exception $e) {
                    $error = sprintf('Error creating shipment object: "%s"', Tools::formatException($e));
                }
            }
        } else {
            $error = sprintf(
                'Error creating shipment: "%s"',
                \CanadaPostPs\Tools::formatErrorMessage($Shipping->getErrorMessage())
            );
        }

        if ($error) {

            // append order ID
            if (isset($values['id_order'])) {
                $error = sprintf('Order ID %s: %s', $values['id_order'], $error);
            }

            $this->context->controller->errors[] = $error;
            $this->log($error);

            // Store error message
            if (isset($values['id_order'])) {
                $orderErrorArr = OrderError::getOrderErrorByOrderId($values['id_order']);
                if ($orderErrorArr) {
                    $OrderError = new OrderError($orderErrorArr['id_order_error']);
                } else {
                    $OrderError = new OrderError();
                    $OrderError->id_order = $values['id_order'];
                }
                $OrderError->errorMessage = $error;
                $OrderError->id_batch = $id_batch;
                $OrderError->save();
            }

            if (Tools::isSubmit('ajaxCreateLabel')) {
                $response['error'] = $error;
                die(json_encode($response));
            }

            $this->addFlash('error', $error);

            if ($redirect) {
                $this->redirectToRequestUri();
            }
        }
    }
    /*
     * @return bool
     * */
    public function processSubmitCreateReturnLabel($redirect = false, $id_order = null)
    {
        $AuthorizedReturn = $this->createReturnShipment();

        $error = false;
        $response = array();

        /* @var $AuthorizedReturnInfoType CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnInfoType */
        if ($AuthorizedReturn instanceof CanadaPostWs\AuthorizedReturn && $AuthorizedReturn->isSuccess()) {
            $AuthorizedReturnInfoType = $AuthorizedReturn->getResponse();
            if ($AuthorizedReturnInfoType instanceof CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnInfoType) {
                try {
                    $DeliverySpec = new \CanadaPostWs\Type\Shipment\DeliverySpecType();
                    $DeliverySpec->setServiceCode(Tools::getValue('service-code'));
                    $Returner = new \CanadaPostWs\Type\Shipment\DestinationType();
                    $Returner->setName(Tools::getValue('name'));
                    $ReturnerAddress = new \CanadaPostWs\Type\Shipment\DestinationAddressDetailsType();
                    $ReturnerAddress->setAddressLine1(Tools::getValue('address-line-1'));
                    $ReturnerAddress->setAddressLine2(Tools::getValue('address-line-2'));
                    $ReturnerAddress->setCity(Tools::getValue('city'));
                    $ReturnerAddress->setProvState(Tools::getValue('prov-state'));
                    $ReturnerAddress->setPostalZipCode(Tools::getValue('postal-zip-code'));
                    $Returner->setAddressDetails($ReturnerAddress);
                    $DeliverySpec->setDestination($Returner);

                    // Create Shipment obj in DB
                    $ReturnShipment = $this->createReturnShipmentObject($AuthorizedReturnInfoType, $DeliverySpec, $id_order);
                    $DateTime = new DateTime($ReturnShipment->date_add);

                    // Setup dir hierarchy (Y/m/d)
                    $this->makeLabelDirectoryForDate($DateTime, $this->getLabelsReturnsPathLocal());

                    $Shipping = new CanadaPostWs\Shipping($this->getApiParams());

                    // Get PDF label and store it
                    $Shipping->getArtifact(
                        $AuthorizedReturnInfoType->getReturnLabelLink()->getHref(),
                        $AuthorizedReturnInfoType->getTrackingPin(),
                        $this->getLabelsReturnsPathLocal() . $DateTime->format('Y/m/d')
                    );

                    $filePath = $this->getLabelsReturnsPathUri() . $DateTime->format('Y/m/d') . '/' . $AuthorizedReturnInfoType->getTrackingPin() . '.pdf';

                    // Output src path if ajax request
                    if (Tools::isSubmit('ajaxCreateLabel')) {
                        $response['src'] = $filePath;
                        die(json_encode($response));
                    }

                    if (self::getConfig('OPEN_LABEL_ON_CREATION')) {
                        // Redirect to label
                        Tools::redirectAdmin($filePath);
                    } elseif ($redirect) {
                        Tools::redirectAdmin($redirect);
                    }

                    $this->context->controller->confirmations[] = $this->l('Successfully created return shipment #') . $AuthorizedReturnInfoType->getTrackingPin();

                    return true;
                } catch (\Exception $e) {
                    $error = sprintf('Error creating return shipment object: "%s"', Tools::formatException($e));
                }
            }
        } else {
            $error = sprintf(
                'Error creating return shipment: "%s"',
                \CanadaPostPs\Tools::formatErrorMessage($AuthorizedReturn->getErrorMessage())
            );
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);

            if (Tools::isSubmit('ajaxCreateLabel')) {
                $response['error'] = $error;
                die(json_encode($response));
            }
        }
    }

    /**
     * @var $Object Shipment|ReturnShipment|Manifest
     * @var $invoice bool
     * @return bool
     * */
    public function getLabelForObject($Object, $invoice = false)
    {
        $Shipping = $this->isContract() ? new CanadaPostWs\Shipping($this->getApiParams()) : new CanadaPostWs\NcShipping($this->getApiParams());
        $DateTime = new DateTime($Object->date_add);
        $error = false;

        if ($Object instanceof Shipment) {
            $link = $invoice ? $Object->commercial_invoice_link : $Object->label_link;
            $fileName = $Object->shipment_id . ($invoice ? '_invoice' : '');
            $path = $this->getLabelsShippingPathLocal();
        } elseif ($Object instanceof ReturnShipment) {
            $link = $Object->return_label_link;
            $fileName = $Object->tracking_pin;
            $path = $this->getLabelsReturnsPathLocal();
        } elseif ($Object instanceof Manifest) {
            $link = $Object->label_link;
            $fileName = $Object->poNumber;
            $path = $this->getManifestsPathLocal();
        }

        try {
            // Get PDF label and store it
            $Shipping->getArtifact(
                $link,
                $fileName,
                $path . $DateTime->format('Y/m/d')
            );

            if ($Shipping && $Shipping->isSuccess()) {
                if ($Shipping->getResponse() instanceof CanadaPostWs\Type\Messages\MessagesType) {
                    $error = $this->l('Error retrieving label.');
                } else {
                    return true;
                }
            } else {
                $error = sprintf(
                    'Error getting PDF: "%s"',
                    \CanadaPostPs\Tools::formatErrorMessage($Shipping->getErrorMessage())
                );
            }
        } catch (\Exception $e) {
            $error = sprintf('Error getting PDF: "%s"', Tools::formatException($e));
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);
            return false;
        }
    }

    /**
     * @var Batch $Batch
     * */
    public function getBatchLabels($Batch)
    {
        $files = array();

        $shipments = $Batch->getShipmentsInBatch();

        $this->sortLabels($shipments);

        // Get file paths for each label
        foreach ($shipments as $shipment) {
            $DateTime = new DateTime($shipment['date_add']);
            $fileName  = $this->getLabelsShippingPathLocal() . $DateTime->format('Y/m/d') . '/' . $shipment['shipment_id'] . '.pdf';
            if (!Tools::file_exists_no_cache($fileName)) {
                $Shipment = new Shipment($shipment['id_shipment']);
                if ($this->getLabelForObject($Shipment)) {
                    $files[] = $fileName;
                } else {
                    return false;
                }
            } else {
                $files[] = $fileName;
            }
        }

        $DateTime = new DateTime($Batch->date_add);

        // Make dir for label
        $this->makeLabelDirectoryForDate($DateTime, $this->getBatchPathLocal());

        // Merge PDFs into one
        MergePdf::merge(
            $files,
            MergePdf::DESTINATION__DISK,
            $this->getBatchPathLocal() . $DateTime->format('Y/m/d') . '/' . $Batch->id . '.pdf'
        );
    }

    /**
     * @var $Shipment Shipment|ReturnShipment|Manifest
     * @var $pathLocal string
     * @var $pathUri string
     * @var $fileName string
     * */
    public function processSubmitPrint($Object, $pathLocal, $pathUri, $fileName, $invoice = false)
    {
        if (\Validate::isLoadedObject($Object)) {
            $redirect = false;
            $DateTime = new DateTime($Object->date_add);

            // Setup dir hierarchy (YEAR/MONTH/DAY)
            $this->makeLabelDirectoryForDate($DateTime, $pathLocal);

            // Check if label is stored already
            if (!Tools::file_exists_no_cache(
                $pathLocal . $DateTime->format('Y/m/d') . '/' . $fileName . '.pdf'
            )
            ) {
                if ($Object instanceof Shipment && ($Object->voided || $Object->transmitted)) {
                    $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_PRINT']);
                } elseif ($Object instanceof Batch) {
                    $this->getBatchLabels($Object);
                    $redirect = true;
                } else {
                    if ($this->getLabelForObject($Object, $invoice)) {
                        $redirect = true;
                    }
                }
            } else {
                $redirect = true;
            }

            if ($redirect) {
                // Redirect to label
                Tools::redirectAdmin(
                    $pathUri . $DateTime->format('Y/m/d') . '/' . $fileName . '.pdf'
                );
            }
        }
    }

    /**
     * @var string $obj
     * @var string $path
     * @var string $fileNameProperty Name of the obj property to use as the filename
     * */
    public function processSubmitBulkPrint($obj, $pathLocal, $fileNameProperty, $pathUri)
    {
        $DateTime = new DateTime();

        // Setup output dir
        $this->makeLabelDirectoryForDate($DateTime, $pathLocal);

        /* @var $obj Shipment|ReturnShipment|Batch */
        $fileNames = array();
        foreach (Tools::getValue($obj::$definition['table'].'Box') as $id) {
            $Object = new $obj($id);
            $ObjectDateTime = new DateTime($Object->date_add);
            $fileName = $pathLocal . $ObjectDateTime->format('Y/m/d') . '/' . $Object->{$fileNameProperty} . '.pdf';

            // If file doesn't exist, get label from API
            if (!Tools::file_exists_no_cache($fileName)) {
                if ($Object instanceof Shipment && ($Object->voided || $Object->transmitted)) {
                    $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_PRINT']);
                } elseif ($Object instanceof Batch) {
                    $this->getBatchLabels($Object);
                    $fileNames[] = $fileName;
                } else {
                    if ($this->getLabelForObject($Object)) {
                        $fileNames[] = $fileName;
                    }
                }
            } else {
                $fileNames[] = $fileName;
            }
        }

        if (!empty($fileNames)) {
            $filePath = $DateTime->format('Y/m/d') . '/bulk_' . $DateTime->format('Y-m-d_H:i:s') . '.pdf';
            MergePdf::merge(
                $fileNames,
                MergePdf::DESTINATION__DISK,
                $pathLocal . $filePath
            );
            if (Tools::file_exists_no_cache($pathLocal . $filePath)) {
                Tools::redirectAdmin($pathUri . $filePath);
            } else {
                $this->context->controller->errors[] = $this->l('Error while creating bulk PDF.');
            }
        } else {
            $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_BULK_PRINT']);
        }

        if (!empty($this->context->controller->errors)) {
            $this->addFlash('error', $this->context->controller->errors);
            $this->redirectToRequestUri();
        }
    }

    /**
     * @var $Shipment Shipment
     * @return bool
     * */
    public function voidShipment($Shipment)
    {
        $error = false;
        $Shipping = new \CanadaPostWs\Shipping($this->getApiParams());
        try {
            $Shipping->processVoid($Shipment->self_link);

            if ($Shipping && $Shipping->isSuccess()) {
                $Shipment->voided = 1;
                $Shipment->save();

                $confirmation = $this->l('Successfully voided shipment #').$Shipment->id;
                $this->context->controller->confirmations[] = $confirmation;
                $this->addFlash('success', $confirmation);

                return true;
            } else {
                $error = 'Error voided shipment: "' . Tools::formatErrorMessage($Shipping->getErrorMessage()) . '"';
            }
        } catch (\Exception $e) {
            $error = 'Error voiding shipment: "' . Tools::formatException($e) . '"';
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);
            return false;
        }
    }

    /**
     * @var $Shipment Shipment
     * @return bool
     * */
    public function refundShipment($Shipment)
    {
        $error = false;

        if ($this->isContract()) {
            $Shipping = new \CanadaPostWs\Shipping($this->getApiParams());
        } else {
            $Shipping = new \CanadaPostWs\NcShipping($this->getApiParams());
        }
        try {
            $Shipping->processRefund($Shipment->refund_link, self::getConfig('REFUND_EMAIL'));

            if ($Shipping && $Shipping->isSuccess()) {
                $Shipment->voided = 1;
                $Shipment->save();

                $confirmation = $this->l('Successfully refunded shipment #').$Shipment->id;
                $this->context->controller->confirmations[] = $confirmation;
                $this->addFlash('success', $confirmation);

                return true;
            } else {
                $error = 'Error refunding shipment: "' . Tools::formatErrorMessage($Shipping->getErrorMessage()) . '"';
            }
        } catch (\Exception $e) {
            $error = 'Error refunding shipment: "' . Tools::formatException($e) . '"';
        }

        if ($error) {
            $this->context->controller->errors[] = $error;
            $this->log($error);
            return false;
        }
    }

    public function processSubmitRefund($redirect = false)
    {
        $Shipment = new \CanadaPostPs\Shipment(Tools::getValue('id_shipment'));

        if ($Shipment->voided) {
            $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_REFUND']);
        } elseif (!$Shipment->refund_link) {
            $this->context->controller->errors[] = sprintf(
                $this->l(\CanadaPostPs\Tools::$error_messages['NO_REFUND_LINK']),
                $Shipment->id
            );
        } elseif (!self::getConfig('REFUND_EMAIL')) {
            $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['NO_REFUND_EMAIL']);
        } else {
            if ($this->refundShipment($Shipment)) {
                // Redirect to remove URL params
                if ($redirect) {
                    $this->redirectToRequestUri();
                    Tools::redirectAdmin($redirect);
                }
            }
        }

        if (!empty($this->context->controller->errors)) {
            $this->addFlash('error', $this->context->controller->errors);
            $this->redirectToRequestUri();
        }
    }

    public function processSubmitVoid($redirect = false)
    {
        $Shipment = new \CanadaPostPs\Shipment(Tools::getValue('id_shipment'));

        if ($Shipment->voided || $Shipment->transmitted) {
            $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_VOID']);
        } else {
            if ($this->voidShipment($Shipment)) {
                // Redirect to remove URL params
                if ($redirect) {
                    $this->redirectToRequestUri();
                    Tools::redirectAdmin($redirect);
                }
            }
        }

        if (!empty($this->context->controller->errors)) {
            $this->addFlash('error', $this->context->controller->errors);
            $this->redirectToRequestUri();
        }
    }

    public function processSubmitBulkVoid($redirect = false)
    {
        $error = false;
        foreach (Tools::getValue(\CanadaPostPs\Shipment::$definition['table'].'Box') as $id_shipment) {
            $Shipment = new \CanadaPostPs\Shipment($id_shipment);

            if ($Shipment->voided || $Shipment->transmitted) {
                $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_VOID']) . ' #'.$id_shipment;
                $error = true;
            } else {
                $this->voidShipment($Shipment);
            }
        }

        if (!$error) {
            // Redirect to remove URL params
            if ($redirect) {
                $this->redirectToRequestUri();
                Tools::redirectAdmin($redirect);
            }
        }

        if (!empty($this->context->controller->errors)) {
            $this->addFlash('error', $this->context->controller->errors);
            $this->redirectToRequestUri();
        }
    }

    public function processSubmitBulkRefund()
    {
        if (!self::getConfig('REFUND_EMAIL')) {
            $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['NO_REFUND_EMAIL']);
        } else {
            foreach (Tools::getValue(\CanadaPostPs\Shipment::$definition['table'] . 'Box') as $id_shipment) {
                $Shipment = new \CanadaPostPs\Shipment($id_shipment);

                if ($Shipment->voided) {
                    $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_REFUND']) . ' #' . $id_shipment;
                } elseif (!$Shipment->refund_link) {
                    $this->context->controller->errors[] = sprintf(
                        $this->l(\CanadaPostPs\Tools::$error_messages['NO_REFUND_LINK']),
                        $Shipment->id
                    );
                } else {
                    $this->refundShipment($Shipment);
                }
            }
        }

        if (!empty($this->context->controller->errors)) {
            $this->addFlash('error', $this->context->controller->errors);
            $this->redirectToRequestUri();
        }
    }

    /*
     * Store label form values for order in DB
     * */
    public function processSubmitSaveChanges()
    {
        $orderLabelSettingsArr = OrderLabelSettings::getOrderLabelSettingForOrderId(Tools::getValue('id_order'));
        $response = array();

        try {
            if ($orderLabelSettingsArr) {
                $OrderLabelSettings = new OrderLabelSettings($orderLabelSettingsArr['id_order_label_settings']);
            } else {
                $OrderLabelSettings           = new OrderLabelSettings();
                $OrderLabelSettings->id_order = Tools::getValue('id_order');
            }

            // Prepend prefixes to checkbox fields
            $checkboxes = array_merge(
                array_map(function ($option) {
                    return 'options_'.$option;
                }, array_keys(Method::$options)),
                array_map(function ($option) {
                    return 'notification_'.$option;
                }, Method::$notifications)
            );

            // Store all fields in DB
            foreach (Tools::getAllValues() as $postKey => $postVal) {
                // Change dash to underscore to match DB names
                $postKey = str_replace('-', '_', $postKey);
                foreach (OrderLabelSettings::$labelSettings as $labelSetting) {
                    if (property_exists($OrderLabelSettings->{$labelSetting}, $postKey)) {
                        $OrderLabelSettings->{$labelSetting}->{$postKey} = $postVal;
                    }
                }
            }

            // Remove checkbox values from DB if missing from POST request
            foreach ($checkboxes as $checkbox) {
                if (!Tools::getIsset($checkbox)) {
                    // Change dash to underscore to match DB names
                    $classField = str_replace('-', '_', $checkbox);
                    $OrderLabelSettings->options->{$classField} = '';
                }
            }

            // Add customs products to DB
            $customsProducts = array();
            foreach (Tools::getValue('item') as $key => $product) {
                $orderLabelCustomsProductArr = OrderLabelCustomsProduct::getOrderLabelCustomsProducts(array(
                    'id_order_label_settings' => $OrderLabelSettings->id,
                    'id_product' => $key
                ));
                if (count($orderLabelCustomsProductArr) > 0) {
                    $OrderLabelCustomsProduct = new OrderLabelCustomsProduct($orderLabelCustomsProductArr[0]['id_order_label_customs_product']);
                } else {
                    $OrderLabelCustomsProduct = new OrderLabelCustomsProduct();
                    $OrderLabelCustomsProduct->id_product = $key;
                }

                foreach (Method::$customsProductFields as $customsProductField) {
                    $classField = str_replace('-', '_', $customsProductField);
                    $OrderLabelCustomsProduct->{$classField} = $product[$customsProductField];
                }

                $customsProducts[] = $OrderLabelCustomsProduct;
            }

            // Save objects
            if ($OrderLabelSettings->save()) {
                foreach (OrderLabelSettings::$labelSettings as $labelSetting) {
                    $OrderLabelSettings->{$labelSetting}->id_order_label_settings = $OrderLabelSettings->id;
                    if ($OrderLabelSettings->{$labelSetting}->save()) {
                        $id                        = 'id_order_label_' . $labelSetting;
                        $OrderLabelSettings->{$id} = $OrderLabelSettings->{$labelSetting}->id;
                    }
                }
                /* @var $customsProduct OrderLabelCustomsProduct */
                foreach ($customsProducts as $customsProduct) {
                    $customsProduct->id_order_label_settings = $OrderLabelSettings->id;
                    $customsProduct->save();
                }
                $OrderLabelSettings->save();

                if (Tools::getIsset('ajaxSaveChanges')) {
                    $response['success'] = $this->l('Label settings saved, modified values will now be used instead of the order\'s default values.');
                    die(json_encode($response));
                }
            }
        } catch (\Exception $e) {
            $error = Tools::formatException($e);
            $this->log($error);
            $this->context->controller->errors[] = $error;

            if (Tools::getIsset('ajaxSaveChanges')) {
                $response['error'] = $error;
                die(json_encode($response));
            }
        }
    }

    public function processSubmitBulkCreateLabel()
    {
        $error = false;

        try {
            $Batch = new Batch();
            $Batch->save();

            $orderIds = Tools::getValue(\Order::$definition['table'] . 'Box');
            foreach ($orderIds as $id_order) {
                $Order = new \Order($id_order);

                if (\Validate::isLoadedObject($Order)) {

                    // Reset script time limit
                    set_time_limit(30);

                    $Forms            = new Forms();
                    $orderLabelValues = $Forms->getCreateLabelFormFieldValues($id_order, $Order->getProducts());

                    $this->processSubmitCreateLabel($orderLabelValues, false, $Batch->id);
                }

                // Sleep in microseconds
                if (
                    self::getConfig('LABEL_DELAY') &&
                    self::getConfig('LABEL_DELAY') > 0 &&
                    count($orderIds) > 60
                ) {
                    usleep((int)self::getConfig('LABEL_DELAY'));
                }
            }

            $shipments = $Batch->getShipmentsInBatch();

            // If no shipments in batch, delete batch
            if (empty($shipments)) {
                $Batch->delete();
            } else {
                // Merge all shipment labels into one batch PDF
                $this->getBatchLabels($Batch);

                $DateTime = new DateTime($Batch->date_add);
                $filePath = $this->getBatchPathUri() . $DateTime->format('Y/m/d') . '/' . $Batch->id . '.pdf';

                $orderErrors = OrderError::getOrderErrors(array('id_batch' => $Batch->id));

                if (!empty($orderErrors)) {
                    $error = sprintf(Tools::$error_messages['BATCH_ERRORS'], $Batch->id);
                } elseif (self::getConfig('OPEN_LABEL_ON_CREATION')) {
                    // Redirect to label
                    Tools::redirectAdmin($filePath);
                }

                $this->context->controller->confirmations[] = sprintf(
                    $this->l('Batch #%s created. You can print this batch of labels from the View Batches page.'),
                    $Batch->id
                );
            }
        } catch (\Exception $e) {
            $error = Tools::formatException($e);
        }

        if ($error) {
            $this->log($error);
            $this->context->controller->errors[] = $error;
        }
    }

    /**
     * Sort array of shipments
     *
     * @var array $shipments
     * @return array
     * */
    public function sortLabels(&$shipments)
    {
        usort($shipments, function ($a, $b) {
            if (self::getConfig('LABELS_ORDER_BY') == 'order_date_add') {
                $aOrder = new \Order($a['id_order']);
                $bOrder = new \Order($b['id_order']);

                if (self::getConfig('LABELS_ORDER_WAY') == 'ASC') {
                    return strtotime($aOrder->date_add) - strtotime($bOrder->date_add);
                } else {
                    return strtotime($bOrder->date_add) - strtotime($aOrder->date_add);
                }
            } elseif (self::getConfig('LABELS_ORDER_BY') == 'id_order') {
                if (self::getConfig('LABELS_ORDER_WAY') == 'ASC') {
                    return $a['id_order'] - $b['id_order'];
                } else {
                    return $b['id_order'] - $a['id_order'];
                }
            } elseif (self::getConfig('LABELS_ORDER_BY') == 'shipment_date_add') {
                if (self::getConfig('LABELS_ORDER_WAY') == 'ASC') {
                    return strtotime($a['date_add']) - strtotime($b['date_add']);
                } else {
                    return strtotime($b['date_add']) - strtotime($a['date_add']);
                }
            }
        });

        return $shipments;
    }

    /**
     * @var array $batches
     * @return array
     * */
    public function mergeBatchShipments($batches)
    {
        $shipments = array();
        foreach ($batches as $batch) {
            $BatchObj = new Batch($batch['id_batch']);
            $shipments = array_merge($shipments, $BatchObj->getShipmentsInBatch());
        }
        return $shipments;
    }

    /**
     * Retrieve list of shipments from Canada Post and add any missing shipments in DB
     *
     * @var int|bool $id_group
     * @var DateTime|bool $from
     * @var DateTime|bool $to
     * */
    public function syncShipments($id_group = false, $from = false, $to = false)
    {
        try {
            if ($this->isContract()) {
                $Shipping = new CanadaPostWs\Shipping($this->getApiParams());

                if ($id_group) {
                    // Retrieve list of shipment links from Canada Post for group
                    $Group = new Group($id_group);
                    $Shipping->getShipments($Group->name);
                }  else {
                    $Shipping->getShipments(null, $from, $to);
                }
            } else {
                $Shipping = new CanadaPostWs\NcShipping($this->getApiParams());

                // Retrieve list of shipment links from Canada Post for date range
                $Shipping->getShipments($from, $to);
            }

            if ($Shipping && $Shipping->isSuccess()) {
                $missingShipments = array();

                /* @var $ShipmentsType CanadaPostWs\Type\Shipment\ShipmentsType|CanadaPostWs\Type\NcShipment\NonContractShipmentsType */
                $ShipmentsType = $Shipping->getResponse();
                /* @var $LinkType CanadaPostWs\Type\Common\LinkType */
                foreach ($ShipmentsType->getLinks() as $LinkType) {
                    // Check if the link is in the DB already
                    $shipments = Shipment::getShipments(array('self_link' => $LinkType->getHref()));
                    if (empty($shipments)) {
                        $missingShipments[] = $LinkType;
                    }
                }

                // Add missing shipments to shipment DB
                if (!empty($missingShipments)) {

                    // if there are more than 30 shipments, we'll add a 2 second delay to avoid
                    // hitting the Canada Post API limit of 60 calls per minute since we're
                    // making 2 calls per shipment (getShipment and getShipmentDetails)
                    $throttle = count($missingShipments) > 30;

                    $syncedShipments = array();

                    /* @var $missingShipment CanadaPostWs\Type\Common\LinkType */
                    foreach ($missingShipments as $missingShipment) {
                        // Get the shipment ID from the end of the shipment Link
                        // e.g. https://soa-gw.canadapost.ca/rs/0001234567-0001234567/0001234567/shipment/328791549435236813
                        $shipmentId = preg_replace('/.+[\/](\d+$)/', '$1', $missingShipment->getHref());

                        // Get shipment links
                        $Shipping->getShipment($missingShipment->getHref());

                        if ($Shipping && $Shipping->isSuccess()) {

                            /* @var $ShipmentInfoType CanadaPostWs\Type\Shipment\ShipmentInfoType|CanadaPostWs\Type\NcShipment\NonContractShipmentInfoType */
                            $ShipmentInfoType = $Shipping->getResponse();

                            // Get the shipment details
                            $Shipping->getShipmentDetails($ShipmentInfoType->getDetailsLink()->getHref());

                            if ($Shipping && $Shipping->isSuccess()) {

                                /* @var $ShipmentDetailsInfoType CanadaPostWs\Type\Shipment\ShipmentInfoType|CanadaPostWs\Type\NcShipment\NonContractShipmentInfoType */
                                $ShipmentDetailsInfoType = $Shipping->getResponse();
                                $ShipmentInfoType->setDeliverySpec($ShipmentDetailsInfoType->getDeliverySpec());

                                // Create object in DB
                                $this->createShipmentObject($ShipmentInfoType, false, $id_group);

                                $syncedShipments[] = $ShipmentInfoType->getShipmentId();
                            } else {
                                if ($Shipping === false) {
                                    $this->context->controller->errors[] = sprintf($this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_SHIPMENT_DETAILS']), $ShipmentInfoType->getShipmentId());
                                } elseif ($Shipping->getErrorMessage()) {
                                    $this->context->controller->errors[] = $Shipping->getErrorMessage();
                                }
                            }
                        } else {
                            if ($Shipping === false) {
                                $this->context->controller->errors[] = sprintf($this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_SHIPMENT_DETAILS']), $shipmentId);
                            } elseif ($Shipping->getErrorMessage()) {
                                $this->context->controller->errors[] = $Shipping->getErrorMessage();
                            }
                        }

                        // throttle to avoid API limit
                        if ($throttle) {
                            sleep(2);
                        }
                    }

                    if (!empty($syncedShipments)) {
                        $this->context->controller->confirmations[] = sprintf($this->l('Successfully synced %s shipments.'), count($syncedShipments));
                    }
                } else {
                    $this->context->controller->confirmations[] = $this->l('Shipments are identical between the module and Canada Post, no sync was performed.');
                }
            } else {
                if ($Shipping === false) {
                    $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_SHIPMENTS']);
                } elseif ($Shipping->getErrorMessage()) {
                    $this->context->controller->errors[] = $Shipping->getErrorMessage();
                }
            }
        } catch (\Exception $e) {
            $this->context->controller->errors[] = Tools::formatException($e);
        }
    }

    /**
     * Retrieve list of manifests from Canada Post and add any missing in DB
     * @var DateTime $start
     * @var DateTime $end
     * */
    public function syncManifests($start, $end)
    {
        $Shipping = new \CanadaPostWs\Shipping($this->getApiParams());
        try {
            // Retrieve list of shipment links from Canada Post for group
            $Shipping->getManifests($start, $end);

            if ($Shipping && $Shipping->isSuccess()) {
                $missingManifests = array();

                /* @var $ManifestsType CanadaPostWs\Type\Manifest\ManifestsType */
                $ManifestsType = $Shipping->getResponse();
                /* @var $LinkType CanadaPostWs\Type\Common\LinkType */
                foreach ($ManifestsType->getLinks() as $LinkType) {
                    // Check if the link is in the DB already
                    $manifests = Manifest::getManifests(
                        array('self_link' => $LinkType->getHref())
                    );

                    if (empty($manifests)) {
                        $missingManifests[] = $LinkType;
                    }
                }

                // Add missing shipments to shipment DB
                if (!empty($missingManifests)) {

                    // if there are more than 30 shipments, we'll add a 2 second delay to avoid
                    // hitting the Canada Post API limit of 60 calls per minute since we're
                    // making 2 calls per shipment (getManifest and getManifestDetails)
                    $throttle = count($missingManifests) > 30;

                    $syncedManifests = array();

                    /* @var $missingManifest CanadaPostWs\Type\Common\LinkType */
                    foreach ($missingManifests as $missingManifest) {
                        // Get the shipment ID from the end of the shipment Link
                        // e.g. https://soa-gw.canadapost.ca/rs/0001234567-0001234567/0001234567/shipment/328791549435236813
                        $manifestId = preg_replace('/.+[\/](\d+$)/', '$1', $missingManifest->getHref());

                        // Get shipment links
                        $Shipping->getManifest($missingManifest->getHref());

                        if ($Shipping && $Shipping->isSuccess()) {

                            /* @var $ManifestType CanadaPostWs\Type\Manifest\ManifestType */
                            $ManifestType = $Shipping->getResponse();

                            // Get the shipment details
                            $Shipping->getManifestDetails($ManifestType->getDetailsLink()->getHref());

                            if ($Shipping && $Shipping->isSuccess()) {

                                /* @var $ManifestDetailsType CanadaPostWs\Type\Manifest\ManifestDetailsType */
                                $ManifestDetailsType = $Shipping->getResponse();

                                // Create object in DB
                                $this->createManifestObject($ManifestType, $ManifestDetailsType);

                                $syncedManifests[] = $ManifestType->getPoNumber();
                            } else {
                                if ($Shipping === false) {
                                    $this->context->controller->errors[] = sprintf($this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_MANIFEST_DETAILS']), $ManifestType->getPoNumber());
                                } elseif ($Shipping->getErrorMessage()) {
                                    $this->context->controller->errors[] = $Shipping->getErrorMessage();
                                }
                            }
                        } else {
                            if ($Shipping === false) {
                                $this->context->controller->errors[] = sprintf($this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_MANIFEST_DETAILS']), $manifestId);
                            } elseif ($Shipping->getErrorMessage()) {
                                $this->context->controller->errors[] = $Shipping->getErrorMessage();
                            }
                        }

                        // throttle to avoid API limit
                        if ($throttle) {
                            sleep(2);
                        }
                    }

                    if (!empty($syncedManifests)) {
                        $this->context->controller->confirmations[] = sprintf($this->l('Successfully synced %s manifests.'), count($syncedManifests));
                    }
                } else {
                    $this->context->controller->confirmations[] = $this->l('Manifests are identical between the module and Canada Post, no sync was performed.');
                }
            } else {
                if ($Shipping === false) {
                    $this->context->controller->errors[] = $this->l(\CanadaPostPs\Tools::$error_messages['CANNOT_GET_MANIFESTS']);
                } elseif ($Shipping->getErrorMessage()) {
                    $this->context->controller->errors[] = $Shipping->getErrorMessage();
                }
            }
        } catch (\Exception $e) {
            $this->context->controller->errors[] = Tools::formatException($e);
        }
    }
}
