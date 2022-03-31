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
{extends file="helpers/list/list_header.tpl"}
{block name="override_header"}
	<div class="panel">
		<h3>
			<i class="icon-warning-sign"></i> {l s='Severity levels' mod='wkinventory'}
		</h3>
		<p>{l s='Meaning of severity levels:' mod='wkinventory'}</p>
		<ol>
			<li><span class="badge badge-success">{l s='Informative only' mod='wkinventory'}</span></li>
			<li><span class="badge badge-warning">{l s='Warning' mod='wkinventory'}</span></li>
			<li><span class="badge badge-danger">{l s='Error' mod='wkinventory'}</span></li>
			<li><span class="badge badge-critical">{l s='Major issue (crash)!' mod='wkinventory'}</span></li>
		</ol>
	</div>
{/block}
