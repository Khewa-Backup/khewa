{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel elegantal_panel">
        <div class="panel-heading">
            <i class="icon-image"></i> SEO Images Alt Rules
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltUpdate">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Add New' mod='elegantalseoessentials'}" data-placement="top">
                        <i class="process-icon-new"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="panel-body">
            {if $models}
                <div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    {l s='Name' mod='elegantalseoessentials'}
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=name&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=name&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th>{l s='Categories' mod='elegantalseoessentials'}</th>
                                <th class="text-center">
                                    {l s='Applied' mod='elegantalseoessentials'}
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=applied_at&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=applied_at&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center">
                                    {l s='Status' mod='elegantalseoessentials'}
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=is_active&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy=is_active&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="min-width: 130px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$models item=model}
                                <tr>
                                    <td>
                                        {$model.name|escape:'html':'UTF-8'}
                                    </td>
                                    <td>
                                        {if $model.categories}
                                            {$model.categories|escape:'html':'UTF-8'}
                                        {else}
                                            {l s='All' mod='elegantalseoessentials'}
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $model.applied_at}
                                            {$model.applied_at|escape:'html':'UTF-8'|date_format:'%e %b %Y %H:%M:%S'}
                                        {else}
                                            -
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        {if $model.is_active}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltChangeStatus&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}">
                                                <i class="icon-check" style="color: #72C279"></i>
                                            </a>
                                        {else}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltChangeStatus&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}">
                                                <i class="icon-remove" style="color: #E08F95"></i>
                                            </a>
                                        {/if}
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group" role="group">
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltUpdate&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}" class="btn btn-default">
                                                <i class="icon-edit"></i> {l s='Edit Rule' mod='elegantalseoessentials'}
                                            </a>
                                            <a href="" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltApply&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}" onclick="return confirm('Apply the rule on all languages?');">
                                                        <i class="icon-refresh"></i> {l s='Apply Rule' mod='elegantalseoessentials'} {if count($languages) > 1}{l s='ALL' mod='elegantalseoessentials'}{/if}
                                                    </a>
                                                </li>
                                                {if count($languages) > 1}
                                                    {foreach from=$languages item=lang}
                                                        <li>
                                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltApply&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}&lang_id={$lang.id_lang|intval}" title="{$lang.name|escape:'html':'UTF-8'}">
                                                                <img src="{$img_lang_dir|escape:'html':'UTF-8'}{$lang.id_lang|intval}.jpg" style="max-width:16px" />&nbsp;
                                                                {l s='Apply Rule' mod='elegantalseoessentials'} <span style="text-transform: uppercase">{$lang.iso_code|escape:'html':'UTF-8'}</span>
                                                            </a>
                                                        </li>
                                                    {/foreach}
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltCron&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}&name={$model.name|escape:'html':'UTF-8'}">
                                                        <i class="icon-time"></i> {l s='Setup CRON' mod='elegantalseoessentials'}
                                                    </a>
                                                </li>
                                                {if $model.is_active}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltChangeStatus&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}">
                                                            <i class="icon-off"></i> {l s='Disable' mod='elegantalseoessentials'}
                                                        </a>
                                                    </li>
                                                {else}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltChangeStatus&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}">
                                                            <i class="icon-off"></i> {l s='Enable' mod='elegantalseoessentials'}
                                                        </a>
                                                    </li>
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltDuplicate&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}">
                                                        <i class="icon-copy"></i> {l s='Duplicate' mod='elegantalseoessentials'}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltDelete&id_elegantalseoessentials_image_alt={$model.id_elegantalseoessentials_image_alt|intval}" onclick="return confirm('Are you sure?');">
                                                        <i class="icon-trash"></i> {l s='Delete' mod='elegantalseoessentials'}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
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
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page=1" aria-label="Previous">
                                                <span aria-hidden="true">&lt;&lt; {l s='First' mod='elegantalseoessentials'}</span>
                                            </a>
                                        </li>
                                        {if $pPrev > 1}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pPrev|intval}" aria-label="Previous">
                                                    <span aria-hidden="true">&lt; {l s='Prev' mod='elegantalseoessentials'}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/if}
                                    {for $i=$pStart to $pages max=$pMax}
                                        <li{if $i == $currentPage} class="active" onclick="return false;"{/if}>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$i|intval}">{$i|intval}</a>
                                        </li>
                                    {/for}
                                    {if $pNext > $currentPage && $pNext <= $pages}
                                        {if $pNext < $pages}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pNext|intval}" aria-label="Next">
                                                    <span aria-hidden="true">{l s='Next' mod='elegantalseoessentials'} &gt;</span>
                                                </a>
                                            </li>
                                        {/if}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pages|intval}" aria-label="Next">
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
                        {l s='You have not created Image Alt rule yet.' mod='elegantalseoessentials'} <br>

                    </p>
                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltUpdate" style="font-size: 14px;">
                        <i class="icon-plus" style="font-size:10px"></i>
                        {l s='Create Now' mod='elegantalseoessentials'}
                    </a>
                </div>
            {/if}
        </div>
        <div class="panel-footer clearfix">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=imageAltUpdate" class="btn btn-default pull-right">
                <i class="process-icon-new"></i> {l s='New Rule' mod='elegantalseoessentials'}
            </a> 
            <a href="{$adminUrl|escape:'html':'UTF-8'}" class="btn btn-default pull-left">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantalseoessentials'}
            </a>
        </div>
    </div>
</div>