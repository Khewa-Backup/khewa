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

class WkHdGroup extends ObjectModel
{
    public $id;
    public $is_default_group;
    public $active;
    public $date_add;
    public $date_upd;
    public $group_name;

    public static $definition = array(
        'table' => 'wk_hd_group',
        'primary' => 'id',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'is_default_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
             /* Lang fields */
            'group_name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => true,
                'size' => 128,
            ),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_group', array('type' => 'shop', 'primary' => 'id'));
        Shop::addTableAssociation('wk_hd_group_lang', array('type' => 'fk_shop'));
    }

    public function delete()
    {
        $obj_group_query_mapping = new WkHdGroupQueryTypeMapping();
        $obj_group_query_mapping->deleteMappingByIdGroup($this->id);

        return parent::delete();
    }

    public function getGroupInfoByIdGroup($id, $id_lang = false)
    {
        if ($id) {
            if ($id_lang) {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group` hdg
                    JOIN `'._DB_PREFIX_.'wk_hd_group_lang` hdgl
                    ON (hdg.`id` = hdgl.`id` AND hdgl.`id_lang` = '.(int) $id_lang.' '
                    .Shop::addSqlRestrictionOnLang('hdgl') . ') '
                    . WkHdGroup::addSqlAssociationCustom('wk_hd_group', 'hdg') . ' WHERE hdg.`id` = '.(int) $id;
            } else {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group` hdg '
                    . WkHdGroup::addSqlAssociationCustom('wk_hd_group', 'hdg') . ' WHERE hdg.`id` = '.(int) $id;
            }
            $sql .= ' GROUP BY hdg.`id`';

            return Db::getInstance()->getRow($sql);
        }

        return false;
    }

    public function getAllGroupInfoByIdLang($id_lang)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group` hdg
            JOIN `'._DB_PREFIX_.'wk_hd_group_lang` hdgl
            ON(hdgl.`id` = hdg.`id` AND hdgl.`id_lang` = '.(int) $id_lang.' AND hdg.`is_default_group` = 0 '.
            Shop::addSqlRestrictionOnLang('hdgl').') '.WkHdGroup::addSqlAssociationCustom('wk_hd_group', 'hdg')
            .' GROUP BY hdg.`id`'
        );
    }

    public function getGroupLangInfoByIdGroup($id)
    {
        if ($id) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group_lang` hdgl
                WHERE hdgl.`id` = '.(int) $id.Shop::addSqlRestrictionOnLang('hdgl')
            );
        }

        return false;
    }

    public static function createTable()
    {
        $success = true;
        $db = Db::getInstance();
        $queries = self::getDbTableQueries();

        foreach ($queries as $query) {
            $success &= $db->execute($query);
        }

        return $success;
    }

    private static function getDbTableQueries()
    {
        return array(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_code_lang` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                `id_lang` int(10) unsigned NOT NULL,
                `ticket_status` varchar(50) character set utf8 NOT NULL,
                PRIMARY KEY (`id`, `id_lang`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_mapping` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_status` int(10) unsigned NOT NULL,
                `id_status_selected` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_status_mapping_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                `id_status` int(10) unsigned NOT NULL,
                `id_status_selected` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_access_right` (
                `id` int(10) unsigned NOT NULL,
                `access_right_text` enum('create', 'delete', 'update', 'assign', 'remove'),
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_agent` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(10) unsigned NOT NULL,
                `name` varchar(255) character set utf8 NOT NULL,
                `email` varchar(128) character set utf8 NOT NULL,
                `is_super_admin`  tinyint(1) unsigned NOT NULL,
                `active`  tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_agent_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_access_right_agent_mapping` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_agent` int(10) unsigned NOT NULL,
                `id_access_right` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_access_right_agent_mapping_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `is_default_group` tinyint(1) unsigned NOT NULL,
                `active`  tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_lang` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                `id_lang` int(10) unsigned NOT NULL,
                `group_name` varchar(128) character set utf8 NOT NULL,
                PRIMARY KEY (`id`, `id_lang`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_agent_mapping` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_agent` int(10) unsigned NOT NULL,
                `id_group` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_agent_mapping_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_query_type` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `active`  tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_query_type_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_query_type_lang` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                `id_lang` int(10) unsigned NOT NULL,
                `query_name` varchar(128) character set utf8 NOT NULL,
                PRIMARY KEY (`id`, `id_lang`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_query_type_mapping` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_group` int(10) unsigned NOT NULL,
                `id_query_type` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_group_query_type_mapping_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `hd_id_customer` int(10) unsigned NOT NULL,
                `first_name` varchar(32) character set utf8 NOT NULL,
                `last_name` varchar(32) character set utf8 NOT NULL,
                `id_query_type` int(10) unsigned NOT NULL,
                `assigned_agent_id` int(10) unsigned NOT NULL,
                `id_status` int(10) unsigned NOT NULL,
                `subject` varchar(255) character set utf8 NOT NULL,
                `id_order` int(10),
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_msg` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `hd_id_ticket` int(10) unsigned NOT NULL,
                `message` text,
                `id_customer` int(10) unsigned NOT NULL,
                `id_agent` int(10) unsigned NOT NULL,
                `is_internal_note` tinyint(1) unsigned NOT NULL,
                `is_status_update` tinyint(1) unsigned NOT NULL,
                `status_from` int(5) unsigned NOT NULL,
                `status_to` int(5) unsigned NOT NULL,
                `is_agent_assign` tinyint(1) unsigned NOT NULL,
                `agent_from` int(5) unsigned NOT NULL,
                `agent_to` int(5) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_msg_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_token` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `hd_id_ticket` int(10) unsigned NOT NULL,
                `token` varchar(100) character set utf8 NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_token_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_customer` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_ps_customer` int(10) unsigned NOT NULL,
                `is_spam` int(10) unsigned NOT NULL DEFAULT '0',
                `first_name` varchar(32) character set utf8 NOT NULL,
                `last_name` varchar(32) character set utf8 NOT NULL,
                `email` varchar(128) character set utf8 NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_customer_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",

            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_attachment` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `hd_id_msg` int(10) unsigned NOT NULL,
                `attachment_name` varchar(128) character set utf8 NOT NULL,
                `attachment_token` varchar(128) character set utf8 NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            // Customization By Ram Chandra
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_message_sync_imap` (
                `md5_header` varbinary(32),
                PRIMARY KEY  (`md5_header`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            // END
            // code added By Abdul Basid
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_reply_sync_imap` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_ticket` int(11) unsigned NOT NULL DEFAULT '0',
                `message_id` varchar(256) character set utf8 NOT NULL,
                `reply_id` varchar(256) character set utf8,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_customer_reply_sync_imap_shop` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_shop` int(11) unsigned DEFAULT '1',
                `id_ticket` int(11) unsigned NOT NULL DEFAULT '0',
                `message_id` varchar(256) character set utf8 NOT NULL,
                `reply_id` varchar(256) character set utf8,
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            // code added By Abdul Basid end
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_attachment_shop` (
                `id` int(10) unsigned NOT NULL,
                `id_shop` int(11) unsigned DEFAULT '1',
                PRIMARY KEY  (`id`, `id_shop`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            //Customization By Ram Chandra
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_hd_ticket_thread` (
                `id_ticket` int(10) unsigned NOT NULL,
                `id_customer_thread` int(11) unsigned NOT NULL,
                PRIMARY KEY  (`id_ticket`, `id_customer_thread`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            // END
            "INSERT INTO `"._DB_PREFIX_."wk_hd_access_right` (`id`, `access_right_text`) VALUES
            ('1', 'create'),
            ('2', 'delete'),
            ('3', 'update'),
            ('4', 'assign'),
            ('5', 'remove')"
        );
    }

    public static function addSqlAssociationCustom(
        $table,
        $alias,
        $inner_join = true,
        $on = null,
        $force_not_default = false,
        $identifier = 'id'
    ) {
        $table_alias = $table . '_shop';
        if (strpos($table, '.') !== false) {
            list($table_alias, $table) = explode('.', $table);
        }

        $asso_table = Shop::getAssoTable($table);
        if ($asso_table === false || $asso_table['type'] != 'shop') {
            return;
        }
        $sql = (($inner_join) ? ' INNER' : ' LEFT') . ' JOIN ' . _DB_PREFIX_ . $table . '_shop ' . $table_alias . '
        ON (' . $table_alias . '.' . $identifier . ' = ' . $alias . '.' . $identifier;
        if ((int) Shop::getContextShopID()) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . (int) Shop::getContextShopID();
        } elseif (Shop::checkIdShopDefault($table) && !$force_not_default) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . $alias . '.id_shop_default';
        } else {
            $sql .= ' AND ' . $table_alias . '.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }
        $sql .= (($on) ? ' AND ' . $on : '') . ')';

        return $sql;
    }
}
