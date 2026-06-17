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
var ets_solo_popup = false;
var ets_solo_func = {
    closeForm: function () {
        if ($('.ets_solo_popup.actived').length > 0)
            $('.ets_solo_popup.actived').remove();
    },
    popupForm: function () {
        setTimeout(function () {
            if ($('.ets_solo_popup.actived').length <= 0) {
                $('.ets_solo_popup').addClass('actived');
            }
        }, 3000);
    },
    authPopup: function (provider) {
        if (typeof ets_solo_callbackUrl !== typeof undefined && ets_solo_callbackUrl !== '' && typeof provider !== typeof undefined && provider != '') {
            var callbackURL = ets_solo_callbackUrl;
            if (ets_solo_callbackUrl.indexOf('?') !== -1) {
                callbackURL += '&provider=' + provider
            } else {
                callbackURL += '?provider=' + provider
            }
            if (callbackURL)
                window.open(callbackURL + '&currentUrl=' + window.location.href, '_parent', 'width=800,height=auto,scrollbars=yes');//authWindow
            return false;
        }
    },
    formAccount: function (type) {
        if (!type)
            return false;
        switch (type) {
            case 'close':
                $('.ets_solo_social_wrapper.alw.clone').removeClass('active');
                $('body').removeClass('ets_solo_loading');
                break;
            case 'active':
                $('.ets_solo_social_wrapper.alw.clone').addClass('active');
                $('body').addClass('ets_solo_loading');
                break;
        }
    },
    autoLoad: function () {
        if ($('.ets_solo_social_wrapper.alw').length > 0 && ($('.header_user_info').length > 0 || $('#_desktop_user_info, #_mobile_user_info, #_desktop_ps_customersignin, #_mobile_ps_customersignin').length > 0 || $('#header_user_info').length > 0) && !$('.ets_solo_social_wrapper.alw.clone').length) {
            $('.header_user_info, #_desktop_user_info,#_desktop_ps_customersignin, #_mobile_ps_customersignin, #_mobile_user_info, #header_user_info').append($('.ets_solo_social_wrapper.alw').clone().addClass('clone'));
            if ($('.header_user_info .ets_solo_social_wrapper.alw').length > 0 || $('#_desktop_ps_customersignin .ets_solo_social_wrapper.alw, #_mobile_ps_customersignin .ets_solo_social_wrapper.alw').length > 0 || $('#_desktop_user_info .ets_solo_social_wrapper.alw, #_mobile_user_info .ets_solo_social_wrapper.alw').length > 0 || $('#header_user_info .ets_solo_social_wrapper.alw').length > 0) {
                $('.ets_solo_social_wrapper.alw').not('.clone').remove();
                $('.ets_solo_social_wrapper.hover.alw.clone').parent().addClass('hover');
            }
        }
    },
    loginPageCheckout: function () {
        if ($('#checkout').length > 0 && $('#checkout-login-form #login-form').length > 0 && $('.ets_solo_social_wrapper.lgp').length > 0 && !$('.ets_solo_social_wrapper.lgp.clone').length) {
            $('#login-form section').append($('.ets_solo_social_wrapper.lgp').clone().addClass('clone'));
            if ($('#login-form .ets_solo_social_wrapper.lgp.clone').length > 0)
                $('.ets_solo_social_wrapper.lgp:not(.clone)').remove();
        }
    }
};
$(document).ready(function () {
    ets_solo_func.autoLoad();
    ets_solo_func.loginPageCheckout();
    $(document).on('click', '.nav-link.active', function () {
        ets_solo_func.loginPageCheckout();
    });
    $(document).on('hover', '.header_user_info, #_desktop_user_info, #_mobile_user_info, #header_user_info', function () {
        ets_solo_func.autoLoad();
    });
    if ($('.ets_solo_popup').length > 0) {
        ets_solo_func.popupForm();
    }
    $('.ets_solo_popup_close, .ets_solo_popup_close_overlay').click(function () {
        ets_solo_func.closeForm();
    });
    $('.ets_sl_wrapper').click(function (evt) {
        evt.stopPropagation();
    });
    $(document).on('click', '.solo_discount_code input', function (e) {
        $(this).select();
        var textcopied = $(this).attr('data-copy');
        document.execCommand("copy");
        $(this).parent().append('<span class="text_copied">' + textcopied + '</span>');
        $(this).parent().addClass('copied');

        setTimeout(function () {
            $(this).parent().removeClass('copied');
            $('.text_copied').remove();
        }, 3000);
    });

    function ets_solo_slide_event(button) {
        if (button.hasClass('active')) {
            button.removeClass('active');
            button.parents('.ets_solo_social_wrapper').removeClass('active s1');
            button.parents('.ets_solo_social_wrapper').find('.ets_solo_wrapper_content.slw.active').removeClass('active');
            button.parents('.ets_solo_social_wrapper').find('.ets_solo_slide_widget.active').removeClass('active');

        } else {
            button.addClass('active');
            //$('.ets_solo_wrapper_content.slw, .ets_solo_slide_widget, .ets_solo_social_wrapper').addClass('active');
            button.parents('.ets_solo_social_wrapper').addClass('active s1');
            button.parents('.ets_solo_social_wrapper').find('.ets_solo_wrapper_content.slw').addClass('active');
            button.parents('.ets_solo_social_wrapper').find('.ets_solo_slide_widget').addClass('active');
        }
    }

    function ets_solo_slide_event_hover(button) {
        if (button.hasClass('active')) {
            button.removeClass('active');
            button.removeClass('active');
            button.find('.ets_solo_wrapper_content.slw.active').removeClass('active');
            button.find('.ets_solo_slide_widget.active').removeClass('active');

        } else {
            button.addClass('active');
            //$('.ets_solo_wrapper_content.slw, .ets_solo_slide_widget, .ets_solo_social_wrapper').addClass('active');
            button.addClass('active');
            button.find('.ets_solo_wrapper_content.slw').addClass('active');
            button.find('.ets_solo_slide_widget').addClass('active');
        }
    }

    $(document).on('click', '.ets_solo_social_wrapper.click.slw .ets_solo_slide_widget', function () {
        ets_solo_slide_event($(this));
    });
    $(document).on('click', '.ets_solo_social_wrapper.slw:not(.click) .ets_solo_slide_widget', function () {
        if ($(window).width() < 1200) {
            ets_solo_slide_event($(this));
        }
    });
    $(window).resize(function () {
        if ($(window).width() > 767) {
            $('.ets_solo_social_wrapper.active, .ets_solo_wrapper_content.active, .ets_solo_slide_widget.active, .ets_solo_wrapper_content.active').removeClass('active');
            $('body.ets_solo_loading').removeClass('ets_solo_loading');
        }
    });
    $(document).on('mouseenter mouseleave', '.ets_solo_social_wrapper.hover.slw', function () {
        if ($(window).width() >= 1200)
            ets_solo_slide_event_hover($(this));
    });
    $(document).mouseup(function (e) {
        if (!$('.ets_solo_popup').is(e.target) && $('.ets_solo_popup').has(e.target).length === 0)
            ets_solo_func.closeForm();

        //if (!$('.ets_solo_wrapper_content.slw').is(e.target) && $('.ets_solo_wrapper_content.slw').has(e.target).length !== 0 && !$('.ets_solo_slide_widget.active').is(e.target))
        //$('.ets_solo_slide_widget.active, .ets_solo_wrapper_content.slw.active, .ets_solo_social_wrapper.active').removeClass('active');

        //if (!$('.ets_solo_social_wrapper.alw.clone').is(e.target) && $('.ets_solo_social_wrapper.alw.clone').has(e.target).length === 0)
        //ets_solo_func.formAccount('close');
    });
    $(document).keyup(function (e) {
        if (e.keyCode === 27) {
            ets_solo_func.closeForm();
            $('.ets_solo_slide_widget.active, .ets_solo_wrapper_content.slw.active, .ets_solo_social_wrapper.active').removeClass('active');
            ets_solo_func.formAccount('close');
        }
    });
    $(document).on('click', '.ets_solo_social_item', function () {
        if ($(this).data('auth') != '') {
            ets_solo_func.authPopup($(this).data('auth'));
        }
    });
    $('button[class^=solo-submit-login]').click(function () {
        if ($('form[class^=solo-login-form]').length > 0) {
            var button = $(this), $HOOK = button.parents('form').data('hook'), form = button.parents('form');
            if (!button.hasClass('active')) {
                button.addClass('active');
                $('.solo-login-form-' + $HOOK + ' .bootstrap, .solo-login-form-' + $HOOK + ' .module_error').remove();
                var formData = new FormData(form.get(0));
                $.ajax({
                    url: form.attr('action'),
                    data: formData,
                    type: 'post',
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: function (json) {
                        button.removeClass('active');
                        if (json) {
                            if (json.hasError) {
                                form.prepend(json.errors);
                            } else {
                                window.location.reload();
                            }
                        }
                    },
                    error: function () {
                        button.removeClass('active');
                    }
                });
            }
        }
        return false;
    });
    $(document).on('focus', '.solo-login-form-alw input', function () {
        if ($('#_desktop_user_info, #_mobile_user_info, .header_user_info, #header_user_info').length > 0) {
            $('#_desktop_user_info, #_mobile_user_info, .header_user_info, #header_user_info').addClass('active');
        }
    });
    $(document).on('focusout', '.solo-login-form-alw input', function () {
        if ($('#_desktop_user_info.active, #_mobile_user_info.active, .header_user_info.active, #header_user_info.active').length > 0) {
            $('#_desktop_user_info.active, #_mobile_user_info.active, .header_user_info.active, #header_user_info.active').removeClass('active');
        }
    });
    $(document).on('click', '.user-info a:not(.logout, .account), .header_user_info a.login, #header_user_info a.login, .ps-customersignin a', function (evt) {
        ets_solo_func.autoLoad();
        if (($(window).width() < 768 && $('.ets_solo_social_wrapper.alw.mobile').length > 0) || $('.ets_solo_social_wrapper.alw.clone.click').length > 0) {
            evt.preventDefault();
            ets_solo_func.formAccount('active');
        }
    });
    $('.ets_solo_account_close').click(function () {
        if ($(this).parents('.ets_solo_social_wrapper').find('.ets_solo_wrapper_content.slw.active').length > 0) {
            $(this).parents('.ets_solo_social_wrapper').find('.ets_solo_slide_widget.active').removeClass('active');
            $(this).parents('.ets_solo_social_wrapper').find('.ets_solo_wrapper_content.slw.active').removeClass('active');
        }

        $(this).parents('.ets_solo_social_wrapper').removeClass('active');

        if (($(window).width() < 768 && $('.ets_solo_social_wrapper.alw.mobile').length > 0) || $('.ets_solo_social_wrapper.alw.clone.click').length > 0)
            ets_solo_func.formAccount('close');
    });

    $(document).on('click', '.ets_solo_wrapper_content.alw', function (e) {
        e.stopPropagation();
    });
});
