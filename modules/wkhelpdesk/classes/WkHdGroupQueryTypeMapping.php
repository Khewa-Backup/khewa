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

class WkHdGroupQueryTypeMapping extends ObjectModel
{
    public $id;
    public $id_group;
    public $id_query_type;

    public static $definition = array(
        'table' => 'wk_hd_group_query_type_mapping',
        'primary' => 'id',
        'fields' => array(
            'id_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_query_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true)
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_group_query_type_mapping', array('type' => 'shop', 'primary' => 'id'));
    }

    public function deleteMappingByIdQueryType($idQueryType)
    {
        $allMappings = $this->getInfoByIdQueryType($idQueryType);
        $success = true;
        if (!empty($allMappings)) {
            if (isset($allMappings['id'])) {
                $allMappings = array($allMappings);
            }
            foreach ($allMappings as $accessMapping) {
                $objMapping = new self((int) $accessMapping['id']);
                $success &= $objMapping->delete();
            }
        }
        return $success;
    }

    public function deleteMappingByIdGroup($idGroup)
    {
        $allMappings = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdg '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'hdg') . ' WHERE hdg.`id_group` = '
            .(int) $idGroup.' GROUP BY hdg.`id`'
        );
        $success = true;
        if (!empty($allMappings)) {
            foreach ($allMappings as $accessMapping) {
                $objMapping = new self((int) $accessMapping['id']);
                $success &= $objMapping->delete();
            }
        }
        return $success;
    }

    public function getInfoByIdQueryType($idQueryType, $isDefaultGroupInclude = false)
    {
        if ($isDefaultGroupInclude) {
            // return mapped group ids
            return Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdg '
                . WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'hdg') . '
                WHERE hdg.`id_query_type` = '.(int) $idQueryType.' GROUP BY hdg.`id`'
            );
        } else {
            // return mapped group id. all query type mapped in default group so remove default group id
            return Db::getInstance()->getRow(
                'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group_query_type_mapping` hdg '
                . WkHdGroup::addSqlAssociationCustom('wk_hd_group_query_type_mapping', 'hdg')
                . ' WHERE `id_query_type` = '.(int) $idQueryType.'
                AND hdg.`id_group` NOT IN (
                    SELECT whg.`id` FROM `'._DB_PREFIX_.'wk_hd_group` whg '
                    .WkHdGroup::addSqlAssociationCustom('wk_hd_group', 'whg')
                    .' WHERE whg.`is_default_group` = 1 GROUP BY whg.`id`
                ) GROUP BY hdg.`id`'
            );
        }
    }
}
