<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

class WkHelpDeskCronTicketModuleFrontController extends ModuleFrontController
{
    /**
     * InitContent function.
     */
    public function initContent()
    {
        $objWkHelpDesk = Module::getInstanceByName('wkhelpdesk');
        if (!Tools::getValue('token') || (Tools::getValue('token') != $objWkHelpDesk->secure_key)) {
            error_log(date('[Y-m-d H:i e] ').'Failed to sync IMAP server. Token Invalid.'.PHP_EOL, 3, _PS_MODULE_DIR_.'wkhelpdesk/error.log');
            die('Something went wrong.');
        }
        $data = WkHdTicketToken::syncImap();
        if ($data['hasError'] == true) {
            echo $data['errors'];
        }
        die('<br> END');
    }
}
