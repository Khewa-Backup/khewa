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
{if $column == 'image'}
	{if !empty($url)}<img src="{$url|escape:'html':'UTF-8'}" width="50" height="50" />{/if}
{else if $column == 'url'}
	<a href="{$url|escape:'html':'UTF-8'}" target="_blank" title="{l s='View/Edit Product' mod='wkinventory'}">{$id_product|escape:'html':'UTF-8'}</a>
{else if $column == 'name'}
	{$name|escape:'html':'UTF-8'}<br /><span class="grey-desc">{$combination|escape:'html':'UTF-8'}</span>
{/if}