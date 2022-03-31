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

class AdminStocktakedashController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        $this->toolbar_title = $this->l('Dashboard');
        parent::__construct();
    }

    /*
     * Render general view
    */
    public function renderView()
    {
        $url = Context::getContext()->link->getAdminLink('AdminModules').'&configure='.$this->module->name
        .'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;

        array_shift($this->module->my_tabs); // remove the first element of array
        if ($this->module->is_greater_17) {
            array_shift($this->module->my_tabs);
        }

        $this->tpl_view_vars = array(
            'url_config' => $url,
            'module_folder' => _MODULE_DIR_.$this->module->name,
            'module_tabs' => $this->module->my_tabs,
            'is_before_16' => $this->module->is_before_16
        );
        if ($this->module->is_before_16) {
            $this->tpl_view_vars['title_page'] = $this->toolbar_title;
        }
        $this->base_tpl_view = 'dashboard.tpl';

        return parent::renderView();
    }

    /*
    * Toolbar for PS 1.5
    */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['back']);
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
            $translated = $this->translator->trans($string);
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
