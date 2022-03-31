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

<div class="row">
<div class="col-xs-12">
<div class="payment_module stripe-payment-17 sepa_payment_module">
    <div class="stripe-payment-errors-sepa">{if isset($stripe_error)}{$stripe_error|escape:'htmlall':'UTF-8'}{/if}</div>
	{* This form will be displayed only if a previous credit card was saved *}
	{if isset($stripe_sepa_account)}
	<form action="" method="POST" id="stripe-sepa-form-cc">
		<p>{l s='Pay with my saved SEPA Direct Debit account' mod='stripejs'}  (XXXXXXXXXXXX<b>{$stripe_sepa_account|escape:'htmlall':'UTF-8'}</b>)
		<input type="hidden" name="stripeToken" value="0" />
		<p><a id="stripe-replace-sepa">{l s='Replace this SEPA account with a new one' mod='stripejs'}</a>
	</form>
	{/if}
	{* Classic Credit card form *}
	<form action="" method="POST" id="stripe-sepa-form"{if isset($stripe_sepa_account)} style="display: none;"{/if}>
        {if isset($stripe_sepa_account)}<a id="stripe-use-saved-sepa">{l s='Pay with my saved SEPA Direct Debit account' mod='stripejs'} (XXXXXXXXXXXX<b>{$stripe_sepa_account|escape:'htmlall':'UTF-8'}</b>)</a><hr />{/if}
        <div>
          <label>{l s='Owner\'s Name' mod='stripejs'}</label>
          <input type="text" autocomplete="off" class="stripe-owner-name" data-stripe="name" value="{$cu_name|escape:'htmlall':'UTF-8'}"/>
        </div>
        <div>
		<label>{l s='IBAN Number' mod='stripejs'}</label>
		<input type="text" size="34" autocomplete="off" data-stripe="iban" class="stripe-iban-number" />
        </div>
    <p style="font-size:11px;line-height:14px;border-radius: 5px;padding: 5px;background: #fff;border: 1px solid #ccc;">
     {l s='By providing your IBAN and confirming this payment, you are authorizing' mod='stripejs'} <b>{Configuration::get('STRIPE_STMNT_DESC')|escape:'htmlall':'UTF-8'}</b> {l s='and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions.' mod='stripejs'}<br>
     {l s='You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank.' mod='stripejs'}
<br>
{l s='A refund must be claimed within 8 weeks starting from the date on which your account was debited.' mod='stripejs'}
        </p>
	</form>
     <div id="stripe-ajax-loader-sepa" style="display:none"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> {l s='Do not press BACK or REFRESH while processing...' mod='stripejs'}</div>
	<div class="stripe-translations" style="display:none">
		<span id="stripe-incorrect_ownername">{l s='The account holder name is empty.' mod='stripejs'}</span>
        <span id="stripe-incorrect_number_iban">{l s='IBAN number is incorrect.' mod='stripejs'}</span>
        <span id="stripe-mandate">{l s='You must accept the SEPA Direct Debit mandate.' mod='stripejs'}</span>
		<span id="stripe-currency_error">{l s='SEPA Direct Debit payments only support Euros as a currency.' mod='stripejs'}</span>
        <span id="stripe-please-fix">{l s='Please fix it and submit your payment again.' mod='stripejs'}</span>
	</div>
</div>
</div>
</div>