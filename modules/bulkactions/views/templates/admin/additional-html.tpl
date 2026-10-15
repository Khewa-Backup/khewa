{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{function renderGroupsSelector class=''}
	<div class="inline-item ba-group {$class|escape:'html':'UTF-8'}">
		<select class="groups-list form-control" name="id_group">
			<option value="0" class="first">{l s='Select group' mod='bulkactions'}</option>
			{foreach $groups as $group}
				<option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
{/function}
{function renderCategoryOptions values = [] nesting_prefix = ''}
	{foreach $values as $id_category => $value}
		<option value="{$id_category|intval}">
			{$ba->formatCategoryID($id_category)|escape:'html':'UTF-8'}
			{$nesting_prefix|escape:'html':'UTF-8'}
			{$value.name|escape:'html':'UTF-8'}
		</option>
		{if !empty($structured_categories[$id_category])}
			{renderCategoryOptions values = $structured_categories[$id_category] nesting_prefix = $nesting_prefix|cat:'-'}
		{/if}
	{/foreach}
{/function}
{function renderCategorySelector class='' selector_class=''}
	<div class="inline-item ba-category {$class|escape:'html':'UTF-8'}">
		<select class="cat-list form-control {$selector_class|escape:'html':'UTF-8'}" name="id_cat">
			<option value="0" class="first">{l s='Select category' mod='bulkactions'}</option>
			{if !empty($structured_categories[$id_root])}
				{renderCategoryOptions values = $structured_categories[$id_root]}
			{/if}
		</select>
	</div>
{/function}
{function renderPriceInput class=''}
	<div class="inline-item ba-price {$class|escape:'html':'UTF-8'}">
		<input type="text" name="price" class="form-control has-suffix">
		<span class="input-suffix">{$currency_sign|escape:'html':'UTF-8'}</span>
	</div>
{/function}
{function renderReplacementInputs class=''}
	<div class="inline-item ba-replace {$class|escape:'html':'UTF-8'}">
		<select class="ba-lang-selector form-control" name="replace[id_lang]">
			{foreach $ba_languages as $id_lang => $iso_code}
				<option value="{$id_lang|intval}"{if $id_lang == $ba_id_lang} selected{/if}>{$iso_code|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="inline-item ba-replace {$class|escape:'html':'UTF-8'}">
		<input type="text" name="replace[from]" class="form-control">
	</div>
	<div class="inline-item ba-replace has-label {$class|escape:'html':'UTF-8'}">
		<span class="inline-label">→</span>
		<input type="text" name="replace[to]" class="form-control">
	</div>
{/function}
{function renderFeatureInputs class=''}
	<div class="inline-item ba-feature {$class|escape:'html':'UTF-8'}">
		<select class="f-list form-control" name="id_feature">
			<option value="0" class="first">{l s='Select feature' mod='bulkactions'}</option>
			{foreach $f_groups as $group_id => $group_name}
				<option value="{$group_id|intval}">{$group_name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="inline-item ba-feature {$class|escape:'html':'UTF-8'}">
		<select class="fv-list form-control" name="id_feature_value">
			<option value="0" class="first">{l s='Select value' mod='bulkactions'}</option>
			{* filled dynamically *}
		</select>
	</div>
{/function}
{function renderConfirmationBtn container_class = ''}
	<div class="{$container_class|escape:'html':'UTF-8'}">
		<a href="#" class="btn btn-default btn-outline-primary runAction">
			<span class="text">{l s='OK' mod='bulkactions'}</span>
			<i class="loading-indicator"></i>
		</a>
	</div>
{/function}
{function renderAssignableImages}
	<div class="assignImages{if $is_16} bulk-action{/if} hidden">
		{foreach $assignable_images as $img}
			<label class="bulk-img-label img-{$img.id_image|intval}">
				{* not using value, because it is force-redefined in bundle.js after submitting form *}
				<input type="checkbox" id="bulk-img-{$img.id_image|intval}" class="bulk-img-checkbox" data-id="{$img.id_image|intval}">
				<img src="{$img.src|escape:'html':'UTF-8'}" class="img-thumbnail">
			</label>
		{/foreach}
		<div class="inline-block">
			<a href="#" class="btn btn-default btn-outline-primary runAction">
				<span class="text">{if !$is_16}{l s='Assign images' mod='bulkactions'}{else}{l s='Assign' mod='bulkactions'}{/if}</span>
				<i class="loading-indicator"></i>
			</a>
			{if !$is_16}
				<input type="hidden" name="action_type" value="assignImages" class="bulk-action-type">
			{/if}
		</div>
	</div>
{/function}
{function renderBulkSelectionTools}
	<div class="bulk-selection-tools{if $is_16} ps-16{/if}">
		{if $is_16}
			<label class="label-uppercase"><input type="checkbox" id="toggle-all-combinations"> {l s='Check / Uncheck all' mod='bulkactions'}</label>
			<a href="#" class="label-uppercase invertSelection"><i class="icon-random"></i>  {l s='Invert selection' mod='bulkactions'}</a>
		{/if}
		<div class="inline-block">
			{include file="./multiselect.tpl" options = $attribute_options}
		</div>
	</div>
{/function}

{if $ba_type == 'combinations'}
	{if $is_16}
		<div class="panel">
			<div class="handy-bulk-actions for-combinations">
				<label class="control-label inline-block">{l s='Bulk actions' mod='bulkactions'}:</label>
				<div class="inline-block">
					<select class="bulk-action-type form-control">
						<option value="0">-</option>
						<option value="assignImages">{l s='Assign images' mod='bulkactions'}</option>
						<option value="setPriceImpact">{l s='Set price impact' mod='bulkactions'}</option>
						<option value="setUnitPriceImpact">{l s='Set unit price impact' mod='bulkactions'}</option>
						<option value="setWeightImpact">{l s='Set weight impact' mod='bulkactions'}</option>
					</select>
				</div>
				<div class="inline-block">
					<div class="setPriceImpact side-margins bulk-action hidden">
						<input type="text" class="bulk-price-impact has-suffix">
						<span class="input-suffix">{$currency_sign|escape:'html':'UTF-8'}</span>
					</div>
					<div class="setUnitPriceImpact side-margins bulk-action hidden">
						<input type="text" class="bulk-unit-price-impact has-suffix">
						<span class="input-suffix">{$currency_sign|escape:'html':'UTF-8'}</span>
					</div>
					<div class="setWeightImpact side-margins bulk-action hidden">
						<input type="text" class="bulk-weight-impact has-suffix">
						<span class="input-suffix">{$weight_sign|escape:'html':'UTF-8'}</span>
					</div>
				</div>
				<div class="inline-block">
					{renderConfirmationBtn container_class='bulk-action setPriceImpact setUnitPriceImpact setWeightImpact hidden'}
				</div>
				{renderAssignableImages}
				{renderBulkSelectionTools}
			</div>
		</div>
	{else}
		{renderAssignableImages}
		{renderBulkSelectionTools}
	{/if}
{elseif $ba_type == 'product' || $ba_type == 'category' || $ba_type == 'customer'}
	<div class="handy-bulk-actions ib {$ba_type|escape:'html':'UTF-8'}{if $is_16} ps-16{/if}">
		<div class="inline-item">
			<select name="action_type" class="bulk-action-type form-control">
				{if $ba_type == 'product'}
					<option value="addToCategory" data-show="category">{l s='Add to category' mod='bulkactions'}</option>
					<option value="removeFromCategory" data-show="category">{l s='Remove from category' mod='bulkactions'}</option>
					<option value="setDefaultCategory" data-show="category">{l s='Set default category' mod='bulkactions'}</option>
					<option value="setPrice" data-show="price">{l s='Set base price' mod='bulkactions'}</option>
					<option value="addFeatureValue" data-show="feature">{l s='Add feature value' mod='bulkactions'}</option>
					<option value="removeFeatureValue" data-show="feature">{l s='Remove feature value' mod='bulkactions'}</option>
					<option value="replaceText" data-show="replace" data-confirm="{l s='Are you sure?' mod='bulkactions'}">{l s='Replace text' mod='bulkactions'}</option>
				{elseif $ba_type == 'category'}
					<option value="moveToParent" data-show="category">{l s='Set parent' mod='bulkactions'}</option>
					<option value="copyToParent" data-show="category">{l s='Copy to parent' mod='bulkactions'}</option>
					<option value="addGroupAccess" data-show="group">{l s='Add access for group' mod='bulkactions'}</option>
					<option value="removeGroupAccess" data-show="group">{l s='Restrict access for group' mod='bulkactions'}</option>
				{elseif $ba_type == 'customer'}
					<option value="addToGroup">{l s='Add to group' mod='bulkactions'}</option>
					<option value="removeFromGroup">{l s='Remove from group' mod='bulkactions'}</option>
					<option value="setDefaultGroup">{l s='Set default group' mod='bulkactions'}</option>
				{/if}
			</select>
		</div>
		{if $ba_type != 'customer'}
			{renderCategorySelector}
			{if $ba_type == 'product'}
				{renderPriceInput class='hidden'}
				{renderFeatureInputs class='hidden'}
				{renderReplacementInputs class='hidden'}
			{else if $ba_type == 'category'}
				{renderGroupsSelector class='hidden'}
			{/if}
		{else}
			{renderGroupsSelector}
		{/if}
		{foreach $hidden_data as $name => $value}
			<input type="hidden" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}">
		{/foreach}
		{renderConfirmationBtn container_class='inline-item last'}
	</div>
{/if}
{* since 1.3.0 *}
