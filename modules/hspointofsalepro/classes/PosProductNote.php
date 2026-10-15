<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PosProductNote extends ObjectModel
{
    public $id_pos_product_note;
    public $id_cart;
    public $id_order;
    public $id_product;
    public $id_product_attribute;
    public $id_customization;
    public $note;
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'pos_product_note',
        'primary' => 'id_pos_product_note',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT),
            'id_order' => array('type' => self::TYPE_INT),
            'id_product' => array('type' => self::TYPE_INT),
            'id_product_attribute' => array('type' => self::TYPE_INT),
            'id_customization' => array('type' => self::TYPE_INT),
            'note' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * @param $id_cart
     * @return mixed
     */
    public static function getByIdCart($id_cart)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pos_product_note');
        $sql->where('`id_cart` = ' . (int)$id_cart);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $id_order
     * @return mixed
     */
    public static function getByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('pos_product_note');
        $sql->where('`id_order` = ' . (int)$id_order);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param array $notes
     * @param $id_product
     * @param int $id_product_attribute
     * @param int $id_customization
     * @return mixed|null
     */
    public static function findByProductAttribute($notes, $id_product, $id_product_attribute = 0, $id_customization = 0)
    {
        foreach ($notes as $note) {
            if ($note['id_product'] == $id_product && $note['id_product_attribute'] == $id_product_attribute && $note['id_customization'] == $id_customization) {
                return $note;
            }
        }

        return null;
    }

    /**
     * @param $id_order
     * @param $id_cart
     * @return mixed
     */
    public static function addOrderToNoteList($id_order, $id_cart)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'pos_product_note` set `id_order` = ' . (int)$id_order . ' where `id_cart` = ' . (int)$id_cart;
        return Db::getInstance()->execute($sql);
    }

    /**
     * @param $id_order
     * @param $id_cart
     * @param $id_product_attribute
     * @param $id_product
     * @return mixed
     */
    public static function addNoteByIdProduct($id_order, $id_cart, $id_product, $id_product_attribute)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'pos_product_note` set `id_order` = ' . (int)$id_order . ' where `id_cart` = ' . (int)$id_cart . ' AND `id_product` = ' . (int)$id_product . ' AND `id_product_attribute` = ' . (int)$id_product_attribute . '';
        return Db::getInstance()->execute($sql);
    }
}
