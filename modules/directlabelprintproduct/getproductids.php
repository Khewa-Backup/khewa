<?php
/**
 * 2016-2017 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2016-2017 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

require_once("../../config/config.inc.php");
require_once(dirname(__FILE__).'/../../init.php');

$token=Tools::getValue("token");
$token1=Module::getInstanceByName('directlabelprintproduct')->getSecurityToken();

if ($token!=$token1) {
    return;
}

header("Access-Control-Allow-Origin: *");

$sql="SELECT id_product, id_product_attribute FROM `"._DB_PREFIX_."product_attribute`";
$results = Db::getInstance()->ExecuteS($sql);

$results2 = Db::getInstance()->ExecuteS("SELECT id_product FROM `"._DB_PREFIX_."product`");

foreach ($results2 as $r) {
    $r["id_product_attribute"]=0;
    $results[]=$r;
}

print json_encode($results);
