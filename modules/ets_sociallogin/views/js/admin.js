/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
var solo_func = {
    buttonSort: function (selector) {
        if (selector.length > 0) {
            $(selector).sortable().bind('sortupdate', function (e, ui) {
                solo_func.updateSort();
            }).disableSelection();
            $('.solo_note').show();
        }
        return false;
    },
    updateSort: function () {
        if ($('.ets_solo_social_item').length > 0 && $('input[id^=ETS_SOLO_NETWORKS_ORDER]').length > 0 && $('#module_form').length > 0) {
            var nets = [], old_order = $('input[id^=ETS_SOLO_NETWORKS_ORDER]').val();
            $('.ets_solo_social_item').each(function () {
                nets.push($(this).data('group'));
            });
            if (nets != old_order) {
                $('input[id^=ETS_SOLO_NETWORKS_ORDER]').val(nets);
                if (!$('#module_form').hasClass('active') && !$('#ets_solo_panel').hasClass('active')) {
                    $('#module_form, #ets_solo_panel').addClass('active');
                    var formData = new FormData($('#module_form').get(0));
                    $.ajax({
                        url: $('#module_form').attr('action') + '&ajax=1',
                        data: formData,
                        type: 'post',
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (json) {
                            $('#module_form, #ets_solo_panel').removeClass('active');
                            if (json) {
                                if (json.hasError)
                                    showErrorMessage(json.msg);
                                else
                                    showSuccessMessage(json.msg);
                            }
                        },
                        error: function () {
                            $('#module_form, #ets_solo_panel').removeClass('active');
                        }
                    });
                }
                return false;
            }
        }
    },
    changeFilterDate: function (selector) {
        if (selector.length > 0) {
            if (selector.val() == '')
                $('#months option[value=""]').prop('selected', true);
        }
    },
    changeHooks: function (selector) {
        if (selector.length > 0 && selector.val() == 'custom')
            selector.parents('.row_hook').find('p.help-block').show();
        else
            selector.parents('.row_hook').find('p.help-block').hide();

    },
    changeDisOpt: function () {
        if ($('#ETS_SOLO_DISCOUNT_ENABLED_on').length > 0) {
            if ($('#ETS_SOLO_DISCOUNT_OPTION_fixed').is(':checked')) {
                $('.row_ets_solo_discount_code').show();
                $('.row_ets_solo_apply_discount, .row_ets_solo_apply_discount_in, .row_ets_solo_discount_name,.row_ets_solo_discount_prefix,.row_ets_solo_free_shipping').hide();
            } else {
                $('.row_ets_solo_discount_code').hide();
                $('.row_ets_solo_apply_discount, .row_ets_solo_apply_discount_in, .row_ets_solo_discount_name,.row_ets_solo_discount_prefix,.row_ets_solo_free_shipping').show();
            }
            if (!$('#ETS_SOLO_DISCOUNT_OPTION_auto').is(':checked')) {
                $('input[name=ETS_SOLO_APPLY_DISCOUNT][value=off]').prop('checked', true).trigger('change');
            } else {
                $('input[name=ETS_SOLO_APPLY_DISCOUNT]').trigger('change');
            }
        }
    },
    changeApDis: function () {
        if ($('#ETS_SOLO_DISCOUNT_ENABLED_on').length > 0) {
            if ($('#ETS_SOLO_APPLY_DISCOUNT_percent').is(':checked')) {
                $('.row_ets_solo_reduction_percent').show();
                $('.row_ets_solo_reduction_amount,.row_ets_solo_id_currency,.row_ets_solo_reduction_tax').hide();
            } else if ($('#ETS_SOLO_APPLY_DISCOUNT_amount').is(':checked')) {
                $('.row_ets_solo_reduction_amount,.row_ets_solo_id_currency,.row_ets_solo_reduction_tax').show();
                $('.row_ets_solo_reduction_percent').hide();
            } else {
                $('.row_ets_solo_reduction_percent,.row_ets_solo_reduction_amount,.row_ets_solo_id_currency,.row_ets_solo_reduction_tax').hide();
            }
        }
    },
    changeSendDis: function () {
        if ($('#ETS_SOLO_DISCOUNT_ENABLED_on').length > 0) {
            if ($('#ETS_SOLO_SEND_DISCOUNT option:selected').val() == 'email') {
                $('.row_ets_solo_email_subject, .row_ets_solo_email_content').show();
                $('.row_ets_solo_popup_title, .row_ets_solo_popup_content').hide();
            } else if ($('#ETS_SOLO_SEND_DISCOUNT option:selected').val() == 'popup') {
                $('.row_ets_solo_email_subject, .row_ets_solo_email_content').hide();
                $('.row_ets_solo_popup_title, .row_ets_solo_popup_content').show();
            } else {
                $('.row_ets_solo_email_subject, .row_ets_solo_email_content, .row_ets_solo_popup_title, .row_ets_solo_popup_content').show();
            }
        }
    },
    autoLoadPreview: function () {
        if ($('#ets_solo_panel').length > 0 && $('.ets_solo_social_wrapper.admin').length > 1) {
            $('.ets_solo_social_wrapper.admin:not(:first)').remove();
        }
        if ($('.ets_solo_sub_item.active').length > 0) {
            $('.ets_solo_sub_item.active').each(function () {
                if (!$('.ets_solo_social_item[data-group=' + $(this).data('group') + ']').hasClass('active'))
                    $('.ets_solo_social_item[data-group=' + $(this).data('group') + ']').addClass('active');
            });
        }
    },
    customHook: function () {
        if ($('#ETS_SOLO_DISPLAY_SOCIAL_PAGE_custom').length > 0 && $('.row_ets_solo_display_social_page').length > 0) {
            if ($('#ETS_SOLO_DISPLAY_SOCIAL_PAGE_custom').is(':checked'))
                $('.row_ets_solo_display_social_page').find('.help-block').show();
            else
                $('.row_ets_solo_display_social_page').find('.help-block').hide();
        }
    },
    changeTypeBtn: function (loaded) {
        var thisVal = $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE]').length > 0 ? $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE]').val() : false;
        if (thisVal && thisVal != 'img') {
            $('*[class*="row_ets_solo_border"], *[class*="row_ets_solo_button_size"]').show();
        } else {
            $('*[class*="row_ets_solo_border"], *[class*="row_ets_solo_button_size"]').hide();
        }
        if (thisVal) {
            if (thisVal == 'custom') {
                $('.form-group-wrapper.ets_solo_custom').show();
            } else {
                $('.form-group-wrapper.ets_solo_custom').hide();
            }
            if (!loaded && $('.ets_solo_btn').length > 0 && $('.ets_solo_btn.' + thisVal).length > 0) {
                $('.ets_solo_btn').hide();
                $('.ets_solo_btn.' + thisVal).show();
            }
            if (!loaded && $('.ets_solo_social_btn').length > 0 && $('.ets_solo_social_item').length > 0) {
                var optVal = '', parVal = '', tmpVal = '';
                $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE] option').each(function () {
                    tmpVal = $(this).val() + ' ';
                    optVal += tmpVal;
                    parVal += 'item_type_' + tmpVal;
                });
                $('.ets_solo_social_btn').removeClass(optVal).addClass(thisVal);
                $('.ets_solo_social_item').removeClass(parVal).addClass('item_type_' + thisVal);
            }
        }
        if ($('.ets_solo_custom:visible').length > 0) {
            $('input[id^=ETS_SOLO_][id*=_TITLE_],textarea[name^=ETS_SOLO_ADDITIONAL_DESC]').change();
        }
    },
    copyToClipboard: function (el) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(el.text()).select();
        document.execCommand("copy");
        $temp.remove();
        el.append('<span class="copied_text">Copied</span>');
        setTimeout(function () {
            el.removeClass('copy');
            $('.copied_text').remove();
        }, 800);
    },
    googleTheme: function () {
        if ($('select[id^=ETS_SOLO_SOCIAL_TO_USE] > option[value=gl]:selected').length > 0 || $('select[id^=ETS_SOLO_SOCIAL_TO_USE] > option[value=all]:selected').length > 0) {
            $('input[name=ETS_SOLO_GOOGLE_THEME_TRP]').closest('.form-group-wrapper').show();
        } else {
            $('input[name=ETS_SOLO_GOOGLE_THEME_TRP]').closest('.form-group-wrapper').hide();
        }
    },
    configPanel: function () {
        var wrapper = document.getElementById('etssolo-admin');
        if (!wrapper) {
            return;
        }
        var content = wrapper.parentElement,
            pageHead = wrapper.parentElement.querySelector('.bootstrap > .page-head');
        if (!content || !pageHead) {
            return;
        }
        var cpTop = 105;
        try {
            cpTop = parseInt(getComputedStyle(content)['padding-top'], 10);
        } catch (e) {
            cpTop = 105;
        }
        var menuOffset = $(pageHead).offset().top + $(pageHead).outerHeight() - $(window).scrollTop();
        var offset = $(content).offset().top + cpTop - menuOffset;
        wrapper.style.setProperty('--etssolo-wrapper-offset-top', offset + 'px');
        wrapper.style.setProperty('--etssolo-menu-offset-top', menuOffset + 'px');
    }
}

$(document).ready(function () {
    var panelResizeTimeout = 150;
    $('.solo_admin_wrapper table .datepicker:not(.hasEtsDatepicker)').datepicker({
        prevText: '',
        nextText: '',
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd'
    }).addClass('hasEtsDatepicker');
    $(document).on('click', '.ets_solo_pink', function (e) {
        solo_func.copyToClipboard($(this));
    });
    $('.panel-heading-action .list-toolbar-btn').hover(function () {
        $('.solo_admin_tabs').toggleClass('active');
    });
    $('body .filter.row_hover').find('input').attr('autocomplete', 'off');
    $(document).ajaxComplete(function (e) {
        $('body .filter.row_hover').find('input').attr('autocomplete', 'off');
    });
    $('body').removeClass('ets_has_noti');

    if ($('.bootstrap > div').hasClass('alert-success')) {
        $('body').addClass('ets_has_noti');
    }
    setTimeout(function () {
        $('div.alert-success').show();
    }, 800);
    $(".bootstrap > div.alert-success button").on("click", function () {
        if ($('body').hasClass('ets_has_noti')) {
            $('body').removeClass('ets_has_noti');
        }
    });
    var block_menu_height = $('.solo_admin_tabs').height();
    if ( $('div#content.bootstrap').length > 0){
        $('div#content.bootstrap').prepend('<div class="solo_admin_tabs_height" style="height:'+block_menu_height+'"></div>');
    } else {
        $('#etssolo-admin').prepend('<div class="solo_admin_tabs_height" style="height:'+block_menu_height+'"></div>');
    }

    $(window).resize(function (e) {
        var block_left_height = $('.solo_admin_tabs').height();
        if ( $('.solo_admin_tabs_height').length > 0 ){
            $('.solo_admin_tabs_height').css('height',block_left_height);
        }else{
            if ( $('div#content.bootstrap').length > 0){
                $('div#content.bootstrap').prepend('<div class="solo_admin_tabs_height" style="height:'+block_left_height+'"></div>');
            } else {
                $('#etssolo-admin').prepend('<div class="solo_admin_tabs_height" style="height:'+block_left_height+'"></div>');
            }
        }


        clearTimeout(panelResizeTimeout);
        panelResizeTimeout = setTimeout(function() {
            solo_func.configPanel();
        }, 150);
    });
    setTimeout(function() {
        solo_func.configPanel();
    }, 100);
    ets_solo_init();
    change_google_type();
    if ( $('.ets_solo_social').length > 0 ){
        solo_func.buttonSort('.ets_solo_social');
    }

    solo_func.changeHooks($('#hook'));
    solo_func.autoLoadPreview();
    solo_func.customHook();
    solo_func.changeTypeBtn(true);

    //Event.
    //update code.
    function change_google_type() {
        $('input#google_theme_light').on('click',function(e){
            $('.ets_solo_social_item.google_new_desginer').removeClass('dark').addClass('light');
        });
        $('input#google_theme_dark').on('click',function(e){
            $('.ets_solo_social_item.google_new_desginer').removeClass('light').addClass('dark');
        });
    }

    function ets_solo_init() {
        if ($('.solo_admin_social_networks.active').length > 0 && !$('body.' + $('.solo_admin_social_networks.active').data('tab')).length) {
            $('body').addClass($('.solo_admin_social_networks.active').data('tab'));
        }
        if ($('.ets_solo_sub_item .ets_solo_switch').length > 0) {
            $('.ets_solo_switch').each(function () {
                if ($('.ets_solo_form_item.' + $(this).data('group') + ' input[id^=ETS_SOLO_][id$=_APP_ID]').val() && $('.ets_solo_form_item.' + $(this).data('group') + ' input[id^=ETS_SOLO_][id$=_APP_SECRET]').val()) {
                    $(this).addClass('valid');
                }
            });
        }
    }

    function ets_sl_switch_change(button) {
        if (button.length && $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').length > 0) {
            button.hasClass('active') ? button.removeClass('active') : button.addClass('active');
            $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').val(button.hasClass('active') ? 1 : 0);
        }
    }

    $('.ets_solo_switch').click(function (evt) {
        evt.stopPropagation();
        var button = $(this);
        if (!button.hasClass('loading') && button.hasClass('valid') && button.data('group') != '' && $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').length > 0) {
            button.addClass('loading');
            ets_sl_switch_change(button);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: {
                    ETS_SOLO_ENABLED: 1,
                    group: $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').attr('id'),
                    enabled: button.hasClass('active') ? 1 : 0,
                },
                success: function (json) {
                    button.removeClass('loading');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.msg);
                            ets_sl_switch_change(button);
                        } else {
                            showSuccessMessage(json.msg);
                        }
                    }
                },
                error: function () {
                    button.removeClass('loading');
                    ets_sl_switch_change(button);
                }
            });
        }
    });

    //submit form social networks.
    $('button[name=saveSocial_networks], input[name=saveSocial_networks]').click(function (evt) {
        evt.preventDefault();
        var button = $(this),
            form = button.parents('form'),
            postItem = $('.ets_solo_form_item.active');
        if (!button.hasClass('active') && postItem.length > 0) {
            button.addClass('active');
            $('.ets_solo_form_item .bootstrap').remove();
            var postForm = {},
                enabled = form.find('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + postItem.data('social') + ']');
            //all item not radio.
            postItem.find('input:not(:radio)').each(function () {
                postForm[$(this).attr('name')] = $(this).val();
            });
            //filter item radio.
            postItem.find('input:radio:checked').each(function () {
                postForm[$(this).attr('name')] = $(this).val();
            });
            postForm[enabled.attr('name')] = enabled.val();

            if (postItem.find('.row_ets_solo_paypal_sandbox_mode').length > 0) {
                postForm['ETS_SOLO_PAYPAL_SANDBOX_MODE'] = $('#ETS_SOLO_PAYPAL_SANDBOX_MODE_on:checked').length > 0 ? 1 : 0;
            }
            postForm[button.attr('name')] = 1;
            $.ajax({
                url: form.attr('action'),
                data: postForm,
                type: 'post',
                dataType: 'json',
                success: function (json) {
                    button.removeClass('active');
                    if (json) {
                        if (json.errors) {
                            postItem.find('h3').after(json.errors);
                        } else if (json.msg) {
                            showSuccessMessage(json.msg);
                            if (postItem.data('group') != '' && $('.ets_solo_switch[data-group=' + postItem.data('group') + '].valid').length <= 0) {
                                $('.ets_solo_switch[data-group=' + postItem.data('group') + ']').addClass('active valid');
                            }
                        }
                    }
                },
                error: function () {
                    button.removeClass('active');
                }
            });
        }
    });

    function ets_solo_social_networks(button) {
        if (button.length > 0 && $('.ets_solo_form input[type=hidden]').length > 0) {
            $('.ets_solo_sub_item.active, .ets_solo_form_item.active').removeClass('active');
            button.addClass('active');
            $('.ets_solo_form_item.' + button.data('group')).addClass('active');
            if (!$('.ets_solo_form_item.active input[id^=ETS_SOLO_][id$=_APP_ID]').val() || !$('.ets_solo_form_item.active input[id^=ETS_SOLO_][id$=_APP_SECRET]').val()) {
                $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').val(1);
            }
        }
    }

    ets_solo_social_networks($('.ets_solo_sub_item').first());
    $('.ets_solo_sub_item').click(function () {
        var button = $(this);
        if (!button.hasClass('active')) {
            if ($('.ets_solo_sub_item.active').length > 0 && (!$('.ets_solo_form_item.active input[id^=ETS_SOLO_][id$=_APP_ID]').val() || !$('.ets_solo_form_item.active input[id^=ETS_SOLO_][id$=_APP_SECRET]').val())) {
                $('input[type=hidden][id^=ETS_SOLO_][id$=_ENABLED][id*=' + button.data('social') + ']').val(0);
            }
            ets_solo_social_networks(button);
        }
    });
    //end update code.
    //end.
    $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE]').change(function () {
        solo_func.changeTypeBtn();
    });
    if ($('#ETS_SOLO_APPLY_DISCOUNT_percent').length > 0)
        solo_func.changeApDis();
    $('input[name=ETS_SOLO_APPLY_DISCOUNT]').change(function () {
        solo_func.changeApDis();
    });
    if ($('#ETS_SOLO_DISCOUNT_OPTION_auto').length > 0)
        solo_func.changeDisOpt();
    $('input[name=ETS_SOLO_DISCOUNT_OPTION]').change(function () {
        solo_func.changeDisOpt();
    });
    if ($('#ETS_SOLO_SEND_DISCOUNT').length > 0)
        solo_func.changeSendDis();
    $('#ETS_SOLO_SEND_DISCOUNT').change(function () {
        solo_func.changeSendDis();
    });
    $('#ETS_SOLO_DISPLAY_SOCIAL_PAGE_custom').click(function () {
        solo_func.customHook();
    });
    $('select[name^=ETS_SOLO_BORDER]').change(function () {
        var optionVals = '';
        $('select[name^=ETS_SOLO_BORDER] option').each(function () {
            optionVals += $(this).val() + ' ';
        });
        $('.ets_solo_social_btn').removeClass(optionVals).addClass($(this).val());

    });
    $('select[name^=ETS_SOLO_BUTTON_SIZE]').change(function () {
        var optionVals = '';
        $('select[name^=ETS_SOLO_BUTTON_SIZE] option').each(function () {
            optionVals += $(this).val() + ' ';
        });
        $('.ets_solo_social_btn').removeClass(optionVals).addClass($(this).val());
    });
    if ($('.defaultForm input[name=saveDesign]').length > 0) {
        $('input[id^=ETS_SOLO_][id*=_TITLE_],textarea[name^=ETS_SOLO_ADDITIONAL_DESC]').change(function () {
            var position = $('.ets_solo_menu_item.active').length > 0 && $('.ets_solo_menu_item.active').data('pos') ? $('.ets_solo_menu_item.active').data('pos') : '';
            var itemId = $(this).attr('name').replace(new RegExp("ETS_SOLO_|_TITLE_|_DESC_|" + position + "_|\\d+", "gi"), '');
            if (itemId == 'ADDITIONAL' && $('.ets_solo_social_desc').length > 0) {
                $('.ets_solo_social_desc').html($(this).val());
            } else if (itemId != 'LOGIN' && itemId != 'ADDITIONAL' && $('.ets_solo_social_item.active[data-auth*="' + itemId + '" i]').length > 0) {
                $('.ets_solo_social_item.active[data-auth*="' + itemId + '" i] .ets_solo_btn.title').html($(this).val());
            } else if (itemId == 'LOGIN') {
                $('.ets_solo_title').html($(this).val());
            }
        });
        $('.dropdown-menu a, .language_flags img').click(function () {
            setTimeout(function () {
                $('.translatable-field, .translatable div[class*=lang_]').each(function () {
                    if ($(this).is(':visible')) {
                        var position = $('.ets_solo_menu_item.active').length > 0 && $('.ets_solo_menu_item.active').data('pos') ? $('.ets_solo_menu_item.active').data('pos') : '';
                        if ($(this).find('input[type=text]').length > 0) {
                            var inputId = $(this).find('input[type=text]').attr('id').replace(new RegExp("ETS_SOLO_|_TITLE_|" + position + "_|\\d+", "gi"), '');
                            var inputVal = $(this).find('input[type=text]').val();
                            if (inputId != 'LOGIN' && $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE]').length > 0 && $('select[name^=ETS_SOLO_LOGIN_BUTTON_TYPE]').val() == 'custom' && $('.ets_solo_social_item.active[data-auth*="' + inputId + '" i]').length > 0) {
                                $('.ets_solo_social_item.active[data-auth*="' + inputId + '" i] .ets_solo_btn.title').html(inputVal);
                            } else if (inputId == 'LOGIN') {
                                $('.ets_solo_title').html(inputVal);
                            }
                        } else if ($('.ets_solo_social_desc').length > 0 && $(this).find('textarea').length > 0 && $(this).find('textarea[name*=ETS_SOLO_ADDITIONAL_DESC_' + position + ']').length > 0) {
                            $('.ets_solo_social_desc').html($(this).find('textarea').val());
                        }
                    }
                });
            }, 300);
        });
    }
    $('#hook').on('change', function () {
        solo_func.changeHooks($(this));
    });
    $('#years').on('change', function () {
        solo_func.changeFilterDate($(this));
    });
    $('#months').on('change', function () {
        solo_func.changeFilterDate($('#years'));
    });
    //discount.
    if ($('select[name="ETS_SOLO_DISCOUNT_NETWORKS[]"] option[value="all"]').is(':selected'))
        $('select[name="ETS_SOLO_DISCOUNT_NETWORKS[]"] option').prop('selected', true);
    $('select[name="ETS_SOLO_DISCOUNT_NETWORKS[]"]').change(function () {
        if ($(this).val() && ($(this).val().indexOf('all') !== -1 || ($(this).val().length == $('select[name="ETS_SOLO_DISCOUNT_NETWORKS[]"] option').length - 1)))
            $('select[name="ETS_SOLO_DISCOUNT_NETWORKS[]"] option').prop('selected', true);
    });
    //function new.
    //social to use with hook.
    if ($('select[id^=ETS_SOLO_SOCIAL_TO_USE] option[value="all"]').is(':selected'))
        $('select[id^=ETS_SOLO_SOCIAL_TO_USE] option').prop('selected', true);
    ets_solo_social_networks_to_use();
    solo_func.googleTheme();
    $('select[id^=ETS_SOLO_SOCIAL_TO_USE]').change(function () {
        solo_func.googleTheme();
        if ($(this).val().indexOf('all') !== -1 || ($(this).val().length == $('select[id^=ETS_SOLO_SOCIAL_TO_USE] option').length - 1))
            $('select[id^=ETS_SOLO_SOCIAL_TO_USE] option').prop('selected', true);
        ets_solo_social_networks_to_use();
    });

    function ets_solo_social_networks_to_use() {
        if ($('select[id^=ETS_SOLO_SOCIAL_TO_USE]').length > 0 && $('select[id^=ETS_SOLO_SOCIAL_TO_USE]').val()) {
            $('.ets_solo_social_item').each(function () {
                if ($('select[id^=ETS_SOLO_SOCIAL_TO_USE]').val() && $('select[id^=ETS_SOLO_SOCIAL_TO_USE]').val().indexOf($(this).data('group')) !== -1)
                    $(this).addClass('active');
                else
                    $(this).removeClass('active');
            });
        }
    }

    //new.
    $('.ets_solo_position').click(function (evt) {
        evt.stopPropagation();
        var button = $(this), positions = [];
        if (!button.hasClass('loading') && button.data('pos')) {
            button.addClass('loading');
            button.hasClass('active') ? button.removeClass('active') : button.addClass('active');
            $('.ets_solo_position.active').each(function () {
                positions.push($(this).data('pos'));
            });
            $.ajax({
                data: {
                    positions: positions.join(),
                },
                type: 'post',
                dataType: 'json',
                success: function (json) {
                    button.removeClass('loading');
                    if (json) {
                        if (json.errors) {
                            showErrorMessage(json.msg);
                            button.hasClass('active') ? button.removeClass('active') : button.addClass('active');
                        } else {
                            showSuccessMessage(json.msg);
                            if (json.positions && $('#ETS_SOLO_DISPLAY_SOCIAL_PAGE').length > 0) {
                                $('#ETS_SOLO_DISPLAY_SOCIAL_PAGE').val(json.positions);
                            }
                        }
                    }
                },
                error: function () {
                    button.removeClass('loading');
                    button.hasClass('active') ? button.removeClass('active') : button.addClass('active');
                }
            });
        }
    });

    $(document).on('change', 'input[name=ETS_SOLO_PAYPAL_SANDBOX_MODE]', function (ev) {
        ev.preventDefault();
        if ($(this).val() <= 0 && $('.ets_solo_paypal_popup').length > 0 && $('.ets_solo_paypal_popup.active').length <= 0) {
            $('.ets_solo_paypal_popup').addClass('active');
        }
    });
    $(document).on('click', '.ets_solo_paypal_btn_ok', function () {
        if ($('.ets_solo_paypal_popup.active').length > 0) {
            ets_solo_paypal_live_mode(true);
        }
    });
    $(document).on('click', '.ets_solo_paypal_btn_no, .ets_solo_paypal_close', function () {
        if ($('.ets_solo_paypal_popup.active').length > 0) {
            ets_solo_paypal_live_mode(false);
        }
    });

    function ets_solo_paypal_live_mode($live) {
        $('#ETS_SOLO_PAYPAL_SANDBOX_MODE_on').prop('checked', !$live);
        $('#ETS_SOLO_PAYPAL_SANDBOX_MODE_off').prop('checked', $live);
        $('.ets_solo_paypal_popup.active').removeClass('active');
    }

    $(document).keyup(function (e) {
        if (e.keyCode == 27) {
            ets_solo_paypal_live_mode(false);
        }
    });
    $(document).mouseup(function (e) {
        var paypal_popup = $('.ets_solo_paypal_popup.active');
        if (paypal_popup.length > 0 && !paypal_popup.is(e.target) && paypal_popup.has(e.target).length === 0) {
            ets_solo_paypal_live_mode(false);
        }
    });
    $(document).on('submit', '#form-ets_solo_connect', function (evt) {
        var form = $(this);
        if (!form.hasClass('active')) {
            form.addClass('active');
            var formData = new FormData(form.get(0));
            $.ajax({
                url: form.attr('action'),
                data: formData,
                type: 'POST',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (json) {
                    form.removeClass('active');
                    if (json) {
                        $('.ets_solo_list').html(json.html);
                    }
                },
                error: function () {
                    form.removeClass('active');
                }
            });
        }
        evt.preventDefault();
    });
    $(document).on('click', '#nav-sidebar .menu-collapse, .lc_move_chat_window, .lc_list_customer_chat_small_bubble', function () {
        setTimeout(function () {
            ets_solo_stats_init_chart();
        }, 500);
    });
    $(document).on('click', '.ets_solo_callback_url', function () {
        var range, selection;
        if (window.getSelection && document.createRange) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents($(this)[0]);
            selection.removeAllRanges();
            selection.addRange(range);
        } else if (document.selection && document.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText($(this)[0]);
            range.select();
        }
        document.execCommand('copy');
        if ($(this).data('msg'))
            showSuccessMessage($(this).data('msg'), 1500);
    });
    ets_solo_stats_init_chart();

    function ets_solo_stats_init_chart() {
        ets_solo_stats_pie_chart();
        ets_solo_stats_line_chart();
        ets_solo_stats_bar_chart();
    }

    //pie.
    function ets_solo_stats_pie_chart() {
        if (typeof ets_solo_pie_chart !== "undefined") {
            nv.addGraph(function () {
                var pie_chart = nv.models.pieChart()
                    .x(function (d) {
                        return d.label
                    })
                    .y(function (d) {
                        return d.value
                    })
                    .showLabels(true)
                    .labelThreshold(.05)
                    .labelType("percent")
                    .pieLabelsOutside(false)
                    .tooltipContent(
                        function (key, y, e, graph) {
                            return '<h3>' + key + '</h3>'
                        }
                    );
                d3.select(".pie_chart svg")
                    .datum(ets_solo_pie_chart)
                    .transition().duration(300)
                    .call(pie_chart);

                nv.utils.windowResize(pie_chart.update);
                pie_chart.update();
                return pie_chart;
            });
        }
    }

    //line.
    function ets_solo_stats_line_chart() {
        if (typeof ets_solo_line_chart !== "undefined") {
            var slLabel = ets_solo_x_days;
            if ($('#months').length > 0 && $('#months').val() == '' && $('#years').length > 0 && $('#years').val() != '')
                slLabel = ets_solo_x_months;
            else if ($('#years').length > 0 && $('#years').val() == '')
                slLabel = ets_solo_x_years;
            nv.addGraph(function () {
                var line_chart = nv.models.lineChart()
                    .useInteractiveGuideline(true)
                    .x(function (d) {
                        return (d !== undefined ? d[0] : 0);
                    })
                    .y(function (d) {
                        return (d !== undefined ? parseInt(d[1]) : 0);
                    })
                    .margin({left: 80})
                    .showLegend(true)
                    .showYAxis(true)
                    .showXAxis(true);
                line_chart.xAxis
                    .axisLabel(slLabel)
                    .tickFormat(d3.format('d'));
                line_chart.yAxis
                    .axisLabel(ets_solo_y_label)
                    .tickFormat(d3.format('d'));
                d3.select('.line_chart svg')
                    .datum(ets_solo_line_chart)
                    .transition().duration(500)
                    .call(line_chart);

                nv.utils.windowResize(line_chart.update);

                return line_chart;
            });
        }
    }

    //bar.
    function ets_solo_stats_bar_chart() {
        if (typeof ets_solo_bar_chart != "undefined") {
            var ctx = $('.solo_admin_bar_chart .bar_chart canvas');
            var bar_chart = new Chart(ctx, {
                type: 'horizontalBar',
                data: ets_solo_bar_chart,
                options: {
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                        }
                    },
                    responsive: true,
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false,
                    },
                    scales: {
                        xAxes: [{
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    if (value % 1 === 0) {
                                        return value;
                                    }
                                }
                            },
                        }],
                        yAxes: [{
                            maxBarThickness: 25,
                            minBarLength: 20,
                            gridLines: {
                                offsetGridLines: true
                            }
                        }]
                    },
                    elements: {
                        rectangle: {
                            borderSkipped: 'left',
                        }
                    }
                }
            });
            return bar_chart;
        }
    }

    //dashboard chart.
    if ($('#ets_solo_dashboard_chart').length > 0 && typeof ets_solo_chart_data_sets !== "undefined" && typeof ets_solo_chart_labels !== "undefined") {
        var ets_solo_line_chart_created = ets_sl_createDashboardChart(
            $('#ets_solo_dashboard_chart'),
            ets_solo_chart_data_sets,
            ets_solo_chart_labels,
            ets_solo_chart_title,
            ets_solo_chart_label_x,
            ets_solo_chart_label_y,
            ets_solo_y_max_value
        );
        $(document).on('click', '.ets_solo_time_item a', function (evt) {
            evt.preventDefault();
            var button = $(this), prevItemSelected = $('li.ets_solo_time_item.active');
            if (!button.hasClass('active') && !button.parents('li').hasClass('active')) {
                button.addClass('active');
                prevItemSelected.removeClass('active');
                button.parents('li').addClass('active');
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        updateChart: 1,
                        month: button.data('month'),
                        year: button.data('year'),
                    },
                    success: function (json) {
                        button.removeClass('active');
                        if (json) {
                            ets_sl_updateDashboardChart(
                                ets_solo_line_chart_created,
                                json.datasets,
                                json.labels,
                                json.title,
                                json.labelX,
                                json.labelY,
                                json.y_max_value
                            );
                        }
                    },
                    error: function () {
                        button.removeClass('active');
                    }
                });
            }
            return false;
        });
    }

    //create chart.
    function ets_sl_createDashboardChart(cavans, datasets, labels, title, labelX, labelY, y_max_value) {
        return new Chart(cavans, {
            type: 'line',
            data: {
                datasets: datasets,
                labels: labels,
            },
            spanGaps: true,
            options: {
                responsive: true,
                title: {
                    text: title,
                    position: 'top',
                },
                scales: {
                    xAxes: [{
                        scaleLabel: {
                            labelString: labelX
                        },
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0,
                            max: y_max_value,
                            callback: function (value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        },
                        scaleLabel: {
                            labelString: labelY
                        },
                    }]
                },
                legend: {
                    fullWidth: true,
                    position: 'bottom',
                },
                layout: {
                    padding: {
                        left: 50,
                        right: 50,
                        top: 0,
                        bottom: 50
                    }
                },
                tooltips: {
                    mode: 'point',
                    intersect: true,
                }
            }
        });
    }

    //update chart.
    function ets_sl_updateDashboardChart(chart, datasets, labels, title, labelX, labelY, y_max_value) {
        if (labels)
            chart.data.labels = labels;
        if (datasets)
            chart.data.datasets = datasets;
        if (title)
            chart.options.title.text = title;
        var scales = [];
        if (labelX)
            chart.options.scales.xAxes = [{
                scaleLabel: {
                    labelString: labelX
                },
            }];
        if (labelY)
            chart.options.scales.yAxes = [{
                ticks: {
                    min: 0,
                    max: y_max_value,
                    callback: function (value) {
                        if (value % 1 === 0) {
                            return value;
                        }
                    }
                },
                scaleLabel: {
                    labelString: labelY
                },
            }];
        chart.update();
    }
});