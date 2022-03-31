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
<div class="stripe-payment-errors-checkout"></div>
<img src="{$stripe_cc|escape:'htmlall':'UTF-8'}" alt="stripe credit/ debit cards">{if $stripe_allow_btc}&nbsp;<img src="{$stripe_btc|escape:'htmlall':'UTF-8'}" alt="stripe bitcoin">{/if}
<div id="stripe-ajax-loader-checkout" style="display:none"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> {l s='Do not press BACK or REFRESH while processing...' mod='stripejs'}</div>
<script type="text/javascript">
  var popup_title = "{$popup_title|escape:'htmlall':'UTF-8'}";
  var popup_desc = "{$popup_desc|escape:'htmlall':'UTF-8'}";
  var publishableKey = "{$publishableKey|escape:'htmlall':'UTF-8'}";
  var logo_url = "{$logo_url|escape:'htmlall':'UTF-8'}";
  var popup_locale = "{$popup_locale|escape:'htmlall':'UTF-8'}";
  
  var mode = {$stripe_mode|escape:'htmlall':'UTF-8'};
  var ps_cart_id = "{$ps_cart_id|escape:'htmlall':'UTF-8'}";
  var cu_email = "{$cu_email|escape:'htmlall':'UTF-8'}";
  var cu_name = "{$customer_name|escape:'htmlall':'UTF-8'}";
  var country_iso_code = "{$country_iso_code|escape:'htmlall':'UTF-8'}";
  var currency = "{$currency|escape:'htmlall':'UTF-8'}";
  var currency_lower = "{$currency|lower|escape:'htmlall':'UTF-8'}";
  var amount_ttl = {$amount_ttl|escape:'htmlall':'UTF-8'};
  var secure_mode = {$secure_mode|escape:'htmlall':'UTF-8'};
  var baseDir = "{$baseDir|escape:'htmlall':'UTF-8'}";
  var billing_address = {$billing_address|escape nofilter};
  var module_dir = "{$module_dir|escape:'htmlall':'UTF-8'}";
  var StripePubKey = "{$publishableKey|escape:'htmlall':'UTF-8'}";
  var stripe_token = "{$stripe_token|escape:'htmlall':'UTF-8'}";
  var stripe_allow_zip = {if $stripe_allow_zip}true{else}false{/if};
  var stripe_allow_btc = {if $stripe_allow_btc}true{else}false{/if};
  var stripe_allow_alipay = {if $stripe_allow_alipay}true{else}false{/if};
  var stripe_allow_prbutton = {if $stripe_allow_prbutton}true{else}false{/if};
  var validation_url = "{$order_validation_url|escape:'htmlall':'UTF-8'}";
  var stripe_error = "{$stripe_error|escape:'htmlall':'UTF-8'}";
  var stripe_error_msg = "{l s='An error occured during transaction. Please contact us' mod='stripejs'}";
  var prbutton_alert = "{l s='Click on Pay Now button under Pay with Google/Apple Pay/ Microsoft Pay option.' mod='stripejs'}";
</script>
</div>

{if !Configuration::get('STRIPE_ALLOW_CARDS')}
<div id="modal_stripe"  class="modal" style="display: none"><div id="close_secure" class="close" style="display:none;">X</div>
  <div id="result_3d" style="text-align:center;">
  <div id="3d_window_loading">{l s='3D-Secure authentication page is loading, Please wait...' mod='stripejs'}</div>
  <div id="3d_window_loaded" style="display:none;">{l s='Waiting for 3D-Secure authorization. Popup will close automatically...' mod='stripejs'}</div>
  </div></div>
     <div id="stripe-translations">
      <span id="stripe-incorrect_ownername">{l s='The card owner name is empty.' mod='stripejs'}</span>
      <span id="stripe-incorrect_number">{l s='The card number is incorrect.' mod='stripejs'}</span>
      <span id="stripe-invalid_number">{l s='The card number is not a valid credit card number.' mod='stripejs'}</span>
      <span id="stripe-invalid_expiry_month">{l s='The card\'s expiration month is invalid.' mod='stripejs'}</span>
      <span id="stripe-invalid_expiry_year">{l s='The card\'s expiration year is invalid.' mod='stripejs'}</span>
      <span id="stripe-invalid_cvc">{l s='The card\'s security code is invalid.' mod='stripejs'}</span>
      <span id="stripe-expired_card">{l s='The card has expired.' mod='stripejs'}</span>
      <span id="stripe-incorrect_cvc">{l s='The card\'s security code is incorrect.' mod='stripejs'}</span>
      <span id="stripe-incorrect_zip">{l s='The card\'s zip code failed validation.' mod='stripejs'}</span>
      <span id="stripe-card_declined">{l s='The card was declined.' mod='stripejs'}</span>
      <span id="stripe-missing">{l s='There is no card on a customer that is being charged.' mod='stripejs'}</span>
      <span id="stripe-processing_error">{l s='An error occurred while processing the card.' mod='stripejs'}</span>
      <span id="stripe-rate_limit">{l s='An error occurred due to requests hitting the API too quickly. Please let us know if you\'re consistently running into this error.' mod='stripejs'}</span>
      <span id="stripe-3d_declined">{l s='The card doesn\'t support 3DS.' mod='stripejs'}</span>
      <span id="stripe-3d_required">{l s='3D Secure is required to process the payment.' mod='stripejs'}</span>
      <span id="stripe-no_api_key">{l s='There\'s an error with your API keys. If you\'re the administrator of this website, please go on the "Connection" tab of your plugin.' mod='stripejs'}</span>
      <span id="stripe-timeout">{l s='Request timed out, please try again..' mod='stripejs'}</span>
      <span id="stripe-wechat_declined">{l s='Wechat payment was declined.' mod='stripejs'}</span>
      </div>
      
<div id="modal-stripe-error" class="modal" style="display: none">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <p class="stripe-payment-europe-errors"></p>
</div>
<div id="sofort_available_countries" class="modal" style="display: none">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="title">{l s='Choose your bank country :' mod='stripejs'}</div>
    <select id="sofort_country">
        {foreach from=$sofort_countries item=country key=iso}
            <option value="{$iso|escape:'htmlall':'UTF-8'}" {if $iso == $country_iso_code} selected="selected"{/if}>{$country|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
    </select><br>
    <button class="btn btn-primary" onclick="$('#payment-confirmation button[type=submit]').addClass('sofort_country_selected').click();">{l s='Submit' mod='stripejs'}</button>
</div>
{/if}