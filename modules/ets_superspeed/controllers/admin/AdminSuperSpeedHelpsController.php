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
class AdminSuperSpeedHelpsController extends ModuleAdminController
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
        if(Tools::isSubmit('update_tocken_sp'))
        {
            $ETS_SPEED_SUPER_TOCKEN = Tools::getValue('ETS_SPEED_SUPER_TOCKEN');
            if($ETS_SPEED_SUPER_TOCKEN)
            {
                if(Tools::strlen($ETS_SPEED_SUPER_TOCKEN)>=6 && Validate::isCleanHtml($ETS_SPEED_SUPER_TOCKEN))
                {
                    Configuration::updateGlobalValue('ETS_SPEED_SUPER_TOCKEN',$ETS_SPEED_SUPER_TOCKEN);
                    die(
                        Tools::jsonEncode(
                            array(
                                'success' => $this->module->displaySuccessMessage($this->module->l('Secure token updated successfully')),
                                'link_cronjob'=> $this->context->link->getAdminLink('AdminSuperSpeedAjax').'&submitRunCronJob=1&token=' . $ETS_SPEED_SUPER_TOCKEN,
                            )
                        )
                    );
                }
                else
                {
                    die(
                        Tools::jsonEncode(
                            array(
                                'errors' => $this->module->displayError($this->module->l('Secure token cannot be shorter than 6 characters')),
                            )
                        )
                    );
                }
            }
            else
            {
                die(
                    Tools::jsonEncode(
                        array(
                            'errors' => $this->module->displayError($this->module->l('Token is required')),
                        )
                    )
                );
            }
        }
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'html_form' =>$this->module->renderSpeedHelps(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
}