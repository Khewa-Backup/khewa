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
<pagebreak />
<div class="back_page">
	<div class="back_table">
    <table style="width: 90%;" align="center">
        <tr>
            <td class="label">{l s='Product(s)' mod='wkinventory'}</td>
            <td class="result">{$inventory_count|intval}</td>
        </tr>
        <tr>
            <td class="label"><strong>{l s='Stock valuation' mod='wkinventory'}</strong></td>
            <td class="result">{displayPrice price=$stock_valuation}</td>
        </tr>
    </table>
    </div>
    <div class="back_page_url">{$server_name|escape:'url':'UTF-8'}</div>
</div>