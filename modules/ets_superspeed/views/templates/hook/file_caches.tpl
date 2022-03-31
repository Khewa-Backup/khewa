{*
* 2007-2020 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2021 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($page_caches) && $page_caches}
    <table {if !$file_caches} class="table_nodata" {/if}>
{/if}
        <tr>
            <td class="sitename">
                <span class="title_box">{l s='Url' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='request_uri' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=request_uri&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='request_uri' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=request_uri&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
            <td class="lang_name">
                <span class="title_box">{l s='Language' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='lang_name' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=lang_name&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='lang_name' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=lang_name&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
            <td class="currency">
                <span class="title_box">{l s='Currency' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='iso_code' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=iso_code&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='iso_code' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=iso_code&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
            <td class="country_name">
                <span class="title_box">{l s='Country' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='country_name' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=country_name&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='country_name' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=country_name&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
            <td class="size_mb">
                <span class="title_box">{l s='Size' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='file_size' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=file_size&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='file_size' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=file_size&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
            {if Configuration::get('ETS_RECORD_PAGE_CLICK')}
                <td class="click_site">
                    <span class="title_box">{l s='Click(s)' mod='ets_superspeed'}</span>
                    <span class="soft">
                        <a{if $sort=='click' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=click&sort_type=desc">
                            <i class="icon-caret-down"></i>
                        </a>
                        <a{if $sort=='click' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=click&sort_type=asc">
                            <i class="icon-caret-up"></i>
                        </a>
                    </span>
                </td>
            {/if}
            <td class="date_cache">
                <span class="title_box">{l s='Date' mod='ets_superspeed'}</span>
                <span class="soft">
                    <a{if $sort=='date_upd' && $sort_type=='desc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=date_upd&sort_type=desc">
                        <i class="icon-caret-down"></i>
                    </a>
                    <a{if $sort=='date_upd' && $sort_type=='asc'} class="active"{/if} href="{$page_current_url|escape:'html':'UTF-8'}&sort=date_upd&sort_type=asc">
                        <i class="icon-caret-up"></i>
                    </a>
                </span>
            </td>
        </tr>
{if $file_caches}
    {foreach from=$file_caches item='file_cache'}
         <tr>
            <td class="sitename"><a href="/..{$file_cache.request_uri|escape:'html':'UTF-8'}" title="{if $file_cache.request_uri && $file_cache.request_uri!='/'}{$file_cache.request_uri|escape:'html':'UTF-8'}{else}{l s='Home' mod='ets_superspeed'}{/if}" target="_blank">{if isset($file_cache.name_display)}{$file_cache.name_display|escape:'html':'UTF-8'}{else}{if $file_cache.request_uri && $file_cache.request_uri!='/'}{$file_cache.request_uri|escape:'html':'UTF-8'}{else}{l s='Home' mod='ets_superspeed'}{/if}{/if}</a></td>
            <td class="lang_name">{if $file_cache.lang_name}{$file_cache.lang_name|escape:'html':'UTF-8'}{else}--{/if}</td>
            <td class="currency">{if $file_cache.iso_code}{$file_cache.iso_code|escape:'html':'UTF-8'}{else}--{/if}</td>
            <td class="country_name">{if $file_cache.country_name}{$file_cache.country_name|escape:'html':'UTF-8'}{else}--{/if}</td>
            <td class="size_mb">{$file_cache.file_size|floatval}KB</td>
            {if Configuration::get('ETS_RECORD_PAGE_CLICK')}
                <td class="click_site">{$file_cache.click|intval}</td>
            {/if}
            <td class="date_cache">{$file_cache.date_upd|escape:'html':'UTF-8'}</td>
        </tr>
    {/foreach} 
    {if !isset($page_caches)}
        <tr>
            <td colspan="4" class="text-center"><a class="btn btn-default link_page_caches" href="{$link->getAdminLink('AdminSuperSpeedPageCaches')|escape:'html':'UTF-8'}&&current_tab=page-list-caches">{l s='View all page caches' mod='ets_superspeed'}</a> </td>
        </tr>
    {/if}
{else}
    <tr>
        <td colspan="7"><p class="not-data">{l s='No data available' mod='ets_superspeed'}</p></td>
    </tr>
{/if}
{if isset($page_caches) && $page_caches}
</table>
{$paggination nofilter}
{/if}