<?php
/**
 * 2021 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2021 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_5_0($module)
{
    return $module->installTabs() && $module->createDBTable() && $module->upgradeOverride();
}
