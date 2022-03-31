{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel elegantal_panel">
        <div class="panel-heading">
            <i class="icon-warning"></i> {l s='Pages Not Found' mod='elegantalseoessentials'} "404"
        </div>
        <div class="panel-body">
            {if $models}
                <div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    {l s='URL' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=request_uri&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=request_uri&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th>
                                    {l s='Referer' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=http_referer&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=http_referer&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center">
                                    {l s='Date' mod='elegantalseoessentials'}
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=date_add&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy=date_add&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$models item=model}
                                <tr>
                                    <td>
                                        <a href="{$shop_url|escape:'html':'UTF-8'}{$model.request_uri|escape:'html':'UTF-8'}" title="{$shop_url|escape:'html':'UTF-8'}{$model.request_uri|escape:'html':'UTF-8'}" target="_blank">
                                            {$model.request_uri|truncate:80:'...'|escape:'html':'UTF-8'}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{$model.http_referer|escape:'html':'UTF-8'}" title="{$model.http_referer|escape:'html':'UTF-8'}" target="_blank">
                                            {$model.http_referer|truncate:50:'...'|escape:'html':'UTF-8'}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {$model.date_add|escape:'html':'UTF-8'|date_format:'%e %b %Y &nbsp; %H:%M:%S'}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {*START PAGINATION*}
                    {if $pages > 1}
                        {assign var="pMax" value=2 * $halfVisibleLinks + 1} {*Number of visible pager links*}
                        {assign var="pStart" value=$currentPage - $halfVisibleLinks} {*Starter link*}
                        {assign var="moveStart" value=$currentPage - $pages + $halfVisibleLinks} {*Numbers that pStart can be moved left to fill right side space*}
                        {if $moveStart > 0}
                            {assign var="pStart" value=$pStart - $moveStart}
                        {/if}                                    
                        {if $pStart < 1}
                            {assign var="pStart" value=1}
                        {/if}
                        {assign var="pNext" value=$currentPage + 1} {*Next page*}
                        {if $pNext > $pages}
                            {assign var="pNext" value=$pages}
                        {/if}
                        {assign var="pPrev" value=$currentPage - 1} {*Previous page*}
                        {if $pPrev < 1}
                            {assign var="pPrev" value=1}
                        {/if}
                        <div class="text-center">
                            <br>
                            <nav>
                                <ul class="pagination pagination-sm">
                                    {if $pPrev < $currentPage}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page=1" aria-label="Previous">
                                                <span aria-hidden="true">&lt;&lt; {l s='First' mod='elegantalseoessentials'}</span>
                                            </a>
                                        </li>
                                        {if $pPrev > 1}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pPrev|intval}" aria-label="Previous">
                                                    <span aria-hidden="true">&lt; {l s='Prev' mod='elegantalseoessentials'}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/if}
                                    {for $i=$pStart to $pages max=$pMax}
                                        <li{if $i == $currentPage} class="active" onclick="return false;"{/if}>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$i|intval}">{$i|intval}</a>
                                        </li>
                                    {/for}
                                    {if $pNext > $currentPage && $pNext <= $pages}
                                        {if $pNext < $pages}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pNext|intval}" aria-label="Next">
                                                    <span aria-hidden="true">{l s='Next' mod='elegantalseoessentials'} &gt;</span>
                                                </a>
                                            </li>
                                        {/if}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pages|intval}" aria-label="Next">
                                                <span aria-hidden="true">{l s='Last' mod='elegantalseoessentials'} &gt;&gt;</span>
                                            </a>
                                        </li>
                                    {/if}
                                </ul>
                            </nav>
                        </div>
                    {/if}
                    {*END PAGINATION*}
                </div>
            {else}
                <div class="text-center" style="padding:20px">
                    <p style="color: #999; font-size: 22px;">
                        {l s='404 error pages were not found in your shop.' mod='elegantalseoessentials'} <br>
                    </p>
                </div>
            {/if}
        </div>
        <div class="panel-footer clearfix">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=pageNotFoundDownload" class="btn btn-default pull-right">
                <i class="process-icon-upload"></i> {l s='Export to CSV' mod='elegantalseoessentials'}
            </a>
            <a href="{$adminUrl|escape:'html':'UTF-8'}" class="btn btn-default pull-left">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantalseoessentials'}
            </a>
        </div>
    </div>
</div>