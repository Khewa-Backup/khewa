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

class WkHdGroupAgentMapping extends ObjectModel
{
    public $id;
    public $id_agent;
    public $id_group;

    public static $definition = array(
        'table' => 'wk_hd_group_agent_mapping',
        'primary' => 'id',
        'fields' => array(
            'id_agent' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_group_agent_mapping', array('type' => 'shop', 'primary' => 'id'));
    }

    public function deleteMappingByIdGroup($id_group)
    {
        $allMappings = $this->getInfoByIdGroup($id_group);
        $success = true;
        if (!empty($allMappings)) {
            foreach ($allMappings as $accessMapping) {
                $objMapping = new self((int) $accessMapping['id']);
                $success &= $objMapping->delete();
            }
        }
        return $success;
    }

    public function getInfoByIdGroup($id_group)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_hd_group_agent_mapping` hdg '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_group_agent_mapping', 'hdg')
            .' WHERE hdg.`id_group` = '.(int) $id_group.' GROUP BY hdg.`id`'
        );
    }

    public function getMappedAgentInfoByIdGroup($id_group)
    {
        return Db::getInstance()->executeS(
            'SELECT *, hdta.`name`, hdta.`email` FROM `'._DB_PREFIX_.'wk_hd_group_agent_mapping` hdgam '
            . WkHdGroup::addSqlAssociationCustom('wk_hd_group_agent_mapping', 'hdgam') . '
            JOIN `'._DB_PREFIX_.'wk_hd_ticket_agent` hdta
            ON (hdta.`id` = hdgam.`id_agent`)
            WHERE hdgam.`id_group` = '.(int) $id_group.'
            AND hdta.`active` = 1 GROUP BY hdgam.`id`'
        );
    }
}
