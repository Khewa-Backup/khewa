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
if (!defined('_PS_VERSION_')) {
    exit;
}
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

    public static $definition = [
        'table' => 'wk_hd_ticket_msg',
        'primary' => 'id',
        'fields' => [
            'hd_id_ticket' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'message' => ['type' => self::TYPE_HTML/* 'validate' => 'isCleanHtml' */],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_agent' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'is_internal_note' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_status_update' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'status_from' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'status_to' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'is_agent_assign' => ['type' => self::TYPE_INT],
            'agent_from' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'agent_to' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket_msg', ['type' => 'shop', 'primary' => 'id']);
    }
}
