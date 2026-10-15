<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

class WkCustomerReplySyncImap extends ObjectModel
{
    public $id;
    public $message_id;
    public $reply_id;
    public $id_ticket;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_customer_reply_sync_imap',
        'primary' => 'id',
        'fields' => array(
            'message_id' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'shop' => true,
                'size' => 256
            ),
            'id_ticket' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'shop' => true),
            'reply_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'shop' => true, 'size' => 256),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_customer_reply_sync_imap', array('type' => 'shop', 'primary' => 'id'));
    }

    public function getMessageId($messageId)
    {
        Shop::addTableAssociation('wk_customer_reply_sync_imap', array('type' => 'shop', 'primary' => 'id'));
        return Db::getInstance()->getRow(
            'SELECT crsi.message_id, crsi.id_ticket FROM `'._DB_PREFIX_.'wk_customer_reply_sync_imap` crsi '
            . WkHdGroup::addSqlAssociationCustom('wk_customer_reply_sync_imap', 'crsi')
            .' WHERE crsi.`message_id` like "'. $messageId .'" AND crsi.id_ticket != 0'
        );
    }
}
