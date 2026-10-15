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

namespace CanadaPostWs\Type\Messages;

class MessagesType
{
    /**
     * @var MessageType[]
     * name="message" minOccurs="0" maxOccurs="unbounded"
     */
    protected $messages = array();

    /**
     * @return MessageType[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param MessageType[] $messages
     * @return MessagesType
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param MessageType $message
     * @return MessagesType
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;

        return $this;
    }
}
