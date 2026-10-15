<?php

require_once __DIR__.'/../config/config.inc.php';
require_once __DIR__.'/../init.php';

$hspointofsalepro = Module::getInstanceByName('hspointofsalepro');

echo PosProductSearchIndexStats::getTotalProductsAllShop();