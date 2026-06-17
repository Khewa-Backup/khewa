/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

$(document).ready(function () {
    var postUrl = controller_link;
    var selectedColumns;
    showFieldsFilterAlert = true;
    var error = false;
    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        if (!error) {
            showErrorMessage(dtableLoadError);
            error = true;
        }
    };

//    function controlSortingByProduct() {
//        if ($('input[type=radio][name=orders_merge]:checked').val() === '1') {
//            $('#data_export_orders_sort optgroup[label=' + optGroup.product + ']').prop('disabled', true);
//        } else {
//            $('#data_export_orders_sort optgroup[label=' + optGroup.product + ']').prop('disabled', false);
//        }
//    }
    function controlOrdersMerge() {

        if ([optGroup['product'], optGroup['category'], optGroup['manufacturer'], optGroup['supplier']].includes($('#data_export_orders_sort').find("option:selected").parent().attr('label'))) {
            $('input[name="orders_merge_helper"]').prop('disabled', true);
        } else {
            $('input[name="orders_merge_helper"]').prop('disabled', false);
        }
        if ([optGroup['product'], optGroup['category'], optGroup['manufacturer'], optGroup['supplier']].includes($('#data_export_orders_sort').find("option:selected").parent().attr('label')) ||
                $('[name="orders_merge_helper"]:checked').val() === '0') {
            $('#data_export_orders_merge').prop('checked', false);
            $('#data_export_orders_unmerge').prop('checked', true);
        } else {
            $('#data_export_orders_merge').prop('checked', true);
            $('#data_export_orders_unmerge').prop('checked', false);
        }
    }

    function triggerChanges() {
        $('input[name="orders_export_as"]').change();
        $('#data_export_orders_target_action').change();
        $('#data_export_orders_target_action_ftp_type').change();

        $('#data_export_orders_creation_date').change();
        $('#data_export_orders_invoice_date').change();
        $('#data_export_orders_delivery_date').change();
        $('#data_export_orders_payment_date').change();
        $('#data_export_orders_shipping_date').change();

//        $('#expand_data_filter').click();
    }

    var applySelected = (function applySelectedFunc() {
        selectedColumns = {};
        $('#data_export_orders_filter_fields .list-group a').each(function (index) {
            var key = $(this).attr('id').replace('orders_filter_fields_', '');
            var selectedItems = $('#data_export_orders_' + key + '_columns>li:not(.hidden)').find('i.icon-check-square-o');
            var unselectedItems = $('#data_export_orders_' + key + '_columns>li:not(.hidden)').find('i.icon-square-o');
            var totalItems = selectedItems.length + unselectedItems.length;
            $(this).children('span').text(selectedItems.length);
            selectedColumns[key] = {};
            if ($(this).hasClass('active')) {
                for (var i = 0; i < selectedItems.length; i++) {
                    var attr = $(selectedItems[i]).attr('data-value');
                    selectedColumns[key][attr] = $(selectedItems[i]).parent().clone().children().remove().end().text().trim();
                    $(selectedItems[i]).parent().css('background-color', '#f5f5f5');
                }
            } else {
                for (var i = 0; i < selectedItems.length; i++) {
                    var attr = $(selectedItems[i]).attr('data-value');
                    selectedColumns[key][attr] = $(selectedItems[i]).parent().clone().children().remove().end().text().trim();
                }
            }

            if ($(this).hasClass('active')) {
                for (var i = 0; i < unselectedItems.length; i++) {
                    $(unselectedItems[i]).parent().css('background-color', '#ffffff');
                }
                if (selectedItems.length === 0) {
                    $('#data_export_orders_select_all_columns').
                            find('i').
                            removeClass('icon-minus-square-o icon-check-square-o').
                            addClass('icon-square-o');
                } else if (selectedItems.length < totalItems) {
                    $('#data_export_orders_select_all_columns').
                            find('i').
                            removeClass('icon-square-o icon-check-square-o').
                            addClass('icon-minus-square-o');
                } else if (selectedItems.length === totalItems) {
                    $('#data_export_orders_select_all_columns').
                            find('i').
                            removeClass('icon-square-o icon-minus-square-o').
                            addClass('icon-check-square-o');
                }
            }
        });

//        controlSortingByProduct();

        $('#orders_selectedColumns').val(JSON.stringify(selectedColumns));
        return applySelectedFunc;

    })();

    function updateAutoexportSchedule(key, value) {
        $('#fader, #spinner').css('display', 'block');
        var autoexportData = {
            ajax_action: "updateAutoexportSchedule",
            type: 'dml',
            key: key,
            value: value
        };
        $.post(postUrl, autoexportData, function (result) {
            $('#fader, #spinner').css('display', 'none');
            var json = JSON.parse(result);
            if (json.type === 'success') {
                showSuccessMessage(json.message);
            } else {
                showErrorMessage(json.message);
            }
        });
    }

    var absentColumns = [
        'product_link',
        'product_image',
        'product_image_link',
        'attribute_image',
        'attribute_image_link',
        'category_link',
        'category_image',
        'category_image_link',
        'manufacturer_link',
        'manufacturer_image',
        'manufacturer_image_link',
        'supplier_link',
        'supplier_image',
        'supplier_image_link',
        'profit_amount',
        'profit_margin',
        'profit_percentage',
        'net_profit_amount',
        'net_profit_margin',
        'net_profit_percentage'
    ];

    var key, item, opt, selectedOptions;
    function fillSortingSelect() {
        selectedOptions = '';
        $('#data_export_orders_filter_fields .list-group a').each(function () {
            key = $(this).attr('id').replace('orders_filter_fields_', '');
            selectedOptions += '<optgroup label="' + optGroup[key] + '">';

            $('#data_export_orders_' + key + '_columns>li').find('i.icon-check-square-o').each(function () {
                item = $(this).attr('data-value');
                if (absentColumns.indexOf(item) === -1) {
                    opt = $(this).parent().clone().children().remove().end().text().trim();
                    selectedOptions += '<option value="'
                            + (item.indexOf('.') === -1 ? key + '.' + item : item)
                            + '">' + opt
                            + '</option>';
                }
            });
            selectedOptions += '</optgroup>';
        });
        $('#data_export_orders_sort').children('optgroup, option').remove().end().append(selectedOptions);
    }

    fillSortingSelect();
//    controlSortingByProduct();

    $('#data_export_orders_sort').change(function () {
        controlOrdersMerge();
    });

    var defaults = $('#data_export_form').serialize();
//    console.log(decodeURIComponent(defaults).replace(/\+/g, ' '));

    // Sub left list
    $('.data_export_filter_fields .list-group a').click(function (e) {
        e.preventDefault();
        var key = $(this).attr('id').replace('orders_filter_fields_', '');
        $(this).addClass('active').siblings().removeClass('active');
        $('#data_export_orders_' + key + '_columns').
                removeClass('hidden').siblings().filter(':not(p):not(legend)').addClass('hidden');
        $('#data_export_orders_filter_fields .col-xs-7 legend').html(keyColumns[key] + ':&nbsp;');
        var hiddenList = $('#data_export_orders_' + key + '_columns>li.hidden');
        if (hiddenList.length > 0) {
            $('#data_export_orders_show_all').html(show_all).attr('data-value', '0');
        } else {
            $('#data_export_orders_show_all').html(show_selected).attr('data-value', '1');
        }

        if ($('#data_export_orders_' + key + '_columns').hasClass('expanded')) {
            $('#data_export_orders_scroll_columns').removeClass('overflowable');
            $('#data_export_orders_expand_all > i').removeClass('icon-angle-right').addClass('icon-angle-down');
            $('#data_export_orders_expand_all > span').text(collapse);
        } else {
            $('#data_export_orders_scroll_columns').addClass('overflowable');
            $('#data_export_orders_expand_all > i').removeClass('icon-angle-down').addClass('icon-angle-right');
            $('#data_export_orders_expand_all > span').text(expand);
        }

        $('#data_export_orders_select_all_columns').blur();
        $('#data_export_orders_reset_columns').blur();
        $('#data_export_orders_show_all').blur();
        $('#data_export_orders_expand_all').blur();

        if (showFieldsFilterAlert) {
            $('#data_export_orders_filter_fields .alert.' + key)
                    .removeClass('hidden')
                    .siblings(':not(.row)')
                    .addClass('hidden');
        }
        applySelected();
    });

    $('.columns_group, .data_export_columns').sortable({
        cursor: "move",
        axis: "y",
        stop: function (event, ui) {
            ui.item.css('left', '');
            applySelected();
        }
    });

    $('.data_export_columns>li').click(function () {
        if ($(this).children('i').hasClass('icon-check-square-o')) {
            $(this).children('i').removeClass('icon-check-square-o').addClass('icon-square-o');
        } else if ($(this).children('i').hasClass('icon-square-o')) {
            $(this).children('i').removeClass('icon-square-o').addClass('icon-check-square-o');
        }
        fillSortingSelect();
        applySelected();
    });


    $('input[type=radio][name=orders_merge_helper]').change(function () {
        if ($(this).is(':checked')) {
            $('#data_export_orders_merge').prop('checked', parseInt($(this).val()));
            $('#data_export_orders_unmerge').prop('checked', !parseInt($(this).val()));
        }
    });

    $('#data_export_orders_autoexport_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export').collapse("show");
            updateAutoexportSchedule('OXSRP_AEXP_ENABLE', $('input[name="orders_autoexport"]:checked').val());
        }
    });
    $('#data_export_orders_autoexport_no').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export').collapse("hide");
            updateAutoexportSchedule('OXSRP_AEXP_ENABLE', $('input[name="orders_autoexport"]:checked').val());
        }
    });

    $('#data_export_orders_autoexport_use_email_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export-email').collapse("show");
            updateAutoexportSchedule('OXSRP_AEXP_USE_EMAIL', $('input[name="orders_autoexport_use_email"]:checked').val());
        }
    });
    $('#data_export_orders_autoexport_use_email_no').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export-email').collapse("hide");
            updateAutoexportSchedule('OXSRP_AEXP_USE_EMAIL', $('input[name="orders_autoexport_use_email"]:checked').val());
        }
    });

    $('#data_export_orders_autoexport_use_ftp_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export-ftp').collapse("show");
            updateAutoexportSchedule('OXSRP_AEXP_USE_FTP', $('input[name="orders_autoexport_use_ftp"]:checked').val());
        }
    });
    $('#data_export_orders_autoexport_use_ftp_no').change(function () {
        if ($(this).prop('checked')) {
            $('.auto-export-ftp').collapse("hide");
            updateAutoexportSchedule('OXSRP_AEXP_USE_FTP', $('input[name="orders_autoexport_use_ftp"]:checked').val());
        }
    });

    $('input[id^="data_export_orders_autoexport_order_state_yes_"]').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_AEXP_ON_WHAT', $.map($('input:radio[id^="data_export_orders_autoexport_order_state_yes"]'), function (val, i) {
                if ($(val).is(':checked')) {
                    return $(val).attr('data-id');
                }
            }).join(';'));
        }
    });
    $('input[id^="data_export_orders_autoexport_order_state_no_"]').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_AEXP_ON_WHAT', $.map($('input:radio[id^="data_export_orders_autoexport_order_state_yes"]'), function (val, i) {
                if ($(val).is(':checked')) {
                    return $(val).attr('data-id');
                }
            }).join(';'));
        }
    });

    $('#data_export_orders_autoexport_dont_send_empty_yes').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_AUTOEXP_DNSEM', $('input[name="orders_autoexport_dont_send_empty"]:checked').val());
        }
    });
    $('#data_export_orders_autoexport_dont_send_empty_no').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_AUTOEXP_DNSEM', $('input[name="orders_autoexport_dont_send_empty"]:checked').val());
        }
    });

    $('#data_export_orders_schedule_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule').collapse("show");
            updateAutoexportSchedule('OXSRP_SCHDL_ENABLE', $('input[name="orders_schedule"]:checked').val());
        }
    });
    $('#data_export_orders_schedule_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule').collapse("hide");
            updateAutoexportSchedule('OXSRP_SCHDL_ENABLE', $('input[name="orders_schedule"]:checked').val());
        }
    });

    $('#data_export_orders_schedule_use_email_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-email').collapse("show");
            updateAutoexportSchedule('OXSRP_SCHDL_USE_EMAIL', $('input[name="orders_schedule_use_email"]:checked').val());
        }
    });
    $('#data_export_orders_schedule_use_email_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-email').collapse("hide");
            updateAutoexportSchedule('OXSRP_SCHDL_USE_EMAIL', $('input[name="orders_schedule_use_email"]:checked').val());
        }
    });

    $('#data_export_orders_schedule_use_ftp_yes').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("show");
            updateAutoexportSchedule('OXSRP_SCHDL_USE_FTP', $('input[name="orders_schedule_use_ftp"]:checked').val());
        }
    });
    $('#data_export_orders_schedule_use_ftp_no').change(function () {
        if ($(this).prop('checked')) {
            $('.schedule-ftp').collapse("hide");
            updateAutoexportSchedule('OXSRP_SCHDL_USE_FTP', $('input[name="orders_schedule_use_ftp"]:checked').val());
        }
    });

    $('#data_export_orders_schedule_dont_send_empty_yes').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_SCHDL_DNSEM', $('input[name="orders_schedule_dont_send_empty"]:checked').val());
        }
    });
    $('#data_export_orders_schedule_dont_send_empty_no').change(function () {
        if ($(this).prop('checked')) {
            updateAutoexportSchedule('OXSRP_SCHDL_DNSEM', $('input[name="orders_schedule_dont_send_empty"]:checked').val());
        }
    });


    $('#data_export_orders_target_action').change(function () {
        if ($(this).val() === 'download') {
            $('.target_action_email').collapse("hide");
            $('.target_action_ftp').collapse("hide");
        } else if ($(this).val() === 'email') {
            $('.target_action_email').collapse("show");
            $('.target_action_ftp').collapse("hide");
        } else if ($(this).val() === 'ftp') {
            $('.target_action_email').collapse("hide");
            $('.target_action_ftp').collapse("show");
        }
    });

    $(".collapse").collapse({toggle: false});

    $('input[name="orders_export_as"]').change(function () {
        if ($(this).is(':checked')) {
//            controlSortingByProduct();
            controlOrdersMerge();
            if ($(this).val() === 'excel') {
                $('.file_type_icon').attr('class', 'icon-file-excel-o file_type_icon');
                $('.file_type_text').html('.xlsx');
//                $('.file_type_ext').val('.xlsx');
                $('.csv_options').collapse('hide');
            } else if ($(this).val() === 'csv') {
                $('.file_type_icon').attr('class', 'icon-file-text-o file_type_icon');
                $('.file_type_text').html('.csv');
//                $('.file_type_ext').val('.csv');
                $('.csv_options').collapse('show');
            } else if ($(this).val() === 'pdf') {
                $('.file_type_icon').attr('class', 'icon-file-pdf-o file_type_icon');
                $('.file_type_text').html('.pdf');
//                $('.file_type_ext').val('.pdf');
                $('.csv_options').collapse('hide');
            } else if ($(this).val() === 'html') {
                $('.file_type_icon').attr('class', 'icon-file-code-o file_type_icon');
                $('.file_type_text').html('.html');
//                $('.file_type_ext').val('.html');
                $('.csv_options').collapse('hide');
            }
        }
    });
    
    $('#data_export_orders_target_action_ftp_type').change(function () {
        if ($(this).val() === 'sftp') {
            $('.target_action_ftp_mode').collapse('hide');
        } else {
            $('.target_action_ftp_mode').collapse('show');
        }
    });
    
    $('#data_export_orders_autoexport_ftp_type').change(function () {
        if ($(this).val() === 'sftp') {
            $('.autoexport_ftp_mode').collapse('hide');
        } else {
            $('.autoexport_ftp_mode').collapse('show');
        }
    });
    
    $('#data_export_orders_schedule_ftp_type').change(function () {
        if ($(this).val() === 'sftp') {
            $('.schedule_ftp_mode').collapse('hide');
        } else {
            $('.schedule_ftp_mode').collapse('show');
        }
    });

    $('#columns_search').keyup(function (e) {
        var value = $(this).val().toLowerCase();
        if (value) {
            $('.clearable_clear').show();
        } else {
            $('.clearable_clear').hide();
        }

        $(".list-group.item-list.data_export_columns > li").each(function () {
            if ($(this).text().toLowerCase().search(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('.clearable_clear').click(function () {
        $('#columns_search').val('').trigger('keyup');
    });

    $('.data_export_filter_fields>.alert a.close').click(function (e) {
        showFieldsFilterAlert = false;
    });
    $('.data_export_select_all_columns').click(function (e) {
        e.preventDefault();
//        $(this).blur();
        if ($(this).find('i').hasClass('icon-check-square-o')) {
            $(this).find('i').removeClass('icon-check-square-o').addClass('icon-square-o');
            $('#data_export_orders_scroll_columns').find('ul:not(.hidden)>li:not(.hidden)').find('i').removeClass('icon-check-square-o').addClass('icon-square-o');
        } else if ($(this).find('i').hasClass('icon-square-o')) {
            $(this).find('i').removeClass('icon-square-o').addClass('icon-check-square-o');
            $('#data_export_orders_scroll_columns').find('ul:not(.hidden)>li:not(.hidden)').find('i').removeClass('icon-square-o').addClass('icon-check-square-o');
        } else if ($(this).find('i').hasClass('icon-minus-square-o')) {
            $(this).find('i').removeClass('icon-minus-square-o').addClass('icon-square-o');
            $('#data_export_orders_scroll_columns').find('ul:not(.hidden)>li:not(.hidden)').find('i').removeClass('icon-check-square-o').addClass('icon-square-o');
        }
        fillSortingSelect();
        applySelected();
    });
    $('.data_export_show_all').click(function (e) {
        e.preventDefault();
//        $(this).blur();
        if ($(this).attr('data-value') === '0') {
            $(this).attr('data-value', '1').html(show_selected);
            $('#data_export_orders_scroll_columns').children('ul:not(.hidden)').children('li.hidden').removeClass('hidden');
        } else if ($(this).attr('data-value') === '1') {
            $(this).attr('data-value', '0').html(show_all);
            $('#data_export_orders_scroll_columns').children('ul:not(.hidden)').find('i.icon-square-o').parent().addClass('hidden');
        }
        applySelected();
    });
    $('.data_export_reset_columns').click(function (e) {
        e.preventDefault();
//        $(this).blur();
        $('#data_export_orders_scroll_columns').children('ul:not(.hidden)').children('li:not(.advanced)').children('i').removeClass('icon-square-o').addClass('icon-check-square-o');
        $('#data_export_orders_scroll_columns').children('ul:not(.hidden)').children('li.advanced').children('i').removeClass('icon-check-square-o').addClass('icon-square-o');
        if ($('.data_export_show_all').attr('data-value') === '0') {
            $('#data_export_orders_scroll_columns')
                    .children('ul:not(.hidden)')
                    .children('li:not(.advanced)')
                    .removeClass('hidden');
            $('#data_export_orders_scroll_columns')
                    .children('ul:not(.hidden)')
                    .children('li.advanced')
                    .addClass('hidden');
        }
        fillSortingSelect();
        applySelected();
    });
    $('#data_export_orders_expand_all').click(function (e) {
        e.preventDefault();
        if ($(this).children('i').hasClass('icon-angle-right')) {
            $(this).children('i').removeClass('icon-angle-right').addClass('icon-angle-down');
            $(this).children('span').text(collapse);
            $('#data_export_orders_scroll_columns').removeClass('overflowable');
            $('#data_export_orders_scroll_columns > ul:not(.hidden)').addClass('expanded');
//            $('#data_export_orders_scroll_columns').css('padding-right', '5px');
        } else {
            $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-right');
            $(this).children('span').text(expand);
            $('#data_export_orders_scroll_columns').addClass('overflowable');
            $('#data_export_orders_scroll_columns > ul:not(.hidden)').removeClass('expanded');
//            $('#data_export_orders_scroll_columns').css('padding-right', '10px');
        }
    });

    $('#data_export_orders_from_date, #data_export_orders_to_date, \n\
    #data_export_orders_invoice_from_date, #data_export_orders_invoice_to_date, \n\
    #data_export_orders_invoice_add_from_date, #data_export_orders_invoice_add_to_date, \n\
    #data_export_orders_delivery_from_date, #data_export_orders_delivery_to_date, \n\
    #data_export_orders_payment_from_date, #data_export_orders_payment_to_date, \n\
    #data_export_orders_shipping_from_date, #data_export_orders_shipping_to_date').datetimepicker({
        dateFormat: "yy-mm-dd",
        timeFormat: "hh:mm:ss",
        showSecond: true
    });
//    $.datepicker.setDefaults($.datepicker.regional["fr"]);
//    var date = new Date();
//    $('#data_export_orders_to_date').datepicker('setDate', date);
//    date.setMonth(date.getMonth() - 1);
//    $('#data_export_orders_from_date').datepicker('setDate', date);

    $('#data_export_form').submit(function (e) {

        e.preventDefault();

        collectShops();
        collectGroups();
        collectCustomers();
        collectOrders();
        collectOrderStates();
        collectCartRules();
        collcetCarriers();
        collectManufacturers();
        collectSuppliers();
        collectAttributes();
        collectFeatures();
        collectPaymentMethods();
        collectCountries();
        collectCurrencies();
        collectProducts();

        var form = $(this);
//        var url = form.attr('action').replace('index.php', 'ajax-tab.php');
        var url = location.href.replace('index.php', 'ajax-tab.php');
        var postData = {};
        $('#data_export_orders_target_action_to_emails').val($('#data_export_orders_target_action_to_emails').tagify('serialize'));
        var request = {
            type: "POST",
            url: url,
            dataType: "json",
            data: form.serialize(), // serializes the form's elements.
            success: function (data)
            {
                $('#fader, #spinner').css('display', 'none');
                if (data) {
                    if (data.type === 'pdf' || data.type === 'html') {
                        open(controller_link + '&action=getFile&id=' + data.id + '&type=' + data.type + '&name=' + data.name, '_blank');
                    } else if (data.type === 'excel' || data.type === 'csv') {
                        location.replace(controller_link + '&action=getFile&id=' + data.id + '&type=' + data.type + '&name=' + data.name);
                    }
                }
            },
            error: function (data) {
                $('#fader, #spinner').css('display', 'none');
                if (data.statusText === 'timeout') {
                    $('#emailModal').modal('show');
                    $.ajax({
                        type: "POST",
                        url: url,
                        dataType: "json",
                        data: postData,
                        success: function (data)
                        {
                            if (data.status === 'error') {
                                $('#emailModal2').modal('show');
                            }
                        }
                    });
                } else if (data.statusText === 'error') {
                    $('#emailModal3').modal('show');
                }
            }
        };

        if ($('#data_export_orders_target_action').val() === 'email') {
            var to_emails = $('#data_export_orders_target_action_to_emails').tagify('serialize');
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{1,})+$/;
            for (let elem of to_emails.split(";")) {
                if (!regex.test(elem)) {
                    alert(invalid_receiver_emails);
                    return;
                }
            }
            $('#emailModal .modal-body p').html(target_email_ftp);
            $('#emailModal').modal('show');
            var req = $.ajax(request);
            setTimeout(function () {
                req.abort();
            }, 1500);
        } else if ($('#data_export_orders_target_action').val() === 'ftp') {
            var ftp_url = $('#data_export_orders_target_action_ftp_url').val();

            if (!ftp_url) {
                alert(empty_ftp_url);
                return;
            }
            $('#emailModal .modal-body p').html(target_email_ftp);
            $('#emailModal').modal('show');
            var req = $.ajax(request);
            setTimeout(function () {
                req.abort();
            }, 1500);
        } else {
            $.ajax(request);
            $('#fader, #spinner').css('display', 'block');
        }

    });

    $('.data_export_form_save_btn').click(function (e) {
        e.preventDefault();
//        var config = $('#data_export_form').serialize();
        var name = $('#data_export_orders_settings_name').val();
        if (!name) {
            return alert(invalid_settings_mame);
        }
        var myform = $('#data_export_form');
        // Find disabled inputs, and remove the "disabled" attribute
        var disabled = myform.find(':input:disabled').prop('disabled', false);
        // serialize the form
        var serialized = myform.serialize();
        // re-disabled the set of inputs that you previously enabled
        disabled.prop('disabled', true);
        var config = decodeURIComponent(serialized).replace(/\+/g, ' ');

        config += '&target_action_to_emails=' + $('#data_export_orders_target_action_to_emails').tagify('serialize');

        // As this takes more space
        delete featuresTable.state().checkboxes;
        
        var dataTables = JSON.stringify({
            shops: {type: shops.type, data: shops.data, state: shopsTable.state()},
            groups: {type: groups.type, data: groups.data, state: groupsTable.state()},
            customers: {type: customers.type, data: customers.data, state: customersTable.state()},
            orders: {type: orders.type, data: orders.data, state: ordersTable.state()},
            orderStates: {type: orderStates.type, data: orderStates.data, state: orderStatesTable.state()},
            cartRules: {type: cartRules.type, data: cartRules.data, state: cartRulesTable.state()},
            manufacturers: {type: manufacturers.type, data: manufacturers.data, state: manufacturersTable.state()},
            suppliers: {type: suppliers.type, data: suppliers.data, state: suppliersTable.state()},
            attributes: {type: attributes.type, data: attributes.data, state: attributesTable.state()},
            features: {type: features.type, data: features.data, state: featuresTable.state()},
            carriers: {type: carriers.type, data: carriers.data, state: carriersTable.state()},
            paymentMethods: {type: paymentMethods.type, data: paymentMethods.data, state: paymentMethodsTable.state()},
            countries: {type: countries.type, data: countries.data, state: countriesTable.state()},
            currencies: {type: currencies.type, data: currencies.data, state: currenciesTable.state()},
            products: {type: products.type, data: products.data, state: productsTable.state()}
        });
//        console.log(attributesTable.state());
//        return console.log(featuresTable.state());
        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveConfig', type: 'dml', name: name, config: config, datatables: dataTables},
                function (result) {
                    var json = JSON.parse(result);

                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                    if (json.configs) {
                        var html = '', html2 = '<option value="orders_default">' + default_setting + '</option>';
                        $.each(json.configs, function (index, value) {
                            html += '<li data-id="' + value.id_orders_export_srpro
                                    + '" data-name=\'' + value.name
                                    + '\' data-config=\'' + value.configuration
                                    + '\' data-datatables=\'' + value.datatables + '\' class="list-group-item"><b>' +
                                    value.name + '</b><span class="pull-right">'
                                    + '<button class="btn btn-default apply_config">' + apply + '</button> | <i title="' + value.title
                                    + '" class="icon-trash" style="color:#c50000" aria-hidden="true"></i></span></li>';

                            html2 += '<option value="' + value.name + '">' + value.name + '</option>';

                        });
                        $('#configs').html(html);
                        $('#data_export_orders_autoexport_ftp_setting').html(html2);
                        $('#data_export_orders_schedule_ftp_setting').html(html2);
                        $('#data_export_orders_autoexport_email_setting').html(html2);
                        $('#data_export_orders_schedule_email_setting').html(html2);
                        $('#data_export_orders_settings_name').val('');
                    }
                    $('#fader, #spinner').css('display', 'none');
                });
    });
    $(document).on('click', '#configs i.icon-trash', function (e) {
        if (confirm(will_be_changed_to_default)) {
            $('#fader, #spinner').css('display', 'block');
            $.post(postUrl, {ajax_action: 'deleteConfig', type: 'dml', id: $(this).parent().parent().attr('data-id')},
                    function (result) {
                        var json = JSON.parse(result);

                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                        if (json.configs) {
                            var html = '', html2 = '<option value="orders_default">' + default_setting + '</option>';
                            $.each(json.configs, function (index, value) {
                                html += '<li data-id="' + value.id_orders_export_srpro
                                        + '" data-name=\'' + value.name
                                        + '\' data-config=\'' + value.configuration
                                        + '\' data-datatables=\'' + value.datatables + '\' class="list-group-item"><b>' +
                                        value.name + '</b><span class="pull-right">'
                                        + '<button class="btn btn-default apply_config">' + apply + '</button> | <i title="' + value.title
                                        + '" class="icon-trash" style="color:#c50000" aria-hidden="true"></i></span></li>';

                                html2 += '<option value="' + value.name + '">' + value.name + '</option>';

                            });
                            if (json.configs.length === 0) {
                                html = '<span style="color: #999;"><i class="icon-warning-sign"></i> ' + no_saved_setting + '</span>';
                            }
                            $('#configs').html(html);
                            $('#data_export_orders_autoexport_ftp_setting').html(html2);
                            $('#data_export_orders_schedule_ftp_setting').html(html2);
                            $('#data_export_orders_autoexport_email_setting').html(html2);
                            $('#data_export_orders_schedule_email_setting').html(html2);
                        }
                        $('#fader, #spinner').css('display', 'none');
                    });
        }
    });
    $(document).on('click', '.apply_config', function (e) {
        e.preventDefault();
        $('#fader, #spinner').css('display', 'block');
        uncheckAllAssociatedCategories($('#data_export_orders_categories_tree'));
        var config = $(this).parent().parent().attr('data-config');
        var pairs = (config[0] === '?' ? config.substr(1) : config).split('&');
        var $elem;
        for (var i = 0; i < pairs.length; i++) {
            var pair = pairs[i].split('=');
            if (pair[0] === 'orders_selectedColumns') {
                $('[name="' + pair[0] + '"]').val(pair[1]);
                $('#data_export_orders_scroll_columns')
                        .find('li.advanced')
                        .removeClass('hidden');
                $('#data_export_orders_show_all').attr('data-value', '1').html(show_selected);
                $.each(JSON.parse(pair[1]), function (key, value) {

                    $('#orders_columns_group').append($('#orders_filter_fields_' + key));

                    var selectedCount = 0, sortedList = [];
                    var values = Object.keys(value);
                    var list = $('#data_export_orders_' + key + '_columns').children('li');

                    for (var i = 0; i < list.length; i++) {
                        if (values.indexOf($(list[i]).children('i').attr('data-value')) === -1) {
                            sortedList[i] = $(list[i])
                                    .children('i')
                                    .removeClass('icon-check-square-o')
                                    .addClass('icon-square-o')
                                    .parent()
                                    .css('background-color', '#ffffff');
                        } else {
                            sortedList[i] = $('#data_export_orders_' + key + '_columns')
                                    .find('i[data-value="' + values[selectedCount] + '"]')
                                    .removeClass('icon-square-o')
                                    .addClass('icon-check-square-o')
                                    .parent()
                                    .css('background-color', '#f5f5f5');
                            selectedCount++;
                        }
                    }

                    $('#data_export_orders_' + key + '_columns').append(sortedList);
                    $('#orders_filter_fields_' + key).children('span').text(selectedCount);

                    if ($('#orders_filter_fields_' + key).hasClass('active')) {
                        if (selectedCount === 0) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-minus-square-o icon-check-square-o').
                                    addClass('icon-square-o');
                        } else if (selectedCount < list.length) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-square-o icon-check-square-o').
                                    addClass('icon-minus-square-o');
                        } else if (selectedCount === list.length) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-square-o icon-minus-square-o').
                                    addClass('icon-check-square-o');
                        }
                    }
                });
                fillSortingSelect();
            } else if ($('[name="' + pair[0] + '"]').attr('type') === 'radio') {
                if (!pair[0].includes('autoexport') && !pair[0].includes('schedule')) {
                    $('input[name="' + pair[0] + '"]').prop('checked', false);
                    $('input[name="' + pair[0] + '"][value="' + pair[1] + '"]').prop('checked', true);
                }
//                $('input[name="' + pair[0] + '"]').change();
            } else if (pair[0] === 'products_categories[]') {
                $elem = $('input[name="' + pair[0] + '"][value="' + pair[1] + '"]');
                $elem.prop("checked", true);
                $elem.parent().addClass("tree-selected");
            } else if (pair[0] === 'target_action_to_emails') {
                $('input[name="' + pair[0] + '"]').val(pair[1]);
                $('input[name="' + pair[0] + '"]').tagify('destroy');
                $('input[name="' + pair[0] + '"]').tagify({
                    delimiters: [13, 32, 188, 190],
                    duplicates: false,
                    addTagOnBlur: true,
                    placeholder: '',
                    addTagPrompt: add_email2,
                    outputDelimiter: ';'
                });
            } else {
                $('[name="' + pair[0] + '"]:input').val(pair[1]);
            }
        }

        var datatables = JSON.parse($(this).parent().parent().attr('data-datatables'));
//        console.log(datatables);
        for (var prop in datatables) {
            eval(prop).type = datatables[prop].type;
            eval(prop).data = datatables[prop].data;
            if (datatables[prop].state) {
                eval(prop + 'Table').page.len(datatables[prop].state.length).search(datatables[prop].state.search.search).order(datatables[prop].state.order).page(datatables[prop].state.start / datatables[prop].state.length).draw(false);
            }
            if (eval(prop).data.length !== 0 && parseInt(eval(prop).total) !== eval(prop).data.length) {
                $('#' + prop + '_table thead .dt-checkboxes').prop('indeterminate', true);
            }
        }
        triggerChanges();
        $('#fader, #spinner').css('display', 'none');
        showSuccessMessage(settings_applied);
    });

    $(document).on('click', '#data_export_orders_reset_settings', function (e) {
        e.preventDefault();
        $('#fader, #spinner').css('display', 'block');
        var config = decodeURIComponent(defaults).replace(/\+/g, ' ');

        var pairs = (config[0] === '?' ? config.substr(1) : config).split('&');
        for (var i = 0; i < pairs.length; i++) {
            var pair = pairs[i].split('=');
            if (pair[0] === 'orders_selectedColumns') {
                $('[name="' + pair[0] + '"]').val(pair[1]);
                $('#data_export_orders_scroll_columns')
                        .find('li.advanced')
                        .removeClass('hidden');
                $('#data_export_orders_show_all').attr('data-value', '1').html(show_selected);
                $.each(JSON.parse(pair[1]), function (key, value) {

                    $('#orders_columns_group').append($('#orders_filter_fields_' + key));

                    var selectedCount = 0, sortedList = [];
                    var values = Object.keys(value);
                    var list = $('#data_export_orders_' + key + '_columns').children('li');

                    for (var i = 0; i < list.length; i++) {
                        if (values.indexOf($(list[i]).children('i').attr('data-value')) === -1) {
                            sortedList[i] = $(list[i])
                                    .children('i')
                                    .removeClass('icon-check-square-o')
                                    .addClass('icon-square-o')
                                    .parent()
                                    .css('background-color', '#ffffff');
                        } else {
                            sortedList[i] = $('#data_export_orders_' + key + '_columns')
                                    .find('i[data-value="' + values[selectedCount] + '"]')
                                    .removeClass('icon-square-o')
                                    .addClass('icon-check-square-o')
                                    .parent()
                                    .css('background-color', '#f5f5f5');
                            selectedCount++;
                        }
                    }

                    $('#data_export_orders_' + key + '_columns').append(sortedList);
                    $('#orders_filter_fields_' + key).children('span').text(selectedCount);

                    if ($('#orders_filter_fields_' + key).hasClass('active')) {
                        if (selectedCount === 0) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-minus-square-o icon-check-square-o').
                                    addClass('icon-square-o');
                        } else if (selectedCount < list.length) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-square-o icon-check-square-o').
                                    addClass('icon-minus-square-o');
                        } else if (selectedCount === list.length) {
                            $('#data_export_orders_select_all_columns').
                                    find('i').
                                    removeClass('icon-square-o icon-minus-square-o').
                                    addClass('icon-check-square-o');
                        }
                    }
                });
            } else if ($('[name="' + pair[0] + '"]').attr('type') === 'radio') {
                if (!pair[0].includes('autoexport') && !pair[0].includes('schedule')) {
                    $('input[name="' + pair[0] + '"]').prop('checked', false);
                    $('input[name="' + pair[0] + '"][value="' + pair[1] + '"]').prop('checked', true);
                }
//                $('input[name="' + pair[0] + '"]').change();
            } else {
                $('[name="' + pair[0] + '"]:input').val(pair[1]);
//                $('input[name="' + pair[0] + '"]').change();
            }
        }

        $('#data_export_orders_target_action_to_emails').tagify({
            delimiters: [13, 32, 188, 190],
            duplicates: false,
            addTagOnBlur: true,
            placeholder: '',
            addTagPrompt: add_email2,
            outputDelimiter: ';'
        });

        groups = {type: "unselected", data: [], total: 0, filtered: 0};
        groupsTable.draw();
        customers = {type: "unselected", data: [], total: 0, filtered: 0};
        customersTable.draw();
        orders = {type: "unselected", data: [], total: 0, filtered: 0};
        ordersTable.draw();
        orderStates = {type: "unselected", data: [], total: 0, filtered: 0};
        orderStatesTable.draw();
        cartRules = {type: "unselected", data: [], total: 0, filtered: 0};
        cartRulesTable.draw();
        paymentMethods = {type: "unselected", data: [], total: 0, filtered: 0};
        paymentMethodsTable.draw();
        manufacturers = {type: "unselected", data: [], total: 0, filtered: 0};
        manufacturersTable.draw();
        suppliers = {type: "unselected", data: [], total: 0, filtered: 0};
        suppliersTable.draw();
        attributes = {type: "unselected", data: [], total: 0, filtered: 0};
        attributesTable.draw();
        features = {type: "unselected", data: [], total: 0, filtered: 0};
        featuresTable.draw();
        carriers = {type: "unselected", data: [], total: 0, filtered: 0};
        carriersTable.draw();
        shops = {type: "unselected", data: [], total: 0, filtered: 0};
        shopsTable.draw();
        countries = {type: "unselected", data: [], total: 0, filtered: 0};
        countriesTable.draw();
        currencies = {type: "unselected", data: [], total: 0, filtered: 0};
        currenciesTable.draw();
        products = {type: "unselected", data: [], total: 0, filtered: 0};
        productsTable.draw();

        triggerChanges();
        checkAllAssociatedCategories($('#data_export_orders_categories_tree'));

        $('#expand_data_filter').children('i').removeClass('icon-angle-down').addClass('icon-angle-right');
        $('#data_export_orders_filter_data h3:not(:first-of-type) > i.pull-right').attr('class', 'icon-chevron-right pull-right');
        $('#data_export_orders_filter_data h3:not(:first-of-type) + div').collapse('hide');
        $('#data_export_orders_filter_data h3:first-of-type > i.pull-right').attr('class', 'icon-chevron-down pull-right');
        $('#data_export_orders_filter_data h3:first-of-type + div').collapse('show');
        $('#expand_data_filter').children('span').text(expand_all);

        $('#fader, #spinner').css('display', 'none');
        showSuccessMessage(settings_reset);
    });

    $('#data_export_orders_autoexport_reset').click(function (e) {
        e.preventDefault();
//        $('#data_export_orders_autoexport_setting').val('1');
//        $('#data_export_orders_autoexport_setting').multiselect('rebuild');
        $('#data_export_orders_autoexport_new_yes').prop('checked', false);
        $('#data_export_orders_autoexport_new_no').prop('checked', true);
        $('input[id^="data_export_orders_autoexport_order_state_yes"]').prop('checked', false);
        $('input[id^="data_export_orders_autoexport_order_state_no"]').prop('checked', true);
        $('#data_export_orders_autoexport_order_state_no_0').prop('checked', false);
        $('#data_export_orders_autoexport_order_state_yes_0').prop('checked', true);
        updateAutoexportSchedule('OXSRP_AEXP_ON_WHAT', $.map($('input:radio[id^="data_export_orders_autoexport_order_state_yes"]'), function (val, i) {
            if ($(val).is(':checked')) {
                return $(val).attr('data-id');
            }
        }).join(';'));
    });

    $('#data_export_orders_target_action_to_emails').tagify({
        delimiters: [13, 32, 188, 190],
        duplicates: false,
        addTagOnBlur: true,
        placeholder: '',
        addTagPrompt: add_email2,
        outputDelimiter: ';'
    });

    $('.refresh_button').click(function (e) {
        e.preventDefault();
        var table = $(this).attr('id').substr(8);
        eval(table + 'Table').ajax.reload();
    });

    if (typeof setGroupsTable !== 'undefined') {

        function updateGroupsSelectInfo() {
            $('#groups_select_info').text(groups.type === 'selected' ? groups.data.length + ' rows selected' : groups.total - groups.data.length + ' rows selected');

        }

        var groups = {type: "unselected", data: [], total: 0, filtered: 0};
        var allGroupsClicked = false;

        var groupsTable = $('#groups_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getGroups';
                    d.extra_search_type = $('#ctrl-show-selected-groups').val();
                    d.extra_search_params = {type: groups.type, data: groups.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "reduction"},
                {data: "members"},
                {data: "show_prices"},
                {data: "date_add"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#groups_select_info').length === 0) {
                    $('#groups_table_info').append('<span id="groups_select_info" class="select_info"></span>');
                }
                updateGroupsSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (groups.type === 'unselected') {
                                    index = groups.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            groups.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            groups.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = groups.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            groups.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {
                                        if (index > -1) {
                                            groups.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateGroupsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allGroupsClicked) {
                                if (selected) {
                                    groups.type = 'unselected';
                                } else {
                                    groups.type = 'selected';
                                }
                                groups.data = [];
                                allGroupsClicked = false;
                            }
                            updateGroupsSelectInfo();

                            if (!indeterminate && groups.data.length !== 0 && parseInt(groups.total) !== groups.data.length) {
                                $('#groups_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (groups.type === 'unselected') {

                            if (groups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (groups.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
//                    width: "220px",
                    render: function (data, type, row, meta) {
                        return data + '%';
                    }
                },
                {
                    targets: 4,
                    className: "dt-center"

                },
                {
                    targets: 5,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        groupsTable.on('xhr', function () {
            var json = groupsTable.ajax.json();
            groups.total = json.recordsTotal;
        });

        $('#groups_table th.dt-checkboxes-select-all').click(function () {
            allGroupsClicked = true;
        });
        $('#ctrl-show-selected-groups').on('change', function () {
            groupsTable.ajax.reload();
        });

        var collectGroups = function () {
            $('input[name="groups_type"]').remove();
            $('input[name="groups_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups_type").val(groups.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "groups_data").val(groups.data));
        };
    }

    if (typeof setCustomersTable !== 'undefined') {

        function updateCustomersSelectInfo() {
            $('#customers_select_info').text(customers.type === 'selected' ? customers.data.length + ' rows selected' : customers.total - customers.data.length + ' rows selected');

        }

        var customers = {type: "unselected", data: [], total: 0, filtered: 0};
        var allCustomersClicked = false;

        var customersTable = $('#customers_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCustomers';
                    d.extra_search_type = $('#ctrl-show-selected-customers').val();
                    d.extra_search_params = {type: customers.type, data: customers.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "gender"},
                {data: "firstname"},
                {data: "lastname"},
                {data: "email"},
                {data: "group"},
                {data: "enabled"},
                {data: "newsletter"},
//                {data: "deleted"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#customers_select_info').length === 0) {
                    $('#customers_table_info').append('<span id="customers_select_info" class="select_info"></span>');
                }
                updateCustomersSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (customers.type === 'unselected') {
                                    index = customers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            customers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            customers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = customers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            customers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            customers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCustomersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCustomersClicked) {
                                if (selected) {
                                    customers.type = 'unselected';
                                } else {
                                    customers.type = 'selected';
                                }
                                customers.data = [];
                                allCustomersClicked = false;
                            }
                            updateCustomersSelectInfo();

                            if (!indeterminate && customers.data.length !== 0 && parseInt(customers.total) !== customers.data.length) {
                                $('#customers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (customers.type === 'unselected') {

                            if (customers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (customers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 5,
                    width: "220px"
                },
                {
                    targets: 7,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                },
                {
                    targets: 8,
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        customersTable.on('xhr', function () {
            var json = customersTable.ajax.json();
            customers.total = json.recordsTotal;
        });

        $('#customers_table th.dt-checkboxes-select-all').click(function () {
            allCustomersClicked = true;
        });
        $('#ctrl-show-selected-customers').on('change', function () {
            customersTable.ajax.reload();
        });

        var collectCustomers = function () {
            $('input[name="customers_type"]').remove();
            $('input[name="customers_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "customers_type").val(customers.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "customers_data").val(customers.data));
        };
    }


    if (typeof setOrdersTable !== 'undefined') {

        function updateOrdersSelectInfo() {
            $('#orders_select_info').text(orders.type === 'selected' ? orders.data.length + ' rows selected' : orders.total - orders.data.length + ' rows selected');

        }

        var orders = {type: "unselected", data: [], total: 0, filtered: 0};
        var allOrdersClicked = false;

        var ordersTable = $('#orders_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            serverSide: true,
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getOrders';
                    d.extra_search_type = $('#ctrl-show-selected-orders').val();
                    d.extra_search_params = {type: orders.type, data: orders.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "invoice_number"},
                {data: "reference"},
                {data: "new_client"},
                {data: "delivery_country"},
                {data: "customer"},
                {data: "total_paid"},
                {data: "payment"},
                {data: "date_add"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#orders_select_info').length === 0) {
                    $('#orders_table_info').append('<span id="orders_select_info" class="select_info"></span>');
                }
                updateOrdersSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (orders.type === 'unselected') {
                                    index = orders.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            orders.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            orders.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = orders.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            orders.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            orders.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateOrdersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allOrdersClicked) {
                                if (selected) {
                                    orders.type = 'unselected';
                                } else {
                                    orders.type = 'selected';
                                }
                                orders.data = [];
                                allOrdersClicked = false;
                            }
                            updateOrdersSelectInfo();

                            if (!indeterminate && orders.data.length !== 0 && parseInt(orders.total) !== orders.data.length) {
                                $('#orders_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (orders.type === 'unselected') {

                            if (orders.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (orders.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        ordersTable.on('xhr', function () {
            var json = ordersTable.ajax.json();
            orders.total = json.recordsTotal;
        });

        $('#orders_table th.dt-checkboxes-select-all').click(function () {
            allOrdersClicked = true;
        });
        $('#ctrl-show-selected-orders').on('change', function () {
            ordersTable.ajax.reload();
        });

        var collectOrders = function () {
            $('input[name="orders_type"]').remove();
            $('input[name="orders_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "orders_type").val(orders.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "orders_data").val(orders.data));
        };
    }


    if (typeof setOrderStatesTable !== 'undefined') {

        function updateOrderStatesSelectInfo() {
            $('#orderStates_select_info').text(orderStates.type === 'selected' ? orderStates.data.length + ' rows selected' : orderStates.total - orderStates.data.length + ' rows selected');

        }

        var orderStates = {type: "unselected", data: [], total: 0, filtered: 0};
        var allOrderStatesClicked = false;

        var orderStatesTable = $('#orderStates_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            serverSide: true,
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getOrderStates';
                    d.extra_search_type = $('#ctrl-show-selected-orderStates').val();
                    d.extra_search_params = {type: orderStates.type, data: orderStates.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "icon"},
                {data: "send_email"},
                {data: "delivery"},
                {data: "invoice"},
                {data: "template"},
//                {data: "deleted"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#orderStates_select_info').length === 0) {
                    $('#orderStates_table_info').append('<span id="orderStates_select_info" class="select_info"></span>');
                }
                updateOrderStatesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (orderStates.type === 'unselected') {
                                    index = orderStates.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            orderStates.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            orderStates.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = orderStates.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            orderStates.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            orderStates.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateOrderStatesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allOrderStatesClicked) {
                                if (selected) {
                                    orderStates.type = 'unselected';
                                } else {
                                    orderStates.type = 'selected';
                                }
                                orderStates.data = [];
                                allOrderStatesClicked = false;
                            }
                            updateOrderStatesSelectInfo();

                            if (!indeterminate && orderStates.data.length !== 0 && parseInt(orderStates.total) !== orderStates.data.length) {
                                $('#orderStates_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (orderStates.type === 'unselected') {

                            if (orderStates.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (orderStates.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "50%",
                    render: function (data, type, row, meta) {
                        return '<span class="label color_field" style="background-color:' + row.color + ';color:' + row.font_color + ';">' + data + '</span>';
                    }
                },
                {
                    targets: 3,
                    width: "30px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                },
                {
                    targets: 5,
                    className: "dt-center",
                    width: "80px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "80px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                },
                {
                    targets: 7,
                    width: "130px"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        orderStatesTable.on('xhr', function () {
            var json = orderStatesTable.ajax.json();
            orderStates.total = json.recordsTotal;
        });

        $('#orderStates_table th.dt-checkboxes-select-all').click(function () {
            allOrderStatesClicked = true;
        });
        $('#ctrl-show-selected-orderStates').on('change', function () {
            orderStatesTable.ajax.reload();
        });

        var collectOrderStates = function () {
            $('input[name="order_states_type"]').remove();
            $('input[name="order_states_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "order_states_type").val(orderStates.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "order_states_data").val(orderStates.data));
        };
    }

    if (typeof setCartRulesTable !== 'undefined') {

        function updateCartRulesSelectInfo() {
            $('#cartRules_select_info').text(cartRules.type === 'selected' ? cartRules.data.length + ' rows selected' : cartRules.total - cartRules.data.length + ' rows selected');

        }

        var cartRules = {type: "unselected", data: [], total: 0, filtered: 0};
        var allCartRulesClicked = false;

        var cartRulesTable = $('#cartRules_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            serverSide: true,
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCartRules';
                    d.extra_search_type = $('#ctrl-show-selected-cartRules').val();
                    d.extra_search_params = {type: cartRules.type, data: cartRules.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "priority"},
                {data: "code"},
                {data: "quantity"},
                {data: "date_to"},
                {data: "active"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#cartRules_select_info').length === 0) {
                    $('#cartRules_table_info').append('<span id="cartRules_select_info" class="select_info"></span>');
                }
                updateCartRulesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "50px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (cartRules.type === 'unselected') {
                                    index = cartRules.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            cartRules.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            cartRules.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = cartRules.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            cartRules.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            cartRules.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCartRulesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCartRulesClicked) {
                                if (selected) {
                                    cartRules.type = 'unselected';
                                } else {
                                    cartRules.type = 'selected';
                                }
                                cartRules.data = [];
                                allCartRulesClicked = false;
                            }
                            updateCartRulesSelectInfo();

                            if (!indeterminate && cartRules.data.length !== 0 && parseInt(cartRules.total) !== cartRules.data.length) {
                                $('#cartRules_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (cartRules.type === 'unselected') {

                            if (cartRules.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (cartRules.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "60px"
                },
                {
                    targets: 3,
                    width: "80px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 5,
                    width: "80px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 7,
                    className: "dt-center",
                    width: "80px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        cartRulesTable.on('xhr', function () {
            var json = cartRulesTable.ajax.json();
            cartRules.total = json.recordsTotal;
        });

        $('#cartRules_table th.dt-checkboxes-select-all').click(function () {
            allCartRulesClicked = true;
        });
        $('#ctrl-show-selected-cartRules').on('change', function () {
            cartRulesTable.ajax.reload();
        });

        var collectCartRules = function () {
            $('input[name="cart_rules_type"]').remove();
            $('input[name="cart_rules_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "cart_rules_type").val(cartRules.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "cart_rules_data").val(cartRules.data));
        };
    }

    if (typeof setProductsTable !== 'undefined') {

        function updateProductsSelectInfo() {
            $('#products_select_info').text(products.type === 'selected' ? products.data.length + ' rows selected' : products.total - products.data.length + ' rows selected');

        }

        var products = {type: "unselected", data: [], total: 0, filtered: 0};
        var allProductsClicked = false;

        var productsTable = $('#products_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            serverSide: true,
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getProducts';
                    d.extra_search_type = $('#ctrl-show-selected-products').val();
                    d.extra_search_params = {type: products.type, data: products.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "image"},
                {data: "name"},
                {data: "reference"},
                {data: "category"},
                {data: "base_price"},
                {data: "final_price"},
                {data: "quantity"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#products_select_info').length === 0) {
                    $('#products_table_info').append('<span id="products_select_info" class="select_info"></span>');
                }
                updateProductsSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "dt-center",
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (products.type === 'unselected') {
                                    index = products.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            products.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            products.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = products.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            products.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            products.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateProductsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allProductsClicked) {
                                if (selected) {
                                    products.type = 'unselected';
                                } else {
                                    products.type = 'selected';
                                }
                                products.data = [];
                                allProductsClicked = false;
                            }
                            updateProductsSelectInfo();

                            if (!indeterminate && products.data.length !== 0 && parseInt(products.total) !== products.data.length) {
                                $('#products_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (products.type === 'unselected') {

                            if (products.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (products.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
                    width: "80px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center"
                },
                {
                    targets: 7,
                    className: "dt-center"
                },
                {
                    targets: 8,
                    className: "dt-center"
                },
                {
                    targets: 9,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        productsTable.on('xhr', function () {
            var json = productsTable.ajax.json();
            products.total = json.recordsTotal;
        });

        $('#products_table th.dt-checkboxes-select-all').click(function () {
            allProductsClicked = true;
        });
        $('#ctrl-show-selected-products').on('change', function () {
            productsTable.ajax.reload();
        });

        var collectProducts = function () {
            $('input[name="products_type"]').remove();
            $('input[name="products_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "products_type").val(products.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "products_data").val(products.data));
        };
    }


    if (typeof setManufacturersTable !== 'undefined') {

        function updateManufacturersSelectInfo() {
            $('#manufacturers_select_info').text(manufacturers.type === 'selected' ? manufacturers.data.length + ' rows selected' : manufacturers.total - manufacturers.data.length + ' rows selected');

        }

        var manufacturers = {type: "unselected", data: [], total: 0, filtered: 0};
        var allManufacturersClicked = false;

        var manufacturersTable = $('#manufacturers_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getManufacturers';
                    d.extra_search_type = $('#ctrl-show-selected-manufacturers').val();
                    d.extra_search_params = {type: manufacturers.type, data: manufacturers.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "address_count"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#manufacturers_select_info').length === 0) {
                    $('#manufacturers_table_info').append('<span id="manufacturers_select_info" class="select_info"></span>');
                }
                updateManufacturersSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (manufacturers.type === 'unselected') {
                                    index = manufacturers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            manufacturers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            manufacturers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = manufacturers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            manufacturers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            manufacturers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateManufacturersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allManufacturersClicked) {
                                if (selected) {
                                    manufacturers.type = 'unselected';
                                } else {
                                    manufacturers.type = 'selected';
                                }
                                manufacturers.data = [];
                                allManufacturersClicked = false;
                            }
                            updateManufacturersSelectInfo();

                            if (!indeterminate && manufacturers.data.length !== 0 && parseInt(manufacturers.total) !== manufacturers.data.length) {
                                $('#manufacturers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (manufacturers.type === 'unselected') {

                            if (manufacturers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (manufacturers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    orderable: false,
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 6,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        manufacturersTable.on('xhr', function () {
            var json = manufacturersTable.ajax.json();
            manufacturers.total = json.recordsTotal;
        });

        $('#manufacturers_table th.dt-checkboxes-select-all').click(function () {
            allManufacturersClicked = true;
        });
        $('#ctrl-show-selected-manufacturers').on('change', function () {
            manufacturersTable.ajax.reload();
        });

        var collectManufacturers = function () {
            $('input[name="manufacturers_type"]').remove();
            $('input[name="manufacturers_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "manufacturers_type").val(manufacturers.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "manufacturers_data").val(manufacturers.data));
        };
    }

    if (typeof setSuppliersTable !== 'undefined') {

        function updateSuppliersSelectInfo() {
            $('#suppliers_select_info').text(suppliers.type === 'selected' ? suppliers.data.length + ' rows selected' : suppliers.total - suppliers.data.length + ' rows selected');

        }

        var suppliers = {type: "unselected", data: [], total: 0, filtered: 0};
        var allSuppliersClicked = false;

        var suppliersTable = $('#suppliers_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getSuppliers';
                    d.extra_search_type = $('#ctrl-show-selected-suppliers').val();
                    d.extra_search_params = {type: suppliers.type, data: suppliers.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "prod_count"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#suppliers_select_info').length === 0) {
                    $('#suppliers_table_info').append('<span id="suppliers_select_info" class="select_info"></span>');
                }
                updateSuppliersSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (suppliers.type === 'unselected') {
                                    index = suppliers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            suppliers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            suppliers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = suppliers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            suppliers.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            suppliers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateSuppliersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allSuppliersClicked) {
                                if (selected) {
                                    suppliers.type = 'unselected';
                                } else {
                                    suppliers.type = 'selected';
                                }
                                suppliers.data = [];
                                allSuppliersClicked = false;
                            }
                            updateSuppliersSelectInfo();

                            if (!indeterminate && suppliers.data.length !== 0 && parseInt(suppliers.total) !== suppliers.data.length) {
                                $('#suppliers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (suppliers.type === 'unselected') {

                            if (suppliers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (suppliers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
//                    width: "30%",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 4,
                    width: "90px",
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? data : '--';
                    }
                },
                {
                    targets: 5,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        suppliersTable.on('xhr', function () {
            var json = suppliersTable.ajax.json();
            suppliers.total = json.recordsTotal;
        });

        $('#suppliers_table th.dt-checkboxes-select-all').click(function () {
            allSuppliersClicked = true;
        });
        $('#ctrl-show-selected-suppliers').on('change', function () {
            suppliersTable.ajax.reload();
        });

        var collectSuppliers = function () {
            $('input[name="suppliers_type"]').remove();
            $('input[name="suppliers_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers_type").val(suppliers.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "suppliers_data").val(suppliers.data));
        };
    }

    if (typeof setAttributesTable !== 'undefined') {

        function updateAttributesSelectInfo() {
            $('#attributes_select_info').text(attributes.type === 'selected' ? attributes.data.length + ' rows selected' : attributes.total - attributes.data.length + ' rows selected');

        }

        var attributes = {type: "unselected", data: [], total: 0, filtered: 0};
        var allAttributesClicked = false;

        var attributesTable = $('#attributes_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getAttributes';
                    d.extra_search_type = $('#ctrl-show-selected-attributes').val();
                    d.extra_search_params = {type: attributes.type, data: attributes.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "group_name"},
                {data: "attribute_name"},
                {data: "group_type"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#attributes_select_info').length === 0) {
                    $('#attributes_table_info').append('<span id="attributes_select_info" class="select_info"></span>');
                }
                updateAttributesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (attributes.type === 'unselected') {
                                    index = attributes.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            attributes.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            attributes.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = attributes.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            attributes.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            attributes.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateAttributesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allAttributesClicked) {
                                if (selected) {
                                    attributes.type = 'unselected';
                                } else {
                                    attributes.type = 'selected';
                                }
                                attributes.data = [];
                                allAttributesClicked = false;
                            }
                            updateAttributesSelectInfo();

                            if (!indeterminate && attributes.data.length !== 0 && parseInt(attributes.total) !== attributes.data.length) {
                                $('#attributes_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (attributes.type === 'unselected') {

                            if (attributes.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (attributes.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        attributesTable.on('xhr', function () {
            var json = attributesTable.ajax.json();
            attributes.total = json.recordsTotal;
        });

        $('#attributes_table th.dt-checkboxes-select-all').click(function () {
            allAttributesClicked = true;
        });
        $('#ctrl-show-selected-attributes').on('change', function () {
            attributesTable.ajax.reload();
        });

        var collectAttributes = function () {
            $('input[name="attributes_type"]').remove();
            $('input[name="attributes_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributes_type").val(attributes.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "attributes_data").val(attributes.data));
        };
    }

    if (typeof setFeaturesTable !== 'undefined') {

        function updateFeaturesSelectInfo() {
            $('#features_select_info').text(features.type === 'selected' ? features.data.length + ' rows selected' : features.total - features.data.length + ' rows selected');

        }

        var features = {type: "unselected", data: [], total: 0, filtered: 0};
        var allFeaturesClicked = false;

        var featuresTable = $('#features_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getFeatures';
                    d.extra_search_type = $('#ctrl-show-selected-features').val();
                    d.extra_search_params = {type: features.type, data: features.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "feature_name"},
                {data: "feature_value"},
                {data: "custom"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#features_select_info').length === 0) {
                    $('#features_table_info').append('<span id="features_select_info" class="select_info"></span>');
                }
                updateFeaturesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (features.type === 'unselected') {
                                    index = features.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id.replace('"', '&quuot;'));

                                    if (selected) {
                                        if (index > -1) {
                                            features.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            features.data.push(nodes.ajax.json().data[nodes.selector.rows].id.replace('"', '&quuot;'));
                                        }
                                    }
                                } else {
                                    index = features.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id.replace('"', '&quuot;'));
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            features.data.push(nodes.ajax.json().data[nodes.selector.rows].id.replace('"', '&quuot;'));
                                        }
                                    } else {

                                        if (index > -1) {
                                            features.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateFeaturesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allFeaturesClicked) {
                                if (selected) {
                                    features.type = 'unselected';
                                } else {
                                    features.type = 'selected';
                                }
                                features.data = [];
                                allFeaturesClicked = false;
                            }
                            updateFeaturesSelectInfo();

                            if (!indeterminate && features.data.length !== 0 && parseInt(features.total) !== features.data.length) {
                                $('#features_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (features.type === 'unselected') {

                            if (features.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (features.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 3,
                    width: "80px",
                    className: "dt-center",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        featuresTable.on('xhr', function () {
            var json = featuresTable.ajax.json();
            features.total = json.recordsTotal;
        });

        $('#features_table th.dt-checkboxes-select-all').click(function () {
            allFeaturesClicked = true;
        });
        $('#ctrl-show-selected-features').on('change', function () {
            featuresTable.ajax.reload();
        });

        var collectFeatures = function () {
            $('input[name="features_type"]').remove();
            $('input[name="features_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "features_type").val(features.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "features_data").val(features.data));
        };
    }

    if (typeof setCarriersTable !== 'undefined') {

        function updateCarriersSelectInfo() {
            $('#carriers_select_info').text(carriers.type === 'selected' ? carriers.data.length + ' rows selected' : carriers.total - carriers.data.length + ' rows selected');

        }

        var carriers = {type: "unselected", data: [], total: 0, filtered: 0};
        var allCarriersClicked = false;

        var carriersTable = $('#carriers_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCarriers';
                    d.extra_search_type = $('#ctrl-show-selected-carriers').val();
                    d.extra_search_params = {type: carriers.type, data: carriers.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "reference"},
                {data: "id"},
                {data: "reference"},
                {data: "name"},
                {data: "logo"},
                {data: "delay"},
                {data: "enabled"},
                {data: "is_free"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#carriers_select_info').length === 0) {
                    $('#carriers_table_info').append('<span id="carriers_select_info" class="select_info"></span>');
                }
                updateCarriersSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (carriers.type === 'unselected') {
                                    index = carriers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);

                                    if (selected) {
                                        if (index > -1) {
                                            carriers.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            carriers.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    }
                                } else {
                                    index = carriers.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].reference);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            carriers.data.push(nodes.ajax.json().data[nodes.selector.rows].reference);
                                        }
                                    } else {

                                        if (index > -1) {
                                            carriers.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCarriersSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCarriersClicked) {
                                if (selected) {
                                    carriers.type = 'unselected';
                                } else {
                                    carriers.type = 'selected';
                                }
                                carriers.data = [];
                                allCarriersClicked = false;
                            }
                            updateCarriersSelectInfo();

                            if (!indeterminate && carriers.data.length !== 0 && parseInt(carriers.total) !== carriers.data.length) {
                                $('#carriers_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (carriers.type === 'unselected') {

                            if (carriers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (carriers.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 2,
                    width: "70px"
                },
                {
                    targets: 4,
//                    width: "90px",
                    className: "dt-center",
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                },
                {
                    targets: 7,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        carriersTable.on('xhr', function () {
            var json = carriersTable.ajax.json();
            carriers.total = json.recordsTotal;
        });

        $('#carriers_table th.dt-checkboxes-select-all').click(function () {
            allCarriersClicked = true;
        });
        $('#ctrl-show-selected-carriers').on('change', function () {
            carriersTable.ajax.reload();
        });

        var collcetCarriers = function () {
            $('input[name="carriers_type"]').remove();
            $('input[name="carriers_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers_type").val(carriers.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "carriers_data").val(carriers.data));
        };
    }

    if (typeof setShopsTable !== 'undefined') {

        function updateShopsSelectInfo() {
            $('#shops_select_info').text(shops.type === 'selected' ? shops.data.length + ' rows selected' : shops.total - shops.data.length + ' rows selected');

        }

        var shops = {type: "unselected", data: [], total: 0, filtered: 0};
        var allShopsClicked = false;

        var shopsTable = $('#shops_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getShops';
                    d.extra_search_type = $('#ctrl-show-selected-shops').val();
                    d.extra_search_params = {type: shops.type, data: shops.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "sg_name"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#shops_select_info').length === 0) {
                    $('#shops_table_info').append('<span id="shops_select_info" class="select_info"></span>');
                }
                updateShopsSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (shops.type === 'unselected') {
                                    index = shops.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            shops.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            shops.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = shops.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            shops.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            shops.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateShopsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allShopsClicked) {
                                if (selected) {
                                    shops.type = 'unselected';
                                } else {
                                    shops.type = 'selected';
                                }
                                shops.data = [];
                                allShopsClicked = false;
                            }
                            updateShopsSelectInfo();

                            if (!indeterminate && shops.data.length !== 0 && parseInt(shops.total) !== shops.data.length) {
                                $('#shops_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (shops.type === 'unselected') {

                            if (shops.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (shops.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        shopsTable.on('xhr', function () {
            var json = shopsTable.ajax.json();
            shops.total = json.recordsTotal;
        });

        $('#shops_table th.dt-checkboxes-select-all').click(function () {
            allShopsClicked = true;
        });
        $('#ctrl-show-selected-shops').on('change', function () {
            shopsTable.ajax.reload();
        });

        var collectShops = function () {
            $('input[name="shops_type"]').remove();
            $('input[name="shops_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "shops_type").val(shops.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "shops_data").val(shops.data));
        };
    }

    if (typeof setCountriesTable !== 'undefined') {

        function updateCountriesSelectInfo() {
            $('#countries_select_info').text(countries.type === 'selected' ? countries.data.length + ' rows selected' : countries.total - countries.data.length + ' rows selected');

        }

        var countries = {type: "unselected", data: [], total: 0, filtered: 0};
        var allCountriesClicked = false;

        var countriesTable = $('#countries_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCountries';
                    d.extra_search_type = $('#ctrl-show-selected-countries').val();
                    d.extra_search_params = {type: countries.type, data: countries.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "iso_code"},
                {data: "call_prefix"},
                {data: "zone"},
                {data: "enabled"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#countries_select_info').length === 0) {
                    $('#countries_table_info').append('<span id="countries_select_info" class="select_info"></span>');
                }
                updateCountriesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (countries.type === 'unselected') {
                                    index = countries.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            countries.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            countries.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = countries.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            countries.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            countries.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCountriesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCountriesClicked) {
                                if (selected) {
                                    countries.type = 'unselected';
                                } else {
                                    countries.type = 'selected';
                                }
                                countries.data = [];
                                allCountriesClicked = false;
                            }
                            updateCountriesSelectInfo();

                            if (!indeterminate && countries.data.length !== 0 && parseInt(countries.total) !== countries.data.length) {
                                $('#countries_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (countries.type === 'unselected') {

                            if (countries.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (countries.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
//                {
//                    targets: 3,
//                    className: "dt-center",
////                    width: "120px"
//                },
//                {
//                    targets: 4,
//                    className: "dt-center",
//                    width: "100px"
//                },
//                {
//                    targets: 5,
//                    className: "dt-center",
//                    width: "120px"
//                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        countriesTable.on('xhr', function () {
            var json = countriesTable.ajax.json();
            countries.total = json.recordsTotal;
        });

        $('#countries_table th.dt-checkboxes-select-all').click(function () {
            allCountriesClicked = true;
        });
        $('#ctrl-show-selected-countries').on('change', function () {
            countriesTable.ajax.reload();
        });

        var collectCountries = function () {
            $('input[name="countries_type"]').remove();
            $('input[name="countries_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "countries_type").val(countries.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "countries_data").val(countries.data));
        };
    }

    if (typeof setCurrenciesTable !== 'undefined') {

        function updateCurrenciesSelectInfo() {
            $('#currencies_select_info').text(currencies.type === 'selected' ? currencies.data.length + ' rows selected' : currencies.total - currencies.data.length + ' rows selected');

        }

        var currencies = {type: "unselected", data: [], total: 0, filtered: 0};
        var allCurrenciesClicked = false;

        var currenciesTable = $('#currencies_table').DataTable({
            order: [[1, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getCurrencies';
                    d.extra_search_type = $('#ctrl-show-selected-currencies').val();
                    d.extra_search_params = {type: currencies.type, data: currencies.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "id"},
                {data: "name"},
                {data: "iso_code"},
                {data: "symbol"},
                {data: "conversion_rate"},
                {data: "enabled"},
//                {data: "deleted"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#currencies_select_info').length === 0) {
                    $('#currencies_table_info').append('<span id="currencies_select_info" class="select_info"></span>');
                }
                updateCurrenciesSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "70px",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (currencies.type === 'unselected') {
                                    index = currencies.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            currencies.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            currencies.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = currencies.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            currencies.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            currencies.data.splice(index, 1);
                                        }
                                    }
                                }
                                updateCurrenciesSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allCurrenciesClicked) {
                                if (selected) {
                                    currencies.type = 'unselected';
                                } else {
                                    currencies.type = 'selected';
                                }
                                currencies.data = [];
                                allCurrenciesClicked = false;
                            }
                            updateCurrenciesSelectInfo();

                            if (!indeterminate && currencies.data.length !== 0 && parseInt(currencies.total) !== currencies.data.length) {
                                $('#currencies_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (currencies.type === 'unselected') {

                            if (currencies.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (currencies.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "70px"
                },
                {
                    targets: 3,
                    className: "dt-center",
//                    width: "120px"
                },
                {
                    targets: 4,
                    className: "dt-center",
                    width: "100px"
                },
                {
                    targets: 5,
                    className: "dt-center",
                    width: "120px"
                },
                {
                    targets: 6,
                    className: "dt-center",
                    width: "120px",
//                    orderable: false,
                    render: function (data, type, row, meta) {
                        return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        currenciesTable.on('xhr', function () {
            var json = currenciesTable.ajax.json();
            currencies.total = json.recordsTotal;
        });

        $('#currencies_table th.dt-checkboxes-select-all').click(function () {
            allCurrenciesClicked = true;
        });
        $('#ctrl-show-selected-currencies').on('change', function () {
            currenciesTable.ajax.reload();
        });

        var collectCurrencies = function () {
            $('input[name="currencies_type"]').remove();
            $('input[name="currencies_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "currencies_type").val(currencies.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "currencies_data").val(currencies.data));
        };
    }

    if (typeof setPaymentMethodsTable !== 'undefined') {

        function updatePaymentMethodsSelectInfo() {
            $('#paymentMethods_select_info').text(paymentMethods.type === 'selected' ? paymentMethods.data.length + ' rows selected' : paymentMethods.total - paymentMethods.data.length + ' rows selected');

        }

        var paymentMethods = {type: "unselected", data: [], total: 0, filtered: 0};
        var allPaymentMethodsClicked = false;

        var paymentMethodsTable = $('#paymentMethods_table').DataTable({
            order: [[2, 'asc']],
            bAutoWidth: false,
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
            ajax: {
                url: postUrl,
                type: "POST",
                data: function (d) {
                    d.ajax_action = 'getPaymentMethods2';
                    d.extra_search_type = $('#ctrl-show-selected-paymentMethods').val();
                    d.extra_search_params = {type: paymentMethods.type, data: paymentMethods.data};
                }
            },
            language: {
                select: {
                    rows: {
                        '_': ''
                    }
                }
            },
            columns: [
                {data: "id"},
                {data: "logo"},
                {data: "name"},
                {data: "module_name"}
            ],
            stateSave: true,
            stateSaveCallback: function (settings, data) {
//                localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function (settings) {
//                return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
            },
            drawCallback: function (settings) {
                if ($('#paymentMethods_select_info').length === 0) {
                    $('#paymentMethods_table_info').append('<span id="paymentMethods_select_info" class="select_info"></span>');
                }
                updatePaymentMethodsSelectInfo();
            },
            columnDefs: [
                {
                    targets: 0,
                    width: "7%",
//                    width: "55px",
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    checkboxes: {
                        selectRow: true,
                        selectAllRender: '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>',
                        selectCallback: function (nodes, selected) {

                            var index;
                            if (nodes.selector.rows) {

                                if (paymentMethods.type === 'unselected') {
                                    index = paymentMethods.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);

                                    if (selected) {
                                        if (index > -1) {
                                            paymentMethods.data.splice(index, 1);
                                        }
                                    } else {
                                        if (index === -1) {
                                            paymentMethods.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    }
                                } else {
                                    index = paymentMethods.data.indexOf(nodes.ajax.json().data[nodes.selector.rows].id);
//                                            
                                    if (selected) {
                                        if (index === -1) {
                                            paymentMethods.data.push(nodes.ajax.json().data[nodes.selector.rows].id);
                                        }
                                    } else {

                                        if (index > -1) {
                                            paymentMethods.data.splice(index, 1);
                                        }
                                    }
                                }
                                updatePaymentMethodsSelectInfo();
                            }
                        },
                        selectAllCallback: function (nodes, selected, indeterminate) {
                            if (!indeterminate && allPaymentMethodsClicked) {
                                if (selected) {
                                    paymentMethods.type = 'unselected';
                                } else {
                                    paymentMethods.type = 'selected';
                                }
                                paymentMethods.data = [];
                                allPaymentMethodsClicked = false;
                            }
                            updatePaymentMethodsSelectInfo();

                            if (!indeterminate && paymentMethods.data.length !== 0 && parseInt(paymentMethods.total) !== paymentMethods.data.length) {
                                $('#paymentMethods_table thead .dt-checkboxes').prop('indeterminate', true);
                            }
                        }
                    },
                    'createdCell': function (td, cellData, rowData, row, col) {

                        if (paymentMethods.type === 'unselected') {

                            if (paymentMethods.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.deselect();
                            } else {
                                this.api().cell(td).checkboxes.select();
                            }
                        } else {
                            if (paymentMethods.data.includes(cellData)) {
                                this.api().cell(td).checkboxes.select();
                            } else {
                                this.api().cell(td).checkboxes.deselect();
                            }
                        }
                    }
                },
                {
                    targets: 1,
                    width: "20%",
                    orderable: false,
                    className: "dt-center",
                    render: function (data, type, row, meta) {
                        return data ? data : '';
                    }
                }
            ],
            select: {
                style: 'os multi',
                info: false
            }
        });


        paymentMethodsTable.on('xhr', function () {
            var json = paymentMethodsTable.ajax.json();
            paymentMethods.total = json.recordsTotal;
        });

        $('#paymentMethods_table th.dt-checkboxes-select-all').click(function () {
            allPaymentMethodsClicked = true;
        });
        $('#ctrl-show-selected-paymentMethods').on('change', function () {
            paymentMethodsTable.ajax.reload();
        });

        var collectPaymentMethods = function () {
            $('input[name="payment_methods_type"]').remove();
            $('input[name="payment_methods_data"]').remove();
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "payment_methods_type").val(paymentMethods.type));
            $('#data_export_form').append($("<input>")
                    .attr("type", "hidden")
                    .attr("name", "payment_methods_data").val(paymentMethods.data));
        };
    }

    $('#expand-all-data_export_orders_categories_tree').hide();

    $.post(postUrl, {ajax_action: 'updateCurrencySymbols', type: 'dml'});

    $('#data_export_orders_filter_data h3').click(function (e) {

        if ($(this).next().hasClass('in')) {
            $(this).children('i.pull-right').attr('class', 'icon-chevron-right pull-right');
            $(this).next().collapse('hide');
        } else {
            $(this).children('i.pull-right').attr('class', 'icon-chevron-down pull-right');
            $(this).next().collapse('show');
        }
    });

    $('#expand_data_filter').click(function (e) {
        e.preventDefault();
        if ($(this).children('i').hasClass('icon-angle-right')) {
            $(this).children('i').removeClass('icon-angle-right').addClass('icon-angle-down');
            $('#data_export_orders_filter_data h3 > i.pull-right').attr('class', 'icon-chevron-down pull-right');
            $('#data_export_orders_filter_data h3 + div').collapse('show');
            $(this).children('span').text(collapse_all);
        } else {
            $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-right');
            $('#data_export_orders_filter_data h3 > i.pull-right').attr('class', 'icon-chevron-right pull-right');
            $('#data_export_orders_filter_data h3 + div').collapse('hide');
            $(this).children('span').text(expand_all);
        }
    });

    $('.date_collapser').on('change', 'select', function () {

        if ($(this).val() === 'select_date') {
            $(this).closest('.form-group.date_collapser').next().addClass('in');
//            $(this).closest('.form-group.date_collapser').next().collapse("show");
        } else {
            $(this).closest('.form-group.date_collapser').next().removeClass('in');
//            $(this).closest('.form-group.date_collapser').next().collapse("hide");
        }
    });


    var autoexportFTPsTable = $('#autoexportFTPs_table').DataTable({
        order: [[1, 'asc']],
        bAutoWidth: false,
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: postUrl,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getAutoexportFTPs';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "ftp_id"},
            {data: "ftp_type"},
            {data: "ftp_url"},
            {data: "ftp_username"},
            {data: "ftp_password"},
            {data: "ftp_folder"},
            {data: "ftp_timestamp"},
            {data: "ftp_setting_name"},
            {data: "ftp_active"},
            {data: ""},
            {data: "ftp_setting"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "30px"
            },
            {
                targets: 2,
                width: "70px",
                render: function (data, type, row, meta) {
                    return data === 'ftp' ? 'FTP' : 'SFTP';
                }
            },
            {
                targets: 7,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                }
            },
            {
                targets: 8,
                width: "12%"
            },
            {
                targets: 9,
                className: "dt-center",
                width: "60px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders change"></i>' : '<i class="icon-remove orders change"></i>';
                }
            },
            {
                targets: 10,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_autoexport"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#ef0000" class="icon-trash delete_autoexport"></i>';
                }
            },
            {
                targets: 11,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#data_export_orders_autoexport_ftp_save').click(function () {
        var url = $('#data_export_orders_autoexport_ftp_url').val();
        var username = $('#data_export_orders_autoexport_ftp_username').val();
        var data = {
            ftp_type: $('#data_export_orders_autoexport_ftp_type').val(),
            ftp_mode: $('#data_export_orders_autoexport_ftp_mode').val(),
            ftp_url: url,
            ftp_port: $('#data_export_orders_autoexport_ftp_port').val(),
            ftp_username: username,
            ftp_password: $('#data_export_orders_autoexport_ftp_password').val(),
            ftp_folder: $('#data_export_orders_autoexport_ftp_folder').val(),
            ftp_timestamp: $('input[name="orders_autoexport_ftp_add_ts"]:checked').val(),
            ftp_setting: $('#data_export_orders_autoexport_ftp_setting').val(),
            ftp_active: $('input[name="orders_autoexport_ftp_active"]:checked').val()
        };
        var ftp_id = $('#data_export_orders_autoexport_ftp_id').val();
        if (!url || !username) {
            return alert(fill_required_fields);
        }
        $('#autoexport_ftp_modal').modal('hide');
        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    autoexportFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#data_export_orders_autoexport_add_new_ftp').click(function () {
        $('#autoexport_ftp_modal h4.modal-title').text(add_ftp);
        $('#data_export_orders_autoexport_ftp_id').val('');
        $('#data_export_orders_autoexport_ftp_type').val('ftp');
        $('#data_export_orders_autoexport_ftp_mode').val('active');
        $('#data_export_orders_autoexport_ftp_type').change();
        $('#data_export_orders_autoexport_ftp_url').val('');
        $('#data_export_orders_autoexport_ftp_port').val('');
        $('#data_export_orders_autoexport_ftp_username').val('');
        $('#data_export_orders_autoexport_ftp_password').val('');
        $('#data_export_orders_autoexport_ftp_folder').val('');
        $('#data_export_orders_autoexport_ftp_setting').val('orders_default');
        $('#data_export_orders_autoexport_ftp_active_no').prop("checked", false);
        $('#data_export_orders_autoexport_ftp_active_yes').prop("checked", true);
        $('#data_export_orders_autoexport_ftp_add_ts_no').prop("checked", true);
        $('#data_export_orders_autoexport_ftp_add_ts_yes').prop("checked", false);
    });

    $('#autoexportFTPs_table').on('click', 'td > .edit_autoexport', function (e) {
        e.preventDefault();
        var data = autoexportFTPsTable.row($(this).closest('tr')).data();
        $('#data_export_orders_autoexport_ftp_id').val(data.ftp_id);
        $('#data_export_orders_autoexport_ftp_type').val(data.ftp_type);
        $('#data_export_orders_autoexport_ftp_mode').val(data.ftp_mode);
        $('#data_export_orders_autoexport_ftp_type').change();
        var url = data.ftp_url.split(':');
        $('#data_export_orders_autoexport_ftp_url').val(url[0]);
        if (typeof url[1] !== 'undefined') {
            $('#data_export_orders_autoexport_ftp_port').val(url[1]);
        } else {
            $('#data_export_orders_autoexport_ftp_port').val('');
        }
        $('#data_export_orders_autoexport_ftp_username').val(data.ftp_username);
        $('#data_export_orders_autoexport_ftp_password').val(data.ftp_password);
        $('#data_export_orders_autoexport_ftp_folder').val(data.ftp_folder);
        $('#data_export_orders_autoexport_ftp_setting').val(data.ftp_setting);
        $('#data_export_orders_autoexport_ftp_active_no').prop("checked", !parseInt(data.ftp_active));
        $('#data_export_orders_autoexport_ftp_active_yes').prop("checked", parseInt(data.ftp_active));
        $('#data_export_orders_autoexport_ftp_add_ts_no').prop("checked", !parseInt(data.ftp_timestamp));
        $('#data_export_orders_autoexport_ftp_add_ts_yes').prop("checked", parseInt(data.ftp_timestamp));

        $('#autoexport_ftp_modal h4.modal-title').text(edit_ftp);
        $('#autoexport_ftp_modal').modal('show');
    });

    $('#autoexportFTPs_table').on('click', 'td > .delete_autoexport', function (e) {
        e.preventDefault();
        if (confirm(sure_to_delete)) {
            var ftp_id = autoexportFTPsTable.row($(this).closest('tr')).data().ftp_id;
            $('#fader, #spinner').css('display', 'block');
            $.post(postUrl, {ajax_action: 'deleteAutoexportFTP', type: 'dml', ftp_id: ftp_id},
                    function (result) {
                        autoexportFTPsTable.draw();
                        $('#fader, #spinner').css('display', 'none');
                        var json = JSON.parse(result);
                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });


    var autoexportEmailsTable = $('#autoexportEmails_table').DataTable({
        order: [[1, 'asc']],
        bAutoWidth: false,
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: postUrl,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getAutoexportEmails';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "email_id"},
            {data: "email_address"},
            {data: "email_setting_name"},
            {data: "email_active"},
            {data: ""},
            {data: "email_setting"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "40px"
            },
            {
                targets: 4,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders change"></i>' : '<i class="icon-remove orders change"></i>';
                }
            },
            {
                targets: 5,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_autoexport"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#ef0000" class="icon-trash delete_autoexport"></i>';
                }
            },
            {
                targets: 6,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#data_export_orders_autoexport_email_save').click(function () {
        var email = $('#data_export_orders_autoexport_email_address').val();
        var data = {
            email_address: email,
            email_setting: $('#data_export_orders_autoexport_email_setting').val(),
            email_active: $('input[name="orders_autoexport_email_active"]:checked').val()
        };
        var email_id = $('#data_export_orders_autoexport_email_id').val();
        if (!email) {
            return alert(fill_required_fields);
        }
        $('#autoexport_email_modal').modal('hide');
        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    autoexportEmailsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#data_export_orders_autoexport_add_new_email').click(function () {
        $('#autoexport_email_modal h4.modal-title').text(add_email);
        $('#data_export_orders_autoexport_email_id').val('');
        $('#data_export_orders_autoexport_email_address').val('');
        $('#data_export_orders_autoexport_email_setting').val('orders_default');
        $('#data_export_orders_autoexport_email_active_yes').prop("checked", true);
        $('#data_export_orders_autoexport_email_active_no').prop("checked", false);
    });

    $('#autoexportEmails_table').on('click', 'td > .edit_autoexport', function (e) {
        e.preventDefault();
        var data = autoexportEmailsTable.row($(this).closest('tr')).data();
        $('#data_export_orders_autoexport_email_id').val(data.email_id);
        $('#data_export_orders_autoexport_email_address').val(data.email_address);
        $('#data_export_orders_autoexport_email_setting').val(data.email_setting);
        $('#data_export_orders_autoexport_email_active_yes').prop("checked", parseInt(data.email_active));
        $('#data_export_orders_autoexport_email_active_no').prop("checked", !parseInt(data.email_active));

        $('#autoexport_email_modal h4.modal-title').text(edit_email);
        $('#autoexport_email_modal').modal('show');
    });

    $('#autoexportEmails_table').on('click', 'td > .delete_autoexport', function (e) {
        e.preventDefault();
        if (confirm(sure_to_delete)) {
            var email_id = autoexportEmailsTable.row($(this).closest('tr')).data().email_id;
            $('#fader, #spinner').css('display', 'block');
            $.post(postUrl, {ajax_action: 'deleteAutoexportEmail', type: 'dml', email_id: email_id},
                    function (result) {
                        autoexportEmailsTable.draw();
                        $('#fader, #spinner').css('display', 'none');
                        var json = JSON.parse(result);
                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });


    $('#autoexportEmails_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = autoexportEmailsTable.row($(this).closest('tr')).data();

        var email_id = data.email_id;

        var data = {
            email_address: data.email_address,
            email_setting: data.email_setting,
            email_active: 0
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    autoexportEmailsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#autoexportEmails_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = autoexportEmailsTable.row($(this).closest('tr')).data();

        var email_id = data.email_id;

        var data = {
            email_address: data.email_address,
            email_setting: data.email_setting,
            email_active: 1
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    autoexportEmailsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });

    });

    $('#autoexportFTPs_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = autoexportFTPsTable.row($(this).closest('tr')).data();

        var ftp_id = data.ftp_id, url_parts = data.ftp_url.split(':');

        var data = {
            ftp_type: data.ftp_type,
            ftp_mode: data.ftp_mode,
            ftp_url: url_parts[0],
            ftp_port: url_parts[1] ? url_parts[1] : '',
            ftp_username: data.ftp_username,
            ftp_password: data.ftp_username,
            ftp_folder: data.ftp_folder,
            ftp_timestamp: data.ftp_timestamp,
            ftp_setting: data.ftp_setting,
            ftp_active: 0
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    autoexportFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#autoexportFTPs_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = autoexportFTPsTable.row($(this).closest('tr')).data();

        var ftp_id = data.ftp_id, url_parts = data.ftp_url.split(':');

        var data = {
            ftp_type: data.ftp_type,
            ftp_mode: data.ftp_mode,
            ftp_url: url_parts[0],
            ftp_port: url_parts[1] ? url_parts[1] : '',
            ftp_username: data.ftp_username,
            ftp_password: data.ftp_username,
            ftp_folder: data.ftp_folder,
            ftp_timestamp: data.ftp_timestamp,
            ftp_setting: data.ftp_setting,
            ftp_active: 1
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveAutoexportFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    autoexportFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });
    

    $('#scheduleEmails_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();

        var email_id = data.email_id;

        var data = {
            email_address: data.email_address,
            email_setting: data.email_setting,
            email_active: 0
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    scheduleEmailsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleEmails_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();

        var email_id = data.email_id;

        var data = {
            email_address: data.email_address,
            email_setting: data.email_setting,
            email_active: 1
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    scheduleEmailsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleFTPs_table').on('click', 'td > .icon-check.change', function (e) {
        e.preventDefault();
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();

        var ftp_id = data.ftp_id, url_parts = data.ftp_url.split(':');

        var data = {
            ftp_type: data.ftp_type,
            ftp_mode: data.ftp_mode,
            ftp_url: url_parts[0],
            ftp_port: url_parts[1] ? url_parts[1] : '',
            ftp_username: data.ftp_username,
            ftp_password: data.ftp_username,
            ftp_folder: data.ftp_folder,
            ftp_timestamp: data.ftp_timestamp,
            ftp_setting: data.ftp_setting,
            ftp_active: 0
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    scheduleFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#scheduleFTPs_table').on('click', 'td > .icon-remove.change', function (e) {
        e.preventDefault();
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();

        var ftp_id = data.ftp_id, url_parts = data.ftp_url.split(':');

        var data = {
            ftp_type: data.ftp_type,
            ftp_mode: data.ftp_mode,
            ftp_url: url_parts[0],
            ftp_port: url_parts[1] ? url_parts[1] : '',
            ftp_username: data.ftp_username,
            ftp_password: data.ftp_username,
            ftp_folder: data.ftp_folder,
            ftp_timestamp: data.ftp_timestamp,
            ftp_setting: data.ftp_setting,
            ftp_active: 1
        };

        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    scheduleFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });
    

    var scheduleFTPsTable = $('#scheduleFTPs_table').DataTable({
        order: [[1, 'asc']],
        bAutoWidth: false,
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: postUrl,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getScheduleFTPs';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "ftp_id"},
            {data: "ftp_type"},
            {data: "ftp_url"},
            {data: "ftp_username"},
            {data: "ftp_password"},
            {data: "ftp_folder"},
            {data: "ftp_timestamp"},
            {data: "ftp_setting_name"},
            {data: "ftp_active"},
            {data: ""},
            {data: "ftp_setting"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "30px"
            },
            {
                targets: 2,
                width: "70px",
                render: function (data, type, row, meta) {
                    return data === 'ftp' ? 'FTP' : 'SFTP';
                }
            },
            {
                targets: 7,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders"></i>' : '<i class="icon-remove orders"></i>';
                }
            },
            {
                targets: 8,
                width: "12%"
            },
            {
                targets: 9,
                className: "dt-center",
                width: "60px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders change"></i>' : '<i class="icon-remove orders change"></i>';
                }
            },
            {
                targets: 10,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_schedule"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#ef0000" class="icon-trash delete_schedule"></i>';
                }
            },
            {
                targets: 11,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#data_export_orders_schedule_ftp_save').click(function () {
        var url = $('#data_export_orders_schedule_ftp_url').val();
        var username = $('#data_export_orders_schedule_ftp_username').val();
        var data = {
            ftp_type: $('#data_export_orders_schedule_ftp_type').val(),
            ftp_mode: $('#data_export_orders_schedule_ftp_mode').val(),
            ftp_url: url,
            ftp_port: $('#data_export_orders_schedule_ftp_port').val(),
            ftp_username: username,
            ftp_password: $('#data_export_orders_schedule_ftp_password').val(),
            ftp_folder: $('#data_export_orders_schedule_ftp_folder').val(),
            ftp_timestamp: $('input[name="orders_schedule_ftp_add_ts"]:checked').val(),
            ftp_setting: $('#data_export_orders_schedule_ftp_setting').val(),
            ftp_active: $('input[name="orders_schedule_ftp_active"]:checked').val()
        };
        var ftp_id = $('#data_export_orders_schedule_ftp_id').val();
        if (!url || !username) {
            return alert(fill_required_fields);
        }
        $('#schedule_ftp_modal').modal('hide');
        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleFTP', type: 'dml', ftp_id: ftp_id, data},
                function (result) {
                    scheduleFTPsTable.draw();
                    $('#fader, #spinner').css('display', 'none');
                    var json = JSON.parse(result);
                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#data_export_orders_schedule_add_new_ftp').click(function () {
        $('#schedule_ftp_modal h4.modal-title').text(add_ftp);
        $('#data_export_orders_schedule_ftp_id').val('');
        $('#data_export_orders_schedule_ftp_type').val('ftp');
        $('#data_export_orders_schedule_ftp_mode').val('active');
        $('#data_export_orders_schedule_ftp_type').change();
        $('#data_export_orders_schedule_ftp_url').val('');
        $('#data_export_orders_schedule_ftp_port').val('');
        $('#data_export_orders_schedule_ftp_username').val('');
        $('#data_export_orders_schedule_ftp_password').val('');
        $('#data_export_orders_schedule_ftp_folder').val('');
        $('#data_export_orders_schedule_ftp_setting').val('orders_default');
        $('#data_export_orders_schedule_ftp_active_no').prop("checked", false);
        $('#data_export_orders_schedule_ftp_active_yes').prop("checked", true);
        $('#data_export_orders_schedule_ftp_add_ts_no').prop("checked", false);
        $('#data_export_orders_schedule_ftp_add_ts_yes').prop("checked", true);
    });

    $('#scheduleFTPs_table').on('click', 'td > .edit_schedule', function (e) {
        e.preventDefault();
        var data = scheduleFTPsTable.row($(this).closest('tr')).data();
        $('#data_export_orders_schedule_ftp_id').val(data.ftp_id);
        $('#data_export_orders_schedule_ftp_type').val(data.ftp_type);
        $('#data_export_orders_schedule_ftp_mode').val(data.ftp_mode);
        $('#data_export_orders_schedule_ftp_type').change();
        var url = data.ftp_url.split(':');
        $('#data_export_orders_schedule_ftp_url').val(url[0]);
        if (typeof url[1] !== 'undefined') {
            $('#data_export_orders_schedule_ftp_port').val(url[1]);
        } else {
            $('#data_export_orders_schedule_ftp_port').val('');
        }
        $('#data_export_orders_schedule_ftp_username').val(data.ftp_username);
        $('#data_export_orders_schedule_ftp_password').val(data.ftp_password);
        $('#data_export_orders_schedule_ftp_folder').val(data.ftp_folder);
        $('#data_export_orders_schedule_ftp_setting').val(data.ftp_setting);
        $('#data_export_orders_schedule_ftp_active_no').prop("checked", !parseInt(data.ftp_active));
        $('#data_export_orders_schedule_ftp_active_yes').prop("checked", parseInt(data.ftp_active));
        $('#data_export_orders_schedule_ftp_add_ts_no').prop("checked", !parseInt(data.ftp_timestamp));
        $('#data_export_orders_schedule_ftp_add_ts_yes').prop("checked", parseInt(data.ftp_timestamp));

        $('#schedule_ftp_modal h4.modal-title').text(edit_ftp);
        $('#schedule_ftp_modal').modal('show');
    });

    $('#scheduleFTPs_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        if (confirm(sure_to_delete)) {
            var ftp_id = scheduleFTPsTable.row($(this).closest('tr')).data().ftp_id;
            $('#fader, #spinner').css('display', 'block');
            $.post(postUrl, {ajax_action: 'deleteScheduleFTP', type: 'dml', ftp_id: ftp_id},
                    function (result) {
                        scheduleFTPsTable.draw();
                        $('#fader, #spinner').css('display', 'none');
                        var json = JSON.parse(result);
                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });


    var scheduleEmailsTable = $('#scheduleEmails_table').DataTable({
        order: [[1, 'asc']],
        bAutoWidth: false,
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50, 100, 250, 500, 1000],
        ajax: {
            url: postUrl,
            type: "POST",
            data: function (d) {
                d.ajax_action = 'getScheduleEmails';
            }
        },
        language: {
            select: {
                rows: {
                    '_': ''
                }
            }
        },
        columns: [
            {data: ""},
            {data: "email_id"},
            {data: "email_address"},
            {data: "email_setting_name"},
            {data: "email_active"},
            {data: ""},
            {data: "email_setting"}
        ],
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            },
            {
                targets: 1,
                width: "40px"
            },
            {
                targets: 4,
                className: "dt-center",
                width: "85px",
//                    orderable: false,
                render: function (data, type, row, meta) {
                    return parseInt(data) ? '<i class="icon-check orders change"></i>' : '<i class="icon-remove orders change"></i>';
                }
            },
            {
                targets: 5,
                className: "dt-center",
                width: "60px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '<i title="' + edit + '" style="color:orange" class="icon-edit edit_schedule"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i title="' + delet + '" style="color:#ef0000" class="icon-trash delete_schedule"></i>';
                }
            },
            {
                targets: 6,
                width: "0px",
                orderable: false,
                render: function (data, type, row, meta) {
                    return '';
                }
            }
        ]
    });

    $('#data_export_orders_schedule_email_save').click(function () {
        var email = $('#data_export_orders_schedule_email_address').val();
        var data = {
            email_address: email,
            email_setting: $('#data_export_orders_schedule_email_setting').val(),
            email_active: $('input[name="orders_schedule_email_active"]:checked').val()
        };
        var email_id = $('#data_export_orders_schedule_email_id').val();
        if (!email) {
            return alert(fill_required_fields);
        }
        $('#schedule_email_modal').modal('hide');
        $('#fader, #spinner').css('display', 'block');
        $.post(postUrl, {ajax_action: 'saveScheduleEmail', type: 'dml', email_id: email_id, data},
                function (result) {
                    $('#fader, #spinner').css('display', 'none');
                    scheduleEmailsTable.draw();
                    var json = JSON.parse(result);

                    if (json.type === 'success') {
                        showSuccessMessage(json.message);
                    } else {
                        showErrorMessage(json.message);
                    }
                });
    });

    $('#data_export_orders_schedule_add_new_email').click(function () {
        $('#schedule_email_modal h4.modal-title').text(add_email);
        $('#data_export_orders_schedule_email_id').val('');
        $('#data_export_orders_schedule_email_address').val('');
        $('#data_export_orders_schedule_email_setting').val('orders_default');
        $('#data_export_orders_schedule_email_active_yes').prop("checked", true);
        $('#data_export_orders_schedule_email_active_no').prop("checked", false);
    });

    $('#scheduleEmails_table').on('click', 'td > .edit_schedule', function (e) {
        e.preventDefault();
        var data = scheduleEmailsTable.row($(this).closest('tr')).data();
        $('#data_export_orders_schedule_email_id').val(data.email_id);
        $('#data_export_orders_schedule_email_address').val(data.email_address);
        $('#data_export_orders_schedule_email_setting').val(data.email_setting);
        $('#data_export_orders_schedule_email_active_yes').prop("checked", parseInt(data.email_active));
        $('#data_export_orders_schedule_email_active_no').prop("checked", !parseInt(data.email_active));

        $('#schedule_email_modal h4.modal-title').text(edit_email);
        $('#schedule_email_modal').modal('show');
    });

    $('#scheduleEmails_table').on('click', 'td > .delete_schedule', function (e) {
        e.preventDefault();
        if (confirm(sure_to_delete)) {
            var email_id = scheduleEmailsTable.row($(this).closest('tr')).data().email_id;
            $('#fader, #spinner').css('display', 'block');
            $.post(postUrl, {ajax_action: 'deleteScheduleEmail', type: 'dml', email_id: email_id},
                    function (result) {
                        $('#fader, #spinner').css('display', 'none');
                        scheduleEmailsTable.draw();
                        var json = JSON.parse(result);

                        if (json.type === 'success') {
                            showSuccessMessage(json.message);
                        } else {
                            showErrorMessage(json.message);
                        }
                    });
        }
    });

    checkAllAssociatedCategories($('#data_export_orders_categories_tree'));
    $('#data_export_orders_categories_tree').tree('expandAll');

});
