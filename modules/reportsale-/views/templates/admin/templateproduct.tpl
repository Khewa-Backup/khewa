{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Buy-addons <contact@buy-addons.com>
*  @copyright  2007-2021 Buy-addons
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="ba_load_hidden">
	<img src="{$url_bareport|escape:'htmlall':'UTF-8'}modules/reportsale/views/img/default_01.gif" alt="">
</div>
<form action="{$url_report|escape:'htmlall':'UTF-8'}" method="post" id="PR_form_data">
	<input type="hidden" value="PR_" name="prefixreport"/>
	<div class="panel col-lg-12 ba-panel-report">
		<h3 id="report_click_opent_timeline">{l s='FILTER BY DATE' mod='reportsale'}<i id="timeline_icon" class="fa fa-plus-square report_ba_click"></i></h3>
		<div id="timeline_filtering" class="main_helper_content">
			<div class="row">
				<div class="form-group col-lg-3 main_text_option">
					<h4>{l s='Order Date' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-8">
					<div class="form-inline order_date">
						<div class="form-group">
							<label>{l s='From' mod='reportsale'}</label>
							<input type="Date" class="form-control from" name="PR_order_date_from" value="{$arr_configuration['order_date_from']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='To' mod='reportsale'}</label>
							<input type="Date" class="form-control to" name="PR_order_date_to" value="{$arr_configuration['order_date_to']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='Range' mod='reportsale'}</label>
							<select class="reportsale_range" rev="order_date">
								<option value="1" selected>{l s='Custom Date' mod='reportsale'}</option>
								<option value="2">{l s='Today' mod='reportsale'}</option>
								<option value="3">{l s='Yesterday' mod='reportsale'}</option>
								<option value="4">{l s='This week (Monday - Today)' mod='reportsale'}</option>
								<option value="5">{l s='Last week' mod='reportsale'}</option>
								<option value="6">{l s='7 days ago' mod='reportsale'}</option>
								<option value="7">{l s='14 days ago' mod='reportsale'}</option>
								<option value="8">{l s='This month' mod='reportsale'}</option>
								<option value="9">{l s='Last month' mod='reportsale'}</option>
								<option value="10">{l s='30 days ago' mod='reportsale'}</option>
							</select>
						</div>
						<input type="hidden" id="hidden_prefix" name="hidden_prefix" value="PR_">
						<span id="ba_url_hidden">{$url_bareport|escape:'htmlall':'UTF-8'}</span> <!--****************-->
						<span id="url_report_hidden">{$url_report|escape:'htmlall':'UTF-8'}</span> <!--****************-->
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-lg-3 main_text_option">
					<h4>{l s='Invoice Date' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-8">
					<div class="form-inline invoice_date">
						<div class="form-group">
							<label>{l s='From' mod='reportsale'}</label>
							<input type="date" class="form-control from" name="PR_invoice_date_from" value="{$arr_configuration['invoice_date_from']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='To' mod='reportsale'}</label>
							<input type="date" class="form-control to" name="PR_invoice_date_to" value="{$arr_configuration['invoice_date_to']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='Range' mod='reportsale'}</label>
							<select class="reportsale_range" rev="invoice_date">
								<option value="1" selected>{l s='Custom Date' mod='reportsale'}</option>
								<option value="2">{l s='Today' mod='reportsale'}</option>
								<option value="3">{l s='Yesterday' mod='reportsale'}</option>
								<option value="4">{l s='This week (Monday - Today)' mod='reportsale'}</option>
								<option value="5">{l s='Last week' mod='reportsale'}</option>
								<option value="6">{l s='7 days ago' mod='reportsale'}</option>
								<option value="7">{l s='14 days ago' mod='reportsale'}</option>
								<option value="8">{l s='This month' mod='reportsale'}</option>
								<option value="9">{l s='Last month' mod='reportsale'}</option>
								<option value="10">{l s='30 days ago' mod='reportsale'}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-lg-3 main_text_option">
					<h4>{l s='Delivery Date' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-8">
					<div class="form-inline delivery_date">
						<div class="form-group">
							<label>{l s='From' mod='reportsale'}</label>
							<input type="date" class="form-control from" name="PR_delivery_date_from" value="{$arr_configuration['delivery_date_from']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='To' mod='reportsale'}</label>
							<input type="date" class="form-control to" name="PR_delivery_date_to" value="{$arr_configuration['delivery_date_to']|escape:'htmlall':'UTF-8'}">
						</div>
						<div class="form-group">
							<label>{l s='Range' mod='reportsale'}</label>
							<select class="reportsale_range" rev="delivery_date">
								<option value="1" selected>{l s='Custom Date' mod='reportsale'}</option>
								<option value="2">{l s='Today' mod='reportsale'}</option>
								<option value="3">{l s='Yesterday' mod='reportsale'}</option>
								<option value="4">{l s='This week (Monday - Today)' mod='reportsale'}</option>
								<option value="5">{l s='Last week' mod='reportsale'}</option>
								<option value="6">{l s='7 days ago' mod='reportsale'}</option>
								<option value="7">{l s='14 days ago' mod='reportsale'}</option>
								<option value="8">{l s='This month' mod='reportsale'}</option>
								<option value="9">{l s='Last month' mod='reportsale'}</option>
								<option value="10">{l s='30 days ago' mod='reportsale'}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-lg-3 main_text_option">
					<h4>{l s='Order number' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-6">
					<div class="form-inline">
						<div class="form-group">
							<label>{l s='From' mod='reportsale'}</label>
							<input type="text" class="form-control" name="PR_order_number_from" value="{if $arr_configuration['order_number_from'] ne ''}{$arr_configuration['order_number_from']|escape:'htmlall':'UTF-8'}{/if}">
						</div>
						<div class="form-group">
							<label>{l s='To' mod='reportsale'}</label>
							<input type="text" class="form-control" name="PR_order_number_to" value="{if $arr_configuration['order_number_to'] ne ''}{$arr_configuration['order_number_to']|escape:'htmlall':'UTF-8'}{/if}">
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-lg-3 main_text_option">
					<h4>{l s='Invoice number' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-6">
					<div class="form-inline">
						<div class="form-group">
							<label>{l s='From' mod='reportsale'}</label>
							<input type="text" class="form-control" name="PR_invoice_number_from" id="exampleInputName2" placeholder="" value="{if $arr_configuration['invoice_number_from'] ne ''}{$arr_configuration['invoice_number_from']|escape:'htmlall':'UTF-8'}{/if}">
						</div>
						<div class="form-group">
							<label>{l s='To' mod='reportsale'}</label>
							<input type="text" class="form-control" name="PR_invoice_number_to" id="exampleInputEmail2" placeholder="" value="{if $arr_configuration['invoice_number_to'] ne ''}{$arr_configuration['invoice_number_to']|escape:'htmlall':'UTF-8'}{/if}">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel col-lg-12 ba-panel-report">
		<h3 id="report_click_opent_status">{l s='FILTER BY STATUS' mod='reportsale'}<i id="status_icon" class="fa fa-plus-square report_ba_click"></i></h3>
		<div id="status_filtering" class="main_helper_content">
			<div class="row">
				<div class="form-group col-lg-5 main_text_option">
					<strong><span>{l s='Select All' mod='reportsale'}: </span></strong>
				</div>
				<div class="col-lg-7">
					<div class="form-inline">
						<div class="form-group">
							<input type="checkbox" class="checkbox_check" id="checkall" placeholder="">
						</div>
					</div>
				</div>
			</div>
			{for $i=0 to $count_filtering-1}
			<div class="row">
				<div class="form-group col-lg-5 main_text_option">
					<span>{$status_filtering[$i]['name']|escape:'htmlall':'UTF-8'} </span>
				</div>
				<div class="col-lg-7">
					<div class="form-inline">
						<div class="form-group">
							{$name='PR_status_filtering_'|cat:$status_filtering[$i]['id_order_state']}
							{$value=$status_filtering[$i]['id_order_state']}
							{if $value eq $arr_status[$name]}
							<input type="checkbox" class="checkbox_check" name="{$name|escape:'htmlall':'UTF-8'}" checked="true" placeholder="" value="{$value|escape:'htmlall':'UTF-8'}">
							{else}
							<input type="checkbox" class="checkbox_check" name="{$name|escape:'htmlall':'UTF-8'}" value="{$value|escape:'htmlall':'UTF-8'}">
							{/if}
						</div>
					</div>
				</div>
			</div>
			{/for}
		</div>
	</div>
	<div class="panel col-lg-12 ba-panel-report">
		<h3 id="report_click_opent_product">{l s='Filtering By Product' mod='reportsale'}<i id="product_filtering_icon" class="fa fa-plus-square report_ba_click"></i></h3>
		<div id="product_filtering" class="main_helper_content">
			<div class="row">
				<div class="form-group col-lg-1"></div>
				<div class="form-group col-lg-3">
					<h4>{l s='Categories' mod='reportsale'}:</h4>
					<select size="15" name="PR_category[]" id="PR_category" multiple="multiple">
						{for $i=0 to $count_cate-1}
						<option {if in_array($list_category[$i]['id_category'], $categories_selected)} selected{/if} value="{$list_category[$i]['id_category']|escape:'htmlall':'UTF-8'}">{$list_category[$i]['name']|escape:'htmlall':'UTF-8'}</option>
						{/for}
					</select>
				</div>
				<div class="col-lg-3">
					<h4>{l s='Manufacturers' mod='reportsale'}:</h4>
					<select size="15" class="floatLeft" multiple="multiple" name="PR_manufacturers[]" id="PR_manufacturers">
						{for $i=0 to $count_manufacturers-1}
						<option {if in_array($list_manufacturers[$i]['id_manufacturer'], $manufacturers_selected)} selected{/if} value="{$list_manufacturers[$i]['id_manufacturer']|escape:'htmlall':'UTF-8'}">{$list_manufacturers[$i]['name']|escape:'htmlall':'UTF-8'}</option>
						{/for}
					</select>
				</div>
				<div class="col-lg-3">
					<h4>{l s='Suppliers' mod='reportsale'}:</h4>
					<select size="15" multiple="multiple" name="PR_supplier[]" id="otprSuppliers">
						{for $i=0 to $count_supplier-1}
						<option {if in_array($list_supplier[$i]['id_supplier'], $suppliers_selected)} selected{/if} value="{$list_supplier[$i]['id_supplier']|escape:'htmlall':'UTF-8'}">{$list_supplier[$i]['name']|escape:'htmlall':'UTF-8'}</option>
						{/for}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="panel col-lg-12 ba-panel-report">
		<h3 id="report_click_opent_country">{l s='Countries & Stores' mod='reportsale'}<i id="contry_icon" class="fa fa-plus-square report_ba_click"></i></h3>
		<div id="Country_filtering" class="main_helper_content">
			<div class="row form-group">
				<div class="form-group col-lg-4 main_text_option">
					<h4>{l s='Choose your countries' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-3">
					<select name="PR_country_filtering[]" multiple="multiple" class="form-control">
						{foreach from=$get_coutry key=k item=v}
							{if $v['active'] eq 1}
								{if $v['active'] eq 1}
								<option class="countries_selected" {for $i=0 to $count_country-1}{if $country_filtering[$i]==$k}selected="true" {/if}{/for} value="{$k|escape:'htmlall':'UTF-8'}">{$v['name']|escape:'htmlall':'UTF-8'}</option>
								{/if}
							{/if}
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row form-group">
				<div class="form-group col-lg-4 main_text_option">
					<h4>{l s='Stores' mod='reportsale'}: </h4>
				</div>
				<div class="col-lg-3">
					<select name="PR_stores[]" multiple="multiple" class="form-control">
						{foreach from=$stores item=store}
								<option value="{$store.id_shop|escape:'htmlall':'UTF-8'}"{if in_array($store.id_shop, $stores_selected)} selected{/if}>{$store.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-4" style="padding: 10px;margin-bottom: 7px;text-align: left; max-width: max-content;">
			<button type="button" id="PR_save_filter" name="PR_save_filter" class="btn btn-success">
				{l s='Generate Report' mod='reportsale'} <span class="report_counter"></span>
			</button>
		</div>
		<div class="col-lg-4" style="padding: 10px;margin-bottom: 7px;text-align: left; max-width: max-content;">
			<button type="submit" name="reset_filter" id="reset_filter" class="btn btn-warning">
				{l s='Reset' mod='reportsale'}
			</button>
		</div>
		<div class="col-lg-4">
		</div>
	</div>
</form>