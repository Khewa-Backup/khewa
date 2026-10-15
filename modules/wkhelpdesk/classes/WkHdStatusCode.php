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

class WkHdStatusCode extends ObjectModel
{
    public $id;
    public $date_add;
    public $date_upd;
    public $ticket_status;

    public static $definition = array(
        'table' => 'wk_hd_status_code',
        'primary' => 'id',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'ticket_status' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => true,
                'size' => 50,
            ),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        )
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        Shop::addTableAssociation('wk_hd_status_code_lang', array('type' => 'fk_shop', 'primary' => 'id'));
    }

    public function getStatusInfoById($id, $idLang = false)
    {
        Shop::addTableAssociation('wk_hd_status_code', array('type' => 'shop', 'primary' => 'id'));
        if ($id) {
            if ($idLang) {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_status_code` hdst'
                    . WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'hdst')
                    .' JOIN `'._DB_PREFIX_.'wk_hd_status_code_lang` hdstl
                    ON (hdst.`id` = hdstl.`id` AND hdstl.`id_lang` = '
                    .(int) $idLang.Shop::addSqlRestrictionOnLang('hdstl').')
                    WHERE hdst.`id` = '.(int) $id.' GROUP BY hdst.`id`';
            } else {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_hd_status_code` hdst '
                . WkHdGroup::addSqlAssociationCustom('wk_hd_status_code', 'hdst') . ' WHERE hdst.`id` = '.(int) $id
                .' GROUP BY hdst.`id`';
            }

            return Db::getInstance()->getRow($sql);
        }

        return false;
    }

    public function getStatusLangInfoById($id)
    {
        if ($id) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'wk_hd_status_code_lang` hdstl
                WHERE `id` = '.(int) $id.Shop::addSqlRestrictionOnLang('hdstl')
            );
        }

        return false;
    }
}
