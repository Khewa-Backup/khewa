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
{if count($summary) > 0}
<div class="summary_parent">
	<div class="summary_wrapper">
		{assign var="i" value="0"}
		{foreach from=$fields_list item=field key=k}
		{assign var="i" value=$i + 1}
		<div class="summary_col_{$i|escape:'htmlall':'UTF-8'} summary_col">
			{foreach from=$summary.$k item=line}
				<span class="summary_line">{$line|escape:'htmlall':'UTF-8'}</span>
			{/foreach}
		</div>
		{/foreach}
	</div>
</div>
{literal}
<script>
/**** align Summary block ***********/
jQuery(document).ready(function(){
	reportsale_alignSummaryTable();
	$(".panel.col-lg-12 .table-responsive-row, .panel.col-lg-12 .table-responsive").scroll(function(){
		reportsale_alignSummaryTable();
	});
});
</script>
{/literal}
{/if}