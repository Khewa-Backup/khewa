(function() {
    $(document).ready(function () {
        select2Include();

        var ps_version = $("#ps_version").val();
        ps_version = parseFloat(ps_version).toFixed(1);

        var select_of_products_type = "#type_products_show";
        var add_products_block = ".products_page";
        var add_categories_block = ".categories_page";
        var select_type_of_page_to_display_slider_on = "#display_page";
        var option_display_products_from_current_category = $(select_of_products_type + " option[value='current']");
        var select_type_of_hook_to_display_slider_on = "#display_hook";
        var product_exclusive_hook = $(select_type_of_hook_to_display_slider_on + " option[value='displayFooterProduct']");
        var home_page_exclusive_hook = $(select_type_of_hook_to_display_slider_on + " option[value='displayHome']");
    
        if ($(select_type_of_page_to_display_slider_on).val() === 'home') {
            $(home_page_exclusive_hook).show();
        } else {
            $(home_page_exclusive_hook).hide();
        }
    
        if ($("#show_in_specific_categories_on").is(":checked")) {
            $(".categories_in_which_to_show_slider").show();
        } else {
            $(".categories_in_which_to_show_slider").hide();
        }
        
        if ($(select_type_of_page_to_display_slider_on).val() === 'category') {
            $(option_display_products_from_current_category).show();
            $(".show-in-specific-categories-form-group").show();
    
            if ($("#show_in_specific_categories_on").is(":checked")) {
                $(".categories_in_which_to_show_slider").show();
            } else {
                $(".categories_in_which_to_show_slider").hide();
            }
        } else {
            $(option_display_products_from_current_category).hide();
            $(".show-in-specific-categories-form-group").hide();
            $(".categories_in_which_to_show_slider").hide();
        }

        if ($(select_type_of_page_to_display_slider_on).val() === 'product') {
            $(option_display_products_from_current_category).show();
            $(product_exclusive_hook).show();
        } else {
            $(option_display_products_from_current_category).hide();
            $(product_exclusive_hook).hide();
        }
        
        $(document).on("change click", "input[name='show_in_specific_categories']", function() {
            if ($("#show_in_specific_categories_on").is(":checked")) {
                $(".categories_in_which_to_show_slider").show();
            } else {
                $(".categories_in_which_to_show_slider").hide();
            }
        });

        $(document).on("change", select_type_of_page_to_display_slider_on, function () {
            if ($(select_type_of_page_to_display_slider_on).val() === 'home') {
                $(home_page_exclusive_hook).show();
            } else {
                $(home_page_exclusive_hook).hide();
            }
    
            if ($(select_type_of_page_to_display_slider_on).val() === 'category') {
                $(option_display_products_from_current_category).show();
                $(".show-in-specific-categories-form-group").show();
        
                if ($("#show_in_specific_categories_on").is(":checked")) {
                    $(".categories_in_which_to_show_slider").show();
                } else {
                    $(".categories_in_which_to_show_slider").hide();
                }
            } else {
                $(option_display_products_from_current_category).hide();
                $(".show-in-specific-categories-form-group").hide();
                $(".categories_in_which_to_show_slider").hide();
            }

            if ($(select_type_of_page_to_display_slider_on).val() === 'product') {
                $(option_display_products_from_current_category).show();
                $(product_exclusive_hook).show();
            } else {
                $(option_display_products_from_current_category).hide();
                $(product_exclusive_hook).hide();
            }
        });


        if ($(select_of_products_type).val() === "products") {
            $(add_products_block).show();
        } else {
            $(add_products_block).hide();
        }

        if ($(select_of_products_type).val() === "category") {
            $(add_categories_block).show();
        } else {
            $(add_categories_block).hide();
        }

        var show_navigation_status_switch = "input[name='show_control']";
        var show_navigation_arrows_switch = "input[name='show_navigation_arrow']";
        toggleSliderNavColorSettingsFields();

        $(document).on("change live", show_navigation_status_switch + ", " + show_navigation_arrows_switch, function() {
            toggleSliderNavColorSettingsFields()
        });

        var colorSettingsFieldsToToggle = {
            show_product_availability_status: {
                switch_selector: "input[name='show_product_availability_status']",
                fields_to_toggle: ".product-availability-status-color-form-group"
            },
            show_product_name: {
                switch_selector: "input[name='show_product_name']",
                fields_to_toggle: ".product-name-color-form-group"
            },
            show_product_description: {
                switch_selector: "input[name='show_product_description']",
                fields_to_toggle: ".product-description-color-form-group"
            },
            show_product_price: {
                switch_selector: "input[name='show_product_price']",
                fields_to_toggle: ".product-price-color-form-group"
            },
            show_product_link_to_full_page: {
                switch_selector: "input[name='show_product_link_to_full_page']",
                fields_to_toggle: ".product-link-to-full-page-color-form-group"
            },
            show_product_quickview: {
                switch_selector: "input[name='show_product_quickview']",
                fields_to_toggle: ".product-quickview-color-form-group"
            },
            show_product_button_add: {
                switch_selector: "input[name='show_product_button_add']",
                fields_to_toggle: ".product-addtocart-color-form-group"
            }
        };

        var custom_design_switch = "input[name='use_custom_design']";


        toggleCustomDesignSlaveSettings(colorSettingsFieldsToToggle);

        $(document).on("change live", custom_design_switch, function() {
            toggleCustomDesignSlaveSettings(colorSettingsFieldsToToggle);
        });

        if (ps_version < 1.7) {
            $(".use-custom-design-switch").hide();
        }

        $(document).on("change", select_of_products_type, function () {
            if ($(select_of_products_type).val() === "products") {
                $(add_products_block).show();
            } else {
                $(add_products_block).hide();
            }

            if ($(select_of_products_type).val() === "category") {
                $(add_categories_block).show();
            } else {
                $(add_categories_block).hide();
            }
        });

        $(document).on('click', '#slider_general .table_list_delete a', function () {
            removeProductItem($(this).attr('data-id-product'), '#productIds', '#slider_general');
        });

        
        
        $(document).on('click', '#add_products_item', function () {
            addProductItem();
        });
        
        $(document).on("click", "#featuredproducts_form .nav-tabs li a[href='#support']", function(e) {
            e.preventDefault();
            window.open("https://addons.prestashop.com/en/contact-us?id_product=8732");
            $("#featuredproducts_form .nav-tabs li a[href='#slider_general']").trigger("click");
        });
    });

    function toggleCustomDesignSlaveSettings(colorSettingsFieldsToToggle) {
        var custom_design_switch = "input[name='use_custom_design']";
        var custom_design_switch_is_on = $(custom_design_switch + ":checked").val() === "1";
        var custom_design_slaves = ".mpm-featuredproducts-single-slide-settings";

        if (custom_design_switch_is_on) {
            $(custom_design_slaves).show();
            $.each(colorSettingsFieldsToToggle, function () {
                toggleColorSettingField(this.switch_selector, this.fields_to_toggle);
            });
        } else {
            $(custom_design_slaves).hide();
        }
    }

    function toggleSliderNavColorSettingsFields() {
        var show_navigation_status_switch = "input[name='show_control']";
        var show_navigation_arrows_switch = "input[name='show_navigation_arrow']";
        var slider_navigation_color_settings = ".slider-navigation-color-form-group";

        var isNavSwitchesAllOff = $(show_navigation_status_switch + ":checked").val() === "0" && $(show_navigation_arrows_switch + ":checked").val() === "0";

        if (isNavSwitchesAllOff) {
            $(slider_navigation_color_settings).hide();
        } else {
            $(slider_navigation_color_settings).show();
        }
    }

    function removeProductItem(id, field, page) {
        var products = $(field).val();
        if (products) {
            var new_products = products.split(',');
            var index = $.inArray(id, new_products);
            new_products.splice(index, 1);
            $(field).val(new_products);
            $(page + ' .row_' + id).remove();
        }
    }

    function addProductItem() {
        var id = $('#slider_general #attendee').val();
        var products = $('#' + $('#class_products_add').val()).val();

        if (!products) {
            var new_products = [id];
        }
        else {
            var new_products = products.split(',');
            var index = $.inArray(id, new_products);
            if (index < 0) {
                new_products.push(id);
            }
        }

        $.ajax({
            type: "POST",
            url: 'index.php?rand=' + new Date().getTime(),
            dataType: 'json',
            async: true,
            cache: false,
            data: {
                ajax: true,
                token: $('input[name=token_featuredproducts]').val(),
                controller: 'AdminFeaturedProducts',
                fc: 'module',
                module: 'featuredproducts',
                action: 'addProduct',
                idLang: $("input[name='idLang']").val(),
                idShop: $("input[name='idShop']").val(),
                products: new_products

            },
            success: function (json) {

                if (json['list']) {
                    $('#' + $('#class_products_add').val()).val(json['products']);
                    $('#slider_general .table_product_list_block').replaceWith(json['list']);
                }

            }
        });
    }

    function select2Include() {

        $('.attendee').select2({
            placeholder: "Search for a repository",
            minimumInputLength: 1,
            width: '345px',
            dropdownCssClass: "bootstrap",
            ajax: {
                url: 'index.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params,
                        ajax: true,
                        token: $('input[name=token_featuredproducts]').val(),
                        controller: 'AdminFeaturedProducts',
                        action: 'searchProduct'
                    };
                },
                results: function (data) {
                    if (data) {
                        return {results: data};
                    }
                    else {
                        return {
                            results: []
                        }
                    }
                }
            },
            formatResult: productFormatResult,
            formatSelection: productFormatSelection,
        })
    }

    function productFormatResult(item) {
        itemTemplate = "<div class='media'>";
        itemTemplate += "<div class='pull-left'>";
        itemTemplate += "<img class='media-object' width='40' src='" + item.image + "' alt='" + item.name + "'>";
        itemTemplate += "</div>";
        itemTemplate += "<div class='media-body'>";
        itemTemplate += "<h4 class='media-heading'>" + item.name + "</h4>";
        itemTemplate += "<span>REF: " + item.ref + "</span>";
        itemTemplate += "</div>";
        itemTemplate += "</div>";
        return itemTemplate;
    }
    function productFormatSelection(item) {
        return item.name;
    }

    function toggleColorSettingField(switch_selector, fields_to_toggle) {
        if ($(switch_selector + ":checked").val() === "1") {
            $(fields_to_toggle).show();
        } else {
            $(fields_to_toggle).hide();
        }

        $(document).on("change live", switch_selector, function () {
            if ($(switch_selector + ":checked").val() === "1") {
                $(fields_to_toggle).show();
            } else {
                $(fields_to_toggle).hide();
            }
        });
    }
})();
