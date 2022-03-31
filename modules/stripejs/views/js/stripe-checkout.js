/**
 * 2007-2018 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function() {
	
	if (typeof StripePubKey =='undefined') {
		return;
	}
	
		var handler = StripeCheckout.configure({
		key: publishableKey,
		image: logo_url,
		currency:currency,
		email:cu_email,
		locale:popup_locale,
		zipCode:stripe_allow_zip,
		bitcoin:stripe_allow_btc,
		allowRememberMe:false,
		token: function(token) {
			
			/* Disable the submit button to prevent repeated clicks */
			  $('#payment-confirmation button[type=submit]').prop("disabled", true)
			  $('.stripe-payment-errors-checkout').hide();
			  $('#stripe-ajax-loader-checkout').show();

		  Stripe.source.create({
			  type: token.type,
			  token: token.id
		  }, function (status, response) {

            if (response.error) {
                // Show error on the form
                $('#stripe-ajax-loader-checkout').hide();
				$('#payment-confirmation button[type=submit]').removeAttr('disabled');

                var err_msg = $('#stripe-'+response.error.code).val();
                if (!err_msg || err_msg == "undefined" || err_msg == '')
                    err_msg = response.error.message;
                $('.stripe-payment-errors-checkout').text(err_msg).fadeIn(1000);
            } else {
				
                if ((secure_mode || response.card.three_d_secure == 'required') && response.card.three_d_secure != 'undefined' && response.card.three_d_secure != "not_supported" && token.type=='card') {
					
                    Stripe.source.create({
                        type: 'three_d_secure',
                        amount: amount_ttl,
                        currency: currency,
                        three_d_secure: {
                            card: response.id
                        },
                        owner: {
                            address: {
                                line1: billing_address.line1,
                                line2: billing_address.line2,
                                city: billing_address.city,
                                postal_code: billing_address.zip_code,
                                country: billing_address.country
                            },
                            email: billing_address.email,
                        },
                        redirect: {
                            return_url: baseDir+"modules/stripejs/conf_3d.php"
                        }
                    }, function (status, response) {
						
						 if (response.status == "pending") {
                            $('#modal_stripe').modalStripe({cloning: false, closeOnOverlayClick: false, closeOnEsc: false}).open();
                            Stripe.threeDSecure.createIframe(response.redirect.url, result_3d, callbackFunction3D);
                            $('#result_3d iframe').css({
                                height: '700px',
                                width: '100%'
                            });
							
							var secure_timeout = setTimeout(timeout_secure3D, 220000);

							$('#result_3d iframe').load(function(){
								
							    Stripe.source.poll(
                                response.id,
                                response.client_secret,
                                function(status, source) {
                                    if (source.status == "chargeable") {
                                        $('#modal_stripe').modalStripe().close();
                                        create3DCharge(source);
                                    } else if (source.status == "failed") {
                                        $('#result_3d iframe').remove();
                                        $('#modal_stripe').modalStripe().close();
                                        $('#stripe-ajax-loader-checkout').hide();
                                        $('.stripe-payment-errors-checkout').text($('#stripe-card_declined').text()).fadeIn(1000);
										$('#payment-confirmation button[type=submit]').removeAttr('disabled');
                                    }
                                }
                            );
							    
							    $("#3d_window_loading, #3d_window_loaded, #close_secure").toggle();
							    clearTimeout(secure_timeout);
							});
                           
                        } else if (response.status == "chargeable") {
                            $('#modal_stripe').modalStripe().close();
                            create3DCharge(response);
                        } else if (response.status == "failed") {
                            var cardType = Stripe.card.cardType($('.stripe-card-number').val());
                            if (cardType == "American Express") {
                                create3DCharge(response);
                            } else {
                                $('#stripe-ajax-loader-checkout').hide();
                                $('.stripe-payment-errors-checkout').text($('#stripe-3d_declined').text()).fadeIn(1000);
								$('#payment-confirmation button[type=submit]').removeAttr('disabled');
                            }
                        }
                    });
                    function callbackFunction3D(result) {
                        $('#modal_stripe').modalStripe().close();
                    }
                } else {
                    create3DCharge();
                }

                function create3DCharge(result) {
                    if (typeof(result) == "undefined") {
                        result = response;
                    }
					$('#card-token-success').show();
                     $.ajax({
					  type: 'POST',
					  dataType: 'json',
					  url: baseDir + 'modules/stripejs/payment.php',
					  data: {
						  stripeToken: result.id,
						  sourceType: result.type,
						  ajax: true,
					  },
					  success: function(data) {
						  if (data.code == '1') {
							  // Charge ok : redirect the customer to order confirmation page
							  location.replace(data.url);
						  } else {
							  //  Charge ko
							  $('#stripe-ajax-loader-checkout').hide();
							  $('.stripe-payment-errors-checkout').text(data.msg).fadeIn(1000);
							  $('#payment-confirmation button[type=submit]').removeAttr('disabled');
						  }
					  },
					  error: function(err) {
						  // AJAX ko
						  $('#stripe-ajax-loader-checkout').hide();
						  $('.stripe-payment-errors-checkout').text('An error occured during the request. Please contact us').fadeIn(1000);
						  $('#payment-confirmation button[type=submit]').removeAttr('disabled');
					  }
				  });
                }
            }
        });
			   return false;
			  }
		});
		
		
		 $('#payment-confirmation button').on('click',function (event) {
		  if ($('input[name=payment-option]:checked').data('module-name')=='stripeCheckout') {
			
            handler.open({
			  name: popup_title,
			  description: popup_desc,
			  amount: amount_ttl
			});
			event.preventDefault();
            event.stopPropagation();
			return false;
		}
    });
		
		// Close Checkout on page navigation
		window.addEventListener('popstate', function() {
		  handler.close();
		});
});

function timeout_secure3D() {
	
	alert($('#stripe-timeout').text());
	$('#result_3d iframe').remove();
	$('#stripe-ajax-loader,#stripe-ajax-loader-checkout').hide();
	$('#payment-confirmation button[type=submit]').removeAttr('disabled');
	$('#modal_stripe').modalStripe().close();
	}