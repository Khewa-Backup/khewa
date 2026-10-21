<?php
/**
 * RockPOS MixPanel
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PosMixPanel
{

    /**
     *
     * @return bool
     */
    public static function updateTime()
    {
        return self::readyToGo() ? Configuration::updateValue('POS_TIME_RUN_MIX_PANEL', strtotime(" + 30 days")) : true;
    }

    public static function readyToGo()
    {
        return Configuration::get('POS_TIME_RUN_MIX_PANEL') <= strtotime("TODAY") && Configuration::get('POS_SHARE_YOUR_DATA');
    }
}
