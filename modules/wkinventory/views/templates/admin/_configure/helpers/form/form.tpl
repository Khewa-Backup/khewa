{*
* This file is part of the 'WK Advanced Search Block' module feature
* Developped by Khoufi Wissem (2015).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
* @author    Khoufi Wissem - K.W
* @copyright Khoufi Wissem
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends file="helpers/form/form.tpl"}

{block name="description"}
    {if isset($input.desc) && !empty($input.desc)}
        <p class="{if $is_before_16}preference_description{else}help-block{/if}">
            {if is_array($input.desc)}
                {foreach $input.desc as $p}
                    {if is_array($p)}
                        <span id="{$p.id|escape:'html':'UTF-8'}">{$p.text|escape:'html':'UTF-8'}</span><br />
                    {else}
                        {$p}{* HTML CONTENT *}<br />
                    {/if}
                {/foreach}
            {else}
            	{if $input.name == 'WKINVENTORY_PREFIX_CODE'}
                    <div class="alert alert-warning"> {l s='For example : Germany --> 400, France --> 300, Spain --> 840' mod='wkinventory'}<br/>{l s='Please refer to' mod='wkinventory'} GS1.org : <a href="http://www.gs1.org/barcodes/support/prefix_list" target="_blank">http://www.gs1.org/barcodes/support/prefix_list</a></div>
                {else}
                	{$input.desc}{* HTML CONTENT *}
                {/if}
            {/if}
        </p>
    {/if}
{/block}

{block name="label"}
    {if isset($input.label) && $input.type == 'free'}
    	{if $input.name == 'option_settings'}
		<div class="left-free-block">{$input.label|escape:'html':'UTF-8'}</div>
    	{/if}
    	{if $input.name == 'help_tab'}
        <div id="addons-rating-container" class="ui-widget note">
            <div style="margin-bottom: 20px; padding: 1em; text-align: center;" class="ui-state-highlight ui-corner-all">
                <p class="invite">
                    {l s='You are satisfied with our module and want to encourage us to add new features' mod='wkinventory'} ?
                    <br/>
                    <a href="http://addons.prestashop.com/ratings.php" target="_blank"><strong>{l s='Please rate it on Prestashop Addons, and give us 5 stars !' mod='wkinventory'}</strong></a>
                </p>
            </div>
        </div>

        <div class="col-lg-3"><img src="{$this_path|escape:'html':'UTF-8'}/views/img/inventory-img.png" /></div>
        <div class="col-lg-8">
            <p>{l s='This module is created to manage your stock inventory' mod='wkinventory'} :</p>
            <ul>
                <li>{l s='Possibility to create and manage [1]multiple inventories[/1]' tags=['<strong>'] mod='wkinventory'}.</li>
                <li>{l s='The inventory can be performed [1]manually[/1] or using a [1]scan[/1]' tags=['<strong>'] mod='wkinventory'}.</li>
                <li>{l s='Possibility to [1]manage inventories while leaving open your online store[/1] and [1]keep selling[/1]' tags=['<strong>'] mod='wkinventory'}.</li>
                <li>{l s='Generate [1]valid EAN and UPC codes to marketplace[/1] for all your products/combinations or only for [1]missing codes[/1] to facilitate management of your stock' tags=['<strong>'] mod='wkinventory'}.</li>
                <li>{l s='EAN and UPC codes are generated automatically [1]respecting algorithms and specific rules[/1] that respects our module' tags=['<strong>'] mod='wkinventory'}.</li>
                <li>{l s='Can be used for [1]Multi-shops[/1]' tags=['<strong>'] mod='wkinventory'}.</li>
            </ul>
        </div>
        <div class="clear"></div>
    	{/if}
    {else}
		{$smarty.block.parent}
    {/if}
{/block}
