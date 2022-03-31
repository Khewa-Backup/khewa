<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_0_2($object)
{
    // add HS Tariff Code column
    $sql = 'alter table '._DB_PREFIX_.\CanadaPostPs\OrderLabelCustomsProduct::$definition['table'].'
	add hs_tariff_code varchar(255) null after customs_number_of_units';

    Db::getInstance()->execute($sql);
    return true;
}