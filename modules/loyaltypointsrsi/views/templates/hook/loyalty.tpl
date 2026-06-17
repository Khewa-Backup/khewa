{*
* 2007-2023 PrestaShop
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
*  @copyright  2007-2023 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file='page.tpl'}
{block name='page_content'}


{literal}
<script>
(function(){"use strict";var c=[],f={},a,e,d,b;if(!window.jQuery){a=function(g){c.push(g)};f.ready=function(g){a(g)};e=window.jQuery=window.$=function(g){if(typeof g=="function"){a(g)}return f};window.checkJQ=function(){if(!d()){b=setTimeout(checkJQ,100)}};b=setTimeout(checkJQ,100);d=function(){if(window.jQuery!==e){clearTimeout(b);var g=c.shift();while(g){jQuery(g);g=c.shift()}b=f=a=e=d=window.checkJQ=null;return true}return false}}})();
</script>
{/literal}
<h2>{l s='My loyalty points' mod='loyaltypointsrsi'}</h2>

{if $orders}
<div class="block-center" id="block-history">
	{if $orders && count($orders)}
	<table id="order-list" class="table table-striped table-bordered table-labeled hidden-sm-down">
		<thead>
			<tr>
				<th class="first_item">{l s='Order' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Date' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Points' mod='loyaltypointsrsi'}</th>
				<th class="last_item">{l s='Points Status' mod='loyaltypointsrsi'}</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="alternate_item">
				<td colspan="2" class="history_method bold" style="text-align:center;">{l s='Total points available:' mod='loyaltypointsrsi'}</td>
				<td class="history_method" style="text-align:left;">{$totalPoints|intval}</td>
				<td class="history_method">&nbsp;</td>
			</tr>
		</tfoot>
		<tbody>trtr
		{foreach from=$displayorders item='order'}
			<tr class="alternate_item">
				<td class="history_link bold">{l s='#' mod='loyaltypointsrsi'}{$order.id|string_format:"%06d"}</td>
				<td class="history_date">{dateFormat date=$order.date full=1}</td>
				<td class="history_method">{$order.points|intval}</td>
				<td class="history_method">{$order.state|escape:'html':'UTF-8'}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div id="block-order-detail" class="hidden">&nbsp;</div>
	{else}
		<p class="warning">{l s='You have not placed any orders.' mod='loyaltypointsrsi'}</p>
	{/if}
</div>
<div class="bottom-pagination-content clearfix">
	<div id="pagination" class="pagination clearfix">
		{if $orders|@count > $nbpagination}
			<form class="showall" action="{$pagination_link}" method="get">
				<div>
					<button type="submit" class="btn btn-default button exclusive-medium">
						<span>{l s='Show all' mod='loyaltypointsrsi'}</span>
					</button>
					<!--<input name="n" id="nb_item" class="hidden" value="{$orders|@count}" />
					<input name="process" class="hidden" value="summary" />-->
				</div>
			</form>
		{/if}
		{if $nbpagination < $orders|@count}
		<nav class="pagination"></nav>
			<ul class="page-list clearfix text-center">
			{if $pagen != 1}
				{assign var='p_previous' value=$pagen-1}
				<li id="pagination_previous" class="pagination_previous">
					<a href="{summarypaginationlink p=$p_previous n=$nbpagination}" title="{l s='Previous' mod='loyaltypointsrsi'}" rel="prev">
						<i class="icon-chevron-left"></i> <b>{l s='Previous' mod='loyaltypointsrsi'}</b>
					</a>
				</li>
			{else}
				<li id="pagination_previous" class="disabled pagination_previous">
					<span>
						<i class="icon-chevron-left"></i> <b>{l s='Previous' mod='loyaltypointsrsi'}</b>
					</span>
				</li>
			{/if}
			{if $pagen > 2}
				<li>
					<a href="{summarypaginationlink p='1' n=$nbpagination}" rel="nofollow"><span>1</span></a>
				</li>
				{if $pagen > 3}
					<li class="truncate">
						<span>
							<span>...</span>
						</span>
					</li>
				{/if}
			{/if}
			{section name=pagination start=$pagen-1 loop=$pagen+2 step=1}
				{if $pagen == $smarty.section.pagination.index}
					<li class="active current">
						<span>
							<span>{$pagen|escape:'html':'UTF-8'}</span>
						</span>
					</li>
				{elseif $smarty.section.pagination.index > 0 && $orders|@count+$nbpagination > ($smarty.section.pagination.index)*($nbpagination)}
					<li>
						<a href="{summarypaginationlink p=$smarty.section.pagination.index n=$nbpagination}">
							<span>{$smarty.section.pagination.index|escape:'html':'UTF-8'}</span>
						</a>
					</li>
				{/if}
			{/section}
			{if $max_page-$pagen > 1}
				{if $max_page-$pagen > 2}
					<li class="truncate">
						<span>
							<span>...</span>
						</span>
					</li>
				{/if}
				<li>
					<a href="{summarypaginationlink p=$max_page n=$nbpagination}">
						<span>{$max_page}</span>
					</a>
				</li>
			{/if}
			{if $orders|@count > $pagen * $nbpagination}
				{assign var='p_next' value=$pagen+1}
				<li id="pagination_next" class="pagination_next">
					<a href="{summarypaginationlink p=$p_next n=$nbpagination}" rel="next">
						<b>{l s='Next' mod='loyaltypointsrsi'}</b> <i class="icon-chevron-right"></i>
					</a>
				</li>
			{else}
				<li id="pagination_next" class="pagination_next disabled">
					<span>
						<b>{l s='Next' mod='loyaltypointsrsi'}</b> <i class="icon-chevron-right"></i>
					</span>
				</li>
			{/if}
			</ul>
			</nav>
		{/if}
	</div>
	<div class="product-count">
		{if ($nbpagination*$pagen) < $orders|@count }
			{assign var='itemShowing' value=$nbpagination*$pagen}
		{else}
			{assign var='itemShowing' value=($nbpagination*$pagen-$orders|@count-$nbpagination*$pagen)*-1}
		{/if}
		{if $pagen==1}
			{assign var='itemShowingStart' value=1}
		{else}
			{assign var='itemShowingStart' value=$nbpagination*$pagen-$nbpagination+1}
		{/if}
		{if $orders|@count > 1}
			{l s='Showing %1$d - %2$d of %3$d items' sprintf=[$itemShowingStart, $itemShowing, $orders|@count] mod='loyaltypointsrsi'}
		{else}
			{l s='Showing %1$d - %2$d of 1 item' sprintf=[$itemShowingStart, $itemShowing] mod='loyaltypointsrsi'}
	{/if}
	</div>
</div>

<br />{l s='Vouchers generated here are usable in the following categories : ' mod='loyaltypointsrsi'}
{if $categories}{$categories}{else}{l s='All' mod='loyaltypointsrsi'}{/if}

{if $transformation_allowed}
<p style="text-align:center; margin-top:20px">
	<a href="{url entity='module' name='loyaltypointsrsi' controller='default' params=['process' => 'transformpoints']}" onclick="return confirm('{l s='Are you sure you want to transform your points into vouchers?' mod='loyaltypointsrsi' js=1}');">{l s='Transform my points into a voucher of' mod='loyaltypointsrsi'} <span class="price">{Tools::convertPrice($voucher)}</span>.</a>
</p>
{/if}

<br />
<br />
<br />

<h2>{l s='My vouchers from loyalty points' mod='loyaltypointsrsi'}</h2>

{if $nbDiscounts}
<div class="block-center" id="block-history">
	<table id="order-list" class="table table-striped table-bordered table-labeled hidden-sm-down">
		<thead>
			<tr>
				<th class="first_item">{l s='Created' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Value' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Code' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Valid from' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Valid until' mod='loyaltypointsrsi'}</th>
				<th class="item">{l s='Status' mod='loyaltypointsrsi'}</th>
				<th class="last_item">{l s='Details' mod='loyaltypointsrsi'}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$discounts item=discount name=myLoop}
			<tr class="alternate_item">
				<td class="history_date">{dateFormat date=$discount->date_add}</td>
				<td class="history_price"><span class="price">{if $discount->reduction_percent > 0}
						{$discount->reduction_percent}%
					{elseif $discount->reduction_amount}
						{Tools::displayPrice($discount->reduction_amount)}
					{else}
						{l s='Free shipping' mod='loyaltypointsrsi'}
					{/if}</span></td>
				<td class="history_method bold">{$discount->code}</td>
				<td class="history_date">{dateFormat date=$discount->date_from}</td>
				<td class="history_date">{dateFormat date=$discount->date_to}</td>
				<td class="history_method bold">{if $discount->quantity > 0}{l s='Ready to use' mod='loyaltypointsrsi'}{else}{l s='Already used' mod='loyaltypointsrsi'}{/if}</td>
				<td class="history_method">

					{l s='Generated by these following orders' mod='loyaltypointsrsi'}|{foreach from=$discount->orders item=myorder name=myLoop}
					{$myorder.id_order|string_format:{l s='Order #%d' mod='loyaltypointsrsi'}}
					({Tools::displayPrice($myorder.total_paid,$myorder.id_currency)}) :
					{if $myorder.points > 0}{$myorder.points|string_format:{l s='%d points.' mod='loyaltypointsrsi'}}{else}{l s='Cancelled' mod='loyaltypointsrsi'}{/if}
					{if !$smarty.foreach.myLoop.last}|{/if}{/foreach}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div id="block-order-detail" class="hidden">&nbsp;</div>
</div>

{if $minimalLoyalty > 0}<p>{l s='The minimum order amount in order to use these vouchers is:' mod='loyaltypointsrsi'} {Tools::convertPrice($minimalLoyalty)}</p>{/if}

<script type="text/javascript">
{literal}
$(document).ready(function()
{
	$('a.tips').cluetip({
		showTitle: false,
		splitTitle: '|',
		arrows: false,
		fx: {
			open: 'fadeIn',
			openSpeed: 'fast'
		}
	});
});
{/literal}
</script>
{else}
<p class="warning">{l s='No vouchers yet.' mod='loyaltypointsrsi'}</p>
{/if}
{else}
<p class="warning">{l s='No reward points yet.' mod='loyaltypointsrsi'}</p>
{/if}

<ul class="footer_links">
	<li>
		<a href="{$link->getPageLink('my-account', true)}" title="{l s='Back to Your Account' mod='loyaltypointsrsi'}" rel="nofollow">{l s='Back to Your Account' mod='loyaltypointsrsi'}</a>
	</li>
	<li class="f_right">
		<a href=" {$urls.base_url}" title="{l s='Home' mod='loyaltypointsrsi'}">{l s='Home' mod='loyaltypointsrsi'}</a>
	</li>
</ul>
{/block}
