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

$(document).ready(function () {

    $body = $("body");

    $(document).on({
        ajaxStart: function() {
            $body.addClass("loading");
        },
        ajaxStop: function() {
            $body.removeClass("loading");
        }
    });

    $('#kb_wm_shipping_templates_form').on('click', 'button[name="removeShippingOverride"]', function(){
        $(this).closest('#new-add-shipping-override').remove();
    });


    $('.filtersalereport').click(function(){
        var error = false;
        $('.error_message').remove();
        $('input[name="start_date"]').removeClass('error_field');
        $('input[name="end_date"]').removeClass('error_field');
        $('input[name="end_date"]').closest('.input-group').removeClass('error_field');
        var start_date_mand = velovalidation.checkMandatory($('input[name="start_date"]'));
        if (start_date_mand != true) {
            error = true;
            $('input[name="start_date"]').addClass('error_field');
            $('input[name="start_date"]').closest('.input-group').after('<span class="error_message">' + start_date_mand + '</span>');
        }
        var end_date_mand = velovalidation.checkMandatory($('input[name="end_date"]'));
        if (end_date_mand != true) {
            error = true;
            $('input[name="end_date"]').addClass('error_field');
            $('input[name="end_date"]').closest('.input-group').after('<span class="error_message">' + end_date_mand + '</span>');
        } else {
            var start_date = Date.parse($('input[name="start_date"]').val());
            var end_date = Date.parse($('input[name="end_date"]').val());
            if (parseInt(end_date) < parseInt(start_date)) {
                error = true;
                $('input[name="end_date"]').closest('.input-group').addClass('error_field');
                $('input[name="end_date"]').closest('.input-group').after('<span class="error_message">' + end_date_error + '</span>');
            }
        }

        if (error) {
            $('html, body').animate({
                scrollTop: $(".error_message").offset().top-200
            }, 1000);
            return false;
        }
        if (error) {
            return false;
        } else {
            $.ajax({
                url: module_path,
                data: "start=" + $('input[name="start_date"]').val() + "&end=" + $('input[name="end_date"]').val() + '&groupby='+$('select[name="groupby"]').val()+'&ajax=true&getChart=true',
                type: 'post',
                datatype: 'json',
                success: function (json)
                {
//                    console.log()
                    $('.flot_graph').html('');
                    kbDrawChart(json.graph);
                    $('.salereporttable').remove();
                    $('#show_loader_filter').hide();
                    $('.salereportgraph').append(json.table);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(technical_error);
                }
            });
        }

    });


    $('.productsalereportbtn').click(function(){
        var error = false;
        $('.error_message').remove();
        $('input[name="start_date"]').closest('.input-group').removeClass('error_field');
        $('#product_sale_sku_select_chosen').removeClass('error_field');
        $('input[name="end_date"]').removeClass('error_field');
        $('input[name="start_date"]').removeClass('error_field');
        $('input[name="end_date"]').closest('.input-group').removeClass('error_field');
        var start_date_mand = velovalidation.checkMandatory($('input[name="start_date"]'));
        if (start_date_mand != true) {
            error = true;
            $('input[name="start_date"]').addClass('error_field');
            $('input[name="start_date"]').closest('.input-group').after('<span class="error_message">' + start_date_mand + '</span>');
        }
        var end_date_mand = velovalidation.checkMandatory($('input[name="end_date"]'));
        if (end_date_mand != true) {
            error = true;
            $('input[name="end_date"]').addClass('error_field');
            $('input[name="end_date"]').closest('.input-group').after('<span class="error_message">' + end_date_mand + '</span>');
        } else {
            var start_date = Date.parse($('input[name="start_date"]').val());
            var end_date = Date.parse($('input[name="end_date"]').val());
            if (parseInt(end_date) < parseInt(start_date)) {
                error = true;
                $('input[name="end_date"]').closest('.input-group').addClass('error_field');
                $('input[name="end_date"]').closest('.input-group').after('<span class="error_message">' + end_date_error + '</span>');
            }
        }


        if ($('select[name="sku[]"]').val() == null) {
            error = true;
            $('#product_sale_sku_select_chosen').addClass('error_field');
            $('#product_sale_sku_select_chosen').after('<span class="error_message">' + sku_empty_error + '</span>');
        }

        if (error) {
            $('html, body').animate({
                scrollTop: $(".error_message").offset().top-200
            }, 1000);
            return false;
        }
        if (error) {
            return false;
        } else {
            $.ajax({
                url: module_path,
                data: 'skus='+$('select[name="sku[]"]').val()+"&start=" + $('input[name="start_date"]').val() + "&end=" + $('input[name="end_date"]').val() + '&groupby='+$('select[name="groupby"]').val()+'&ajax=true&getProductSaleReport=true',
                type: 'post',
                datatype: 'json',
                success: function (json)
                {
                    $('.productsalereporttable tbody').html('');
                    if (json.table != '') {
                         var content = '';
                        for (var i in json.table) {
                            content += '<tr>';
                            content += '<td>' + json.table[i]['sku'] + '</td>';
                            content += '<td>' + json.table[i]['count'] + '</td>';
                            content += '' + '<td>' + json.table[i]['total_product'] + '</td>';
                            content += '<td>' + json.table[i]['total'] + '</td>';
                            content += '</tr>';
                        }
                       $('.productsalereporttable tbody').html(content);
                    } else {
                        $('.productsalereporttable tbody').html('<tr><td colspan="4" style="text-align: center;">No order found.</td></tr>')

                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(technical_error);
                }
            });
        }

    });


});

function kbDrawChart(json)
{
    var data = [];
    $.each(json, function (key, orderrevenue) {
//        console.log(orderrevenue.total);
//        for (var i in orderrevenue) {
        var revenue = orderrevenue.total;
        var ordercount = orderrevenue.count;
        var label = orderrevenue.label;
        var obj = {x: label, y: ordercount, z: revenue};
        data.push(obj);

    });
    Morris.Bar({
        element: 'flot-placeholder',
        data: data,
        xkey: 'x',
        ykeys: ['y', 'z'],
        labels: [Order_label, Revenue_label],
        barColors: ['#2dd006', '#61b0f5']
    });
    $('#flot-placeholder').append(
        '<div class="legend" style="margin-top: 0px;margin-bottom: 51px;"><div style="position: absolute; width: 362px; height: 28px; top: 9px; right: 9px; background-color: rgb(255, 255, 255); opacity: 0.85;"> </div><table style="position:absolute;color:#545454"><tbody><tr><td class="legendColorBox"><div style="border:1px solid null;padding:1px"><div style="width:4px;height:0;border:5px solid #2dd006;overflow:hidden"></div></div></td><td class="legendLabel"> ' + Order_label + '</td><td class="legendColorBox"><div style="border:1px solid null;padding:1px"><div style="width:4px;height:0;border:5px solid #61b0f5;overflow:hidden"></div></div></td><td class="legendLabel"> ' + Revenue_label + '</td></tr></tbody></table></div>');
}


