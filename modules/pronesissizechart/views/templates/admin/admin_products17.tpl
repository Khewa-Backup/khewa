{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="row">
	<div class="col-md-12">
		<div class="alert expandable-alert alert-info mt-3">
			{l s='According to the default category of this product, the size chart that will be displayed in the product page is/are: ' mod='pronesissizechart'} <b>{if $string_sctocat}{$string_sctocat|escape:'html':'UTF-8'}{else}{l s='No predefined size chart available' mod='pronesissizechart'}{/if}</b>.<br/><br/>
			{l s='You can associate a different size chart or create a specific size chart for this single product.' mod='pronesissizechart'}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<h2>{l s='OPTION 1: Select this if you don\'t want to show any size chart for this product' mod='pronesissizechart'}</h2>			
				<div class="checkbox">
					<input type="checkbox" name="hide_all" id="hide_all" value="1"{if $sizechartproduct[0]['hide_all']==1} CHECKED{/if}>
				</div>
	</div>
</div>

 <div class="row">
	<div class="col-md-12">
		<h2>{l s='OPTION 2: choose a different predefined size chart for this product' mod='pronesissizechart'}</h2>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<select name="sizechart_per_product" id="sizechart_per_product" class="select form-control">
			<option value="">{l s='Select...' mod='pronesissizechart'}</option>
			{foreach from=$sizechartgroups item=sizechartgroup}
			<option value="{$sizechartgroup.psc_group_id|intval}"{if $sizechartproduct[0]['psc_group_id']==$sizechartgroup.psc_group_id} SELECTED{/if}>{$sizechartgroup.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-md-12" style="padding-top: 5px;">
		<label>{l s='NOTE: if you create also a specific size chart, the image displayed will be the one in the predefined size chart.' mod='pronesissizechart'}</label>
	</div>			
</div>
<div class="row">
	<div class="col-md-12">
		<h2>{l s='OPTION 3: create a specific size chart for this product' mod='pronesissizechart'}</h2>
	</div>
</div>
<div class="row">
	<div class="col-md-8">
		<select name="copy_sizechart" id="copy_sizechart" class="select form-control">
			<option value="0">{l s='Select...' mod='pronesissizechart'}</option>
			{if count($sizechartproduct) && $sizechartproduct[0]['value'] != $set_empty}
			<option value="-2">{l s='*** Delete Specific size chart created for this product ***' mod='pronesissizechart'}</option>
			{/if}
			<option value="-1">{l s='Create from empty...' mod='pronesissizechart'}</option>
			{foreach from=$sizechartgroups item=sizechartgroup}
			<option value="{$sizechartgroup.psc_group_id|intval}">{$sizechartgroup.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select> 
	</div>
	<div class="col-md-8" style="padding-top: 5px;">
		<label>{l s='Create from empty or copy from a predefined size chart' mod='pronesissizechart'}</label>		
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<p id="the_table"></p> 
	</div>
</div> 

<script type="text/javascript">
	{literal}
	$(document).ready(function() {
		$.ajax({
			method: "POST",
			url: '{/literal}{$path|escape:'html':'UTF-8'}{literal}productpronesissizechart17.php',
			data: {langtext:'{/literal}{$langtext|escape:'html':'UTF-8'}{literal}', reloaddescription: '{/literal}{if $sizechartproduct[0]['description'] != $set_empty}{$sizechartproduct[0]['description']|escape:'html':'UTF-8'}{/if}{literal}', reloadtable: '{/literal}{if $sizechartproduct[0]['value'] != $set_empty}{$sizechartproduct[0]['value']|escape:'html':'UTF-8'}{/if}{literal}', ajax: 1, token: '{/literal}{$token}{literal}'}
		}).done(function(result) {
			if(result != 'false'){
				$('#the_table').html(result);
				$('#psc_buttons_container .psc_button').toggleClass('btn-danger btn-success');
				$('#psc_buttons_container .psc_button').find('i').toggleClass('icon-check icon-times');
			}
				else
					$('#the_table').html('');
		});
		$('#copy_sizechart').on('change', function() {
			$.ajax({
				method: "POST",
				url: '{/literal}{$path|escape:'html':'UTF-8'}{literal}productpronesissizechart17.php',
				data: {langtext:'{/literal}{$langtext|escape:'html':'UTF-8'}{literal}', option: $(this).val(), ajax: 1, token: '{/literal}{$token}{literal}'}
			}).done(function(result) {
				if(result != 'false'){
					$('#the_table').html(result);
					$('#psc_buttons_container .psc_button').toggleClass('btn-danger btn-success');
					$('#psc_buttons_container .psc_button').find('i').toggleClass('icon-check icon-times');
				}
					else
						$('#the_table').html('');
			});
		});
	});
	{/literal}
</script>
<script type="text/javascript">
	{include file="./pronesissizechartjs17.tpl"}
</script>