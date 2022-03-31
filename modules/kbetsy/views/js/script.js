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
    showHideProductType($("select#etsy_product_type").val());
    //tagInputConvert();

    $('.start_date,.end_date').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#product-sale-sku-select").chosen();
    $("#product-sale-sku-select_chosen").attr('style', 'width:200px;');

    $('#etsy_category_mapping_form_submit_btn').on('click', function () {
        $(".error_message").hide();
        var error = false;
        var presta_cat = '';
        var presta_cat_list = '';
        $('#prestashop_category').find(":input[type=checkbox]").each(
                function ()
                {
                    if ($(this).prop("checked") == true) {
                        presta_cat = '1';
                        presta_cat_list = presta_cat_list + $(this).val() + ','
                    }
                });
        if (presta_cat == '') {
            error = store_cat_proc;
            $('<p class="error_message" style="color:red">' + error + '</p>').appendTo(document.getElementById('prestashop_category').closest('.col-lg-9'));
        }

        if (!error) {
            var error_data = CheckCategoryExist($("select#etsy_category_code").val(), presta_cat_list);
            if ((error_data != '') && (error_data != 'undefined')) {
                error = true;
                $('<p class="error_message" style="color:red">' + error_data + '</p>').appendTo(document.getElementById('prestashop_category').closest('.col-lg-9'));
            }
        }
        if (!error)
        {
            $("#etsy_category_mapping_form").submit();
        } else {
            return false;
        }
    });

});

function tagInputConvert() {
    $('input[name="etsy_selected_products"]').autocomplete(product_search_action + 'ajax_products_list.php', {
        delay: 10,
        minChars: 1,
        autoFill: true,
        max: 20,
        matchContains: true,
        mustMatch: true,
        scroll: false,
        multipleSeparator: '||',
        formatItem: function(item) {
            return item[1] + ' - ' + item[0];
        },
        extraParams: {
            excludeVirtuals: 0,
            exclude_packs: 0
        }
    }).result(function(event, item, formatted) {
        addProductToExclude(item);
        event.stopPropagation();
    });


    /*
    var citynames = [ { "value": 1 , "text": "Amsterdam"   , "continent": "Europe"    },
  { "value": 2 , "text": "London"      , "continent": "Europe"    },
  { "value": 3 , "text": "Paris"       , "continent": "Europe"    },
  { "value": 4 , "text": "Washington"  , "continent": "America"   },
  { "value": 5 , "text": "Mexico City" , "continent": "America"   },
  { "value": 6 , "text": "Buenos Aires", "continent": "America"   },
  { "value": 7 , "text": "Sydney"      , "continent": "Australia" },
  { "value": 8 , "text": "Wellington"  , "continent": "Australia" },
  { "value": 9 , "text": "Canberra"    , "continent": "Australia" },
  { "value": 10, "text": "Beijing"     , "continent": "Asia"      },
  { "value": 11, "text": "New Delhi"   , "continent": "Asia"      },
  { "value": 12, "text": "Kathmandu"   , "continent": "Asia"      },
  { "value": 13, "text": "Cairo"       , "continent": "Africa"    },
  { "value": 14, "text": "Cape Town"   , "continent": "Africa"    },
  { "value": 15, "text": "Kinshasa"    , "continent": "Africa"    }
];

    var elt = $('#etsy_selected_products');


    elt.tagsinput({
        itemValue: 'value',
        itemText: 'text',
        typeaheadjs: {
            name: 'cities',
            source: function (query, process) {
                return $.ajax({
                    url: 'http://localhost/cities.json',
                    // async: false, // better go async
                    data: 'q=' + query,
                    type: 'POST',
                    cache: false,
                    success: function (data) {
                        data = $.parseJSON(data);
                        return process(data);

                    }
                })
            }
        }
    });
    elt.tagsinput('add', { "value": 1 , "text": "Amsterdam"   , "continent": "Europe"    });
*/
}
function getOptionsFromJson(json) {
    return $.map(json, function (n, i) {
        return n.text;
    });
}

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
        $('#savebtn').val('save');
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
        $("select[name^=etsy_sync_lang]").removeClass('error_field');
        $("#etsy_default_lang").removeClass('error_field');

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

        /*Knowband validation start*/
        var api_ver_err = velovalidation.checkMandatory($("#etsy_default_lang"));
        if (api_ver_err != true)
        {
            error = true;
            $("#etsy_default_lang").addClass('error_field');
            $("#etsy_default_lang").after('<span class="error_message">' + api_ver_err + '</span>');
        }
        /*Knowband validation end*/

        /*Knowband validation start*/
//        console.log($("#etsy_default_lang").val());
//$('td[name=tcol1]')

        var sync_lang = $("select[name^=etsy_sync_lang]").val();
        if (sync_lang != null) {
            var present = sync_lang.indexOf($("#etsy_default_lang").val());
            if (present > -1) {
                error = true;
                $("select[name^=etsy_sync_lang]").addClass('error_field');
                $("#etsy_default_lang").addClass('error_field');
                $("select[name^=etsy_sync_lang]").after('<span class="error_message">' + lang_err + '</span>');
            }
        }
        /*Knowband validation end*/
    }

    //Validating Configuration Form
    if (form_id == 'saveonly_configuration_form')
    {
        $('#savebtn').val('saveonly');
        var etsy_api_key = $("#etsy_api_key").val();
        var etsy_api_secret = $("#etsy_api_secret").val();
        var etsy_api_host = $("#etsy_api_host").val();
        var etsy_api_version = $("#etsy_api_version").val();
        var etsy_currency = $("#etsy_currency").val();

        //Hide error message default
        $(".error_message").hide();
        $("#etsy_api_key").removeClass('error_field');
        $("#etsy_api_secret").removeClass('error_field');
        $("#etsy_api_host").removeClass('error_field');
        $("#etsy_api_version").removeClass('error_field');
        $("select[name^=etsy_sync_lang]").removeClass('error_field');
        $("#etsy_default_lang").removeClass('error_field');
        $("#min_threshold_quant").removeClass('error_field');
        $("#etsy_currency").removeClass('error_field');

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

        var etsy_currency_err = velovalidation.checkMandatory($("#etsy_currency"));
        if (etsy_currency_err != true)
        {
            error = true;
            $("#etsy_currency").addClass('error_field');
            $("#etsy_currency").after('<span class="error_message">' + etsy_currency_err + '</span>');
        }

        //Check Etsy API Secret Value
        /*Knowband validation start*/
        var api_secret_err = velovalidation.checkMandatory($("#etsy_api_secret"));
        if (api_secret_err != true)
        {
            error = true;
            $("#etsy_api_secret").addClass('error_field');
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

        /*Knowband validation start*/
        var api_ver_err = velovalidation.checkMandatory($("#etsy_default_lang"));
        if (api_ver_err != true)
        {
            error = true;
            $("#etsy_default_lang").addClass('error_field');
            $("#etsy_default_lang").after('<span class="error_message">' + api_ver_err + '</span>');
        }

        var threshold_qty_err = velovalidation.checkMandatory($("#min_threshold_quant"));
        if (threshold_qty_err != true)
        {
            error = true;
            $("#min_threshold_quant").addClass('error_field');
            $("#min_threshold_quant").after('<span class="error_message">' + threshold_qty_err + '</span>');
        } else {
            var min_qty_num = velovalidation.isNumeric($('input[name="min_threshold_quant"]'), true);
            if (min_qty_num != true) {
                error = true;
                $('input[name="min_threshold_quant"]').addClass('error_field');
                $('input[name="min_threshold_quant"]').after('<span class="error_message">' + min_qty_num + '</span>');
            }

        }
        /*Knowband validation end*/

        /*Knowband validation start*/
        var sync_lang = $("select[name^=etsy_sync_lang]").val();
        if (sync_lang != null) {
            var present = sync_lang.indexOf($("#etsy_default_lang").val());
            if (present > -1) {
                error = true;
                $("select[name^=etsy_sync_lang]").addClass('error_field');
                $("#etsy_default_lang").addClass('error_field');
                $("select[name^=etsy_sync_lang]").after('<span class="error_message">' + lang_err + '</span>');
            }
        }
        /*Knowband validation end*/

        form_id = 'configuration_form';
    }
    //Validating Shipping Template Form
    if (form_id == 'etsy_shipping_templates_form')
    {
        //changes by vishal for adding velovalidation in shipping template
        var shipping_template_title = $("#shipping_template_title").val();
        var origin_country = $("#shipping_origin_country_id").val();
        var min_processing_days = $("#shipping_min_process_days").val();
        var max_processing_days = $("#shipping_max_process_days").val();

        //Hide error message default
        $('input[name*="shipping_primary_cost"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('input[name*="shipping_secondary_cost"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('input[name*="shipping_upgrade_primary_cost"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('input[name*="shipping_upgrade_secondary_cost"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('input[name*="shipping_upgrade_title"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('select[name*="shipping_desination_country"]').each(function () {
            $(this).removeClass('error_field');
        });
        $('select[name*="shipping_destination_region"]').each(function () {
            $(this).removeClass('error_field');
        });


//        $(".error_message").hide();
        $(".error_message").remove();
        $("#shipping_template_title").removeClass('error_field');
        $("#shipping_origin_country_id").removeClass('error_field');
        $("#shipping_min_process_days").removeClass('error_field');
        $("#shipping_max_process_days").removeClass('error_field');

        var ship_title_err = velovalidation.checkMandatory($("#shipping_template_title"));
        if (ship_title_err != true)
        {
            error = true;
            $("#shipping_template_title").addClass('error_field');
            $("#shipping_template_title").after('<span class="error_message">' + ship_title_err + '</span>');
        }

        var ship_ori_count_err = velovalidation.checkMandatory($("#shipping_origin_country_id"));
        if (ship_ori_count_err != true)
        {
            error = true;
            $("#shipping_origin_country_id").addClass('error_field');
            $("#shipping_origin_country_id").next('.chosen-container').after('<span class="error_message">' + ship_ori_count_err + '</span>');
        }


        if (min_processing_days == '')
        {
            error = true;
            $("#shipping_min_process_days").addClass('error_field');
            $("#shipping_min_process_days").after('<span class="error_message">' + min_proc_err + '</span>');
        } else {
            var regex = /^([0-9]|10|15|20|25|30|35|40|45|50)$/;
            if (!regex.test(min_processing_days))
            {
                error = true;
                $("#shipping_min_process_days").addClass('error_field');
                $("#shipping_min_process_days").after('<span class="error_message">' + range_err + '</span>');
            }
        }
        if (max_processing_days == '')
        {
            error = true;
            $("#shipping_max_process_days").addClass('error_field');
            $("#shipping_max_process_days").after('<span class="error_message">' + max_proc_err + '</span>');
        } else
        {
            var regex = /^([0-9]|10|15|20|25|30|35|40|45|50)$/;
            if (!regex.test(max_processing_days))
            {
                error = true;
                $("#shipping_max_process_days").addClass('error_field');
                $("#shipping_max_process_days").after('<span class="error_message">' + range_err + '</span>');
            }
        }

        if ((min_processing_days != '') && (max_processing_days != ''))
        {
            if (parseInt(min_processing_days) > parseInt(max_processing_days))
            {
                error = true;
                $("#shipping_min_process_days").addClass('error_field');
                $("#shipping_min_process_days").after('<span class="error_message">' + process_day_err + '</span>');
            }
            if (!error) {
                if ((parseInt(min_processing_days) != NaN) && ((parseInt(min_processing_days) % 5) != 0) && (parseInt(max_processing_days) != NaN) && (parseInt(max_processing_days) > 10)) {
                    error = true;
                    $("#shipping_max_process_days").addClass('error_field');
                    $("#shipping_max_process_days").after('<span class="error_message">' + range_max_err + '</span>');
                }
            }
        }

        $('input[name*="shipping_primary_cost"]').each(function () {
            var kb_shipping_primary_cost = velovalidation.checkMandatory($(this));
            var amount_shipping_primary_cost = velovalidation.checkAmount($(this));
            if (kb_shipping_primary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + kb_shipping_primary_cost + '</span>');
            } else if (amount_shipping_primary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + amount_shipping_primary_cost + '</span>');
            }
        });

        $('input[name*="shipping_secondary_cost"]').each(function () {
            var kb_shipping_secoundary_cost = velovalidation.checkMandatory($(this));
            var amount_shipping_secoundary_cost = velovalidation.checkAmount($(this));
            if (kb_shipping_secoundary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + kb_shipping_secoundary_cost + '</span>');
            } else if (amount_shipping_secoundary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + amount_shipping_secoundary_cost + '</span>');
            } else if($(this).parent().parent().parent().find('input[name*="shipping_primary_cost"]').val() != ""){
                if(parseFloat($(this).parent().parent().parent().find('input[name*="shipping_primary_cost"]').val())<=parseFloat($(this).val())){
                    error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + greater_amount_shipping_secoundary_cost + '</span>');
                }
            }
        });

        $('input[name*="shipping_desination_country"]').each(function () {
            var kb_shipping_destination_country = velovalidation.checkMandatory($(this));
            if ($(this).closest('.form-group').find('select[name*="destination_type"]').val() == '1') { //check if country selected
                if (kb_shipping_destination_country != true) {
                    error = true;
                    $(this).addClass('error_field');
                    $(this).next('.chosen-container').after('<span class="error_message">' + kb_shipping_destination_country + '</span>');
                }
            }

        });

        if ($('#shipping_template_entry').children().hasClass('form-group') == false) {
            error = true;
            $('#shipping_template_entry').children().addClass('error_field');
            $('#shipping_template_entry').after('<span class="error_message">' + kb_shipping_template_entry_error + '</span>');

        }

        $('input[name*="shipping_upgrade_primary_cost"]').each(function () {
            var kb_upgrade_shipping_primary_cost = velovalidation.checkMandatory($(this));
            var upgrade_amount_shipping_primary_cost = velovalidation.checkAmount($(this));
            if (kb_upgrade_shipping_primary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + kb_upgrade_shipping_primary_cost + '</span>');
            } else if (upgrade_amount_shipping_primary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + upgrade_amount_shipping_primary_cost + '</span>');
            }
        });

        $('input[name*="shipping_upgrade_secondary_cost"]').each(function () {
            var kb_upgrade_shipping_secoundary_cost = velovalidation.checkMandatory($(this));
            var upgrade_amount_shipping_secoundary_cost = velovalidation.checkAmount($(this));
            if (kb_upgrade_shipping_secoundary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + kb_upgrade_shipping_secoundary_cost + '</span>');
            } else if (upgrade_amount_shipping_secoundary_cost != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + upgrade_amount_shipping_secoundary_cost + '</span>');
            } else if($(this).parent().parent().parent().find('input[name*="shipping_upgrade_primary_cost"]').val() != ""){
                if(parseFloat($(this).parent().parent().parent().find('input[name*="shipping_upgrade_primary_cost"]').val())<=parseFloat($(this).val())){
                    error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + greater_amount_shipping_secoundary_cost + '</span>');
                }
            }
        });

        $('input[name*="shipping_upgrade_title"]').each(function () {
            var kb_upgrade_title = velovalidation.checkMandatory($(this));
            var upgrade_amount_shipping_secoundary_cost = velovalidation.checkAmount($(this));
            if (kb_upgrade_title != true) {
                error = true;
                $(this).addClass('error_field');
                $(this).after('<span class="error_message">' + kb_upgrade_title + '</span>');
            }
        });

        $('select[name*="shipping_desination_country"]').each(function () {
            var kb_shipping_destination_country = velovalidation.checkMandatory($(this));
            if ($(this).closest('.form-group').find('select[name*="destination_type"]').val() == '1') { //check if country selected
                if (kb_shipping_destination_country != true) {
                    error = true;
                    $(this).addClass('error_field');
                    $(this).after('<span class="error_message">' + kb_shipping_destination_country + '</span>');
                }
            }
        });

        $('select[name*="shipping_destination_region"]').each(function () {
            var kb_shipping_destination_region = velovalidation.checkMandatory($(this));
            if ($(this).closest('.form-group').find('select[name*="destination_type"]').val() == '2') { //check if regions selected
                if (kb_shipping_destination_region != true) {
                    if ($(this).parent().parent().parent().find('.destination_type').val() == 2) {
                        error = true;
                        $(this).addClass('error_field');
                        $(this).after('<span class="error_message">' + kb_shipping_destination_region + '</span>');
                    }
                }
            }
        });

        $('input[name*="shipping_upgrade_title"]').each(function () {
            var kb_title_name = $(this).val();
            var kb_tiitle_attr_name = $(this).attr('name');
            $('input[name*="shipping_upgrade_title"]').each(function () {
                if ($(this).attr('name') == kb_tiitle_attr_name || $(this).val() == "") {
                    return;
                }
                if ($(this).val() == kb_title_name) {
                    if ($(this).parent().find('.error_message').length > 0) {
                        return;
                    }
                    error = true;
                    $(this).addClass('error_field');
                    $(this).after('<span class="error_message">' + kb_title_multiple_error + '</span>');
                }

            });
        });

        $('select[name*="shipping_desination_country"]').each(function () {
            var kb_title_name = $(this).val();
            var kb_tiitle_attr_name = $(this).attr('name');
            $('select[name*="shipping_desination_country"]').each(function () {
                if ($(this).attr('name') == kb_tiitle_attr_name || $(this).val() == "") {
                    return;
                }
                if ($(this).val() == kb_title_name) {
                    if ($(this).parent().find('.error_message').length > 0) {
                        return;
                    }
                    error = true;
                    $(this).addClass('error_field');
                    $(this).after('<span class="error_message">' + kb_title_multiple_country_error + '</span>');
                }

            });
        });
    }
    //changes end

    //Validating Shipping Template Form
    if (form_id == 'etsy_shop_section_form')
    {
        //Hide error message default
        $(".error_message").hide();
        $("#section_template_title").removeClass('error_field');

        /*Knowband validation start*/
        var section_title_err = velovalidation.checkMandatory($("#shop_section_title"));
        if (section_title_err != true)
        {
            error = true;
            $("#shop_section_title").addClass('error_field');
            $("#shop_section_title").after('<span class="error_message">' + section_title_err + '</span>');
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

        if (destination_country == '' && destination_type == '1')
        {
            error = true;
            $("#shipping_entry_destination_country_id").addClass('error_field');
            $("#shipping_entry_destination_country_id").next('.chosen-container').after('<span class="error_message">' + country_err + '</span>');
        }

//        if (destination_country != '' && destination_type == '1')
//        {
//            if (origin_country == destination_country)
//            {
//                error = true;
//                $("#shipping_entry_destination_country_id").addClass('error_field');
//                $("#shipping_entry_destination_country_id").after('<span class="error_message">Origin and Destination cannot be same.</span>');
//            }
//        }

        if (destination_region == '' && destination_type == '2')
        {
            error = true;
            $("#shipping_entry_destination_region_id").addClass('error_field');
            $("#shipping_entry_destination_region_id").after('<span class="error_message">' + region_err + '</span>');
        }

        /*Knowband validation start*/
        var ship_ent_prim_cst_err = velovalidation.checkMandatory($("#shipping_entry_primary_cost"));
        if (ship_ent_prim_cst_err != true)
        {
            error = true;
            $("#shipping_entry_primary_cost").addClass('error_field');
            $("#shipping_entry_primary_cost").after('<span class="error_message">' + ship_ent_prim_cst_err + '</span>');
        }
        if ($("#shipping_entry_primary_cost").val().trim() != '' && ship_ent_prim_cst_err == true)
        {
            var val = $("#shipping_entry_primary_cost").val().trim();
            var regex = /^\d*(.\d{2})?$/;
            var entry_primary_cost = velovalidation.checkAmount($("#shipping_entry_primary_cost"));
            if (entry_primary_cost != true)
            {
                error = true;
                $("#shipping_entry_primary_cost").addClass('error_field');
                $("#shipping_entry_primary_cost").after('<span class="error_message">' + amount_err + '</span>');
            } else if (val > 20000) {
                error = true;
                $("#shipping_entry_primary_cost").addClass('error_field');
                $("#shipping_entry_primary_cost").after('<span class="error_message">' + amount_max_err + '</span>');
            }
        }
        /*Knowband validation end*/

        /*Knowband validation start*/
        var ship_ent_sec_cst_err = velovalidation.checkMandatory($("#shipping_entry_secondary_cost"));
        if (ship_ent_sec_cst_err != true)
        {
            error = true;
            $("#shipping_entry_secondary_cost").addClass('error_field');
            $("#shipping_entry_secondary_cost").after('<span class="error_message">' + ship_ent_sec_cst_err + '</span>');
        }
        if ($("#shipping_entry_secondary_cost").val().trim() != '' && ship_ent_sec_cst_err == true)
        {
            var val = $("#shipping_entry_secondary_cost").val().trim();
            var regex = /^\d*(.\d{2})?$/;
            var entry_secondary_cost = velovalidation.checkAmount($("#shipping_entry_secondary_cost"));
            if (entry_secondary_cost != true)
            {
                error = true;
                $("#shipping_entry_secondary_cost").addClass('error_field');
                $("#shipping_entry_secondary_cost").after('<span class="error_message">' + amount_err + '</span>');
            } else if (val > 20000) {
                error = true;
                $("#shipping_entry_secondary_cost").addClass('error_field');
                $("#shipping_entry_secondary_cost").after('<span class="error_message">' + amount_max_err + '</span>');
            }
        }
        /*Knowband validation end*/
    }

    //Validating Shipping Template Entry Form
    if (form_id == 'etsy_shipping_upgrades_form')
    {
        var origin_country = $("#shipping_origin_country_id").val().trim();
        var destination_type = $("#destination_type").val().trim();
        var primary_cost = $("#shipping_upgrade_primary_cost").val().trim();
        var secondary_cost = $("#shipping_upgrade_secondary_cost").val().trim();
        var upgrade_title = $("#shipping_upgrade_title").val().trim();

        //Hide error message default
        $(".error_message").hide();
        $("#shipping_upgrade_primary_cost").removeClass('error_field');
        $("#shipping_upgrade_secondary_cost").removeClass('error_field');
        $("#shipping_upgrade_title").removeClass('error_field');

        /*Knowband validation start*/
        var ship_upg_tit_err = velovalidation.checkMandatory($("#shipping_upgrade_title"));
        if (ship_upg_tit_err != true)
        {
            error = true;
            $("#shipping_upgrade_title").addClass('error_field');
            $("#shipping_upgrade_title").after('<span class="error_message">' + ship_upg_tit_err + '</span>');
        }



        var ship_ent_prim_cst_err = velovalidation.checkMandatory($("#shipping_upgrade_primary_cost"));
        if (ship_ent_prim_cst_err != true)
        {
            error = true;
            $("#shipping_upgrade_primary_cost").addClass('error_field');
            $("#shipping_upgrade_primary_cost").after('<span class="error_message">' + ship_ent_prim_cst_err + '</span>');
        }
        if ($("#shipping_upgrade_primary_cost").val().trim() != '' && ship_ent_prim_cst_err == true)
        {
            var val = $("#shipping_upgrade_primary_cost").val().trim();
            var regex = /^\d*(.\d{2})?$/;
            var entry_primary_cost = velovalidation.checkAmount($("#shipping_upgrade_primary_cost"));
            if (entry_primary_cost != true)
            {
                error = true;
                $("#shipping_upgrade_primary_cost").addClass('error_field');
                $("#shipping_upgrade_primary_cost").after('<span class="error_message">' + amount_err + '</span>');
            } else if (val > 20000) {
                error = true;
                $("#shipping_upgrade_primary_cost").addClass('error_field');
                $("#shipping_upgrade_primary_cost").after('<span class="error_message">' + amount_max_err + '</span>');
            }
        }
        /*Knowband validation end*/

        /*Knowband validation start*/
        var ship_ent_sec_cst_err = velovalidation.checkMandatory($("#shipping_upgrade_secondary_cost"));
        if (ship_ent_sec_cst_err != true)
        {
            error = true;
            $("#shipping_upgrade_secondary_cost").addClass('error_field');
            $("#shipping_upgrade_secondary_cost").after('<span class="error_message">' + ship_ent_sec_cst_err + '</span>');
        }
        if ($("#shipping_upgrade_secondary_cost").val().trim() != '' && ship_ent_sec_cst_err == true)
        {
            var val = $("#shipping_upgrade_secondary_cost").val().trim();
            var regex = /^\d*(.\d{2})?$/;
            var entry_secondary_cost = velovalidation.checkAmount($("#shipping_upgrade_secondary_cost"));
            if (entry_secondary_cost != true)
            {
                error = true;
                $("#shipping_upgrade_secondary_cost").addClass('error_field');
                $("#shipping_upgrade_secondary_cost").after('<span class="error_message">' + amount_err + '</span>');
            } else if (val > 20000) {
                error = true;
                $("#shipping_upgrade_secondary_cost").addClass('error_field');
                $("#shipping_upgrade_secondary_cost").after('<span class="error_message">' + amount_max_err + '</span>');
            }
        }
        /*Knowband validation end*/
    }

    if (form_id == 'etsy_profiles_form')
    {
        var profile_title = $("#profile_title").val();

        //Hide error message default
        $(".error_message").remove();
        $("#profile_title").removeClass('error_field');
        $('input[name="banner_image"]').closest('.form-group').find('.input-group').removeClass('error_field');
        /*Knowband validation start*/
        var pro_title_err = velovalidation.checkMandatory($("#profile_title"));
        if (pro_title_err != true)
        {
            error = true;
            $("#profile_title").addClass('error_field');
            $("#profile_title").after('<span class="error_message">' + pro_title_err + '</span>');
        }
        /*Start-MK made changes on 23-11-2017 to validate the custom title, max, min price field*/
        $('input[name="customize_product_title"]').removeClass('error_field');
        var custom_title = velovalidation.checkMandatory($('input[name="customize_product_title"]'));
        if (custom_title != true) {
            error = true;
            $('input[name="customize_product_title"]').addClass('error_field');
            $('input[name="customize_product_title"]').after('<span class="error_message">' + custom_title + '</span>');
        }

        var max_qty = parseInt($('input[name="max_qty"]').val().trim());
        var min_qty = parseInt($('input[name="min_qty"]').val().trim());

        $('input[name="min_qty"]').removeClass('error_field');
        $('input[name="max_qty"]').removeClass('error_field');
        if ($("input[name='enable_min_qty']:checked").val() == 1) {
            var min_qty_mand = velovalidation.checkMandatory($('input[name="min_qty"]'));
            if (min_qty_mand != true) {
                error = true;
                $('input[name="min_qty"]').addClass('error_field');
                $('input[name="min_qty"]').after('<span class="error_message">' + min_qty_mand + '</span>');
            } else {
                var min_qty_num = velovalidation.isNumeric($('input[name="min_qty"]'), true);
                if (min_qty_num != true) {
                    error = true;
                    $('input[name="min_qty"]').addClass('error_field');
                    $('input[name="min_qty"]').after('<span class="error_message">' + min_qty_num + '</span>');
                } else {
                    if (min_qty == 0) {
                        error = true;
                        $('input[name="min_qty"]').addClass('error_field');
                        $('input[name="min_qty"]').after('<span class="error_message">' + min_qty_zero + '</span>');
                    } else if (min_qty > 999) {
                        error = true;
                        $('input[name="min_qty"]').addClass('error_field');
                        $('input[name="min_qty"]').after('<span class="error_message">' + min_qty_vald + '</span>');
                    }
                }

            }
        }

        if ($("input[name='size_chart_image']:checked").val() == 1) {
            if ($('input[name="banner_image"]').prop('files').length) {
                validate_image = velovalidation.checkImage($('input[name="banner_image"]'));
                if (validate_image != true) {
                    error = true;
                    $('input[name="banner_image"]').closest('.form-group').find('.input-group').addClass('error_field');
                    $('input[name="banner_image"]').closest('.form-group').after('<span class="error_message">' + validate_image + '</span>');
                }
            } else {
                if (is_size_chart_image_exists == 0) {
                    error = true;
                    $('input[name="banner_image"]').closest('.form-group').find('.input-group').addClass('error_field');
                    $('input[name="banner_image"]').closest('.form-group').after('<span class="error_message">' + size_chart_image_missing + '</span>');
                }
            }
        } else {
            if ($('input[name="banner_image"]').prop('files').length) {
                validate_image = velovalidation.checkImage($('input[name="banner_image"]'));
                if (validate_image != true) {
                    $('input[name="banner_image"]').val('');
                }
            }
        }

        if ($("input[name='enable_max_qty']:checked").val() == 1) {
            var max_qty_mand = velovalidation.checkMandatory($('input[name="max_qty"]'));
            if (max_qty_mand != true) {
                error = true;
                $('input[name="max_qty"]').addClass('error_field');
                $('input[name="max_qty"]').after('<span class="error_message">' + max_qty_mand + '</span>');
            } else {
                var max_qty_num = velovalidation.isNumeric($('input[name="max_qty"]'), true);
                if (max_qty_num != true) {
                    error = true;
                    $('input[name="max_qty"]').addClass('error_field');
                    $('input[name="max_qty"]').after('<span class="error_message">' + max_qty_num + '</span>');
                } else {
                    if (max_qty == 0) {
                        error = true;
                        $('input[name="max_qty"]').addClass('error_field');
                        $('input[name="max_qty"]').after('<span class="error_message">' + min_qty_zero + '</span>');
                    } else if (max_qty > 999) {
                        error = true;
                        $('input[name="max_qty"]').addClass('error_field');
                        $('input[name="max_qty"]').after('<span class="error_message">' + max_qty_vald + '</span>');
                    } else {
                        if ($("input[name='enable_min_qty']:checked").val() == 1) {

                            if (max_qty >= 0) {
                                if (max_qty < min_qty) {
                                    error = true;
                                    $('input[name="max_qty"]').addClass('error_field');
                                    $('input[name="max_qty"]').after('<span class="error_message">' + max_qty_err + '</span>');
                                }
                            }
                        }
                    }
                }

            }
        }


        var presta_cat = '';
        var presta_cat_list = '';
        if ($('select[name="etsy_product_type"]').val() == "0") {
            $('#prestashop_category').find(":input[type=checkbox]").each(function () {
                if ($(this).prop("checked") == true) {
                    presta_cat = '1';
                    presta_cat_list = presta_cat_list + $(this).val() + ','
                }
            });
            if (presta_cat == '') {
                error = store_cat_proc;
                $('<p class="error_message" style="color:red">' + error + '</p>').appendTo(document.getElementById('prestashop_category').closest('.col-lg-9'));
            }
        } else {
            if ($('#kbetsy_selected_products').val() == "") {
                error = store_profile_product;
                $('<p class="error_message" style="color:red">' + error + '</p>').appendTo(document.getElementById('etsy_selected_products').closest('.col-lg-4'));
            }
        }

        if ($('input[name="custom_pricing"]:checked').val() == 1) {
            var price_mand = velovalidation.checkMandatory($('input[name="custom_price"]'));
            if (price_mand != true) {
                error = true;
                $('input[name="custom_price"]').addClass('error_field');
                $('input[name="custom_price"]').after('<span class="error_message">' + price_mand + '</span>');
            } else {
                if ($('select[name="price_type"]').val() == 'Fixed') {
                    var check_price_mand = velovalidation.checkAmount($('input[name="custom_price"]'));
                    if (check_price_mand != true) {
                        error = true;
                        $('input[name="custom_price"]').addClass('error_field');
                        $('input[name="custom_price"]').after('<span class="error_message">' + check_price_mand + '</span>');
                    }

                } else if ($('select[name="price_type"]').val() == 'Percentage') {
                    var check_price_per = velovalidation.checkPercentage($('input[name="custom_price"]'));
                    if (check_price_per != true) {
                        error = true;
                        $('input[name="custom_price"]').addClass('error_field');
                        $('input[name="custom_price"]').after('<span class="error_message">' + check_price_per + '</span>');
                    }
                }
            }

        }

        if (!error) {
            if ($('select[name="etsy_product_type"]').val() == "0") {
                var error_data = CheckCategoryExist($("select#etsy_category_code").val(), presta_cat_list);
                if ((error_data != '') && (error_data != 'undefined')) {
                    error = true;
                    $('<p class="error_message" style="color:red">' + error_data + '</p>').appendTo(document.getElementById('prestashop_category').closest('.col-lg-9'));
                }
            }
        }
        /*End-MK made changes on 23-11-2017 to validate the custom title, max, min price field*/
        if (!error) {
            if (callback !== undefined) {
                return true;
            }
            var id_etsy_profiles = $.trim($('input[name="id_etsy_profiles"]').val());
            if (id_etsy_profiles != '') {
                var r = confirm(profile_confirmation_text);
                if (r == true) {
                    $('input[name="update_profile_product"]').val("1");
                } else {
                    $('input[name="update_profile_product"]').val("0");
                }
            }

            $("#" + form_id).submit();

        } else {
            if (callback !== undefined) {
                return false;
            }
        }

        /*Knowband validation end*/
    }

    //Submit Form if no error
    if (!error)
    {
        if (callback !== undefined)
        {
            return true;
        }
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

//Function definition to disconnect connection
function save()
{
    var url = $("#save_url").val();
    window.location.href = url;
}

$(document).ready(function () {

    if ($('input[name="map_etsy_order_store_carrier"]').length) {
        if ($("input[name='map_etsy_order_store_carrier']:checked").val() == 0) {
            $('#KB_SHIPPING_METHOD_ETSY_ORDER').closest('.form-group').hide();
        }
        if ($("input[name='map_etsy_order_store_carrier']:checked").val() == 1) {
            $('#KB_SHIPPING_METHOD_ETSY_ORDER').closest('.form-group').show();
        }

        $('input[name="map_etsy_order_store_carrier"]').on('click', function () {
            if ($("input[name='map_etsy_order_store_carrier']:checked").val() == 0) {
                $('#KB_SHIPPING_METHOD_ETSY_ORDER').closest('.form-group').hide();
            } else {
                $('#KB_SHIPPING_METHOD_ETSY_ORDER').closest('.form-group').show();
            }
        });

        if ($("input[name='upload_tracking_number']:checked").val() == 0) {
            $('#etsy_selected_shipment_name').closest('.form-group').hide();
        }
        if ($("input[name='upload_tracking_number']:checked").val() == 1) {
            $('#etsy_selected_shipment_name').closest('.form-group').show();
        }

        $('input[name="upload_tracking_number"]').on('click', function () {
            if ($("input[name='upload_tracking_number']:checked").val() == 0) {
                $('#etsy_selected_shipment_name').closest('.form-group').hide();
            } else {
                $('#etsy_selected_shipment_name').closest('.form-group').show();
            }
        });

    }

    if ($('input[name="banner_image"]').length) {

        if ($("input[name='size_chart_image']:checked").val() == 0) {
            $('#kbsizechartlogo').closest('.form-group').parent().parent().hide();
        }
        if ($("input[name='size_chart_image']:checked").val() == 1) {
            $('#kbsizechartlogo').closest('.form-group').parent().parent().show();
        }

        $('input[name="size_chart_image"]').on('click', function () {
            if ($("input[name='size_chart_image']:checked").val() == 0) {
                $('#kbsizechartlogo').closest('.form-group').parent().parent().hide();
            } else {
                $('#kbsizechartlogo').closest('.form-group').parent().parent().show();
            }
        });
        $('input[name="banner_image"]').on('change', function () {
            $("input[name='banner_image']").find('.input-group').removeClass('error_field');
            var imgPath = $(this)[0].value;
            $('.error_message').remove();
            var image_holder = $("#kbsizechartlogo");
            if (($("input[name='banner_image']").prop("files").length)) {
                var validate_image = velovalidation.checkImage($(this), 2097152, 'kb');
                if (validate_image != true) {
                    $('input[name="filename"]').val('');
                    showErrorMessage(validate_image);
                    $("input[name='banner_image']").closest('.form-group').find('.input-group').addClass('error_field');
                    $('input[name="banner_image"]').closest('.form-group').after('<span class="error_message">' + validate_image + '</span>');
                } else {
                    $("input[name='banner_image']").parents('.form-group').removeClass('error_field');
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#kbsizechartlogo').attr('src', e.target.result);
                    }
                    image_holder.show();
                    reader.readAsDataURL($(this)[0].files[0]);
                }
            }
        });
    }
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
   })

   //changes by vishal for adding velovalidation in shipping template
   $('select[name*="destination_type"]').change(function(){
      if($(this).parent().parent().next().children().children().hasClass('error_field')){
          $(this).parent().parent().next().children().children().removeClass('error_field');
      }
   });
   //
});

function switchEntryDestinationTypes(val)
{

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

function showHideProductType(product_selection_type) {
    if(product_selection_type == 0) {
        $("#prestashop_category").parent().parent().parent().show();
        $("#etsy_selected_products").parent().parent().hide();
    } else {
        $("#prestashop_category").parent().parent().parent().hide();
        $("#etsy_selected_products").parent().parent().show();
    }
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
                id_etsy_profiles: $("#id_etsy_profiles").val(),
                id_profile_category: $('#id_profile_category').val(),
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

//Function definition to send ajax request to Check existing category
function CheckCategoryExist(category_code, category_list)
{
    var error_data = '';
    if (category_code != '')
    {

        $.ajax({
            type: 'POST',
            url: $("#property_ajax_url").val(),
            async: false,
            data: {
                //required parameters
                ajaxCheckCategoryExist: true,
                etsy_category_code: category_code,
                id_etsy_profiles: $("#id_etsy_profiles").val(),
                id_profile_category: $('#id_profile_category').val(),
                prestashop_category: category_list
            },
            success: function (data) {
                error_data = data;
            }
        });
    }
    return error_data;
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
        $(".synchronization").html('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">x</button>' + sync_msg + '</div>');
    } else {
        $(".synchronization").html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button>' + sync_fail_msg + '</div>');
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
//Function definition to execute job to sync orders listing
function sync_product_image()
{
    $("#sync_image_url").attr('disabled', true);
    $(".sync_image_url_loader").show();
    //Execute Job to sync orders listing from Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_image_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_image_url_loader").hide();
                $("#sync_image_url").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_image_url_loader").hide();
                $("#sync_image_url").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync orders listing
function sync_product_quantitys()
{
    $("#sync_product_quantity_url").attr('disabled', true);
    $(".sync_product_quantitys_loader").show();
    //Execute Job to sync orders listing from Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_product_quantity_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_product_quantitys_loader").hide();
                $("#sync_product_quantity_url").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_product_quantitys_loader").hide();
                $("#sync_product_quantity_url").attr('disabled', false);
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

//Function definition to execute job to sync translations
function sync_translations()
{
    $("#sync_translations").attr('disabled', true);
    $(".sync_translations_loader").show();
    //Execute Job to sync orders status on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_translations_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_translations_loader").hide();
                $("#sync_translations").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_translations_loader").hide();
                $("#sync_translations").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

//Function definition to execute job to sync translations
function sync_inventory()
{
    $("#sync_inventory").attr('disabled', true);
    $(".sync_inventory_loader").show();
    //Execute Job to sync orders status on Etsy Marketplace
    $.ajax({
        type: 'GET',
        url: $("#sync_inventory_url").val(),
        data: {},
        success: function (data) {
            if (data == 'Success')
            {
                $(".sync_inventory_loader").hide();
                $("#sync_inventory").attr('disabled', false);
                show_notification('success');
            } else {
                $(".sync_inventory_loader").hide();
                $("#sync_inventory").attr('disabled', false);
                show_notification('failed');
            }
        }
    });
}

$(document).ready(function () {
    if (typeof KbcurrentToken != 'undefined') {
        $('.etsy_selected_products').autocomplete(
                'ajax-tab.php', {
                    minChars: 2,
                    max: 50,
                    delay: 100,
                    width: 500,
                    selectFirst: false,
                    scroll: false,
                    dataType: 'json',
                    cache: true,
                    cacheLength: 0,
                    formatItem: function (data, i, max, value, term) {
                        return value;
                    },
                    parse: function (data) {
                        var mytab = new Array();
                        for (var i = 0; i < data.length; i++) {
                            mytab[mytab.length] = {data: data[i], value: data[i].name + ' (' + data[i].reference + ')'};
                        }
                        return mytab;
                    },
                    extraParams: {
                        controller: 'AdminEtsyProfileManagement',
                        excludeIds: function () {
                            var ids = '';
                            if ($('input[name="kbetsy_selected_products"]').val() === undefined)
                                return '';

                            return  $('input[name="kbetsy_selected_products"]').val().replace(/\-/g, ',');
                        },
                        token: KbcurrentToken,
                        searchKbProduct: 1
                    }
                }
        ).result(function (event, data, formatted) {
            $.ajax({
                type: "POST",
                url: controller_path,
                data: 'is_product_selected=true&ajax=true&id_product=' + data.id_product,
                beforeSend: function () {
                },
                success: function (response) {
                    if (response != '') {
                        var $kbetsy_selected_products = $('input[name="kbetsy_selected_products"]');
                        $('.etsy_selected_products').after($('.kb-add-etsy-product'));
                        $('.kb-add-etsy-product').show();

                        $('.kb-add-etsy-product tbody').append(response);
                        $kbetsy_selected_products.val($kbetsy_selected_products.val() + data.id_product + '-');
                        $('.etsy_selected_products').val('');
                    }
                }
            });
        });
    }

    $('.etsy_selected_products').after($('.kb-add-etsy-product'));
    $('input[name="kbetsy_selected_products"]').change(function () {
        if ($('input[name="kbetsy_selected_products"]').val() != '') {
            $('.kb-add-etsy-product').show();
        } else {
            $('.kb-add-etsy-product').hide();
        }

    }).change();

    $('.kb-add-etsy-product').on('click', '.kb-product-remove', function () {
        var id_product = $(this).closest('tr').find('.kb-etsy-product-id').html();
        var input = $('input[name="kbetsy_selected_products"]');
        var inputCut = input.val().split('-');
        // Reset all hidden fields
        input.val('');

        for (i in inputCut)
        {
            // If empty, error, next
            if (!inputCut[i]) {
                continue;
            }

            // Add to hidden fields no selected products OR add to select field selected product
            if (inputCut[i] != id_product)
            {
                input.val(input.val() + inputCut[i] + '-');
            }
        }
        $(this).closest('tr').remove();
        if (input.val() == '') {
            $('.kb-add-etsy-product').hide();
        } else {
            $('.kb-add-etsy-product').show();
        }
    });

    $(document).on('change', '.velsof_number_field', function () {
        this.value = this.value.replace(/,/g, '.');
    });

    $('input[name="customize_product_title"]').parent().append($('#customize_product_title_block'));
    $('#customize_product_title_block').show();

    $("input[name='enable_max_qty']").click(function () {
        if ($("input[name='enable_max_qty']:checked").val() == 0) {
            $('input[name="max_qty"]').closest('.form-group').hide();
        } else {
            $('input[name="max_qty"]').closest('.form-group').show();
        }
    });

    if ($("input[name='enable_max_qty']:checked").val() == 0) {
        $('input[name="max_qty"]').closest('.form-group').hide();
    } else if ($("input[name='enable_max_qty']:checked").val() == 1) {
        $('input[name="max_qty"]').closest('.form-group').show();
    }

    $("input[name='enable_min_qty']").click(function () {
        if ($("input[name='enable_min_qty']:checked").val() == 0) {
            $('input[name="min_qty"]').closest('.form-group').hide();
        } else {
            $('input[name="min_qty"]').closest('.form-group').show();
        }
    });

    if ($("input[name='enable_min_qty']:checked").val() == 0) {
        $('input[name="min_qty"]').closest('.form-group').hide();
    } else if ($("input[name='enable_min_qty']:checked").val() == 1) {
        $('input[name="min_qty"]').closest('.form-group').show();
    }

    $('input[name="custom_pricing"]').closest('.form-group').after($('#etsy_custom_pricing'));

    if ($("input[name='custom_pricing']:checked").val() == 0) {
        $('#etsy_custom_pricing').hide();
    }
    if ($("input[name='custom_pricing']:checked").val() == 1) {
        $('#etsy_custom_pricing').show(200);
    }

    $('input[name="custom_pricing"]').on('click', function () {
        if ($("input[name='custom_pricing']:checked").val() == 0) {
            $('#etsy_custom_pricing').hide();
        } else {
            $('#etsy_custom_pricing').show(200);
        }
    });

});
