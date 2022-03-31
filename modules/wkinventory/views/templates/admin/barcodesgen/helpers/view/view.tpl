{*
* This file is part of the 'Wk Stock Manager' module feature
* Developped by Khoufi Wissem from Tunisia (2016).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
* @author    Khoufi Wissem - K.W
* @copyright Khoufi Wissem
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{* TOOLBAR FOR PS 1.5 *}
{if $is_before_16}
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title={$title_page|escape:'html':'UTF-8'}}
{/if}

{if !$isModuleFolderWritable}
<div class="alert alert-danger">
    {l s='Sorry, you don\'t have permissions to generate codes' mod='wkinventory'}!
</div>
{/if}
<div class="barCodeGenerator">
    {***** FILTERS PANEL *****}
    <div class="panel col-lg-3">
        <div class="panel-heading"><span class="badge">I</span> {l s='Apply to products which belong to' mod='wkinventory'}</div>
        <div class="form-group">
        <label>{l s='Supplier(s)' mod='wkinventory'}</label>
        <select id="ids_suppliers" class="chosen" multiple="multiple" data-placeholder="{l s='Choose' mod='wkinventory'}">
            <option value=""></option>
            {foreach from=$suppliers item=supplier}
            <option value="{$supplier.id_supplier|intval}">{$supplier.name|escape:'html':'UTF-8'}</option>
            {/foreach}
        </select>
        </div>
        <div class="form-group">
        <label>{l s='Manufacturer(s)' mod='wkinventory'}</label>
        <select id="ids_manufacturers" class="chosen" multiple="multiple" data-placeholder="{l s='Choose' mod='wkinventory'}">
            <option value=""></option>
            {foreach from=$manufacturers item=manufacturer}
            <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
            {/foreach}
        </select>
        </div>
        <div class="alert alert-info">
        	{l s='Don\'t use filters above if you want to apply to all products' mod='wkinventory'}.
        </div>
    </div>
    {***** GENERATION PANEL *****}
    <div class="panel col-lg-9" id="barcode-panels">
        <div class="panel-heading"><i class="icon-barcode"></i> {l s='Choose the generation method' mod='wkinventory'}</div>
        <div class="content">
            {***** FIRST PANEL -> GENERATE FOR MISSING CODES *****}
            <div class="panel panel-empty">
                <div class="panel-heading"><span class="badge">II</span> {l s='Generate EAN13/UPC for products with empty codes' mod='wkinventory'}</div>
                <div class="alert alert-info">
                    <ul>
                        <li>{l s='Click on this button to generate automatically EAN13/UPC for missing codes' mod='wkinventory'}.</li>
                        <li>{l s='The codes we generate are compatible with marketplaces through digit control' mod='wkinventory'}.</li>
                    </ul>
                </div>
                <button class="btn btn-primary btn-lg btn-block{if !$isModuleFolderWritable} disabled{/if}" id="genCodeEmptyProducts" type="button"/><span class="icon-barcode"></span> {l s='Start the generation' mod='wkinventory'} <span class="icon-arrow-circle-right"></span></button>
            </div>

            {***** MESSAGE WHEN PROCESSING TASKS *****}
            <div class="ajaxstatus alert alert-warning" style="display:none;"><a href="#" class="close" data-dismiss="alert">&times;</a>
                <span></span> <i class="icon icon-refresh icon-spin" style="display:none;"></i>
            </div>
            {****************************************}

            {***** SECOND PANEL -> GENERATE FOR ALL PRODUCTS *****}
            <div class="panel panel-all">
                <div class="panel-heading"><span class="badge">III</span> {l s='Generate EAN13/UPC codes for all products' mod='wkinventory'}</div>
                <div class="alert alert-info">
                    <ul>
                        <li>{l s='Click on this button to generate automatically EAN13/UPC codes for all products' mod='wkinventory'}.</li>
                        <li>{l s='If you do not have an EAN13 or UPC we advise you to generate for the first time' mod='wkinventory'}.</li>
                        <li>{l s='The codes we generate are compatible with marketplaces through digit control' mod='wkinventory'}.</li>
                        <li><div class="badge badge-warning">{l s='Be carefull, All EAN and UPC will be regenerated even if they exist' mod='wkinventory'}.</div></li>
                    </ul>
                </div>
                <button class="btn btn-primary btn-lg btn-block{if !$isModuleFolderWritable} disabled{/if}" id="genCodeForceProducts" type="button"/><span class="icon-barcode"></span> {l s='Start the generation' mod='wkinventory'} <span class="icon-arrow-circle-right"></span></button>
            </div>
        </div>
    </div>
    <div id="openmsgbox"></div>
</div>

<script type="text/javascript">
	var generationInfo = "{l s='The generation process may take some time depending on the number of your products/combinations in your catalog' js=1 mod='wkinventory'}";
	var continueMsg = '{l s='Are you sur you want to continue?' mod='wkinventory' js=1}';
	var warningMsg = '{l s='Warning' mod='wkinventory' js=1}';
	var loadingMsg = '{l s='Loading' mod='wkinventory' js=1}...';
	var cancelMsg = '{l s='Cancel' mod='wkinventory' js=1}';
	var waitingMsg = '{l s='Please wait' mod='wkinventory' js=1}';

	$('#ids_suppliers, #ids_manufacturers').chosen({
		no_results_text: "{l s='No matches found' js=1 mod='wkinventory'}",
        disable_search: false,
	});	
</script>
