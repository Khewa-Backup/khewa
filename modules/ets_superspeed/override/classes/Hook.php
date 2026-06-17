<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class Hook extends HookCore
{
    public static function getMethodName(string $hookName): string
    {
        return 'hook' . ucfirst($hookName);
    }
    public static function callHookOn_parent(Module $module, string $hookName, array $hookArgs)
    {
        $methodName = self::getMethodName($hookName);
        if (is_callable([$module, $methodName])) {
            return static::coreCallHook($module, $methodName, $hookArgs);
        }
        // fall back to all other names
        foreach (static::getAllKnownNames($hookName) as $hook) {
            $methodName = self::getMethodName($hook);
            if (is_callable([$module, $methodName])) {
                return static::coreCallHook($module, $methodName, $hookArgs);
            }
        }
        return '';
    }
    public static function getDynamicHook($module,$hookName,$hookArgs)
    {
        if(!Ets_superspeed_cache_page::isAjax())
        {
            if(!Ets_superspeed_cache_page::checkPageNoCache())
            {
                $dynamicHook = Ets_superspeed_cache_page::getDynamicHookModule($module->id,$hookName);
                if(Tools::strtolower($hookName)=='cetemplate')
                {
                    $hid = Configuration::get('posthemeoptionsheader_template') ? : 7;
                    if(isset($hookArgs['id']) && ($idCreative = $hookArgs['id']) && $idCreative==$hid)
                    {
                        $dynamicHook = array(
                            'empty_content' => 0,
                        );
                    }
                }
                if($dynamicHook) {
                    $params = array();
                    if(isset($hookArgs['id_product']) && $hookArgs['id_product'])
                        $params['id_product'] = $hookArgs['id_product'];
                    elseif(isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product']) && $hookArgs['product']['id_product'])
                        $params['id_product'] = $hookArgs['product']['id_product'];
                    elseif(isset($hookArgs['product']) && is_object($hookArgs['product']) && isset($hookArgs['product']->id) && $hookArgs['product']->id)
                        $params['id_product'] = $hookArgs['product']->id;
                    if(isset($hookArgs['id_product_attribute']) && $hookArgs['id_product_attribute'])
                        $params['id_product_attribute'] = $hookArgs['id_product_attribute'];
                    elseif(isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product_attribute']) && $hookArgs['product']['id_product_attribute'])
                        $params['id_product_attribute'] = $hookArgs['product']['id_product_attribute'];
                    if(isset($hookArgs['type']) && $hookArgs['type'])
                        $params['type'] = $hookArgs['type'];
                    if(isset($hookArgs['tpl']) && $hookArgs['tpl'])
                        $params['tpl'] = $hookArgs['tpl'];
                    return [
                        'html_before' =>'<div id="ets_speed_dy_'.$module->id.$hookName.(isset($params['id_product']) ? '_'.$params['id_product']:'').(isset($params['id_product_attribute']) ? '_'.$params['id_product_attribute']:'').(isset($params['type']) ? '_'.$params['type']:'').'" data-moudule="'.$module->id.'" data-module-name="'.$module->name.'" data-hook="'.$hookName.'" data-params=\''.json_encode($params).'\' class="ets_speed_dynamic_hook" '.(isset($idCreative) ? ' data-idCreative="'.(int)$idCreative.'"':'').'>',
                        'empty_content' => $dynamicHook['empty_content'],
                    ];
                }
            }
        }
        return false;
    }
    public static function callHookOn(Module $module, string $hookName, array $hookArgs)
    {
        require_once(dirname(__FILE__).'/../../modules/ets_superspeed/ets_superspeed.php');
        $time_start = microtime(true);
        $content =self::callHookOn_parent($module,$hookName,$hookArgs);
        if (isset($_SERVER['REQUEST_URI']) && (Tools::strpos($_SERVER['REQUEST_URI'], '/api') !== false || Tools::strpos($_SERVER['REQUEST_URI'], 'api/') !== false)) {
            return $content;
        }
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            /** @var Ets_superspeed $ets_superspeed */
            $ets_superspeed = Module::getInstanceByName('ets_superspeed');
            $ets_superspeed->setTimeExecHook($time_start, $time_end, $hookName, $module->id);
        }
        if(Tools::strpos($hookName,'action')===0 ||  is_array($content) || is_object($content)  || Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader' || !Module::isEnabled('ets_superspeed'))
            return $content;
        $html ='';
        $dynamicHook = self::getDynamicHook($module,$hookName,$hookArgs);
        if($dynamicHook)
            $html .= $dynamicHook['html_before'];
        if(!$dynamicHook || !$dynamicHook['empty_content']) {
            $html .= $content;
        }
        if($dynamicHook)
        {
            $html .='</div>';
        }
        return $html;
    }
    public static function coreRenderWidget($module, $hookName, $hookArgs)
    {
        $time_start = microtime(true);
        $content = parent::coreRenderWidget($module,$hookName,$hookArgs);
        if (isset($_SERVER['REQUEST_URI']) && (Tools::strpos($_SERVER['REQUEST_URI'], '/api') !== false || Tools::strpos($_SERVER['REQUEST_URI'], 'api/') !== false)) {
            return $content;
        }
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            /** @var Ets_superspeed $ets_superspeed */
            $ets_superspeed = Module::getInstanceByName('ets_superspeed');
            $ets_superspeed->setTimeExecHook($time_start, $time_end, $hookName, $module->id);
        }
        if(is_array($content) || is_object($content) || Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader')
            return $content;
        if(Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader')
            return $content;
        $html ='';

        $dynamicHook = self::getDynamicHook($module,$hookName,$hookArgs);
        if($dynamicHook)
            $html .= $dynamicHook['html_before'];
        if(!$dynamicHook || !$dynamicHook['empty_content']) {
            $html .= $content;
        }
        if($dynamicHook)
        {
            $html .='</div>';
        }

        return $html;
    }
}