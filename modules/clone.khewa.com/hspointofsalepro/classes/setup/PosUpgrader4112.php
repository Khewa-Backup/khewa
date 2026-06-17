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
class PosUpgrader4112 extends PosUpgrader
{
    /**
     * @return bool
     */
    protected function installTables()
    {
        $install_queries = array();
        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_customer` (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `id_customer` int(10) unsigned NOT NULL,
                                `barcode` varchar(255) NOT NULL ,
                                `date_add` datetime NULL,
                                `date_update` datetime NULL,
                            PRIMARY KEY (`id`)
                            )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $success = array();
        foreach ($install_queries as $install_query) {
            $success[] = Db::getInstance()->execute($install_query);
        }
        return array_sum($success) >= count($success);
    }

    /**
     * @return bool
     */
    protected function installOthers()
    {
        $this->insertDataPosCustomer();
    }

    protected function insertDataPosCustomer()
    {
        $success = array();
        $current_date = Date('Y-m-d h:i:s');
        $id_customers = PosCustomer::getAllId();
        foreach ($id_customers as $id_customer) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'pos_customer`(`id_customer`, `barcode`, `date_add`)
                 SELECT *  FROM (SELECT ' . (int)$id_customer['id_customer'] . ' ,  \'' . pSQL(sprintf("%06d", $id_customer['id_customer'])) . '\' , \'' . pSQL($current_date) . '\') AS tmp 
                    WHERE NOT EXISTS (SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'pos_customer` WHERE `id_customer` =  ' . (int)$id_customer['id_customer'] . '  )';
            $success[] = Db::getInstance()->execute($sql);
        }
        return array_sum($success) >= count($success);
    }
}
