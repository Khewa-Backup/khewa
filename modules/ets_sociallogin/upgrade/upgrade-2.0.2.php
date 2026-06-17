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

function upgrade_module_2_0_2($object)
{
    return $object->_installConfigs(true) && upgrade_fetch($object);
}
function upgrade_fetch($object)
{
    if ($object->hooks)
    {
        foreach ($object->hooks as $pos => $val)
        {
            if (($result = Configuration::get(($key = 'ETS_SOLO_NETWORKS_ORDER'.($pos? '_'.$pos : '')))) && $val)
            {
                $results = explode(',', $result);
                foreach ($results as &$social)
                {
                    if ($social == 'bat')
                        $social = 'bli';
                    elseif ($social == 'disq')
                        $social = 'dqs';
                }
                $results = implode(',', $results);
                Configuration::updateValue($key, $results);
            }
        }
    }
}
