<?php
// Khewa: only the command line, or a request with the secret key, may run this (the .sh cron scripts send it)
if (php_sapi_name() !== 'cli') {
    $khewaParams = include __DIR__ . '/../app/config/parameters.php';
    $khewaKey = md5($khewaParams['parameters']['cookie_key'] . 'khewa_pos_crons');
    if (!isset($_GET['key']) || !is_string($_GET['key']) || !hash_equals($khewaKey, $_GET['key'])) {
        http_response_code(403);
        exit;
    }
}


require_once __DIR__.'/../config/config.inc.php';
require_once __DIR__.'/../init.php';

$hspointofsalepro = Module::getInstanceByName('hspointofsalepro');

echo PosCustomerCustomerIndexStats::getTotalCustomersAllShop();