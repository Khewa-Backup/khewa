<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class SalesExportHelper
{
    public static $formatArray = array(
        'Y-m-d' => '%Y-%m-%d',
        'd/m/Y' => '%d/%m/%Y',
        'Y/m/d' => '%Y/%m/%d',
        'm/d/e' => '%m/%d/%e',
        'd.m.Y' => '%d.%m.%Y',
        'Ymd' => '%Y%m%d',
        'e/c/Y' => '%e/%c/%Y',
        'c/e/Y' => '%c/%e/%Y',
        'e.c.Y' => '%e.%c.%Y',
        'e/c/y' => '%e/%c/%y',
        'c/e/Y' => '%c/%e/%Y',
        'e.c.y' => '%e.%c.%y',
        'd b Y' => '%d %b %Y',
        'e b Y' => '%e %b %Y',
        'e b y' => '%e %b %y',
        'd M Y' => '%d %M %Y',
        'e M Y' => '%e %M %Y',
        'e M y' => '%e %M %y',
        'Ymd' => '%Y%m%d',
        'H:i:s' => ' %H:%i:%s',
        'k:i:s' => ' %k:%i:%s',
        'h:i:s p' => ' %h:%i:%s %p',
        'l:i:s p' => ' %l:%i:%s %p',
        'His' => ' %H%i%s',
        'no_time' => '',
    );

    public static function createColumnsArray($x)
    {
        $letters = range('A', 'Z');
        $arr = array();
        $i = 1;
        foreach ($letters as $val) {
            $arr[] = $val;
            if ($i === $x) {
                return $arr;
            }
            if ($i === 26) {
                break;
            }
            ++$i;
        }

        foreach ($letters as $outerVal) {
            foreach ($letters as $innerVal) {
                ++$i;
                $arr[] = $outerVal.$innerVal;
                if ($i === $x) {
                    return $arr;
                }
            }
        }
    }

    public static function getOrderAddresses()
    {
        $sql = "SELECT DISTINCT CONCAT(ctl.`name`, ', ', sta.`name`) zone
                FROM `" ._DB_PREFIX_.'orders` ordr
                LEFT JOIN `' ._DB_PREFIX_.'address` adr ON ordr.id_address_delivery = adr.id_address
                LEFT JOIN `' ._DB_PREFIX_.'country` cnt ON cnt.id_country = adr.id_country
                LEFT JOIN `' ._DB_PREFIX_.'country_lang` ctl ON ctl.id_country = cnt.id_country
                LEFT JOIN `' ._DB_PREFIX_.'state` sta ON sta.id_state = adr.id_state
                WHERE ctl.id_lang = 1;';

        return Db::getInstance()->executeS($sql);
    }
}
