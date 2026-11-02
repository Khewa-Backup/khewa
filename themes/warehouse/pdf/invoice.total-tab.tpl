{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}
{* Khewa PS 9.2 (CORE_CHANGES.md #18): an after-tax gift card bigger than the products total without tax
   leaves less tax in the paid totals than the order really has. Then show the tax from the tax lines
   (products + shipping + wrapping), like the 1.7 site did (classes/pdf/HTMLTemplateInvoice.php, commit edd95ec6b).
   Ecotax is off on this shop, so 9.2 puts exactly that gap in ecotax_taxes; on every other invoice it is 0. *}
{if $footer.ecotax_taxes < -0.015}{assign var="khewaTotalTaxes" value=$footer.product_taxes + $footer.shipping_taxes + $footer.wrapping_taxes}{else}{assign var="khewaTotalTaxes" value=$footer.total_taxes}{/if}
<table id="total-tab" width="100%">

	{if $footer.product_discounts_tax_excl > 0}

		{* Total Products *}
		<tr>
			<td class="grey" width="50%">
				{l s='Total Products' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white" width="50%">
				{displayPrice currency=$order->id_currency price=$footer.products_before_discounts_tax_excl}
			</td>
		</tr>

		{* Shipping *}
		{if !$order->isVirtual()}
			<tr>
				<td class="grey" width="50%">
					{l s='Shipping Costs' d='Shop.Pdf' pdf='true'}
				</td>
				<td class="white" width="50%">
					{if $footer.shipping_tax_excl > 0}
						{displayPrice currency=$order->id_currency price=$footer.shipping_tax_excl}
					{else}
						{l s='Free Shipping' d='Shop.Pdf' pdf='true'}
					{/if}
				</td>
			</tr>
		{/if}

		{* Corrected: Discount shown as tax-excluded *}
		<tr>
			<td class="grey" width="50%">
				{l s='Total Discounts' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white" width="50%">
				- {displayPrice currency=$order->id_currency price=$footer.product_discounts_tax_excl}
			</td>
		</tr>

		{* Total (Tax Incl.) *}
		<tr class="bold">
			<td class="grey">
				{l s='Total (Tax Excl.)' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">
				{displayPrice currency=$order->id_currency price=$footer.products_after_discounts_tax_excl}
			</td>
		</tr>

		{* Tax shown after discount - use footer.total_taxes which correctly handles fixed amount discounts *}
		<tr class="bold">
			<td class="grey">
				{l s='Total Tax' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">
				{displayPrice currency=$order->id_currency price=$khewaTotalTaxes}
			</td>
		</tr>

		{* Final Total Paid *}
		<tr class="bold big">
			<td class="grey">
				{l s='Total' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">
				{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}
			</td>
		</tr>

	{else}

		{* Default block (no discounts) *}
		<tr>
			<td class="grey" width="50%">
				{l s='Total Products' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white" width="50%">
				{displayPrice currency=$order->id_currency price=$footer.products_before_discounts_tax_excl}
			</td>
		</tr>

		{if !$order->isVirtual()}
			<tr>
				<td class="grey" width="50%">
					{l s='Shipping Costs' d='Shop.Pdf' pdf='true'}
				</td>
				<td class="white" width="50%">
					{if $footer.shipping_tax_excl > 0}
						{displayPrice currency=$order->id_currency price=$footer.shipping_tax_excl}
					{else}
						{l s='Free Shipping' d='Shop.Pdf' pdf='true'}
					{/if}
				</td>
			</tr>
		{/if}

		{if $footer.wrapping_tax_excl > 0}
			<tr>
				<td class="grey">
					{l s='Wrapping Costs' d='Shop.Pdf' pdf='true'}
				</td>
				<td class="white">
					{displayPrice currency=$order->id_currency price=$footer.wrapping_tax_excl}
				</td>
			</tr>
		{/if}

		<tr class="bold">
			<td class="grey">
				{l s='Total Tax' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">
				{displayPrice currency=$order->id_currency price=$khewaTotalTaxes}
			</td>
		</tr>

		<tr class="bold big">
			<td class="grey">
				{l s='Total' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">
				{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}
			</td>
		</tr>

	{/if}

</table>