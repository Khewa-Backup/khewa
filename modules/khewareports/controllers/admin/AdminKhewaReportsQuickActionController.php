<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    Khewa
 *  @copyright 2024 Khewa
 *  @license   Commercial License
 */

class AdminKhewaReportsQuickActionController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();
        
        $this->content = $this->context->smarty->fetch($this->getTemplatePath().'quickaction.tpl');
        $this->context->smarty->assign('content', $this->content);
    }
}

