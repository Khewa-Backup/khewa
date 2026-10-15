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
{foreach $inventory_products as $inventory_product}
    <tr style="font-size: 7pt;">                    
        <td style="text-align: left; padding-left: 1px; border-bottom: 0.5pt solid #8E8E8E;">{$inventory_product['id_product']|intval}</td>
        <td style="text-align: left; padding-left: 1px; border-bottom: 0.55pt solid #8E8E8E;">{$inventory_product['name']|escape:'htmlall':'UTF-8'}
        {if $inventory_product['combination']}<br /><span style="color:#999; font-style:italic">{$inventory_product['combination']|escape:'htmlall':'UTF-8'}</span>{/if}
        {if $inventory_product['reference']}<br /><strong>{l s='Reference:' mod='wkinventory'}:</strong> {$inventory_product['reference']|escape:'htmlall':'UTF-8'}{/if}
        {if $inventory_product['ean13']}<br /><strong>{l s='EAN13' mod='wkinventory'}:</strong> {$inventory_product['ean13']|escape:'htmlall':'UTF-8'}{/if}
        </td>
        <td style="text-align: right; padding-right: 1px; border-bottom: 0.5pt solid #8E8E8E;">{$inventory_product['real_quantity']|intval}</td>
        <td style="text-align: right; padding-right: 1px; border-bottom: 0.5pt solid #8E8E8E;">{displayPrice price=$inventory_product['unit_price']}</td>
        {assign var=total_price value=$inventory_product['real_quantity']*$inventory_product['unit_price']}
        <td style="text-align: right; padding-right: 1px; border-bottom: 0.5pt solid #8E8E8E;">{displayPrice price=$total_price}</td>
    </tr>
{/foreach}
