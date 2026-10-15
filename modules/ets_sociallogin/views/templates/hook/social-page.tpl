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
{if isset($connects) && $connects}
    <div class="ets_solo_myaccount_wrapper">
        <h3 class="ets_solo_title">{l s='Your social networks' mod='ets_sociallogin'}</h3>
        <div class="ets_solo_networks_login">
            <table class="table">
                <thead>
                <th>{l s='Social network' mod='ets_sociallogin'}</th>
                <th>{l s='Last login time' mod='ets_sociallogin'}</th>
                <th class="text-center">{l s='Connect your social account' mod='ets_sociallogin'}</th>
                </thead>
                <tbody>
                {foreach from=$connects item='connect'}
                    <tr>
                        <td>
                            <span class="ets_solo_network {if $connect.identifier|trim !== ''}active{/if} net_{if $connect.last_login_type != 'ms'}{$socials[$connect.last_login_type].label|lower|escape:'html':'UTF-8'}{else}microsoft{/if}">
                                <i class="fa fa-{if $connect.last_login_type != 'ms'}{$socials[$connect.last_login_type].label|lower|escape:'html':'UTF-8'}{else}microsoft{/if} icon icon-{if $connect.last_login_type != 'ms'}{$socials[$connect.last_login_type].label|lower|escape:'html':'UTF-8'}{else}microsoft{/if}">
                                    {if $connect.last_login_type == 'ms'}<svg width="16" height="16" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg"><path fill="#F25022" d="M1 1h10v10H1z"></path><path fill="#7FBA00" d="M12 1h10v10H12z"></path><path fill="#00A4EF" d="M1 12h10v10H1z"></path><path fill="#FFB900" d="M12 12h10v10H12z"></path></svg>{/if}
                                </i>

                                {$socials[$connect.last_login_type].name|escape:'html':'UTF-8'}
                            </span>
                        </td>
                        <td>
                            <span class="ets_solo_timer">{if isset($connect.last_login_time) && $connect.last_login_time}{$connect.last_login_time|escape:'html':'UTF-8'}{else}{l s='Never connected' mod='ets_sociallogin'}{/if}</span>
                        </td>
                        <td class="text-center">
                            {if $connect.identifier|trim !== ''}
                                <a href="?disconnect={$connect.last_login_type|escape:'html':'UTF-8'}" class="btn btn-default ets_solo_disconnect">
                                    {l s='Disconnect' mod='ets_sociallogin'}
                                </a>
                            {else}
                                <span class="btn btn-default ets_solo_social_item" data-auth="{$socials[$connect.last_login_type].label|escape:'html':'UTF-8'}">
                                    {l s='Connect' mod='ets_sociallogin'}
                                </span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{else}
    <div class="alert alert-danger">{l s='No permission.' mod='ets_sociallogin'}</div>
{/if}