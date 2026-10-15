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

class Rc_PgTagManagerClientId extends ObjectModel
{
    public $id_customer;
    public $id_shop;
    public $client_id;

    public static $definition = array(
        'table' => 'rc_pgtagmanager_client_id',
        'primary' => 'id_customer',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'client_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true)
        )
    );

    public static function getClientIdByCustomerId($id_customer)
    {
        $query = 'SELECT client_id FROM `'._DB_PREFIX_.'rc_pgtagmanager_client_id` 
            WHERE id_customer = '.(int)$id_customer
        ;
        return Db::getInstance()->getValue($query);
    }
}
