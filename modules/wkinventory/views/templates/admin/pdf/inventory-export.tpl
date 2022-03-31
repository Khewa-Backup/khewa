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
<div style="font-size: 9pt; color: #444">
    <h1>{$inventory->name|escape:'htmlall':'UTF-8'}</h1>
    <table style="width: 30%; border: 0.65pt solid #4D4D4D;">
        <tbody style="font-size: 12pt; color: #666;">
            <tr>
                <td style="font-weight: bold;">{l s='Begin' mod='wkinventory'}</td>
                <td>: {$begin_inventory|escape:'htmlall':'UTF-8'}</td>
            </tr>            
            <tr>
                <td style="font-weight: bold;">{l s='End' mod='wkinventory'}</td>
                <td>: {$end_inventory|escape:'htmlall':'UTF-8'}</td>
            </tr>            
            <tr>
                <td style="font-weight: bold;">{l s='Employee' mod='wkinventory'}</td>
                <td>: {$employee_name|escape:'htmlall':'UTF-8'}</td>
            </tr>            
        </tbody>
    </table>
    <p>&nbsp;</p>
    <h4>{l s='Inventoried products' mod='wkinventory'}:</h4>
    <!-- PRODUCTS -->
    <div style="font-size: 5pt;">
        <table style="width: 100%;">
            <tr style="line-height:6px; border: none; font-size: 8pt;">                
                <td style="width: 7%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">#ID</td>
                <td style="width: 53%; text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 2px; font-weight: bold;">{l s='Product Info' mod='wkinventory'}</td>
                <td style="width: 10%; text-align: right; background-color: #4D4D4D; color: #FFF; padding-right: 2px; font-weight: bold;">{l s='Real Qty' mod='wkinventory'}</td>
                <td style="width: 15%; text-align: right; background-color: #4D4D4D; color: #FFF; padding-right: 2px; font-weight: bold;">{l s='Unit Price T.Excl' mod='wkinventory'}</td>
                <td style="width: 15%; text-align: right; background-color: #4D4D4D; color: #FFF; padding-right: 2px; font-weight: bold;">{l s='Total T.Excl' mod='wkinventory'}</td>                
            </tr>
            {* Inventory products *}
            {$inventory_products}{* HTML CONTENT *}
            <tr style="font-size: 8pt;">
                <td colspan="4" style="background-color: #4D4D4D; color: #FFF; text-align: right; padding-right: 1px;"><strong>{l s='Stock valuation' mod='wkinventory'}</strong> (<em>{$inventory_count|intval} {l s='product(s)' mod='wkinventory'}</em>)</td>
                <td style="text-align: right;background-color: #F2F2F2;">{displayPrice price=$stock_valuation}</td>
            </tr>
        </table>
    </div>
</div>