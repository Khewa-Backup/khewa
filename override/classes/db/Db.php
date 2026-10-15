<?php
if (!defined('_PS_VERSION_')) { exit; }
abstract class Db extends DbCore
{
    /*
    * module: ets_superspeed
    * date: 2026-01-17 12:14:11
    * version: 2.1.2
    */
    public function query($sql)
    {
        if (!class_exists('Ets_superspeed')) {
            require_once(dirname(__FILE__) . '/../../../modules/ets_superspeed/ets_superspeed.php');
        }
        Ets_superspeed::$query_count++;
        return parent::query($sql);
    }
}