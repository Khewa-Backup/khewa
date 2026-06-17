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
class PosUpgrader424 extends PosUpgrader
{

    protected $hooks_to_register = array(
           'actionCustomerSave',
           'actionCustomerDelete',
       );

    public function installTables()
    {
        $install_queries = array();
        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_customer_search_word` (
                                        `id_word` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`id_shop` int(10) NOT NULL DEFAULT 1,
					`id_lang` int(10) NULL,
					`word` varchar(30) NOT NULL,
					PRIMARY KEY (`id_word`),
                                        UNIQUE KEY `id_lang` (`id_lang`,`id_shop`,`word`)
				    )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_customer_search_index` (
                                        `id_customer` int(10) UNSIGNED NOT NULL,
                                        `id_word` int(10) UNSIGNED NOT NULL,
                                        `weight` smallint(4) UNSIGNED NOT NULL DEFAULT 1,
					PRIMARY KEY (`id_customer`, `id_word`),
                                        KEY `id_customer` (`id_customer`)
				    )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $success = array();

        foreach ($install_queries as $install_query) {
            $success[] = Db::getInstance()->execute($install_query);
        }

        return array_sum($success) >= count($success);
    }
}
