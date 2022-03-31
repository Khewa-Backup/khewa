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

class Hook extends HookCore
{   
    public static function exec($hook_name, $hook_args = array(), $id_module = null, $array_return = false, $check_exceptions = true,$use_push = false, $id_shop = null,$chain=false)
    {
        require_once(dirname(__FILE__).'/../../modules/ets_superspeed/classes/ext/ets_hook');
        $class='Ets_Hook';
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        if (Tools::version_compare(_PS_VERSION_,'1.7','>=')) {
            $method='exec17';
            return call_user_func_array(array($class, $method),array($hook_name,$hook_args,$id_module,$array_return,$check_exceptions,$use_push,$id_shop,$chain,$backtrace));
        }
        else
        {
            $method='exec16';
            return call_user_func_array(array($class, $method),array($hook_name,$hook_args,$id_module,$array_return,$check_exceptions,$use_push,$id_shop));
        }
    }
}