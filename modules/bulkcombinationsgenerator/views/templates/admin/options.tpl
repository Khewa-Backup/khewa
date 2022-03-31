{*
* 2007-2019 Amazzing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
*
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright 2007-2019 Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

{function renderOptionsLevel options = [] root = 1}
	<div class="opt-level{if $root} root{else} child{/if}">
	{foreach $options as $id => $display_name}
		{$children = []}{if !empty($data.id_parent) && !empty($data.options[$id])}{$children = $data.options[$id]}{/if}
		<div class="opt{if $children} has-children{if !$root} closed{/if}{/if}">
			<label class="opt-label">
				<input type="checkbox" class="opt-checkbox" name="{$name|escape:'html':'UTF-8'}[]" value="{$id|intval}">
				<span class="opt-id hidden">{$id|intval}</span>
				<span class="opt-name">{$display_name|escape:'html':'UTF-8'}</span>
				{if $children && !$root}<a href="#" class="icon-folder-open toggleChildren"></a>{/if}
			</label>
			{if $children}
				<span class="checked-num hidden">(<span class="dynamic-num"></span> {l s='checked' mod='bulkcombinationsgenerator'})</span>
				{renderOptionsLevel options = $children root = 0}
			{/if}
		</div>
	{/foreach}
	</div>
{/function}

<div class="selected-options-inline{if empty($fixed_name)} dynamic-name{/if}">
	<span class="all">
		{if empty($fixed_name)}
			{l s='No filters' mod='bulkcombinationsgenerator'}
		{else}
			{$fixed_name|escape:'html':'UTF-8'}
		{/if}
	</span>
	<span class="selected-items"><span class="item-names"></span><span class="total"></span></span>
	<i class="icon-angle-down toggleIndicator"></i>
</div>
<div class="available-options hidden">
	{if !empty($data.id_parent) && !empty($data.options[$data.id_parent])}
		{$options = $data.options[$data.id_parent]}
	{else}
		{$options = $data.options}
	{/if}
	{renderOptionsLevel options = $options}
	<div class="options-footer clearfix">
		<div class="opt-single-action pull-right">
			<label class="opt-action"><input type="checkbox" class="toggleIDs"> {l s='Show IDs' mod='bulkcombinationsgenerator'}</label>
		</div>
		<div class="opt-bulk-actions pull-left">
			{if !empty($data.id_parent)}
				<label class="opt-action bulk" data-bulk-action="open" data-toggle="close">
					<i class="icon-folder-open"></i> {l s='Open all' mod='bulkcombinationsgenerator'}
				</label>
				<label class="opt-action bulk hidden" data-bulk-action="close" data-toggle="open">
					<i class="icon-folder-close"></i> {l s='Close all' mod='bulkcombinationsgenerator'}
				</label>
			{/if}
			<label class="opt-action bulk" data-bulk-action="check" data-toggle="uncheck">
				<i class="icon-check-sign"></i> {l s='Check all' mod='bulkcombinationsgenerator'}
			</label>
			<label class="opt-action bulk hidden" data-bulk-action="uncheck" data-toggle="check">
				<i class="icon-check-empty"></i> {l s='Uncheck' mod='bulkcombinationsgenerator'}
			</label>
			{if empty($fixed_name)}
				<label class="opt-action bulk" data-bulk-action="invert">
					<span class="txt"><i class="icon-random"></i> {l s='invert' mod='bulkcombinationsgenerator'}</span>
				</label>
			{/if}
		</div>
	</div>
</div>
{* since 2.0.0 *}
