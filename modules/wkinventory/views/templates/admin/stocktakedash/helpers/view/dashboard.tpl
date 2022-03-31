{*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{* TOOLBAR FOR PS 1.5 *}
{if $is_before_16}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title={$title_page|escape:'html':'UTF-8'}}
{/if}
<div class="panel">
    <div class="panel-heading"><i class="icon-link"></i> {l s='Manage Module' mod='wkinventory'}</div>
    {foreach $module_tabs AS $module_tab}
        {assign var=tabname value=$module_tab.name}
        {if !$module_tab['is_tool'] && !$module_tab['is_hidden']}
        <div class="col-lg-2">
          <div class="panel text-center center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/{$module_tab.ico|escape:'html':'UTF-8'}"><br /><br />
          <a href="{$link->getAdminLink({$module_tab.className|escape:'html':'UTF-8'})|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-arrow-circle-right"></i>&nbsp;{if !isset($tabname[$lang_iso])}{$tabname['en']|escape:'html':'UTF-8'}{else}{$tabname[$lang_iso]|escape:'html':'UTF-8'}{/if}</a>
            </div>
        </div>
        {/if}
    {/foreach}
	<div style="clear:both"></div>
</div>

<div style="clear:both"></div>
<div class="panel">
    <div class="panel-heading"><i class="icon-cog"></i> {l s='Logs & Configuration' mod='wkinventory'}</div>
    {foreach $module_tabs AS $module_tab}
        {assign var=tabname value=$module_tab.name}
        {if $module_tab['is_tool']}
        <div class="col-lg-2">
          <div class="panel text-center center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/{$module_tab.ico|escape:'html':'UTF-8'}"><br /><br />
          <a href="{$link->getAdminLink({$module_tab.className|escape:'html':'UTF-8'})|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-cog"></i>&nbsp;{if !isset($tabname[$lang_iso])}{$tabname['en']|escape:'html':'UTF-8'}{else}{$tabname[$lang_iso]|escape:'html':'UTF-8'}{/if}</a>
            </div>
        </div>
        {/if}
    {/foreach}
    <div class="col-lg-2">
      <div class="panel text-center center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/config.png"><br /><br />
      <a href="{$url_config|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-cog"></i>&nbsp;{l s='Configuration' mod='wkinventory'}</a>
      </div>
    </div>
    <div style="clear:both"></div>
</div>
