/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a trade license awarded by
 * Garamo Online L.T.D.
 *
 * Any use, reproduction, modification or distribution
 * of this source file without the written consent of
 * Garamo Online L.T.D It Is prohibited.
 *
 * @author    ReactionCode <info@reactioncode.com>
 * @copyright 2015-2021 Garamo Online L.T.D
 * @license   Commercial license
 */

function RcTagManagerLib() {
    // reference to this
    var publicValues = this;

    ////////////////////////////////////
    // Private values

    var privateValues = {};

    // get module url from common js var prestashop
    privateValues.moduleUrl =
        prestashop.urls.base_url + 'modules/rc_pgtagmanager/';

    // don't change this value!! used for redirect after hit
    privateValues.redirected = false;
    privateValues.redirectLink = null;

    // products position detected on scroll tracking
    privateValues.productsPosition = {};

    // scroll tracking event
    privateValues.initial = true;
    privateValues.sendProducts = [];
    privateValues.sentProducts = [];
    privateValues.sendPromotions = [];
    privateValues.sentPromotions = [];
    privateValues.scrollTimeout = null;

    // product page
    privateValues.lastIdProductView = '';

    privateValues.cartQuantity = 1;

    // don not track
    privateValues.doNotTrack =
        window.doNotTrack == '1' ||
        navigator.doNotTrack == 'yes' ||
        navigator.doNotTrack == '1' ||
        navigator.msDoNotTrack == '1';

    ////////////////////////////////////
    // Public values

    // all tracking features
    publicValues.trackingFeatures = null;

    publicValues.lists = null;

    // cache products
    publicValues.productsListCache = null;

    // remarketing page type
    publicValues.ecommPageType = '';

    // checkout data
    publicValues.controllerName = '';
    publicValues.isCheckout = '';
    publicValues.compliantModuleName = '';
    publicValues.skipCartStep = '';
    publicValues.opcCheckoutOption = 'payment / shipping';

    // Public Customer Events
    publicValues.eventSocialFollow = eventSocialFollow;
    publicValues.eventSearchResult = eventSearchResult;
    publicValues.eventScrollList = eventScrollList;
    publicValues.eventClickPromotionItem = eventClickPromotionItem;
    publicValues.eventClickProductList = eventClickProductList;
    publicValues.eventProductView = eventProductView;
    publicValues.eventSocialShareProductView = eventSocialShareProductView;
    publicValues.eventGetAddCartQuantity = eventGetAddCartQuantity;
    publicValues.eventAddCartProduct = eventAddCartProduct;
    publicValues.eventCartQuantityUp = eventCartQuantityUp;
    publicValues.eventCartQuantityDown = eventCartQuantityDown;
    publicValues.eventCartQuantityDelete = eventCartQuantityDelete;
    publicValues.eventPrestashopCheckout = eventPrestashopCheckout;
    publicValues.eventOpcSuperCheckout = eventOpcSuperCheckout;
    publicValues.eventCartOpcSuperCheckout = eventCartOpcSuperCheckout;
    publicValues.eventOpcPrestaTeam = eventOpcPrestaTeam;
    publicValues.eventCartOpcPrestaTeam = eventCartOpcPrestaTeam;
    publicValues.eventOpcTheCheckout = eventOpcTheCheckout;
    publicValues.eventCartOpcTheCheckout = eventCartOpcTheCheckout;
    publicValues.eventOpcStEasyCheckout = eventOpcStEasyCheckout;
    publicValues.eventCartOpcStEasyCheckout = eventCartOpcStEasyCheckout;

    // Public GTM Methods
    publicValues.onCheckoutProducts = onCheckoutProducts;
    publicValues.onOrderComplete = onOrderComplete;
    publicValues.onSignUp = onSignUp;

    // Common Methods
    publicValues.setClientId = setClientIdInDb;

    // Singleton Pattern
    if (RcTagManagerLib.prototype.getInstance) {
        return RcTagManagerLib.prototype.getInstance;
    }

    RcTagManagerLib.prototype.getInstance = this;
    ///////////////////////////////////////////////
    // THEME EVENTS

    // ALL PAGES - SOCIAL FOLLOW
    function eventSocialFollow() {
        // facebook events
        if (typeof window.FB === 'object') {
            // like page
            window.FB.Event.subscribe('edge.create', function(url) {
                onSocialAction('facebook', 'like', url);
            });
            // unlike page
            window.FB.Event.subscribe('edge.remove', function(url) {
                onSocialAction('facebook', 'unlike', url);
            });
        }

        // twitter events
        if (typeof window.twttr === 'object' && window.twttr.events) {
            // follow page
            window.twttr.events.bind('follow', function() {
                onSocialAction('twitter', 'follow');
            });
            // tweet comment
            window.twttr.events.bind('tweet', function() {
                onSocialAction('twitter', 'tweet');
            });
        }
    }

    // SEARCH RESULT - Get search term
    function eventSearchResult() {
        var searchWordNode;
        var searchTerm;

        if (document.body.id === 'search') {
            searchWordNode =
                document.querySelector('[name=s]') ||
                document.querySelector('[name=q]') ||
                document.querySelector('[name=search_query]');
            searchTerm = searchWordNode.value || null;
            onSearchResults(searchTerm);
        }
    }

    // PRODUCT LISTS - Scroll
    function eventScrollList() {
        if (!privateValues.initial) {
            clearTimeout(privateValues.scrollTimeout);
            scrollElementDetection();

            privateValues.scrollTimeout = setTimeout(function() {
                if (
                    privateValues.sendProducts.length ||
                    privateValues.sendPromotions.length
                ) {
                    doneScroll();
                }
            }, 800);
        } else {
            privateValues.initial = false;
            scrollElementDetection();
            doneScroll();
        }
    }

    // PROMOTION CLICK - Click on promotion
    function eventClickPromotionItem(event) {
        var mainSelector = ['.js-ga-track-promo a'];
        var target = delegateEvents(mainSelector, event.target);
        var promoQuery;
        var promoLink;

        // Check if Google Tag Manager is blocked by uBlock or similar
        if (event.button === 0 && target && target.nodeName === 'A' && window.google_tag_manager) {
            promoQuery = target.search;
            promoLink = target.href;

            if (promoQuery && promoLink) {
                event.preventDefault();
                onPromotionClick(promoQuery, promoLink);
            }
        }
    }

    // PRODUCT LISTS - CLICK TO VIEW PRODUCT
    function eventClickProductList(event) {
        var mainSelector = ['.js-product-miniature'];
        var variantSelector = ['.js-product-miniature .variant-links a'];
        var eventSelectors = ['.js-product-miniature a'];
        var target = delegateEvents(eventSelectors, event.target);
        var caseClick = 1;
        var classList;
        var link;
        var productNode;
        var variantNode;
        var variantAttribute;
        var idProduct;
        var idProductAttribute;
        var list;

        // Check if Google analytics is blocked by uBlock or similar
        if (event.button === 0 && target && target.nodeName === 'A' && window.google_tag_manager) {
            // if click done with ctrl or shift key avoid preventDefault
            if (!event.ctrlKey && !event.shiftKey) {
                // get the target class list
                classList = target.classList;

                // If Quick view event don't get link redirection
                if (
                    !classList.contains('quick-view') &&
                    !classList.contains('quick-view-mobile')
                ) {
                    // retrieve the product link.
                    link = target.getAttribute('href');

                    if (link) {
                        // prevent redirection on normal click
                        event.preventDefault();
                    }
                }
            }

            // Get the product node
            productNode = delegateEvents(mainSelector, target);

            // Get variant node
            variantNode = delegateEvents(variantSelector, target);

            if (productNode) {
                idProduct = parseInt(
                    productNode.getAttribute('data-id-product')
                );
                idProductAttribute = parseInt(
                    productNode.getAttribute('data-id-product-attribute')
                );
            }

            // Check if any filter is applied
            list = checkFilters();

            if (!isNaN(idProduct)) {
                // If selected color variant
                if (variantNode) {
                    // get the attribute selected
                    variantAttribute = variantNode.getAttribute(
                        'data-id-product-attribute'
                    );

                    if (variantAttribute) {
                        // if exist update the id product attribute
                        idProductAttribute = variantAttribute;
                    }
                }

                // check if idProductAttribute has valid value
                if (isNaN(idProductAttribute)) {
                    idProductAttribute = 0;
                }

                // add the attribute to idProduct
                idProduct = idProduct + '-' + idProductAttribute;

                // Send data to GTM without link redirection
                getData(caseClick, idProduct, list, link, null);
            } else if (link) {
                // If idProduct not detected try redirect to product page
                document.location = link;
            }
        }
    }

    // PRODUCT VIEW - View
    function eventProductView(event) {
        var caseClick = 4;
        var productDetailsNode;
        var productDetails;
        var idProductValue;
        var idProductAttributeValue;
        var idProductView;

        if (document.body.id !== 'product') {
            if (event && event.dataset) {
                // first quick view display
                idProductValue = event.dataset.idProduct;
                idProductAttributeValue = event.dataset.idProductAttribute;
            } else {
                // quick view mode
                idProductValue = document.querySelector(
                    '#product_page_product_id'
                ).value;
                idProductAttributeValue = event.id_product_attribute;
            }
        } else {
            // body id product
            productDetailsNode = document.querySelector('#product-details');
            productDetails = JSON.parse(productDetailsNode.dataset.product);
            idProductValue = productDetails.id_product;
            idProductAttributeValue = productDetails.id_product_attribute;
        }

        // normalize id product to track
        idProductView = idProductValue + '-' + idProductAttributeValue;

        // avoid send productView multiple times when change quantity
        if (idProductView !== privateValues.lastIdProductView) {
            getData(caseClick, idProductView, null, null, null);
            privateValues.lastIdProductView = idProductView;
        }
    }

    // PRODUCT VIEW - SOCIAL ACTIONS SHARE ON NETWORK
    function eventSocialShareProductView(event) {
        var eventSelectors = [
            '.facebook',
            '.twitter',
            '.googleplus',
            '.pinterest',
        ];
        var target = delegateEvents(eventSelectors, event.target);
        var network = '';
        var action = 'share_product';

        if (event.button === 0 && target && window.google_tag_manager) {
            if (target) {
                eventSelectors.forEach(function(platform) {
                    // remove first char
                    platform = platform.substring(1);
                    // check if platform match
                    if (target.classList.contains(platform)) {
                        network = platform;
                    }
                });

                if (network) {
                    onSocialAction(network, action);
                }
            }
        }
    }

    function eventGetAddCartQuantity(event) {
        var eventSelectors = ['.add-to-cart'];
        var target = delegateEvents(eventSelectors, event.target);
        var formNode;
        var inputQtyNode;

        privateValues.addCartQuantity = 1;

        if (event.button === 0 && target && window.google_tag_manager) {
            formNode = target.closest('form');
            if (formNode) {
                inputQtyNode = formNode.querySelector('[name=qty]');
                if (inputQtyNode) {
                    privateValues.addCartQuantity = parseInt(inputQtyNode.value);
                }
            }
        }
    }

    // COMMON - Add to cart
    function eventAddCartProduct(event) {
        var caseClick = 2;
        var idProduct;
        var idProductAttribute;

        if (
            event &&
            event.hasOwnProperty('reason') &&
            event.hasOwnProperty('resp') &&
            !!event.resp.success &&
            document.body.id !== 'cart'
        ) {
            idProduct = parseInt(event.reason.idProduct);
            idProductAttribute = parseInt(event.reason.idProductAttribute);

            if (!isNaN(idProduct)) {
                // check if idProductAttribute has valid value
                if (isNaN(idProductAttribute)) {
                    idProductAttribute = 0;
                }

                // add the attribute to idProduct
                idProduct = idProduct + '-' + idProductAttribute;

                getData(
                    caseClick,
                    idProduct,
                    null,
                    null,
                    privateValues.addCartQuantity
                );
            }
        }
    }

    // SUMMARY CART - INCREASE PRODUCT
    function eventCartQuantityUp(event) {
        var eventSelectors = ['.js-increase-product-quantity', '.bootstrap-touchspin-up'];
        var mainSelector = ['.cart-item'];
        var target = delegateEvents(eventSelectors, event.target);
        var caseClick = 2;
        var quantityWanted = 1;
        var mainNode;
        var dataNode;
        var idProduct;
        var idProductAttribute;

        if (event.button === 0 && target && window.google_tag_manager) {
            mainNode = delegateEvents(mainSelector, target);
            dataNode = mainNode.querySelector('.remove-from-cart');

            if (dataNode) {
                idProduct = parseInt(dataNode.getAttribute('data-id-product'));
                idProductAttribute = parseInt(
                    dataNode.getAttribute('data-id-product-attribute')
                );
            }

            if (!isNaN(idProduct)) {
                // check if idProductAttribute has valid value
                if (isNaN(idProductAttribute)) {
                    idProductAttribute = 0;
                }

                // add the attribute to idProduct
                idProduct = idProduct + '-' + idProductAttribute;

                getData(
                    caseClick,
                    idProduct,
                    publicValues.lists.default,
                    null,
                    quantityWanted
                );
            }
        }
    }

    // SUMMARY CART - DECREASE PRODUCT
    function eventCartQuantityDown(event) {
        var eventSelectors = ['.js-decrease-product-quantity', '.bootstrap-touchspin-down'];
        var mainSelector = ['.cart-item'];
        var target = delegateEvents(eventSelectors, event.target);
        var caseClick = 3;
        var quantityRemoved = 1;
        var mainNode;
        var dataNode;
        var idProduct;
        var idProductAttribute;

        if (event.button === 0 && target && window.google_tag_manager) {
            mainNode = delegateEvents(mainSelector, target);
            dataNode = mainNode.querySelector('.remove-from-cart');

            if (dataNode) {
                idProduct = parseInt(dataNode.getAttribute('data-id-product'));
                idProductAttribute = parseInt(
                    dataNode.getAttribute('data-id-product-attribute')
                );
            }

            if (!isNaN(idProduct)) {
                // check if idProductAttribute has valid value
                if (isNaN(idProductAttribute)) {
                    idProductAttribute = 0;
                }

                // add the attribute to idProduct
                idProduct = idProduct + '-' + idProductAttribute;

                getData(
                    caseClick,
                    idProduct,
                    publicValues.lists.default,
                    null,
                    quantityRemoved
                );
            }
        }
    }

    // SUMMARY CART - REMOVE PRODUCT
    function eventCartQuantityDelete(event) {
        var eventSelectors = ['.remove-from-cart'];
        var mainSelector = ['.cart-item'];
        var target = delegateEvents(eventSelectors, event.target);
        var caseClick = 3;
        var mainNode;
        var idProduct;
        var idProductAttribute;
        var quantityRemoved;

        if (event.button === 0 && target && window.google_tag_manager) {
            idProduct = parseInt(target.getAttribute('data-id-product'));
            idProductAttribute = parseInt(
                target.getAttribute('data-id-product-attribute')
            );

            mainNode = delegateEvents(mainSelector, target);

            if (mainNode) {
                quantityRemoved = mainNode.querySelector(
                    '.js-cart-line-product-quantity'
                );
                quantityRemoved = parseInt(
                    quantityRemoved ? quantityRemoved.value : null
                );
            }

            if (!isNaN(idProduct) && !isNaN(quantityRemoved)) {
                // check if idProductAttribute has valid value
                if (isNaN(idProductAttribute)) {
                    idProductAttribute = 0;
                }

                // add the attribute to idProduct
                idProduct = idProduct + '-' + idProductAttribute;

                // send data to GTM
                getData(
                    caseClick,
                    idProduct,
                    publicValues.lists.default,
                    null,
                    quantityRemoved
                );
            }
        }
    }

    // CHECKOUT - basic PS checkout
    function eventPrestashopCheckout(event) {
        var eventSelectors = [
            '#checkout-delivery-step button',
            '#payment-confirmation button',
        ];
        var target = delegateEvents(eventSelectors, event.target);

        var currentStepNode;
        var shippingNode;
        var paymentNode;
        var checkoutValue;
        var checkoutOption;

        if (event.button === 0 && target && window.google_tag_manager) {
            // get selected step node
            currentStepNode = document.querySelector('.js-current-step');

            if (currentStepNode.id === 'checkout-delivery-step') {
                // get shipping option
                shippingNode = document.querySelector('[id^=delivery_option_]:checked');
                shippingNode = delegateEvents(['.delivery-option'], shippingNode);
                shippingNode = shippingNode ? shippingNode.querySelector('.carrier-name') : null;
                checkoutValue = shippingNode ? shippingNode.textContent.trim() : null;
                checkoutOption = 'Delivery Selected';
            } else if (currentStepNode.id === 'checkout-payment-step') {
                // get payment option
                paymentNode = document.querySelector('[id^=payment-option-]:checked');
                checkoutValue = paymentNode ? paymentNode.dataset.moduleName : 'not detected';
                checkoutOption = 'Payment Selected';
            }

            if (checkoutOption && checkoutValue) {
                onCheckoutOption(checkoutOption, checkoutValue);
            }
        }
    }

    // CHECKOUT - opc by knowband
    function eventOpcSuperCheckout(event) {
        var eventSelectors = ['#supercheckout_confirm_order'];
        var mainCarrierSelector = ['.highlight'];
        var mainPaymentSelector = ['.highlight'];
        var target = delegateEvents(eventSelectors, event.target);

        var cgv;
        var shippingNode;
        var shippingOption;
        var paymentNode;
        var paymentOption;
        var checkoutValue;

        if (event.button === 0 && target && window.google_tag_manager) {
            cgv = document.querySelector('input[id^=conditions_to_approve]');

            if (!cgv || cgv.checked) {
                // get selected shipping node
                shippingNode = document.querySelector(
                    '.supercheckout_shipping_option:checked'
                );

                // if virtual product don't has any shipping node
                if (shippingNode) {
                    shippingNode = delegateEvents(
                        mainCarrierSelector,
                        shippingNode
                    );
                    shippingNode =
                        shippingNode.querySelector('label img') ||
                        shippingNode.querySelector('label');

                    // get selected shipping option
                    shippingOption = shippingNode
                        ? shippingNode.getAttribute('alt') ||
                          shippingNode.textContent.trim()
                        : '';
                    shippingOption = normalizeText(shippingOption);
                }

                // get selected payment node
                paymentNode = document.querySelector(
                    '#payment-method input:checked'
                );
                paymentNode = delegateEvents(mainPaymentSelector, paymentNode);
                paymentNode =
                    paymentNode.querySelector('label img') ||
                    paymentNode.querySelector('label span');

                // get selected payment option
                paymentOption = paymentNode
                    ? paymentNode.getAttribute('alt') ||
                      paymentNode.textContent.trim()
                    : '';
                paymentOption = normalizeText(paymentOption);

                // prepare option and send data to GTM
                checkoutValue = paymentOption + ' / ' + shippingOption;
                onCheckoutOption(publicValues.opcCheckoutOption, checkoutValue);
            }
        }
    }

    function eventCartOpcSuperCheckout(event) {
        var eventSelectors = [
            '.increase_button',
            '.decrease_button',
            '.removeProduct',
        ];
        var mainSelector = ['[id^=product_]'];

        var target = delegateEvents(eventSelectors, event.target);
        var targetClassList;

        // default case click is add to cart
        var caseClick = 2;
        var quantity = 1;

        var mainNode;
        var dataNode;
        var quantityNode;
        var ids;
        var idProduct;
        var idProductAttribute;
        var indexedProduct;

        if (event.button === 0 && target && window.google_tag_manager) {
            // get the class list collection
            targetClassList = target.classList;
            mainNode = delegateEvents(mainSelector, target);

            if (mainNode) {
                dataNode = mainNode.id;

                if (dataNode) {
                    ids = dataNode.split('_');
                    idProduct = parseInt(ids[1]);
                    idProductAttribute = parseInt(ids[2]);
                }

                if (!isNaN(idProduct)) {
                    // check if idProductAttribute has valid value
                    if (isNaN(idProductAttribute)) {
                        idProductAttribute = 0;
                    }

                    // add the attribute to idProduct
                    indexedProduct = idProduct + '-' + idProductAttribute;

                    if (
                        targetClassList.contains('decrease_button') ||
                        targetClassList.contains('removeProduct')
                    ) {
                        // set case click to remove from cart
                        caseClick = 3;

                        // check if action is remove product and get the quantity
                        if (targetClassList.contains('removeProduct')) {
                            quantityNode = mainNode.querySelector(
                                '.quantitybox'
                            );
                            quantity = parseInt(
                                quantityNode ? quantityNode.value : null
                            );
                        }
                    }

                    // send data to GTM
                    getData(
                        caseClick,
                        indexedProduct,
                        publicValues.lists.default,
                        null,
                        quantity
                    );
                }
            }
        }
    }

    // CHECKOUT - opc by prestateam
    function eventOpcPrestaTeam(event) {
        var eventSelectors = ['#btn_place_order'];
        var mainCarrierSelector = ['.delivery-option'];
        var mainPaymentSelector = ['.module_payment_container'];
        var target = delegateEvents(eventSelectors, event.target);

        var cgv;
        var shippingNode;
        var shippingOption;
        var paymentNode;
        var paymentOption;
        var checkoutValue;

        if (event.button === 0 && target && window.google_tag_manager) {
            cgv = document.querySelector('#cgv');

            if (!cgv || cgv.checked) {
                // get selected shipping node
                shippingNode = document.querySelector(
                    '.delivery_option_radio:checked'
                );
                // get selected payment node
                paymentNode = document.querySelector('.payment_radio:checked');

                // if virtual product don't has any shipping node
                if (shippingNode) {
                    shippingNode = delegateEvents(
                        mainCarrierSelector,
                        shippingNode
                    );
                    shippingNode = shippingNode.querySelector(
                        '.delivery_option_title'
                    );

                    // get selected shipping option
                    shippingOption = shippingNode
                        ? shippingNode.textContent.trim()
                        : '';
                    shippingOption = normalizeText(shippingOption);
                }

                if (paymentNode) {
                    // continue only if payment selected
                    paymentNode = delegateEvents(
                        mainPaymentSelector,
                        paymentNode
                    );
                    paymentNode = paymentNode.querySelector(
                        '.payment_content span'
                    );

                    // get selected payment option
                    paymentOption = paymentNode
                        ? paymentNode.textContent.trim()
                        : '';
                    paymentOption = normalizeText(paymentOption);

                    // prepare option and send data to GTM
                    checkoutValue = paymentOption + ' / ' + shippingOption;
                    onCheckoutOption(
                        publicValues.opcCheckoutOption,
                        checkoutValue
                    );
                }
            }
        }
    }

    function eventCartOpcPrestaTeam(event) {
        var eventSelectors = [
            '.bootstrap-touchspin-up',
            '.bootstrap-touchspin-down',
            '.remove-from-cart',
        ];
        var mainSelector = ['.bootstrap-touchspin'];

        var target = delegateEvents(eventSelectors, event.target);
        var targetClassList;

        // default case click is add to cart
        var caseClick = 2;
        var quantity = 1;

        var mainNode;
        var dataNode;
        var quantityNode;
        var idProduct;
        var idProductAttribute;
        var indexedProduct;

        if (event.button === 0 && target && window.google_tag_manager) {
            // get the class list collection
            targetClassList = target.classList;
            mainNode = delegateEvents(mainSelector, target);

            if (mainNode) {
                dataNode = mainNode.querySelector('.remove-from-cart');

                if (dataNode) {
                    idProduct = dataNode.dataset.idProduct;
                    idProductAttribute = dataNode.dataset.idProductAttribute;

                    if (!isNaN(idProduct)) {
                        // check if idProductAttribute has valid value
                        if (isNaN(idProductAttribute)) {
                            idProductAttribute = 0;
                        }

                        // add the attribute to idProduct
                        indexedProduct = idProduct + '-' + idProductAttribute;

                        if (
                            targetClassList.contains(
                                'bootstrap-touchspin-down'
                            ) ||
                            targetClassList.contains('remove-from-cart')
                        ) {
                            // set case click to remove from cart
                            caseClick = 3;

                            // check if action is remove product and get the quantity
                            if (targetClassList.contains('remove-from-cart')) {
                                quantityNode = mainNode.querySelector(
                                    '.cart-line-product-quantity'
                                );
                                quantity = parseInt(
                                    quantityNode ? quantityNode.value : null
                                );
                            }
                        }

                        // send data to GTM
                        getData(
                            caseClick,
                            indexedProduct,
                            publicValues.lists.default,
                            null,
                            quantity
                        );
                    }
                }
            }
        }
    }

    // CHECKOUT - opc by zelarg
    function eventOpcTheCheckout(event) {
        var eventSelectors = ['#confirm_order'];
        var mainCarrierSelector = ['.delivery-option'];
        var mainPaymentSelector = ['.payment-option'];
        var target = delegateEvents(eventSelectors, event.target);

        var requiredCheckBox1;
        var requiredCheckBox2;
        var shippingNode;
        var shippingOption;
        var paymentNode;
        var paymentOption;
        var checkoutValue;

        if (event.button === 0 && target && window.google_tag_manager) {
            requiredCheckBox1 = document.querySelector(
                'input[name=required-checkbox-1]'
            );
            requiredCheckBox2 = document.querySelector(
                'input[name=required-checkbox-2]'
            );

            if (
                (!requiredCheckBox1 || requiredCheckBox1.checked) &&
                (!requiredCheckBox2 || requiredCheckBox2.checked)
            ) {
                // get selected shipping node
                shippingNode = document.querySelector(
                    '[id^=delivery_option]:checked'
                );
                // get selected payment node
                paymentNode = document.querySelector(
                    '[id^=payment-option]:checked'
                );

                // if virtual product don't has any shipping node
                if (shippingNode) {
                    shippingNode = shippingNode.closest(mainCarrierSelector);
                    shippingNode = shippingNode.querySelector('.carrier-name');

                    // get selected shipping option
                    shippingOption = shippingNode
                        ? shippingNode.textContent.trim()
                        : '';
                    shippingOption = normalizeText(shippingOption);
                }

                if (paymentNode) {
                    // continue only if payment selected
                    paymentNode = paymentNode.closest(mainPaymentSelector);
                    paymentNode = paymentNode.querySelector(
                        'label[for^=payment-option-] span'
                    );

                    // get selected payment option
                    paymentOption = paymentNode
                        ? paymentNode.textContent.trim()
                        : '';
                    paymentOption = normalizeText(paymentOption);

                    // prepare option and send data to GTM
                    checkoutValue = paymentOption + ' / ' + shippingOption;
                    onCheckoutOption(
                        publicValues.opcCheckoutOption,
                        checkoutValue
                    );
                }
            }
        }
    }

    function eventCartOpcTheCheckout(event) {
        var eventSelectors = [
            '.cart-line-product-quantity-up',
            '.cart-line-product-quantity-down',
            '.remove-from-cart',
        ];
        var mainSelector = ['.product-line-actions'];

        var target = delegateEvents(eventSelectors, event.target);
        var targetClassList;

        // default case click is add to cart
        var caseClick = 2;
        var quantity = 1;

        var mainNode;
        var dataNode;
        var quantityNode;
        var idProduct;
        var idProductAttribute;
        var indexedProduct;

        if (event.button === 0 && target && window.google_tag_manager) {
            // get the class list collection
            targetClassList = target.classList;
            mainNode = delegateEvents(mainSelector, target);

            if (mainNode) {
                dataNode = mainNode.querySelector('.remove-from-cart');

                if (dataNode) {
                    idProduct = dataNode.dataset.idProduct;
                    idProductAttribute = dataNode.dataset.idProductAttribute;

                    if (!isNaN(idProduct)) {
                        // check if idProductAttribute has valid value
                        if (isNaN(idProductAttribute)) {
                            idProductAttribute = 0;
                        }

                        // add the attribute to idProduct
                        indexedProduct = idProduct + '-' + idProductAttribute;

                        if (
                            targetClassList.contains(
                                'cart-line-product-quantity-down'
                            ) ||
                            targetClassList.contains('remove-from-cart')
                        ) {
                            // set case click to remove from cart
                            caseClick = 3;

                            // check if action is remove product and get the quantity
                            if (targetClassList.contains('remove-from-cart')) {
                                quantityNode = mainNode.querySelector(
                                    '.cart-line-product-quantity'
                                );
                                quantity = parseInt(
                                    quantityNode ? quantityNode.value : null
                                );
                            }
                        }

                        // send data to GTM
                        getData(
                            caseClick,
                            indexedProduct,
                            publicValues.lists.default,
                            null,
                            quantity
                        );
                    }
                }
            }
        }
    }

    function eventOpcStEasyCheckout (event) {
        var eventSelectors = ['.steco_confirmation_btn'];
        var mainCarrierSelector = ['.delivery-option'];
        var mainPaymentSelector = ['.steco-payment-option'];
        var target = delegateEvents(eventSelectors, event.target);

        var cgv;
        var shippingNode;
        var shippingOption;
        var paymentNode;
        var paymentOption;
        var checkoutValue;

        if (event.button === 0 && target && window.google_tag_manager) {
            cgv = document.querySelector('#conditions_to_approve[terms-and-conditions]');

            if (!cgv || cgv.checked) {
                // get selected shipping node
                shippingNode = document.querySelector('.delivery-option :checked');
                // get selected payment node
                paymentNode = document.querySelector('.steco-payment-option :checked');

                // if virtual product don't has any shipping node
                if (shippingNode) {
                    shippingNode = delegateEvents(
                        mainCarrierSelector,
                        shippingNode
                    );
                    shippingNode = shippingNode.querySelector(
                        '.carrier-name'
                    );

                    // get selected shipping option
                    shippingOption = shippingNode
                        ? shippingNode.textContent.trim()
                        : '';
                    shippingOption = normalizeText(shippingOption);
                }

                if (paymentNode) {
                    // continue only if payment selected
                    paymentNode = delegateEvents(
                        mainPaymentSelector,
                        paymentNode
                    );

                    paymentNode = paymentNode.querySelector('.steco_payment_option_title') ||
                        paymentNode.querySelector('input').dataset.moduleName;


                    // get selected payment option
                    paymentOption = paymentNode
                        ? paymentNode.textContent.trim()
                        : '';
                    paymentOption = normalizeText(paymentOption);

                    // prepare option and send data to GTM
                    checkoutValue = paymentOption + ' / ' + shippingOption;
                    onCheckoutOption(publicValues.opcCheckoutOption, checkoutValue);
                }
            }
        }
    }

    function eventCartOpcStEasyCheckout (event) {
        var eventSelectors = [
            '.bootstrap-touchspin-up',
            '.bootstrap-touchspin-down',
            '.remove-from-cart',
        ];
        var mainSelector = ['.line_item'];

        var target = delegateEvents(eventSelectors, event.target);
        var targetClassList;

        // default case click is add to cart
        var caseClick = 2;
        var quantity = 1;

        var mainNode;
        var dataNode;
        var quantityNode;
        var quantityValue;
        var idProduct;
        var idProductAttribute;
        var indexedProduct;

        if (event.button === 0 && target && window.google_tag_manager) {
            // get the class list collection
            targetClassList = target.classList;
            mainNode = delegateEvents(mainSelector, target);

            if (mainNode) {
                dataNode = mainNode.querySelector('.js-cart-line-product-quantity') ||
                    mainNode.querySelector('.remove-from-cart')
                ;

                if (dataNode) {
                    idProduct = dataNode.dataset.idProduct || dataNode.dataset.productId;
                    idProductAttribute = dataNode.dataset.idProductAttribute;

                    if (!isNaN(idProduct)) {
                        // check if idProductAttribute has valid value
                        if (isNaN(idProductAttribute)) {
                            idProductAttribute = 0;
                        }

                        // add the attribute to idProduct
                        indexedProduct = idProduct + '-' + idProductAttribute;

                        if (targetClassList.contains('bootstrap-touchspin-down') ||
                            targetClassList.contains('remove-from-cart')
                        ) {
                            // set case click to remove from cart
                            caseClick = 3;

                            // check if action is remove product and get the quantity
                            if (targetClassList.contains('remove-from-cart')) {
                                quantityNode = mainNode.querySelector('.js-cart-line-product-quantity') ||
                                    mainNode.querySelector('.product-quantity')
                                ;
                                quantityValue = quantityNode.value || quantityNode.innerText;
                                quantity = parseInt(quantityValue);
                            }
                        }

                        // send data to GTM
                        getData(
                            caseClick,
                            indexedProduct,
                            publicValues.lists.default,
                            null,
                            quantity
                        );
                    }
                }
            }
        }
    }
    /////////////////////////////////////////////
    // GTM EVENTS

    // SEARCH RESULT - get the search term
    function onSearchResults(searchTerm) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'searchResults',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'view_search_results',
            eventLabel: searchTerm,
            eventValue: '',
        };
        // if search term are not empty send the event
        if (searchTerm) {
            // set search term
            dataLayerObj.eventLabel = searchTerm;

            pushDataLayer(dataLayerObj);
        }
    }

    // gtm event - scroll tracking
    function onScrollTracking(products) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'scrollTracking',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'view_item_list',
            eventLabel: '',
            eventValue: '',
        };
        var remarketingLayer;
        var sendNow;
        var commonContent = {
            'productIds' : []
        };

        // check if is an array and is not empty
        if (Array.isArray(products) && products.length) {
            while (products.length > 0) {
                // get products to send
                sendNow = products.splice(
                    0,
                    publicValues.trackingFeatures.gua.sendLimit
                );

                // init values to avoid send duplicates
                dataLayerObj.ecommerce = {
                    currencyCode:
                        publicValues.trackingFeatures.common.currencyCode,
                    impressions: '',
                };
                remarketingLayer = '';

                // GA Enhanced Ecommerce
                if (publicValues.trackingFeatures.gua.trackingId) {
                    dataLayerObj.ecommerce = {
                        currencyCode:
                            publicValues.trackingFeatures.common.currencyCode,
                        impressions: getProductsLayered(sendNow, 'gua'),
                    };
                }

                // remarketing data layer
                if (
                    publicValues.trackingFeatures.gua.trackingId ||
                    publicValues.trackingFeatures.googleAds.trackingId
                ) {
                    if (
                        publicValues.trackingFeatures.gua.remarketingFeature ||
                        publicValues.trackingFeatures.gua.businessDataFeature
                    ) {
                        remarketingLayer = getRemarketingLayer(
                            sendNow,
                            publicValues.ecommPageType
                        );
                        // merge layers
                        Object.assign(dataLayerObj, remarketingLayer);
                    }
                }

                sendNow.forEach(function(product) {
                    commonContent.productIds.push(product.id);
                });

                dataLayerObj.common = commonContent;

                pushDataLayer(dataLayerObj);
            }
        }
    }

    // gtm event - promo view
    function onPromotionView(promotions) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'promotionView',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'view_promotion',
            eventLabel: '',
            eventValue: '',
        };

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            dataLayerObj.ecommerce = {
                promoView: {
                    promotions: getPromotionsLayered(promotions),
                },
            };
        }

        pushDataLayer(dataLayerObj);
    }

    // gtm event - promo click
    function onPromotionClick(promotion, link) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'promotionClick',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'select_content',
            eventLabel: 'promotion',
            eventValue: '',
        };

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            dataLayerObj.ecommerce = {
                promoClick: {
                    promotions: [getPromotionLayer(promotion)],
                },
            };
        }

        if (link) {
            privateValues.redirectLink = link;
            dataLayerObj.eventCallback = callbackWithTimeout(function() {
                redirectLink();
            }, 2000);
        }

        pushDataLayer(dataLayerObj);
    }

    // gtm event - product view click
    function onProductClick(product, link) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'productClick',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'select_content',
            eventLabel: 'product_list',
            eventValue: '',
        };

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            dataLayerObj.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                click: {
                    actionField: { list: product.list },
                    products: [getProductLayer(product, 'gua')],
                },
            };
        }

        if (link) {
            privateValues.redirectLink = link;
            dataLayerObj.eventCallback = callbackWithTimeout(function() {
                redirectLink();
            }, 2000);
        }

        pushDataLayer(dataLayerObj);
    }

    // gtm event - view on product page
    function onProductDetail(product) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'productDetail',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'view_item',
            eventLabel: '',
            eventValue: '',
        };
        var productDetailLayer = getProductDetailLayer(product);

        // merge layers
        Object.assign(dataLayerObj, productDetailLayer);

        pushDataLayer(dataLayerObj);
    }

    // gtm event - add to cart on product click
    function onAddToCart(product, link) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'addToCart',
            // event definition for all tracking layers
            eventCategory: 'ecommerce',
            eventAction: 'add_to_cart',
            eventLabel: '',
            eventValue: '',
        };
        var addToCartLayer = getAddToCartLayer(product);

        // merge layers
        Object.assign(dataLayerObj, addToCartLayer);

        if (!publicValues.trackingFeatures.common.cartAjax && link) {
            privateValues.redirectLink = link;
            dataLayerObj.eventCallback = callbackWithTimeout(function() {
                redirectLink();
            }, 2000);
        }

        pushDataLayer(dataLayerObj);
    }

    // gtm event - remove from cart click
    function onRemoveFromCart(product, link) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'removeFromCart',
            // event definition for all tracking layers
            eventCategory: 'ecommerce',
            eventAction: 'remove_from_cart',
            eventLabel: '',
            eventValue: '',
        };

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            dataLayerObj.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                remove: {
                    actionField: { list: product.list },
                    products: [getProductLayer(product, 'gua')],
                },
            };
        }

        if (!publicValues.trackingFeatures.common.cartAjax && link) {
            privateValues.redirectLink = link;
            dataLayerObj.eventCallback = callbackWithTimeout(function() {
                redirectLink();
            }, 2000);
        }

        pushDataLayer(dataLayerObj);
    }

    // gtm event - send products and actual checkout step
    function onCheckoutProducts(checkoutProducts) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'checkout',
            // event definition for all tracking layers
            eventCategory: 'ecommerce',
            eventAction: 'checkout_progress',
            eventLabel: '',
            eventValue: '',
        };
        var checkoutLayer;

        // get actual checkout step
        var currentCheckoutStep = getCheckOutStep();

        // set the event label with actual step
        dataLayerObj.eventLabel = 'step_' + currentCheckoutStep;

        if (currentCheckoutStep === 1) {
            dataLayerObj.eventAction = 'begin_checkout';
        }

        // get layer after get checkout step
        checkoutLayer = getCheckoutLayer(checkoutProducts);

        // merge layers
        Object.assign(dataLayerObj, checkoutLayer);

        pushDataLayer(dataLayerObj);
    }

    // gtm event - checkout options selected by customer
    function onCheckoutOption(checkoutOption, checkoutValue) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'checkoutOption',
            // event definition for all tracking layers
            eventCategory: 'ecommerce',
            eventAction: 'set_checkout_option',
            eventLabel: checkoutValue,
            eventValue: '',
        };

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            dataLayerObj.ecommerce = {
                checkout_option: {
                    actionField: {
                        step: getCheckOutStep(),
                        option: checkoutValue,
                    },
                },
            };
        }

        // Common checkout layer
        dataLayerObj.common = {
            checkoutOptionLabel: checkoutOption,
            checkoutOptionValue: checkoutValue,
        };

        pushDataLayer(dataLayerObj);
    }

    // gtm event - order complete
    function onOrderComplete(orderComplete) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'orderComplete',
            // event definition for all tracking layers
            eventCategory: 'ecommerce',
            eventAction: 'purchase',
            eventLabel: '',
            eventValue: '',
        };
        var orderLayer = getOrderLayer(orderComplete);

        // merge orderLayer into dataLayer
        Object.assign(dataLayerObj, orderLayer);

        // after data layer send, include order to control table to avoid duplicates
        dataLayerObj.eventCallback = callbackWithTimeout(function() {
            setOrderInDb(orderComplete.id, orderComplete.idShop);
        }, 1000);

        // send order event
        pushDataLayer(dataLayerObj);

        // if enabled goal and order complete has coupons
        if (
            publicValues.trackingFeatures.goals.coupon &&
            Array.isArray(orderComplete.coupons)
        ) {
            // send 1 event goal for coupon
            orderComplete.coupons.forEach(function(coupon) {
                onCoupon(coupon);
            });
        }
    }

    // gtm event - new customer registration
    function onSignUp() {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'signUpGoal',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: 'sign_up',
            eventLabel: '',
            eventValue:
                publicValues.trackingFeatures.common.eventValues.signUpGoal,
        };
        var index = publicValues.trackingFeatures.common.isGuest;

        // assign customer type to label
        dataLayerObj.eventLabel =
            publicValues.trackingFeatures.common.signUpTypes[index];

        if (
            publicValues.trackingFeatures.goals.signUp &&
            publicValues.trackingFeatures.common.isNewSignUp
        ) {
            // send data layer
            pushDataLayer(dataLayerObj);

            // reset values to avoid multiple sends
            publicValues.trackingFeatures.common.isNewSignUp = 0;
            publicValues.trackingFeatures.common.isGuest = 0;
        }
    }

    // gtm event - social network action
    function onSocialAction(network, action, target) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'socialAction',
            // event definition for all tracking layers
            eventCategory: 'engagement',
            eventAction: action,
            eventLabel: network,
            eventValue:
                publicValues.trackingFeatures.common.eventValues.socialAction,
        };

        target = target || null;

        // GA - Social Tracking
        if (publicValues.trackingFeatures.gua.trackingId) {
            dataLayerObj.gua = {
                social: {
                    network: network,
                    action: action,
                    target: target,
                },
            };
        }

        if (publicValues.trackingFeatures.goals.socialAction) {
            // send data layer
            pushDataLayer(dataLayerObj);
        }
    }

    // gtm event - coupon used
    function onCoupon(coupon) {
        var dataLayerObj = {
            // gtm - trigger event
            event: 'coupon',
            // event definition for all tracking layers
            eventCategory: 'promotion',
            eventAction: 'coupon',
            eventLabel: coupon,
            eventValue: publicValues.trackingFeatures.common.eventValues.coupon,
        };

        if (publicValues.trackingFeatures.goals.coupon) {
            // send data layer
            pushDataLayer(dataLayerObj);
        }
    }

    // send data layer to gtm
    function pushDataLayer(dataLayerObj) {
        var dataLayer = window.dataLayer || [];

        if (typeof dataLayerObj === 'object') {
            dataLayer.push(dataLayerObj);
        }
    }
    /////////////////////////////////////////////
    // GTM DATA LAYER - TOOLS
    function getPromotionsLayered(promotions) {
        var promotionsLayered = [];

        promotions.forEach(function(promotion) {
            promotionsLayered.push(getPromotionLayer(promotion));
        });

        return promotionsLayered;
    }

    function getPromotionLayer(promotion) {
        var promotionFields = {
            pid: 'id',
            pn: 'name',
            pc: 'creative',
            pp: 'position',
        };
        var promotionLayer = {};
        var promotionQueryData;
        var gaKey;

        promotionQueryData = getQueryData(promotion);

        Object.keys(promotionFields).forEach(function(key) {
            gaKey = promotionFields[key];
            if (promotionQueryData.hasOwnProperty(key)) {
                promotionLayer[gaKey] = decodeURIComponent(
                    promotionQueryData[key]
                );
            }
        });
        return promotionLayer;
    }

    function getProductsLayered(products, platform) {
        var productsLayered = [];

        for (var i = 0; i < products.length; i++) {
            productsLayered.push(getProductLayer(products[i], platform));
        }

        return productsLayered;
    }

    function getProductLayer(product, platform) {
        var productFields = {
            gua: [
                'id',
                'name',
                'category',
                'brand',
                'price',
                'list',
                'position',
                'variant',
                'quantity',
            ],
            facebook: ['id', 'stock', 'quantity', 'price'],
            common: [
                'id',
                'name',
                'category',
                'brand',
                'price',
                'list',
                'position',
                'variant',
                'quantity',
                'ean13',
                'upc',
                'reference',
            ],
        };
        var renameFields = {
            facebook: { price: 'item_price', stock: 'quantity' },
        };
        var productLayer = {};

        if (productFields.hasOwnProperty(platform)) {
            productFields[platform].forEach(function(field) {
                // check that product has the property
                if (product.hasOwnProperty(field) && product[field] !== null) {
                    // handles id catalog for facebook ids
                    if (platform === 'facebook' && field === 'id') {
                        productLayer[field] = getFeedIdProduct(
                            product.id,
                            product.id_attribute,
                            publicValues.trackingFeatures.facebook.catalogPrefix,
                            publicValues.trackingFeatures.facebook.catalogVariant,
                            publicValues.trackingFeatures.facebook.catalogSuffix
                        );
                    } else if (
                        renameFields[platform] &&
                        renameFields[platform][field]
                    ) {
                        productLayer[renameFields[platform][field]] =
                            product[field];
                    } else {
                        productLayer[field] = product[field];
                    }
                }
            });
        }

        return productLayer;
    }

    function getRemarketingLayer(products, ecommPageType) {
        var ecommDimensions = {};
        var businessDimensions = {};
        var remarketingLayer = { gua: {}, google_tag_params: {} };
        var totalValue = 0;
        var productPrice = 0;

        products.forEach(function(product) {
            // set basic product price
            productPrice = product.price;

            // check if product have quantity
            if (product.quantity) {
                productPrice = productPrice * product.quantity;
            }
            // calc total_value dimension and cut to 2 decimals
            totalValue = parseFloat((totalValue + productPrice).toFixed(2));

            if (publicValues.trackingFeatures.gua.remarketingFeature) {
                ecommDimensions = processEcommProduct(
                    product,
                    ecommDimensions,
                    ecommPageType,
                    totalValue
                );
            }

            // add products ids and attribute ids to dynx tags
            if (publicValues.trackingFeatures.gua.businessDataFeature) {
                businessDimensions = processBusinessProduct(
                    product,
                    businessDimensions,
                    ecommPageType,
                    totalValue
                );
            }
        });

        // merge business data and remarketing dimensions to remarketing layer
        Object.assign(
            remarketingLayer.gua,
            ecommDimensions,
            businessDimensions
        );

        remarketingLayer.google_tag_params = processGoogleTagParams(
            ecommDimensions,
            businessDimensions
        );

        return remarketingLayer;
    }

    function getProductDetailLayer(product) {
        var remarketingLayer = {};
        var productDetailLayer = {};
        var ecomm_pageType = publicValues.ecommPageType;

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            productDetailLayer.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                detail: {
                    actionField: { list: product.list },
                    products: [getProductLayer(product, 'gua')],
                },
            };
        }

        // remarketing data layer
        if (
            publicValues.trackingFeatures.gua.trackingId ||
            publicValues.trackingFeatures.googleAds.trackingId
        ) {
            if (
                publicValues.trackingFeatures.gua.remarketingFeature ||
                publicValues.trackingFeatures.gua.businessDataFeature
            ) {
                remarketingLayer = getRemarketingLayer(
                    [product],
                    ecomm_pageType
                );
                // merge layers
                Object.assign(productDetailLayer, remarketingLayer);
            }
        }

        // facebook data layer
        if (publicValues.trackingFeatures.facebook.trackingId) {
            productDetailLayer.facebook = {
                contents: [getProductLayer(product, 'facebook')],
                contentType: 'product',
            };
        }

        // twitter data layer
        if (publicValues.trackingFeatures.twitter.trackingId) {
            // populate data layer
            productDetailLayer.twitter = {
                contentIds: JSON.stringify([product.id]),
                contentType: 'product',
            };
        }

        // populate a common product layer
        productDetailLayer.common = {
            product: getProductLayer(product, 'common'),
        };

        return productDetailLayer;
    }

    function getAddToCartLayer(product) {
        var addToCartLayer = {};
        var remarketingLayer = {};
        var ecommPageType = 'cart';

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            addToCartLayer.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                add: {
                    actionField: { list: product.list },
                    products: [getProductLayer(product, 'gua')],
                },
            };
        }

        // remarketing data layer
        if (
            publicValues.trackingFeatures.gua.trackingId ||
            publicValues.trackingFeatures.googleAds.trackingId
        ) {
            if (
                publicValues.trackingFeatures.gua.remarketingFeature ||
                publicValues.trackingFeatures.gua.businessDataFeature
            ) {
                remarketingLayer = getRemarketingLayer(
                    [product],
                    ecommPageType
                );
                // merge layers
                Object.assign(addToCartLayer, remarketingLayer);
            }
        }

        // facebook data layer
        if (publicValues.trackingFeatures.facebook.trackingId) {
            addToCartLayer.facebook = {
                contents: [getProductLayer(product, 'facebook')],
                contentType: 'product',
            };
        }

        // twitter data layer
        if (publicValues.trackingFeatures.twitter.trackingId) {
            // populate data layer
            addToCartLayer.twitter = {
                contentIds: JSON.stringify([product.id]),
                contentType: 'product',
            };
        }

        // populate a common product layer
        addToCartLayer.common = {
            product: getProductLayer(product, 'common'),
        };

        return addToCartLayer;
    }

    function getCheckoutLayer(checkoutProducts) {
        var checkoutContents = {
            productsIds: [],
            productsEan: [],
            productsReference: [],
            amount: 0,
            totalCart: 0,
        };
        var currentCheckoutStep = getCheckOutStep();
        var checkoutLayer = {};
        var remarketingLayer = {};

        // get all product ids into array and count all product quantities
        checkoutProducts.forEach(function(product) {
            checkoutContents.productsIds.push(product.id);
            checkoutContents.productsEan.push(product.ean13);
            checkoutContents.productsReference.push(product.reference);
            checkoutContents.amount += product.quantity;
            checkoutContents.totalCart += product.quantity * product.price;
        });

        // GA Enhanced Ecommerce
        if (publicValues.trackingFeatures.gua.trackingId) {
            // prepare data layer
            checkoutLayer.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                checkout: {
                    actionField: { step: currentCheckoutStep },
                    products: getProductsLayered(checkoutProducts, 'gua'),
                },
            };
        }

        // remarketing data layer
        if (
            publicValues.trackingFeatures.gua.trackingId ||
            publicValues.trackingFeatures.googleAds.trackingId
        ) {
            if (
                publicValues.trackingFeatures.gua.remarketingFeature ||
                publicValues.trackingFeatures.gua.businessDataFeature
            ) {
                remarketingLayer = getRemarketingLayer(
                    checkoutProducts,
                    publicValues.ecommPageType
                );
                // merge layers
                Object.assign(checkoutLayer, remarketingLayer);
            }
        }

        // initiate checkout
        if (currentCheckoutStep === 1) {
            // facebook data layer
            if (publicValues.trackingFeatures.facebook.trackingId) {
                checkoutLayer.facebook = {
                    contents: getProductsLayered(checkoutProducts, 'facebook'),
                    contentType: 'product',
                };
            }

            // twitter data layer
            if (publicValues.trackingFeatures.twitter.trackingId) {
                // populate data layer
                checkoutLayer.twitter = {
                    contentIds: JSON.stringify(checkoutContents.productsIds),
                    contentType: 'product',
                };
            }
        }

        // Common checkout layer
        checkoutLayer.common = {
            checkoutStep: currentCheckoutStep,
            products: getProductsLayered(checkoutProducts, 'common'),
            productIds: checkoutContents.productsIds,
            productEans: checkoutContents.productsEan,
            productReferences: checkoutContents.productsReference,
            numItems: checkoutContents.amount,
            totalCart: (
                Math.round(checkoutContents.totalCart * 100) / 100
            ).toFixed(2),
        };

        return checkoutLayer;
    }

    function getOrderLayer(orderComplete) {
        var orderContents = {
            productsIds: [],
            productsEan: [],
            productsReference: [],
            amount: 0,
        };
        var orderLayer = {};
        var remarketingLayer = {};

        // get all product ids into array and count all product quantities
        orderComplete.products.forEach(function(product) {
            orderContents.productsIds.push(product.id);
            orderContents.productsEan.push(product.ean13);
            orderContents.productsReference.push(product.reference);
            orderContents.amount += product.quantity;
        });

        // google analytics data layer
        if (publicValues.trackingFeatures.gua.trackingId) {
            // populate data layer
            orderLayer.ecommerce = {
                currencyCode: publicValues.trackingFeatures.common.currencyCode,
                purchase: {
                    actionField: {
                        id: orderComplete.id,
                        affiliation: orderComplete.affiliation,
                        revenue: orderComplete.revenue,
                        tax: orderComplete.tax,
                        shipping: orderComplete.shipping,
                    },
                    products: getProductsLayered(orderComplete.products, 'gua'),
                },
            };

            // if order has coupon add it
            if (
                Array.isArray(orderComplete.coupons) &&
                orderComplete.coupons.length
            ) {
                orderLayer.ecommerce.purchase.actionField.coupon = orderComplete.coupons.join(
                    ' / '
                );
            }
        }

        // remarketing data layer
        if (
            publicValues.trackingFeatures.gua.trackingId ||
            publicValues.trackingFeatures.googleAds.trackingId
        ) {
            if (
                publicValues.trackingFeatures.gua.remarketingFeature ||
                publicValues.trackingFeatures.gua.businessDataFeature
            ) {
                remarketingLayer = getRemarketingLayer(
                    orderComplete.products,
                    publicValues.ecommPageType
                );
                // merge layers
                Object.assign(orderLayer, remarketingLayer);
            }
        }

        // facebook data layer
        if (publicValues.trackingFeatures.facebook.trackingId) {
            // populate data layer
            orderLayer.facebook = {
                contents: getProductsLayered(
                    orderComplete.products,
                    'facebook'
                ),
                contentType: 'product',
            };
        }

        // twitter data layer
        if (publicValues.trackingFeatures.twitter.trackingId) {
            // populate data layer
            orderLayer.twitter = {
                contentIds: JSON.stringify(orderContents.productsIds),
                contentType: 'product',
            };
        }

        // Common order layer
        orderLayer.common = {
            orderId: orderComplete.id,
            products: getProductsLayered(orderComplete.products, 'common'),
            productIds: orderContents.productsIds,
            productEans: orderContents.productsEan,
            productReferences: orderContents.productsReference,
            numItems: orderContents.amount,
            orderRevenue: orderComplete.revenue,
            emailHash: orderComplete.emailHash,
            coupons: orderComplete.coupons,
        };

        return orderLayer;
    }

    /////////////////////////////////////////////
    // AJAX REQUEST

    // AJAX - get Product data and send to GA
    function getData(caseClick, idProducts, list, link, quantityWanted) {
        var req = new XMLHttpRequest();
        var url = privateValues.moduleUrl + 'rc_pgtagmanager-ajax.php';
        var data = {
            action: 'product',
            products_position: privateValues.productsPosition,
            list: list,
            quantity_wanted: quantityWanted,
            products_list_cache: publicValues.productsListCache,
        };
        var formData;

        if (typeof idProducts === 'object') {
            // for products lists
            data['id_products'] = idProducts;
        } else {
            // for product page or events
            data['id_products'] = [idProducts];
        }

        formData = new FormData();
        formData.append('data', encodeURIComponent(JSON.stringify(data)));
        formData.append('token', publicValues.trackingFeatures.common.token);

        req.open('POST', url, true);
        req.responseType = 'json';
        req.onload = () => {
            try {
                if (req.status === 200) {
                    if (caseClick === 0) {
                        onScrollTracking(req.response);
                    } else if (caseClick === 1) {
                        onProductClick(req.response[0], link);
                    } else if (caseClick === 2) {
                        onAddToCart(req.response[0], link);
                    } else if (caseClick === 3) {
                        onRemoveFromCart(req.response[0], link);
                    } else if (caseClick === 4) {
                        onProductDetail(req.response[0]);
                    }
                } else {
                    throw 'Unexpected XHR error';
                }
            } catch (error) {
                console.warn('rcpgtagmanager: ' + error);
                if (link) {
                    // add redirect to product page.
                    privateValues.redirectLink = link;
                    redirectLink();
                }
            }
        };
        req.send(formData);
    }

    // Ajax Call - after sent transaction to GA set order data in DB
    function setOrderInDb(orderId, idShop) {
        var req = new XMLHttpRequest();
        var url = privateValues.moduleUrl + 'rc_pgtagmanager-ajax.php';
        var data = {
            action: 'orderComplete',
            is_order: true,
            id_order: orderId,
            id_shop: idShop,
            id_customer: publicValues.trackingFeatures.common.userId,
        };

        var adBlocker = !(window.google_tag_manager && window.GoogleAnalyticsObject);
        var doNotTrack =
            publicValues.trackingFeatures.checkDoNotTrack &&
            privateValues.doNotTrack;
        var formData;

        // check if ga is loaded
        if (doNotTrack || adBlocker) {
            data.action = 'abortedTransaction';
            data.doNotTrack = privateValues.doNotTrack;
            data.adBlocker = adBlocker;
        }

        formData = new FormData();
        formData.append('data', encodeURIComponent(JSON.stringify(data)));
        formData.append('token', publicValues.trackingFeatures.common.token);

        req.open('POST', url, true);
        req.send(formData);
    }

    // Ajax Call - check if clientId exist and set to control DB
    function setClientIdInDb() {
        var clientId;
        var trackers;
        var req;
        var url;
        var data;
        var formData;

        // fire only when ga is enabled
        if (window.ga) {
            ga(function() {
                // get all trackers
                trackers = ga.getAll();
                // check is trackers is an Array and is not empty
                if (Array.isArray(trackers) && trackers.length) {
                    // get clientId of customer
                    clientId = trackers[0].get('clientId');

                    if (
                        clientId &&
                        clientId !== publicValues.trackingFeatures.gua.clientId
                    ) {
                        req = new XMLHttpRequest();
                        url = privateValues.moduleUrl + 'rc_pgtagmanager-ajax.php';
                        data = {
                            action: 'clientId',
                            id_customer: publicValues.trackingFeatures.common.userId,
                            id_shop: publicValues.trackingFeatures.common.idShop,
                            client_id: clientId,
                        };

                        formData = new FormData();
                        formData.append('data', encodeURIComponent(JSON.stringify(data)));
                        formData.append('token', publicValues.trackingFeatures.common.token);

                        req.open('POST', url, true);
                        // setRequestHeader breaks the formData object, don't add it
                        req.send(formData);
                    }
                }
            });
        }
    }

    /////////////////////////////////////////////
    // EVENTS - TOOLS
    // SCROLL - Detect products  and promos on scroll
    function scrollElementDetection() {
        var products = document.querySelectorAll('.js-product-miniature');
        var promos = document.querySelectorAll('.js-ga-track-promo');

        if (products.length) {
            processScrollElement(products, 'product');
        }

        if (promos.length) {
            processScrollElement(promos, 'promo');
        }
    }

    function processScrollElement(elements, type) {
        var visibleElement;
        var idProduct;
        var idProductAttribute;
        var isInViewport;

        elements.forEach(function(element) {
            isInViewport = isElementInViewport(element);

            if (isInViewport) {
                // handle product cases
                if (type === 'product') {
                    // get product data
                    idProduct = parseInt(
                        element.getAttribute('data-id-product')
                    );
                    idProductAttribute =
                        parseInt(
                            element.getAttribute('data-id-product-attribute')
                        ) | 0;

                    if (!isNaN(idProduct)) {
                        // set element index format
                        visibleElement = idProduct + '-' + idProductAttribute;

                        // check that element has not sent and is not a duplicate
                        if (
                            privateValues.sentProducts.indexOf(
                                visibleElement
                            ) === -1 &&
                            privateValues.sendProducts.indexOf(
                                visibleElement
                            ) === -1
                        ) {
                            privateValues.sendProducts.push(visibleElement);
                        }
                    }
                }

                // handle promo cases
                else if (type === 'promo') {
                    // index promotions with query selector
                    visibleElement = element.querySelector('a').search;

                    if (visibleElement) {
                        // check that element has not sent and is not a duplicate
                        if (
                            privateValues.sentPromotions.indexOf(
                                visibleElement
                            ) === -1 &&
                            privateValues.sendPromotions.indexOf(
                                visibleElement
                            ) === -1
                        ) {
                            privateValues.sendPromotions.push(visibleElement);
                        }
                    }
                }
            }
        });
    }

    // SCROLL - Calc product position
    function scrollProductPositionDetection() {
        // populate productsPosition counting
        // every product with class .js-product-miniature
        var products = document.querySelectorAll('.js-product-miniature');
        var actualPosition = getInitPosition();
        var productKey;
        var idProduct;
        var idProductAttribute;

        products.forEach(function(product) {
            idProduct = parseInt(product.getAttribute('data-id-product'));
            idProductAttribute = parseInt(
                product.getAttribute('data-id-product-attribute')
            );

            if (isNaN(idProductAttribute)) {
                idProductAttribute = 0;
            }

            if (!isNaN(idProduct)) {
                productKey = idProduct + '-' + idProductAttribute;

                // check if productsPosition has the product ID as key
                if (
                    !privateValues.productsPosition.hasOwnProperty(productKey)
                ) {
                    privateValues.productsPosition[productKey] = actualPosition;
                    actualPosition++;
                }
            }
        });
    }
    // SCROLL - Get initial product position
    function getInitPosition() {
        var pagination;
        var itemsNumber;

        pagination = document.querySelector(
            '.current .disabled.js-search-link'
        );
        pagination = pagination ? pagination.textContent.trim() : 1;
        itemsNumber = publicValues.trackingFeatures.common.productsPerPage;

        // get the first product position
        return (
            parseInt(itemsNumber) * parseInt(pagination) -
            parseInt(itemsNumber) +
            1
        );
    }
    // SCROLL - Launch event
    function doneScroll() {
        var caseClick = 0;
        var list;

        // check if exists new products to send
        if (privateValues.sendProducts.length > 0) {
            // calculate products position in each scroll for possible lazy loads products
            scrollProductPositionDetection();
            list = checkFilters();
            // process data to GA
            getData(caseClick, privateValues.sendProducts, list, null, null);
            // add new products to sent list
            Array.prototype.push.apply(
                privateValues.sentProducts,
                privateValues.sendProducts
            );
            // reset sendProducts to avoid multiple sends
            privateValues.sendProducts = [];
        }

        // check if exists new promotions to send
        if (privateValues.sendPromotions.length > 0) {
            // send promo view to GA
            onPromotionView(privateValues.sendPromotions);
            // add new products to sent list
            Array.prototype.push.apply(
                privateValues.sentPromotions,
                privateValues.sendPromotions
            );
            // reset sendPromotions to avoid multiple sends
            privateValues.sendPromotions = [];
        }
        clearTimeout(privateValues.scrollTimeout);
    }

    function checkFilters() {
        var list = publicValues.lists.default;
        // get filter nodes
        var isEnabledFilter = document.querySelector('#js-active-search-filters');
        var pmAdvancedSearch = document.querySelector('.PM_ASResetGroup');

        if ((isEnabledFilter && isEnabledFilter.className === 'active_filters') ||
            pmAdvancedSearch
        ) {
            list = publicValues.lists.filter;
        } else if (document.body.id === 'search') {
            publicValues.ecommPageType = 'searchresults';
        }

        return list;
    }

    // REMARKETING - Generate custom id product to match with remarketing data feed
    function getFeedIdProduct(
        idProduct,
        idAttribute,
        feedPrefix,
        feedVariant,
        feedSuffix
    ) {
        var feedIdProduct = idProduct;

        if (feedVariant && idAttribute) {
            feedIdProduct = idProduct + feedVariant + idAttribute;
        }

        return feedPrefix + feedIdProduct + feedSuffix;
    }

    function processEcommProduct(
        product,
        ecommDimensions,
        ecommPageType,
        totalValue
    ) {
        var feedIdProduct;

        // set pagetype
        ecommDimensions.ecommPageType = ecommPageType;

        // set ecommProdId
        if (
            ecommPageType === 'product' ||
            ecommPageType === 'cart' ||
            ecommPageType === 'purchase'
        ) {
            // feed id product
            feedIdProduct = getFeedIdProduct(
                product.id,
                product.id_attribute,
                publicValues.trackingFeatures.gua.merchantPrefix,
                publicValues.trackingFeatures.gua.merchantVariant,
                publicValues.trackingFeatures.gua.merchantSuffix
            );

            if (ecommPageType === 'cart' || ecommPageType === 'purchase') {
                // init ecom_prodid_item at first loop
                if (!ecommDimensions.hasOwnProperty('ecommProdId')) {
                    ecommDimensions.ecommProdId = [];
                }

                // add product dimension
                ecommDimensions.ecommProdId.push(feedIdProduct);
            } else {
                ecommDimensions.ecommProdId = feedIdProduct;
            }
        }

        // set ecommTotalValue
        if (
            ecommPageType === 'product' ||
            ecommPageType === 'cart' ||
            ecommPageType === 'purchase'
        ) {
            // update total value dimension
            ecommDimensions.ecommTotalValue = totalValue;
        }

        // set ecommCategory
        if (
            (ecommPageType === 'category' || ecommPageType === 'product') &&
            product.category
        ) {
            ecommDimensions.ecommCategory = product.category;
        }
        return ecommDimensions;
    }

    function processBusinessProduct(
        product,
        businessDimensions,
        ecommPageType,
        totalValue
    ) {
        var dynxPageTypes = {
            home: 'home',
            searchresults: 'searchresults',
            product: 'offerdetail',
            cart: 'conversionintent',
            purchase: 'conversion',
        };
        var dynxPageType = 'other';
        var idAttribute;
        var feedIdProduct;

        // convert ecomm pagetype to dynx page type
        if (dynxPageTypes.hasOwnProperty(ecommPageType)) {
            dynxPageType = dynxPageTypes[ecommPageType];
        }

        // set dynx_pagetype
        businessDimensions.dynxPageType = dynxPageType;

        // set dynxItemId and dynxItemId2
        if (
            dynxPageType === 'searchresults' ||
            dynxPageType === 'offerdetail' ||
            dynxPageType === 'conversionintent' ||
            dynxPageType === 'conversion'
        ) {
            // basic id product
            feedIdProduct = getFeedIdProduct(
                product.id,
                product.id_attribute,
                publicValues.trackingFeatures.gua.businessDataPrefix,
                publicValues.trackingFeatures.gua.businessDataVariant,
                ''
            );

            // if don't exist variant separator add attribute on itemid2
            if (!publicValues.trackingFeatures.gua.businessDataVariant) {
                // init dynx_item2 at first loop
                if (!businessDimensions.hasOwnProperty('dynxItemId2')) {
                    businessDimensions.dynxItemId2 = [];
                }

                if (product.id_attribute) {
                    idAttribute = product.id_attribute.toString();
                }

                // add data to itemid2
                businessDimensions.dynxItemId2.push(idAttribute);
            }

            // init dynx_item at first loop
            if (!businessDimensions.hasOwnProperty('dynxItemId')) {
                businessDimensions.dynxItemId = [];
            }
            // add data to itemid
            businessDimensions.dynxItemId.push(feedIdProduct);

            // set dynxTotalValue
            if (
                dynxPageType === 'offerdetail' ||
                dynxPageType === 'conversionintent' ||
                dynxPageType === 'conversion'
            ) {
                // update total value dimension
                businessDimensions.dynxTotalValue = totalValue;
            }
        }
        return businessDimensions;
    }

    function processGoogleTagParams(ecommDimensions, businessDimensions) {
        var gaTagParamsLink = {
            ecommProdId: 'ecomm_prodid',
            ecommPageType: 'ecomm_pagetype',
            ecommCategory: 'ecomm_category',
            ecommTotalValue: 'ecomm_totalvalue',
            dynxItemId: 'dynx_itemid',
            dynxItemId2: 'dynx_itemid2',
            dynxPageType: 'dynx_pagetype',
            dynxTotalValue: 'dynx_totalvalue',
        };
        var google_tag_params = {};

        Object.keys(gaTagParamsLink).forEach(function(key) {
            if (ecommDimensions.hasOwnProperty(key)) {
                google_tag_params[gaTagParamsLink[key]] = ecommDimensions[key];
            }

            if (businessDimensions.hasOwnProperty(key)) {
                google_tag_params[gaTagParamsLink[key]] =
                    businessDimensions[key];
            }
        });

        return google_tag_params;
    }

    // CHECKOUT - get step position
    function getCheckOutStep() {
        var currentStepValue = 1;
        var currentStepNode;

        if (publicValues.isCheckout) {
            if (
                document.body.id === 'checkout' &&
                publicValues.controllerName === 'order' &&
                publicValues.compliantModuleName === 'default'
            ) {
                // get selected step node
                currentStepNode = document.querySelector('.js-current-step');

                // get step value of selected step
                switch (currentStepNode.id) {
                    case 'checkout-personal-information-step':
                        currentStepValue = 2;
                        break;
                    case 'checkout-addresses-step':
                        currentStepValue = 3;
                        break;
                    case 'checkout-delivery-step':
                        currentStepValue = 4;
                        break;
                    case 'checkout-payment-step':
                        currentStepValue = 5;
                        break;
                }
            } else if (
                publicValues.controllerName ===
                    publicValues.compliantModuleName ||
                (publicValues.controllerName === 'order' &&
                    publicValues.compliantModuleName === 'onepagecheckoutps' &&
                    publicValues.skipCartStep === '') ||
                (publicValues.controllerName === 'default' &&
                    publicValues.compliantModuleName === 'steasycheckout' &&
                    publicValues.skipCartStep === '')
            ) {
                currentStepValue = 2;
            }
            // assign current checkout step
            return currentStepValue;
        }
    }

    ///////////////////////
    // COMMON TOOLS

    // GENERAL - redirect to new location
    function redirectLink() {
        if (!privateValues.redirected) {
            // set flag to avoid multiple redirection
            privateValues.redirected = true;
            window.location = privateValues.redirectLink;
        }
    }

    // GENERAL - timeout method to avoid page blocking
    function callbackWithTimeout(callback, timeout) {
        var called = false;

        function fn() {
            if (!called) {
                called = true;
                callback();
            }
        }
        setTimeout(fn, timeout || 1000);

        return fn;
    }

    // parse query link to get object
    function getQueryData(query) {
        var vars = {};
        query.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
            vars[key] = value;
        });
        return vars;
    }

    // check if element is inside viewport
    function isElementInViewport(element) {
        var isVisible = false;
        var winHeight = window.innerHeight;
        var winOffset = window.pageYOffset;
        var minY = winOffset;
        var maxY = winOffset + winHeight;
        var itemTop;
        var itemBottom;
        var elHeight;
        var elComputedStyle;
        var elHeightPadding;
        var rect;

        // size of inner height including padding
        elHeight = element.clientHeight;

        // if elHeight === 0 means element is not visible or have display none
        if (elHeight) {
            // get computed styles to retrieve the real padding applied on css styles.
            elComputedStyle = getComputedStyle(element);

            // sum the top and bottom padding to get the height padding
            elHeightPadding =
                parseInt(elComputedStyle.paddingTop) +
                parseInt(elComputedStyle.paddingBottom);

            // get element rectangle
            rect = element.getBoundingClientRect();

            // calc element display position
            itemTop = rect.top + winOffset;
            itemBottom = itemTop + (elHeight - elHeightPadding);

            // check if element is inside display
            isVisible =
                (itemTop >= minY && itemTop < maxY) ||
                (itemBottom >= minY && itemBottom < maxY);
        }
        return isVisible;
    }

    // Remove extra spaces
    function normalizeText(text) {
        var filtered = '';

        if (typeof text === 'string') {
            filtered = text.replace(/^\s+|\n+.*/g, '').trim();
        }

        return filtered;
    }

    // EVENT TOOLS - LIKE JQ CLOSEST
    function delegateEvents(selectors, target) {
        var matchMode;

        if (target) {
            // get available browser matches function
            matchMode =
                target.matches ||
                target.webkitMatchesSelector ||
                target.msMatchesSelector;

            // get function name (general browsers || iE9)
            matchMode =
                matchMode.name ||
                /function\s+([\w\$]+)\s*\(/.exec(matchMode.toString());

            // on iE9 get the name value, empty value on anonymous fn
            if (typeof matchMode !== 'string') {
                matchMode = matchMode ? matchMode[1] : '';
            }

            // continue only if we get matches selector function
            if (matchMode) {
                while (target.parentNode !== null) {
                    if (target.nodeType === 1) {
                        // iterate all selectors
                        for (var i = 0; i < selectors.length; i++) {
                            // compare if node match with selector
                            if (target[matchMode](selectors[i])) {
                                // if match return target
                                return target;
                            }
                        }
                    }
                    // if no match or nodeType !== 1 go to parent
                    target = target.parentNode;
                }
            }
        }
    }
}

