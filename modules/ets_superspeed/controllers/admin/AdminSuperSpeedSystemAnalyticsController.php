<?php
/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    	exit;
class AdminSuperSpeedSystemAnalyticsController extends ModuleAdminController
{
    public function __construct()
    {
       parent::__construct();
       $this->context= Context::getContext();
       $this->bootstrap = true;
    }
    public function initContent()
    {
        parent::initContent();
        $change_register_option = (int)Tools::getValue('change_register_option');
        if(Tools::isSubmit('change_register_option') && ($id_module = Tools::getValue('id_module')) && ($hook_name = Tools::getValue('hook_name')) && Validate::isHookName($hook_name))
        {
            $id_hook = Hook::getIdByName($hook_name);
            if($id_hook && $id_module && Validate::isUnsignedId($id_hook) && Validate::isUnsignedId($id_module))
            {
                $module = Module::getInstanceById($id_module);
                if($change_register_option)
                {
                    if(!$module->isRegisteredInHook($hook_name))
                    {
                        $module->registerHook($hook_name);
                        $module_hook_old = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_hook_module` WHERE id_module="'.(int)$id_module.'" AND id_hook="'.(int)$id_hook.'" AND id_shop='.(int)$this->context->shop->id);
                        if($module_hook_old)
                            $module->updatePosition($id_hook,false,$module_hook_old['position']);
                    }
                    Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_superspeed_hook_module` WHERE id_module="'.(int)$id_module.'" AND id_hook="'.(int)$id_hook.'" AND id_shop="'.(int)$this->context->shop->id.'"');
                }
                else
                {
                    if($module->isRegisteredInHook($hook_name))
                    {
                        $postion = $module->getPosition($id_hook);
                        $module->unregisterHook($hook_name); 
                    }
                    else
                        $postion=0;
                    Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_hook_module`(id_module,id_hook,position,id_shop) values("'.(int)$id_module.'","'.(int)$id_hook.'","'.(int)$postion.'","'.(int)$this->context->shop->id.'")');
                }
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' =>$change_register_option ? $this->module->l('Hook registered successfully. Clear cache to see changes in front office.'): $this->module->l('Hook unregistered'),
                                'url'=> $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics').'&change_register_option='.($change_register_option?'0':'1').'&id_module='.(int)$id_module.'&hook_name='.$hook_name,
                            )
                        )
                    );
                }
                else
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics').'&conf='.($change_register_option ? 16:17));
            }
            else
            {
                if(Tools::isSubmit('ajax'))
                    die(
                        Tools::jsonEncode(
                            array(
                                'error' => $this->module->l('Module or hook does not exist'),
                            )
                        )
                    );
                else   
                    $this->context->controller->errors[] = $this->module->l('Module or hook does not exist');
            }
        }
        if(Tools::isSubmit('paggination_ajax'))
        {
            die(
                Tools::jsonEncode(
                    array(
                        'html' =>$this->module->renderSpeedSystemAnalytics(),
                    )
                )
            );
        }
        if(Tools::isSubmit('ETS_SPEED_RECORD_MODULE_PERFORMANCE') && !Tools::isSubmit('submitFilterModule') && !Tools::isSubmit('submitResetModule'))
        {
            $ETS_SPEED_RECORD_MODULE_PERFORMANCE = (int)Tools::getValue('ETS_SPEED_RECORD_MODULE_PERFORMANCE');
            Configuration::updateValue('ETS_SPEED_RECORD_MODULE_PERFORMANCE',$ETS_SPEED_RECORD_MODULE_PERFORMANCE);
            die(
                Tools::jsonEncode(
                    array(
                        'success' => $this->module->l('Updated successfully'),
                    )
                )
            );
        }
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'html_form' =>$this->module->renderSpeedSystemAnalytics(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
}