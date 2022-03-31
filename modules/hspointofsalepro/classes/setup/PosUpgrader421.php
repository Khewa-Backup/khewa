<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 *
 */
class PosUpgrader421 extends PosUpgrader
{
    /**
     * @return bool
     */
    protected function installTables()
    {
        $install_queries = array();
        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_employee_checkin` (
                                `id_pos_employee_checkin` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_employee` int(10) unsigned NOT NULL,
                                `id_shop` int(10) unsigned NOT NULL,
                                `employee_ip` varchar(20),
                                `action` varchar(20),
                                `img_path` varchar(255),
                                `date_add` datetime NULL,
                            PRIMARY KEY (`id_pos_employee_checkin`)
                            )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_product_note` (
                                `id_pos_product_note` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_cart` int(10) unsigned NOT NULL,
                                `id_order` int(10) unsigned NOT NULL,
                                `id_product` int(10) unsigned NOT NULL,
                                `id_product_attribute` int(10) unsigned NOT NULL,
                                `id_customization` int(10) unsigned NOT NULL,
                                `note` text,
                                `date_add` datetime NULL,
                            PRIMARY KEY (`id_pos_product_note`)
                            )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $success = array();
        foreach ($install_queries as $install_query) {
            $success[] = Db::getInstance()->execute($install_query);
        }
        return array_sum($success) >= count($success);
    }
}
