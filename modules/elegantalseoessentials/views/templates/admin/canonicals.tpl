{*
* @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
* @copyright (c) 2022, Jamoliddin Nasriddinov
* @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
*}
<div class="elegantalBootstrapWrapper">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-link"></i> {l s='Custom Canonical URLs' mod='elegantalseoessentials'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn" href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalEdit">
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
                                    {l s='Old URL' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=old_url&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=old_url&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th>
                                    {l s='New URL' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=new_url&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=new_url&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th class="text-center" style="min-width: 80px">
                                    {l s='Status' mod='elegantalseoessentials'} 
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=is_active&orderType=desc"><i class="icon-caret-down"></i></a>
                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy=is_active&orderType=asc"><i class="icon-caret-up"></i></a>
                                </th>
                                <th style="min-width: 100px">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$models item=model}
                                <tr>
                                    <td>
                                        <a href="{$shop_url|escape:'html':'UTF-8'}{$model.old_url|escape:'html':'UTF-8'}" target="_blank">
                                            {$shop_url|escape:'html':'UTF-8'}{$model.old_url|escape:'html':'UTF-8'}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{$shop_url|escape:'html':'UTF-8'}{$model.new_url|escape:'html':'UTF-8'}" target="_blank">
                                            {$shop_url|escape:'html':'UTF-8'}{$model.new_url|escape:'html':'UTF-8'}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {if $model.is_active}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalChangeStatus&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}">
                                                <i class="icon-check" style="color: #72C279"></i>
                                            </a>
                                        {else}
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalChangeStatus&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}">
                                                <i class="icon-remove" style="color: #E08F95"></i>
                                            </a>
                                        {/if}
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group" role="group">
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalEdit&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}" class="btn btn-default">
                                                <i class="icon-edit"></i> {l s='Edit' mod='elegantalseoessentials'}
                                            </a>
                                            <a href="" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                {if $model.is_active == 1}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalChangeStatus&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}">
                                                            <i class="icon-off"></i> {l s='Disable' mod='elegantalseoessentials'}
                                                        </a>
                                                    </li>
                                                {else}
                                                    <li>
                                                        <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalChangeStatus&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}">
                                                            <i class="icon-off"></i> {l s='Enable' mod='elegantalseoessentials'}
                                                        </a>
                                                    </li>
                                                {/if}
                                                <li>
                                                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalDelete&id_elegantalseoessentials_canonicals={$model.id_elegantalseoessentials_canonicals|intval}" onclick="return confirm('{l s='Are you sure you want to delete this?' mod='elegantalseoessentials'}')">
                                                        <i class="icon-trash-o"></i> {l s='Delete' mod='elegantalseoessentials'}
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
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page=1" aria-label="Previous">
                                                <span aria-hidden="true">&lt;&lt; {l s='First' mod='elegantalseoessentials'}</span>
                                            </a>
                                        </li>
                                        {if $pPrev > 1}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pPrev|intval}" aria-label="Previous">
                                                    <span aria-hidden="true">&lt; {l s='Prev' mod='elegantalseoessentials'}</span>
                                                </a>
                                            </li>
                                        {/if}
                                    {/if}
                                    {for $i=$pStart to $pages max=$pMax}
                                        <li{if $i == $currentPage} class="active" onclick="return false;"{/if}>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$i|intval}">{$i|intval}</a>
                                        </li>
                                    {/for}
                                    {if $pNext > $currentPage && $pNext <= $pages}
                                        {if $pNext < $pages}
                                            <li>
                                                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pNext|intval}" aria-label="Next">
                                                    <span aria-hidden="true">{l s='Next' mod='elegantalseoessentials'} &gt;</span>
                                                </a>
                                            </li>
                                        {/if}
                                        <li>
                                            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalsList&orderBy={$orderBy|escape:'html':'UTF-8'}&orderType={$orderType|escape:'html':'UTF-8'}&page={$pages|intval}" aria-label="Next">
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
                        {l s='You have not created custom canonical URLs yet.' mod='elegantalseoessentials'} <br>

                    </p>
                    <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalEdit" style="text-decoration: none;font-size: 14px;">
                        <i class="icon-plus" style="font-size:10px"></i>
                        {l s='Create Now' mod='elegantalseoessentials'}
                    </a>
                </div>
            {/if}
        </div>
        <div class="panel-footer clearfix">
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalEdit" class="btn btn-default pull-right">
                <i class="process-icon-new"></i> {l s='Add New' mod='elegantalseoessentials'}
            </a>
            {if $models}
                <a href="{$adminUrl|escape:'html':'UTF-8'}&event=canonicalDeleteAll" class="btn btn-default pull-right" onclick="return confirm('{l s='Are you sure?' mod='elegantalseoessentials'}')">
                    <i class="process-icon-delete"></i> {l s='Delete All' mod='elegantalseoessentials'}
                </a>
            {/if}
            <a href="{$adminUrl|escape:'html':'UTF-8'}&event=editSettingsCanonical" class="btn btn-default pull-left">
                <i class="process-icon-back"></i> {l s='Back' mod='elegantalseoessentials'}
            </a>
        </div>
    </div>
</div>