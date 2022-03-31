
(function($) {
    "use strict";
    $(document).ready(function() {
        product_page_values_config(0);
        $("input:radio[name=product_page]").on('click', function() {
            product_page_values_config(500);
        });
        $('form[id="crazyprdlayouts_form"] input[id="specific_product_id_text"]').each(function() {
            $(this).change(prd_textchange).change();
        });
        $('form[id="crazyprdlayouts_form"] select[id="specific_product_id"]').each(function() {
            $(this).change(prd_listchange);
        });
        function product_page_values_checked(speed) {
            $("#ajax_choose_product").closest('.form-group').hide(speed);
            $("#ajax_choose_cat").closest('.form-group').hide(speed);
        }
        function product_page_values_config(speed) {
            var product_page_val = $('input:radio[name=product_page]:checked').val();
            if (product_page_val == 1) {
                product_page_values_checked(speed);
            } else {
                product_page_values_unchecked(speed);
            }
        }
        function product_page_values_unchecked(speed) {
            $("#ajax_choose_product").closest('.form-group').show(speed);
            $("#ajax_choose_cat").closest('.form-group').show(speed);
        }
        // START Change Prd selection
        function prd_listchange() {
            var obj = $(this);
            var str = obj.val().join(',');
            obj.closest('form').find('#specific_product_id_text').val(str);
        }
        function prd_textchange() {
            var obj = $(this);
            var list = obj.closest('form').find('#specific_product_id');
            var values = obj.val().split(',');
            var len = values.length;
            list.find('option').prop('selected', false);
            for (var i = 0; i < len; i++)
                list.find('option[value="' + $.trim(values[i]) + '"]').prop('selected', true);
        }
        $('#product_autocomplete_input')
            .autocomplete(crazyprdlayout_ajaxurl + '&crazyprdlayout_ajaxgetproducts=1', {
                minChars: 1,
                autoFill: true,
                max: 20,
                matchContains: true,
                mustMatch: false,
                scroll: false,
                cacheLength: 0,
                formatItem: function(item) {
                    console.log(item)
                    return item[1] + ' - ' + item[0];
                }
            }).result(addAccessory);
        $('#product_autocomplete_input').setOptions({
            extraParams: {
                excludeIds: getAccessoriesIds()
            }
        });
        $('#cat_autocomplete_input')
            .autocomplete(crazyprdlayout_ajaxurl + '&crazyprdlayout_ajaxgetcats=1', {
                minChars: 1,
                autoFill: true,
                max: 20,
                matchContains: true,
                mustMatch: false,
                scroll: false,
                cacheLength: 0,
                formatItem: function(item) {
                    return item[1] + ' - ' + item[0];
                }
            }).result(addCatAccessory);
        $('#cat_autocomplete_input').setOptions({
            extraParams: {
                excludeIds: getCatAccessoriesIds()
            }
        });
        function delAccessory(id) {
            var div = $('#divAccessories');
            var input = $('#inputAccessories');
            var name = $('#nameAccessories');
            // Cut hidden fields in array
            var inputCut = input.val().split('-');
            var nameCut = name.val().split('¤');
            if (inputCut.length != nameCut.length)
                return jAlert('Bad size');
            // Reset all hidden fields
            input.val('');
            name.val('');
            div.html('');
            var inputVal = '',
                nameVal = '',
                divHtml = '';
            for (var i in inputCut) {
                // If empty, error, next
                if (!inputCut[i] || !nameCut[i])
                    continue;
                if (typeof inputCut[i] == 'function') // to resolve jPaq issues
                    continue;
                // Add to hidden fields no selected products OR add to select field selected product
                if (inputCut[i] != id) {
                    inputVal += inputCut[i] + '-';
                    nameVal += nameCut[i] + '¤';
                    divHtml += '<div class="form-control-static"><button type="button" class="delAccessory btn btn-default" name="' + inputCut[i] + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
                } else
                    $('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
            }
            input.val(inputVal);
            name.val(nameVal);
            div.html(divHtml);
            $('#product_autocomplete_input').setOptions({
                extraParams: {
                    excludeIds: getAccessoriesIds()
                }
            });
        }
        $('#divAccessories').on('click', '.delAccessory', function() {
            delAccessory($(this).attr('name'));
        });
        function getAccessoriesIds() {
            if ($('#inputAccessories').val() === undefined) {
                return;
            }
            return $('#inputAccessories').val().replace(/\-/g, ',');
        }
        function addAccessory(event, data, formatted) {
            if (data == null)
                return false;
            var productId = data[1];
            var productName = data[0];
            var $divAccessories = $('#divAccessories');
            var $inputAccessories = $('#inputAccessories');
            var $nameAccessories = $('#nameAccessories');
            /* delete product from select + add product line to the div, input_name, input_ids elements */
            $divAccessories.html($divAccessories.html() + '<div class="form-control-static"><button type="button" class="delAccessory btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + productName + '</div>');
            $nameAccessories.val($nameAccessories.val() + productName + '¤');
            $inputAccessories.val($inputAccessories.val() + productId + '-');
            $('#product_autocomplete_input').val('');
            $('#product_autocomplete_input').setOptions({
                extraParams: {
                    excludeIds: getAccessoriesIds()
                }
            });
        };
        function delCatAccessory(id) {
            var div = $('#divCatAccessories');
            var input = $('#inputCatAccessories');
            var name = $('#nameCatAccessories');
            // Cut hidden fields in array
            var inputCut = input.val().split('-');
            var nameCut = name.val().split('¤');
            if (inputCut.length != nameCut.length)
                return jAlert('Bad size');
            // Reset all hidden fields
            input.val('');
            name.val('');
            div.html('');
            var inputVal = '',
                nameVal = '',
                divHtml = '';
            for (var i in inputCut) {
                // If empty, error, next
                if (!inputCut[i] || !nameCut[i])
                    continue;
                if (typeof inputCut[i] == 'function') // to resolve jPaq issues
                    continue;
                // Add to hidden fields no selected products OR add to select field selected product
                if (inputCut[i] != id) {
                    inputVal += inputCut[i] + '-';
                    nameVal += nameCut[i] + '¤';
                    divHtml += '<div class="form-control-static"><button type="button" class="delCatAccessory btn btn-default" name="' + inputCut[i] + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
                } else
                    $('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
            }
            input.val(inputVal);
            name.val(nameVal);
            div.html(divHtml);
            $('#cat_autocomplete_input').setOptions({
                extraParams: {
                    excludeIds: getCatAccessoriesIds()
                }
            });
        }
        $('#divCatAccessories').on('click', '.delCatAccessory', function() {
            delCatAccessory($(this).attr('name'));
        });
        function getCatAccessoriesIds() {

            if ($('#inputAccessories').val() === undefined) {
                return;
            }
            return $('#inputCatAccessories').val().replace(/\-/g, ',');
        }
        function addCatAccessory(event, data, formatted) {
            if (data == null)
                return false;
            var productId = data[1];
            var productName = data[0];
            var $divCatAccessories = $('#divCatAccessories');
            var $inputCatAccessories = $('#inputCatAccessories');
            var $nameCatAccessories = $('#nameCatAccessories');
            /* delete product from select + add product line to the div, input_name, input_ids elements */
            $divCatAccessories.html($divCatAccessories.html() + '<div class="form-control-static"><button type="button" class="delCatAccessory btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + productName + '</div>');
            $nameCatAccessories.val($nameCatAccessories.val() + productName + '¤');
            $inputCatAccessories.val($inputCatAccessories.val() + productId + '-');
            $('#cat_autocomplete_input').val('');
            $('#cat_autocomplete_input').setOptions({
                extraParams: {
                    excludeIds: getAccessoriesIds()
                }
            });
        };
        $(document).on("change", '#selected_prds', function() {
            var btnObj = $('#edit_with_button');
            if (btnObj.hasClass("from-extratab")) {
                var extrahref = btnObj.attr('href');
                var prdid = $("#selected_prds").val();
                btnObj.attr('href', extrahref + "&exprdid=" + prdid);
            }
        });
    });
})(jQuery);