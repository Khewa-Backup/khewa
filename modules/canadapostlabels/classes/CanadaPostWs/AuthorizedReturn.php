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
use CanadaPostWs\Type\Messages\MessagesType;
use CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnType;
use CanadaPostWs\Type\AuthorizedReturn\AuthorizedReturnInfoType;
use SimpleXMLElement;

class AuthorizedReturn extends WebService
{
    const API_VERSION = '2';

    /**
     * WebService constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $mailedBy = $this->options['api_customer_number'];
        $mobo = $this->options['api_customer_number'];

        $this->requestUrl .= '/rs/'.$mailedBy.(array_key_exists('platform_id', $this->options) ? '-'.$this->options['platform_id'] : '').'/'.$mobo;
    }

    /**
     * @param string $pin
     * @return bool|MessagesType|AuthorizedReturnInfoType
     */
    public function createAuthorizedReturn(AuthorizedReturnType $AuthorizedReturn)
    {
        $XmlAuthorizedReturn = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><authorized-return xmlns="http://www.canadapost.ca/ws/authreturn-v'.self::API_VERSION.'"/>');

        $XmlAuthorizedReturn->addChild('service-code', $AuthorizedReturn->getServiceCode());

        // Returner
        $XmlAuthorizedReturnReturner = $XmlAuthorizedReturn->addChild('returner');
        $XmlAuthorizedReturnReturner->addChild('name', $AuthorizedReturn->getReturner()->getName());

        if (null !== $AuthorizedReturn->getReturner()->getCompany()) {
            $XmlAuthorizedReturnReturner->addChild('company', $AuthorizedReturn->getReturner()->getCompany());
        }

        $XmlAuthorizedReturnReturnerDomesticAddress = $XmlAuthorizedReturnReturner->addChild('domestic-address');
        $XmlAuthorizedReturnReturnerDomesticAddress->addChild('address-line-1', $AuthorizedReturn->getReturner()->getAddressDetails()->getAddressLine1());

        if (null !== $AuthorizedReturn->getReturner()->getAddressDetails()->getAddressLine2()) {
            $XmlAuthorizedReturnReturnerDomesticAddress->addChild('address-line-2', $AuthorizedReturn->getReturner()->getAddressDetails()->getAddressLine2());
        }

        $XmlAuthorizedReturnReturnerDomesticAddress->addChild('city', $AuthorizedReturn->getReturner()->getAddressDetails()->getCity());
        $XmlAuthorizedReturnReturnerDomesticAddress->addChild('province', $AuthorizedReturn->getReturner()->getAddressDetails()->getProvState());
        $XmlAuthorizedReturnReturnerDomesticAddress->addChild('postal-code', $AuthorizedReturn->getReturner()->getAddressDetails()->getPostalZipCode());

        // Receiver
        $XmlAuthorizedReturnReceiver = $XmlAuthorizedReturn->addChild('receiver');
        $XmlAuthorizedReturnReceiver->addChild('name', $AuthorizedReturn->getReceiver()->getName());

        if (null !== $AuthorizedReturn->getReceiver()->getCompany()) {
            $XmlAuthorizedReturnReceiver->addChild('company', $AuthorizedReturn->getReceiver()->getCompany());
        }

        if (null !== $AuthorizedReturn->getReceiver()->getEmail()) {
            $XmlAuthorizedReturnReceiver->addChild('email', $AuthorizedReturn->getReceiver()->getEmail());
        }

        if (null !== $AuthorizedReturn->getReceiver()->getReceiverVoiceNumber()) {
            $XmlAuthorizedReturnReceiver->addChild('receiver-voice-number', $AuthorizedReturn->getReceiver()->getReceiverVoiceNumber());
        }

        $XmlAuthorizedReturnReceiverDomesticAddress = $XmlAuthorizedReturnReceiver->addChild('domestic-address');
        $XmlAuthorizedReturnReceiverDomesticAddress->addChild('address-line-1', $AuthorizedReturn->getReceiver()->getAddressDetails()->getAddressLine1());

        if (null !== $AuthorizedReturn->getReceiver()->getAddressDetails()->getAddressLine2()) {
            $XmlAuthorizedReturnReceiverDomesticAddress->addChild('address-line-2', $AuthorizedReturn->getReceiver()->getAddressDetails()->getAddressLine2());
        }

        $XmlAuthorizedReturnReceiverDomesticAddress->addChild('city', $AuthorizedReturn->getReceiver()->getAddressDetails()->getCity());
        $XmlAuthorizedReturnReceiverDomesticAddress->addChild('province', $AuthorizedReturn->getReceiver()->getAddressDetails()->getProvState());
        $XmlAuthorizedReturnReceiverDomesticAddress->addChild('postal-code', $AuthorizedReturn->getReceiver()->getAddressDetails()->getPostalZipCode());

        // Parcel
        if (null !== $AuthorizedReturn->getParcelCharacteristics()) {
            $XmlAuthorizedReturnParcelCharacteristics = $XmlAuthorizedReturn->addChild('parcel-characteristics');
            if (null !== $AuthorizedReturn->getParcelCharacteristics()->getWeight()) {
                $XmlAuthorizedReturnParcelCharacteristics->addChild('weight', $AuthorizedReturn->getParcelCharacteristics()->getWeight());
            }
            if (null !== $AuthorizedReturn->getParcelCharacteristics()->getDimensions()) {
                $XmlAuthorizedReturnParcelCharacteristicsDimensions = $XmlAuthorizedReturnParcelCharacteristics->addChild('dimensions');
                $XmlAuthorizedReturnParcelCharacteristicsDimensions->addChild('length', $AuthorizedReturn->getParcelCharacteristics()->getDimensions()->getLength());
                $XmlAuthorizedReturnParcelCharacteristicsDimensions->addChild('width', $AuthorizedReturn->getParcelCharacteristics()->getDimensions()->getWidth());
                $XmlAuthorizedReturnParcelCharacteristicsDimensions->addChild('height', $AuthorizedReturn->getParcelCharacteristics()->getDimensions()->getHeight());
            }
        }

        if (null !== $AuthorizedReturn->getPrintPreferences()) {
            $XmlAuthorizedReturnPrintPreferences = $XmlAuthorizedReturn->addChild('print-preferences');

            if (null !== $AuthorizedReturn->getPrintPreferences()->getOutputFormat()) {
                $XmlAuthorizedReturnPrintPreferences->addChild('output-format', $AuthorizedReturn->getPrintPreferences()->getOutputFormat());
            }

            if (null !== $AuthorizedReturn->getPrintPreferences()->getEncoding()) {
                $XmlAuthorizedReturnPrintPreferences->addChild('encoding', $AuthorizedReturn->getPrintPreferences()->getEncoding());
            }
        }
        
        $XmlAuthorizedReturnSettlementInfo = $XmlAuthorizedReturn->addChild('settlement-info');

        if (null !== $AuthorizedReturn->getSettlementInfo()->getPaidByCustomer()) {
            $XmlAuthorizedReturnSettlementInfo->addChild('paid-by-customer', $AuthorizedReturn->getSettlementInfo()->getPaidByCustomer());
        }

        if (null !== $AuthorizedReturn->getSettlementInfo()->getContractId()) {
            $XmlAuthorizedReturnSettlementInfo->addChild('contract-id', $AuthorizedReturn->getSettlementInfo()->getContractId());
        }


        if (null !== $AuthorizedReturn->getReferences()) {
            $XmlAuthorizedReturnReferences = $XmlAuthorizedReturn->addChild('references');

            if (null !== $AuthorizedReturn->getReferences()->getCustomerRef1()) {
                $XmlAuthorizedReturnReferences->addChild('customer-ref-1', $AuthorizedReturn->getReferences()->getCustomerRef1());
            }

            if (null !== $AuthorizedReturn->getReferences()->getCustomerRef2()) {
                $XmlAuthorizedReturnReferences->addChild('customer-ref-2', $AuthorizedReturn->getReferences()->getCustomerRef2());
            }
        }

        if (null !== $AuthorizedReturn->getNotifications()) {
            $NotificationsList = $AuthorizedReturn->getNotifications();

            if ($NotificationsList) {
                $Notifications = $NotificationsList->getNotifications();

                if ($Notifications) {
                    $XmlAuthorizedReturnNotifications = $XmlAuthorizedReturn->addChild('notifications');

                    foreach ($Notifications as $Notification) {
                        $XmlAuthorizedReturnNotificationsNotification = $XmlAuthorizedReturnNotifications->addChild('notification');
                        $XmlAuthorizedReturnNotificationsNotification->addChild('email', (string)$Notification->getEmail());
                        $XmlAuthorizedReturnNotificationsNotification->addChild(
                            'on-shipment',
                            ((int)$Notification->isOnShipment() ? 'true' : 'false')
                        );
                        $XmlAuthorizedReturnNotificationsNotification->addChild(
                            'on-exception',
                            ((int)$Notification->isOnException() ? 'true' : 'false')
                        );
                        $XmlAuthorizedReturnNotificationsNotification->addChild(
                            'on-delivery',
                            ((int)$Notification->isOnDelivery() ? 'true' : 'false')
                        );
                    }
                }
            }
        }

        $request = $XmlAuthorizedReturn->asXML();

        $response = $this->processRequest(array(
            'request_url' => '/authorizedreturn',
            'headers' => array(
                'Accept:    application/vnd.cpc.authreturn-v2+xml',
                'Content-Type:    application/vnd.cpc.authreturn-v2+xml',
            ),
            'request' => $request,
        ));

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'authorized-return-info':
                $AuthorizedReturnInfo = new AuthorizedReturnInfoType();

                $AuthorizedReturnInfo->setTrackingPin((string)$responseXML->{'tracking-pin'});
                if ($responseXML->{'links'}->link) {
                    foreach ($responseXML->{'links'}->link as $link) {
                        $LinkType = new LinkType();

                        $LinkType->setHref((string)$link['href']);
                        $LinkType->setRel((string)$link['rel']);
                        $LinkType->setMediaType((string)$link['media-type']);

                        if (isset($link['index'])) {
                            $LinkType->setIndex((string)$link['index']);
                        }

                        switch ($LinkType->getRel()) {
                            case 'returnLabel':
                                $AuthorizedReturnInfo->setReturnLabelLink($LinkType);
                                break;
                            default:
                                break;
                        }

                        $AuthorizedReturnInfo->addLink($LinkType);
                    }
                }

                $this->setResponse($AuthorizedReturnInfo);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }
}
