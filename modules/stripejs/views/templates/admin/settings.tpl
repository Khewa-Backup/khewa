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

 <script type="text/javascript">
            $(document).ready(function() { 
             $(".stripe-module-wrapper .list-group .list-group-item").click(function(){
                 $(".list-group .list-group-item").removeClass("active");
                 $(this).addClass("active");
                 var ID = $(this).attr("id");
                 $(".stripe-module-wrapper fieldset").removeClass("show");
                 $(".stripe-module-wrapper fieldset."+ID).addClass("show");
                 });
             });
</script>
<link href="{$stripeBOCssUrl|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css">
{if $success}<div class="conf confirmation alert alert-success">{l s='Settings successfully saved' mod='stripejs'}</div>{/if}
            <div class="stripe-module-wrapper col-md-12">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/stripe.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/google.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/apple.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/mspay.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/stripe-cc.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/3d.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/stripe-btc.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/alipay.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/bancontact.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/ideal.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/giropay.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/sofort.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/p24.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/eps.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/wechat.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/cc-sepa.png">
            <img class="tabs-logo" src="{$this_path|escape:'htmlall':'UTF-8'}/views/img/multibanco.png">
            </div>
            <div class="tabs stripe-module-wrapper">
            <div class="sidebar navigation col-md-3">
            <nav class="list-group categorieList">
<a class="list-group-item active" id="technical_checkes" href="javascript:void();"><i class="icon-check-circle-o tabcbpfw-icon"></i>{l s='Technical Checks' mod='stripejs'}
<span class="badge-module-tabs pull-right {if $requirements['result']}tab-success{else}tab-warning{/if}"></span></a>
<a class="list-group-item" id="stripe_settings" href="javascript:void();"><i class="icon-power-off tabcbpfw-icon"></i>{l s='Stripe Connexion' mod='stripejs'}
<span class="badge-module-tabs pull-right {if $checkSettings}tab-success{else}tab-warning{/if}"></span></a>
<a class="list-group-item" id="stripe_checkout" href="javascript:void();"><i class="icon-star tabcbpfw-icon"></i>{l s='Stripe Checkout' mod='stripejs'}</a>
<a class="list-group-item" id="order_statuses" href="javascript:void();"><i class="icon-filter tabcbpfw-icon"></i>{l s='Order Statuses' mod='stripejs'}</a>
<a class="list-group-item" id="stripe-cc-numbers" href="javascript:void();"><i class="icon-dollar tabcbpfw-icon"></i>{l s='Test Credit Card Numbers' mod='stripejs'}</a>
<a class="list-group-item" id="stripe_webhooks" href="javascript:void();"><i class="icon-link tabcbpfw-icon"></i>{l s='Stripe Webhook URL' mod='stripejs'}</a>
<br /> <br />
<a class="list-group-item" id="technical_checkes" href="https://addons.prestashop.com/en/ratings.php" target="_blank"><i class="icon-star tabcbpfw-icon" style="color:gold;"></i>{l s='Rate me' mod='stripejs'}</a>
<br />
<a class="list-group-item" id="technical_checkes" target="_blank"><i class="icon-info tabcbpfw-icon"></i>{l s='Version' mod='stripejs'}: {$ps_version|escape:'htmlall':'UTF-8'}</a>
            </nav>
            </div>
            <div class="panel content-wrap form-horizontal col-lg-9">
            <fieldset class="technical_checkes show">
            <h3 class="tab"> <i class="icon-check-circle-o"></i>&nbsp;{l s='Technical Checks' mod='stripejs'}</h3>
                <div class="{if $requirements['result']}conf confirmation alert alert-success">{l s='Good news! All the checks were successfully performed. You can now configure your module and start using Stripe.' mod='stripejs'}{else}
                error alert alert-danger">{l s='Unfortunately, at least one issue is preventing you from using Stripe. Please fix the issue and reload this page.' mod='stripejs'}{/if}</div><table cellspacing="0" cellpadding="0" class="stripe-technical">
                {foreach $requirements as $k => $requirement}
                    {if $k != 'result'}
                        <tr>
                            <td><img src="../img/admin/{if $requirement['result']}enabled{else}disabled{/if}.gif" alt="" />&nbsp;</td>
                            <td>{$requirement['name']|escape:'htmlall':'UTF-8'}
                            {if !$requirement['result'] && isset($requirement['resolution'])}<br />{$requirement['resolution']|escape:'htmlall':'UTF-8'}{/if}</td>
                        </tr>
                        {/if}
                  {/foreach}
                </table>
                    <div class="alert alert-info">
                    <strong>{l s='Minimum requirements to use Apple Pay:' mod='stripejs'}</strong><hr />
                    <ul>
                    <li>{l s='In Safari on an iOS device running iOS 10. Make sure that you have at least one card in your Wallet (you can add one by going to Settings → Wallet & Apple Pay).' mod='stripejs'}</li>
                    <li>{l s='In Safari on a Mac running macOS Sierra. You will also need an iOS device running iOS 10 with a card in its Wallet to be paired to your Mac via Handoff (instructions on how to do this can be found on' mod='stripejs'}&nbsp;<a href="https://support.apple.com/en-us/HT204681" target="_blank">Apple Support website.</a>)</li>
                    <li>{l s='Devices that support Apple Pay include iPhone 6 or newer, iPhone 6 Plus or newer, iPad Air 2, and iPad mini 3.' mod='stripejs'}</li>
                    <li>{l s='For testing Apple Pay, please set module in LIVE mode because in TEST mode you need to setup a SANDBOX TESTER ACCOUNT for your device which is a long process.' mod='stripejs'}</li>
                    </ul>
                    </div><hr />
					<h1>If you need any additional support regarding this module then click <a href="https://addons.prestashop.com/contact-community.php?id_product=17856" target="_blank">Here</a></h1>
            </fieldset>

        {if !empty($errors)}
            <fieldset class="technical_checkes show">
                <legend>Errors</legend>
                <table cellspacing="0" cellpadding="0" class="stripe-technical">
                        <tbody>
                    {foreach $errors as $error} 
                        <tr>
                            <td><img src="../img/admin/status_red.png" alt=""></td>
                            <td>{$error|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody></table>
            </fieldset>
        {/if}
        
        <form action="" method="post">
        <fieldset class="stripe_settings">
        <h3 class="tab"> <i class="icon-power-off"></i>&nbsp;{l s='Stripe Connexion' mod='stripejs'}</h3>
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product">{l s='Test Secret Key' mod='stripejs'}:</label>
        <div class="col-lg-5">
            <input type="text" name="stripe_private_key_test" value="{Configuration::get('STRIPE_PRIVATE_KEY_TEST')|escape:'htmlall':'UTF-8'}" />
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product">{l s='Test Publishable Key' mod='stripejs'}:</label>
        <div class="col-lg-5">
                <input type="text" name="stripe_public_key_test" value="{Configuration::get('STRIPE_PUBLIC_KEY_TEST')|escape:'htmlall':'UTF-8'}" />
        </div></div>
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product">{l s='Live Secret Key' mod='stripejs'}:</label>
        <div class="col-lg-5">
            <input type="text" name="stripe_private_key_live" value="{Configuration::get('STRIPE_PRIVATE_KEY_LIVE')|escape:'htmlall':'UTF-8'}" />
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product">{l s='Live Publishable Key' mod='stripejs'}:</label>
        <div class="col-lg-5">
                <input type="text" name="stripe_public_key_live" value="{Configuration::get('STRIPE_PUBLIC_KEY_LIVE')|escape:'htmlall':'UTF-8'}" />
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product">{l s='Transaction Mode' mod='stripejs'}:</label>
        <div class="col-lg-5">
                <select name="stripe_mode" style="width:auto">
                <option value="0"{if !Configuration::get('STRIPE_MODE')} selected="selected"{/if}>{l s='Test' mod='stripejs'}</option>
                <option value="1"{if Configuration::get('STRIPE_MODE')} selected="selected"{/if}>{l s='Live' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Choose whether to authorize payments and manually capture them later, or to both authorize and capture (i.e. fully charge) payments when orders are placed. You can capture a payment that is only Authorized by using Stripe payment tab for the order.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title=""> 
                {l s='Charge Mode' mod='stripejs'}:
            </span></label>
        <div class="col-lg-5">
                <select name="STRIPE_CAPTURE_TYPE" style="width:auto">
                <option value="0"{if !Configuration::get('STRIPE_CAPTURE_TYPE')} selected="selected"{/if}>{l s='Authorize Only' mod='stripejs'}</option>
                <option value="1"{if Configuration::get('STRIPE_CAPTURE_TYPE')} selected="selected"{/if}>{l s='Authorize & Capture' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Enable standard checkout form to accept card payments.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title=""><b>{l s='Accept Cards:' mod='stripejs'}</b></span></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_CARDS" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_CARDS')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_CARDS')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='3D-Secure (Verified by VISA, MasterCard SecureCode™) is a service that is used to reduce fraud payments by verifing a customer identity before an online purchase can be completed.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='3D-Secure authentication:' mod='stripejs'}</span></label>
        <div class="col-lg-5">
                <select name="STRIPE_3DSECURE" style="width:auto">
                <option value="0"{if !Configuration::get('STRIPE_3DSECURE')} selected="selected"{/if}>{l s='No' mod='stripejs'}</option>
                <option value="1"{if Configuration::get('STRIPE_3DSECURE')} selected="selected"{/if}>{l s='On all charges' mod='stripejs'}</option>
                <option value="2"{if Configuration::get('STRIPE_3DSECURE')==2} selected="selected"{/if}>{l s='On charges above 50 EUR/USD/GBP Only' mod='stripejs'}</option>
                </select>{l s='For 3D-Secure required cards, payment will be processed automatically with it.' mod='stripejs'}
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Faster checkout experience for your returning customers.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Quick Pay:' mod='stripejs'}</span></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_USEDCARD" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_USEDCARD')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_USEDCARD')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>{l s='Show used credit cards as a new payment option for returning customer.' mod='stripejs'} {l s='It will create Stripe customers with the payment card source to charge them later.' mod='stripejs'}
        </div></div>
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Allows customers to securely make payments using any options saved to a customer\'s Google Account—including Google Play, YouTube, Chrome, or Android Pay. Apple Pay with your saved cards in your Apple devices. Microsoft Pay using Edge browser with your saved cards.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title=""><b>{l s='Pay with Google/ Apple Pay/ Microsoft Pay options using payment request button:' mod='stripejs'}</b></span></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_PRBUTTON" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_PRBUTTON')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_PRBUTTON')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>{l s='To use Apple Pay, add your domain in your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account/apple_pay" class="btc_link" target="_blank">{l s='Stripe dashboard' mod='stripejs'}</a>
        </div></div>
       
        <div class="form-group">
        <div class="col-lg-1"></div>
        <div class="col-lg-10" style="border:1px solid #ccc;background:#f8f8f8;"><h4>
        {l s='To process below payment methods, you will need to activate them through your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account/payments/settings" class="btc_link" target="_blank">{l s='Stripe dashboard' mod='stripejs'}</a></h4>
        <div class="col-lg-1"></div>
        </div></div> 
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='SEPA Direct Debit:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-6">
                <select name="STRIPE_ALLOW_SEPA" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_SEPA')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_SEPA')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>{l s='Webhook URL setup required on your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account/webhooks" class="btc_link" target="_blank">{l s='Stripe dashboard' mod='stripejs'}</a>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Need to display your statement descriptor to show the agreement for SEPA Direct Debit payments mandate' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Statement Descriptor for SEPA:' mod='stripejs'}</span></label>
        <div class="col-lg-6">
		<input type="text" name="STRIPE_STMNT_DESC" value="{Configuration::get('STRIPE_STMNT_DESC')|escape:'htmlall':'UTF-8'}" />{l s='It must be same as your statement descriptor in your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account" class="btc_link" target="_blank">{l s='Stripe account' mod='stripejs'}</a>
        </div></div> 
         <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='Alipay Payments:' mod='stripejs'}</b><br /><i>{l s='(AUD, CAD, EUR, GBP, HKD, JPY, NZD, SGD, USD)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_ALIPAY" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_ALIPAY')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_ALIPAY')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='iDEAL Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_IDEAL" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_IDEAL')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_IDEAL')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='GIROPAY Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_GIROPAY" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_GIROPAY')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_GIROPAY')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='BANCONTACT Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_BANCONTACT" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_BANCONTACT')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_BANCONTACT')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Accept SOFORT payments from customers in countries:
Austria, Belgium, Germany, Italy, Netherlands, Spain' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title=""><b>{l s='SOFORT Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></span></label>
        <div class="col-lg-6">
                <select name="STRIPE_ALLOW_SOFORT" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_SOFORT')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_SOFORT')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>{l s='Webhook URL setup required on your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account/webhooks" class="btc_link" target="_blank">{l s='Stripe dashboard' mod='stripejs'}</a>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='EPS Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_EPS" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_EPS')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_EPS')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='MULTIBANCO Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR only)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_MULTIBANCO" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_MULTIBANCO')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_MULTIBANCO')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>{l s='Webhook URL setup required on your ' mod='stripejs'}<a href="https://dashboard.stripe.com/account/webhooks" class="btc_link" target="_blank">{l s='Stripe dashboard' mod='stripejs'}</a>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='P24 Payments:' mod='stripejs'}</b><br /><i>{l s='(EUR or PLN)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_P24" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_P24')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_P24')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><b>{l s='WeChat Pay:' mod='stripejs'}</b><br /><i>{l s='(AUD, CAD, EUR, GBP, HKD, JPY, SGD, or USD)' mod='stripejs'}</i></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_WECHAT" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_WECHAT')} selected="selected"{/if}>{l s='Enable' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_WECHAT')} selected="selected"{/if}>{l s='Disable' mod='stripejs'}</option>
                </select>
        </div></div> 
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='You can see ORDER ID and EMAIL in your Stripe account in each charge description.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Save Order ID in charge description' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
                <select name="STRIPE_CHARGE_ORDERID" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_CHARGE_ORDERID')} selected="selected"{/if}>{l s='Yes' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_CHARGE_ORDERID')} selected="selected"{/if}>{l s='No' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-5" for="simple_product"><span title="{l s='Specify whether Checkout should validate the billing ZIP code.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='ZipCode Verification' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_ZIP" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_ZIP')} selected="selected"{/if}>{l s='Yes' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_ZIP')} selected="selected"{/if}>{l s='No' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
                <label class="control-label col-lg-5" for="simple_product"><span title="{l s='If you want to notify your customers for each successful payment then you need to enable it in your Stripe account.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Email receipts to your customers for successful payment charges or refunds' mod='stripejs'}:</span></label>
                <div class="col-lg-4">
                    <a class="button btn btn-primary" href="https://dashboard.stripe.com/account/emails" target="_blank">{l s='Click here to enable' mod='stripejs'}</a>
                </div></div>
        <div class="panel-footer">
                <button type="submit" name="SubmitStripe" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='stripejs'}</button>
            </div>
        </fieldset>
        </form>
        <form method="post" action="">
            <fieldset class="stripe_checkout">
            <h3 class="tab"> <i class="icon-star"></i>&nbsp;{l s='Stripe Checkout' mod='stripejs'}</h3>
         <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='Its a stripe hosted secure and device friendly checkout form. Stripe Checkout is the best payment flow, on web and mobile' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Use Stripe Checkout Pop-up' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
            <select name="STRIPE_CHKOUT_POPUP" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_CHKOUT_POPUP')} selected="selected"{/if}>{l s='Yes' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_CHKOUT_POPUP')} selected="selected"{/if}>{l s='No' mod='stripejs'}</option>
                </select>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='Currently, Bitcoin payments can only be paid out in USD to a US bank account.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Accept Bitcoin Payments' mod='stripejs'}<br /><i>{l s='(Support USD Only)' mod='stripejs'}</i></span></label>
        <div class="col-lg-5">
                <select name="STRIPE_ALLOW_BTC" style="width:auto">
                <option value="1"{if Configuration::get('STRIPE_ALLOW_BTC')} selected="selected"{/if}>{l s='Yes' mod='stripejs'}</option>
                <option value="0"{if !Configuration::get('STRIPE_ALLOW_BTC')} selected="selected"{/if}>{l s='No' mod='stripejs'}</option>
                </select>{l s='To process live Bitcoin payments, you need to activate it on your ' mod='stripejs'}<br /><a href="https://dashboard.stripe.com/account/payments/settings" class="btc_link" target="_blank">{l s='Stripe account' mod='stripejs'}</a>
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='A relative URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px. The recommended image types are .gif, .jpeg, and .png.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Pop-up Logo' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
            <input type="text" name="STRIPE_POPUP_LOGO" value="{$logo_url|escape:'htmlall':'UTF-8'}" style="max-width: 350px;" />
            <br /><img src="{$logo_url|escape:'htmlall':'UTF-8'}" alt="stripe logo" style="max-width: 350px;">
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='The name of your company or website.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Pop-up Title' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
            <input type="text" name="STRIPE_POPUP_TITLE" value="{if !Configuration::get('STRIPE_POPUP_TITLE')}{Configuration::get('PS_SHOP_NAME')|escape:'htmlall':'UTF-8'}{else}{Configuration::get('STRIPE_POPUP_TITLE')|escape:'htmlall':'UTF-8'}{/if}" style="max-width: 350px;" />
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='A description of the product or service being purchased.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Pop-up Description' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
        {foreach $langs as $lang}
            <input type="text" name="STRIPE_POPUP_DESC[{$lang.id_lang|escape:'htmlall':'UTF-8'}]" value="{if !Configuration::get('STRIPE_POPUP_DESC',{$lang.id_lang})}{l s='Complete your transaction' mod='stripejs'}{else}{Configuration::get('STRIPE_POPUP_DESC',{$lang.id_lang})|escape:'htmlall':'UTF-8'}{/if}" style="max-width: 350px;display: inline;" />{$lang.iso_code|escape:'htmlall':'UTF-8'}<br />
            {/foreach}
        </div></div>
        <div class="form-group">
        <label class="control-label col-lg-4" for="simple_product"><span title="{l s='We recommend letting Checkout automatically select a language based on the user’s browser configuration by passing “auto”.' mod='stripejs'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Pop-up language' mod='stripejs'}:</span></label>
        <div class="col-lg-5">
            <select name="STRIPE_POPUP_LOCALE" style="width:auto">
                <option value="auto"{if !Configuration::get('STRIPE_POPUP_LOCALE') || !Configuration::get('STRIPE_POPUP_LOCALE')=='auto'} selected="selected"{/if}>{l s='Auto' mod='stripejs'}</option>
                <option value="zh"{if Configuration::get('STRIPE_POPUP_LOCALE')=='zh'} selected="selected"{/if}>{l s='Chinese (zh)' mod='stripejs'}</option>
                <option value="nl"{if Configuration::get('STRIPE_POPUP_LOCALE')=='nl'} selected="selected"{/if}>{l s='Dutch (nl)' mod='stripejs'}</option>
                <option value="en"{if Configuration::get('STRIPE_POPUP_LOCALE')=='en'} selected="selected"{/if}>{l s='English (en)' mod='stripejs'}</option>
                <option value="fr"{if Configuration::get('STRIPE_POPUP_LOCALE')=='fr'} selected="selected"{/if}>{l s='French (fr)' mod='stripejs'}</option>
                <option value="de"{if Configuration::get('STRIPE_POPUP_LOCALE')=='de'} selected="selected"{/if}>{l s='German (de)' mod='stripejs'}</option>
                <option value="it"{if Configuration::get('STRIPE_POPUP_LOCALE')=='it'} selected="selected"{/if}>{l s='Italian (it)' mod='stripejs'}</option>
                <option value="ja"{if Configuration::get('STRIPE_POPUP_LOCALE')=='ja'} selected="selected"{/if}>{l s='Japanese (ja)' mod='stripejs'}</option>
                <option value="es"{if Configuration::get('STRIPE_POPUP_LOCALE')=='es'} selected="selected"{/if}>{l s='Spanish (es)' mod='stripejs'}</option>
                <option value="da"{if Configuration::get('STRIPE_POPUP_LOCALE')=='da'} selected="selected"{/if}>{l s='Danish (da)' mod='stripejs'}</option>
                <option value="fi"{if Configuration::get('STRIPE_POPUP_LOCALE')=='fi'} selected="selected"{/if}>{l s='Finnish (fi)' mod='stripejs'}</option>
                <option value="no"{if Configuration::get('STRIPE_POPUP_LOCALE')=='no'} selected="selected"{/if}>{l s='Norwegian (no)' mod='stripejs'}</option>
                <option value="sv"{if Configuration::get('STRIPE_POPUP_LOCALE')=='sv'} selected="selected"{/if}>{l s='Swedish (sv)' mod='stripejs'}</option>
                </select>
        </div></div>
         <div class="panel-footer">
                <button type="submit" name="SubmitStripeCheckout" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='stripejs'}</button>
            </div>
            </fieldset>
            </form>
            
           <form method="post" action="">
            <fieldset class="order_statuses">
            <h3 class="tab"><i class="icon-filter"></i>&nbsp;{l s='Order Statuses' mod='stripejs'}</h3>
                
                    {foreach $statuses_options as $status_options}
                  
                        <div class="form-group">
                         <label class="control-label col-lg-6" for="simple_product">{$status_options['label']|escape:'htmlall':'UTF-8'}</label>
                            <div class="col-lg-5">
                                <select name="{$status_options['name']|escape:'htmlall':'UTF-8'}" style="width:auto">';
                                    {foreach $statuses as $status}
                                        <option value="{$status['id_order_state']|escape:'htmlall':'UTF-8'}"{if $status['id_order_state'] == $status_options['current_value']} selected="selected"{/if}>{$status['name']|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                </select>
                            </div></div>
                    {/foreach}

          <div class="panel-footer">
                <button type="submit" name="SubmitOrderStatuses" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='stripejs'}</button>
            </div>
            </fieldset>
            </form><form method="post" action="">
            <fieldset class="stripe-cc-numbers">
               <h3 class="tab"><i class="icon-dollar"></i>&nbsp;{l s='Test Credit Card Numbers' mod='stripejs'}</h3>
                <table cellspacing="0" cellpadding="0" class="stripe-cc-numbers" width="100%">
                  <thead>
                    <tr>
                      <th>{l s='Number' mod='stripejs'}</th>
                      <th>{l s='Card type' mod='stripejs'}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td class="number"><code>4242424242424242</code></td><td>Visa</td></tr>
                    <tr><td class="number"><code>4000000000003063</code></td><td>Visa (3D-Secure)</td></tr>
                    <tr><td class="number"><code>5555555555554444</code></td><td>MasterCard</td></tr>
                    <tr><td class="number"><code>378282246310005</code></td><td>American Express</td></tr>
                    <tr><td class="number"><code>6011111111111117</code></td><td>Discover</td></tr>
                    <tr><td class="number"><code>30569309025904</code></td><td>Diner's Club</td></tr>
                    <tr><td class="number last"><code>3530111333300000</code></td><td class="last">JCB</td></tr>
                  </tbody>
                </table>
            </fieldset>
            <div class="clear"></div>
            <fieldset class="stripe_webhooks">
            <h3 class="tab"><i class="icon-link"></i>&nbsp;&nbsp;{l s='Stripe Webhook URL' mod='stripejs'}</h3>
            <div class="alert alert-info">{l s='On changing the charge status event in stripe for payment like SOFORT, SEPA etc, Module Will change the Order status to the selected one in the "Order STATUSES" tab of this module.' mod='stripejs'}</div>
            <div class="alert alert-info">{l s='On changing the source status event in stripe for payment like MULTIBANCO, Module Will change the Order status to the selected one in the "Order STATUSES" tab of this module.' mod='stripejs'}</div>
                {l s='In Order to receive above information from Stripe, Setup the following Webhook link in Stripe\'s admin panel:' mod='stripejs'}&nbsp;<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" class="button btn btn-primary">{l s='here' mod='stripejs'}</a><br /><strong>{$webhook_url|escape:'htmlall':'UTF-8'}</strong></fieldset>
        </form>
        </div></div>
