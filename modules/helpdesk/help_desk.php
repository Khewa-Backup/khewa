<?php
/**
* FMM Helpdesk Module
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright FMM Modules
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   FmmHelpdesk
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/helpdesk.php');


if (Tools::getValue('secure_key')) {
    $secureKey = Configuration::get('HELPDESK_SECURE_KEY');
    if (!empty($secureKey) && $secureKey == Tools::getValue('secure_key')) {
        $crontask = new Helpdesk();
        $crontask->cronTask();
    }
}
