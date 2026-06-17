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

class WkHdAccessRightMapping extends ObjectModel
{
    public $id;
    public $id_agent;
    public $id_access_right;
    public $date_add;

    public static $definition = array(
        'table' => 'wk_hd_access_right_agent_mapping',
        'primary' => 'id',
        'fields' => array(
            'id_agent' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_access_right' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_access_right_agent_mapping', array('type' => 'shop', 'primary' => 'id'));
    }

    public function deleteAccessRightByIdAgent($idAgent)
    {
        $mappedAccessRights = $this->getAccessRightByIdAgent($idAgent);
        $success = true;

        if (!empty($mappedAccessRights)) {
            foreach ($mappedAccessRights as $accessMapping) {
                $objMapping = new self($accessMapping['id']);
                $success = $objMapping->delete();
            }
        }
        return $success;
    }

    public function getAccessRightByIdAgent($idAgent)
    {
        return Db::getInstance()->executeS(
            'SELECT acam.*, ar.`access_right_text`
            FROM `'._DB_PREFIX_.'wk_hd_access_right_agent_mapping` acam '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_access_right_agent_mapping', 'acam') . '
            JOIN `'._DB_PREFIX_.'wk_hd_access_right` ar
            ON (acam.`id_access_right` = ar.`id`)
            WHERE acam.`id_agent` = '.(int) $idAgent.' GROUP BY acam.`id`'
        );
    }

    public static function getIdByAccessRight($status)
    {
        return Db::getInstance()->getValue(
            'SELECT `id` FROM `'._DB_PREFIX_.'wk_hd_access_right` WHERE `access_right_text` = \''.pSQL($status).'\''
        );
    }

    public function checkTicketCreateAccessRightByIdAgent($idAgent)
    {
        $result = false;
        $access_right_id = WkHdAccessRightMapping::getIdByAccessRight('create');
        if ($access_right_id) {
            $result = Db::getInstance()->getValue(
                'SELECT acam.`id` FROM `'._DB_PREFIX_.'wk_hd_access_right_agent_mapping` acam '
                . WkHdGroup::addSqlAssociationCustom('wk_hd_access_right_agent_mapping', 'acam') . '
                WHERE acam.`id_agent` = '.(int) $idAgent.' AND acam.`id_access_right` = '.(int) $access_right_id
                .' GROUP BY acam.`id`'
            );
        }
        return $result;
    }
}
