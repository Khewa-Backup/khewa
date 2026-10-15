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

class WkHdQueryType extends ObjectModel
{
    public $id;
    public $active;
    public $date_add;
    public $date_upd;
    public $query_name;

    public static $definition = array(
        'table' => 'wk_hd_query_type',
        'primary' => 'id',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            /* Lang fields */
            'query_name' => array(
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
        Shop::addTableAssociation('wk_hd_query_type', array('type' => 'shop', 'primary' => 'id'));
        Shop::addTableAssociation('wk_hd_query_type_lang', array('type' => 'fk_shop', 'primary' => 'id'));
    }

    public function getQueryInfoById($id, $idLang = false)
    {
        if ($id) {
            if ($idLang) {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_query_type` hdqt'
                    . WkHdGroup::addSqlAssociationCustom('wk_hd_query_type', 'hdqt')
                    .' JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` hdqtl
                    ON (hdqt.`id` = hdqtl.`id` AND hdqtl.`id_lang` = '
                    .(int) $idLang.Shop::addSqlRestrictionOnLang('hdqtl').')
                    WHERE hdqt.`id` = '.(int) $id.' GROUP BY hdqt.`id`';
            } else {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_query_type` hdg '
                . WkHdGroup::addSqlAssociationCustom('wk_hd_query_type', 'hdg') . ' WHERE hdg.`id` = '.(int) $id
                .' GROUP BY hdg.`id`';
            }

            return Db::getInstance()->getRow($sql);
        }

        return false;
    }

    public function getQueryLangInfoById($id)
    {
        if ($id) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'wk_hd_query_type_lang` hdgl
                WHERE `id` = '.(int) $id.Shop::addSqlRestrictionOnLang('hdgl')
            );
        }

        return false;
    }

    public function getAllQueryType($idLang = false, $active = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_query_type` qt '
        .WkHdGroup::addSqlAssociationCustom('wk_hd_query_type', 'qt');
        if ($idLang) {
            $sql .= ' JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` qtl
            ON (qt.`id` = qtl.`id` AND qtl.`id_lang` = '.(int) $idLang.Shop::addSqlRestrictionOnLang('qtl').')';
        }

        if ($active) {
            $sql .= ' WHERE qt.`active` = '.(int) $active;
        }
        $sql .= ' GROUP BY qt.`id`';

        return Db::getInstance()->executeS($sql);
    }

    public function isSuperAdmin($id)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` hdagent
            WHERE hdagent.`employee_id` = '.(int) $id;
        $data = Db::getInstance()->executeS($sql);
        if ($data[0]['is_super_admin']) {
            return true;
        }
        return false;
    }

    public function getAllQueryTypeForAgent($idAgent, $idLang = false, $active = false)
    {
        Shop::addTableAssociation('wk_hd_group_query_type_mapping', array('type' => 'shop', 'primary' => 'id'));
        Shop::addTableAssociation('wk_hd_group_agent_mapping', array('type' => 'shop', 'primary' => 'id'));

        $sql = 'SELECT qt.*, qtl.query_name  FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` gqtm '
        .WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'gqtm');
        $sql .= ' INNER JOIN `'._DB_PREFIX_.'wk_hd_query_type` qt'
            .' ON (qt.`id` = gqtm.`id_query_type`)'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_query_type', 'qt', true);
        if ($idLang) {
            $sql .= ' JOIN `'._DB_PREFIX_.'wk_hd_query_type_lang` qtl
            ON (qtl.`id` = gqtm.`id_query_type` AND qtl.`id_lang` = '
            .(int) $idLang.Shop::addSqlRestrictionOnLang('qtl').')';
        }
        $sql .= ' INNER JOIN `'._DB_PREFIX_.'wk_hd_group_agent_mapping` gam'
            .' ON (gam.`id_group` = gqtm.`id_group`)'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_group_agent_mapping', 'gam', true);
        $sql .= ' WHERE gam.`id_agent`='.(int) $idAgent;
        if ($active) {
            $sql .= ' AND qt.`active` = '.(int) $active;
        }
        $sql .= ' GROUP BY qt.`id`';

        return Db::getInstance()->executeS($sql);
    }

    public function getTicketAgentId($idEmployee)
    {
        $sql = 'SELECT id FROM `'._DB_PREFIX_.'wk_hd_ticket_agent` hdagent
            WHERE hdagent.`employee_id` = '.(int) $idEmployee;
        $data = Db::getInstance()->getValue($sql);

        return $data;
    }
}
