<?php
/**
 * DiscountGenerator Prestashop Module
 *
 * @author    iRessources <support-prestashop@iressources.com>
 * @copyright Copyright &copy; 2015-2019 iRessources
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @link http://www.iressources.com/
 * @version 1.4.1
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
        $this->version = '1.4.1';
        $this->author = 'iRessources';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'e8a3feb3a051012ac0de47a3442ae349';

        parent::__construct();

        $this->displayName = $this->l('Discount generator');
        $this->description = $this->l('This module gives you an ability to generate discount vouchers with unique promo-codes in great numbers.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
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
        $this->_html = "";

        // Download 'All'
        if (Tools::getIsset('generatetable') && Tools::getIsset('id_group')) {
            if (ob_get_length() > 0) {
                ob_clean();
            }

            // Use headers from classes/controller/AdminController.php -> processExport
            header('Content-type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-disposition: attachment; filename="DG-vouchers-all-' . date("Y-m-d__H:i:s") . '.csv"');

            $discounts = Db::getInstance()->ExecuteS("SELECT d.*, cust.* FROM `" . _DB_PREFIX_ . "cart_rule` AS d
                INNER JOIN `" . _DB_PREFIX_ . "discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `" . _DB_PREFIX_ . "cart_cart_rule` AS ccr ON (ccr.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `" . _DB_PREFIX_ . "cart` AS c ON (c.id_cart = ccr.id_cart)
                LEFT JOIN `" . _DB_PREFIX_ . "customer` AS cust ON (cust.id_customer = c.id_customer)
                WHERE dg.id_group = " . Tools::getValue("id_group", 0));
            $this->csvExport($discounts);

            exit();
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
            header('Content-disposition: attachment; filename="DG-vouchers-used-' . date("Y-m-d__H:i:s") . '.csv"');

            $discounts = Db::getInstance()->ExecuteS("SELECT d.*, cust.* FROM `" . _DB_PREFIX_ . "cart_rule` AS d
                INNER JOIN `" . _DB_PREFIX_ . "discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                INNER JOIN `" . _DB_PREFIX_ . "cart_cart_rule` AS ccr ON (ccr.id_cart_rule = d.id_cart_rule)   
                LEFT JOIN `" . _DB_PREFIX_ . "cart` AS c ON (c.id_cart = ccr.id_cart)
                LEFT JOIN `" . _DB_PREFIX_ . "customer` AS cust ON (cust.id_customer = c.id_customer)
                WHERE dg.id_group = " . Tools::getValue("id_group", 0));
            $this->csvExport($discounts);

            exit();
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
            header('Content-disposition: attachment; filename="DG-vouchers-new-' . date("Y-m-d__H:i:s") . '.csv"');

            $discount_list = Db::getInstance()->ExecuteS("SELECT d.* FROM `" . _DB_PREFIX_ . "cart_rule` AS d
                INNER JOIN `" . _DB_PREFIX_ . "discountgenerator_list` AS dg ON (dg.id_cart_rule = d.id_cart_rule) 
                LEFT JOIN `" . _DB_PREFIX_ . "cart_cart_rule` AS cr ON (cr.id_cart_rule = d.id_cart_rule)     
                WHERE cr.id_cart IS NULL AND dg.id_group = " . Tools::getValue("id_group", 0));
            $this->csvExport($discount_list);

            exit();
        }

        // Delete history
        if (Tools::getIsset('deletefile') && Tools::getIsset('id_group')) {
            Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "discountgenerator_group` WHERE `id_group` = " . Tools::getValue("id_group", 0) . "");
            Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "discountgenerator_group_lang` WHERE `id_group` = " . Tools::getValue("id_group", 0) . "");
            Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "discountgenerator_list` WHERE `id_group` = " . Tools::getValue("id_group", 0) . "");
        }

        $this->_html .= '<h2>' . $this->displayName . '</h2>';

        $history = Db::getInstance()->ExecuteS("SELECT * FROM `" . _DB_PREFIX_ . "discountgenerator_group` AS `dg`
            INNER JOIN `" . _DB_PREFIX_ . "discountgenerator_group_lang` AS `dgl` ON (dg.id_group = dgl.id_group AND dgl.id_lang = " . (int)($this->context->cookie->id_lang) . ")");

        $this->context->smarty->assign(array(
            'ps_version' => Tools::substr(_PS_VERSION_, 0, 3),
            'history' => $history,
            'link' => $this->context->link->getAdminLink('AdminModules') . '&configure=discountgenerator&tab_module=pricing_promotion&module_name=discountgenerator',
            'generate' => $this->context->link->getAdminLink('AdminCartRules', true) . '&addcart_rule&show_group_discount=1'
        ));

        $this->_html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $this->_html;
    }

    /**
     * Exports discounts lists
     *
     * @param $discounts
     * @return bool
     */
    private function csvExport($discounts)
    {
        if (count($discounts) == 0) {
            echo $this->l('No codes found');
            return true;
        }

        // Export fields
        $reductionFields = array('reduction_percent', 'reduction_amount');
        $fields = array(
            $this->l('code') => 'code',
            $this->l('from') => 'date_from',
            $this->l('to') => 'date_to',
            $this->l('value') => $reductionFields,
            $this->l('firstname') => 'firstname',
            $this->l('lastname') => 'lastname',
            $this->l('email') => 'email'
        );
        $enclosure = '"';
        $separator = ";";

        // Use export_precontent like in classes/controller/AdminController.php -> processExport
        echo "\xEF\xBB\xBF";

        // Then use fputcsv instead of presta logic that do not cover quotes and other stuff
        $fh = @fopen('php://output', 'w');
        fputcsv($fh, array_keys($fields), $separator, $enclosure);

        // Add columns
        foreach ($discounts as $discount) {
            $columns = array();
            foreach ($fields as $k => $field) {
                $val = !is_array($field) && isset($discount[$field]) ? $discount[$field] : null;
                if ($field == $reductionFields) {
                    foreach ($field as $fieldName) {
                        $value = (float)$discount[$fieldName];
                        if (!empty($value)) {
                            $val = $discount[$fieldName];
                            if ($fieldName == 'reduction_percent') {
                                $val .= '%';
                            } else {
                                $currency = Currency::getCurrencyInstance((int)$discount['reduction_currency']);
                                $val .= ' ' . $currency->iso_code;
                            }
                        }
                    }
                }
                $columns[] = $val;
            }
            fputcsv($fh, $columns, $separator, $enclosure);
        }
    }

    /**
     * Initialize override translation variables
     * Since prestashop can't load from override/controllers/admin
     * Load variables and use module instance inside override/controllers/admin
     *
     * @return array
     */
    private function initOverrideTranslations()
    {
        $translations = array(
            $this->l('Your combination of numbers and letters : %s is only sufficient to create %s vouchers. To create %s vouchers you have to increase your combination power. Please add an X or a Y to the Code mask field.'),
            $this->l('Can\'t generate this count of vouchers using current mask. You can generate %s vouchers with current mask (possible mask combinations - %s, already exists mask vouchers - %s).'),
            $this->l('Invalid coupons quantity count'),
            $this->l('Prefix'),
            $this->l('Invalid coupons prefix'),
            $this->l('Code mask'),
            $this->l('Invalid coupons mask')
        );

        return $translations;
    }
}
