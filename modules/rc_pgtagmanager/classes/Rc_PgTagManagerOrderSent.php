<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a trade license awarded by
 * Garamo Online L.T.D.
 *
 * Any use, reproduction, modification or distribution
 * of this source file without the written consent of
 * Garamo Online L.T.D It Is prohibited.
 *
 * @author    ReactionCode <info@reactioncode.com>
 * @copyright 2015-2021 Garamo Online L.T.D
 * @license   Commercial license
 */

class Rc_PgTagManagerOrderSent extends ObjectModel
{
    public $id_order;
    public $id_shop;
    public $sent_from;
    public $sent_at;

    public static $definition = array(
        'table' => 'rc_pgtagmanager_orders_sent',
        'primary' => 'id_order',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'sent_from' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'sent_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
        )
    );

    public static function getOrderReport($id_order, $id_shop)
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'rc_pgtagmanager_orders_sent` 
            WHERE id_order = '.(int)$id_order.' AND id_shop = '.(int)$id_shop
        ;
        return Db::getInstance()->getRow($query);
    }

    public static function removeOrder($id_order, $id_shop)
    {
        $query = 'DELETE FROM `'._DB_PREFIX_.'rc_pgtagmanager_orders_sent` 
            WHERE id_order = '.(int)$id_order.' AND id_shop = '.(int)$id_shop
        ;
        return Db::getInstance()->execute($query);
    }
}
