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
{if $type == 'img'}
    <a {if $tr.profile_url}target="_blank"{/if} class="{if $tr.profile_url}ets_solo_link{/if}" href="{if $tr.profile_url}{$tr.profile_url|escape:'quotes':'UTF-8'}{else}javascript:void(0){/if}" title="{if $tr.profile_url}{l s='Open profile page' mod='ets_sociallogin'}{else}{l s='No profile page URL available' mod='ets_sociallogin'}{/if}">
        <img width="80" src="{if $tr.network == 'Tumblr'}https://api.tumblr.com/v2/blog/{preg_replace('#^https?://#', '', $tr.profile_url)|escape:'quotes':'UTF-8'}avatar{else}{if $tr.profile_img}{$tr.profile_img|escape:'quotes':'UTF-8'}{else}{$img_base_dir nofilter}views/img/profile.jpg{/if}{/if}" alt="{$tr.network|escape:'html':'UTF-8'}" />
        {* if $tr.profile_url && ($tr.network !='Yahoo' && $tr.network !='Google')}<span>{l s='View info' mod='ets_sociallogin'} <i class="fa fa-external-link" aria-hidden="true"></i></span>{/if *}
    </a>
{elseif $type == 'link'}
    <div class="btn-group-action">
        <a href="{$link|escape:'quotes':'UTF-8'}" class="btn btn-default" title="{l s='View' mod='ets_sociallogin'}">
            <i class="icon-eye"></i> {l s='View' mod='ets_sociallogin'}
        </a>
    </div>
{elseif $type == 'shortcode'}
    <span class="ets_solo_short_code" title="{l s='Click to copy' mod='ets_sociallogin'}">{$tr.short_code|escape:'html':'UTF-8'}</span>
{elseif $type == 'badge'}
    <span class="badge badge-{$badgeType|escape:'quotes':'UTF-8'}">{$value|escape:'html':'UTF-8'}</span>
{elseif $type == 'rule'}
    <p class="solo_rule">{$value|escape:'html':'UTF-8'}</p>
    <span class="solo_rule_label" style="color: #999999;">{$label|escape:'html':'UTF-8'}</span>
{/if}
