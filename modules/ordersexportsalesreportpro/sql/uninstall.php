<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'orders_export_srpro;';
$sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'oxsrp_aexp_email;';
$sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'oxsrp_aexp_ftp;';
$sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'oxsrp_schdl_email;';
$sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'oxsrp_schdl_ftp;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

ConfigurationCore::deleteByName('OXSRP_AEXP_ON_WHAT');
Configuration::deleteByName('OXSRP_SECURE_KEY');
