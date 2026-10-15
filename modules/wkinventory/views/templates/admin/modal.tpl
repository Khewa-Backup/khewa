{*
* This file is part of the 'Wk Stock Manager' module feature
* Developped by Khoufi Wissem (2016).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
* @author    Khoufi Wissem : K.W
* @copyright Khoufi Wissem
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="modal fade" id="{$modal_id|escape:'html':'UTF-8'}" tabindex="-1">
	<div class="modal-dialog {if isset($modal_class)}{$modal_class|escape:'html':'UTF-8'}{/if}">
		<div class="modal-content">
			{if isset($modal_title)}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">{$modal_title|escape:'html':'UTF-8'}</h4>
			</div>
			{/if}
			{include file="./modal_update_progress.tpl"}
			{if isset($modal_actions)}
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='wkinventory'}</button>
				{foreach $modal_actions as $action}
					{if $action.type == 'link'}
						<a href="{$action.href|escape:'html':'UTF-8'}" class="btn {$action.class|escape:'html':'UTF-8'}">{$action.label|escape:'html':'UTF-8'}</a>
					{elseif $action.type == 'button'}
						<button type="button" value="{$action.value|escape:'html':'UTF-8'}" class="btn {$action.class|escape:'html':'UTF-8'}">
							{$action.label|escape:'html':'UTF-8'}
						</button>
					{/if}
				{/foreach}
			</div>
			{/if}
		</div>
	</div>
</div>
