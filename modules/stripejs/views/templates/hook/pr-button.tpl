{*
* 2007-2018 PrestaShop
*

* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*	@author PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2018 PrestaShop SA
*	@license		http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*}

<div class="payment_module">

<div class="stripe-payment-errors-prbutton" style="display:none;"></div>
<div id="stripe-ajax-loader-prbutton" style="display:none;"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> {l s='Do not press BACK or REFRESH while processing...' mod='stripejs'}</div>

<div id="payment-request-button"></div>
<div class="prbutton-alert alert alert-warning">{l s='Your device does not qualify for Apple Pay, meet the requirements' mod='stripejs'} <a href="https://support.apple.com/en-in/KM207105" target="_blank"><b><u>{l s='here' mod='stripejs'}</u></b></a></div>

<div class="prbutton-alert alert alert-warning"><b>{l s='To pay with Google:' mod='stripejs'}</b>
<ul><li>- {l s='Use Chrome 61 or newer.' mod='stripejs'}</li><li>- {l s='You must have a' mod='stripejs'} <a href="https://support.google.com/chrome/answer/142893?co=GENIE.Platform%3DDesktop&hl={$lang_iso_code|escape:'htmlall':'UTF-8'}" target="_blank">{l s='saved payment card' mod='stripejs'}</a> {l s='in Chrome or an activated' mod='stripejs'} <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.walletnfcrel" target="_blank">{l s='Android Pay' mod='stripejs'}</a> {l s='in Chrome mobile for Android.' mod='stripejs'}</li></ul></div>

<div class="prbutton-alert alert alert-warning"><b>{l s='To pay with Microsoft:' mod='stripejs'}</b>
<ul><li>- {l s='Use Microsoft Edge browser.' mod='stripejs'}</li><li>- {l s='You must have a saved payment card in Edge' mod='stripejs'}</li></ul></div>

</div>