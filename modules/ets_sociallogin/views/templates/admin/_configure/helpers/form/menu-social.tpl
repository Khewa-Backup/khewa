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
<div class="ets_solo_form_tabs">
    <ul id="ets_solo_sub_menu" class="ets_solo_sub_menu">
        {foreach from=$networks item='net'}
            <li class="ets_solo_sub_item" data-group="{$net.id_option|escape:'html':'UTF-8'}" data-social="{$net.label|upper|escape:'html':'UTF-8'}">
                <img class="ets_solo_icon" alt="{$net.name|escape:'html':'UTF-8'}" title="{$net.name|escape:'html':'UTF-8'}" src="{$path|escape:'quotes':'UTF-8'}views/img/32x32/new/{if $net.id_option == 'ms'}microsoft.svg{else}{$net.label|lower|escape:'quotes':'UTF-8'}.png{/if}">
                {$net.name|escape:'html':'UTF-8'}
                <label class="ets_solo_switch{if $fields_value['ETS_SOLO_'|cat: $net.label|upper|cat:'_ENABLED'] > 0} active{/if}" data-group="{$net.id_option|escape:'html':'UTF-8'}" data-social="{$net.label|upper|escape:'html':'UTF-8'}">
                    <span class="ets_solo_slider_label on">{l s='On' mod='ets_sociallogin'}</span>
                    <span class="ets_solo_slider_label off">{l s='Off' mod='ets_sociallogin'}</span>
                </label>
            </li>
        {/foreach}
    </ul>
</div>