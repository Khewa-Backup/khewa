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
{extends file="helpers/form/form.tpl"}
{if !$is15}
    {block name="legend"}
        {if $field.title != ''}
            {$smarty.block.parent}
        {/if}
    {/block}
{/if}
{block name="label"}
    {if $is15}
        {if $fieldset.form.name == 'social_networks' && isset($networks) && $networks}
            {if $input.name == 'ETS_SOLO_FACEBOOK_APP_ID'}
                <div class="form-wrapper">
            {/if}
            {if $input.name == 'ETS_SOLO_FACEBOOK_APP_ID'}
                {include file="./menu-social.tpl"}
                <div class="ets_solo_form">
            {/if}
            {if $input.name == 'ETS_SOLO_'|cat: $networks[$input.group]['label']|upper|cat:'_APP_ID'}
                <div class="ets_solo_form_item {$input.group|escape:'html':'UTF-8'}" data-group="{$input.group|escape:'html':'UTF-8'}" data-social="{$networks[$input.group]['label']|upper|escape:'html':'UTF-8'}">
                    <h3>{$networks[$input.group]['name']|escape:'quotes':'UTF-8'}</h3>
                    <div class="clear"></div>
            {/if}
        {/if}
        {*begin form design*}
        {assign var="form_design" value=($fieldset.form.name == 'design' && isset($hooks) && $hooks && isset($pos) && $pos)}
        {if $form_design && $input.name == 'ETS_SOLO_LOGIN_BUTTON_TYPE'|cat:$pos}
            <div id="ets_solo_panel" class="ets_solo_panel">{hook h='displaySoloPreview'}</div><div class="form-wrapper">
            <div class="ets_solo_menu_hooks">
                <ul>
                    {foreach from=$hooks item='hook'}<li class="ets_solo_menu_item{if $pos|lower == '_'|cat:$hook.id_option|lower} active{/if}" data-pos="{$hook.id_option|upper|escape:'html':'UTF-8'}">
                        <a class="ets_solo_menu_item" href="{$design_link|cat:'&pos='|cat:$hook.id_option|escape:'html':'UTF-8'}">{$hook.name|escape:'html':'UTF-8'}</a>
                        <label class="ets_solo_position{if in_array($hook.id_option, $social_pages)} active{/if}" data-pos="{$hook.id_option|escape:'html':'UTF-8'}">
                            <span class="ets_solo_position_label on">{l s='On' mod='ets_sociallogin'}</span>
                            <span class="ets_solo_position_label off">{l s='Off' mod='ets_sociallogin'}</span>
                        </label>
                    </li>{/foreach}
                </ul>
            </div>
            <div class="ets_solo_form_design">
        {/if}
        {if $form_design && $input.name == 'ETS_SOLO_LOGIN_BUTTON_TYPE_CUS'}
            <p class="help-block">{l s='To use "custom hook", put' mod='ets_sociallogin'}&nbsp;<span class="ets_solo_pink" title="{l s='Click to copy' mod='ets_sociallogin'}">{l s='{hook h="displaySoLoSocialLogin"}' mod='ets_sociallogin'}</span>&nbsp;{l s='on tpl file where you want to display the social login buttons.' mod='ets_sociallogin'}</p>
        {/if}
        {*end form design*}
        <div class="form-group-wrapper {if isset($fieldset.form.name) && $fieldset.form.name == 'design' && $input.name !=('ETS_SOLO_LOGIN_DEFAULT'|cat:$pos) && $input.name!='ETS_SOLO_LOGIN_POSITION'}ets_solo_default{/if} {if isset($input.group) && $input.group}ets_solo_{$input.group|escape:'quotes':'UTF-8'}{/if} row_{$input.name|lower|escape:'html':'UTF-8'}">
    {/if}
    {$smarty.block.parent}
{/block}
{block name="field"}
    {$smarty.block.parent}
    {if $is15}
        </div>
        {if isset($form_design) && $form_design && $input.name == 'ETS_SOLO_GOOGLE_THEME'|cat:$pos}</div></div><div class="clear"></div>{/if}
        {if $fieldset.form.name == 'social_networks' && isset($networks) && $networks}
            {if $input.name == 'ETS_SOLO_'|cat: $networks[$input.group]['label']|upper|cat:'_CALLBACK'}
                    <p class="ets_solo_required">
                        {$networks[$input.group]['name']|ucfirst|cat:' '|escape:'html':'UTF-8'}{l s='requires that you create an external application linking your website to their API. To know how to create this application click on "Where do I get this info?" and follow steps.' mod='ets_sociallogin'}
                    </p>
                </div>
            {/if}
            {if $input.name == 'ETS_SOLO_BLIZZARD_CALLBACK'}
                    {*button 1.5 save here*}
                    <div class="margin-form"><input id="module_form_submit_btn" value="Save" name="saveSocial_networks" type="submit"></div>
                </div>
                </div>
                <div class="clear"></div>
            {/if}
        {/if}
    {/if}
{/block}
{*prestashop > 1.6*}
{block name="input"}
    {if $input.type == 'solo_group'}
        {assign var=groups value=$input.values}
        {if count($groups) && isset($groups)}
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="fixed-width-xs">
                                <span class="title_box">
                                    <input type="checkbox" name="checkme" id="checkme" onclick="checkDelBoxes(this.form, '{$input.name|escape:'html':'UTF-8'}[]', this.checked)" />
                                </span>
                            </th>
                            <th><span class="title_box">{l s='Available positions (Prestashop hooks)' mod='ets_sociallogin'}</span></th>
                            <th><span class="title_box"></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $groups as $key => $group}
                            <tr>
                                <td>
                                    <input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" class="groupBox" id="{$input.name|escape:'html':'UTF-8'}_{$group.id_option|escape:'quotes':'UTF-8'}" value="{$group.id_option|escape:'quotes':'UTF-8'}" {if is_array($fields_value[$input.name]) && in_array($group.id_option, $fields_value[$input.name])}checked="checked"{/if} />
                                </td>
                                <td>
                                    <label for="{$input.name|escape:'html':'UTF-8'}_{$group.id_option|escape:'quotes':'UTF-8'}">{$group.name|escape:'html':'UTF-8'}</label>
                                </td>
                                <td>
                                    <a href="{if isset($design_link) && $design_link}{$design_link|cat:'&pos='|cat:$group.id_option|escape:'quotes':'UTF-8'}{else}javascript:void(0){/if}">{l s='Customize' mod='ets_sociallogin'}</a>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {else}
            <p>
                {l s='No group created' mod='ets_sociallogin'}
            </p>
        {/if}
    {elseif $input.type == 'solo_url'}
        {if $input.name|trim == 'ETS_SOLO_STACKEXC_CALLBACK' && isset($base_url)}
            <span class="ets_solo_callback_url" data-msg="{l s='Copied' mod='ets_sociallogin'}"> {$base_url nofilter} </span>
        {elseif isset($callback_url)}
            {foreach $callback_url as $url}
                <p style="padding-bottom:5px;padding-top: 5px;"><span class="ets_solo_callback_url" data-msg="{l s='Copied' mod='ets_sociallogin'}">{$url nofilter}</span></p>
            {/foreach}
        {/if}
    {elseif $input.type == 'solo_option'}
        {if isset($input.values) && $input.values}
            <ul class="ets_solo_options">
                {foreach from=$input.values item='option'}
                    <li class="ets_solo_item">
                        <div class="radio">
                            <label for="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}">
                                <input id="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{$option.id_option|escape:'quotes':'UTF-8'}" {if $fields_value[$input.name] == $option.id_option}checked{/if} type="radio">
                                {$option.name nofilter}
                            </label>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {/if}
    {elseif $input.type == 'switch'}
        {if $is15}
            <span class="switch prestashop-switch fixed-width-lg">
                {foreach $input.values as $value}
                    <input type="radio" name="{$input.name|escape:'quotes':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'quotes':'UTF-8'}_on"{else} id="{$input.name|escape:'quotes':'UTF-8'}_off"{/if} value="{$value.value|intval}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                    {strip}
                    <label {if $value.value == 1} for="{$input.name|escape:'quotes':'UTF-8'}_on"{else} for="{$input.name|escape:'quotes':'UTF-8'}_off"{/if}>
                        {if $value.value == 1}
                            {l s='Yes' mod='ets_sociallogin'}
                        {else}
                            {l s='No' mod='ets_sociallogin'}
                        {/if}
                    </label>
                {/strip}
                {/foreach}
                <a class="slide-button btn"></a>
            </span>
        {else}
            {$smarty.block.parent}
            {if $input.name == 'ETS_SOLO_PAYPAL_SANDBOX_MODE'}
                {assign var='link_live_mode' value="https://developer.paypal.com/api/rest/production/#go-live-checklist"}
                <p class="help-block"><a class="ets_solo_paypal_link" target="_blank" href="{$link_live_mode|escape:'html':'UTF-8'}">{$link_live_mode|escape:'html':'UTF-8'}</a></p>
            {/if}
        {/if}
    {else}
        {$smarty.block.parent}
        {if $fieldset.form.name == 'social_networks' && isset($input.group) && (isset($networks[$input.group].doc) || isset($networks[$input.group].link)) && isset($link_document) && $link_document}
            {if $input.name === 'ETS_SOLO_PINTEREST_APP_ID' || $input.name === 'ETS_SOLO_PINTEREST_APP_SECRET'}
                <a target="_blank" class="ets_solo_help_link"
                   href="https://developers.pinterest.com/docs/getting-started/set-up-app/"
                   title="{l s='Where do I get this info?' mod='ets_sociallogin'}">
                    {l s='Where do I get this info?' mod='ets_sociallogin'}
                </a>
            {elseif $input.name === 'ETS_SOLO_FOURSQUARE_APP_ID' || $input.name === 'ETS_SOLO_FOURSQUARE_APP_SECRET'}
                <a target="_blank" class="ets_solo_help_link"
                   href="https://location.foursquare.com/developer/reference/places-api-get-started"
                   title="{l s='Where do I get this info?' mod='ets_sociallogin'}">
                    {l s='Where do I get this info?' mod='ets_sociallogin'}
                </a>
            {else}
                <a target="_blank" class="ets_solo_help_link"
                   href="{if isset($networks[$input.group].doc) && $networks[$input.group].doc}{if $networks[$input.group].doc == 'Get_WindowLive_API_key'}{$networks[$input.group].link_new|escape:'quotes':'UTF-8'}{else}{$link_document|cat:'doc='|cat:$networks[$input.group].doc|escape:'quotes':'UTF-8'}{/if}{elseif isset($networks[$input.group].link) && $networks[$input.group].link}{$networks[$input.group].link nofilter}{else}#{/if}"
                   title="{l s='Where do I get this info?' mod='ets_sociallogin'}">
                    {l s='Where do I get this info?' mod='ets_sociallogin'}
                </a>
            {/if}
        {/if}
    {/if}
{/block}
{block name="input_row"}
    {if $input.name == 'ETS_SOLO_REDUCTION_AMOUNT'}
        <div class="form-group-wrapper ets_solo_discount form-group-amount">
    {/if}
    {if $fieldset.form.name == 'social_networks' && isset($networks) && $networks && $input.name != 'ETS_SOLO_NETWORKS_ORDER'}
        {if $input.name == 'ETS_SOLO_FACEBOOK_ENABLED'}
            {include file="./menu-social.tpl"}
            <div class="ets_solo_form">
            <div class="ets_solo_form_item {$input.group|escape:'html':'UTF-8'}" data-group="{$input.group|escape:'html':'UTF-8'}" data-social="{$networks[$input.group]['label']|upper|escape:'html':'UTF-8'}">
            <h3>{$networks[$input.group]['name']|escape:'quotes':'UTF-8'}</h3>
            {assign var='id_social_network' value = $input.group}
        {/if}
        {if $input.group != 'fb' && $input.name == 'ETS_SOLO_'|cat: $networks[$input.group]['label']|upper|cat:'_ENABLED'}
            <p class="ets_solo_required">
                {l s='%s requires that you create an external application linking your website to their API. To know how to create this application click on "Where do I get this info?" and follow steps.' sprintf=[$networks[$id_social_network]['name']|ucfirst|escape:'html':'UTF-8']  mod='ets_sociallogin'}
            </p>
            </div><div class="ets_solo_form_item {$input.group|escape:'html':'UTF-8'}" data-group="{$input.group|escape:'html':'UTF-8'}" data-social="{$networks[$input.group]['label']|upper|escape:'html':'UTF-8'}">
            <h3>{$networks[$input.group]['name']|escape:'quotes':'UTF-8'}</h3>
        {/if}
        {assign var='id_social_network' value = $input.group}
        <div class="form-group-wrapper ets_solo_social_networks row_{$input.name|lower|escape:'html':'UTF-8'}" {if isset($input.group) && $input.group} data-group="{$input.group|escape:'html':'UTF-8'}"{/if}>{$smarty.block.parent}</div>
        {if $input.name=='ETS_SOLO_BLIZZARD_CALLBACK'}
            <p class="ets_solo_required">
                {l s='%s requires that you create an external application linking your website to their API. To know how to create this application click on "Where do I get this info?" and follow steps.' sprintf=[$networks[$input.group]['name']|ucfirst|escape:'html':'UTF-8']  mod='ets_sociallogin'}
            </p>
            </div>
            {*button save social network*}
            {if isset($fieldset.form.name) && $fieldset.form.name == 'social_networks'}
                {capture name='form_submit_btn'}{counter name='form_submit_btn'}{/capture}
                {if isset($fieldset['form']['submit']) || isset($fieldset['form']['buttons'])}
                    <div class="panel-footer">
                        {if isset($fieldset['form']['submit']) && !empty($fieldset['form']['submit'])}
                            <button type="submit" value="1"	id="{if isset($fieldset['form']['submit']['id'])}{$fieldset['form']['submit']['id']|escape:'html':'UTF-8'}{else}{$table|escape:'html':'UTF-8'}_form_submit_btn{/if}{if $smarty.capture.form_submit_btn > 1}_{($smarty.capture.form_submit_btn - 1)|intval}{/if}" name="{if isset($fieldset['form']['submit']['name'])}{$fieldset['form']['submit']['name']|escape:'html':'UTF-8'}{else}{$submit_action|escape:'html':'UTF-8'}{/if}" class="{if isset($fieldset['form']['submit']['class'])}{$fieldset['form']['submit']['class']|escape:'html':'UTF-8'}{else}btn btn-default pull-left{/if}">
                                <i class="{if isset($fieldset['form']['submit']['icon'])}{$fieldset['form']['submit']['icon']|escape:'html':'UTF-8'}{else}process-icon-save{/if}"></i> {$fieldset['form']['submit']['title']|escape:'html':'UTF-8'}
                            </button>
                        {/if}
                    </div>
                {/if}
            {/if}
            </div>
        {/if}
    {else}
        {assign var="form_design" value=($fieldset.form.name == 'design' && isset($hooks) && $hooks && isset($pos) && $pos)}
        {if $form_design  && $input.name == 'ETS_SOLO_DISPLAY_SOCIAL_PAGE'}
            <div class="ets_solo_menu_hooks">
                <ul>
                    {foreach from=$hooks item='hook'}
                    <li class="ets_solo_menu_item{if $pos|lower == '_'|cat:$hook.id_option|lower} s active{/if}" data-pos="{$hook.id_option|upper|escape:'html':'UTF-8'}">
                        <a class="ets_solo_menu_item" href="{$design_link|cat:'&pos='|cat:$hook.id_option|escape:'html':'UTF-8'}">{$hook.name|escape:'html':'UTF-8'}</a>
                        <label class="ets_solo_position{if in_array($hook.id_option, $social_pages)} active{/if}" data-pos="{$hook.id_option|escape:'html':'UTF-8'}">
                            <span class="ets_solo_position_label on">{l s='On' mod='ets_sociallogin'}</span>
                            <span class="ets_solo_position_label off">{l s='Off' mod='ets_sociallogin'}</span>
                        </label>
                    </li>{/foreach}
                </ul>
            </div>
            <div class="ets_solo_form_design">
                <div class="ets_solo_form_design_left">
                    {if isset($hooks.$pos.name)}
                        <div class="form-group-wrapper ets_solo_default">
                            <h3 class="ets_solo_form_title">{$hooks.$pos.name|escape:'html':'UTF-8'}</h3>
                        </div>
                    {/if}
        {/if}
        <div class="form-group-wrapper  {if isset($fieldset.form.name) && $fieldset.form.name == 'design' && $input.name !=('ETS_SOLO_LOGIN_DEFAULT'|cat:$pos) && $input.name!='ETS_SOLO_LOGIN_POSITION'}ets_solo_default{/if} {if isset($input.group) && $input.group}ets_solo_{$input.group|escape:'quotes':'UTF-8'}{/if} row_{$input.name|lower|escape:'html':'UTF-8'}">
            {if $form_design && $input.name == 'ETS_SOLO_LOGIN_BUTTON_TYPE_CUS'}<p class="help-block">{l s='To use "custom hook", put [1]{hook h="displaySoLoSocialLogin"}[/1] on tpl file where you want to display the social login buttons.' mod='ets_sociallogin' tags=['<span class="ets_solo_pink">']}</p>{/if}
            {$smarty.block.parent}
        </div>
        {if $form_design && $input.name == 'ETS_SOLO_GOOGLE_THEME'|cat:$pos}</div><div id="ets_solo_panel" class="ets_solo_panel">{hook h='displaySoloPreview'}</div></div>{/if}
    {/if}
    {if $input.name == 'ETS_SOLO_REDUCTION_TAX'}
        </div>
    {/if}
{/block}
{block name="footer"}
    {if isset($fieldset.form.name) && $fieldset.form.name != 'social_networks'}{$smarty.block.parent}{/if}
    <div class="ets_solo_paypal_popup">
        <span class="ets_solo_paypal_close" title="{l s='Close' mod='ets_sociallogin'}">{l s='Close' mod='ets_sociallogin'}</span>
        <div class="ets_solo_pay_pal_message">
            <p>{l s='If your PayPal app is in SANDBOX mode, you can only login with the PayPal account of your self – the administration via Social Login. To enable login with PayPal for your customers, you need to active your app to LIVE mode.' mod='ets_sociallogin'}</p>
        </div>
        <div class="ets_solo_pay_pal_btns">
            <button class="ets_solo_paypal_btn_ok btn btn-primary" name="ets_solo_paypal_btn_ok" type="button"><i class="icon-ok-circle"></i>&nbsp;{l s='Ok, I got it' mod='ets_sociallogin'}</button>
            <button class="ets_solo_paypal_btn_no btn btn-default" name="ets_solo_paypal_btn_no" type="button"><i class="icon-cancel"></i>&nbsp;{l s='Cancel' mod='ets_sociallogin'}</button>
        </div>
    </div>
{/block}
