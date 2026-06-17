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

class WkHdTicketMsg extends ObjectModel
{
    public $id;
    public $hd_id_ticket;
    public $message;
    public $id_customer;
    public $id_agent;
    public $is_internal_note;
    public $is_status_update;
    public $status_from;
    public $status_to;
    public $is_agent_assign;
    public $agent_from;
    public $agent_to;
    public $date_add;

    public static $definition = array(
        'table' => 'wk_hd_ticket_msg',
        'primary' => 'id',
        'fields' => array(
            'hd_id_ticket' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'message' => array('type' => self::TYPE_HTML, /* 'validate' => 'isCleanHtml' */),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_agent' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'is_internal_note' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_status_update' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'status_from' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'status_to' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'is_agent_assign' => array('type' => self::TYPE_INT),
            'agent_from' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'agent_to' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false)
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket_msg', array('type' => 'shop', 'primary' => 'id'));
    }
}
