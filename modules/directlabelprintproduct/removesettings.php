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

Db::getInstance()->Execute("DELETE FROM `"._DB_PREFIX_."configuration` WHERE name LIKE 'dlp_%'");
Db::getInstance()->Execute("DELETE FROM `"._DB_PREFIX_."configuration` WHERE name LIKE 'label_printertypeset'");
Db::getInstance()->Execute("DELETE FROM `"._DB_PREFIX_."configuration` WHERE name LIKE 'printproductlabels_%'");

//Db::getInstance()->Execute("DELETE FROM `ps_configuration` WHERE name LIKE 'label_%'");

//copy ( "MyText_sample.label" , "MyText.label");
