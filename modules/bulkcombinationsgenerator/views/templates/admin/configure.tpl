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

<div class="bootstrap bcg-container clearfix">
	<div class="att-actions clearfix">
		<a href="#manual-assign" class="tab-option uppercase first active"><i class="icon-cogs"></i> {l s='Prepare attributes for combinations' mod='bulkcombinationsgenerator'}</a><a href="#duplicate-combinations" class="tab-option uppercase last"><i class="icon-copy"></i> {l s='Duplicate combinations from an existing product' mod='bulkcombinationsgenerator'}</a>
		<div class="im-export pull-right">
			<form action="" method="POST" class="inline-block">
				<a href="#" class="no-decoration exportSettings uppercase"
				data-toggle="tooltip" title="{l s='Export settings' mod='bulkcombinationsgenerator'}">
					<i class="icon-download icon-rotate-180"></i> {l s='Export' mod='bulkcombinationsgenerator'}
				</a>
				<input type="hidden" name="serialized_data" value="">
				<input type="hidden" name="exportSettings" value="1">
			</form>
			<form action="" method="POST" class="inline-block">
				<a href="#" class="no-decoration importSettings uppercase"
				data-toggle="tooltip" title="{l s='Import settings' mod='bulkcombinationsgenerator'}">
					<i class="icon-download"></i> {l s='Import' mod='bulkcombinationsgenerator'}
				</a>
				<input type="file" name="importSettings" class="hidden">
			</form>
		</div>
	</div>
	<div class="panel attributes cleafix">
		<div id="manual-assign" class="tab-content active clearfix">
			<form method="post" action="" class="form-horizontal attributes-form clearfix">
				<button type="button" class="btn btn-primary showAttributes" data-toggle="modal" data-target="#dynamic-popup">
					<i class="icon-plus"></i> {l s='Add attributes' mod='bulkcombinationsgenerator'}
				</button>
				<span class="selected-atts-summary">
					{l s='[1]0[/1] attributes selected | [2]0[/2] possible combinations' mod='bulkcombinationsgenerator'
					tags=['<span class="b total-atts">', '<span class="b total-combs">']}
				</span>
				<div class="selected-atts clear-both">
					<table class="table">
						<thead>
						<tr>
							<th class="att-id">{l s='ID' mod='bulkcombinationsgenerator'}</th>
							<th class="att-name">{l s='Name' mod='bulkcombinationsgenerator'}</th>
							{foreach $combination_fields as $key => $field}
								<th class="text-center">
									{$field.name|escape:'html':'UTF-8'}
									{if $key == 'price'}
										<div class="inline-block"><select name="a[options][tax_incl]" class="minimalistic">
											<option value="0">({l s='tax excl.' mod='bulkcombinationsgenerator'})</option>
											<option value="1">({l s='tax incl.' mod='bulkcombinationsgenerator'})</option>
										</select></div>
									{/if}
								</th>
							{/foreach}
							<th class="last text-right"><a href="#" class="icon-trash action-icon removeAllRows hidden"></a></th>
						</tr>
						</thead>
						<tbody class="dynamic-att-rows">{* filled dynamically *}</tbody>
					</table>
				</div>
				<div class="selected-att-options">
					{foreach $attribute_options_fields as $name => $field}
						<div class="att-option{if !empty($field.class)} {$field.class|escape:'html':'UTF-8'}{/if}">
							<label>
								{$field.label|escape:'html':'UTF-8'}
								{if $name == 'reference'}{include file="./reference-variables.tpl"}{/if}
							</label>
							{if (!empty($field.options))}
								<select name="a[options][{$name|escape:'html':'UTF-8'}]"{if !empty($class)} class="{$class|escape:'html':'UTF-8'}"{/if}>
									{foreach $field.options as $opt_value => $opt_text}
										<option value="{$opt_value|escape:'html':'UTF-8'}">{$opt_text|escape:'html':'UTF-8'}</option>
									{/foreach}
								</select>
							{else}
								<input type="text" name="a[options][{$name|escape:'html':'UTF-8'}]" value="{if !empty($field.value)}{$field.value|escape:'html':'UTF-8'}{/if}" class="form-control">
								{if isset($field.override)}
									<label class="override-label">
										<input type="checkbox" name="a[override_options][{$name|escape:'html':'UTF-8'}]" value="1" {if $field.override} checked{/if}>
										{l s='Override existing values' mod='bulkcombinationsgenerator'}
									</label>
								{/if}
							{/if}
						</div>
					{/foreach}
				</div>
			</form>
		</div>
		<div id="duplicate-combinations" class="tab-content clearfix">
			<form method="post" action="" class="form-horizontal attributes-form clearfix">
				{foreach $duplicate_fields as $name => $field}
					<div class="form-group">
						<label class="control-label col-lg-3">
							<span{if !empty($field.tooltip)} class="label-tooltip" data-toggle="tooltip" title="{$field.tooltip|escape:'html':'UTF-8'}"{/if}>
								{$field.label|escape:'html':'UTF-8'}
							</span>
						</label>
						<div class="col-lg-4"><input type="text" name="a[{$name|escape:'html':'UTF-8'}]" class="{$name|escape:'html':'UTF-8'}"></div>
						<div class="col-lg-5 additional-info">
							{if $name == 'new_reference'}
								{include file="./reference-variables.tpl" include_duplicate_variables = 1}
							{else}
								<i class="icon-refresh icon-spin hidden"></i><span class="dynamic-text"></span>
							{/if}
						</div>
					</div>
				{/foreach}
			</form>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-7">
			<div class="panel clearfix">
				<div class="panel-header"><i class="icon-sitemap"></i> {l s='Select products' mod='bulkcombinationsgenerator'}</div>
				<form method="post" action="" class="form-horizontal products-form clearfix">
				{foreach $product_filters as $name => $filter}
					{$name = 'filters['|cat:$name|cat:']'}
					<div class="form-group">
						<label class="filter-label control-label col-lg-2">{$filter.label|escape:'html':'UTF-8'}</label>
						<div class="filter-value col-lg-10">
							{if isset($filter.options)}
								{include file="./options.tpl" name=$name data=$filter}
							{else}
								<input type="text" name="{$name|escape:'html':'UTF-8'}" class="text-input numeric">
							{/if}
						</div>
						<a href="#" class="icon-eraser resetFilter" data-toggle="tooltip" title="{l s='Reset' mod='bulkcombinationsgenerator'}"></a>
					</div>
				{/foreach}
				</form>
			</div>
		</div>
		<div class="col-lg-5">
			<div class="panel process clearfix">
				<div class="panel-header"><i class="icon-refresh"></i> {l s='Process combinations' mod='bulkcombinationsgenerator'}</div>
				<form method="post" action="" class="form-horizontal process-form clearfix">
					<div class="form-group">
						<div class="combination-actions col-lg-10">
							<select name="action" class="processAction">
								<option value="updateCombinations">{l s='Update existing combinations' mod='bulkcombinationsgenerator'}</option>
								<option value="regenerateCombinations">{l s='Re-Generate all combinations' mod='bulkcombinationsgenerator'}</option>
								{*
								<option value="removeAttributes">{l s='Remove attributes from existing combinations' mod='bulkcombinationsgenerator'}</option>
								*}
								<option value="deleteCombinations">{l s='Delete existing combinations' mod='bulkcombinationsgenerator'}</option>
								<option value="duplicateCombinations" class="hidden">{l s='Duplicate combinations' mod='bulkcombinationsgenerator'}</option>
							</select>
						</div>
						<div class="col-lg-2 no-left-gutter">
							<button type="button" class="btn btn-success full-width runAction">
								<span data-command="start" class="active">{l s='Start' mod='bulkcombinationsgenerator'}</span>
								{*
								<span data-command="pause">{l s='Pause' mod='bulkcombinationsgenerator'}</span>
								<span data-command="resume" data-time="0">{l s='Resume' mod='bulkcombinationsgenerator'}</span>
								*}
							</button>
						</div>
					</div>
				</form>
				<div class="dynamic-log">
					<i class="icon-refresh icon-spin loading-indicator"></i>
					<div class="dynamic-log-content"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="info-footer text-center clear-both">
		<span class="info-item">v {$version|escape:'html':'UTF-8'}</span>
		{foreach $info_links as $name => $link}
			<a class="info-item no-decoration" href="{$link.url|escape:'html':'UTF-8'}" target="_blank" >
				<i class="icon-{$link.icon|escape:'html':'UTF-8'}"></i>
				{$link.title|escape:'html':'UTF-8'}
			</a>
		{/foreach}
	</div>
</div>

<div class="modal fade" id="dynamic-popup" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            	<h3 class="modal-title"></h3>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="dynamic-content clearfix"></div>
        </div>
    </div>
</div>
{* since 2.1.1 *}
