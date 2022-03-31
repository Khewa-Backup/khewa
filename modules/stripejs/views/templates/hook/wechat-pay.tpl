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

<p class="payment_module">
        <div id="stripe-ajax-loader-wepay" style="display:none;"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> {l s='Do not press BACK or REFRESH while processing...' mod='stripejs'}</div>
        <div class="stripe-payment-errors-wechat">{if isset($stripe_error)}{$stripe_error|escape:'htmlall':'UTF-8'}{/if}</div>
        <div id="stripe-ajax-loader-wechat" style="display:none;">
        <div class="alert alert-success">{l s='Payment authorized successfully.' mod='stripejs'}</div>
        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> {l s='Do not press BACK or REFRESH while processing the payment...' mod='stripejs'}</div>
        <span class="qr_code" style="display:none;"><h4><u>
        {if $stripe_mode}{l s='Scan below QR Code with your WeChat app to authorize this payment:' mod='stripejs'}
        {else}{l s='Scan below QR Code with any QR Code scanner app to authorize this payment in TEST mode:' mod='stripejs'}{/if}</u></h4><br>
        <img src="" alt="WeChat Pay QR Code" width="150" height="150" />
        <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ajax-loader.gif" alt="" /> 
        {l s='Waiting for authorization of this payment...' mod='stripejs'}</span>
</p>