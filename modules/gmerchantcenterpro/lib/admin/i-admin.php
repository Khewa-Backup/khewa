<?php

/**
 * Google Merchant Center Pro
 *
 * @author    businesstech.fr <modules@businesstech.fr> - https://www.businesstech.fr/
 * @copyright Business Tech - https://www.businesstech.fr/
 * @license   see file: LICENSE.txt
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

interface BT_IAdmin
{
    /**
     * process display or updating or etc ... admin
     *
     * @param string $sType => defines which method to execute
     * @param mixed $aParam => $_GET or $_POST
     * @return bool
     */
    public function run($sType, array $aParam = null);
}
