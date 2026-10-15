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

/*
 * Show specific Unsubscribe Text in live edit
 */
showSpecificUnsubscribeText = (elem) => {

}

/**
 * Wait for document being ready
 */
$(document).ready(function() {

    /**
     * Change language need to reload all datas
     */
    $(document).on('change', 'select[name="cap-email-language"]', (event) => {
        let idlang = $(event.target).find(":selected").val();
        $('.content_by_lang').addClass('hide');
        $('.content_by_lang.lang_' + idlang).removeClass('hide');
        // Waiting for the ckeditor to be initialize
        // Load current language content ckeditor
        let nameCkeditorContent = $('.content_by_lang:visible #email_content textarea').attr('name');
        getDataAndWrite(nameCkeditorContent);


        // Load current language content ckeditor
        let nameCkeditorDiscount = $('.content_by_lang:visible #email_discount textarea').attr('name');
        getDataAndWrite(nameCkeditorDiscount);

        // Load current language unsubscribe ckeditor
        let nameCkeditorUnsubscribe = $('.content_by_lang:visible #email_unsubscribe textarea').attr('name');
        getDataAndWrite(nameCkeditorUnsubscribe);

        // Load unsubscribeText
        let unsubscribeText = $('#email_unsubscribe_text:visible input');
        showSpecificUnsubscribeText(unsubscribeText);

        $('.cap-email-reassurance:visible input').trigger('keyup');
        $('.cap-email-socials:visible input').trigger('keyup');

        toggleInputsVisibilityFromSubjectLength();
    });

    function toggleInputsVisibilityFromSubjectLength() {
        let lang = $('select[name="cap-email-language"]').val();
        let isHidden = ($('.subject input:visible').val() == '');

        $('.lang_' + lang + ' #email_unsubscribe').toggleClass('hide', isHidden);
        $('.lang_' + lang + ' #email_cta').toggleClass('hide', isHidden);
        $('.lang_' + lang + ' #email_discount').toggleClass('hide', isHidden);
        $('.lang_' + lang + ' #email_content').toggleClass('hide', isHidden);
    }

    /*
     * Show or Hide Appearance or Text & content panel
     */
    $(document).on('click', '.inner-template-section', (e) => {
        let tagSource = $(e.target).prop("tagName");
        let elem = '#' + $(e.target).parent().attr('id');

        if (tagSource == 'I') {
            elem = '#' + $(e.target).parent().parent().attr('id');
            $(e.target).text('keyboard_arrow_up');
        } else {
            $('i', e.target).html('keyboard_arrow_up');
        }
        if (elem == '#cap_content_conf') {
            $('.devicePdf').removeClass('hide');
        } else {
            $('.devicePdf').addClass('hide');
        }

        $('.inner-template-section i').html('keyboard_arrow_down');
        $('#cap_appearance_conf, #cap_content_conf').removeClass('selected');
        $('.cap_content_conf_elems').slideUp();

        $(elem).addClass('selected');
        $(elem + ' .cap_content_conf_elems').slideDown();
    });

    /*
     * change template
     */
    $(document).on('click', '.cap_content_conf_elems .customradiodesign input', (e) => {
        let template = '#' + $(e.target).val();

        $('.show_template').addClass('hide');
        $(template).removeClass('hide');
    });

    /*
     * Update email template Title
     */
    $(document).on('keyup', '#email_subject:visible input', (e) => {
        let subject = $(e.target).val();
        let lang = $('select[name="cap-email-language"]').val();

        $('.lang_' + lang + ' #show_template title').html(subject);
        toggleInputsVisibilityFromSubjectLength();
    });

    /*
     * Open popup for responsive
     */
    let openPreviewTemplateInDeviceWindow = (event) => {
        let device = $(event.target).data('device');
        let width = '900';
        let height = '1000';

        if (device == 'smartphone') {
            width = '420';
            height = '600';
        } else if (device == 'tablet') {
            width = '700';
            height = '1000';
        }

        if (device == 'pdf') {
            $.ajax({
                    type: 'POST',
                    url: controller_url,
                    data: {
                        ajax: true,
                        action: 'GetPdfData',
                        footer: $('#email_unsubscribe:visible .email_unsubscribe').val(),
                        message: $('#email_content:visible .email_content').val(),
                    }
                })
                .done(function(data) {
                    window.open(data);
                });
        }

        if (device != 'pdf') {
            // Prepare popup
            let divText = $('.show_template:visible')[0].outerHTML;
            let myWindow = window.open('', '', 'width=' + width + ',height=' + height);
            myWindow.onblur = myWindow.close;
            let doc = myWindow.document;
            doc.open();
            doc.write(divText);
            doc.close();
        }
    };

    $('.device').on('click', openPreviewTemplateInDeviceWindow);

    /*
     * Adding Social Networks images and links
     */
    $(document).on('keyup', '.cap-email-socials input:visible', (e) => {
        showSocialNetworkInTemplate(e.target);
        addaptReassuranceBlockWidth();
    });

    /*
     * Adding specific unsubscribe text
     */
    $(document).on('keyup', '#email_unsubscribe_text input:visible', (e) => {
        showSpecificUnsubscribeText(e.target);
    });


    /*
     * Adding reassurance images and text by updating the text
     */
    $(document).on('keyup', '.cap-email-reassurance input:visible', (e) => {
        showReassuranceInTemplate(e.target);
        addaptReassuranceBlockWidth();
    });

    /**
     *  Close popin Reassurance if click outside
     */
    $(document).on('click', 'body', (e) => {
        let isInside = $(e.target).closest('.reassurance_selectimg').length;
        let isPopin = $(e.target).closest('#reassurance_block').length;

        if (!isInside && !isPopin) {
            $("#reassurance_block").fadeOut(300);
        }
    });

    /**
     *  Show popin Reassurance and move it into the right place
     */
    $(document).on('click', '.reassurance_selectimg', (e) => {
        let reassuranceId = $(e.target).closest('.reassurance_selectimg').attr('data-id');
        let position = $(e.target).closest('.reassurance_section').position();
        let offsetLeft = 24;
        let offsetTop = 105;

        $('#reassurance_block').show();
        $('#reassurance_block').css('top', position.top + offsetTop + 'px');
        $('#reassurance_block').css('left', position.left + offsetLeft + 'px');
        $('#reassurance_block').attr('data-id', reassuranceId);
    });

    /*
     * Count reassurance and unsubscribe characters number
     */
    $(document).on('keyup', '#email_subject input, #reassurance input, #email_unsubscribe_text input', (e) => {
        let amount = $(e.target).val().length;
        let value = $(e.target).val();
        let newValue = value;

        // if characters > 100, we must delete the lastest char (the 101th) and return
        if (amount > 100) {
            newValue = value.substring(0, value.length - 1);
            $(e.target).val(newValue);
            return false;
        }

        $(e.target).parent().find('.caract-count .amount').html(amount);
    });

    if (!$('#email_cta:visible input').val()) {
        $('.cap-email_cta').hide();
    } else {
        $('.cta_content a').text($('#email_cta:visible input').val());
    }
    /*
     * CTA characters number and update live edit with CTA keyup
     */
    $(document).on('keyup', '#email_cta input', (e) => {
        let amount = $(e.target).val().length;
        let value = $(e.target).val();
        let newValue = value;

        // Hide or show for live edit
        if (amount == 0) {
            $('.cap-email_cta').hide();
        } else {
            $('.cap-email_cta').show();
        }

        // if characters > 100, we must delete the lastest char (the 101th) and return
        if (amount > 25) {
            newValue = value.substring(0, value.length - 1);
            $(e.target).val(newValue);
            return false;
        }

        // update live edit value
        $('.cta_content a').text(newValue);

        $(e.target).parent().find('.caract-count .amount').html(amount);
    });

    /*
     *   Reassurance Block select category
     */
    $(document).on('click', '#reassurance_block .category_select div i', (e) => {
        let category = $(e.target).attr('data-id');

        $('#reassurance_block .category_select div').removeClass('active');
        $(e.target).parent().addClass('active');

        $('#reassurance_block .category_reassurance').removeClass('active');
        $('#reassurance_block .cat_' + category).addClass('active');
    });

    /*
     *   Reassurance Block select picto
     */
    $(document).on('click', '#reassurance_block .category_reassurance img', (e) => {
        let icon = $(e.target).attr('src');
        let id = $('#reassurance_block').attr('data-id');

        $('#reassurance_block .category_reassurance img').removeClass('selected');
        $(e.target).addClass('selected');
        $('#reassurance:visible .reassurance_' + id + ' img').attr('src', icon);
        $('#reassurance_block').fadeOut(300);
        $('.cap-email_reassurance_' + id + ' img').attr('src', icon);
        $('#reassurance:visible .reassurance_' + id + ' input').val(icon);
    });
});

