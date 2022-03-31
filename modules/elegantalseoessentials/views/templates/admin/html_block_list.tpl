{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel elegantal_panel">
        <div class="panel-heading">
            <i class="icon-code"></i> HTML Blocks
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate">
                    <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Add New' mod='elegantalseoessentials'}" data-placement="top">
                        <i class="process-icon-new"></i>
                    </span>
                </a>
            </span>
        </div>
        <div class="panel-body">
            {if $models}
                <div>
                    <table class="table table-hover elegantal_sortable_table">
                        <thead>
                            <tr>
                                <th style="width: 5%">&nbsp;</th>
                                <th style="width: 15%">
                                    {l s='Name' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=name&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=name&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="width: 20%">
                                    HTML 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=html&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=html&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="width: 20%">
                                    {l s='Pages' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=pages&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=pages&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="width: 20%">
                                    {l s='Hooks' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=hooks&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=hooks&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center" style="width: 10%">
                                    {l s='Status' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=is_active&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy=is_active&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="min-width: 100px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$models item=model}
                                <tr data-id="{$model.id_elegantalseoessentials_html|intval}">
                                    <td class="text-center position_handle" style="width: 5%">
                                        <span class="position_arrow"><i class="icon-sort"></i></span> 
                                    </td>
                                    <td style="width: 15%">
                                        {$model.name|escape:'htmlall':'UTF-8'}
                                    </td>
                                    <td title="{$model.html|escape:'htmlall':'UTF-8'}" style="width: 20%">
                                        {$model.html|truncate:90:'...'|escape:'htmlall':'UTF-8'}
                                    </td>
                                    <td title="{$model.pages|escape:'htmlall':'UTF-8'}" style="width: 20%">
                                        {$model.pages|truncate:90:'...'|escape:'htmlall':'UTF-8'}
                                    </td>
                                    <td title="{$model.hooks|escape:'htmlall':'UTF-8'}" style="width: 20%">
                                        {$model.hooks|truncate:90:'...'|escape:'htmlall':'UTF-8'}
                                    </td>
                                    <td class="text-center" style="width: 10%">
                                        {if $model.is_active}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockChangeStatus&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}">
                                                <i class="icon-check" style="color: #72C279"></i>
                                            </a>
                                        {else}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockChangeStatus&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}">
                                                <i class="icon-remove" style="color: #E08F95"></i>
                                            </a>
                                        {/if}
                                    </td>
                                    <td class="text-right" style="width: 10%">
                                        <div class="btn-group" role="group">
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}" class="btn btn-default">
                                                <i class="icon-edit"></i> {l s='Edit' mod='elegantalseoessentials'}
                                            </a>
                                            <a href="" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                {if $model.is_active == 1}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockChangeStatus&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}"><i class="icon-off"></i> {l s='Disable' mod='elegantalseoessentials'}</a>
                                                    </li>
                                                {else}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockChangeStatus&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}"><i class="icon-off"></i> {l s='Enable' mod='elegantalseoessentials'}</a>
                                                    </li>
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockDuplicate&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}"><i class="icon-copy"></i> {l s='Duplicate' mod='elegantalseoessentials'}</a>
                                                </li>
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockDelete&id_elegantalseoessentials_html={$model.id_elegantalseoessentials_html|intval}" onclick="return confirm('Are you sure?')"><i class="icon-trash"></i> {l s='Delete' mod='elegantalseoessentials'}</a>
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
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page=1" aria-label="Previous">
                                                <span aria-hidden="true">&lt;&lt; {l s='First' mod='elegantalseoessentials'}</span>
                                            </a>
                                        </li>
                                        {if $pPrev > 1}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pPrev|intval}" aria-label="Previous">
                                                    <span aria-hidden="true">&lt; {l s='Prev' mod='elegantalseoessentials'}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/if}
                                    {for $i=$pStart to $pages max=$pMax}
                                        <li{if $i == $currentPage} class="active" onclick="return false;"{/if}>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$i|intval}">{$i|intval}</a>
                                        </li>
                                    {/for}
                                    {if $pNext > $currentPage && $pNext <= $pages}
                                        {if $pNext < $pages}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pNext|intval}" aria-label="Next">
                                                    <span aria-hidden="true">{l s='Next' mod='elegantalseoessentials'} &gt;</span>
                                                </a>
                                            </li>
                                        {/if}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pages|intval}" aria-label="Next">
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
                        {l s='You have not created HTML blocks yet.' mod='elegantalseoessentials'} <br>

                    </p>
                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate" style="font-size: 14px;">
                        <i class="icon-plus" style="font-size:10px"></i>
                        {l s='Create Now' mod='elegantalseoessentials'}
                    </a>
                </div>
            {/if}
        </div>
        <div class="panel-footer clearfix">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate" class="btn btn-default pull-right">
                <i class="process-icon-new"></i> {l s='New HTML Code' mod='elegantalseoessentials'}
            </a> 
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate&twitter_cards=1" class="btn btn-default pull-right">
                <i class="process-icon-twitter"></i> {l s='Twitter Cards' mod='elegantalseoessentials'}
            </a> 
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=htmlBlockUpdate&facebook_tags=1" class="btn btn-default pull-right">
                <i class="process-icon-facebook"></i> {l s='Facebook Tags' mod='elegantalseoessentials'}
            </a> 
            <a href="{$adminUrl|escape:'html':'UTF-8'}" class="btn btn-default pull-left">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantalseoessentials'}
            </a>
        </div>
    </div>
</div>