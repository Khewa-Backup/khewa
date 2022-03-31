{*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends file="helpers/list/list_header.tpl"}

{block name=override_header}
<script type="text/javascript">
	{if isset($last_id_inventory_product) && $last_id_inventory_product > 0}
	$(document).ready(function() {
		var lastTr = $('.{$last_id_inventory_product|intval}');
		if (lastTr.length > 0) {
			// Change background line of processed product and scroll to it
			lastTr.attr('class', 'success');
			if (typeof WK_INVENTORY !== 'undefined') {
				WK_INVENTORY.scrollToTarget($('#wkinventory_panel_form_scan'), 0);
			}
		}
	});
	{/if}
	var noticeMsg = '{l s='Quantities Update Informations' js=1 mod='wkinventory'}';
	var availableQtyTxt = '{l s='Available quantity' js=1 mod='wkinventory'}';
	var soldQtyTxt = {if version_compare(_PS_VERSION_, '1.7.2', '>=')}'{l s='Reserved quantity' js=1 mod='wkinventory'}'{else}'{l s='Quantity sold' js=1 mod='wkinventory'}'{/if};
	var adjustmentQtyTxt = '{l s='Adjustment quantity' js=1 mod='wkinventory'}';
	var realQtyTxt = '{l s='Real quantity' js=1 mod='wkinventory'}';
</script>
{if isset($inventory_for) && count($inventory_for)}
  {foreach from=$inventory_for key=for item=items}
    <span class="badge badge-danger">\</span>&nbsp;<span class="badge badge-success"><strong>{$for|escape:'html':'UTF-8'} = </strong></span>
    {foreach from=$items item=data}
    <span class="badge">{$data|escape:'html':'UTF-8'}</span>
    {/foreach}
  {/foreach}
{/if}
{/block}
