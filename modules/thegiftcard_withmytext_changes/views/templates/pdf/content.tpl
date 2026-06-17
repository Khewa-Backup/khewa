{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{$style nofilter}{* HTML, cannot escape *}

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
	<!-- Content -->
	<tr>
		<td width="100%" align="center">
			<h3>{if isset($data.beneficiary)}{l s='Hi %s' sprintf=[$data.beneficiary|escape:'html':'UTF-8'] mod='thegiftcard'}{else}{l s='Hi %s' sprintf=[$data.customer|escape:'html':'UTF-8'] mod='thegiftcard'}{/if}</h3>
			<p>
				<span style="font-size:11pt;">{if isset($data.beneficiary)}{l s='You received a gift card worth %s from %s' sprintf=[$data.giftcard_amount|escape:'html':'UTF-8', $data.customer|escape:'html':'UTF-8'] mod='thegiftcard'}{else}{l s='Here is your gift card worth %s' sprintf=[$data.giftcard_amount|escape:'html':'UTF-8'] mod='thegiftcard'}{/if}</span>
			</p>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	<tr>
		<td width="25%">&nbsp;</td>
		<td width="50%" align="center">
			<img
				src="{$data.image_url|escape:'html':'UTF-8'}"
				alt="{l s='gift_card' mod='thegiftcard'}"
				width="{$data.image_width|intval}px"
				height="{$data.image_height|intval}px"
			/>
		</td>
		<td width="25%">&nbsp;</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	{if isset($data.giftcard_message)}
		<tr>
			<td width="100%" align="center">
				<p>
					<span style="font-size:11pt;">{$data.giftcard_message|escape:'html':'UTF-8'}</span>
				</p>
			</td>
		</tr>

		<tr>
			<td width="100%" height="30pt">&nbsp;</td>
		</tr>
	{/if}

	<tr>
		<td width="100%" class="panel">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
					<tr>
						<td width="10%">&nbsp;</td>
						<td width="80%">
							<h4>{l s='Information about the gift card' mod='thegiftcard'}</h4>
							<p>
								<span>{l s='Amount' mod='thegiftcard'} : {$data.giftcard_amount|escape:'html':'UTF-8'}</span><br>
								<span>{l s='Code' mod='thegiftcard'} : {$data.giftcard_code|escape:'html':'UTF-8'}</span><br>
								<span>{l s='Expiry date' mod='thegiftcard'} : {$data.giftcard_expiration|escape:'html':'UTF-8'}</span>
							</p>
						</td>
						<td width="10%">&nbsp;</td>
					</tr>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>

	<tr>
		<td width="100%" class="panel">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
					<tr>
						<td width="10%">&nbsp;</td>
						<td width="80%">
							<h4>{l s='How to use the gift card' mod='thegiftcard'}</h4>
							<p>
								<span>{l s='Available %s from the purchase date, the gift card is usable throughout the store %s (%s). It can be used in several times, including sale seasons, and can be combined with another payment method. To use the gift card, simply copy/paste the code above during the payment process of your next order.' sprintf=[$data.giftcard_expiration_date|escape:'html':'UTF-8', $data.shop_name|escape:'html':'UTF-8', $data.shop_url|escape:'html':'UTF-8'] mod='thegiftcard'}
								</span>
							</p>
						</td>
						<td width="10%">&nbsp;</td>
					</tr>
					<tr>
						<td width="100%" height="10pt">&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>

	<tr>
		<td width="100%" height="30pt">&nbsp;</td>
	</tr>
</table>
