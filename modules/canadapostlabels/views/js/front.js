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

$(document).on('click', '#canadapost-product-rates a.submit-update-address', function(e) {
    e.preventDefault();
    CanadaPost.changeAddress(e);
});

$(document).on('click', '#canadapost-product-rates a.product-information', function(e) {
    e.preventDefault();
});

$(document).on('keypress', '#canadapost-product-rates .update-address-container #postcode', function(e) {
    if (e.keyCode === 10 || e.keyCode === 13) {
        e.preventDefault();
        CanadaPost.changeAddress(e);
    }
});

$(document).on('change', '#canadapost-product-rates select#id_address', function(e) {
    e.preventDefault();
    CanadaPost.changeAddress(e);
});

$(document).on('change', '#canadapost-product-rates input[name=carrier]', function(e) {
    CanadaPost.updateCarrier(e);
});

$(document).on('change', '#canadapost-product-rates #id_country', function(e) {
    e.preventDefault();
    CanadaPost.changeCountry(e);
});

if (!isPsVersion16) {
    prestashop.on('updateCart', function () {
        $('#cart .cart-summary').load(location.href + " .cart-summary>*", "");
    });
}
$(window).load(function() {

    if (isPsVersion16) {
        $.uniform.restore($('.noUniform'));
        $(window).on('resize', function () {
            $.uniform.restore($('.noUniform'));
        });
    }

    // refresh country select/postcode fields
    let countrySelect = $('#canadapost-product-rates #id_country');
    if (countrySelect.length) {
        countrySelect.change();
    }
});

var CanadaPost = {

    updateCarrier: function (e) {

        let form = $(e.target).parents('form');
        let error = $(e.target).parents('form').find('.error-message').hide();
        let id_product_attribute = $('#canadapost-product-rates').data('product-attribute');

        if ($('input[name=carrier]:checked').val()) {

            $.post(this.appendUrlVars(moduleCarrierControllerUrl, form.serialize()), {
                ajax: true,
                submitChangeCarrier: true,
                carrier: $('input[name=carrier]:checked').val(),
                id_product_attribute: id_product_attribute
            }, function (data) {
                error.hide();
                if (data) {
                    if ("success" in data && $('#cart').length) {
                        $('#cart .cart-detailed-totals').load(location.href+" .cart-detailed-totals>*","");
                    }
                }
            }, 'json');
        } else {
            error.show();
        }
    },

    changeCountry: function (e) {

        let postcodeField = $('#canadapost-product-rates input#postcode');
        postcodeField.empty().hide();


        $.post(moduleCarrierControllerUrl, {ajax: true, submitChangeCountry: true, id_country: $(e.target).val()}, function (data) {
            if (data) {
                if ("hasPostcode" in data && data.hasPostcode === true) {
                    postcodeField.show();
                } else {
                    postcodeField.hide();
                }
            } else {
                postcodeField.hide();
            }
        }, 'json');
    },

    changeAddress: function (e) {

        let form = $(e.target).parents('form');
        let container = $('#canadapost-product-rates .delivery-list-container');
        let loading = $('#canadapost-product-rates .loading').hide();
        let id_product_attribute = $('#canadapost-product-rates').data('product-attribute');

        container.empty();
        loading.show();
        $('#canadapost-product-rates .error').hide();
        $('#canadapost-product-rates .fieldError').removeClass('fieldError');

        $.post(this.appendUrlVars(moduleCarrierControllerUrl, form.serialize()), {ajax: true, submitChangeAddress: true, id_product_attribute: id_product_attribute}, function (data) {
            if (data) {
                loading.hide();
                $('#canadapost-product-rates').replaceWith(data);
                $('#canadapost-product-rates .collapse').addClass('in');
                $('#canadapost-product-rates .update-address-container').show();
            }
        }, 'html');
    },

    appendUrlVars: function(url, vars) {
        if (url.indexOf("?") !== -1) {
            url = url+"&"+vars;
        } else {
            url = url+"?"+vars;
        }
        return url;
    },
};

