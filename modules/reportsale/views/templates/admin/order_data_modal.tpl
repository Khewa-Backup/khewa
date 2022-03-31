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
{if $orders|@count >=1}
	<div class="reportsale_searchbox">
		<input type="text" id="reportsale_searchmodal" value=""/>
		<button class="btn btn-default btn-primary reportsale_btn" onclick="reportsale_searchCustomerData()">{l s='Find' mod='reportsale'}</button>
		<button class="btn reportsale_btn reportsale_reset" onclick="reportsale_resetCustomerData()">{l s='Reset' mod='reportsale'}</button>
		<button class="btn reportsale_btn reportsale_reset" onclick="reportsale_hideModal()">{l s='Close' mod='reportsale'}</button>
	</div>
	<ol id="reportsale_searchcontent">
	{foreach from=$orders item=c}
		<li>
			<a href="{$c_controller|escape:'htmlall':'UTF-8'}&id_order={$c.id|escape:'htmlall':'UTF-8'}" target="_blank">
			<span class="reportsale_name">#{$c.id|escape:'htmlall':'UTF-8'}, {l s='Reference' mod='reportsale'}: {$c.reference|escape:'htmlall':'UTF-8'}</span>
			</a>
		</li>
	{/foreach}
	<ol>
{/if}