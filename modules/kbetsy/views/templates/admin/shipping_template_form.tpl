{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Admin Attribute Dropdown tpl file
*}
<form id="etsy_shipping_templates_form" class="defaultForm form-horizontal AdminEtsyShippingTemplates" action="{$form_action}" method="post" enctype="multipart/form-data" novalidate> {*Variable contains URL, can't escape*}
    <input type="hidden" name="submitAddetsy_shipping_templates" value="1" />
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i> 
            {if $fields_value['id_etsy_shipping_templates'] == ""}
                {l s='Add New Shipping Template' mod='kbetsy'}
            {else}
                {l s='Edit Shipping Template' mod='kbetsy'}
            {/if}
        </div>
        <div class="form-wrapper">
            <div class="form-group hide">
                <input type="hidden" name="ps_version" id="ps_version" value="" />
            </div>
            <div class="form-group hide">
                <input type="hidden" name="id_etsy_shipping_templates" id="id_etsy_shipping_templates" value="{$fields_value['id_etsy_shipping_templates']|escape:'htmlall':'UTF-8'}" />
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6">
                        <label class="control-label required col-lg-12" style="display: block; text-align: left">
                            {l s='Shipping Template Title' mod='kbetsy'}
                        </label>
                        <div class="col-lg-12"  style="display: block">
                            <input type="text" name="shipping_template_title" id="shipping_template_title" value="{$fields_value['shipping_template_title']|escape:'htmlall':'UTF-8'}" class="" maxlength="255" required="required"/>
                            <p class="help-block">{l s='Provide Shipping Template Title' mod='kbetsy'}</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="control-label required col-lg-12" style="display: block; text-align: left">
                            {l s='Origin Country' mod='kbetsy'}
                    </label>
                        <div class="col-lg-12"  style="display: block">
                            <select name="shipping_origin_country_id" id="shipping_origin_country_id" onchange="setOriginCountry()">
                                {foreach $countries_list as $country}
                                {if $fields_value['shipping_origin_country_id'] == $country['id_option']} 
                                <option value="{$country['id_option']|escape:'htmlall':'UTF-8'}" selected="selected">{$country['name']|escape:'htmlall':'UTF-8'}</option>
                                {else}
                                <option value="{$country['id_option']|escape:'htmlall':'UTF-8'}">{$country['name']|escape:'htmlall':'UTF-8'}</option>
                                {/if}
                                {/foreach}
                            </select>
                            <p class="help-block">{l s='Choose a country as an origin country of Shipment' mod='kbetsy'}</p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6">
                        <label class="control-label required col-lg-12" style="display: block; text-align: left">
                            {l s='Min Processing Days' mod='kbetsy'}
                        </label>
                        <div class="col-lg-12" style="display: block">
                            <input type="text" name="shipping_min_process_days" id="shipping_min_process_days" value="{$fields_value['shipping_min_process_days']|escape:'htmlall':'UTF-8'}" class=""/>
                            <p class="help-block">{l s='Provide minimum number of days of a shipment' mod='kbetsy'}</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="control-label required col-lg-12" style="display: block; text-align: left">
                            {l s='Max Processing Days' mod='kbetsy'}
                        </label>
                        <div class="col-lg-12" style="display: block">
                            <input type="text" name="shipping_max_process_days" id="shipping_max_process_days" value="{$fields_value['shipping_max_process_days']|escape:'htmlall':'UTF-8'}" class=""/>
                            <p class="help-block">{l s='Provide maximum number of days of a shipment' mod='kbetsy'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="shipping_template_entry">
                <div class="panel-heading" style="clear: both; margin-top: 20px">
                    <a href="javascript://" class="addnew_etnry" style="float: right; margin-right: 20px">{l s='Add new entry' mod='kbetsy'}</a>
                    <i class="icon-cogs"></i> {l s='Standard delivery' mod='kbetsy'}
                </div>
                {$template_entries_html}  {*Variable contains HTML, can not escape*}
            </div>


            <div id="shipping_upgrades_entry">
                <div class="panel-heading" style="clear: both; margin-top: 20px">
                    <a href="javascript://" class="addnew_upgrade" style="float: right; margin-right: 20px">{l s='Add new upgrade' mod='kbetsy'}</a>
                    <i class="icon-cogs"></i> {l s='Delivery upgrades' mod='kbetsy'}
                </div>
                {$template_upgrades_html}  {*Variable contains HTML, can not escape*}
            </div>
        </div>

        <div class="panel-footer">
            <a class="btn btn-default" id="etsy_shipping_templates_form_cancel_btn" onclick="javascript:window.history.back();">
                <i class="process-icon-cancel"></i> {l s='Cancel' mod='kbetsy'}
            </a>
            <button type="button"  class="btn btn-default btn btn-default pull-right" name="submitEtsyShippingTemplates" onclick="validation('etsy_shipping_templates_form')"><i class="process-icon-save" ></i> {l s='Save' mod='kbetsy'}</button>
        </div>
    </div>
</form>
<style type="text/css">
    #etsy_shipping_templates_form .form-group {
        margin-bottom: 0px;
    }    
</style>

<script type="text/javascript">
    //changes by vishal for adding velovalidation in shipping template 
    var kb_shipping_template_entry_error = "{l s='Kindly Add atleast 1 row in Shipping Delivery' mod='kbetsy'}";
    var kb_title_multiple_error = "{l s='Duplicate Titles' mod='kbetsy'}";
    var kb_title_multiple_country_error = "{l s='Duplicate Country Selection' mod='kbetsy'}";
    var greater_amount_shipping_secoundary_cost = "{l s='Additional Item cost should be less than One Item Cost' mod='kbetsy'}";
    //changes end
$(document).ready(function() {
    
    
    $("body").on("click", ".deleteEntry", function() {
        if($(this).parent().parent().parent().find('.existing_entry').val() == '1') {
            var confirm = window.confirm("{l s='Are you sure to delete the same? It will delete from the Etsy account as well.' mod='kbetsy'}");
            if(confirm) {
                var currentelement = $(this);
                $.ajax({
                    url:'{$controller_url}&action=fetchentry',    /*Variable contains URL, can't escape*/
                    type: 'post',
                    data: 'type=deleteentry&entry_id=' + $(this).parent().parent().parent().find('.entry_id').val(),
                    success: function(html) {
                        currentelement.parent().parent().parent().remove();
                    }
                });
            }
        } else {
            var confirm = window.confirm("{l s='Are you sure to delete the same?' mod='kbetsy'}");
            if(confirm) {
                $(this).parent().parent().parent().remove();
            }
        }
    }); 
    
    $("body").on("click", ".deleteUpgrade", function() {
        if($(this).parent().parent().parent().find('.existing_entry').val() == '1') {
            var confirm = window.confirm("{l s='Are you sure to delete the same? It will delete from the Etsy account as well.' mod='kbetsy'}");
            if(confirm) {
                var currentelement = $(this);
                $.ajax({
                    url:'{$controller_url}&action=fetchentry',          /*Variable contains URL, can't escape*/
                    type: 'post',
                    data: 'type=deleteupgrade&upgrade_id=' + $(this).parent().parent().parent().find('.upgrade_id').val(),
                    success: function(html) {
                        currentelement.parent().parent().parent().remove();
                    }
                });
            }
        } else {
            var confirm = window.confirm("{l s='Are you sure to delete the same?' mod='kbetsy'}");
            if(confirm) {
                $(this).parent().parent().parent().remove();
            }
        }
    }); 
    
    
    $(".addnew_etnry").bind("click", function() {
        $.ajax({
           url:'{$controller_url}&action=fetchentry',       /*Variable contains URL, can't escape*/
           type: 'post',
           data: 'type=entry',
           success: function(html) {
               $("#shipping_template_entry").append(html);
               $(".destination_type").each(function() {
                    $(this).on("change", function() {
                        if ($(this).val() == '1') {
                        $(this).parent().parent().parent().find(".country_list").show();
                        $(this).parent().parent().parent().find(".region_list").hide();
                        } else {
                        $(this).parent().parent().parent().find(".country_list").hide();
                        $(this).parent().parent().parent().find(".region_list").show();
                        }
                    });
                });
           }
        });
    });
    
    $(".addnew_upgrade").bind("click", function() {
        $.ajax({
           url:'{$controller_url}&action=fetchentry',       /*Variable contains URL, can't escape*/
           type: 'post',
           data: 'type=upgrade',
           success: function(html) {
               $("#shipping_upgrades_entry").append(html);
           }
        });
    });    
});  
</script>