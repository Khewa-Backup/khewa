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
$(document).ready(function() {
    pdf_controller = pdf_controller.replace(/\amp;/g,'');

    if (!ps_version && typeof $.uniform != 'undefined') {
        $.uniform.restore(".noUniform");
    }

    $('#module-psgiftcards-Giftcards').addClass('page-customer-account');

    giftcards = [];
    checkFields = [];

    refreshGiftcardList();

    var vGiftcardList = new Vue({
        el: '#giftcardList',
        delimiters: ["((","))"],
        data: {
            giftcards: giftcards,
            checkFields: checkFields,
        },
        methods: {
            onChange: function (id_giftcard, index, event) {
                this.giftcards[index].hasChanged = 1;
            },
            save: function (id_giftcard, index, event) {
                save(vGiftcardList.giftcards[index], index);
            },
            configureGiftcard: function (id_giftcard, state, index, event) {
                switch (state) {
                    case 'pdf':
                        generatePDF(id_giftcard, index);
                        break;
                    case 'sendMail':
                        sendMail(id_giftcard, index);
                        break;
                    case 'scheduleMail':
                        scheduleMail(id_giftcard, index);
                        break;
                    default:
                }
            },
        }
    });

    $(document).on('click', '#giftcardList .toggle-detail', function (e) {
        $('#giftcardList .giftcard-header').removeClass('giftcard-open');
        if (!$(this).parent().parent().parent().next().hasClass('giftcard-hide')) {
            $('#giftcardList .giftcard-content').addClass('giftcard-hide');
        } else {
            $('#giftcardList .giftcard-content').addClass('giftcard-hide');
            $(this).parent().parent().parent().next().removeClass('giftcard-hide');
            $(this).parent().addClass('giftcard-open');
        }
    });

    function refreshGiftcardList() {
        front_controller = front_controller.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: front_controller,
            data: {
                ajax: true,
                action: 'RefreshGiftcardList',
            },
            success: function(data) {
                if (!ps_version && typeof $.uniform != 'undefined') {
                    $.uniform.restore(".noUniform");
                }
                vGiftcardList.giftcards = data;
            },
            error: function(err) {
                console.log(err);
            }
        });
    }

    function save(giftcard, index) {
        front_controller = front_controller.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: front_controller,
            data: {
                ajax: true,
                action: 'SaveGiftcard',
                giftcard: giftcard,
            },
            success: function(data) {
                if (data == 'success update') {
                    vGiftcardList.checkFields = '';
                    vGiftcardList.giftcards[index].hasChanged = 0;
                    vGiftcardList.giftcards[index].id_state = 6;
                } else {
                    vGiftcardList.checkFields = data;
                }
            },
            error: function(err) {
                console.log(err);
            }
        });
    }

    function generatePDF(id_giftcard, index) {
        front_controller = front_controller.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'HTML',
            url: front_controller,
            data: {
                ajax: true,
                action: 'GetPdfData',
                id_giftcard: id_giftcard,
            },
            success: function(data) {
                window.location.href = pdf_controller+'&id_giftcard='+id_giftcard
                vGiftcardList.giftcards[index].hasChanged = 0;
                vGiftcardList.giftcards[index].id_state = 4;
                // refreshGiftcardList();
            },
            error: function(err) {
                console.log(err);
            }
        });
    }

    function sendMail(id_giftcard, index) {
        front_controller = front_controller.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: front_controller,
            data: {
                ajax: true,
                action: 'SendMail',
                id_giftcard: id_giftcard,
            },
            success: function(data) {
                vGiftcardList.giftcards[index].hasChanged = 0;
                vGiftcardList.giftcards[index].id_state = 5;
                vGiftcardList.giftcards[index].type = 0;
                // refreshGiftcardList();
            },
            error: function(err) {
                console.log(err);
            }
        });
    }

    function scheduleMail(id_giftcard, index) {
        front_controller = front_controller.replace(/\amp;/g,'');
        $.ajax({
            type: 'POST',
            dataType: 'HTML',
            url: front_controller,
            data: {
                ajax: true,
                action: 'ScheduleMail',
                id_giftcard: id_giftcard,
            },
            success: function(data) {
                vGiftcardList.giftcards[index].hasChanged = 0;
                vGiftcardList.giftcards[index].id_state = 3;
                // refreshGiftcardList();
            },
            error: function(err) {
                console.log(err);
            }
        });
    }
});

