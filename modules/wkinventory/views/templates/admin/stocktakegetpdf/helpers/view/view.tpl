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
<div class="panel">
    <div class="panel-heading"><i class="icon-template"></i> {l s='Generation' mod='wkinventory'}<div id="settings" title="" class="settings_disable"></div></div>
    <div class="form-wrapper">
        <div id="progressbar"><div class="progress-label">{l s='Loading' mod='wkinventory'}...</div></div>
    </div>
    <input type="hidden" name="url2getpdf" value="{$getpdflink|escape:'html':'UTF-8'}" />
	<script type="text/javascript">
        inventories_products_ids = {$inventories_products_ids|escape:'html':'UTF-8'};
        parts = {$parts|escape:'html':'UTF-8'};
        post_data = {$post_data};{* HTML CONTENT *}
        urlpdf = '{$url|escape:'url':'UTF-8'}';
		txt_gencomplete = "{l s='Generation complete' js=1 mod='wkinventory'}";
		txt_download = "{l s='Download' js=1 mod='wkinventory'}";
		txt_waitlink = "{l s='Please wait, forming a link' js=1 mod='wkinventory'}...";
    </script>
</div>