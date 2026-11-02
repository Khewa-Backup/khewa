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
// Khewa PS 9.2: guard from wkhelpdesk 5.0.2 (this file keeps Khewa changes, so it was merged by hand).
if (!defined('_PS_VERSION_')) {
    exit;
}

    include_once 'WkHdAdminTranslationTrait.php'; // Khewa PS 9.2: l() for admin pages
    include_once 'WkHdTicketToken.php';
    include_once 'WkHdAccessRight.php';
    include_once 'WkHdAccessRightMapping.php';
    include_once 'WkHdCustomer.php';
    include_once 'WkHdGroup.php';
    include_once 'WkHdGroupAgentMapping.php';
    include_once 'WkHdQueryType.php';
    include_once 'WkHdGroupQueryTypeMapping.php';
    include_once 'WkHdStatusMapping.php';
    include_once 'WkHdTicket.php';
    include_once 'WkHdTicketAgent.php';
    include_once 'WkHdTicketMsg.php';
    include_once 'WkHdTicketAttachment.php';
    include_once 'WkHdStatusCode.php';
    include_once 'WkCustomerReplySyncImap.php';
    // Customization By Ram Chandra
    include_once 'WkHdTicketManager.php';
    // END
    