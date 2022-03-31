/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

var elegantalFormGroupClass = 'form-group';
var elegantalProcessing = false;
var elegantalAdminUrl = '';

jQuery(document).ready(function () {
    // Identify form group class
    if (jQuery('[type="submit"]').parents('.margin-form').length > 0) {
        elegantalFormGroupClass = 'margin-form';
    }

    // Back button fix on < 1.6.1
    jQuery('.panel-footer button[name="submitOptionsmodule"]').click(function () {
        if (jQuery(this).find('.process-icon-back')) {
            window.location.href = window.location.href.replace(/&event=\w+/gi, '');
        }
    });

    // Short-code popover
    if (jQuery('.elegantaltagpopover').length > 0) {
        jQuery('.elegantaltagpopover').attr('autocomplete', 'off');
        jQuery('.elegantaltagpopover').popover({
            html: true,
            trigger: 'manual',
            placement: 'bottom',
            content: function () {
                return jQuery('.elegantal_shortcodes').html();
            }
        });

        jQuery('.elegantaltagpopover').focus(function () {
            jQuery(this).popover('show');
        });

        jQuery('.elegantaltagpopover').click(function () {
            if (jQuery(this).parent().find('.popover').length == 0) {
                jQuery(this).popover('show');
            }
        });

        jQuery('body').on('click', function (e) {
            if (jQuery(e.target).closest('.popover').length == 0) {
                jQuery('.elegantaltagpopover').each(function () {
                    var el = jQuery(this);
                    if (el.attr('id') != jQuery(e.target).attr('id')) {
                        el.popover('hide');
                    }
                });
            }
        });

        jQuery(document).keyup(function (e) {
            if (e.keyCode == 27) { // escape key maps to keycode `27`
                jQuery('.elegantaltagpopover').popover('hide');
            }
        });
    }

    if (jQuery('.elegantal_shortcodes_list').length > 0) {
        jQuery('body').on('click', '.elegantal_shortcodes_list li', function () {
            var shortcode = jQuery(this).data('shortcode');
            var input = jQuery(this).parents('.' + elegantalFormGroupClass).find('input:visible, textarea:visible');
            var value = input.val();
            var cursor = input.prop('selectionStart');

            // the character before cursor
            var prev_char = value.substr(cursor - 1, 1);
            if (value.length > 0 && cursor > 0 && prev_char && prev_char !== ' ' && prev_char !== '\n') {
                value = value.substr(0, cursor) + ' ' + value.substr(cursor);
                cursor = (value.substr(0, cursor) + ' ').length;
            }
            // the character after cursor
            var next_char = value.substr(cursor, 1);
            if (value.length > 0 && next_char && next_char !== ' ' && next_char !== '\n') {
                shortcode = shortcode + ' ';
            }

            value = value.substr(0, cursor) + shortcode + value.substr(cursor);
            cursor = (value.substr(0, cursor) + shortcode).length;

            input.val(value);
            input.focus();
            input.prop('selectionStart', cursor);
            input.prop('selectionEnd', cursor);
        });
    }
    if (jQuery('.html_textarea').length > 0) {
        if (jQuery('.html_textarea').parents('.form-wrapper>.form-group .form-group').length > 0) {
            jQuery('.html_textarea').parents('.form-wrapper>.form-group .form-group').first().parent().append(jQuery('.elegantal_shortcodes').html());
        } else {
            jQuery('.html_textarea').parent().append(jQuery('.elegantal_shortcodes').html());
        }
        jQuery('.elegantal_shortcodes').remove();
    }

    // Apply Auto Meta Tags Rule
    if (jQuery('.elegantal_autometatags_apply_panel').length > 0) {
        // Prevent accidental page reload
        window.onbeforeunload = function () {
            if (elegantalProcessing) {
                return jQuery('.elegantal_autometatags_apply_panel').data('reloadmsg');
            }
        };
        // Start applying with the first request
        elegantalMetaTagsApply(1);
    }

    // Apply Image Alt Rule
    if (jQuery('.elegantal_imagealt_apply_panel').length > 0) {
        // Prevent accidental page reload
        window.onbeforeunload = function () {
            if (elegantalProcessing) {
                return jQuery('.elegantal_imagealt_apply_panel').data('reloadmsg');
            }
        };
        // Start applying with the first request
        elegantalImageAltApply(1);
    }

    // HTML Block List - sortable table - update positions
    if (jQuery('.elegantal_sortable_table').length > 0) {
        elegantalAdminUrl = jQuery('.elegantalseoessentialsJsDef').data('adminurl');

        jQuery('.elegantal_sortable_table tbody tr td').each(function (index, el) {
            jQuery(el).width(jQuery(el).width());
        });

        jQuery('.elegantal_sortable_table tbody').sortable({
            cursor: 'move',
            axis: "y",
            containment: 'table',
            handle: '.position_handle',
            update: function (e, ui) {
                var positions = '';
                jQuery('.elegantal_sortable_table tbody tr').each(function (index, el) {
                    if (positions) {
                        positions += '-';
                    }
                    positions += jQuery(el).data('id') + '_' + (index + 1);
                    jQuery(el).find('.position_number').text(index + 1);
                });
                jQuery.ajax({
                    url: elegantalAdminUrl,
                    type: 'POST',
                    data: {
                        positions: positions,
                        event: 'htmlBlockUpdatePositions'
                    },
                    success: function () {

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown + ': ' + textStatus);
                    }
                });
            }
        });
    }
});

function elegantalMetaTagsApply(currentRequest) {
    elegantalProcessing = true;
    elegantalAdminUrl = jQuery('.elegantalseoessentialsJsDef').data('adminurl');
    var panel = jQuery('.elegantal_panel.elegantal_autometatags_apply_panel');
    var progress = panel.find('.elegantal_progress_bar');
    var id = panel.data('id');
    var lang_id = panel.data('lang');

    var offset = panel.data('offset');
    var limit = panel.data('limit');
    var totalRequests = panel.data('requests');

    // Generate random number for GET request. This is needed to prevent if there is cache for the URL
    var min = 100;
    var max = 100000000;
    var random = Math.floor(Math.random() * (max - min + 1)) + min;

    jQuery.ajax({
        url: elegantalAdminUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            event: 'metaTagsApply',
            ajax: 1,
            id_elegantalseoessentials_auto_meta: id,
            lang_id: lang_id,
            offset: offset,
            limit: limit,
            elegantal: random
        },
        success: function (result) {
            if (result.success) {
                var completed = (currentRequest * 100) / totalRequests;
                progress.css({width: completed + '%'});
                progress.text(Math.round(completed) + '%');

                if (currentRequest < totalRequests) {
                    panel.data('offset', (offset + limit));
                    elegantalMetaTagsApply(currentRequest + 1);
                } else {
                    elegantalProcessing = false;
                    setTimeout(function () {
                        window.location.href = elegantalAdminUrl + '&event=metaTagsApplySuccess&id_elegantalseoessentials_auto_meta=' + id;
                    }, 1000);
                }
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_error_txt').text(result.message);
                jQuery('.elegantal_error').fadeIn();
                jQuery('html, body').animate({scrollTop: 0});
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            elegantalProcessing = false;
            jQuery('.elegantal_error_txt').text(errorThrown);
            jQuery('.elegantal_error').fadeIn();
            jQuery('html, body').animate({scrollTop: 0});
        }
    });
}

function elegantalImageAltApply(currentRequest) {
    elegantalProcessing = true;
    elegantalAdminUrl = jQuery('.elegantalseoessentialsJsDef').data('adminurl');
    var panel = jQuery('.elegantal_panel.elegantal_imagealt_apply_panel');
    var progress = panel.find('.elegantal_progress_bar');
    var id = panel.data('id');
    var lang_id = panel.data('lang');

    var offset = panel.data('offset');
    var limit = panel.data('limit');
    var totalRequests = panel.data('requests');

    // Generate random number for GET request. This is needed to prevent if there is cache for the URL
    var min = 100;
    var max = 100000000;
    var random = Math.floor(Math.random() * (max - min + 1)) + min;

    jQuery.ajax({
        url: elegantalAdminUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            event: 'imageAltApply',
            ajax: 1,
            id_elegantalseoessentials_image_alt: id,
            lang_id: lang_id,
            offset: offset,
            limit: limit,
            elegantal: random
        },
        success: function (result) {
            if (result.success) {
                var completed = (currentRequest * 100) / totalRequests;
                progress.css({width: completed + '%'});
                progress.text(Math.round(completed) + '%');

                if (currentRequest < totalRequests) {
                    panel.data('offset', (offset + limit));
                    elegantalImageAltApply(currentRequest + 1);
                } else {
                    elegantalProcessing = false;
                    setTimeout(function () {
                        window.location.href = elegantalAdminUrl + '&event=imageAltApplySuccess&id_elegantalseoessentials_image_alt=' + id;
                    }, 1000);
                }
            } else {
                elegantalProcessing = false;
                jQuery('.elegantal_error_txt').text(result.message);
                jQuery('.elegantal_error').fadeIn();
                jQuery('html, body').animate({scrollTop: 0});
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            elegantalProcessing = false;
            jQuery('.elegantal_error_txt').text(errorThrown);
            jQuery('.elegantal_error').fadeIn();
            jQuery('html, body').animate({scrollTop: 0});
        }
    });
}
