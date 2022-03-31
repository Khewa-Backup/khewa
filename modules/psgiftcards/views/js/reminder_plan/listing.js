/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

/**
 * Load more informations about the reminder on click
 */
let onClickListingRow4Child = function(event) {
    let tagSource = $(event.target).prop("tagName");
    let id = $(event.target).parent().parent().attr('data-id');

    if (tagSource == 'U' || tagSource == 'I') {
        id = $(event.target).parent().parent().parent().attr('data-id');
    }

    let elemAlreayExists = $('#'+id+'_more_info .reminder_info').length;
    let alreadyVisible = $('#'+id+'_more_info').hasClass('active');

    if (alreadyVisible) {
        $('.show-more').html('keyboard_arrow_right');
        $('#'+id+'_more_info').removeClass('active').slideUp();
        return true;
    }

    if (!elemAlreayExists) {
        let cap_controller = getControllerName('listing');
        let cap_controller_url = getControllerUrl('listing');

        $.ajax({
            type: 'POST',
            url: cap_controller_url,
            dataType: 'html',
            async: false,
            data: {
                controller : cap_controller,
                action : 'loadReminderInfos',
                id : id,
                ajax : true,
            },
            success : (data) => {
                $('#'+id+'_more_info.ajax_return').html(data);
            }
        });
    }

    $('.listing-general-rol').removeClass('active');
    $(event.target).parent().parent().addClass('active');

    $('.show-more').html('keyboard_arrow_right');
    $(event.target).parent().find('.show-more').html('keyboard_arrow_down');
    $('.more_info').removeClass('active').slideUp();
    $('#'+id+'_more_info').addClass('active');
    $('#'+id+'_more_info').slideDown();
};
$(document).on('click', '.listing-row div:nth-child(-n+4)', onClickListingRow4Child);
/**
 *  Delete reminder
 */

$(document).on('click', '.reminder_more_actions .delete', (e) => {
    let reminderId = $(e.target).parent().attr('data-cart_reminder_id');

    $('#delete_reminder .buttons').show();
    $('#delete_reminder button[name="confirm"]').attr('data-delete_id', reminderId);
    $('#delete_reminder').fadeIn(300);
})

$(document).on('click', '#delete_reminder button', (e) => {
    let action = $(e.target).attr('name');

    if (action == 'cancel') {
        $('#delete_reminder').fadeOut(300);
    } else {
        let reminderId = $(e.target).attr('data-delete_id');
        let cap_controller = getControllerName('listing');
        let cap_controller_url = getControllerUrl('listing');

        $.ajax({
            type: 'POST',
            url: cap_controller_url,
            dataType: 'html',
            async: false,
            data: {
                controller : cap_controller,
                action : 'deleteReminder',
                id : reminderId,
                ajax : true,
            },
            success : (data) => {
                $('#delete_reminder .buttons').hide();

                if (data == 0) {
                    $('#delete_reminder .ajax_return .error').show();
                } else {
                    $('#delete_reminder .ajax_return .success').show();
                }

                // The popin disapear after 1.5secondes then the template is refreshed
                setTimeout(() => {
                    $('#delete_reminder .ajax_return .error').hide();
                    $('#delete_reminder .ajax_return .success').hide();
                    $('#delete_reminder').fadeOut(300);

                    if (data != 0) {
                        ajaxLoadStepTemplate('listing');
                    }
                }, 1500);
            }
        });
    }
})

/**
 *  Set reminder active or inactive
 */

$(document).on('click', '.listing-row .switch-input', (e) => {
    let status = 1;
    let reminderId = $(e.target).parent().attr('data-cart_reminder_id');

    if ($(e.target).hasClass('-checked'))
        status = 0;

    $.ajax({
        type: 'POST',
        url: cap_controller_listing_url,
        dataType: 'html',
        async: false,
        data: {
            controller : cap_controller_listing,
            action : 'changeReminderStatus',
            id : reminderId,
            status : status,
            ajax : true,
        },
        success : (data) => {
            $.growl.notice({
                title: "",
                size: "large",
                message: reminder_updated
            });
        },
        error : (data) => {
            $.growl.error({
                title: "",
                size: "large",
                message: active_error
            });
        }
    });
})

// Email preview
$(document).on('click', '.reminder_more_actions .preview', (e) => {
    preloadCart();
    let reminderId = $(e.target).parent().attr('data-cart_reminder_id');
    let cap_controller = getControllerName('listing');
    let cap_controller_url = getControllerUrl('listing');

    $.ajax({
        type: 'POST',
        url: cap_controller_url,
        dataType: 'html',
        data: {
            controller : cap_controller,
            action : 'previewTemplate',
            id : reminderId,
            ajax : true,
        },
        success : (html) => {
            $('#preview').fadeIn(300);
            $('#preview .show_template').html(html);
            // Load content template text
            let demoThatData = $('#preview .show_template .cap-email_custom_content_here').html();
            setPreviewCustomText('.cap-email_custom_content_here', demoThatData);
            // Load discount template text
            let demoThatDiscount = $('#preview .show_template .cap-email_discount_content_here').html();
            setPreviewCustomText('.cap-email_discount_content_here', demoThatDiscount);
            // Load unsubscribe template text
            let demoThatUnsubscribe = $('#preview .show_template .cap-email_preview_unsubscribe').html();
            setPreviewCustomText('.cap-email_preview_unsubscribe', demoThatUnsubscribe);
        }
    });
});

/**
 *  Switch management
 */

$(document).on('click', '.switch-input', (e) => {
    let switchIsOn = $(e.target).hasClass('-checked');

    $(e.target).parent().find('.switch_text').hide();

    if (switchIsOn) {
        $('input', e.target).attr('checked', false);
        $(e.target).removeClass('-checked');
        $( e.target).parent().find('.switch-off').show();
    } else {
        $('input', e.target).attr('checked', true);
        $(e.target).addClass('-checked');
        $(e.target).parent().find('.switch-on').show();
    }
})

