/*
* 2023 Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author Keyrnel
* @copyright  2023 - Keyrnel
* @license commercial
* International Registered Trademark & Property of Keyrnel
*/

$(function() {
	Kl_TheGiftCard_Mails.init();
});

var Kl_TheGiftCard_Mails = (function() {
	var mails = {
		showTemplate: function(hash) {
			$(document).find('#thegiftcard').slideToggle();
			$(document).find('a[href="'+hash+'"]').trigger('click');
		}
	};

	return {
		init: function() {
			var hash = window.location.hash;
			if (hash && ['#thegiftcard-giftcard_friend', '#thegiftcard-giftcard_print'].includes(hash)) {
				mails.showTemplate(hash);
			}
			
		}
	}
})();
