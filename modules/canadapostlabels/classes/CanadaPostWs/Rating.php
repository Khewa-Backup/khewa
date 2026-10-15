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
use CanadaPostWs\Type\Rating\DimensionalRestrictionType;
use CanadaPostWs\Type\Rating\DimensionType;
use CanadaPostWs\Type\Rating\NumberRangeType;
use CanadaPostWs\Type\Rating\OptionInfoType;
use CanadaPostWs\Type\Rating\OptionsType;
use CanadaPostWs\Type\Rating\OptionType;
use CanadaPostWs\Type\Rating\QuoteType;
use CanadaPostWs\Type\Rating\RatingType;
use CanadaPostWs\Type\Rating\RatingInfoType;
use CanadaPostWs\Type\Rating\RestrictionsType;
use CanadaPostWs\Type\Rating\ServiceInfoType;
use CanadaPostWs\Type\Rating\WeightRestrictionType;
use SimpleXMLElement;

class Rating extends WebService
{
    const API_VERSION = '3';

    /**
     * WebService constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->requestUrl .= '/rs';
    }

    /**
     * @param RatingType $rating
     * @return bool|MessagesType|RatingInfoType
     */
    public function getRate(RatingType $rating)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v'.self::API_VERSION.'"/>');

        $xml->addChild('customer-number', $this->options['api_customer_number']);
        if (null !== $rating->getDeliverySpec()->getSettlementInfo()->getContractId()) {
            $xml->addChild('contract-id', $rating->getDeliverySpec()->getSettlementInfo()->getContractId());
        }

        $xmlServices = $xml->addChild('services');
        foreach ($rating->getDeliverySpec()->getServiceCodes() as $serviceCode) {
            $xmlServices->addChild('service-code', $serviceCode);
        }

        $xml->addchild('origin-postal-code', $rating->getDeliverySpec()->getSender()->getAddressDetails()->getPostalZipCode());

        $xmlDeliverySpecDestination = $xml->addChild('destination');

        switch ($rating->getDeliverySpec()->getDestination()->getAddressDetails()->getCountryCode()) {
            case 'CA':
                $xmlDeliverySpecDestinationType = $xmlDeliverySpecDestination->addChild('domestic');
                $xmlDeliverySpecDestinationType->addChild('postal-code', $rating->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode());
                break;
            case 'US':
                $xmlDeliverySpecDestinationType = $xmlDeliverySpecDestination->addChild('united-states');
                $xmlDeliverySpecDestinationType->addChild('zip-code', $rating->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode());
                break;
            default:
                $xmlDeliverySpecDestinationType = $xmlDeliverySpecDestination->addChild('international');
                $xmlDeliverySpecDestinationType->addChild('country-code', $rating->getDeliverySpec()->getDestination()->getAddressDetails()->getCountryCode());
                break;
        }

        $OptionsList = $rating->getDeliverySpec()->getOptions();

        if ($OptionsList) {
            $Options = $OptionsList->getOptions();

            if ($Options) {
                $xmlDeliverySpecOptions = $xml->addChild('options');

                foreach ($Options as $Option) {
                    $xmlDeliverySpecOptionsOption = $xmlDeliverySpecOptions->addChild('option');
                    $xmlDeliverySpecOptionsOption->addChild('option-code', $Option->getOptionCode());

                    if (null !== $Option->getOptionAmount()) {
                        $xmlDeliverySpecOptionsOption->addChild('option-amount', number_format($Option->getOptionAmount(), 2, '.', ''));
                    }

                    if (null !== $Option->isOptionQualifier1()) {
                        $xmlDeliverySpecOptionsOption->addChild('option-qualifier-1', ((int)$Option->isOptionQualifier1() ? 'true' : 'false'));
                    }

                    if (null !== $Option->getOptionQualifier2()) {
                        $xmlDeliverySpecOptionsOption->addChild('option-amount', $Option->getOptionQualifier2());
                    }
                }
            }
        }

        $xmlDeliverySpecParcelCharacteristics = $xml->addChild('parcel-characteristics');
        $xmlDeliverySpecParcelCharacteristics->addChild('weight', $rating->getDeliverySpec()->getParcelCharacteristics()->getWeight());

        $xmlDeliverySpecParcelCharacteristicsDimensions = $xmlDeliverySpecParcelCharacteristics->addChild('dimensions');
        $xmlDeliverySpecParcelCharacteristicsDimensions->addChild('length', $rating->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getLength());
        $xmlDeliverySpecParcelCharacteristicsDimensions->addChild('width', $rating->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getWidth());
        $xmlDeliverySpecParcelCharacteristicsDimensions->addChild('height', $rating->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getHeight());

        $request = $xml->asXML();

        try {
            $response = $this->processRequest(array(
                'request_url' => '/ship/price',
                'headers'     => array(
                    'Content-Type: application/vnd.cpc.ship.rate-v' . self::API_VERSION . '+xml',
                    'Accept: application/vnd.cpc.ship.rate-v' . self::API_VERSION . '+xml',
                ),
                'request'     => $request,
            ));
        } catch (\Exception $e) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'price-quotes':
                $RatingInfoType = new RatingInfoType();

                foreach ($responseXML->{'price-quote'} as $quote) {
                    $QuoteType = new QuoteType();
                    $QuoteType->setServiceCode((string)$quote->{'service-code'});
                    $QuoteType->setPriceTaxIncl((string)$quote->{'price-details'}->{'due'});

                    $priceTaxExcl = (float)$quote->{'price-details'}->{'due'};
                    if (!empty($quote->{'price-details'}->{'taxes'}->{'gst'})) {
                        $priceTaxExcl -= (float)$quote->{'price-details'}->{'taxes'}->{'gst'};
                    }
                    if (!empty($quote->{'price-details'}->{'taxes'}->{'pst'})) {
                        $priceTaxExcl -= (float)$quote->{'price-details'}->{'taxes'}->{'pst'};
                    }
                    if (!empty($quote->{'price-details'}->{'taxes'}->{'hst'})) {
                        $priceTaxExcl -= (float)$quote->{'price-details'}->{'taxes'}->{'hst'};
                    }
                    $QuoteType->setPriceTaxExcl((float)$priceTaxExcl);
                    $QuoteType->setDeliveryDate((string)$quote->{'service-standard'}->{'expected-delivery-date'});
                    $QuoteType->setTransitTime((string)$quote->{'service-standard'}->{'expected-transit-time'});

                    $RatingInfoType->addQuote($QuoteType);
                }

                $this->setResponse($RatingInfoType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }

    /*
     * Return details about a service
     *
     * @return bool|MessagesType|ServiceInfoType
     * */
    public function getService($serviceCode, $countryCode)
    {
        try {
            $response = $this->processRequest(array(
                'request_url' => '/ship/service/'.$serviceCode.'?country='.$countryCode,
                'headers'     => array(
                    'Accept: application/vnd.cpc.ship.rate-v' . self::API_VERSION . '+xml',
                )
            ));
        } catch (\Exception $e) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'service':
                $ServiceInfoType = new ServiceInfoType();
                $ServiceInfoType->setServiceCode((string)$responseXML->{'service-code'});
                $ServiceInfoType->setServiceName((string)$responseXML->{'service-name'});
                if ($responseXML->options) {
                    $Options = new OptionsType();
                    foreach ($responseXML->options->option as $optionXml) {
                        $OptionInfoType = new OptionInfoType();
                        $OptionInfoType->setOptionCode((string)$optionXml->{'option-code'});
                        if ((string)$optionXml->{'option-amount'}) {
                            $OptionInfoType->setOptionAmount((float)$optionXml->{'option-amount'});
                        }
                        $OptionInfoType->setMandatory((string)$optionXml->{'mandatory'});
                        $OptionInfoType->setQualifierRequired((string)$optionXml->{'qualifier-required'});
                        $OptionInfoType->setQualifierMax((string)$optionXml->{'qualifier-max'});
                        $Options->addOption($OptionInfoType);
                    }
                    $ServiceInfoType->setOptions($Options);
                }

                if ($responseXML->restrictions) {
                    $RestrictionsType = new RestrictionsType();

                    $WeightNumberRangeType = new NumberRangeType();
                    $WeightNumberRangeType->setMin((float)$responseXML->restrictions->{'weight-restriction'}['min']);
                    $WeightNumberRangeType->setMax((float)$responseXML->restrictions->{'weight-restriction'}['max']);
                    $RestrictionsType->setWeightRestriction($WeightNumberRangeType);

                    $DimensionalRestrictionsType = new DimensionalRestrictionType();
                    $dimensions = array('length', 'width', 'height');
                    foreach ($dimensions as $dimension) {
                        $DimensionNumberRangeType = new NumberRangeType();
                        $DimensionNumberRangeType->setMin((float)$responseXML->restrictions->{'dimensional-restrictions'}->{$dimension}['min']);
                        $DimensionNumberRangeType->setMax((float)$responseXML->restrictions->{'dimensional-restrictions'}->{$dimension}['max']);

                        $dynamicMethod = 'set'.\Tools::ucfirst($dimension);
                        $DimensionalRestrictionsType->$dynamicMethod($DimensionNumberRangeType);
                    }

                    $DimensionalRestrictionsType->setLengthHeightWidthSumMax((float)$responseXML->restrictions->{'dimensional-restrictions'}->{'length-height-width-sum-max'});
                    $DimensionalRestrictionsType->setLengthPlusGirthMax((float)$responseXML->restrictions->{'dimensional-restrictions'}->{'length-plus-girth-max'});
                    $DimensionalRestrictionsType->setOversizeLimit((float)$responseXML->restrictions->{'dimensional-restrictions'}->{'oversize-limit'});
                    $RestrictionsType->setDimensionalRestrictions($DimensionalRestrictionsType);

                    $RestrictionsType->setDensityFactor((float)$responseXML->restrictions->{'density-factor'});
                    $RestrictionsType->setCanShipInMailingTube((string)$responseXML->restrictions->{'can-ship-in-mailing-tube'});
                    $RestrictionsType->setCanShipUnpackaged((string)$responseXML->restrictions->{'can-ship-unpackaged'});
                    $RestrictionsType->setAllowedAsReturnService((string)$responseXML->restrictions->{'allowed-as-return-service'});
                    $ServiceInfoType->setRestrictions($RestrictionsType);
                }
                $this->setResponse($ServiceInfoType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }
}
