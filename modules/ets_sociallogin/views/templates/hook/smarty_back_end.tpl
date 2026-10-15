{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{if $key == 'ICON'}
    <i class="icon-{$text|escape:'quotes':'UTF-8'}"></i>&nbsp
{elseif $key == 'EMAIL_CONTENT'}
    <p>Thanks for registering to our website. We’d like to give you a discount code for your next order on the website.</p>
    <p><strong>[discount_code]</strong></p>
    <p>Enter the discount code when you checkout to get <strong>[percentage]</strong> off for your next order. </p>
    <p>The discount is only available <strong>[available_from]</strong> to <strong>[available_to]</strong>.</p>
{elseif $key == 'POPUP_CONTENT'}
    <p>Thanks for registering to our website. We’d like to give you a discount code for your next order on the website.</p>
    <p><strong>[discount_code]</strong></p>
    <p>Enter the discount code when you checkout to get <strong>[percentage]</strong> off for your next order. </p>
    <p>The discount is available from <strong>[available_from]</strong> to <strong>[available_to]</strong>.</p>
{elseif $key == 'SHORTCODE'}
    {l s='Shortcodes:' mod='ets_sociallogin'} <span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">[discount_code]</span>, <span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">[available_from]</span>, <span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">[available_to]</span>, <span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">[percentage]</span>,<span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">[amount]</span>
{elseif $key == 'HOOK'}
    {l s='To use "custom hook", put' mod='ets_sociallogin'}<span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">{literal}{hook h="displaySoLoSocialLogin"}</span>{/literal} {l s='on tpl file where you want to display the social login buttons.' mod='ets_sociallogin'}
{/if}
