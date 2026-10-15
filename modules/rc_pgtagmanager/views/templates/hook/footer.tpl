{*
* NOTICE OF LICENSE
*
* This source file is subject to a trade license awarded by
* Garamo Online L.T.D.
*
* Any use, reproduction, modification or distribution
* of this source file without the written consent of
* Garamo Online L.T.D It Is prohibited.
*
* @author    Reaction Code <info@reactioncode.com>
* @copyright 2015-2021 Garamo Online L.T.D
* @license   Commercial license
*}
<script data-cfasync="false" type="text/javascript">
    'use strict';

    // Instantiate the tracking class
    var rcTagManagerLib = new RcTagManagerLib();

    // Init page values
    var controllerName = '{$controller_name}';
    var compliantModules = {$compliant_modules|json_encode nofilter};
    var compliantModuleName = '{$compliant_module_name}';
    var skipCartStep = '{$skip_cart_step}';
    var isOrder = '{$is_order}';
    var isCheckout = '{$is_checkout}';
    var isClientId = {$is_client_id};
    var gtmProducts;
    var gtmOrderComplete;
    ////////////////////////////

    // set tracking features
    rcTagManagerLib.trackingFeatures = gtmTrackingFeatures;

    // set checkout values
    rcTagManagerLib.controllerName = controllerName;
    rcTagManagerLib.isCheckout = isCheckout;
    rcTagManagerLib.compliantModuleName = compliantModuleName;
    rcTagManagerLib.skipCartStep = skipCartStep;

    // list names
    rcTagManagerLib.lists = {$lists|json_encode nofilter};

    // Google remarketing - page type
    rcTagManagerLib.ecommPageType = '{$ecomm_pagetype}';

    {if isset($products_list_cache)}
        // get products list to cache
        rcTagManagerLib.productsListCache = {$products_list_cache|json_encode nofilter};
    {/if}

    // Listing products
    {if isset($gtm_products)}
        // checkout pages
        gtmProducts = {$gtm_products|json_encode nofilter};
    {/if}
    {if isset($gtm_order_complete)}
        // Order complete
        gtmOrderComplete = {$gtm_order_complete|json_encode nofilter};
    {/if}
    ///////////////////////////////////////////////

    if (!disableInternalTracking) {
        // Initialize all user events when DOM ready
        document.addEventListener('DOMContentLoaded', initGtmEvents, false);
        window.addEventListener('pageshow', fireEventsOnPageShow, false);
    }

    function initGtmEvents() {
        // Events binded on all pages
        // Events binded to document.body to avoid firefox fire events on right/central click
        document.body.addEventListener('click', rcTagManagerLib.eventClickPromotionItem, false);

        if (rcTagManagerLib.trackingFeatures.goals.socialAction) {
            // bind event on like/follow action
            rcTagManagerLib.eventSocialFollow();
        }


        ////////////////////////
        // ALL PAGES EXCEPT CHECKOUT OR ORDER
        if (!isCheckout && !isOrder) {
            // bind prestashop events with tracking events
            prestashop.on(
                'updateCart',
                function (event) {
                    rcTagManagerLib.eventAddCartProduct(event);
                }
            );
            prestashop.on(
                'clickQuickView',
                function (event) {
                    rcTagManagerLib.eventProductView(event)
                }
            );
            prestashop.on(
                'updatedProduct',
                function (event) {
                    rcTagManagerLib.eventProductView(event)
                }
            );

            // init first scroll action for those products all ready visible on screen
            rcTagManagerLib.eventScrollList();
            // bind event to scroll
            window.addEventListener('scroll', rcTagManagerLib.eventScrollList.bind(rcTagManagerLib), false);

            // init Event Listeners
            document.body.addEventListener('click', rcTagManagerLib.eventClickProductList, false);
            document.body.addEventListener('click', rcTagManagerLib.eventGetAddCartQuantity, false);

            if (rcTagManagerLib.trackingFeatures.goals.socialAction) {
                // bind event to allow track social action on
                document.body.addEventListener('click', rcTagManagerLib.eventSocialShareProductView, false);
            }
            ////////////////////////
            // SEARCH PAGE
            if (controllerName === 'search') {
                rcTagManagerLib.eventSearchResult();
            }
            ////////////////////////
            // PRODUCT PAGE
            if (controllerName === 'product') {
                // send product detail view
                rcTagManagerLib.eventProductView();
            }
        }

        ////////////////////////
        // CHECKOUT PROCESS
        if (isCheckout) {
            // SUMMARY CART
            if (controllerName === 'cart') {
                // events on summary Cart
                document.body.addEventListener('click', rcTagManagerLib.eventCartQuantityDelete, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartQuantityUp, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartQuantityDown, false);
            }
            ////////////////////////
            // CHECKOUT
            if (compliantModuleName === 'default' && controllerName === 'order') {
                // Events on Checkout Process
                document.body.addEventListener('click', rcTagManagerLib.eventPrestashopCheckout, false);
            } else if (
                compliantModuleName === 'supercheckout'
                && controllerName === compliantModules[compliantModuleName]
            ) {
                // Compatible with super-checkout by Knowband
                document.body.addEventListener('click', rcTagManagerLib.eventOpcSuperCheckout, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartOpcSuperCheckout, false);
            } else if (
                compliantModuleName === 'onepagecheckoutps'
                && controllerName === compliantModules[compliantModuleName]
            ) {
                // compatible with OPC by PrestaTeamShop
                document.body.addEventListener('click', rcTagManagerLib.eventOpcPrestaTeam, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartOpcPrestaTeam, false);
            } else if (
                compliantModuleName === 'thecheckout'
                && controllerName === compliantModules[compliantModuleName]
            ) {
                // Compatible with thecheckout by Zelarg
                document.body.addEventListener('click', rcTagManagerLib.eventOpcTheCheckout, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartOpcTheCheckout, false);
            } else if (
                compliantModuleName === 'steasycheckout'
                && controllerName === compliantModules[compliantModuleName]
            ) {
                // Events for steasycheckout
                document.body.addEventListener('click', rcTagManagerLib.eventOpcStEasyCheckout, false);
                document.body.addEventListener('click', rcTagManagerLib.eventCartOpcStEasyCheckout, false);
            }
        }
    }

    function fireEventsOnPageShow(event){
        // fixes safari back cache button
        if (event.persisted) {
            window.location.reload()
        }

        // Sign up feature
        if (rcTagManagerLib.trackingFeatures.goals.signUp && rcTagManagerLib.trackingFeatures.common.isNewSignUp) {
            rcTagManagerLib.onSignUp();
        }

        if (rcTagManagerLib.trackingFeatures.gua.trackingId && isClientId) {
            rcTagManagerLib.setClientId();
        }

        // Checkout and order complete
        if (isCheckout && gtmProducts) {
            rcTagManagerLib.onCheckoutProducts(gtmProducts);
        } else if (isOrder && gtmOrderComplete) {
            rcTagManagerLib.onOrderComplete(gtmOrderComplete);
        }
    }
</script>