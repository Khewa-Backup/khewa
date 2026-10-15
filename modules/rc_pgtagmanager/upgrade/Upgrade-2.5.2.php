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

function upgrade_module_2_5_2()
{
    $success = true;

    $upgrade_default_values = array(
        'RC_PGTAGMANAGER_CAVEAT' => 1
    );

    foreach ($upgrade_default_values as $key => $value) {
        // Set default products value
        if (!Configuration::updateGlobalValue($key, $value)) {
            $success = false;
        }
    }

    Media::clearCache();

    return $success;
}
