/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store. 
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 */

//Code to execute on window load
$(function () {
    if ($("#destination_type").val() == '1')
    {
        $("#shipping_destination_region_id").parent().parent().hide();
        $("#shipping_entry_destination_region_id").parent().parent().hide();
    } else {
        $("#shipping_destination_country_id").parent().parent().hide();
        $("#shipping_entry_destination_country_id").parent().parent().hide();
    }
    $('select#etsy_category_code, select#id_etsy_shipping_templates, \n\
        select#who_made, select#when_made, select#recipient, \n\
        select#occassion, select#shipping_origin_country_id, select#destination_type, \n\
        select#shipping_destination_country_id, select#shipping_entry_destination_country_id, \n\
        select#etsy_order_default_status, select#etsy_order_paid_status, select#etsy_order_shipped_status').chosen({width: '100%', search_contains: true});

    getPropertiesList($("select#etsy_category_code").val());
});

//Function to show listing error in FancyBox
function show_etsy_listing_error(c)
{
    $('.' + c).fancybox();
}

//Form Validation
function validation(form_id, callback)
{
    var error = false;

    //Validating Configuration Form
    if (form_id == 'configuration_form')
    {
        var etsy_api_key = $("#etsy_api_key").val();
        var etsy_api_secret = $("#etsy_api_secret").val();
        var etsy_api_host = $("#etsy_api_host").val();
        var etsy_api_version = $("#etsy_api_version").val();

        //Hide error message default
        $(".error_message").hide();
        $("#etsy_api_key").removeClass('error_field');
        $("#etsy_api_secret").removeClass('error_field');
        $("#etsy_api_host").removeClass('error_field');
        $("#etsy_api_version").removeClass('error_field');

        //Check Etsy API Key Value
        /*Knowband validation start*/
        var api_key_err = velovalidation.checkMandatory($("#etsy_api_key"));
        if (api_key_err != true)
        {
            error = true;
            $("#etsy_api_key").addClass('error_field');
            $("#etsy_api_key").after('<span class="error_message">' + api_key_err + '</span>');
        }
        /*Knowband validation end*/
        //Check Etsy API Secret Value
        /*Knowband validation start*/
        var api_secret_err = velovalidation.checkMandatory($("#etsy_api_secret"));
        if (api_secret_err != true)
        {
            error = true;
            $("#etsy_api_secret").addClass('error_field');
//            $("#etsy_api_secret").after('<span class="error_message">Please provide Etsy API Secret.</span>');
            $("#etsy_api_secret").after('<span class="error_message">' + api_secret_err + '</span>');
        }
        /*Knowband validation end*/
        //Check Etsy API Host Value
        /*Knowband validation start*/
        var api_host_err = velovalidation.checkMandatory($("#etsy_api_host"));
        if (api_host_err != true)
        {
            error = true;
            $("#etsy_api_host").addClass('error_field');
//            $("#etsy_api_host").after('<span class="error_message">Please provide Etsy API Host.</span>');
            $("#etsy_api_host").after('<span class="error_message">' + api_host_err + '</span>');
        }
        /*Knowband validation end*/
        //Check Etsy API Version Value
        /*Knowband validation start*/
        var api_ver_err = velovalidation.checkMandatory($("#etsy_api_version"));
        if (api_ver_err != true)
        {
            error = true;
            $("#etsy_api_version").addClass('error_field');
            $("#etsy_api_version").after('<span class="error_message">' + api_ver_err + '</span>');
        }
        /*Knowband validation end*/
    }

    //Validating Shipping Template Form
    if (form_id == 'etsy_shipping_templates_form')
    {
        var shipping_template_title = $("#shipping_template_title").val();
        var origin_country = $("#shipping_origin_country_id").val();
        var primary_cost = $("#shipping_primary_cost").val();
        var secondary_cost = $("#shipping_secondary_cost").val();
        var min_processing_days = $("#shipping_min_process_days").val();
        var max_processing_days = $("#shipping_max_process_days").val();

        //Hide error message default
        $(".error_message").hide();
        $("#shipping_template_title").removeClass('error_field');
        $("#shipping_origin_country_id").removeClass('error_field');
        $("#shipping_primary_cost").removeClass('error_field');
        $("#shipping_secondary_cost").removeClass('error_field');
        $("#shipping_min_process_days").removeClass('error_field');
        $("#shipping_max_process_days").removeClass('error_field');

        /*Knowband validation start*/
        var ship_title_err = velovalidation.checkMandatory($("#shipping_template_title"));
        if (ship_title_err != true)
        {
            error = true;
            $("#shipping_template_title").addClass('error_field');
            $("#shipping_template_title").after('<span class="error_message">' + ship_title_err + '</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        var ship_ori_count_err = velovalidation.checkMandatory($("#shipping_origin_country_id"));
        if (ship_ori_count_err != true)
        {
            error = true;
            $("#shipping_origin_country_id").addClass('error_field');
            $("#shipping_origin_country_id").next('.chosen-container').after('<span class="error_message">' + ship_ori_count_err + '</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        var ship_prim_cst_err = velovalidation.checkMandatory($("#shipping_primary_cost"));
        if (ship_prim_cst_err != true)
        {
            error = true;
            $("#shipping_primary_cost").addClass('error_field');
            $("#shipping_primary_cost").after('<span class="error_message">' + ship_prim_cst_err + '</span>');
        }

        if (primary_cost != '')
        {
            var regex = /^\d*(.\d{2})?$/;
            if (!regex.test(primary_cost))
            {
                error = true;
                $("#shipping_primary_cost").addClass('error_field');
                $("#shipping_primary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
            }
        } else {
            error = true;
            $("#shipping_primary_cost").addClass('error_field');
            $("#shipping_primary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
        }
        
        var ship_prim_cst_am_err = velovalidation.checkAmount($("#shipping_primary_cost"));
        if (ship_prim_cst_am_err != true)
        {
            error = true;
            $("#shipping_primary_cost").addClass('error_field');
            $("#shipping_primary_cost").after('<span class="error_message">' + ship_prim_cst_am_err + '</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        var ship_sec_cst__err = velovalidation.checkMandatory($("#shipping_primary_cost"));
        if (ship_sec_cst__err != true)
        {
            error = true;
            $("#shipping_secondary_cost").addClass('error_field');
            $("#shipping_secondary_cost").after('<span class="error_message">' + ship_sec_cst__err + '</span>');
        }
        var ship_sec_cst_am_err = velovalidation.checkAmount($("#shipping_secondary_cost"));
//            var regex = /^\d*(.\d{2})?$/;
        if (ship_sec_cst_am_err != true)
        {
            error = true;
            $("#shipping_secondary_cost").addClass('error_field');
            $("#shipping_secondary_cost").after('<span class="error_message">' + ship_sec_cst_am_err + '</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        if (min_processing_days == '')
        {
//            error = true;
//            $("#shipping_min_process_days").addClass('error_field');
//            $("#shipping_min_process_days").after('<span class="error_message">Please enter minimum number of processing days.</span>');
        } else
        {
            var regex = /^([1-9]|10)$/;
            if (!regex.test(min_processing_days))
            {
                error = true;
                $("#shipping_min_process_days").addClass('error_field');
                $("#shipping_min_process_days").after('<span class="error_message">Please enter valid number between 1 - 10.</span>');
            }
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        if (max_processing_days == '')
        {
//            error = true;
//            $("#shipping_max_process_days").addClass('error_field');
//            $("#shipping_max_process_days").after('<span class="error_message">Please enter maximum number of processing days.</span>');
        } else
        {
            var regex = /^([1-9]|10)$/;
            if (!regex.test(max_processing_days))
            {
                error = true;
                $("#shipping_max_process_days").addClass('error_field');
                $("#shipping_max_process_days").after('<span class="error_message">Please enter valid number between 1 - 10.</span>');
            }
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        if (parseInt(min_processing_days) != '' && parseInt(max_processing_days) != '')
        {
            if (parseInt(min_processing_days) >= parseInt(max_processing_days))
            {
                error = true;
                $("#shipping_min_process_days").addClass('error_field');
                $("#shipping_min_process_days").after('<span class="error_message">Minimum Processing Days cannot be greater than or equal to Maximum Processing Days.</span>');
            }
        }
        /*Knowband validation end*/

    }

    //Validating Shipping Template Entry Form
    if (form_id == 'etsy_shipping_templates_entries_form')
    {
        var origin_country = $("#shipping_origin_country_id").val().trim();
        var destination_type = $("#destination_type").val().trim();
        var destination_country = $("#shipping_entry_destination_country_id").val().trim();
        var primary_cost = $("#shipping_entry_primary_cost").val().trim();
        var secondary_cost = $("#shipping_entry_secondary_cost").val().trim();
        var destination_region = $("#shipping_entry_destination_region_id").val().trim();

        //Hide error message default
        $(".error_message").hide();
        $("#shipping_entry_destination_country_id").removeClass('error_field');
        $("#shipping_entry_primary_cost").removeClass('error_field');
        $("#shipping_entry_secondary_cost").removeClass('error_field');
        $("#shipping_entry_destination_region_id").removeClass('error_field');
        /*Knowband validation start*/
        if (destination_country == '' && destination_type == '1')
        {
            error = true;
            $("#shipping_entry_destination_country_id").addClass('error_field');
            $("#shipping_entry_destination_country_id").next('.chosen-container').after('<span class="error_message">Please choose Destination Country.</span>');
        }
        /*Knowband validation end*/

//        if (destination_country != '' && destination_type == '1')
//        {
//            if (origin_country == destination_country)
//            {
//                error = true;
//                $("#shipping_entry_destination_country_id").addClass('error_field');
//                $("#shipping_entry_destination_country_id").after('<span class="error_message">Origin and Destination cannot be same.</span>');
//            }
//        }

        /*Knowband validation start*/
        if (destination_region == '' && destination_type == '2')
        {
            error = true;
            $("#shipping_entry_destination_region_id").addClass('error_field');
            $("#shipping_entry_destination_region_id").after('<span class="error_message">Please choose Destination Region.</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        if (primary_cost != '')
        {
            var regex = /^\d*(.\d{2})?$/;
            if (!regex.test(primary_cost))
            {
                error = true;
                $("#shipping_entry_primary_cost").addClass('error_field');
                $("#shipping_entry_primary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
            }
        } else {
            error = true;
            $("#shipping_entry_primary_cost").addClass('error_field');
            $("#shipping_entry_primary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
        }
        /*Knowband validation end*/
        /*Knowband validation start*/
        if (secondary_cost != '')
        {
            var regex = /^\d*(.\d{2})?$/;
            if (!regex.test(secondary_cost))
            {
                error = true;
                $("#shipping_entry_secondary_cost").addClass('error_field');
                $("#shipping_entry_secondary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
            }
        } else {
            error = true;
            $("#shipping_entry_secondary_cost").addClass('error_field');
            $("#shipping_entry_secondary_cost").after('<span class="error_message">Please enter valid amount (e.g. 3.50).</span>');
        }
        /*Knowband validation end*/
    }
    /*Knowband validation start*/
    if (form_id == 'etsy_profiles_form')
    {
        var profile_title = $("#profile_title").val();

        //Hide error message default
        $(".error_message").hide();
        $("#profile_title").removeClass('error_field');

        var pro_title_err = velovalidation.checkMandatory($("#profile_title"));
        if (pro_title_err != true)
        {
            error = true;
            $("#profile_title").addClass('error_field');
            $("#profile_title").after('<span class="error_message">'+pro_title_err+'</span>');
        }
    }
    /*Knowband validation end*/
    //Submit Form if no error
    if (!error)
    {
        if (callback !== undefined)
        {
            return true;
        }
        $( ":button" ).attr('disabled', 'disabled');
        $("#" + form_id).submit();
    } else {
        if (callback !== undefined)
        {
            return false;
        }
    }
}

//Function definition to disconnect connection
function disconnect()
{
    var url = $("#disconnect_url").val();
    window.location.href = url;
}

//Function definition to switch destination type in Shipping Template Entry Form
function switchEntryDestinationTypes(val)
{
    if (val == '1')
    {
        $("#shipping_entry_destination_country_id").parent().parent().show();
        $("#shipping_entry_destination_region_id").parent().parent().hide();
    } else {
        $("#shipping_entry_destination_region_id").parent().parent().show();
        $("#shipping_entry_destination_country_id").parent().parent().hide();
    }
}

//Function definition to set origin country of Shipping Template Form
function setOriginCountry()
{
    var val = $("#shipping_origin_country_id option:selected").text();
    $("#shipping_origin_country").val(val);
}

//Function definition to set destination region of Shipping Template Entry Form
function setEntryDestinationRegion()
{
    var val = $("#shipping_entry_destination_region_id option:selected").text();
    $("#shipping_entry_destination_region").val(val);
}

//Function definition to set destination country of Shipping Template Entry Form
function setEntryDestinationCountry()
{
    var val = $("#shipping_entry_destination_country_id option:selected").text();
    $("#shipping_entry_destination_country").val(val);
}

//Function definition to send ajax request to get properties list of selected Etsy Category
function getPropertiesList(category_code)
{
    if (category_code != '')
    {
        $.ajax({
            type: 'POST',
            url: $("#property_ajax_url").val(),
            data: {
                //required parameters
                ajaxPropertiesList: true,
                category_code: category_code,
                id_etsy_profiles: $("#id_etsy_profiles").val()
            },
            success: function (data) {
                $("#etsy_attribute_mapping").remove();

                if (data != '')
                {
                    $("#etsy_category_code").parent().parent().after(data);
                }
            }
        });
    }
}

//Hide alert messages in Synchronization tab
function hide_notification()
{
    $(".synchronization").html('');
}

//Show alert messages in Synchronization tab
function show_notification(type)
{
    if (type == 'success')
    {
        $(".synchronization").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>Synchronization completed.</div>');
    } else {
        $(".synchronization").html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>Synchronization failed.</div>');
    }
}


//Function definition to execute job to sync shipping templates
function sync_shipping_templates()
{
    $("#sync_shipping_templates").attr('disabled', true);
    $(".sync_shipping_templates_loader").show();
    //Execute Job to sync shipping templates on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_shipping_templates_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_shipping_templates_loader").hide();
                $("#sync_shipping_templates").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_shipping_templates_loader").hide();
                $("#sync_shipping_templates").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync countries and regions
function sync_countries_regions()
{
    $("#sync_countries_regions").attr('disabled', true);
    $(".sync_countries_regions_loader").show();
    //Execute Job to sync countries from Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_countries_regions_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_countries_regions_loader").hide();
                $("#sync_countries_regions").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_countries_regions_loader").hide();
                $("#sync_countries_regions").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync prodcuts listing
function sync_products_listing()
{
    $("#sync_products_listing").attr('disabled', true);
    $(".sync_products_listing_loader").show();
    //Execute Job to sync products listing on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_products_listing_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_products_listing_loader").hide();
                $("#sync_products_listing").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_products_listing_loader").hide();
                $("#sync_products_listing").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync variations listing
function sync_variations_listing()
{
    $("#sync_variations_listing").attr('disabled', true);
    $(".sync_variations_listing_loader").show();
    //Execute Job to sync variations listing on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_variations_listing_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_variations_listing_loader").hide();
                $("#sync_variations_listing").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_variations_listing_loader").hide();
                $("#sync_variations_listing").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync orders listing
function sync_orders_listing()
{
    $("#sync_orders_listing").attr('disabled', true);
    $(".sync_orders_listing_loader").show();
    //Execute Job to sync orders listing from Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_orders_listing_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_orders_listing_loader").hide();
                $("#sync_orders_listing").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_orders_listing_loader").hide();
                $("#sync_orders_listing").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync orders status
function sync_orders_status()
{
    $("#sync_orders_status").attr('disabled', true);
    $(".sync_orders_status_loader").show();
    //Execute Job to sync orders status on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_orders_status_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_orders_status_loader").hide();
                $("#sync_orders_status").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_orders_status_loader").hide();
                $("#sync_orders_status").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

$(document).ready(function(){
    $('.edit').click(function(){
        
        $('.edit').attr('href', 'javascript://')
        
    })
   
    
});

