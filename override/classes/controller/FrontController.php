<?php
 
class FrontController extends FrontControllerCore
{
    /*
    * module: elegantalseoessentials
    * date: 2021-04-06 05:23:02
    * version: 3.4.3
    */
    public function init()
    {
        if (Module::isInstalled('elegantalseoessentials')) {
            $id_shop = $this->context->shop->id;
            $current_url = $_SERVER['REQUEST_URI'];
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
                INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
                WHERE r.`is_active` = 1 AND sh.`id_shop` = " . (int) $id_shop . " AND r.`old_url` = '" . pSQL($current_url) . "' 
                AND (r.`expires_at` < '1970-01-01 08:00:00' OR r.`expires_at` IS NULL OR r.`expires_at` > '" . pSQL(date('Y-m-d H:i:s')) . "') 
                ORDER BY r.`id_elegantalseoessentials_redirects` DESC";
            $redirect = Db::getInstance()->getRow($sql);
            if (!$redirect && $this->php_self == 'product' && Tools::getValue('id_product')) {
                $id_product = Tools::getValue('id_product');
                $sql = "SELECT * FROM `" . _DB_PREFIX_ . "elegantalseoessentials_redirects` r 
                    INNER JOIN `" . _DB_PREFIX_ . "elegantalseoessentials_redirects_shop` sh ON (r.`id_elegantalseoessentials_redirects` = sh.`id_elegantalseoessentials_redirects`) 
                    WHERE r.`is_active` = 1 AND sh.`id_shop` = " . (int) $id_shop . " AND r.`id_product` = " . (int) $id_product . "  
                    AND (r.`expires_at` < '1970-01-01 08:00:00' OR r.`expires_at` IS NULL OR r.`expires_at` > '" . pSQL(date('Y-m-d H:i:s')) . "') 
                    ORDER BY r.`id_elegantalseoessentials_redirects` DESC";
                $redirect = Db::getInstance()->getRow($sql);
            }
            if ($redirect && Validate::isAbsoluteUrl($redirect['new_url'])) {
                $header = 'HTTP/1.1 303 See Other';
                switch ($redirect['redirect_type']) {
                    case 301:
                        $header = 'HTTP/1.1 301 Moved Permanently';
                        break;
                    case 302:
                        $header = 'HTTP/1.1 302 Moved Temporarily';
                        break;
                    default:
                        break;
                }
                Tools::redirect($redirect['new_url'], __PS_BASE_URI__, null, $header);
            }
        }
        parent::init();
    }
    /*
    * module: ets_superspeed
    * date: 2022-02-12 17:04:56
    * version: 1.3.9
    */
    public function initContent()
    {
        if(Tools::isSubmit('ets_superseed_load_content'))
        {
            parent::initContent();
            Hook::exec('actionPageCacheAjax');
        }
        parent::initContent();
    }
}