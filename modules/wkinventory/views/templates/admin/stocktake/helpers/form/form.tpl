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
{extends file="helpers/form/form.tpl"}

{block name="other_input"}
{if $key eq 'help_tab'}
<div class="alert alert-info">
	<ul>
		<li>{l s='Use filters below to create an inventory for a set of products for the needed shop' mod='wkinventory'}.</li>
		<li>{l s='Don\'t use filters if you plan to create a general inventory for all your products' mod='wkinventory'}.</li>
		<li>{l s='You can create an empty inventory to have the ability to add gradually the products to manage their stocks' mod='wkinventory'}.</li>
	</ul>
</div>
{/if}
{/block}

{block name="after"}
<div class="bootstrap">
    <div class="div-location"></div>
    <div class="dialog-notice">
        <a class="button btn btn-danger close-form-location" href="#"><i class="icon-remove-sign"></i></a>
        <div id="messages-container"></div>
    </div>
</div>
<div id="inventory_wrapper">
    {if isset($productsList)}
    	{if !$inventoryDone && isset($isSupervisor) && $isSupervisor}
            {$formScanUpdateProduct}{* HTML CONTENT *}
        {/if}
        {$productsList}{* HTML CONTENT *}
        </div>
    {/if}
</div>
<div id="openmsgbox"></div>
{if isset($id_inventory) && !empty($id_inventory)}
<audio id="alarmAudio" src="{$module_path|escape:'html':'UTF-8'}/views/media/sound.mp3" preload="none">{l s='Browser not support the audio' mod='wkinventory'}</audio>
{/if}
{/block}

{block name="script"}
    {if isset($id_inventory) && !empty($id_inventory)}
    var product_label = '{l s='Product' js=1 mod='wkinventory'}';
    var combination_label = '{l s='Combination' js=1 mod='wkinventory'}';
    var warehouse_label = '{l s='Warehouse' js=1 mod='wkinventory'}';
    var no_product_found = '{l s='No products found' js=1 mod='wkinventory'}';
    (function(){
        'use strict';
        	var ajaxUrl = "{$adminstocktakeLink|escape:'html':'UTF-8'}&token={getAdminToken tab='AdminStocktake'}&id_inventory={$id_inventory|escape:'html':'UTF-8'}",
            defaultQty = {$defaultQty|intval},
            addToExistantQty = {$addToExistantQty|intval},
            isSupervisor = {$isSupervisor|intval},
            updateInterval = 300000, // (in secondes - each 5 minutes).
            translations = {
                waitMsg: "{l s='Please wait' js=1 mod='wkinventory'}...",
                cancelTxt: "{l s='Cancel' js=1 mod='wkinventory'}",
                upToDate: "{l s='Quantities are up to date' js=1 mod='wkinventory'}",
                autofillAlert: "{l s='Real quantities fields will be calculated and aligned according to the defined quantity in this form:' js=1 mod='wkinventory'} ",
                continueMsg: "<br />{l s='Continue?' js=1 mod='wkinventory'}",
                invalidQtyMsg: "{l s='Warning: Invalid quantity to increase/decrease' js=1 mod='wkinventory'}.",
                warningMsg: "{l s='Attention' js=1 mod='wkinventory'}",
            }
        {literal}
            $(document).ready(function() {
                WK_INVENTORY.init({
                	ajaxUrl: ajaxUrl,
                    translations: translations,
                    defaultQty: defaultQty,
                    addToExistantQty: addToExistantQty,
                });
                $('#wkinventory_panel_form_scan input[name="ean"]').focus();

                /*************************************
                * ** Check for order each interval ***
                *************************************/
                if (updateInterval) {
                    setInterval(function(){
                        $.getJSON(ajaxUrl+'&ajax&action=update_orders_quantity', function(result) {
                            //console.log(result.message);
                            WK_INVENTORY.updateOrderQuantity(result, false);
                        });
                    }, updateInterval);
                }

                // Submit scan/find product to correct product qty
                $('#wkinventory_panel_form_scan').submit(WK_INVENTORY.formUpdate);
            });
        })();
        {/literal}
    {/if}
{/block}
