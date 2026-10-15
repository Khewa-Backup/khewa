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
<div class="body_contents">
    <table style="width: 100%;" align="center">
    	<thead>
            <tr>                
                <td style="width: 7%; text-align: center;">#ID</td>
                <td style="width: 53%; text-align: left;">{l s='Product Info' mod='wkinventory'}</td>
                <td style="width: 10%; text-align: center;">{l s='Real Qty' mod='wkinventory'}</td>
                <td style="width: 15%; text-align: center;">{l s='Unit Price T.Excl' mod='wkinventory'}</td>
                <td style="width: 15%; text-align: center;">{l s='Total T.Excl' mod='wkinventory'}</td>                
            </tr>
    	</thead>
        {* Inventory products *}
        {assign var=count value=0}
        {foreach $inventory_products as $key => $inventory_product}
    	<tbody>
            <tr>                    
                <td style="text-align: center;">{$inventory_product['id_product']|intval}</td>
                <td style="text-align: left;">{$inventory_product['name']|escape:'htmlall':'UTF-8'}
                {if $inventory_product['combination']}<br /><span style="color:#999; font-style:italic">{$inventory_product['combination']|escape:'htmlall':'UTF-8'}</span>{/if}
                {if $inventory_product['reference']}<br /><strong>{l s='Reference:' mod='wkinventory'}:</strong> {$inventory_product['reference']|escape:'htmlall':'UTF-8'}{/if}
                {if $inventory_product['ean13']}<br /><strong>{l s='EAN13' mod='wkinventory'}:</strong> {$inventory_product['ean13']|escape:'htmlall':'UTF-8'}{/if}
                </td>
                <td style="text-align: center;">{$inventory_product['real_quantity']|intval}</td>
                <td style="text-align: center;">{displayPrice price=$inventory_product['unit_price']}</td>
                {assign var=total_price value=$inventory_product['real_quantity']*$inventory_product['unit_price']}
                <td style="text-align: center;">{displayPrice price=$total_price}</td>
            </tr>
    	</tbody>
        {assign var=count value=$count+1}
        {if $count == $products_per_page}
			{assign var=count value=0}
        {/if}
        {if $count == 0 && ($key + 1 < $count_of_products)}{*<pagebreak />*}{/if}
        {/foreach}
    </table>
</div>
