<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PosUpgrader4111 extends PosUpgrader
{
    protected $configuation_keys_to_change = array(
        'POS_RECEIPT_BILL_PRINTER_1',
        'POS_RECEIPT_BILL_PRINTER_2',
        'POS_RECEIPT_FOOTER_TEXT',
        'POS_RECEIPT_HEADER_TEXT',
        'POS_RECEIPT_LOGO',
        'POS_RECEIPT_LOGO_MAX_WIDTH',
        'POS_RECEIPT_PAGE_SIZE',
        'POS_PRINT_IN_PDF',
        'POS_RECEIPT_PRINTER_DPI',
        'POS_RECEIPT_SHOW_CURRENCY',
        'POS_RECEIPT_TEMPLATE',
    );

    /**
     * @return bool
     */
    protected function installOthers()
    {
        $success = array();
        $keys_to_change = $this->configuation_keys_to_change;
        foreach ($keys_to_change as $key) {
            $personalize_data = $this->module->formatPersonalizeData(Configuration::get($key));
            $success[] = Configuration::updateValue($key, Tools::jsonEncode($personalize_data));
        }

        return array_sum($success) >= count($success);
    }

    /**
     * @return bool
     */
    protected function installTables()
    {
        $install_queries = array();
        //create table pos_returned_order
        $install_queries[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_returned_order` (
                                        `id_returned_order` int(10) unsigned NOT NULL,
                                        `id_new_order` int(10) unsigned null,
                                        `date_add` datetime NOT NULL,
                                    PRIMARY KEY (`id_returned_order`)
                                    )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $success = array();
        foreach ($install_queries as $install_query) {
            $success[] = Db::getInstance()->execute($install_query);
        }

        return array_sum($success) >= count($success);
    }
}
