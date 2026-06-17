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
{if $key == 'discount_code'}
<span class="solo_discount_code">
    {if isset($text) && $text}

        <input value="{$text|escape:'html':'UTF-8'}" data-copy="{l s='Copied' mod='ets_sociallogin'}" title="{l s='Click to copy' mod='ets_sociallogin'}" />
    {/if}
</span>
{/if}