<?php
class Hook extends HookCore
{   
    /*
    * module: ets_superspeed
    * date: 2023-02-01 20:32:44
    * version: 1.6.5
    */
    public static function exec($hook_name, $hook_args = array(), $id_module = null, $array_return = false, $check_exceptions = true,$use_push = false, $id_shop = null,$chain=false)
    {
        if(Tools::strtolower($hook_name)=='header' || Tools::strtolower($hook_name)=='displayheader')
            return parent::exec($hook_name,$hook_args,$id_module,$array_return,$check_exceptions,$use_push,$id_shop,$chain);
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
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public static function getMethodName(string $hookName): string
    {
        return 'hook' . ucfirst($hookName);
    }
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public static function callHookOn_parent(Module $module, string $hookName, array $hookArgs)
    {
        $methodName = self::getMethodName($hookName);
        if (is_callable([$module, $methodName])) {
            return static::coreCallHook($module, $methodName, $hookArgs);
        }
        foreach (static::getAllKnownNames($hookName) as $hook) {
            $methodName = self::getMethodName($hook);
            if (is_callable([$module, $methodName])) {
                return static::coreCallHook($module, $methodName, $hookArgs);
            }
        }
        return '';
    }
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
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
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public static function callHookOn(Module $module, string $hookName, array $hookArgs)
    {
        require_once(dirname(__FILE__).'/../../modules/ets_superspeed/ets_superspeed.php');
        $time_start = microtime(true);
        $content =self::callHookOn_parent($module,$hookName,$hookArgs);
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            
            $ets_superspeed = Module::getInstanceByName('ets_superspeed');
            $ets_superspeed->setTimeExecHook($time_start, $time_end, $hookName, $module->id);
        }
        if(is_array($content) || is_object($content)  || Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader' || !Module::isEnabled('ets_superspeed'))
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
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public static function coreRenderWidget($module, $hookName, $hookArgs)
    {
        $time_start = microtime(true);
        $content = parent::coreRenderWidget($module,$hookName,$hookArgs);
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            
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