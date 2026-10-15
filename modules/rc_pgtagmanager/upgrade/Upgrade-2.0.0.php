<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a trade license awarded by
 * Garamo Online L.T.D.
 *
 * Any use, reproduction, modification or distribution
 * of this source file without the written consent of
 * Garamo Online L.T.D It Is prohibited.
 *
 * @author    ReactionCode <info@reactioncode.com>
 * @copyright 2015-2021 Garamo Online L.T.D
 * @license   Commercial license
 */

function upgrade_module_2_0_0($module)
{
    $success = true;

    $upgrade_default_values = array(
        'RC_PGTAGMANAGER_CAVEAT' => 1,
        'RC_PGTAGMANAGER_GA_SSSR' => 1,
        'RC_PGTAGMANAGER_GA_D4' => 4,
        'RC_PGTAGMANAGER_OPT_HCN' => 'optimize-loading',
        'RC_PGTAGMANAGER_OPT_HTO' => 4000,
    );

    $add_hooks = array(
        'displayAdminOrderTabOrder',
        'displayAdminOrderContentOrder'
    );

    $remove_hooks = array(
        'productFooter'
    );

    // create new tables
    $upgrade_sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rc_pgtagmanager_client_id` (
        `id_customer` INT(10) UNSIGNED NOT NULL,
        `id_shop` INT(10) UNSIGNED NOT NULL,
        `client_id` VARCHAR(50) NOT NULL,
        PRIMARY KEY  (`id_customer`, `id_shop`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;'
    ;

    // set old values or values to reset in all shops
    $remove_old_values = array(
        'RC_PGTAGMANAGER_DEBUG',
        'RC_PGTAGMANAGER_CAVEAT'
    );

    $module->registerHook($add_hooks);
    $module->unregisterHook($remove_hooks);

    // remove old values from db before add new values
    foreach ($remove_old_values as $old_value) {
        Configuration::deleteByName($old_value);
    }

    foreach ($upgrade_default_values as $key => $value) {
        // Set default products value
        if (!Configuration::updateGlobalValue($key, $value)) {
            $success = false;
        }
    }

    if (!Db::getInstance()->execute($upgrade_sql)) {
        $success = false;
    }

    Media::clearCache();

    return $success;
}
