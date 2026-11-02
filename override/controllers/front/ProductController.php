<?php
/**
 * khewacorechanges — override of ProductController::init()
 *
 * CORE_CHANGES.md #4 — duplicate link-rewrite fix (products 12705 vs 750 opened the wrong page).
 *
 * Origin: the shop's override/controllers/front/ProductController.php (commit f49bb3ffb), which is the
 * prettyurls 2.2.8 module's includes/ProductController.php plus Khewa's change: when the numeric product
 * id in the URL disagrees with the product found by its (non-unique) link_rewrite, trust the number.
 *
 * PrestaShop 9.2:
 * - init() is declared `: void` in 9.2 core, so the override must be too (otherwise PHP stops with a fatal error).
 * - Only init() is kept. The other methods of the old file (getTemplateVarProduct, displayAjaxRefresh,
 *   assignPriceAndTax, getIdProductAttribute, getProductPageTitle) were prettyurls' own 1.7/8 copies for its
 *   "unique combination URL" feature, not Khewa changes. On 9.2 they would replace the 9.2 product page
 *   code with old code, so they are left out; prettyurls needs its own 9.2 review for that feature.
 * - crawlDbForId() is added back: init() calls it, but the old file had dropped it.
 * The 1.7 version of this file is in the module's git history.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class ProductController extends ProductControllerCore
{
    /*
    * module: khewacorechanges
    * date: 2026-11-01 20:05:43
    * version: 1.0.0
    */
    public function init(): void
    {
        $this->context->cookie->id_unique_ipa = 0;
        $this->context->cookie->write();
        $link_rewrite_bk = $link_rewrite = Tools::safeOutput(urldecode(Tools::getValue('product_rewrite')));
        $prod_pattern = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*)\.html/';
        preg_match($prod_pattern, $_SERVER['REQUEST_URI'], $url_array);
        if (isset($url_array[2]) && $url_array[2] != '') {
            $link_rewrite = $url_array[2];
        }
        if ($link_rewrite) {
            $id_lang = $this->context->language->id;
            $id_shop = $this->context->shop->id;
            $sql = 'SELECT id_product
                    FROM ' . _DB_PREFIX_ . 'product_lang
                    WHERE link_rewrite = \'' . pSQL($link_rewrite) . '\' AND id_lang = ' . (int) $id_lang . ' AND id_shop = ' . (int) $id_shop;
            $id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if ($id_product > 0) {
                $_POST['id_product'] = $id_product;
                $_GET['product_rewrite'] = '';
            } else {
                $prod_pattern_sec = '/.*?\/([0-9]+)\-([_a-zA-Z0-9-\pL]*\-[0-9\pL]*)\.html/';
                preg_match($prod_pattern_sec, $_SERVER['REQUEST_URI'], $url_array_sec);
                if (isset($url_array_sec[2]) && $url_array_sec[2] != '') {
                    $segments = explode('-', $url_array_sec[2]);
                    array_pop($segments);
                    $link_rewrite = implode('-', $segments);
                }
                $sql = 'SELECT id_product
                    FROM ' . _DB_PREFIX_ . 'product_lang
                    WHERE link_rewrite = \'' . pSQL($link_rewrite) . '\' AND id_lang = ' . (int) $id_lang . ' AND id_shop = ' . (int) $id_shop;
                $id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                if ($id_product > 0) {
                    $_POST['id_product'] = $id_product;
                    $_GET['product_rewrite'] = '';
                }
            }
        }
        if (isset($url_array[1]) && isset($_POST['id_product']) && $url_array[1] != $_POST['id_product']) {
            $id_product = $_POST['id_product'] = $url_array[1];
            $_GET['product_rewrite'] = $link_rewrite_bk;
        }
        $allow_accented_chars = (int) Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        if ($allow_accented_chars > 0) {
            $id_product = (int) Tools::getValue('id_product');
            if ($id_product <= 0) {
                $id = (int) $this->crawlDbForId(isset($_GET['product_rewrite']) ? $_GET['product_rewrite'] : '');
                if ($id > 0) {
                    $_POST['id_product'] = $id;
                }
            }
        }
        parent::init();
    }
    /**
     * prettyurls: product id from its link_rewrite (was missing from the old file although init() calls it).
     */
    /*
    * module: khewacorechanges
    * date: 2026-11-01 20:05:43
    * version: 1.0.0
    */
    protected function crawlDbForId($rew)
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $sql = new DbQuery();
        $sql->select('`id_product`');
        $sql->from('product_lang');
        $sql->where('`id_lang` = ' . (int) $id_lang . ' AND `id_shop` = ' . (int) $id_shop . ' AND `link_rewrite` = "' . pSQL($rew) . '"');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}
