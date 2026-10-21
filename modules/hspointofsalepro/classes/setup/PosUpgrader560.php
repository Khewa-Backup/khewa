<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosUpgrader560 extends PosUpgrader
{
    protected function installTables()
    {
        $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'pos_search_word` CHANGE `word` `word` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL';
        return Db::getInstance()->execute($sql);
    }
}
