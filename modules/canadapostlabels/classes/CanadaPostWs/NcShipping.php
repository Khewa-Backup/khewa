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

use CanadaPostWs\Type\Common\LinkType;
use CanadaPostWs\Type\Common\ApplicationPdfType;
use CanadaPostWs\Type\Messages\MessagesType;
use CanadaPostWs\Type\NcShipment\NonContractShipmentInfoType;
use CanadaPostWs\Type\NcShipment\DestinationAddressDetailsType;
use CanadaPostWs\Type\NcShipment\DestinationType;
use CanadaPostWs\Type\NcShipment\NonContractShipmentType;
use CanadaPostWs\Type\NcShipment\NonContractShipmentsType;
use CanadaPostWs\Type\NcShipment\ParcelCharacteristicsType;
use CanadaPostWs\Type\NcShipment\DimensionType;
use CanadaPostWs\Type\NcShipment\OptionsType;
use CanadaPostWs\Type\NcShipment\OptionType;
use CanadaPostWs\Type\NcShipment\DeliverySpecType;
use CanadaPostWs\Type\NcShipment\PreferencesType;
use CanadaPostWs\Type\NcShipment\ReferencesType;
use CanadaPostWs\Type\NcShipment\SenderType;
use CanadaPostWs\Type\NcShipment\DomesticAddressDetailsType;
use CanadaPostWs\Type\NcShipment\NonContractShipmentRefundInfoType;
use SimpleXMLElement;

class NcShipping extends WebService
{
    const API_VERSION = '4';

    /**
     * WebService constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $mailedBy = $this->options['api_customer_number'];

        $this->requestUrl .= '/rs/'.$mailedBy.(array_key_exists('platform_id', $this->options) ? '-'.$this->options['platform_id'] : '');
    }

    /**
     * @param NonContractShipmentType $NcShipment
     *
     * @return bool|MessagesType|NonContractShipmentInfoType
     */
    public function createNcShipment(NonContractShipmentType $NcShipment)
    {
        $XmlNonContractShipment = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><non-contract-shipment xmlns="http://www.canadapost.ca/ws/ncshipment-v'.self::API_VERSION.'"/>');
        $XmlNonContractShipment->addChild('requested-shipping-point', $NcShipment->getRequestedShippingPoint());

        $XmlNonContractShipmentDeliverySpec = $XmlNonContractShipment->addChild('delivery-spec');
        $XmlNonContractShipmentDeliverySpec->addChild('service-code', $NcShipment->getDeliverySpec()->getServiceCode());

        $XmlNonContractShipmentDeliverySpecSender = $XmlNonContractShipmentDeliverySpec->addchild('sender');

        if (null !== $NcShipment->getDeliverySpec()->getSender()->getName()) {
            $XmlNonContractShipmentDeliverySpecSender->addChild(
                'name',
                $NcShipment->getDeliverySpec()->getSender()->getName()
            );
        }
        $XmlNonContractShipmentDeliverySpecSender->addChild('company', $NcShipment->getDeliverySpec()->getSender()->getCompany());
        $XmlNonContractShipmentDeliverySpecSender->addChild('contact-phone', $NcShipment->getDeliverySpec()->getSender()->getContactPhone());

        $XmlNonContractShipmentDeliverySpecSenderAddressDetails = $XmlNonContractShipmentDeliverySpecSender->addChild('address-details');
        $XmlNonContractShipmentDeliverySpecSenderAddressDetails->addChild('address-line-1', $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine1());

        if (null !== $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine2()) {
            $XmlNonContractShipmentDeliverySpecSenderAddressDetails->addChild('address-line-2', $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine2());
        }
        $XmlNonContractShipmentDeliverySpecSenderAddressDetails->addChild('city', $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getCity());
        $XmlNonContractShipmentDeliverySpecSenderAddressDetails->addChild('prov-state', $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getProvState());

        if (null !== $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getPostalZipCode()) {
            $XmlNonContractShipmentDeliverySpecSenderAddressDetails->addChild(
                'postal-zip-code',
                $NcShipment->getDeliverySpec()->getSender()->getAddressDetails()->getPostalZipCode()
            );
        }

        $XmlNonContractShipmentDeliverySpecDestination = $XmlNonContractShipmentDeliverySpec->addChild('destination');

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getName()) {
            $XmlNonContractShipmentDeliverySpecDestination->addChild(
                'name',
                $NcShipment->getDeliverySpec()->getDestination()->getName()
            );
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getCompany()) {
            $XmlNonContractShipmentDeliverySpecDestination->addChild(
                'company',
                $NcShipment->getDeliverySpec()->getDestination()->getCompany()
            );
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAdditionalAddressInfo()) {
            $XmlNonContractShipmentDeliverySpecDestination->addChild('additional-address-info', $NcShipment->getDeliverySpec()->getDestination()->getAdditionalAddressInfo());
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getClientVoiceNumber()) {
            $XmlNonContractShipmentDeliverySpecDestination->addChild('client-voice-number', $NcShipment->getDeliverySpec()->getDestination()->getClientVoiceNumber());
        }

        $XmlNonContractShipmentDeliverySpecDestinationAddressDetails = $XmlNonContractShipmentDeliverySpecDestination->addChild('address-details');

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine1()) {
            $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild(
                'address-line-1',
                $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine1()
            );
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine2()) {
            $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild('address-line-2', $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine2());
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCity()) {
            $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild(
                'city',
                $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCity()
            );
        }

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getProvState()) {
            $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild(
                'prov-state',
                $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getProvState()
            );
        }
        $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild('country-code', $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCountryCode());

        if (null !== $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode()) {
            $XmlNonContractShipmentDeliverySpecDestinationAddressDetails->addChild(
                'postal-zip-code',
                $NcShipment->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode()
            );
        }

        $OptionsList = $NcShipment->getDeliverySpec()->getOptions();

        if ($OptionsList) {
            $Options = $OptionsList->getOptions();

            if ($Options) {
                $XmlNonContractShipmentDeliverySpecOptions = $XmlNonContractShipmentDeliverySpec->addChild('options');

                foreach ($Options as $Option) {
                    $XmlNonContractShipmentDeliverySpecOptionsOption = $XmlNonContractShipmentDeliverySpecOptions->addChild('option');
                    $XmlNonContractShipmentDeliverySpecOptionsOption->addChild('option-code', $Option->getOptionCode());

                    if (null !== $Option->getOptionAmount()) {
                        $XmlNonContractShipmentDeliverySpecOptionsOption->addChild('option-amount', number_format($Option->getOptionAmount(), 2, '.', ''));
                    }

                    if (null !== $Option->isOptionQualifier1()) {
                        $XmlNonContractShipmentDeliverySpecOptionsOption->addChild('option-qualifier-1', ((int)$Option->isOptionQualifier1() ? 'true' : 'false'));
                    }

                    if (null !== $Option->getOptionQualifier2()) {
                        $XmlNonContractShipmentDeliverySpecOptionsOption->addChild('option-amount', $Option->getOptionQualifier2());
                    }
                }
            }
        }

        $XmlNonContractShipmentDeliverySpecParcelCharacteristics = $XmlNonContractShipmentDeliverySpec->addChild('parcel-characteristics');
        $XmlNonContractShipmentDeliverySpecParcelCharacteristics->addChild('weight', $NcShipment->getDeliverySpec()->getParcelCharacteristics()->getWeight());


        if (null !== $NcShipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()) {
            $XmlNonContractShipmentDeliverySpecParcelCharacteristicsDimensions = $XmlNonContractShipmentDeliverySpecParcelCharacteristics->addChild('dimensions');
            $XmlNonContractShipmentDeliverySpecParcelCharacteristicsDimensions->addChild(
                'length',
                $NcShipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getLength()
            );
            $XmlNonContractShipmentDeliverySpecParcelCharacteristicsDimensions->addChild(
                'width',
                $NcShipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getWidth()
            );
            $XmlNonContractShipmentDeliverySpecParcelCharacteristicsDimensions->addChild(
                'height',
                $NcShipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getHeight()
            );
        }

        $XmlNonContractShipmentDeliverySpecPreferences = $XmlNonContractShipmentDeliverySpec->addChild('preferences');
        $XmlNonContractShipmentDeliverySpecPreferences->addChild('show-packing-instructions', ((int)$NcShipment->getDeliverySpec()->getPreferences()->isShowPackingInstructions() ? 'true' : 'false'));

        if (null !== $NcShipment->getDeliverySpec()->getPreferences()->isShowPostageRate()) {
            $XmlNonContractShipmentDeliverySpecPreferences->addChild(
                'show-postage-rate',
                ((int)$NcShipment->getDeliverySpec()->getPreferences()->isShowPostageRate() ? 'true' : 'false')
            );
        }

        if (null !== $NcShipment->getDeliverySpec()->getPreferences()->isShowInsuredValue()) {
            $XmlNonContractShipmentDeliverySpecPreferences->addChild(
                'show-insured-value',
                ((int)$NcShipment->getDeliverySpec()->getPreferences()->isShowInsuredValue() ? 'true' : 'false')
            );
        }


        if (null !== $NcShipment->getDeliverySpec()->getReferences()) {
            $XmlNonContractShipmentDeliverySpecReferences = $XmlNonContractShipmentDeliverySpec->addChild('references');

            if (null !== $NcShipment->getDeliverySpec()->getReferences()->getCostCentre()) {
                $XmlNonContractShipmentDeliverySpecReferences->addChild(
                    'cost-centre',
                    $NcShipment->getDeliverySpec()->getReferences()->getCostCentre()
                );
            }

            if (null !== $NcShipment->getDeliverySpec()->getReferences()->getCustomerRef1()) {
                $XmlNonContractShipmentDeliverySpecReferences->addChild(
                    'customer-ref-1',
                    $NcShipment->getDeliverySpec()->getReferences()->getCustomerRef1()
                );
            }

            if (null !== $NcShipment->getDeliverySpec()->getReferences()->getCustomerRef2()) {
                $XmlNonContractShipmentDeliverySpecReferences->addChild(
                    'customer-ref-2',
                    $NcShipment->getDeliverySpec()->getReferences()->getCustomerRef2()
                );
            }
        }

        if (null !== $NcShipment->getDeliverySpec()->getCustoms()) {
            $XmlNonContractShipmentDeliverySpecCustoms = $XmlNonContractShipmentDeliverySpec->addChild('customs');
            $XmlNonContractShipmentDeliverySpecCustoms->addChild('currency', $NcShipment->getDeliverySpec()->getCustoms()->getCurrency());

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getConversionFromCad()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('conversion-from-cad', $NcShipment->getDeliverySpec()->getCustoms()->getConversionFromCad());
            }

            $XmlNonContractShipmentDeliverySpecCustoms->addChild('reason-for-export', $NcShipment->getDeliverySpec()->getCustoms()->getReasonForExport());

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getOtherReason()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('other-reason', $NcShipment->getDeliverySpec()->getCustoms()->getOtherReason());
            }

            $SkuList = $NcShipment->getDeliverySpec()->getCustoms()->getSkuList();

            if (null !== $SkuList) {
                $XmlNonContractShipmentDeliverySpecCustomsSkuList = $XmlNonContractShipmentDeliverySpecCustoms->addChild('sku-list');

                if ($SkuList->getItems()) {
                    foreach ($SkuList->getItems() as $Sku) {
                        $XmlNonContractShipmentDeliverySpecCustomsSkuListItem = $XmlNonContractShipmentDeliverySpecCustomsSkuList->addChild('item');
                        $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('customs-number-of-units', $Sku->getCustomsNumberOfUnits());
                        $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('customs-description', $Sku->getCustomsDescription());

                        if (null != $Sku->getSku()) {
                            $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('sku', $Sku->getSku());
                        }

                        if (null != $Sku->getHsTariffCode()) {
                            $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('hs-tariff-code', $Sku->getHsTariffCode());
                        }

                        $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('unit-weight', $Sku->getUnitWeight());
                        $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('customs-value-per-unit', $Sku->getCustomsValuePerUnit());

                        if (null != $Sku->getCustomsUnitOfMeasure()) {
                            $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('customs-unit-of-measure', $Sku->getCustomsUnitOfMeasure());
                        }

                        if (null != $Sku->getCountryOfOrigin()) {
                            $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('country-of-origin', $Sku->getCountryOfOrigin());
                        }

                        if (null != $Sku->getProvinceOfOrigin()) {
                            $XmlNonContractShipmentDeliverySpecCustomsSkuListItem->addChild('province-of-origin', $Sku->getProvinceOfOrigin());
                        }
                    }
                }
            }

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getDutiesAndTaxesPrepaid()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('duties-and-taxes-prepaid', $NcShipment->getDeliverySpec()->getCustoms()->getDutiesAndTaxesPrepaid());
            }

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getCertificateNumber()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('certificate-number', $NcShipment->getDeliverySpec()->getCustoms()->getCertificateNumber());
            }

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getLicenceNumber()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('licence-number', $NcShipment->getDeliverySpec()->getCustoms()->getLicenceNumber());
            }

            if (null !== $NcShipment->getDeliverySpec()->getCustoms()->getInvoiceNumber()) {
                $XmlNonContractShipmentDeliverySpecCustoms->addChild('invoice-number', $NcShipment->getDeliverySpec()->getCustoms()->getInvoiceNumber());
            }
        }

        $request = $XmlNonContractShipment->asXML();

        $response = $this->processRequest(array(
            'request_url' => '/ncshipment',
            'headers' => array(
                'Content-Type: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
            ),
            'request' => $request,
        ));

        if ($this->isError()) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'non-contract-shipment-info':
                $NonContractShipmentInfoType = new NonContractShipmentInfoType();

                $NonContractShipmentInfoType->setShipmentId((string)$responseXML->{'shipment-id'});
                $NonContractShipmentInfoType->setTrackingPin((string)$responseXML->{'tracking-pin'});

                if ($responseXML->{'links'}->link) {
                    foreach ($responseXML->{'links'}->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $NonContractShipmentInfoType->addLink($LinkType);

                        switch ($LinkType->getRel()) {
                            case 'self':
                                $NonContractShipmentInfoType->setSelfLink($LinkType);
                                break;
                            case 'details':
                                $NonContractShipmentInfoType->setDetailsLink($LinkType);
                                break;
                            case 'refund':
                                $NonContractShipmentInfoType->setRefundLink($LinkType);
                                break;
                            case 'receipt':
                                $NonContractShipmentInfoType->setReceiptLink($LinkType);
                                break;
                            case 'label':
                                $NonContractShipmentInfoType->setLabelLink($LinkType);
                                break;
                            case 'commercialInvoice':
                                $NonContractShipmentInfoType->setCommercialInvoiceLink($LinkType);
                                break;
                            default:
                                break;
                        }
                    }
                }

                $this->setResponse($NonContractShipmentInfoType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
                break;
        }
        return $this->getResponse();
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return bool|NonContractShipmentsType|MessagesType
     */
    public function getShipments(\DateTime $start = null, \DateTime $end = null)
    {
        $queryStr = array();

        if ($start) {
            $queryStr['from'] = $start->format('Ymd').'0000';
        }

        if ($end) {
            $queryStr['to'] = $end->format('Ymd').'0000';
        }

        $requestQueryStr = http_build_query($queryStr);

        $response = $this->processRequest(array(
            'request_url' => '/ncshipment?'.$requestQueryStr,
            'headers' => array(
                'Content-Type: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
            ),
        ));

        if ($this->isError()) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'non-contract-shipments':
                $NonContractShipmentsType = new NonContractShipmentsType();

                if ($responseXML->link) {
                    foreach ($responseXML->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $NonContractShipmentsType->addLink($LinkType);
                    }
                }

                $this->setResponse($NonContractShipmentsType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
                break;
        }
        return $this->getResponse();
    }

    /**
     * @param int $id
     * @return bool|MessagesType|NonContractShipmentInfoType
     */
    public function getShipment($link)
    {
        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Content-Type: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
            ),
        ));

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'non-contract-shipment-info':
                    $NonContractShipmentInfoType = new NonContractShipmentInfoType();

                    $NonContractShipmentInfoType->setShipmentId((string)$responseXML->{'shipment-id'});
                    $NonContractShipmentInfoType->setTrackingPin((string)$responseXML->{'tracking-pin'});

                    if ($responseXML->{'links'}->link) {
                        foreach ($responseXML->{'links'}->link as $link) {
                            $LinkType = new LinkType();

                            $LinkType->setHref((string)$link['href']);
                            $LinkType->setRel((string)$link['rel']);
                            $LinkType->setMediaType((string)$link['media-type']);

                            if (isset($link['index'])) {
                                $LinkType->setIndex((string)$link['index']);
                            }

                            $NonContractShipmentInfoType->addLink($LinkType);
                            switch ($LinkType->getRel()) {
                                case 'self':
                                    $NonContractShipmentInfoType->setSelfLink($LinkType);
                                    break;
                                case 'details':
                                    $NonContractShipmentInfoType->setDetailsLink($LinkType);
                                    break;
                                case 'refund':
                                    $NonContractShipmentInfoType->setRefundLink($LinkType);
                                    break;
                                case 'receipt':
                                    $NonContractShipmentInfoType->setReceiptLink($LinkType);
                                    break;
                                case 'label':
                                    $NonContractShipmentInfoType->setLabelLink($LinkType);
                                    break;
                                case 'commercialInvoice':
                                    $NonContractShipmentInfoType->setCommercialInvoiceLink($LinkType);
                                    break;
                                default:
                                    break;
                            }
                        }
                    }

                    $this->setResponse($NonContractShipmentInfoType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    /**
     * @param int $id
     *
     * @return bool|MessagesType|NonContractShipmentType
     */
    public function getShipmentDetails($link)
    {
        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Content-Type: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
            ),
        ));

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'non-contract-shipment-details':
                    $NonContractShipmentType = new NonContractShipmentInfoType();
//                    $NonContractShipmentType->setShipmentId($id);
                    $NonContractShipmentType->setTrackingPin((string)$responseXML->{'tracking-pin'});
                    $NonContractShipmentType->setFinalShippingPoint((string)$responseXML->{'final-shipping-point'});

                    $DeliverySpec = new DeliverySpecType();
                    $DeliverySpec->setServiceCode((string)$responseXML->{'delivery-spec'}->{'service-code'});

                    // Sender
                    $Sender = new SenderType();
                    if (!empty($responseXML->{'delivery-spec'}->sender->{'name'})) {
                        $Sender->setName((string)$responseXML->{'delivery-spec'}->sender->{'name'});
                    }
                    $Sender->setCompany((string)$responseXML->{'delivery-spec'}->sender->{'company'});
                    $Sender->setContactPhone((string)$responseXML->{'delivery-spec'}->sender->{'contact-phone'});

                    $AddressDetails = new DomesticAddressDetailsType();
                    $AddressDetails->setAddressLine1((string)$responseXML->{'delivery-spec'}->sender->{'address-details'}->{'address-line-1'});
                    if (!empty($responseXML->{'delivery-spec'}->sender->{'address-details'}->{'address-line-2'})) {
                        $AddressDetails->setAddressLine2((string)$responseXML->{'delivery-spec'}->sender->{'address-details'}->{'address-line-2'});
                    }
                    $AddressDetails->setCity((string)$responseXML->{'delivery-spec'}->sender->{'address-details'}->{'city'});
                    $AddressDetails->setProvState((string)$responseXML->{'delivery-spec'}->sender->{'address-details'}->{'prov-state'});
                    $AddressDetails->setPostalZipCode((string)$responseXML->{'delivery-spec'}->sender->{'address-details'}->{'postal-zip-code'});
                    $Sender->setAddressDetails($AddressDetails);
                    $DeliverySpec->setSender($Sender);

                    // Destination
                    $Destination = new DestinationType();
                    if (!empty($responseXML->{'delivery-spec'}->destination->{'name'})) {
                        $Destination->setName((string)$responseXML->{'delivery-spec'}->destination->{'name'});
                    }
                    if (!empty($responseXML->{'delivery-spec'}->destination->{'company'})) {
                        $Destination->setCompany((string)$responseXML->{'delivery-spec'}->destination->{'company'});
                    }

                    $DestinationAddressDetails = new DestinationAddressDetailsType();
                    $DestinationAddressDetails->setAddressLine1((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'address-line-1'});
                    if (!empty($responseXML->{'delivery-spec'}->destination->{'address-details'}->{'address-line-2'})) {
                        $DestinationAddressDetails->setAddressLine2((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'address-line-2'});
                    }
                    $DestinationAddressDetails->setCity((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'city'});
                    $DestinationAddressDetails->setProvState((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'prov-state'});
                    $DestinationAddressDetails->setCountryCode((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'country-code'});
                    $DestinationAddressDetails->setPostalZipCode((string)$responseXML->{'delivery-spec'}->destination->{'address-details'}->{'postal-zip-code'});
                    $Destination->setAddressDetails($DestinationAddressDetails);
                    $DeliverySpec->setDestination($Destination);

                    // Options
                    if (!empty($responseXML->{'delivery-spec'}->options)) {
                        if (!empty($responseXML->{'delivery-spec'}->options->option)) {
                            $Options = new OptionsType();
                            foreach ($responseXML->{'delivery-spec'}->options->option as $option) {
                                $OptionType = new OptionType();
                                $OptionType->setOptionCode((string)$option->{'option-code'});
                                $Options->addOption($OptionType);
                            }

                            $DeliverySpec->setOptions($Options);
                        }
                    }

                    // Dimensions
                    $ParcelCharacteristics = new ParcelCharacteristicsType();
                    $ParcelCharacteristics->setWeight((float)$responseXML->{'delivery-spec'}->{'parcel-characteristics'}->{'weight'});
                    $Dimension = new DimensionType();
                    $Dimension->setLength((float)$responseXML->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'length'});
                    $Dimension->setWidth((float)$responseXML->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'width'});
                    $Dimension->setHeight((float)$responseXML->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'height'});
                    $ParcelCharacteristics->setDimensions($Dimension);
                    $DeliverySpec->setParcelCharacteristics($ParcelCharacteristics);

                    // Preferences
                    $Preferences = new PreferencesType();
                    $Preferences->setShowPackingInstructions((string)$responseXML->{'delivery-spec'}->{'preferences'}->{'show-packing-instructions'});
                    $Preferences->setShowInsuredValue((string)$responseXML->{'delivery-spec'}->{'preferences'}->{'show-insured-value'});
                    $Preferences->setShowPostageRate((string)$responseXML->{'delivery-spec'}->{'preferences'}->{'show-postage-rate'});
                    $DeliverySpec->setPreferences($Preferences);

                    // Refs
                    if (!empty($responseXML->{'delivery-spec'}->{'references'})) {
                        $References = new ReferencesType();
                        if (!empty($responseXML->{'delivery-spec'}->{'references'}->{'cost-centre'})) {
                            $References->setCostCentre((string)$responseXML->{'delivery-spec'}->{'references'}->{'cost-centre'});
                        }
                        if (!empty($responseXML->{'delivery-spec'}->{'references'}->{'customer-ref-1'})) {
                            $References->setCustomerRef1((string)$responseXML->{'delivery-spec'}->{'references'}->{'customer-ref-1'});
                        }
                        if (!empty($responseXML->{'delivery-spec'}->{'references'}->{'customer-ref-2'})) {
                            $References->setCustomerRef2((string)$responseXML->{'delivery-spec'}->{'references'}->{'customer-ref-2'});
                        }
                        $DeliverySpec->setReferences($References);
                    }

                    $NonContractShipmentType->setDeliverySpec($DeliverySpec);
                    $this->setResponse($NonContractShipmentType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    /**
     * @param string $link
     * @param string $email
     *
     * @return bool|MessagesType|NonContractShipmentRefundInfoType
     */
    public function processRefund($link, $email)
    {
        $XmlRefundRequestType = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><non-contract-shipment-refund-request xmlns="http://www.canadapost.ca/ws/ncshipment-v'.self::API_VERSION.'"/>');
        $XmlRefundRequestType->addChild('email', $email);

        $request = $XmlRefundRequestType->asXML();

        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Content-Type: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.ncshipment-v'.self::API_VERSION.'+xml',
            ),
            'request' => $request,
        ));

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'non-contract-shipment-refund-request-info':
                    $NonContractShipmentRefundInfoType = new NonContractShipmentRefundInfoType();
                    $NonContractShipmentRefundInfoType->setServiceTicketDate((string)$responseXML->{'service-ticket-date'});
                    $NonContractShipmentRefundInfoType->setServiceTicketId((string)$responseXML->{'service-ticket-id'});
                    $this->setResponse($NonContractShipmentRefundInfoType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    /*
     * @var string $link Link returned from getShipment()
     * @var string $shipmentId
     * @var $dir Destination for label PDF
     * @return bool|MessagesType|ApplicationPdfType
     * */
    public function getArtifact($link, $fileName, $dir)
    {
        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/pdf',
            ),
            'request' => null,
        ));

        if ($response) {
            if (strpos($response, '<?xml') === 0) {
                $responseXML = new \SimpleXMLElement($response);

                if ($responseXML->getName()) {
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    return $this->getResponse();
                }
            } else {
                $ApplicationPdfType = new ApplicationPdfType();
                $ApplicationPdfType->setContent($response);
                file_put_contents($dir.'/'.$fileName.'.pdf', $response);

                return $ApplicationPdfType;
            }
        }

        return false;
    }
}
