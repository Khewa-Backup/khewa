{*
* Copyright (c) 2023 Smartarget
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
* @author    Smartarget
* @copyright 2023 Smartarget
* @license   https://opensource.org/licenses/MIT  MIT License
* Contact: support@smartarget.online
* Website: https://smartarget.online
*}
<prestashop-accounts></prestashop-accounts>

<div id="ps-billing"></div>
<div id="ps-modal"></div>

{* Built in SmartargetMessageBar::getSmartargetIframeUrl(), so the settings screen loads in one request and
   the shop identity never has to be assembled in the browser. *}
<iframe id="smartarget-frame" frameborder="0" style="width: 100%;"
		src="{$smartargetIframeUrl|escape:'html':'UTF-8'}"></iframe>

<style>
	#main
	{
		padding-bottom: 0 !important;
	}
</style>

<script>
	(function () {
		var frame = document.getElementById('smartarget-frame');

		function fitToViewport() {
			frame.style.height = (document.documentElement.clientHeight - 145) + 'px';
		}

		fitToViewport();
		window.addEventListener('resize', fitToViewport);
	})();
</script>

<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel="preload"></script>
<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel="preload"></script>

<script>
	/*********************
	 * PrestaShop Account *
	 * *******************/
	window?.psaccountsVue?.init();

	if (window?.psaccountsVue?.isOnboardingCompleted() === true)
	{
		/*********************
		 * PrestaShop Billing *
		 * *******************/
		window.psBilling.initialize(window.psBillingContext.context, '#ps-billing', '#ps-modal', function (type, data) {
			// Event hook listener. The subscription state Smartarget acts on arrives over the
			// PrestaShop Billing webhook, so nothing here has to talk to Smartarget itself.
			switch (type) {
				// Hook triggered when PrestaShop Billing is initialized
				case window.psBilling.EVENT_HOOK_TYPE.BILLING_INITIALIZED:
					console.log('Billing initialized', data);
					break;
				// Hook triggered when the subscription is created or updated
				case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
					console.log('Sub updated', data);
					break;
				// Hook triggered when the subscription is cancelled
				case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CANCELLED:
					console.log('Sub cancelled', data);
					break;
			}
		});
	}
</script>
