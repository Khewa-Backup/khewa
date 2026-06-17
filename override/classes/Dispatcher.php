<?php
if (!defined('_PS_VERSION_')) { exit; }
class Dispatcher extends DispatcherCore
{
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public function dispatch() {
        if(Module::isEnabled('ets_superspeed')) {
            if (@file_exists(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php')) {
                require_once(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php');
                Ets_superspeed::$start_time = microtime(true);
                if ($cache = Ets_superspeed::displayContentCache(true)) {
                    echo $cache;
                    exit;
                }
            }
        }
        parent::dispatch();
    }
}