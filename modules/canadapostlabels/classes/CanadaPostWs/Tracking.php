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
use CanadaPostWs\Type\Tracking\DeliveryOptionItemType;
use CanadaPostWs\Type\Tracking\PinSummaryType;
use CanadaPostWs\Type\Tracking\SignificantEventItemType;
use CanadaPostWs\Type\Tracking\TrackingDetailsType;
use CanadaPostWs\Type\Tracking\TrackingSummaryType;
use SimpleXMLElement;

class Tracking extends WebService
{
    const API_VERSION = '1';

    /**
     * WebService constructor.
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->requestUrl .= '/vis/track/pin/';
    }

    /**
     * @param string $pin
     * @return bool|MessagesType|TrackingDetailsType
     */
    public function getTrackingSummary($pin)
    {
        $response = $this->processRequest(array(
            'request_url' => $pin.'/summary',
            'headers' => array(
                'Accept:    application/vnd.cpc.track+xml',
            ),
        ));

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'tracking-summary':
                $TrackingSummary = new TrackingSummaryType();

                if ($responseXML->{'pin-summary'}) {
                    $PinSummaryType = new PinSummaryType();
                    $PinSummaryType->setPin((string)$responseXML->{'pin-summary'}->{'pin'});
                    $PinSummaryType->setOriginPostalId((string)$responseXML->{'pin-summary'}->{'origin-postal-id'});
                    $PinSummaryType->setDestinationPostalId((string)$responseXML->{'pin-summary'}->{'destination-postal-id'});
                    $PinSummaryType->setDestinationProvince((string)$responseXML->{'pin-summary'}->{'destination-province'});
                    $PinSummaryType->setServiceName((string)$responseXML->{'pin-summary'}->{'service-name'});
                    $PinSummaryType->setMailedOnDate((string)$responseXML->{'pin-summary'}->{'mailed-on-date'});
                    $PinSummaryType->setExpectedDeliveryDate((string)$responseXML->{'pin-summary'}->{'expected-delivery-date'});
                    $PinSummaryType->setActualDeliveryDate((string)$responseXML->{'pin-summary'}->{'actual-delivery-date'});
                    $PinSummaryType->setDeliveryOptionCompletedInd((string)$responseXML->{'pin-summary'}->{'delivery-option-completed-ind'});
                    $PinSummaryType->setEventDateTime((string)$responseXML->{'pin-summary'}->{'event-date-time'});
                    $PinSummaryType->setEventDescription((string)$responseXML->{'pin-summary'}->{'event-description'});
                    $PinSummaryType->setAttemptedDate((string)$responseXML->{'pin-summary'}->{'attempted-date'});
                    $PinSummaryType->setCustomerRef1((string)$responseXML->{'pin-summary'}->{'customer-ref-1'});
                    $PinSummaryType->setCustomerRef2((string)$responseXML->{'pin-summary'}->{'customer-ref-2'});
                    $PinSummaryType->setReturnPin((string)$responseXML->{'pin-summary'}->{'return-pin'});
                    $PinSummaryType->setEventType((string)$responseXML->{'pin-summary'}->{'event-type'});
                    $PinSummaryType->setEventLocation((string)$responseXML->{'pin-summary'}->{'event-location'});
                    $PinSummaryType->setSignatoryName((string)$responseXML->{'pin-summary'}->{'signatory-name'});

                    $TrackingSummary->setPinSummary($PinSummaryType);
                }

                $this->setResponse($TrackingSummary);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $pin
     * @return bool|MessagesType|TrackingDetailsType
     */
    public function getTrackingDetails($pin)
    {
        $response = $this->processRequest(array(
            'request_url' => $pin.'/detail',
            'headers' => array(
                'Accept:    application/vnd.cpc.track+xml',
            ),
        ));

        $responseXML = new SimpleXMLElement($response);

        switch ($responseXML->getName()) {
            case 'tracking-detail':
                $TrackingDetails = new TrackingDetailsType();

                $TrackingDetails->setActiveExists((string)$responseXML->{'active-exists'});
                $TrackingDetails->setArchiveExists((string)$responseXML->{'archive-exists'});
                $TrackingDetails->setChangedExpectedDate((string)$responseXML->{'changed-expected-date'});
                $TrackingDetails->setChangedExpectedDeliveryReason((string)$responseXML->{'changed-expected-delivery-reason'});
                $TrackingDetails->setCustomerRef1((string)$responseXML->{'customer-ref-1'});
                $TrackingDetails->setCustomerRef2((string)$responseXML->{'customer-ref-2'});
                $TrackingDetails->setDestinationPostalId((string)$responseXML->{'destination-postal-id'});
                $TrackingDetails->setDuplicateFlagInd((string)$responseXML->{'duplicate-flag-ind'});
                $TrackingDetails->setExpectedDeliveryDate((string)$responseXML->{'expected-delivery-date'});
                $TrackingDetails->setMailedByCustomerNumber((string)$responseXML->{'mailed-by-customer-number'});
                $TrackingDetails->setMailedOnBehalfOfCustomerNumber((string)$responseXML->{'mailed-on-behalf-of-customer-number'});
                $TrackingDetails->setOriginalPin((string)$responseXML->{'original-pin'});
                $TrackingDetails->setPin((string)$responseXML->{'pin'});
                $TrackingDetails->setReturnPin((string)$responseXML->{'return-pin'});
                $TrackingDetails->setServiceName((string)$responseXML->{'service-name'});
                $TrackingDetails->setServiceName2((string)$responseXML->{'service-name-2'});
                $TrackingDetails->setSignatureImageExists((string)$responseXML->{'signature-image-exists'});
                $TrackingDetails->setSuppressSignature((string)$responseXML->{'suppress-signature'});

                if ($responseXML->{'delivery-options'}->item) {
                    foreach ($responseXML->{'delivery-options'}->item as $item) {
                        $DeliveryOptionItem = new DeliveryOptionItemType();

                        $DeliveryOptionItem->setDeliveryOption((string)$item->{'delivery-option'});
                        $DeliveryOptionItem->setDeliveryOptionDescription((string)$item->{'delivery-option-description'});

                        $TrackingDetails->addDeliveryOption($DeliveryOptionItem);
                    }
                }

                if ($responseXML->{'significant-events'}->occurrence) {
                    foreach ($responseXML->{'significant-events'}->occurrence as $item) {
                        $SignificantEvent = new SignificantEventItemType();

                        $SignificantEvent->setEventDate((string)$item->{'event-date'});
                        $SignificantEvent->setEventDescription((string)$item->{'event-description'});
                        $SignificantEvent->setEventIdentifier((string)$item->{'event-identifier'});
                        $SignificantEvent->setEventProvince((string)$item->{'event-province'});
                        $SignificantEvent->setEventRetailLocationId((string)$item->{'event-retail-location-id'});
                        $SignificantEvent->setEventRetailName((string)$item->{'event-retail-name'});
                        $SignificantEvent->setEventSite((string)$item->{'event-site'});
                        $SignificantEvent->setEventTime((string)$item->{'event-time'});
                        $SignificantEvent->setEventTimeZone((string)$item->{'event-time-zone'});
                        $SignificantEvent->setSignatoryName((string)$item->{'signatory-name'});

                        $TrackingDetails->addEvent($SignificantEvent);
                    }
                }

                $this->setResponse($TrackingDetails);
                break;
            case 'messages':
                $this->setResponse(WebService::getMessagesType($responseXML));
                break;
            default:
                return false;
        }
    }
}
