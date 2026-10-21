<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Front controller - Point Of Sale.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class HsPointOfSaleProSearchCronModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool
     */
    protected $display_header = false;

    /**
     * @var bool
     */
    protected $display_footer = false;

    public function postProcess()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true ");
        header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
        header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
        $id_shop = (int)Tools::getValue('id_shop', PosShop::getDefaultShopId());
        $full = (int)Tools::getValue('full', 0);
        $tab = Tools::getValue('sub-tab');
        if ($full) {
            $this->context->shop->setContext(Shop::CONTEXT_ALL);
        } else {
            $this->context->shop->setContext(Shop::CONTEXT_SHOP, $id_shop);
        }

        $search_index = $tab == 'setup-customer' ? new PosCustomerSearchIndex((bool)Tools::getValue('full', 0)) : new PosSearchIndex((bool)Tools::getValue('full', 0));
        $search_index->run();
    }
}
