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
<div class="cover_page">
    <div class="cover_store_name">
        <table align="center">
            <tbody>
                <tr>
                    <td><img style="height: 98px;" src="{$logo|escape:'htmlall':'UTF-8'}" /></td>
                </tr>
                <tr>
                    <td>{$store_name|escape:'html':'UTF-8'}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="cover_title">{l s='Inventory Report' mod='wkinventory'}</div>
    <div class="cover_board" align="center">
        <div class="cover_inventory_name">{$inventory->name|escape:'htmlall':'UTF-8'}</div>
        <table align="center">
            <tbody>
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
    </div>
    <div class="cover_url">{$server_name|escape:'url':'UTF-8'}</div>
</div>