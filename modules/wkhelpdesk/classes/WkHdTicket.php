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

class WkHdTicket extends ObjectModel
{
    public $id;
    public $hd_id_customer;
    public $id_query_type;
    public $first_name;
    public $last_name;
    public $assigned_agent_id;
    public $id_status;
    public $subject;
    public $id_order;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_hd_ticket',
        'primary' => 'id',
        'fields' => array(
            'hd_id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_query_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'first_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true,'size' => 32),
            'last_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'assigned_agent_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_status' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'subject' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isMailSubject',
                'required' => true,
                'size' => 255
            )
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_ticket', array('type' => 'shop', 'primary' => 'id'));
    }

    public function getTicketsByIdQueryType($idQueryType)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket` wht'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wht').'
            WHERE `id_query_type` = '.(int) $idQueryType.' GROUP BY wht.`id`'
        );
    }

    public function updateAssignedAgent($id, $assignedAgentId)
    {
        $obj = new self((int) $id);
        $obj->assigned_agent_id = (int) $assignedAgentId;
        return $obj->save();
    }

    public function getInfoById($id)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket` wht'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wht').'
            WHERE wht.`id` = '.(int) $id.' GROUP BY wht.`id`'
        );
    }

    public static function setSelectedFileExtension($selectedFileExt)
    {
        $result = false;
        if ($selectedFileExt) {
            $allFileExt = Tools::jsonDecode(Configuration::get('WK_HD_ATTACHMENT_TYPE'), true);
            foreach ($allFileExt as &$fileExt) {
                $isAvailble = 0;
                foreach ($selectedFileExt as $selectFileExt) {
                    if ($selectFileExt == $fileExt['ext_name']) {
                        $isAvailble = 1;
                    }
                }
                $fileExt['is_availble'] = $isAvailble;
            }
            $result = Configuration::updateValue('WK_HD_ATTACHMENT_TYPE', Tools::jsonEncode($allFileExt));
        }

        return $result;
    }

    public static function getSelectedFileExtension()
    {
        $fileExt = WkHdTicket::getAllFileExtension();
        $selectedFileExt = array();
        foreach ($fileExt as $file) {
            if ($file['is_availble']) {
                $selectedFileExt[] = $file['ext_name'];
            }
        }

        return $selectedFileExt;
    }

    public static function getAllFileExtension()
    {
        return Tools::jsonDecode(Configuration::get('WK_HD_ATTACHMENT_TYPE'), true);
    }

    public function getAllTicketByIdCustomerAndIdLang($idCustomer, $idLang)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.*, wkc.`first_name`, wkc.`last_name` , wkc.`email`, wkqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_query_type` wkqt
            ON (wkt.`id_query_type` = wkqt.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` wkqtl
            ON (wkqtl.`id` = wkqt.`id`)
            WHERE wkqtl.`id_lang` = '.(int) $idLang.'
            AND wkc.`id_ps_customer` = '.(int) $idCustomer.' GROUP BY wkt.`id`'
        );
    }

    public function getAllTicketByCustomerMailAndIdLang($idCustomer, $idLang)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.*, wkc.`first_name`, wkc.`last_name` , wkc.`email`, wkqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_query_type` wkqt
            ON (wkt.`id_query_type` = wkqt.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` wkqtl
            ON (wkqtl.`id` = wkqt.`id`)
            WHERE wkqtl.`id_lang` = '.(int) $idLang.'
            AND wkc.`email` = \''.pSQL($idCustomer).'\' GROUP BY wkt.`id`'
        );
    }

    public function getTicketConversationByIdTicketAndIdCustomer($id, $idCustomer)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.`subject`, wkt.`first_name`, wkt.`last_name` , wkt.`id` as `ticket_id`, wktm.*
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_msg` wktm
            ON (wkt.`id` = wktm.`hd_id_ticket`)
            WHERE wkc.`id_ps_customer` = '.(int) $idCustomer.'
            AND wkt.`id` = '.(int) $id.'
            ORDER BY wktm.`id` ASC'
        );
    }

    public function getTicketConversationByIdTicketAndToken($id, $token)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.`subject`, wkc.`first_name`, wkc.`last_name` , wkt.`id` as `ticket_id`, wktm.*
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_token` wktt
            ON (wkt.`id` = wktt.`hd_id_ticket`)
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_msg` wktm
            ON (wkt.`id` = wktm.`hd_id_ticket`)
            WHERE wktt.`token` = \''.pSQL($token).'\'
            AND wkt.`id` = '.(int) $id.'
            ORDER BY wktm.`id` ASC'
        );
    }

    public static function validateTicketMainAttachment($ticketAttachment)
    {
        if ($ticketAttachment['size'] > 0) {
            if ($ticketAttachment['tmp_name'] != '') {
                $supportedFileExt = WkHdTicket::getSelectedFileExtension();
                if (ImageManager::isCorrectImageFileExt(
                    $ticketAttachment['name'],
                    $supportedFileExt
                )
                && mime_content_type($ticketAttachment['tmp_name']) == $ticketAttachment['type']
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function validateTicketOtherAttachment($ticketOtherAttachment)
    {
        $supportedFileExt = WkHdTicket::getSelectedFileExtension();
        foreach ($ticketOtherAttachment['size'] as $key => $attachmentSize) {
            if ($attachmentSize > 0) {
                if ($ticketOtherAttachment['tmp_name'][$key] != '') {
                    if (!ImageManager::isCorrectImageFileExt(
                        $ticketOtherAttachment['name'][$key],
                        $supportedFileExt
                    )) {
                        return false;
                    }
                    if (mime_content_type($ticketOtherAttachment['tmp_name'][$key])
                        != $ticketOtherAttachment['type'][$key]
                    ) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function getTicketDetailsByIdAndIdLang($id, $idLang)
    {
        return Db::getInstance()->getRow(
            'SELECT hdt.`subject`, wkc.`first_name`, wkc.`last_name` , hdt.`hd_id_customer`, wkc.is_spam,
            hdt.`date_add`, hdt.`assigned_agent_id`, hdt.`id` as `ticket_id`,
            hdt.`id_status`, hdt.`id_order`, hdqtl.`query_name`, hdtm.`id` as `msg_id`,
            hdta.`name` as `assigned_agent_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (hdt.`hd_id_customer` = wkc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type` hdqt
            ON (hdt.`id_query_type` = hdqt.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
            ON (hdqtl.`id` = hdqt.`id` AND hdqtl.`id_lang` = '.(int) $idLang.')
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_ticket_msg` hdtm
            ON (hdt.`id` = hdtm.`hd_id_ticket`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_ticket_agent` hdta
            ON (hdt.`assigned_agent_id` = hdta.`id`)
            WHERE hdt.`id` = '.(int) $id
        );
    }

    public static function getToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
        // 16 is length of token
    }

    public function getTicketsByIdAgent($idAgent, $idLang, $idTicket = false)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.*, wk_hd_ticket_shop.id_shop as id_shop,
            CONCAT(hdt.`first_name`,\''.' '.'\', hdt.`last_name`) customer_name,
            hdc.`email`, hdc.is_spam, hdc.`id` as `hd_customer_id`, hdqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type` hdqt
            ON (hdt.`id_query_type` = hdqt.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
            ON (hdqt.`id` = hdqtl.`id` AND hdqtl.`id_lang` = '.(int) $idLang.')
            WHERE hdc.is_spam = 0 AND'.($idTicket ? ' hdt.id ='.(int)$idTicket : ' hdt.`id_query_type`
            IN (
                SELECT `id_query_type`
                FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdgqt'
                .WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'hdgqt').'
                WHERE hdgqt.`id_group` IN (
                    SELECT `id_group`
                    FROM `'._DB_PREFIX_.'wk_hd_group_agent_mapping` wkhdgam'
                    .WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'wkhdgam').'
                    JOIN `'._DB_PREFIX_.'wk_hd_group` wkhdg
                    ON (wkhdgam.`id_group` = wkhdg.`id`
                    AND wkhdg.`active` = 1)
                )
            )').
            ($idTicket ? ' ' : 'OR hdt.`assigned_agent_id` = '.(int) $idAgent).'
            GROUP BY hdt.`id` ORDER BY hdt.`id_query_type` ASC'
        );
    }

    public function getSpamTicketsByIdAgent($idAgent, $idLang, $idTicket = false)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.*, wk_hd_ticket_shop.id_shop as id_shop,
            CONCAT(hdt.`first_name`,\''.' '.'\', hdt.`last_name`) customer_name,
            hdc.`email`, hdc.is_spam, hdc.`id` as `hd_customer_id`, hdqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type` hdqt
            ON (hdt.`id_query_type` = hdqt.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
            ON (hdqt.`id` = hdqtl.`id` AND hdqtl.`id_lang` = '.(int) $idLang.')
            WHERE hdc.is_spam = 1 AND'.($idTicket ? ' hdt.id ='.(int)$idTicket : ' hdt.`id_query_type`
            IN (
                SELECT `id_query_type`
                FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdgqt'
                .WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'hdgqt').'
                WHERE hdgqt.`id_group` IN (
                    SELECT `id_group`
                    FROM `'._DB_PREFIX_.'wk_hd_group_agent_mapping` wkhdgam'
                    .WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'wkhdgam').'
                    JOIN `'._DB_PREFIX_.'wk_hd_group` wkhdg
                    ON (wkhdgam.`id_group` = wkhdg.`id`
                    AND wkhdgam.`id_agent` = '.(int) $idAgent.'
                    AND wkhdg.`active` = 1)
                )
            )').
            ($idTicket ? ' ' : 'OR hdt.`assigned_agent_id` = '.(int) $idAgent).'
            GROUP BY hdt.`id` ORDER BY hdt.`id_query_type` ASC'
        );
    }

    public function getTicketsForSuperAdmin($idLang, $idTicket = false)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.*, wk_hd_ticket_shop.id_shop as id_shop,
            CONCAT(hdt.`first_name`,\''.' '.'\',hdt.`last_name`) customer_name,
            hdc.`email`,hdc.is_spam, hdc.`id` as `hd_customer_id`, hdqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type` hdqt
            ON (hdt.`id_query_type` = hdqt.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
            ON (hdqt.`id` = hdqtl.`id` AND hdqtl.`id_lang` = '.(int) $idLang.')
            WHERE '.($idTicket ? ' hdt.id ='.(int) $idTicket.' AND ' : '').'hdc.is_spam = 0
            GROUP BY hdt.`id`
            ORDER BY hdt.`id_query_type` ASC'
        );
    }

    public function getSpamTicketsForSuperAdmin($idLang, $idTicket = false)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.*, wk_hd_ticket_shop.id_shop as id_shop,
            CONCAT(hdt.`first_name`,\''.' '.'\',hdt.`last_name`) customer_name,
            hdc.`email`,hdc.is_spam, hdc.`id` as `hd_customer_id`, hdqtl.`query_name`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type` hdqt
            ON (hdt.`id_query_type` = hdqt.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
            ON (hdqt.`id` = hdqtl.`id` AND hdqtl.`id_lang` = '.(int) $idLang.')
            WHERE '.($idTicket ? ' hdt.id ='.(int)$idTicket.' AND ' : '').'hdc.is_spam = 1
            GROUP BY hdt.`id`
            ORDER BY hdt.`id_query_type` ASC'
        );
    }

    public function getTicketsByIdTicketAndIdAgent($idTicket, $idAgent)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.`id`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdgqtm
            ON (hdt.`id_query_type` = hdgqtm.`id_query_type`)
            JOIN `'._DB_PREFIX_.'wk_hd_group_agent_mapping` hdgam
            ON (hdgam.`id_group` = hdgqtm.`id_group`)
            WHERE hdt.`assigned_agent_id` = '.(int) $idAgent.'
            OR hdgam.`id_agent` = '.(int) $idAgent.'
            AND hdt.`id` LIKE \''.(int) $idTicket.'%\' AND hdc.is_spam = 0 GROUP BY hdt.`id`'
        );
    }

    public function getSpamTicketsByIdTicketAndIdAgent($idTicket, $idAgent)
    {
        return Db::getInstance()->executeS(
            'SELECT hdt.`id`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            JOIN `'._DB_PREFIX_.'wk_hd_customer` hdc
            ON(hdt.`hd_id_customer` = hdc.`id`)
            JOIN `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdgqtm
            ON (hdt.`id_query_type` = hdgqtm.`id_query_type`)
            JOIN `'._DB_PREFIX_.'wk_hd_group_agent_mapping` hdgam
            ON (hdgam.`id_group` = hdgqtm.`id_group`)
            WHERE hdt.`assigned_agent_id` = '.(int) $idAgent.'
            OR hdgam.`id_agent` = '.(int) $idAgent.'
            AND hdt.`id` LIKE \''.(int) $idTicket.'%\' AND hdc.is_spam = 1 GROUP BY hdt.`id`'
        );
    }

    public function getTicketsForSuperAdminByIdTicket($idTicket)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.`id`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            WHERE wkt.`id` LIKE \''.(int) $idTicket.'%\' AND wkc.is_spam = 0 GROUP BY wkt.`id`'
        );
    }

    public function getSpamTicketsForSuperAdminByIdTicket($idTicket)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.`id`
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            WHERE wkt.`id` LIKE \''.(int) $idTicket.'%\' AND wkc.is_spam = 1 GROUP BY wkt.`id`'
        );
    }

    public function checkAgentTicketAccessRight($idAgent, $idTicket)
    {
        return Db::getInstance()->getValue(
            'SELECT hdt.`id` FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'hdt').'
            WHERE (hdt.`assigned_agent_id` = '.(int) $idAgent.'
            AND hdt.`id` = '.(int) $idTicket.')
            OR hdt.`id` = (
            SELECT hdt.`id` FROM `'._DB_PREFIX_.'wk_hd_ticket` hdt
            JOIN `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdgqtm
            ON (hdgqtm.`id_query_type` = hdt.`id_query_type`)
            JOIN `'._DB_PREFIX_.'wk_hd_group_agent_mapping` hdgam
            ON (hdgam.`id_group` = hdgqtm.`id_group`)
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_agent` hdta
            ON (hdta.`id` = hdgam.`id_agent`)
            WHERE hdta.`id` = '.(int) $idAgent.'
            AND hdt.`id` = '.(int) $idTicket.') GROUP BY hdt.`id`'
        );
    }

    public function getTicketConversationByIdTicket($id)
    {
        return Db::getInstance()->executeS(
            'SELECT wkt.`subject`, wkt.`id` as `ticket_id`, wktm.*,
            wkt.`first_name`, wkt.`last_name`, wkta.`name`, wkta.`email`, wkc.id_ps_customer
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_msg` wktm
            ON (wkt.`id` = wktm.`hd_id_ticket`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_customer` wkc
            ON (wkt.`hd_id_customer` = wkc.`id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_hd_ticket_agent` wkta
            ON (wktm.`id_agent` = wkta.`id`)
            WHERE wkt.`id` = '.(int) $id.'
            ORDER BY wktm.`id` ASC'
        );
    }

    public static function insertLangIdinAllTables($newIdLang, $langTables)
    {
        $lang_id = Configuration::get('PS_LANG_DEFAULT');
        if ($langTables) {
            foreach ($langTables as $tables) {
                $tableIdArr = Db::getInstance()->executeS(
                    'SELECT `id` FROM `'._DB_PREFIX_.$tables.'` '
                );
                if ($tableIdArr) {
                    foreach ($tableIdArr as $table_id) {
                        $tableLangArr = Db::getInstance()->getRow(
                            'SELECT * FROM `'._DB_PREFIX_.$tables.'_lang`
                            WHERE `id` = '.$table_id['id'].' AND `id_lang` = '.(int) $lang_id
                        );

                        if ($tableLangArr) {
                            $table_all_val = '';
                            foreach ($tableLangArr as $table_key => $table_val) {
                                if ($table_key == 'id') {
                                    $table_all_val = "'".(int)$table_val."'";
                                } elseif ($table_key == 'id_lang') {
                                    $table_all_val = $table_all_val.', '."'".(int)$newIdLang."'";
                                } else {
                                    $content = str_replace("'", "\'", pSQL($table_val));
                                    $table_all_val = $table_all_val.', '."'".pSQL($content)."'";
                                }
                            }
                        }
                        // we already used pSQL() and int for type casting when we creating this string $table_all_val.
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.$tables.'_lang` VALUES ('.$table_all_val.')'
                        );
                    }
                }
            }
        }
    }

    public static function getOrder($reference)
    {
        $idOrder = 0;
        if ($reference) {
            $reference = ltrim($reference, '#');
            $orders = Order::getByReference($reference);
            if ($orders) {
                foreach ($orders as $order) {
                    $idOrder = (int)$order->id;
                    break;
                }
            }
        }

        return (int)$idOrder;
    }

    public function getTicketByOrderId($idOrder)
    {
        return Db::getInstance()->executeS(
            'SELECT *
            FROM `'._DB_PREFIX_.'wk_hd_ticket` wkt'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_ticket', 'wkt').'
            WHERE wkt.`id_order` = '.(int) $idOrder
        );
    }
}
