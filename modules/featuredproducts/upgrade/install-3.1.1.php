<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_1_1($object)
{
    return $object->upgradeModuleTo_3_1_1();
}