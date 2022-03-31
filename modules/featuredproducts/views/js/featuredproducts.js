(function() {
    $(document).ready(function() {
        var slider_container = ".mpm-featuredproducts-slider-container";
        var slider_wrapper = ".mpm-featuredproducts-slider-wrapper";
        var single_slide = ".mpm-featuredproducts-product-miniature";
        var slider_navigation_status = ".mpm-featuredproducts-slider-navigation-status";
        
        /**
         * Initialize each slider with separate settings
         */
        $(slider_wrapper).each(function() {
            var num_of_products_to_show = $(this).data("show");
            
            var num_of_slides_on_1024 = num_of_products_to_show < 3 ? num_of_products_to_show : 3;
            var num_of_slides_on_768 = num_of_products_to_show < 2 ? num_of_products_to_show : 2;
    
            var num_of_slides_to_scroll_on_1024 =($(this).data("scroll") > num_of_slides_on_1024) ? num_of_slides_on_1024 : $(this).data("scroll");
            var num_of_slides_to_scroll_on_768 = ($(this).data("scroll") > num_of_slides_on_768) ? num_of_slides_on_768 : $(this).data("scroll");
            
            var slick_responsive_for_custom_design = [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: num_of_slides_on_1024,
                        slidesToScroll: num_of_slides_to_scroll_on_1024,
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: num_of_slides_on_768,
                        slidesToScroll: num_of_slides_to_scroll_on_768,
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ];
    
            var slick_responsive_for_default_design = [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: num_of_slides_on_1024,
                        slidesToScroll: num_of_slides_to_scroll_on_1024,
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: num_of_slides_on_768,
                        slidesToScroll: num_of_slides_to_scroll_on_768,
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ];
            
            $(this).slick({
                infinite: true,
                speed: $(this).data("speed"),
                autoplay: $(this).data("auto"),
                autoplaySpeed: $(this).data("pause"),
                pauseOnHover: $(this).data("stop"),
                slidesToShow: $(this).data("show"),
                slidesToScroll: ($(this).data("scroll") > $(this).data("show")) ? $(this).data("show") : $(this).data("scroll"),
                arrows: Boolean($(this).data("arrow")),
                dots: Boolean($(this).data("control")),
                appendArrows: $(this).siblings(".mpm-featuredproducts-slider-head").find(".mpm-featuredproducts-slider-navigation"),
                appendDots: $(this).prev(),
                prevArrow: "<span class='slick-prev slick-arrow' aria-label='Previous'><i class='m-arrow_left'></i></span>",
                nextArrow: "<span class='slick-next slick-arrow' aria-label='Next'><i class='m-arrow_right'></i></span>",
                responsive: $(this).hasClass("mpm-featuredproducts-default-miniature-design") ? slick_responsive_for_default_design : slick_responsive_for_custom_design,
            });

            var slide_background_color = $(this).data("slide-background-color");
            var slider_navigation_color = $(this).data("slider-navigation-color");
            var product_name_color = $(this).data("product-name-color");
            var product_name_hover_color = $(this).data("product-name-hover-color");
            var product_description_color = $(this).data("product-description-color");
            var product_price_color = $(this).data("product-price-color");
            var product_regular_price_color = $(this).data("product-regular-price-color");
            var product_link_to_full_page_color = $(this).data("product-link-to-full-page-color");
            var product_link_to_full_page_hover_color = $(this).data("product-link-to-full-page-hover-color");
            var product_quickview_color = $(this).data("product-quickview-color");
            var product_quickview_hover_color = $(this).data("product-quickview-hover-color");
            var product_addtocart_background_color = $(this).data("product-addtocart-background-color");
            var product_addtocart_hover_background_color = $(this).data("product-addtocart-background-hover-color");
            var product_addtocart_color = $(this).data("product-addtocart-color");
            var product_addtocart_hover_color = $(this).data("product-addtocart-hover-color");
            var product_availability_status_text_color = $(this).data("product-availability-status-text-color");
            var product_availability_status_icon_color = $(this).data("product-availability-status-icon-color");

            /**
             *
             * SLIDER NAVIGATION COLOR
             */
            var active_nav_button = "li.slick-active button";
            var inactive_nav_button = "li:not(.slick-active) button";
            var nav_arrow = ".mpm-featuredproducts-slider-navigation .slick-arrow i";
            var nav_default_color = "#dddddd";
            $(this).siblings(slider_navigation_status).find(active_nav_button).css({"background-color": slider_navigation_color, "height": "3px"});
            $(this).siblings(slider_navigation_status).find(inactive_nav_button).css({"background-color": nav_default_color, "height": "1px"});

            $(this).siblings(slider_navigation_status).find("li").hover(function() {
                $(this).find("button").css("background-color", slider_navigation_color);
            }, function() {
                var is_certainly_not_active = $(this).hasClass("slick-active") == false;
                if (is_certainly_not_active) {
                    $(this).find("button").css("background-color", nav_default_color);
                }
            });

            $(this).siblings(".row").find(nav_arrow).hover(function() {
                $(this).css("color", slider_navigation_color);
            }, function() {
                $(this).css("color", nav_default_color);
            });

            $(this).on("afterChange", function() {
                $(this).siblings(slider_navigation_status).find(active_nav_button).css({"background-color": slider_navigation_color, "height": "3px"});
                $(this).siblings(slider_navigation_status).find(inactive_nav_button).css({"background-color": nav_default_color, "height": "1px"});
            });

            /**
             * SLIDER COLOR
             */
            $(this).find(single_slide).hover(function() {
                $(this).css("background-color", slide_background_color);
            }, function () {
                $(this).css("background-color", "transparent");
            });

            /**
             *SLIDER PRODUCT AVAILABILITY STATUS COLOR
             */
            var product_availability_status_text = ".mpm-featuredproducts-product-availability-status";
            var product_availability_status_icon = product_availability_status_text + " i";
            $(this).find(product_availability_status_text).css("color", product_availability_status_text_color);
            $(this).find(product_availability_status_icon).css("color", product_availability_status_icon_color);

            /**
             *SLIDER PRODUCT TITLE COLOR
             */
            var product_title = ".mpm-featuredproducts-product-title a";
            $(this).find(product_title).css("color", product_name_color);

            $(this).find(product_title).hover(function() {
                $(this).css("color", product_name_hover_color);
            }, function() {
                $(this).css("color", product_name_color);
            });

            /**
             *SLIDER PRODUCT DESCRIPTION COLOR
             */
            var product_description = ".mpm-featuredproducts-product-description-block p";
            $(this).find(product_description).css("color", product_description_color);

            /**
             *SLIDER PRODUCT PRICE BLOCK COLOR
             */
            var product_price_block = ".mpm-featuredproducts-product-price-block";
            $(this).find(product_price_block + " .price").css("color", product_price_color);
            $(this).find(product_price_block + " .regular-price").css("color", product_regular_price_color);

            /**
             *SLIDER PRODUCT ACTIONS BLOCK COLOR
             */
            var link_to_product_full_page = ".mpm-featuredproducts-link-to-product-page-block a";
            $(this).find(link_to_product_full_page).css("color", product_link_to_full_page_color);

            $(this).find(link_to_product_full_page + " i").hover(function() {
                $(this).css("color", product_link_to_full_page_hover_color);
            }, function() {
                $(this).css("color", product_link_to_full_page_color);
            });

            var quickview_link = ".mpm-featuredproducts-quickview-block .quick-view";
            $(this).find(quickview_link).css("color", product_quickview_color);

            $(this).find(quickview_link + " i").hover(function() {
                $(this).css("color", product_quickview_hover_color);
            }, function() {
                $(this).css("color", product_quickview_color);
            });

            var product_addtocart_button = ".mpm-featuredproducts-add-to-cart-btn-block .add-to-cart";
            $(this).find(product_addtocart_button).css({"color": product_addtocart_color, "background": product_addtocart_background_color});

            $(this).find(product_addtocart_button).hover(function() {
                $(this).css({"color": product_addtocart_hover_color, "background": product_addtocart_hover_background_color});
            }, function() {
                $(this).css({"color": product_addtocart_color, "background": product_addtocart_background_color});
            });

            $(this).parents(slider_container).css("visibility", "visible");
        });

        var original_content_of_addtocart_btn = $(".mpm-featuredproducts-add-to-cart-btn-block .add-to-cart").first().html();
        var original_width_of_addtocart_btn = getOuterWidthOfHiddenElementFromActionBlock(".mpm-featuredproducts-add-to-cart-btn-block");

        setSliderNavigationView();

        setTimeout(function() {
            setAddToCartButtonView(original_content_of_addtocart_btn, original_width_of_addtocart_btn);
        }, 1000);

        $(window).resize(function() {
            setTimeout(function() {
                setSliderNavigationView();
                setAddToCartButtonView(original_content_of_addtocart_btn, original_width_of_addtocart_btn);
            }, 2000);
        });

        var product_actions_block = ".mpm-featuredproducts-product-actions-block";
        var variants_block = ".mpm-featuredproducts-product-variants-block";
        $(single_slide).hover(function() {
            var current_container_height = $(this).parents(slider_container).outerHeight();

            $(this).parents(slider_container).css({"height": current_container_height, "z-index": "10"});
            $(this).find(product_actions_block).show();
            $(this).find(variants_block).show();
        }, function() {
            $(this).parents(slider_container).css({"height": "auto", "z-index": "1"});
            $(this).find(product_actions_block).hide();
            $(this).find(variants_block).hide();
        });
    });

    function setSliderNavigationView() {
        var slider_nav_dots_container = ".slick-dots";

        $(slider_nav_dots_container).each(function() {
            var nav_button = $(this).children();
            var num_of_nav_buttons = nav_button.length;
            var dots_wrapper_width = $(this).outerWidth();
            var nav_button_width = ((dots_wrapper_width - 1)/num_of_nav_buttons) + "px";

            nav_button.width(nav_button_width);

            //Remove digit identifiers from navigation buttons
            nav_button.children().html("");
        });
    }

    function setAddToCartButtonView(original_content_of_addtocart_btn, original_width_of_addtocart_btn) {
        $(".slick-slider").each(function() {
            var product_actions_block = $(this).find(".mpm-featuredproducts-product-actions-block");
            var product_actions_block_width = getOuterWidthOfHiddenElementFromActionBlock(product_actions_block);
            var quickview_button = $(product_actions_block).find(".mpm-featuredproducts-quickview-block");
            var link_to_full_product_page = $(product_actions_block).find(".mpm-featuredproducts-link-to-product-page-block");
            var product_actions_block_content_width = getOuterWidthOfHiddenElementFromActionBlock(link_to_full_product_page) + original_width_of_addtocart_btn + getOuterWidthOfHiddenElementFromActionBlock(quickview_button);
            var addtocart_button = $(this).find(".add-to-cart");

            if (product_actions_block_width < (product_actions_block_content_width + 10)) {
                $(addtocart_button).html("<span><i class='m-shopping-cart'></i></span>").css("width", "50px");
            } else {
                $(addtocart_button).html(original_content_of_addtocart_btn).css("width", "150px");
            }
        });
    }

    function getOuterWidthOfHiddenElementFromActionBlock(selector) {
        var product_actions_block = ".mpm-featuredproducts-product-actions-block";

        $(product_actions_block).show();
        var width = $(selector).outerWidth();
        $(product_actions_block).hide();

        return width;
    }
})();


