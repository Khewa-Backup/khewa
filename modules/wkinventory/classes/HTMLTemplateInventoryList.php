<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class HTMLTemplateInventoryList extends HTMLTemplate
{
    public $order;
    public $context;

    public function __construct(StockTake $inventory, $smarty)
    {
        $this->order = $inventory;
        $this->smarty = $smarty;
        $this->context = Context::getContext();
        $this->available_in_your_account = false;

        // header informations
        $this->date = Tools::displayDate($inventory->date_add);
        $this->title = self::l('Inventory Report');
        $this->shop = new Shop((int)$inventory->id_shop);
    }

    /**
     * @see HTMLTemplate::getContent()
     */
    public function getContent()
    {
        $this->smarty->assign(array(
            'employee_name' => WorkshopInv::getShopEmployeeName((int)$this->order->id_employee),
            'inventory' => $this->order,
            'inventory_products' => $this->order->free_html,
            'stock_valuation' => $this->order->stock_valuation,
            'inventory_count' => $this->order->inventory_count,
            'begin_inventory' => $this->date,
            'end_inventory' => Tools::displayDate($this->order->date_upd),
        ));

        return $this->smarty->fetch($this->getTemplate('inventory-export'));
    }

    /**
     * @see HTMLTemplate::getBulkFilename()
     */
    public function getBulkFilename()
    {
        return 'inventory-report.pdf';
    }

    /**
     * @see HTMLTemplate::getFileName()
     */
    public function getFilename()
    {
        return self::l('inventory-export').sprintf('_%d', (int)$this->order->id).'.pdf';
    }

    protected function getTemplate($template_name)
    {
        $template = false;
        $default_template = _PS_PDF_DIR_.$template_name.'.tpl';
        $overriden_template = _PS_MODULE_DIR_.'wkinventory/views/templates/admin/pdf/'.$template_name.'.tpl';

        if (file_exists($overriden_template)) {
            $template = $overriden_template;
        } elseif (file_exists($default_template)) {
            $template = $default_template;
        }

        return $template;
    }
}
