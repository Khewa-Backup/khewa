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
use CanadaPostWs\Type\Manifest\ManifestDetailsType;
use CanadaPostWs\Type\Manifest\ManifestsType;
use CanadaPostWs\Type\Manifest\ManifestType;
use CanadaPostWs\Type\Manifest\ManifestAddressType;
use CanadaPostWs\Type\Manifest\ManifestPricingInfoType;
use CanadaPostWs\Type\Manifest\ShipmentTransmitSetType;
use CanadaPostWs\Type\Messages\MessagesType;
use CanadaPostWs\Type\Shipment\ShipmentInfoType;
use CanadaPostWs\Type\Shipment\ShipmentType;
use CanadaPostWs\Type\Common\ApplicationPdfType;
use CanadaPostWs\Type\Shipment\DestinationAddressDetailsType;
use CanadaPostWs\Type\Shipment\DestinationType;
use CanadaPostWs\Type\Shipment\ShipmentsType;
use CanadaPostWs\Type\Shipment\ParcelCharacteristicsType;
use CanadaPostWs\Type\Shipment\DimensionType;
use CanadaPostWs\Type\Shipment\OptionsType;
use CanadaPostWs\Type\Shipment\OptionType;
use CanadaPostWs\Type\Shipment\DeliverySpecType;
use CanadaPostWs\Type\Shipment\PreferencesType;
use CanadaPostWs\Type\Shipment\ReferencesType;
use CanadaPostWs\Type\Shipment\SenderType;
use CanadaPostWs\Type\Shipment\DomesticAddressDetailsType;
use CanadaPostWs\Type\Shipment\AddressDetailsType;
use CanadaPostWs\Type\Shipment\ShipmentRefundInfoType;
use CanadaPostWs\Type\Shipment\GroupsType;
use CanadaPostWs\Type\Shipment\GroupType;
use SimpleXMLElement;

class Shipping extends WebService
{
    const API_VERSION = 8;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $mailedBy = $this->options['api_customer_number'];
        $mobo = $this->options['api_customer_number'];

        $this->requestUrl .= '/rs/'.$mailedBy.(array_key_exists('platform_id', $this->options) ? '-'.$this->options['platform_id'] : '').'/'.$mobo;
    }

    /**
     * @param ShipmentType $Shipment
     * @return bool|MessagesType|ShipmentInfoType
     */
    public function createShipment(ShipmentType $Shipment)
    {
        $XmlShipment = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><shipment xmlns="http://www.canadapost.ca/ws/shipment-v'.self::API_VERSION.'"/>');

        if (null !== $Shipment->getGroupId()) {
            $XmlShipment->addChild('group-id', $Shipment->getGroupId());
        } else {
            $XmlShipment->addChild('transmit-shipment', ((int)$Shipment->isTransmitShipment() ? 'true' : 'false'));
        }

        if (null !== $Shipment->isQuickshipLabelRequested()) {
            $XmlShipment->addChild('quickship-label-requested', ((int)$Shipment->isQuickshipLabelRequested() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getRequestedShippingPoint()) {
            $XmlShipment->addChild('requested-shipping-point', $Shipment->getRequestedShippingPoint());
        }

        if (null !== $Shipment->getShippingPointId()) {
            $XmlShipment->addChild('shipping-point-id', $Shipment->getShippingPointId());
        }

        if (null !== $Shipment->isCpcPickupIndicator()) {
            $XmlShipment->addChild('cpc-pickup-indicator', ((int)$Shipment->isCpcPickupIndicator() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getExpectedMailingDate()) {
            $XmlShipment->addChild('expected-mailing-date', $Shipment->getExpectedMailingDate()->format('Y-m-d'));
        }

        $XmlShipmentDeliverySpec = $XmlShipment->addChild('delivery-spec');
        $XmlShipmentDeliverySpec->addChild('service-code', $Shipment->getDeliverySpec()->getServiceCode());

        $XmlShipmentDeliverySpecSender = $XmlShipmentDeliverySpec->addChild('sender');

        if (null !== $Shipment->getDeliverySpec()->getSender()->getName()) {
            $XmlShipmentDeliverySpecSender->addChild('name', $Shipment->getDeliverySpec()->getSender()->getName());
        }

        $XmlShipmentDeliverySpecSender->addChild('company', $Shipment->getDeliverySpec()->getSender()->getCompany());
        $XmlShipmentDeliverySpecSender->addChild('contact-phone', $Shipment->getDeliverySpec()->getSender()->getContactPhone());

        $XmlShipmentDeliverySpecSenderAddressDetails = $XmlShipmentDeliverySpecSender->addChild('address-details');
        $XmlShipmentDeliverySpecSenderAddressDetails->addChild('address-line-1', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine1());

        if (null !== $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine2()) {
            $XmlShipmentDeliverySpecSenderAddressDetails->addChild('address-line-2', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getAddressLine2());
        }

        $XmlShipmentDeliverySpecSenderAddressDetails->addChild('city', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getCity());
        $XmlShipmentDeliverySpecSenderAddressDetails->addChild('prov-state', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getProvState());
        $XmlShipmentDeliverySpecSenderAddressDetails->addChild('country-code', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getCountryCode());

        if (null !== $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getPostalZipCode()) {
            $XmlShipmentDeliverySpecSenderAddressDetails->addChild('postal-zip-code', $Shipment->getDeliverySpec()->getSender()->getAddressDetails()->getPostalZipCode());
        }

        $XmlShipmentDeliverySpecDestination = $XmlShipmentDeliverySpec->addChild('destination');

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getName()) {
            $XmlShipmentDeliverySpecDestination->addChild('name', $Shipment->getDeliverySpec()->getDestination()->getName());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getCompany()) {
            $XmlShipmentDeliverySpecDestination->addChild('company', $Shipment->getDeliverySpec()->getDestination()->getCompany());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAdditionalAddressInfo()) {
            $XmlShipmentDeliverySpecDestination->addChild('additional-address-info', $Shipment->getDeliverySpec()->getDestination()->getAdditionalAddressInfo());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getClientVoiceNumber()) {
            $XmlShipmentDeliverySpecDestination->addChild('client-voice-number', $Shipment->getDeliverySpec()->getDestination()->getClientVoiceNumber());
        }

        $XmlShipmentDeliverySpecDestinationAddressDetails = $XmlShipmentDeliverySpecDestination->addChild('address-details');

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine1()) {
            $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('address-line-1', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine1());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine2()) {
            $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('address-line-2', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getAddressLine2());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCity()) {
            $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('city', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCity());
        }

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getProvState()) {
            $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('prov-state', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getProvState());
        }

        $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('country-code', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getCountryCode());

        if (null !== $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode()) {
            $XmlShipmentDeliverySpecDestinationAddressDetails->addChild('postal-zip-code', $Shipment->getDeliverySpec()->getDestination()->getAddressDetails()->getPostalZipCode());
        }

        $OptionsList = $Shipment->getDeliverySpec()->getOptions();

        if ($OptionsList) {
            $Options = $OptionsList->getOptions();

            if ($Options) {
                $XmlShipmentDeliverySpecOptions = $XmlShipmentDeliverySpec->addChild('options');

                foreach ($Options as $Option) {
                    $XmlShipmentDeliverySpecOptionsOption = $XmlShipmentDeliverySpecOptions->addChild('option');
                    $XmlShipmentDeliverySpecOptionsOption->addChild('option-code', $Option->getOptionCode());

                    if (null !== $Option->getOptionAmount()) {
                        $XmlShipmentDeliverySpecOptionsOption->addChild('option-amount', number_format($Option->getOptionAmount(), 2, '.', ''));
                    }

                    if (null !== $Option->isOptionQualifier1()) {
                        $XmlShipmentDeliverySpecOptionsOption->addChild('option-qualifier-1', ((int)$Option->isOptionQualifier1() ? 'true' : 'false'));
                    }

                    if (null !== $Option->getOptionQualifier2()) {
                        $XmlShipmentDeliverySpecOptionsOption->addChild('option-amount', $Option->getOptionQualifier2());
                    }
                }
            }
        }

        $XmlShipmentDeliverySpecParcelCharacteristics = $XmlShipmentDeliverySpec->addChild('parcel-characteristics');
        $XmlShipmentDeliverySpecParcelCharacteristics->addChild('weight', $Shipment->getDeliverySpec()->getParcelCharacteristics()->getWeight());

        if (null !== $Shipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()) {
            $XmlShipmentDeliverySpecParcelCharacteristicsDimensions = $XmlShipmentDeliverySpecParcelCharacteristics->addChild('dimensions');
            $XmlShipmentDeliverySpecParcelCharacteristicsDimensions->addChild('length', $Shipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getLength());
            $XmlShipmentDeliverySpecParcelCharacteristicsDimensions->addChild('width', $Shipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getWidth());
            $XmlShipmentDeliverySpecParcelCharacteristicsDimensions->addChild('height', $Shipment->getDeliverySpec()->getParcelCharacteristics()->getDimensions()->getHeight());
        }

        if (null !== $Shipment->getDeliverySpec()->getParcelCharacteristics()->isUnpackaged()) {
            $XmlShipmentDeliverySpecParcelCharacteristics->addChild('unpackaged', ((int)$Shipment->getDeliverySpec()->getParcelCharacteristics()->isUnpackaged() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getParcelCharacteristics()->isMailingTube()) {
            $XmlShipmentDeliverySpecParcelCharacteristics->addChild('mailing-tube', ((int)$Shipment->getDeliverySpec()->getParcelCharacteristics()->isMailingTube() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getParcelCharacteristics()->isOversized()) {
            $XmlShipmentDeliverySpecParcelCharacteristics->addChild('oversized', ((int)$Shipment->getDeliverySpec()->getParcelCharacteristics()->isOversized() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getNotification()) {
            $XmlShipmentDeliverySpecNotification = $XmlShipmentDeliverySpec->addChild('notification');
            $XmlShipmentDeliverySpecNotification->addChild('email', $Shipment->getDeliverySpec()->getNotification()->getEmail());
            $XmlShipmentDeliverySpecNotification->addChild('on-shipment', ((int)$Shipment->getDeliverySpec()->getNotification()->isOnShipment() ? 'true' : 'false'));
            $XmlShipmentDeliverySpecNotification->addChild('on-exception', ((int)$Shipment->getDeliverySpec()->getNotification()->isOnException() ? 'true' : 'false'));
            $XmlShipmentDeliverySpecNotification->addChild('on-delivery', ((int)$Shipment->getDeliverySpec()->getNotification()->isOnDelivery() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getPrintPreferences()) {
            $XmlShipmentDeliverySpecPrintPreferences = $XmlShipmentDeliverySpec->addChild('print-preferences');

            if (null !== $Shipment->getDeliverySpec()->getPrintPreferences()->getOutputFormat()) {
                $XmlShipmentDeliverySpecPrintPreferences->addChild('output-format', $Shipment->getDeliverySpec()->getPrintPreferences()->getOutputFormat());
            } else {
                $XmlShipmentDeliverySpecPrintPreferences->addChild('output-format', '4x6');
            }

            if (null !== $Shipment->getDeliverySpec()->getPrintPreferences()->getEncoding()) {
                $XmlShipmentDeliverySpecPrintPreferences->addChild('encoding', $Shipment->getDeliverySpec()->getPrintPreferences()->getEncoding());
            }
        }

        $XmlShipmentDeliverySpecPreferences = $XmlShipmentDeliverySpec->addChild('preferences');
        $XmlShipmentDeliverySpecPreferences->addChild('show-packing-instructions', ((int)$Shipment->getDeliverySpec()->getPreferences()->isShowPackingInstructions() ? 'true' : 'false'));

        if (null !== $Shipment->getDeliverySpec()->getPreferences()->isShowPostageRate()) {
            $XmlShipmentDeliverySpecPreferences->addChild('show-postage-rate', ((int)$Shipment->getDeliverySpec()->getPreferences()->isShowPostageRate() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getPreferences()->isShowInsuredValue()) {
            $XmlShipmentDeliverySpecPreferences->addChild('show-insured-value', ((int)$Shipment->getDeliverySpec()->getPreferences()->isShowInsuredValue() ? 'true' : 'false'));
        }

        if (null !== $Shipment->getDeliverySpec()->getReferences()) {
            $XmlShipmentDeliverySpecReferences = $XmlShipmentDeliverySpec->addChild('references');

            if (null !== $Shipment->getDeliverySpec()->getReferences()->getCostCentre()) {
                $XmlShipmentDeliverySpecReferences->addChild('cost-centre', $Shipment->getDeliverySpec()->getReferences()->getCostCentre());
            }

            if (null !== $Shipment->getDeliverySpec()->getReferences()->getCustomerRef1()) {
                $XmlShipmentDeliverySpecReferences->addChild('customer-ref-1', $Shipment->getDeliverySpec()->getReferences()->getCustomerRef1());
            }

            if (null !== $Shipment->getDeliverySpec()->getReferences()->getCustomerRef2()) {
                $XmlShipmentDeliverySpecReferences->addChild('customer-ref-2', $Shipment->getDeliverySpec()->getReferences()->getCustomerRef2());
            }
        }

        if (null !== $Shipment->getDeliverySpec()->getCustoms()) {
            $XmlShipmentDeliverySpecCustoms = $XmlShipmentDeliverySpec->addChild('customs');
            $XmlShipmentDeliverySpecCustoms->addChild('currency', $Shipment->getDeliverySpec()->getCustoms()->getCurrency());

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getConversionFromCad()) {
                $XmlShipmentDeliverySpecCustoms->addChild('conversion-from-cad', $Shipment->getDeliverySpec()->getCustoms()->getConversionFromCad());
            }

            $XmlShipmentDeliverySpecCustoms->addChild('reason-for-export', $Shipment->getDeliverySpec()->getCustoms()->getReasonForExport());

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getOtherReason()) {
                $XmlShipmentDeliverySpecCustoms->addChild('other-reason', $Shipment->getDeliverySpec()->getCustoms()->getOtherReason());
            }

            $SkuList = $Shipment->getDeliverySpec()->getCustoms()->getSkuList();

            if (null !== $SkuList) {
                $XmlShipmentDeliverySpecCustomsSkuList = $XmlShipmentDeliverySpecCustoms->addChild('sku-list');

                if ($SkuList->getItems()) {
                    foreach ($SkuList->getItems() as $Sku) {
                        $XmlShipmentDeliverySpecCustomsSkuListItem = $XmlShipmentDeliverySpecCustomsSkuList->addChild('item');
                        $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('customs-number-of-units', $Sku->getCustomsNumberOfUnits());
                        $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('customs-description', $Sku->getCustomsDescription());

                        if (null != $Sku->getSku()) {
                            $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('sku', $Sku->getSku());
                        }

                        if (null != $Sku->getHsTariffCode()) {
                            $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('hs-tariff-code', $Sku->getHsTariffCode());
                        }

                        $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('unit-weight', $Sku->getUnitWeight());
                        $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('customs-value-per-unit', $Sku->getCustomsValuePerUnit());

                        if (null != $Sku->getCustomsUnitOfMeasure()) {
                            $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('customs-unit-of-measure', $Sku->getCustomsUnitOfMeasure());
                        }

                        if (null != $Sku->getCountryOfOrigin()) {
                            $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('country-of-origin', $Sku->getCountryOfOrigin());
                        }

                        if (null != $Sku->getProvinceOfOrigin()) {
                            $XmlShipmentDeliverySpecCustomsSkuListItem->addChild('province-of-origin', $Sku->getProvinceOfOrigin());
                        }
                    }
                }
            }

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getDutiesAndTaxesPrepaid()) {
                $XmlShipmentDeliverySpecCustoms->addChild('duties-and-taxes-prepaid', $Shipment->getDeliverySpec()->getCustoms()->getDutiesAndTaxesPrepaid());
            }

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getCertificateNumber()) {
                $XmlShipmentDeliverySpecCustoms->addChild('certificate-number', $Shipment->getDeliverySpec()->getCustoms()->getCertificateNumber());
            }

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getLicenceNumber()) {
                $XmlShipmentDeliverySpecCustoms->addChild('licence-number', $Shipment->getDeliverySpec()->getCustoms()->getLicenceNumber());
            }

            if (null !== $Shipment->getDeliverySpec()->getCustoms()->getInvoiceNumber()) {
                $XmlShipmentDeliverySpecCustoms->addChild('invoice-number', $Shipment->getDeliverySpec()->getCustoms()->getInvoiceNumber());
            }
        }

        $XmlShipmentDeliverySpecSettlementInfo = $XmlShipmentDeliverySpec->addChild('settlement-info');

        if (null !== $Shipment->getDeliverySpec()->getSettlementInfo()->getPaidByCustomer()) {
            $XmlShipmentDeliverySpecSettlementInfo->addChild('paid-by-customer', $Shipment->getDeliverySpec()->getSettlementInfo()->getPaidByCustomer());
        }

        if (null !== $Shipment->getDeliverySpec()->getSettlementInfo()->getContractId()) {
            $XmlShipmentDeliverySpecSettlementInfo->addChild('contract-id', $Shipment->getDeliverySpec()->getSettlementInfo()->getContractId());
        }

        if (true === $Shipment->getDeliverySpec()->getSettlementInfo()->isCifShipment()) {
            $XmlShipmentDeliverySpecSettlementInfo->addChild('cif-shipment', ((int)$Shipment->getDeliverySpec()->getSettlementInfo()->isCifShipment() ? 'true' : 'false'));
        }

        $XmlShipmentDeliverySpecSettlementInfo->addChild('intended-method-of-payment', $Shipment->getDeliverySpec()->getSettlementInfo()->getIntendedMethodOfPayment());

        if (null !== $Shipment->getReturnSpec()) {
            $XmlShipmentReturnSpec = $XmlShipment->addChild('return-spec');
            $XmlShipmentReturnSpec->addChild('service-code', $Shipment->getReturnSpec()->getServiceCode());

            $XmlShipmentReturnSpecReturnRecipient = $XmlShipmentReturnSpec->addChild('return-recipient');

            if (null !== $Shipment->getReturnSpec()->getReturnRecipient()->getName()) {
                $XmlShipmentReturnSpecReturnRecipient->addChild('name', $Shipment->getReturnSpec()->getReturnRecipient()->getName());
            }

            if (null !== $Shipment->getReturnSpec()->getReturnRecipient()->getCompany()) {
                $XmlShipmentReturnSpecReturnRecipient->addChild('company', $Shipment->getReturnSpec()->getReturnRecipient()->getCompany());
            }

            $XmlShipmentReturnSpecReturnRecipientAddressDetails = $XmlShipmentReturnSpecReturnRecipient->addChild('address-details');
            $XmlShipmentReturnSpecReturnRecipientAddressDetails->addChild('address-line-1', $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getAddressLine1());

            if (null !== $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getAddressLine2()) {
                $XmlShipmentReturnSpecReturnRecipientAddressDetails->addChild('address-line-2', $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getAddressLine2());
            }

            $XmlShipmentReturnSpecReturnRecipientAddressDetails->addChild('city', $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getCity());
            $XmlShipmentReturnSpecReturnRecipientAddressDetails->addChild('prov-state', $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getProvState());
            $XmlShipmentReturnSpecReturnRecipientAddressDetails->addChild('postal-zip-code', $Shipment->getReturnSpec()->getReturnRecipient()->getAddressDetails()->getPostalZipCode());

            if (null !== $Shipment->getReturnSpec()->getReturnNotification()) {
                $XmlShipmentReturnSpec->addChild('return-notification', $Shipment->getReturnSpec()->getReturnNotification());
            }
        }

        $request = $XmlShipment->asXML();

        $response = $this->processRequest(array(
            'request_url' => '/shipment',
            'headers' => array(
                'Content-Type: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
            'request' => $request,
        ));

        if ($this->isError()) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'shipment-info':
                $ShipmentInfoType = new ShipmentInfoType();

                $ShipmentInfoType->setShipmentId((string)$responseXML->{'shipment-id'});
                $ShipmentInfoType->setShipmentStatus((string)$responseXML->{'shipment-status'});
                $ShipmentInfoType->setTrackingPin((string)$responseXML->{'tracking-pin'});
                if (!empty($responseXML->{'return-tracking-pin'})) {
                    $ShipmentInfoType->setReturnTrackingPin((string)$responseXML->{'return-tracking-pin'});
                }

                if ($responseXML->{'links'}->link) {
                    foreach ($responseXML->{'links'}->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $ShipmentInfoType->addLink($LinkType);

                        switch ($LinkType->getRel()) {
                            case 'self':
                                $ShipmentInfoType->setSelfLink($LinkType);
                                break;
                            case 'details':
                                $ShipmentInfoType->setDetailsLink($LinkType);
                                break;
                            case 'refund':
                                $ShipmentInfoType->setRefundLink($LinkType);
                                break;
                            case 'receipt':
                                $ShipmentInfoType->setReceiptLink($LinkType);
                                break;
                            case 'group':
                                $ShipmentInfoType->setGroupLink($LinkType);
                                break;
                            case 'label':
                                $ShipmentInfoType->setLabelLink($LinkType);
                                break;
                            case 'returnLabel':
                                $ShipmentInfoType->setReturnLabelLink($LinkType);
                                break;
                            case 'commercialInvoice':
                                $ShipmentInfoType->setCommercialInvoiceLink($LinkType);
                                break;
                            default:
                                break;
                        }
                    }
                }

                $this->setResponse($ShipmentInfoType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $link
     * @return bool|MessagesType|ShipmentInfoType
     */
    public function getShipment($link)
    {
//        $response = $this->processRequest(array(
//            'request_url' => '/shipment/'.$id,
//            'headers' => array(
//                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
//            ),
//        ));
//
//        if ($this->isError()) {
//            return false;
//        }
//
//        $responseXML = new SimpleXMLElement($response);

        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
        ));

//        $RequestProcessor = new RequestProcessor(array(
//            'request_url' => $link,
//            'headers' => array(
//                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
//                'Platform-id: '.$this->options['platform_id']
//            ),
//            'api_key' => $this->options['api_key'],
//            'ssl' => $this->options['ssl'],
//        ));
//        $response = $RequestProcessor->process();

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new \SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'shipment-info':
                    $ShipmentInfoType = new ShipmentInfoType();

                    $ShipmentInfoType->setShipmentId((string)$responseXML->{'shipment-id'});
                    $ShipmentInfoType->setShipmentStatus((string)$responseXML->{'shipment-status'});
                    $ShipmentInfoType->setTrackingPin((string)$responseXML->{'tracking-pin'});
                    $ShipmentInfoType->setPoNumber((string)$responseXML->{'po-number'});

                    if ($responseXML->{'links'}->link) {
                        foreach ($responseXML->{'links'}->link as $link) {
                            $LinkType = new LinkType();

                            $LinkType->setHref((string)$link['href']);
                            $LinkType->setRel((string)$link['rel']);
                            $LinkType->setMediaType((string)$link['media-type']);

                            if (isset($link['index'])) {
                                $LinkType->setIndex((string)$link['index']);
                            }

                            $ShipmentInfoType->addLink($LinkType);

                            switch ($LinkType->getRel()) {
                                case 'self':
                                    $ShipmentInfoType->setSelfLink($LinkType);
                                    break;
                                case 'details':
                                    $ShipmentInfoType->setDetailsLink($LinkType);
                                    break;
                                case 'refund':
                                    $ShipmentInfoType->setRefundLink($LinkType);
                                    break;
                                case 'receipt':
                                    $ShipmentInfoType->setReceiptLink($LinkType);
                                    break;
                                case 'group':
                                    $ShipmentInfoType->setGroupLink($LinkType);
                                    break;
                                case 'label':
                                    $ShipmentInfoType->setLabelLink($LinkType);
                                    break;
                                default:
                                    break;
                            }
                        }
                    }

                    $this->setResponse($ShipmentInfoType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * @param string $groupId
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return bool|ShipmentsType|MessagesType
     */
    public function getShipments($groupId, $start = null, $end = null)
    {
        $queryStr = array();

        if ($groupId) {
            $queryStr['groupId'] = $groupId;
        }

        if ($start) {
            $queryStr['noManifest'] = 'true';
            $queryStr['from'] = $start->format('Ymd').'0000';
        }

        if ($end) {
            $queryStr['to'] = $end->format('Ymd').'0000';
        }

        $requestQueryStr = http_build_query($queryStr);

        $response = $this->processRequest(array(
            'request_url' => '/shipment?'.$requestQueryStr,
            'headers' => array(
                'Content-Type: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
        ));

        if ($this->isError()) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'shipments':
                $ShipmentsType = new ShipmentsType();

                if ($responseXML->link) {
                    foreach ($responseXML->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $ShipmentsType->addLink($LinkType);
                    }
                }

                $this->setResponse($ShipmentsType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
        return $this->getResponse();
    }

    /**
     * @param string $link
     * @return bool|MessagesType|ShipmentInfoType
     */
    public function getShipmentDetails($link)
    {
//        $response = $this->processRequest(array(
//            'request_url' => '/shipment/' . $id . '/details',
//            'headers'     => array(
//                'Accept: application/vnd.cpc.shipment-v' . self::API_VERSION . '+xml',
//            ),
//        ));
//
//        if ($this->isError()) {
//            return false;
//        }
//
//        $responseXML = new SimpleXMLElement($response);

        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
        ));

//        $RequestProcessor = new RequestProcessor(array(
//            'request_url' => $link,
//            'headers' => array(
//                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
//                'Platform-id: '.$this->options['platform_id']
//            ),
//            'api_key' => $this->options['api_key'],
//            'ssl' => $this->options['ssl'],
//        ));
//        $response = $RequestProcessor->process();

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new \SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'shipment-details':
                    $ShipmentInfoType = new ShipmentInfoType();
//                    $ShipmentInfoType->setShipmentId((string)$responseXML->{'shipment-id'});
                    $ShipmentInfoType->setTrackingPin((string)$responseXML->{'tracking-pin'});
                    $ShipmentInfoType->setFinalShippingPoint((string)$responseXML->{'final-shipping-point'});
                    $ShipmentInfoType->setShipmentStatus((string)$responseXML->{'shipment-status'});
                    $ShipmentInfoType->setPoNumber((string)$responseXML->{'po-number'});

                    $DeliverySpec = new DeliverySpecType();
                    $DeliverySpec->setServiceCode((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'service-code'});

                    // Sender
                    $Sender = new SenderType();
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'name'})) {
                        $Sender->setName((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'name'});
                    }
                    $Sender->setCompany((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'company'});
                    $Sender->setContactPhone((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'contact-phone'});

                    $AddressDetails = new AddressDetailsType();
                    $AddressDetails->setAddressLine1((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'address-line-1'});
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'address-line-2'})) {
                        $AddressDetails->setAddressLine2((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'address-line-2'});
                    }
                    $AddressDetails->setCity((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'city'});
                    $AddressDetails->setProvState((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'prov-state'});
                    $AddressDetails->setPostalZipCode((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->sender->{'address-details'}->{'postal-zip-code'});
                    $Sender->setAddressDetails($AddressDetails);
                    $DeliverySpec->setSender($Sender);

                    // Destination
                    $Destination = new DestinationType();
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'name'})) {
                        $Destination->setName((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'name'});
                    }
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'company'})) {
                        $Destination->setCompany((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'company'});
                    }

                    $DestinationAddressDetails = new DestinationAddressDetailsType();
                    $DestinationAddressDetails->setAddressLine1((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'address-line-1'});
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'address-line-2'})) {
                        $DestinationAddressDetails->setAddressLine2((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'address-line-2'});
                    }
                    $DestinationAddressDetails->setCity((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'city'});
                    $DestinationAddressDetails->setProvState((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'prov-state'});
                    $DestinationAddressDetails->setCountryCode((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'country-code'});
                    $DestinationAddressDetails->setPostalZipCode((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->destination->{'address-details'}->{'postal-zip-code'});
                    $Destination->setAddressDetails($DestinationAddressDetails);
                    $DeliverySpec->setDestination($Destination);

                    // Options
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->options)) {
                        if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->options->option)) {
                            $Options = new OptionsType();
                            foreach ($responseXML->{'shipment-detail'}->{'delivery-spec'}->options->option as $option) {
                                $OptionType = new OptionType();
                                $OptionType->setOptionCode((string)$option->{'option-code'});
                                $Options->addOption($OptionType);
                            }

                            $DeliverySpec->setOptions($Options);
                        }
                    }

                    // Dimensions
                    $ParcelCharacteristics = new ParcelCharacteristicsType();
                    $ParcelCharacteristics->setWeight((float)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'parcel-characteristics'}->{'weight'});
                    $Dimension = new DimensionType();
                    $Dimension->setLength((float)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'length'});
                    $Dimension->setWidth((float)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'width'});
                    $Dimension->setHeight((float)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'parcel-characteristics'}->{'dimensions'}->{'height'});
                    $ParcelCharacteristics->setDimensions($Dimension);
                    $DeliverySpec->setParcelCharacteristics($ParcelCharacteristics);

                    // Preferences
                    $Preferences = new PreferencesType();
                    $Preferences->setShowPackingInstructions((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'preferences'}->{'show-packing-instructions'});
                    $Preferences->setShowInsuredValue((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'preferences'}->{'show-insured-value'});
                    $Preferences->setShowPostageRate((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'preferences'}->{'show-postage-rate'});
                    $DeliverySpec->setPreferences($Preferences);

                    // Refs
                    if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'})) {
                        $References = new ReferencesType();
                        if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'cost-centre'})) {
                            $References->setCostCentre((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'cost-centre'});
                        }
                        if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'customer-ref-1'})) {
                            $References->setCustomerRef1((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'customer-ref-1'});
                        }
                        if (!empty($responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'customer-ref-2'})) {
                            $References->setCustomerRef2((string)$responseXML->{'shipment-detail'}->{'delivery-spec'}->{'references'}->{'customer-ref-2'});
                        }
                        $DeliverySpec->setReferences($References);
                    }

                    $ShipmentInfoType->setDeliverySpec($DeliverySpec);
                    $this->setResponse($ShipmentInfoType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return bool|ManifestsType|MessagesType
     */
    public function getManifests(\DateTime $start = null, \DateTime $end = null)
    {
        $queryStr = array();

        if ($start) {
            $queryStr['start'] = $start->format('Ymd');
        }

        if ($end) {
            $queryStr['end'] = $end->format('Ymd');
        }

        $requestQueryStr = http_build_query($queryStr);

        $response = $this->processRequest(array(
            'request_url' => '/manifest?'.$requestQueryStr,
            'headers' => array(
                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
            ),
        ));

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'manifests':
                $ManifestsType = new ManifestsType();

                if ($responseXML->link) {
                    foreach ($responseXML->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $ManifestsType->addLink($LinkType);
                    }
                }

                $this->setResponse($ManifestsType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $link
     * @return bool|MessagesType|ManifestType
     */
    public function getManifest($link)
    {
//        $response = $this->processRequest(array(
//            'request_url' => '/manifest/'.$id,
//            'headers' => array(
//                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
//            ),
//        ));
//
//        $responseXML = new SimpleXMLElement($response);

        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
            ),
        ));

//        $RequestProcessor = new RequestProcessor(array(
//            'request_url' => $link,
//            'headers' => array(
//                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
//                'Platform-id: '.$this->options['platform_id']
//            ),
//            'api_key' => $this->options['api_key'],
//            'ssl' => $this->options['ssl'],
//        ));
//        $response = $RequestProcessor->process();

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new \SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'manifest':
                    $ManifestType = new ManifestType();

                    $ManifestType->setPoNumber((string)$responseXML->{'po-number'});

                    if ($responseXML->{'links'}->link) {
                        foreach ($responseXML->{'links'}->link as $link) {
                            $LinkType = new LinkType();

                            $LinkType->setHref((string)$link['href']);
                            $LinkType->setRel((string)$link['rel']);
                            $LinkType->setMediaType((string)$link['media-type']);

                            if (isset($link['index'])) {
                                $LinkType->setIndex((string)$link['index']);
                            }

                            $ManifestType->addLink($LinkType);

                            switch ($LinkType->getRel()) {
                                case 'self':
                                    $ManifestType->setSelfLink($LinkType);
                                    break;
                                case 'details':
                                    $ManifestType->setDetailsLink($LinkType);
                                    break;
                                case 'manifestShipments':
                                    $ManifestType->setShipmentsLink($LinkType);
                                    break;
                                case 'artifact':
                                    $ManifestType->setArtifactLink($LinkType);
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    $this->setResponse($ManifestType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * @param string $link
     * @return bool|MessagesType|ManifestDetailsType
     */
    public function getManifestDetails($link)
    {
//        $response = $this->processRequest(array(
//            'request_url' => '/manifest/'.$id.'/details',
//            'headers' => array(
//                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
//            ),
//        ));
//
//        $responseXML = new SimpleXMLElement($response);


        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
            ),
        ));

//        $RequestProcessor = new RequestProcessor(array(
//            'request_url' => $link,
//            'headers' => array(
//                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
//                'Platform-id: '.$this->options['platform_id']
//            ),
//            'api_key' => $this->options['api_key'],
//            'ssl' => $this->options['ssl'],
//        ));
//        $response = $RequestProcessor->process();

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new \SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'manifest-details':
                    $ManifestType = new ManifestDetailsType();

                    $ManifestType->setPoNumber((string)$responseXML->{'po-number'});
                    $ManifestType->setFinalShippingPoint((string)$responseXML->{'final-shipping-point'});

                    $ManifestType->setShippingPointName((string)$responseXML->{'shipping-point-name'});
                    $ManifestType->setShippingPointId((string)$responseXML->{'shipping-point-id'});
                    $ManifestType->setMailedByCustomer((string)$responseXML->{'mailed-by-customer'});
                    $ManifestType->setMailedOnBehalfOf((string)$responseXML->{'mailed-on-behalf-of'});
                    $ManifestType->setPaidByCustomer((string)$responseXML->{'paid-by-customer'});
                    $ManifestType->setManifestDate((string)$responseXML->{'manifest-date'});
                    $ManifestType->setManifestTime((string)$responseXML->{'manifest-time'});
                    $ManifestType->setContractId((string)$responseXML->{'contract-id'});
                    $ManifestType->setMethodOfPayment((string)$responseXML->{'method-of-payment'});

                    $ManifestAddressType = new ManifestAddressType();
                    $ManifestAddressType->setManifestCompany((string)$responseXML->{'manifest-address'}->{'manifest-company'});
                    $ManifestAddressType->setManifestName((string)$responseXML->{'manifest-address'}->{'manifest-name'});
                    $ManifestAddressType->setPhoneNumber((string)$responseXML->{'manifest-address'}->{'phone-number'});

                    $AddressDetailsType = new \CanadaPostWs\Type\Manifest\AddressDetailsType();
                    $AddressDetailsType->setAddressLine1((string)$responseXML->{'manifest-address'}->{'address-details'}->{'address-line-1'});
                    $AddressDetailsType->setAddressLine2((string)$responseXML->{'manifest-address'}->{'address-details'}->{'address-line-2'});
                    $AddressDetailsType->setCity((string)$responseXML->{'manifest-address'}->{'address-details'}->{'city'});
                    $AddressDetailsType->setProvState((string)$responseXML->{'manifest-address'}->{'address-details'}->{'prov-state'});
                    $AddressDetailsType->setPostalZipCode((string)$responseXML->{'manifest-address'}->{'address-details'}->{'postal-zip-code'});
                    $AddressDetailsType->setCountryCode((string)$responseXML->{'manifest-address'}->{'address-details'}->{'country-code'});

                    $ManifestAddressType->setAddressDetails($AddressDetailsType);
                    $ManifestType->setManifestAddressType($ManifestAddressType);

                    $ManifestPricingInfoType = new ManifestPricingInfoType();
                    $ManifestPricingInfoType->setBaseCost((string)$responseXML->{'manifest-pricing-info'}->{'base-cost'});
                    $ManifestPricingInfoType->setAutomationDiscount((string)$responseXML->{'manifest-pricing-info'}->{'automation-discount'});
                    $ManifestPricingInfoType->setOptionsAndSurcharges((string)$responseXML->{'manifest-pricing-info'}->{'options-and-surcharges'});
                    $ManifestPricingInfoType->setGst((string)$responseXML->{'manifest-pricing-info'}->{'gst'});
                    $ManifestPricingInfoType->setPst((string)$responseXML->{'manifest-pricing-info'}->{'pst'});
                    $ManifestPricingInfoType->setHst((string)$responseXML->{'manifest-pricing-info'}->{'hst'});
                    $ManifestPricingInfoType->setTotalDueCpc((string)$responseXML->{'manifest-pricing-info'}->{'total-due-cpc'});

                    $ManifestType->setManifestPricingInfoType($ManifestPricingInfoType);

                    $this->setResponse($ManifestType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * @param ShipmentTransmitSetType $ShipmentTransmitSet
     * @return bool|MessagesType|ManifestsType
     */
    public function transmitShipments(ShipmentTransmitSetType $ShipmentTransmitSet)
    {
        $XmlTransmitSet = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><transmit-set xmlns="http://www.canadapost.ca/ws/manifest-v'.self::API_VERSION.'"/>');

        $XmlTransmitSetGroupIds = $XmlTransmitSet->addChild('group-ids');
        if (null !== $ShipmentTransmitSet->getGroupIds()) {
            foreach ($ShipmentTransmitSet->getGroupIds() as $GroupId) {
                $XmlTransmitSetGroupIds->addChild('group-id', $GroupId->getGroupId());
            }
        }

        if (true === $ShipmentTransmitSet->isCpcPickupIndicator()) {
            $XmlTransmitSet->addChild('cpc-pickup-indicator', ((int)$ShipmentTransmitSet->isCpcPickupIndicator() ? 'true' : 'false'));
        }

        if (null !== $ShipmentTransmitSet->getRequestedShippingPoint()) {
            $XmlTransmitSet->addChild('requested-shipping-point', $ShipmentTransmitSet->getRequestedShippingPoint());
        }

        if (null !== $ShipmentTransmitSet->getShippingPointId()) {
            $XmlTransmitSet->addChild('shipping-point-id', $ShipmentTransmitSet->getShippingPointId());
        }

        $XmlTransmitSet->addChild('detailed-manifests', ((int)$ShipmentTransmitSet->isDetailedManifests() ? 'true' : 'false'));
        $XmlTransmitSet->addChild('method-of-payment', $ShipmentTransmitSet->getMethodOfPayment());

        if (null !== $ShipmentTransmitSet->getManifestAddress()) {
            $XmlTransmitSetManifestAddress = $XmlTransmitSet->addChild('manifest-address');

            $XmlTransmitSetManifestAddress->addChild('manifest-company', $ShipmentTransmitSet->getManifestAddress()->getManifestCompany());

            if (null !== $ShipmentTransmitSet->getManifestAddress()->getManifestName()) {
                $XmlTransmitSetManifestAddress->addChild('manifest-name', $ShipmentTransmitSet->getManifestAddress()->getManifestName());
            }

            $XmlTransmitSetManifestAddress->addChild('phone-number', $ShipmentTransmitSet->getManifestAddress()->getPhoneNumber());

            if (null !== $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()) {
                $XmlTransmitSetManifestAddressAddressDetails = $XmlTransmitSetManifestAddress->addChild('address-details');
                $XmlTransmitSetManifestAddressAddressDetails->addChild('address-line-1', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getAddressLine1());

                if (null !== $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getAddressLine2()) {
                    $XmlTransmitSetManifestAddressAddressDetails->addChild('address-line-2', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getAddressLine2());
                }

                $XmlTransmitSetManifestAddressAddressDetails->addChild('city', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getCity());
                $XmlTransmitSetManifestAddressAddressDetails->addChild('prov-state', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getProvState());

                if (null !== $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getCountryCode()) {
                    $XmlTransmitSetManifestAddressAddressDetails->addChild('country-code', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getCountryCode());
                }

                $XmlTransmitSetManifestAddressAddressDetails->addChild('postal-zip-code', $ShipmentTransmitSet->getManifestAddress()->getAddressDetails()->getPostalZipCode());
            }
        }

        if (null !== $ShipmentTransmitSet->getCustomerReference()) {
            $XmlTransmitSet->addChild('customer-references', $ShipmentTransmitSet->getCustomerReference());
        }

        if (null !== $ShipmentTransmitSet->getExcludedShipments()) {
            $XmlTransmitSetExcludedShipments = $XmlTransmitSet->addChild('excluded-shipments');

            foreach ($ShipmentTransmitSet->getExcludedShipments() as $ExcludedShipment) {
                $XmlTransmitSetExcludedShipments->addChild('shipment-id', $ExcludedShipment->getShipmentId());
            }
        }

        $request = $XmlTransmitSet->asXML();

        $response = $this->processRequest(array(
            'request_url' => '/manifest',
            'headers' => array(
                'Content-Type: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.manifest-v'.self::API_VERSION.'+xml',
            ),
            'request' => $request,
        ));

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'manifests':
                $ManifestsType = new ManifestsType();

                if ($responseXML->link) {
                    foreach ($responseXML->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        $ManifestsType->addLink($LinkType);
                    }
                }

                $this->setResponse($ManifestsType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }


    /**
     * @param int $id
     * @param string $email
     *
     * @return bool|MessagesType|ShipmentRefundInfoType
     */
    public function processRefund($link, $email)
    {
        $XmlRefundRequestType = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><shipment-refund-request xmlns="http://www.canadapost.ca/ws/shipment-v'.self::API_VERSION.'"/>');
        $XmlRefundRequestType->addChild('email', $email);

        $request = $XmlRefundRequestType->asXML();


        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Content-Type: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
            'request' => $request,
        ));

        if (strpos($response, '<?xml') === 0) {
            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'shipment-refund-request-info':
                    $ShipmentRefundRequestInfoType = new ShipmentRefundInfoType();
                    $ShipmentRefundRequestInfoType->setServiceTicketDate((string)$responseXML->{'service-ticket-date'});
                    $ShipmentRefundRequestInfoType->setServiceTicketId((string)$responseXML->{'service-ticket-id'});
                    $this->setResponse($ShipmentRefundRequestInfoType);
                    break;
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * @param int $id
     * @param string $email
     *
     * @return bool|MessagesType|ShipmentRefundInfoType
     */
    public function processVoid($link)
    {
        $this->requestUrl = '';

        $response = $this->processRequest(array(
            'request_url' => $link,
            'headers' => array(
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
            'request' => 'delete',
        ));
//        $RequestProcessor = new RequestProcessor(array(
//            'request_url' => $link,
//            'headers' => array(
//                'Accept: application/vnd.cpc.shipment-v' . self::API_VERSION . '+xml',
//                'Platform-id: '.$this->options['platform_id']
//            ),
//            'request' => 'delete',
//            'api_key' => $this->options['api_key'],
//            'ssl' => $this->options['ssl'],
//        ));
//        $response = $RequestProcessor->process();

        // Void doesn't return any XML unless there's an error
        if ($response === true) {
            $this->setResponse(true);
        } else {
            $responseXML = new SimpleXMLElement($response);

            switch ($responseXML->getName()) {
                case 'messages':
                    $this->setResponse(WebService::getMessagesType($responseXML));
                    break;
                default:
                    return false;
            }
        }
        return $this->getResponse();
    }

    /*
     * @var string $link Link returned from getShipment()
     * @var string $fileName
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

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return bool|ShipmentsType|MessagesType
     */
    public function getGroups()
    {
        $response = $this->processRequest(array(
            'request_url' => '/group',
            'headers' => array(
                'Content-Type: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
                'Accept: application/vnd.cpc.shipment-v'.self::API_VERSION.'+xml',
            ),
        ));

        if ($this->isError()) {
            return false;
        }

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'groups':
                $GroupsType = new GroupsType();

                if ($responseXML->group) {
                    foreach ($responseXML->group as $group) {
                        $Group = new GroupType();
                        $Group->setGroupId($group->{'group-id'});
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$group->{'href'});
                        $LinkType->setRel((string)$group->{'rel'});
                        $LinkType->setMediaType((string)$group->{'media-type'});

                        if (isset($group->{'index'})) {
                            $LinkType->setIndex((string)$group->{'index'});
                        }

                        $Group->addLink($LinkType);
                        $GroupsType->addGroup($Group);
                    }
                }

                $this->setResponse($GroupsType);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
        return $this->getResponse();
    }
}
