/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */


$(document).on('click', '#addCustomsProduct', function(e) {
    e.preventDefault();
    var btn = $(e.target);
    var initialText = btn.html();
    var id_customs_product = $('#customs .product:last').data('product') + 1;

    btn.text('Loading');
    $.post(canadaPostCreateLabelControllerUrl, {addCustomsProduct: true, id_customs_product: id_customs_product}, function (data) {
        btn.html(initialText);
        if (data) {
            $('#canadapost #customs .addCustomsProduct').before($(data).find('.form-group'));
        }
    }, 'html');
});

$(document).on('click', '.removeCustomsProduct', function(e) {
    e.preventDefault();
    if ($('#customs .product').length === 1) {
        alert('You must keep at least one product in your customs information.');
    } else {
        $('#customs .product-' + $(e.target).data('product')).remove();
    }
});

$(document).on('change', '#parcel .box, #parcel-return .box', function(e) {
    var dimensions = ['length', 'width', 'height'];
    dimensions.forEach(function(dimension) {
        $(e.target).parents('form').find('.'+dimension).val('...');
    });
    // Get box dimensions
    $.post(canadaPostCreateLabelControllerUrl, {getParcelBox: true, id_box: $(e.target).val()}, function (data) {
        if (data) {
            // Populate dimension fields with box
            dimensions.forEach(function(dimension) {
                $(e.target).parents('form').find('.'+dimension).val(data[dimension]);
            });

            // Save settings after dimension fields have been populated
            CanadaPost.saveFormFields(e);
        } else {
            dimensions.forEach(function(dimension) {
                $(e.target).parents('form').find('.'+dimension).val('0.0');
            });
            alert('Error retrieving box dimensions, please enter them manually.');
        }
    }, 'json');
});

$(document).on('change', '#country-code', function(e) {
    CanadaPost.toggleIntlFields();
});

$(window).on('load', function() {
    CanadaPost.toggleIntlFields();

    // Fetch live rates on page load if viewing individual order
    if (typeof id_order !== 'undefined') {
        $('.btn-update-rate').click();
    }
});

$(document).on('click', '.btn-update-rate', function(e) {
    e.preventDefault();
    CanadaPost.updateRate(e);
});

$(document).on('click', '.btn-fetch-bulk-rate', function(e) {
    e.preventDefault();
    CanadaPost.updateBulkOrderRate(e);
});

$(document).on('click', '.order-error-link', function(e) {
    e.preventDefault();
    CanadaPost.getLinkPostInModal(e, '#errorModal');
});

$(document).on('click', '.btn-close-edit', function(e) {
    e.preventDefault();
    window.history.back();
});

$(document).on('click', '.edit-label-settings', function(e) {
    e.preventDefault();
    CanadaPost.getEditLabelSettingsInModal(e, '#editLabelModal');
});

if (autoOpenLabel === "1") {
    $(document).on('click', '.btn-create-label', function (e) {
        e.preventDefault();
        CanadaPost.createLabel(e);
    });
}

$(document).on('change', '#createLabel form input, #createLabel form select:not(#box)', function (e) {
    CanadaPost.saveFormFields(e);
});

var CanadaPost = {

    /*
    * Hide/Show label fields for domestic or international shipments.
    * */
    toggleIntlFields : function()
    {
        let countryValue = $('#canadapost #country-code').val();
        $('#canadapost .nav-tabs a[href^="#customs"]').parent().addClass('customs');
        $('#canadapost .nav-tabs a[href^="#return"]').parent().addClass('return');
        let customsTab = $('#canadapost .customs');
        let returnTab = $('#canadapost .return');
        let nonDeliveryOptions = $('#canadapost .non_delivery_options');

        if (countryValue === 'CA') {
            this.disableTab(customsTab);
            this.enableTab(returnTab, '#return');
            nonDeliveryOptions.hide();
        } else {
            this.enableTab(customsTab, '#customs');
            this.disableTab(returnTab);
            nonDeliveryOptions.show();
        }
    },

    disableTab : function(tab)
    {
        tab.addClass('disabled');
        tab.find('a').attr('href', '').addClass('disabled');
    },

    enableTab : function(tab, href)
    {
        tab.removeClass('disabled');
        tab.find('a').attr('href', href).removeClass('disabled');
    },

    initTabs : function()
    {
        if (typeof formTabs !== 'undefined') {
            $.each($('#canadapost .defaultForm'), function(id, form) {
                $.each($(form).children('.panel'), function (index, fieldset) {
                    if ($(fieldset).find("[data-tab-id]").length > 0) {
                        $(fieldset).children('.form-wrapper').prepend('<div class="tab-content panel card" />');
                        $(fieldset).children('.form-wrapper').prepend('<ul class="nav nav-tabs" />');
                        $.each(formTabs, function (tabId, name) {
                            // Move every form-group into the correct .tab-content > .tab-pane
                            var elemts = $(fieldset).find("[data-tab-id='" + tabId + "']");
                            // Add the item to the .nav-tabs
                            if (elemts.length != 0) {
                                $(fieldset).find('.tab-content').append('<div id="' + tabId + '" class="tab-pane" />');
                                $(elemts).appendTo('#' + tabId);
                                $(fieldset).find('.nav-tabs').append('<li><a class="nav-link" href="#' + tabId + '" data-toggle="tab">' + name + '</a></li>');
                            }
                        });
                      // Activate the first tab
                      $(fieldset).find('.tab-content div').first().addClass('active');
                      $(fieldset).find('.nav-tabs li').first().addClass('active');
                      // set the <a> to active as well for the Symfony pages
                      $(fieldset).find('.nav-tabs li a').first().addClass('active');
                    }
                });
            });
        }
    },

    /*
    * Fetch rate via API using label form values
    * */
    updateRate : function(e)
    {
        let form = $(e.target).parents('form');
        let rateContainer = form.find('#live-rate');

        // Loading spinner
        rateContainer.removeClass('error').addClass('success').html('<i class="icon-refresh rotating"></i> Loading...');

        $.post(canadaPostCreateLabelControllerUrl, form.serialize() + '&updateRate=true', function (data) {
            if (data) {
                if ("rate" in data) {
                    rateContainer.removeClass('error').addClass('success').html(data.rate);
                } else if ("error" in data) {
                    rateContainer.removeClass('success').addClass('error').html(data.error);
                }
            } else {
                rateContainer.removeClass('success').addClass('error').html('Unable to fetch rates.');
            }
        }, 'json');
    },

    /*
    * Fetch rate via API using id_order
    * */
    updateBulkOrderRate : function(e)
    {
        let link = $(e.target).closest('a');
        let rateIcon = link.find('i');

        // Loading spinner
        link.html(rateIcon);
        rateIcon.addClass('rotating');

        $.post(link.attr('href'), {updateBulkOrderRate: true, ajax: true}, function (data) {
            link.html(data.rate);
            if (data) {
                if ("rate" in data) {
                    link.html(data.rate);
                } else {
                    link.html(data.error);
                }
            } else {
                link.html(data.error);
            }
        }, 'json');
    },

    createLabel : function(e)
    {
        let form = $(e.target).parents('form');

        // Setup iframe to contain the label PDF
        var iframe = $('<iframe class="pdf-iframe"></iframe>');

        // Loading spinner
        $('#labelModal .modal-body').html('<p><i class="icon-refresh rotating"></i> Loading label...</p>');
        $('#labelModal').modal('show');

        $.post(canadaPostCreateLabelControllerUrl, form.serialize() + '&ajaxCreateLabel=true', function (data) {
            if (data) {
                if ("src" in data) {
                    // Load PDF
                    iframe.attr('src', data.src);
                    $('#labelModal .modal-body').html(iframe);

                    // Refresh page on modal close to show new data (label lists, order status, tracking number)
                    if (typeof id_order !== 'undefined' && typeof viewOrder !== 'undefined') {
                        $('#labelModal').on('hidden.bs.modal', function () {
                            window.location.reload();
                        });
                    }
                } else if ("error" in data) {
                    $('#labelModal .modal-body').html('<p class="error">'+data.error+'</p>');
                }
            } else {
                $('#labelModal .modal-body').html('<p class="error">Unable to create label.</p>');
            }
        }, 'json');
    },

    getLinkPostInModal : function(e, modalId)
    {
        // Loading spinner
        $(modalId + ' .modal-body').html('<p><i class="icon-refresh rotating"></i> Loading...</p>');
        $(modalId + '').modal('show');

        $.post($(e.target).attr('href'), {ajax: true}, function (data) {
            if (data) {
                if ("response" in data) {
                    $(modalId + ' .modal-body').html(data.response);
                } else if ("error" in data) {
                    $(modalId + ' .modal-body').html('<p class="error">'+data.error+'</p>');
                }
            } else {
                $(modalId + ' .modal-body').html('<p class="error">Unable to retrieve data.</p>');
            }
        }, 'json');
    },

    getEditLabelSettingsInModal : function(e, modalId)
    {
        // Loading spinner
        $(modalId + ' .modal-body').html('<p><i class="icon-refresh rotating"></i> Loading...</p>');
        $(modalId + '').modal('show');

        $.post($(e.target).attr('href'), {ajax: true}, function (data) {
            if (data) {
                $(modalId + ' .modal-body').html(data);
                CanadaPost.initTabs();
                CanadaPost.toggleIntlFields();
                $(modalId).find('.label-tooltip').tooltip();
                $(modalId).find('.btn-update-rate').click();
                $('.btn-create-label').click(function() {
                    $(modalId).modal('hide');
                });
            } else {
                $(modalId + ' .modal-body').html('<p class="error">Unable to retrieve data.</p>');
            }
        }, 'html');
    },

    saveFormFields : function(e)
    {
        if ($('#save-changes').length !== 0) {
            let form = $(e.target).parents('form');
            let saveChangesContainer = form.find('#save-changes');

            // Loading spinner
            saveChangesContainer.removeClass('error').addClass('info').html('<i class="icon-refresh rotating"></i> Saving Changes...');

            $.post(canadaPostCreateLabelControllerUrl, form.serialize() + '&ajaxSaveChanges=true', function (data) {
                if (data) {
                    if ("success" in data) {
                        saveChangesContainer.removeClass('error').addClass('info').html(data.success);
                    } else if ("error" in data) {
                        saveChangesContainer.removeClass('success').addClass('error').html(data.error);
                    }
                } else {
                    saveChangesContainer.removeClass('success').addClass('error').html('Unable to save changes.');
                }
            }, 'json');
        }
    }

};

$(function() {
  CanadaPost.initTabs();
});

