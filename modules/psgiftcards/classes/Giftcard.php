<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class Giftcard extends ObjectModel
{
    public $id;
    public $id_product;
    public $is_active;
    public $id_shop;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'psgiftcards',
        'primary' => 'id_giftcard',
        // 'multilang' => true,
        // 'multilang_shop' => true,
        'fields' => [
            // Config fields
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'is_active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
        ],
    ];

    /*
     * check if product is taged as giftcard
     */
    public static function getGiftCardId($id_produit, $id_shop)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards` psgc WHERE psgc.id_product =' . (int) $id_produit . ' AND psgc.id_shop =' . (int) $id_shop;
        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    public static function getIdGiftcard($id_product)
    {
        $sql = 'SELECT id_giftcard FROM `' . _DB_PREFIX_ . 'psgiftcards` WHERE id_product = ' . (int) $id_product;
        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    /*
     * get all product tag as giftcards
     */
    public static function getGiftcards()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'psgiftcards`';
        $result = Db::getInstance()->ExecuteS($sql);

        return $result;
    }
}
