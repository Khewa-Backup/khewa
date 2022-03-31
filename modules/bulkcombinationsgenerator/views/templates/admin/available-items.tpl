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

{*** Styles/scripts included here for portability. This file is displayed in dynamic popup ***}
{literal}
<style type="text/css">
	.item-group {
		padding: 10px;
	}
	.item-group-title {
		background: #F1F1F1;
    	padding: 5px 25px;
    	margin: 0 -20px 10px;
	}
	.item-group-title label {
		font-weight: normal;
	    margin: 0 10px;
	    color: #999;
	}
	.item-group-title .pull-right .inline-block {
		margin-left: 5px;
	}
	.item-group input.quick-search,
	.item-group select.sort-by {
		height: 31px;
		line-height: 32px;
		border-radius: 5px;
		margin-top: 1px;
	}
	.item-group a.item {
	    padding: 8px;
	    border: 2px solid #DDD;
	    color: #777;
	    margin: 3px;
	    border-radius: 5px;
	    text-decoration: none;
	    display: inline-block;
	}
	.item-id {
		opacity: 0.5;
	}
	.item-group a.item:hover {
		color: #00AFF0;
	}
	.item-group a.item.selected {
		color: #00AFF0;
		border-color: #00AFF0;
	}
	.item-group a.item.blocked {
		background: #F5F5F5;
		color: #CCC;
		border-color: #DDD;
		cursor: not-allowed;
	}
	.blocked .item-id {
		opacity: 0.7;
	}
	.item-group .no-results {
		padding: 10px;
	    margin: 3px;
	}
	.item-items-list {
		max-height: 130px;
		overflow-y: scroll;
	}
	.available-items-footer {
		margin: 15px -10px -10px;
		background: #EEE;
		padding: 15px;
		border-radius: 0 0 5px 5px;
	}
	.available-items-footer .btn {
		padding-left: 30px;
		padding-right: 30px;
	}
	.bootstrap .available-items-footer .btn-blocked,
	.bootstrap .available-items-footer .btn-blocked:hover {
		background: #CCC;
		border: 1px solid #CCC;
		cursor: not-allowed;
		outline: none;
	}
</style>
<script type="text/javascript">
	var quickSearchTimer;
	function activateInstantSearch() {
		$('.quick-search').on('keyup', function(){
			var $parent = $(this).closest('.item-group'),
				$items = $parent.find('.item'),
				$noResulsWarning = $parent.find('.no-results'),
				value = $(this).val();
			clearTimeout(quickSearchTimer);
			quickSearchTimer = setTimeout(function() {
				// search for IDs starting from 1 characters, other strings - starting from 3
				if (!isNaN(value) || value.length > 2) {
					$items.each(function(){
						var hidden = $(this).text().toLowerCase().indexOf(value.toLowerCase()) === -1;
						$(this).toggleClass('hidden', hidden);
					});
				} else {
					$items.removeClass('hidden');
				}
				$noResulsWarning.toggleClass('hidden', !!$items.not('.hidden').length);
				updateTotals($parent);
			}, 300);
		});
	}

	function activateSortBy() {
		$('.sort-by').on('change',function(){
			var $list = $(this).closest('.item-group').find('.item-items-list'),
				$elements = $list.find('.item'),
				sortBy = $(this).val();
			if (sortBy == 'name') {
				$elements.sort(function(a, b){
					return $(a).find('.item-'+sortBy).text().toUpperCase().
					localeCompare($(b).find('.item-'+sortBy).text().toUpperCase());
				});
			} else {
				$elements.sort(function(a, b){
					return $(a).find('.item-'+sortBy).text() - $(b).find('.item-'+sortBy).text();
				});
			}
			$list.prepend($elements);
		})
	}
	function markSelectedItems() {
		$('.dynamic-att-rows').find('.att-row').each(function(){
			$('.item-group').find('.item[data-id="'+$(this).data('id')+'"]').addClass('selected');
		});
		updateTotals(false);
	}
	activateInstantSearch();
	activateSortBy();
	markSelectedItems();
	$('.item').on('click', function(){
		if (!$(this).hasClass('blocked')) {
			$(this).toggleClass('selected');
		}
		toggleAddBtn();
	});
	$('.check-all').on('change', function(){
		var selected = $(this).prop('checked');
		$(this).closest('.item-group').find('.item').not('.hidden, .blocked').toggleClass('selected', selected);
		toggleAddBtn();
	})
	function toggleAddBtn() {
		var selectedNum = $('.item.selected').not('.blocked').length;
		$('.addSelectedItems').toggleClass('btn-blocked', !selectedNum).find('.total-selected').html('('+selectedNum+')');
	}
	function updateTotals($groups){
		$groups = $groups ? $groups : $('.item-group');
		$groups.each(function(){
			var groupTotal = $(this).find('.item').not('.hidden, .blocked').length;
			$(this).find('.total').html('('+groupTotal+')');
		});
		toggleAddBtn();
	}
</script>
{/literal}

{foreach $available_items as $subtitle => $items}
	{$quick_search = $items|count > 10}
	<div class="item-group">
		<div class="item-group-title">
			<h4 class="inline-block">{$subtitle|escape:'html':'UTF-8'}</h4>
			<label class="inline-block">
				<input type="checkbox" class="check-all">
				{l s='select available' mod='bulkcombinationsgenerator'}
				<span class="total">({$items|count})</span>
			</label>
			{if $quick_search}
				<div class="pull-right">
					<div class="inline-block">
						<input type="text" class="quick-search" placeholder="{l s='Quick search' mod='bulkcombinationsgenerator'}">
					</div>
					<div class="inline-block">
						<select name="" class="sort-by hidden">
							<option value="position">{l s='Sort by position' mod='bulkcombinationsgenerator'}</option>
							<option value="name">{l s='Sort by name' mod='bulkcombinationsgenerator'}</option>
							<option value="id">{l s='Sort by ID' mod='bulkcombinationsgenerator'}</option>
						</select>
					</div>
				</div>
			{/if}
		</div>
		<div class="item-items-list">
			{foreach $items as $item}<a href="#" data-id="{$item.id|escape:'html':'UTF-8'}" class="item">
				<span class="item-id{if !$item.id} hidden{/if}">{$item.id|intval}</span>
				<span class="item-name">{$item.name|escape:'html':'UTF-8'}</span>
				<span class="item-position hidden">{$item.position|intval}</span>
			</a>{/foreach}
			{if $quick_search}
				<div class="alert-warning no-results hidden">{l s='No matches' mod='bulkcombinationsgenerator'}</div>
			{/if}
		</div>
	</div>
{/foreach}
<div class="available-items-footer text-center">
	<button class="btn btn-primary btn-blocked addSelectedItems">
		{l s='Add selected' mod='bulkcombinationsgenerator'} <span class="total-selected">(0)</span>
	</button>
</div>
{* since 2.0.1 *}
