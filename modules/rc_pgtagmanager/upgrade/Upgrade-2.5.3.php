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

function upgrade_module_2_5_3($module)
{
    $success = true;

    $add_hooks = array(
        'displayAdminOrderTabLink',
        'displayAdminOrderTabContent'
    );

    $module->registerHook($add_hooks);

    Media::clearCache();

    return $success;
}
