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
{if $customer|@count<=3}
	{foreach from=$customer item=c}
		<a href="{$c_controller|escape:'htmlall':'UTF-8'}&id_customer={$c.id|escape:'htmlall':'UTF-8'}" target="_blank">
		{$c.first_name|escape:'htmlall':'UTF-8'} {$c.last_name|escape:'htmlall':'UTF-8'}
		({$c.total_order|escape:'htmlall':'UTF-8'})
		</a>{if !$c@last}, {/if}
	{/foreach}
{else}
	{assign var=rel value="product_`$row.products_id`_`$row.id_combinations`"}
	<a class="btn btn-default" href="javascript:void(0)" rel="#{$rel|escape:'htmlall':'UTF-8'}" data-id-report="{$row.id_report|escape:'htmlall':'UTF-8'}" data-ajax-token="{$ajax_token|escape:'htmlall':'UTF-8'}" onclick="reportsale_viewCustomerData(this)">
		{l s='View' mod='reportsale'}
	</a>
{/if}