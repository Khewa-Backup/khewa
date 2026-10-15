<?php
/**
 * 2007-2025 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA and Contributors
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class DiscountGenerator extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'discountgenerator';
        $this->tab = 'pricing_promotion';
        $this->version = '1.6.0';
        $this->author = 'iRessources';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'e8a3feb3a051012ac0de47a3442ae349';

        parent::__construct();

        $this->displayName = $this->l('Discount generator');
        $this->description = $this->l('This module gives you an ability to generate discount vouchers with unique promo-codes in great numbers.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall the module?');

        $this->ps_versions_compliancy = ['min' => '1.5', 'max' => _PS_VERSION_];
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        return $this->installDB() && parent::install();
    }

    /**
     * Uninstall module
     *
     * @return mixed
     */
    public function uninstall()
    {
        return $this->uninstallDB() && parent::uninstall();
    }

    /**
     * Install DB
     *
     * @return bool
     */
    private function installDB()
    {
        return Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountgenerator_group` (
            `id_group` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `date` DATETIME NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')
            && Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountgenerator_group_lang` (
            `id_group` BIGINT UNSIGNED NOT NULL ,
            `id_lang` INT UNSIGNED NOT NULL ,
            `name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
            PRIMARY KEY ( `id_group` , `id_lang` )
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')
            && Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountgenerator_list` (
            `id_cart_rule` INT UNSIGNED NOT NULL ,
            `id_group` INT UNSIGNED NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8');
    }

    /**
     * Uninstall DB
     *
     * @return bool
     */
    private function uninstallDB()
    {
        return Db::getInstance()->Execute('
            DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'discountgenerator_group`
        ') && Db::getInstance()->Execute('
            DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'discountgenerator_group_lang`
        ') && Db::getInstance()->Execute('
            DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'discountgenerator_list`
        ');
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $content = '';

        // Download 'All'
        if (Tools::getIsset('generatetable') && Tools::getIsset('id_group')) {
            if (ob_get_length() > 0) {
                ob_clean();
            }

            // Use headers from classes/controller/AdminController.php -> processExport
            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-disposition: attachment; filename="DG-vouchers-all-' . date('Y-m-d_H:i:s') . '.csv"');

            $discounts = Db::getInstance()->ExecuteS('SELECT d.*, cust.* FROM `' . _DB_PREFIX_ . 'cart_rule` AS d
                INNER JOIN `' . _DB_PREFIX_ . 'discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` AS ccr ON (ccr.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS c ON (c.id_cart = ccr.id_cart)
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` AS cust ON (cust.id_customer = c.id_customer)
                WHERE dg.id_group = ' . Tools::getValue('id_group', 0));
            $this->csvExport($discounts);
            exit;
        }

        // Download 'Used'
        if (Tools::getIsset('generatetableused') && Tools::getIsset('id_group')) {
            if (ob_get_length() > 0) {
                ob_clean();
            }

            // Use headers from classes/controller/AdminController.php -> processExport
            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-disposition: attachment; filename="DG-vouchers-used-' . date('Y-m-d_H:i:s') . '.csv"');

            $discounts = Db::getInstance()->ExecuteS('SELECT d.*, cust.* FROM `' . _DB_PREFIX_ . 'cart_rule` AS d
                INNER JOIN `' . _DB_PREFIX_ . 'discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                INNER JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` AS ccr ON (ccr.id_cart_rule = d.id_cart_rule)   
                LEFT JOIN `' . _DB_PREFIX_ . 'cart` AS c ON (c.id_cart = ccr.id_cart)
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` AS cust ON (cust.id_customer = c.id_customer)
                WHERE dg.id_group = ' . Tools::getValue('id_group', 0));
            $this->csvExport($discounts);
            exit;
        }

        // Download 'Unused'
        if (Tools::getIsset('generatetablenew') && Tools::getIsset('id_group')) {
            if (ob_get_length() > 0) {
                ob_clean();
            }

            // Use headers from classes/controller/AdminController.php -> processExport
            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-disposition: attachment; filename="DG-vouchers-new-' . date('Y-m-d_H:i:s') . '.csv"');

            $discount_list = Db::getInstance()->ExecuteS('SELECT d.* FROM `' . _DB_PREFIX_ . 'cart_rule` AS d
                INNER JOIN `' . _DB_PREFIX_ . 'discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` AS cr ON (cr.id_cart_rule = d.id_cart_rule)     
                WHERE cr.id_cart IS NULL AND dg.id_group = ' . Tools::getValue('id_group', 0));
            $this->csvExport($discount_list);
            exit;
        }

        // Delete history
        if (Tools::getIsset('deletefile') && Tools::getIsset('id_group')) {
            Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'discountgenerator_group` WHERE `id_group` = ' . Tools::getValue('id_group', 0) . '');
            Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'discountgenerator_group_lang` WHERE `id_group` = ' . Tools::getValue('id_group', 0) . '');
            Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'discountgenerator_list` WHERE `id_group` = ' . Tools::getValue('id_group', 0) . '');
        }

        $content .= $this->displayName;

        $history = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'discountgenerator_group` AS `dg`
            INNER JOIN `' . _DB_PREFIX_ . 'discountgenerator_group_lang` AS `dgl` ON (dg.id_group = dgl.id_group AND dgl.id_lang = ' . (int) $this->context->cookie->id_lang . ')');

        $this->context->smarty->assign([
            'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
            'history' => $history,
            'link' => $this->context->link->getAdminLink('AdminModules') . '&configure=discountgenerator&tab_module=pricing_promotion&module_name=discountgenerator',
            'generate' => $this->context->link->getAdminLink('AdminCartRules', true) . '&addcart_rule&show_group_discount=1',
        ]);

        $content .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $content;
    }

    /**
     * Exports discounts lists
     *
     * @param $discounts
     *
     * @return bool
     */
    private function csvExport($discounts)
    {
        if (count($discounts) == 0) {
            echo $this->l('No codes found');
            return true;
        }

        // Export fields
        $reductionFields = ['reduction_percent', 'reduction_amount'];
        $fields = [
            $this->l('code') => 'code',
            $this->l('from') => 'date_from',
            $this->l('to') => 'date_to',
            $this->l('value') => $reductionFields,
            $this->l('firstname') => 'firstname',
            $this->l('lastname') => 'lastname',
            $this->l('email') => 'email',
        ];
        $enclosure = '"';
        $separator = ';';

        // Then use fputcsv instead of presta logic that do not cover quotes and other stuff
        $fh = @fopen('php://output', 'w');
        fputcsv($fh, array_keys($fields), $separator, $enclosure);

        // Add columns
        foreach ($discounts as $discount) {
            $columns = [];
            foreach ($fields as $k => $field) {
                $val = !is_array($field) && isset($discount[$field]) ? $discount[$field] : null;
                if ($field == $reductionFields) {
                    foreach ($field as $fieldName) {
                        $value = (float) $discount[$fieldName];
                        if (!empty($value)) {
                            $val = $discount[$fieldName];
                            if ($fieldName == 'reduction_percent') {
                                $val .= '%';
                            } else {
                                $currency = Currency::getCurrencyInstance((int) $discount['reduction_currency']);
                                $val .= ' ' . $currency->iso_code;
                            }
                        }
                    }
                }
                $columns[] = $val;
            }
            fputcsv($fh, $columns, $separator, $enclosure);
        }
        return true;
    }
}
