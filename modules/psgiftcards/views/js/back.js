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

var fnInitDropzone = function () {
    let langsel = $('select[name=cap-email-language]').val();
    let optionsDropzone = {
        acceptedFiles: 'image/*',
        maxFiles: 1,
        previewsContainer: '.gift-image',
        maxFilesize: 50, // File size in Mb
        dictDefaultMessage: '',
        hiddenInputContainer: '.importDropzone.active',
        init: function() {
            this.on("addedfile", function(file) {
                $('.importDropzone.active .module-import-start').hide();
                $('.importDropzone.active .module-import-failure-details').html(file_not_valid);
            });
        },
        sending: function sending() {
            $('.img_upld').hide();
            $('.dz-preview').show();
            $('.dz-success-mark').hide();
            $('.dz-error-mark').hide();
            $('.dz-image').show();
            $('.importDropzone.active .modal .loader').show();
            $('.importDropzone.active .module-import-start').hide();
            $('.importDropzone.active .module-import-failure').hide();
            $('.importDropzone.active .module-import-success').hide();
        },
        success: function(file, response){
            filesAdded.push({ "fileDL": file, "langDL": langsel });
            $('.importDropzone.active .modal .loader').hide();
            $('.importDropzone.active .modal .module-import-failure-details').hide();
            $('.importDropzone.active .module-import-success').show();
            $('.importDropzone.active .module-import-success-msg').html('success');
            $('.delete-img').show();
        },
        error: function(file, response){
            $('.importDropzone.active .modal .loader').hide();
            $('.importDropzone.active .module-import-failure').show();
        }
    };
    Dropzone.instances.forEach( function( elem ) {
        elem.destroy();
    });

    let myDropzone = new Dropzone("form.importDropzone.active", optionsDropzone);
};

var filesAdded = [];

$(window).ready(function() {
    controller_url = controller_url.replace(/\amp;/g,'');

    window.vGiftcards = new Vue({
        el: '#giftcards',
        delimiters: ["((","))"],
        data: {
            giftcards: ''
        },
        methods: {
            getGiftcards: function () {
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: controller_url,
                    data: {
                        ajax: true,
                        action: 'GetProductTagAsGiftcard',
                    },
                    success: function(data) {
                        window.vGiftcards.giftcards = data;
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            },
            switchState: function (id_product, id_giftcard) {
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: controller_url,
                    data: {
                        ajax: true,
                        action: 'SwitchState',
                        id_product: id_product,
                        id_giftcard: id_giftcard,
                    },
                    success: function(data) {
                        window.vGiftcards.getGiftcards();
                        if (data == 'enable') {
                            showSuccessMessage(enableGiftcard);
                        } else {
                            showSuccessMessage(disableGiftcard);
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            },
            untagProduct: function (id_product, id_giftcard) {
                swal({
                    title: sweetAlertTitle,
                    text: sweetAlertMessage,
                    type: "warning",
                    buttons: true,
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    closeOnConfirm: false
                }).then(function (result) {
                    if (result) {
                        $.ajax({
                            type: 'POST',
                            dataType: 'JSON',
                            url: controller_url,
                            data: {
                                ajax: true,
                                action: 'UntagProduct',
                                id_product: id_product,
                                id_giftcard: id_giftcard,
                            },
                            success: function(data) {
                                window.vGiftcards.getGiftcards();
                                showSuccessMessage(untagProduct);
                            },
                            error: function(err) {
                                console.log(err);
                            }
                        });
                    }
                });
            },
        }
    });
    window.vGiftcards.getGiftcards();

    $('#tableGiftcardHisotry').DataTable();

    const pickr1 = Pickr.create({
        el: '.ps_colorpicker1',
        default: color1,
        defaultRepresentation: 'HEX',
        closeWithKey: 'Escape',
        adjustableNumbers: true,
        components: {

            // Main components
            preview: true,
            opacity: false,
            hue: true,

            // Input / output Options
            interaction: {
                hex: false,
                rgba: false,
                hsla: false,
                hsva: false,
                cmyk: false,
                input: true,
                clear: false,
                save: true
            }
        }
    });

    const pickr2 = Pickr.create({
        el: '.ps_colorpicker2',
        default: color2,
        defaultRepresentation: 'HEX',
        closeWithKey: 'Escape',
        adjustableNumbers: true,
        components: {

            // Main components
            preview: true,
            opacity: false,
            hue: true,

            // Input / output Options
            interaction: {
                hex: false,
                rgba: false,
                hsla: false,
                hsva: false,
                cmyk: false,
                input: true,
                clear: false,
                save: true
            }
        }
    });

    pickr1.on('change', (...args) => {
        let pickrColor = pickr1.getColor();
        let hexaColor = pickrColor.toHEX().toString();
        liveEditColor('primary', hexaColor);
        $('#primary_color button').css('color', hexaColor);
        $('#primary_color .data_input').val(hexaColor);
    })

    pickr2.on('change', (...args) => {
        let pickrColor = pickr2.getColor();
        let hexaColor = pickrColor.toHEX().toString();
        liveEditColor('secondary', hexaColor);
        $('#secondary_color button').css('color', hexaColor);
        $('#secondary_color .data_input').val(hexaColor);
    })

    /*
    * Change template color
    */
   liveEditColor = (type, color) => {
        if (type == 'primary') {
            $('.show_template .cap-email_reassurance img').css('background-color', color);
            $('.show_template .primary_color-bordercolor').css('border-color', color);
            $('.show_template .primary_color-backgroundcolor').css('background-color', color);
            $('.show_template .primary_color-bordercolor').css('background-color', color);
            $('.show_template .primary_color-textcolor').css('color', color);
        } else if (type == 'secondary') {
            $('.show_template a').css('color', color);
            $('.show_template .columns-price').css('color', color);
            $('.show_template .cap-email_preview_unsubscribe a').css('color', color);
            $('.show_template .secondary_color-textcolor').css('color', color);
        }
    };

    initializeCkEditors($('.cap-editor.has_content'));

    $('body').on('click', '.module-import-start-select-manual', function(event, manual_select) {
        event.preventDefault();
        $('.importDropzone.active').trigger( "click" );
    });

    $('body').on('click', '.module-import-failure-details-action', function() {
        event.preventDefault();
        $('.module-import-failure-details').slideDown();
    });

    $('body').on('click', '.module-import-failure-retry', function() {
        event.preventDefault();
        $('.module-import-start').show();
        $('.module-import-failure').hide();
    });

    $("#upload-child-modal").on("hidden.bs.modal", function () {
        $('.module-import-start').show();
        $('.module-import-failure').hide();
        $('.module-import-success').hide();
    });

    let langsel = $('select[name=cap-email-language]').val();
    let optionsDropzone = {
        acceptedFiles: 'image/*',
        maxFiles: 1,
        previewsContainer: '.gift-image',
        maxFilesize: 50, // File size in Mb
        dictDefaultMessage: '',
        hiddenInputContainer: '.importDropzone.active',
        init: function() {
            this.on(".addedfile", function(file) {
                $('.importDropzone.active .module-import-start').hide();
                $('.importDropzone.active .module-import-failure-details').html(file_not_valid);
            });
        },
        sending: function sending() {
            $('.img_upld').hide();
            $('.dz-preview').show();
            $('.dz-success-mark').hide();
            $('.dz-error-mark').hide();
            $('.dz-image').show();
            let preview = $('.dz-preview')[0].cloneNode(true);

            let inter = setInterval(() => {
                let imgPreview = $('.dz-image img')[0].src;
                if (imgPreview != '') {
                    clearInterval(inter);
                    $('.gift-image').empty().append(preview);
                    $('.gift-image img').attr('src', imgPreview);
                }
            }, 100);

            $('.importDropzone.active .modal .loader').show();
            $('.importDropzone.active .module-import-start').hide();
            $('.importDropzone.active .module-import-failure').hide();
            $('.importDropzone.active .module-import-success').hide();
        },
        success: function(file, response){
            filesAdded.push({ "fileDL": file, "langDL": langsel });
            $('.importDropzone.active .modal .loader').hide();
            $('.importDropzone.active .modal .module-import-failure-details').hide();
            $('.importDropzone.active .module-import-success').show();
            $('.importDropzone.active .module-import-success-msg').html('success');
            $('.delete-img').show();
        },
        error: function(file, response){
            $('.importDropzone.active .modal .loader').hide();
            $('.importDropzone.active .module-import-failure').show();
        }
    };

    let myDropzone = new Dropzone("form.importDropzone.active", optionsDropzone);

    $('body').on('click', '.delete-img', function() {
        var result = confirm("Want to delete?");
        if (result) {
            $('.img_upld').attr('src', '');
            $('.delete-img').hide();

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: controller_url,
                data: {
                    ajax: true,
                    action: 'RemoveImage',
                },
                success: function(data) {
                    showSuccessMessage(imageRemove);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }
    });

});

