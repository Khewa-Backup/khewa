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

class WkHdStatusMapping extends ObjectModel
{
    public $id;
    public $id_status;
    public $id_status_selected;

    public static $definition = array(
        'table' => 'wk_hd_status_mapping',
        'primary' => 'id',
        'fields' => array(
            'id_status' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'shop' => true
            ),
            'id_status_selected' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'shop' => true
            ),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_status_mapping', array('type' => 'shop', 'primary' => 'id'));
    }

    public function getMappingInfoById($id)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_status_mapping` hsm '
            .WkHdGroup::addSqlAssociationCustom('wk_hd_status_mapping', 'hsm').' WHERE hsm.`id` = '
            .(int) $id.' GROUP BY hsm.`id`'
        );
    }

    public static function getStatusById($id)
    {
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        return Db::getInstance()->getValue(
            'SELECT `ticket_status` FROM `'._DB_PREFIX_.'wk_hd_status_code` as st'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'st', true).
            ' JOIN `'._DB_PREFIX_.'wk_hd_status_code_lang` stl
            ON (stl.`id` = st.`id` AND stl.`id_lang` = '.(int) Context::getContext()->language->id
            .Shop::addSqlRestrictionOnLang('stl').')
            WHERE st.`id` = '.(int) $id
        );
    }

    public static function getAllStatusCode()
    {
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_status_code` as st'
            .WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'st', true).
            ' JOIN `'._DB_PREFIX_.'wk_hd_status_code_lang` stl
            ON (stl.`id` = st.`id` AND stl.`id_lang` = '.(int) Context::getContext()->language->id
            .Shop::addSqlRestrictionOnLang('stl').') Group by st.id'
        );
    }

    public static function getIdByStatus($status)
    {
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        $statuses = array('open', 'closed', 'answered', 'pending', 'resolved', 'spam');
        $index = array_search(Tools::strtolower($status), $statuses);
        if ($index !== false) {
            $results = Db::getInstance()->executeS(
                'SELECT st.`id` FROM `'._DB_PREFIX_.'wk_hd_status_code` as st'
                .WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'st', true).
                ' JOIN `'._DB_PREFIX_.'wk_hd_status_code_lang` stl
                ON (stl.`id` = st.`id` AND stl.`id_lang` = '.(int) Context::getContext()->language->id
                .Shop::addSqlRestrictionOnLang('stl').')'
                .' LIMIT '.$index.',1'
            );
            if (!empty($results)) {
                return $results[0]['id'];
            }
        } else {
            return Db::getInstance()->getValue(
                'SELECT st.`id` FROM `'._DB_PREFIX_.'wk_hd_status_code` as st'
                .WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'st', true).
                ' JOIN `'._DB_PREFIX_.'wk_hd_status_code_lang` stl
                ON (stl.`id` = st.`id` AND stl.`id_lang` = '.(int) Context::getContext()->language->id
                .Shop::addSqlRestrictionOnLang('stl').')'
                .' WHERE `ticket_status` = \''.pSQL($status).'\''
            );
        }
    }

    public function getMappedStatusIdByStatus($status)
    {
        $result = false;
        $idStatus = WkHdStatusMapping::getIdByStatus($status);
        if ($idStatus) {
            $result = Db::getInstance()->getValue(
                'SELECT hsm.`id_status_selected` FROM `'._DB_PREFIX_.'wk_hd_status_mapping` hsm '
                .WkHdGroup::addSqlAssociationCustom('wk_hd_status_mapping', 'hsm').'
                WHERE hsm.`id_status` = '.(int) $idStatus.' GROUP BY hsm.`id`'
            );
        }

        return $result;
    }

    public function getMappedStatusIdByStatusId($idStatus)
    {
        if ($idStatus) {
            $result = Db::getInstance()->getValue(
                'SELECT hsm.`id` FROM `'._DB_PREFIX_.'wk_hd_status_mapping` hsm '
                .WkHdGroup::addSqlAssociationCustom('wk_hd_status_mapping', 'hsm').'
                WHERE hsm.`id_status` = '.(int) $idStatus.' GROUP BY hsm.`id`'
            );
        }

        return $result;
    }

    public function getMappedStatusIdByStatuss($status)
    {
        $result = false;
        $idStatus = WkHdStatusMapping::getIdByStatus($status);
        if ($idStatus) {
            $result = Db::getInstance()->getValue(
                'SELECT hsm.`id_status_selected` FROM `'._DB_PREFIX_.'wk_hd_status_mapping` hsm '
                .WkHdGroup::addSqlAssociationCustom('wk_hd_status_mapping', 'hsm').'
                WHERE hsm.`id_status` = '.(int) $idStatus.' AND hsm.`id_status_selected` != 0 GROUP BY hsm.`id`'
            );
        }

        return $result;
    }
}
