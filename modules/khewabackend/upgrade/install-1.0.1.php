<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1($module)
{
    // Register the new hook
    return $module->registerHook('displayBackOfficeTop');
}