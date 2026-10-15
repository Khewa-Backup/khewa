<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_0_3($object)
{
    return $object->upgradeModuleTo_3_0_3();
}