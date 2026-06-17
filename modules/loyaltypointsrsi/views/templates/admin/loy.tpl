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
<div class="row">
<div class="col">
<div class="card">
<div class="panel">
{if $points}
			<h3 class="panel-heading card-header">{l s='Loyalty points:' mod='loyaltypointsrsi'}{$points}</h3>
			{else}
      <h3 class="panel-heading card-header">{l s='This customer has no points' mod='loyaltypointsrsi'}</h3>
       {/if}

		<div class="panel-body card-body">
		<table cellspacing="0" cellpadding="0" class="table">
			<tr style="background-color:#F5E9CF; padding: 0.3em 0.1em;">
				<th>{l s='Order' mod='loyaltypointsrsi'}</th>
				<th>{l s='Date' mod='loyaltypointsrsi'}</th>
				<th>{l s='Total (without shipping)' mod='loyaltypointsrsi'}</th>
				<th>{l s='Points' mod='loyaltypointsrsi'}</th>
				<th>{l s='Points Status' mod='loyaltypointsrsi'}</th>
			</tr>

 {foreach key=key item=loyalty from=$details}
			<tr style="background-color: {if $key % 2 != 0}#FFF6CF {else} #FFFFFF{/if}">
				<td>{if $loyalty['id'] > 0} <a style="color: #268CCD; font-weight: bold; text-decoration: underline;" href="index.php?tab=AdminOrders&id_order={$loyalty['id']}&vieworder&token={$token}">{$loyalty['id']}</a> {else} --{/if}</td>
				<td>{Tools::displayDate($loyalty['date'])}</td>
				<td>{if $loyalty['id'] > 0} {$loyalty['total_without_shipping']} {else} --{/if}</td>
				<td>{$loyalty['points']}</td>
				<td>{$loyalty['state']}</td>
			</tr>
        {/foreach}

			<tr>
				<td>&nbsp;</td>
				<td colspan="2" class="bold" style="text-align: right;">{l s='Total points available' mod='loyaltypointsrsi'}</td>
				<td>{$points}</td>
				<td>{l s='Voucher value:' mod='loyaltypointsrsi'} {$voucherlo}
			</tr>
		</table>
		<form action="{$linkgen}" method="post" class="form-horizontal">
		<div class="form-group">
		<label class="control-label col-lg-6" style="text-align:left"> {l s='Add or remove points manually for customer' mod='loyaltypointsrsi'}: </label>
		<select name="typelp" class="form-control">
		<option value="2" >{l s='add points' mod='loyaltypointsrsi'}</option>
		</select>
		<input type="text" name="pointslp" id="pointslp" class="form-control"  placeholder="{l s='Enter the points to add to this customer' mod='loyaltypointsrsi'}" />
		<br/>
		<button class="btn btn-primary" type="submit" id="subm"><i class="icon-file"></i>  {l s='Submit' mod='loyaltypointsrsi'} </button>

		</fieldset>
		</form>
		</form>
		</div>
		</div>
		</div>
		</div>
		</div>
