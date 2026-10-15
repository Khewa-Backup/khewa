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

namespace CanadaPostWs\Type\Shipment;

class NotificationsType
{
    /*
     * @var NotificationType[]
     * name="notification" type="NotificationType" maxOccurs="20"
     */
    protected $notifications;

    /**
     * @return NotificationType[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param NotificationType[] $notifications
     * @return NotificationsType
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;

        return $this;
    }

    /**
     * @param NotificationType $notification
     * @return NotificationsType
     */
    public function addNotification($notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }
}
