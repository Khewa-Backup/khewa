/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function() {

    $('#container_forms form').each(function() {
        if($(this).attr("id") != "form-ewrelatedproductsbyprod")
            $(this).addClass("hidden");
    });

    if($('#type_related_products').length > 0 && $('#type_related_products').val() == 0) {
        $('#module_form').find($('.type_related_products')).parent().parent().addClass("hidden");
    } else if($('#id_relatedproduct').val() > 0) {
        if($('#type_related_products').val() == 1) {
            $('#feature').parent().parent().removeClass("hidden");
            $('#feature_value').parent().parent().removeClass("hidden");
            $('#attribute').parent().parent().addClass("hidden");
            $('#attribute_value').parent().parent().addClass("hidden");
            $('#reference').parent().parent().addClass("hidden");

            loadFeatureValues($('#feature').val(), $('#id_relatedproduct').val());
        } else if($('#type_related_products').val() == 2) {
            $('#attribute').parent().parent().removeClass("hidden");
            $('#attribute_value').parent().parent().removeClass("hidden");
            $('#feature').parent().parent().addClass("hidden");
            $('#feature_value').parent().parent().addClass("hidden");
            $('#reference').parent().parent().addClass("hidden");

            loadAttributeValues($('#attribute').val(), $('#id_relatedproduct').val());
        } else if($('#type_related_products').val() == 3) {
            $('#reference').parent().parent().removeClass("hidden");
            $('#feature').parent().parent().addClass("hidden");
            $('#feature_value').parent().parent().addClass("hidden");
            $('#attribute').parent().parent().addClass("hidden");
            $('#attribute_value').parent().parent().addClass("hidden");
        }
    }

    $('#type_related_products').on('change', function() {
        if($('#type_related_products').val() == 1) {
            $('#feature').parent().parent().removeClass("hidden");
            $('#feature_value').parent().parent().removeClass("hidden");
            $('#attribute').parent().parent().addClass("hidden");
            $('#attribute_value').parent().parent().addClass("hidden");
            $('#reference').parent().parent().addClass("hidden");

            loadFeatureValues($('#feature').val());
        } else if($('#type_related_products').val() == 2) {
            $('#attribute').parent().parent().removeClass("hidden");
            $('#attribute_value').parent().parent().removeClass("hidden");
            $('#feature').parent().parent().addClass("hidden");
            $('#feature_value').parent().parent().addClass("hidden");
            $('#reference').parent().parent().addClass("hidden");

            loadAttributeValues($('#attribute').val());
        } else if($('#type_related_products').val() == 3) {
            $('#reference').parent().parent().removeClass("hidden");
            $('#feature').parent().parent().addClass("hidden");
            $('#feature_value').parent().parent().addClass("hidden");
            $('#attribute').parent().parent().addClass("hidden");
            $('#attribute_value').parent().parent().addClass("hidden");
        }
    });

    $.widget('prestashop.psBlockSearchAutocomplete', $.ui.autocomplete, {
        _renderItem: function (ul, product) {
            return $("<li>")
                .append($("<a style='display: flex; align-items: center; align-content: center;'>")
                    .append('<img src="'+product.cover.small.url+'" alt="'+product.name+'" width="50" height="50" style="margin-right: 10px;" />')
                    .append($("<span>").html(product.name + ' (Ref: ' + product.reference + ')').addClass("product"))
                ).appendTo(ul)
                ;
        }
    });

    $('#id_product').psBlockSearchAutocomplete({
        source: function (query, response) {
            $.post(search_controller_url, {
                s: query.term,
                resultsPerPage: 10
            }, null, 'json')
                .then(function (resp) {
                    response(resp.products);
                })
                .fail(response);
        },
        select: function( event, ui ) {
            $("#id_product").val(ui.item.id_product + ' (# ' + ui.item.name + ' #)');
            return false;
        },
    });

    $('#feature').on('change', function() {
        var id_feature = $('#feature').val();
        loadFeatureValues(id_feature);
    });

    $('#attribute').on('change', function() {
        var id_attribute_group = $('#attribute').val();
        loadAttributeValues(id_attribute_group);
    });
});

function loadFeatureValues(id_feature, id_relatedproducts = null) {
    $.ajax({
        type: "POST",
        url: admin_related_products_url,
        data: {
            ajax: true,
            id_feature : id_feature,
            id_relatedproducts: id_relatedproducts,
            action: 'FindFeatureValues'
        },
        success: function(html)
        {
            if (html == 'false')
            {
                $("#feature_value").fadeOut();
            }
            else
            {
                $("#feature_value").html(html);
            }
        }
    });
}

function loadAttributeValues(id_attribute_group, id_relatedproducts = null) {
    $.ajax({
        type: "POST",
        url: admin_related_products_url,
        data: {
            ajax: true,
            id_attribute_group : id_attribute_group,
            id_relatedproducts: id_relatedproducts,
            action: 'FindAttributeGroupValues'
        },
        success: function(html)
        {
            if (html == 'false')
            {
                $("#attribute_value").fadeOut();
            }
            else
            {
                $("#attribute_value").html(html);
            }
        }
    });
}

function showForm(form_name, li_active) {

    $('#container_forms form').each(function() {
        $(this).addClass("hidden");
    });

    $('.list_of_tabs li').each(function() {
        $(this).removeClass("active");
    });

    $('.'+li_active).addClass("active");

    $('#'+form_name).removeClass("hidden");
}
