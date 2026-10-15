<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Front controller - Point Of Sale.
 */
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

        $id_shop = (int)Tools::getValue('id_shop', 0);
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
