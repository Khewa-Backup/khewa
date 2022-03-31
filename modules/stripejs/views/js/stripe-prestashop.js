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
	
	$("#module-stripejs-paymentcard input[name='payment-option']").click(function(e) {
    var option_id = $(this).attr('id');
	$("#module-stripejs-paymentcard .js-additional-information,.js-payment-option-form").each(function(index, element) {
        if($(this).attr('id')=="pay-with-"+option_id+"-form" || $(this).attr('id')==option_id+"-additional-information")
		$(this).fadeIn();
		else
		$(this).hide();
    });
    });
	$("#module-stripejs-paymentcard input[name='payment-option']:first").click();

	
	 // Get Stripe public key
    if (typeof StripePubKey =='undefined') {
		return;
	} else {
        Stripe.setPublishableKey(StripePubKey);
    }
	
	if (typeof stripe_error =='undefined' && stripe_error!="") {
			  if (stripe_error)
				  alert(stripe_error);
			  else
				  alert(stripe_error_msg);
		  }

    $('#stripe-payment-form input').keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
	
	$('#close_secure').click(function(e) {
	  $('#result_3d iframe').remove();
	  $('#stripe-ajax-loader,#stripe-ajax-loader-checkout').hide();
	  $('#stripe-payment-form').show();
	  $('#payment-confirmation button[type=submit]').prop("disabled", false);
      $('#modal_stripe').modalStripe().close();
   });
	
	
	  if(stripe_allow_prbutton==true){
		  
		  var stripe = Stripe(StripePubKey);
		  var paymentRequest = stripe.paymentRequest({
				  country: country_iso_code,
				  currency: currency_lower,
				  total: {
					label: popup_title,
					amount: amount_ttl
				  },
				});
		  var elements = stripe.elements();
		  var prButton = elements.create('paymentRequestButton', {
			paymentRequest: paymentRequest,
			style: {
			paymentRequestButton: {
			  type: 'default', // default: 'default'
			  theme: 'dark', // default: 'dark'
			  height: '50px', // default: '40px', the width is always '100%'
			},
		  },
		  });
		  
		  // Check the availability of the Payment Request API first.
		  paymentRequest.canMakePayment().then(function(result) {
			if (result) {
			  prButton.mount('#payment-request-button');
			  $('.prbutton-alert').hide();
			} else {
			  $('.prbutton-alert').show();
			  document.getElementById('payment-request-button').style.display = 'none';
			}
		  });
		  
		  paymentRequest.on('token', function(ev) {
					$('#stripe-ajax-loader-prbutton').show();
			        $('#payment-confirmation button[type=submit]').prop("disabled", true);
					$.ajax({
							type: 'POST',
							dataType: 'json',
							url: baseDir + 'modules/stripejs/payment.php',
							data: {
								stripeToken: ev.token.id,
								sourceType: 'prbutton',
								last4: ev.token.last4,
								ajax: true,
							},
							success: function(data) {
								if (data.code == '1') {
									ev.complete('success');
									location.replace(data.url);
								} else {
									ev.complete('fail');
									$('#stripe-ajax-loader-prbutton').hide();
									$('.stripe-payment-errors-prbutton').text(data.msg).fadeIn(1000);
									$('#payment-confirmation button[type=submit]').prop("disabled", false);
								}
							},
							error: function(err) {
								ev.complete('fail');
								$('#stripe-ajax-loader-prbutton').hide();
								$('.stripe-payment-errors-prbutton').text('An error occured during the request. Please contact us').fadeIn(1000);
								$('#payment-confirmation button[type=submit]').prop("disabled", false);
							}
						});
		
				}, function(error) {
				  alert(error.message);
				  $('#payment-confirmation button[type=submit]').prop("disabled", false);
				});

				  
	  }
	  
	$(document).on('click', '#sofort_available_countries .close', function(e){
        $('#sofort_available_countries').modalStripe().close();
    });

    $('#payment-confirmation button').click(function (event) {
		
		var method_stripejs = $('input[name=payment-option]:checked').data('module-name');
		var methods_stripejs = ["alipay", "ideal", "giropay", "bancontact", "sofort", "p24", "eps", "multibanco"];

		////////////////////////////////SEPA Direct Debit//////////////////////////////

		if (method_stripejs=='sepa') {
			
			 if ($('#stripe-sepa-form').is(":visible")) {
				 $('#stripe-sepa-form').submit();
			 } else if ($('#stripe-sepa-form-cc').is(":visible")){
                 $('#stripe-sepa-form-cc').submit();
			 }
            event.preventDefault();
            event.stopPropagation();
            return false;
			
	   ////////////////////////////////Card Payment//////////////////////////////
					
		}else if (method_stripejs=='stripejs') {
            $('#stripe-payment-form').submit();
            event.preventDefault();
            event.stopPropagation();
            return false;
			
	   ////////////////////////////////Payment Request Button//////////////////////////////
			
        }else if (method_stripejs=='stripePRButton') {
		   
		   if ($('.prbutton-alert').is(":visible")){
			   alert($('.prbutton-alert').text());
		   }else{
			   alert(prbutton_alert);
			   }
			
			event.preventDefault();
			event.stopPropagation();
			return false;
			
			
			/*************WeChat Pay**************/
			
		}else if (method_stripejs=='stripeWechat') {
					
			event.preventDefault();
            event.stopPropagation();
			$('.stripe-payment-errors-wechat').hide();
			 $('#stripe-ajax-loader-wepay').show();
			 $('#payment-confirmation button[type=submit]').prop("disabled", true);
			  
			  if (StripePubKey && typeof stripe_v3 !== 'object') {
				  var stripe_v3 = Stripe(StripePubKey);
			  }
	  
			  source_params = {
				  type: 'wechat',
				  amount: amount_ttl,
				  currency: currency,
				  statement_descriptor: 'PS cart id: '+ps_cart_id,
				  metadata: {
					  cart_id: ps_cart_id,
				  },
				  owner: {
					  name: cu_name,
					  email: cu_email,
				  },

			  };
			  stripe_v3.createSource(source_params).then(function(response) {
				  $('#stripe-ajax-loader-wepay').hide();
				  if (response.error) {
					  alert(response.error.message);
				  } else {
					     
					     var url = 'https://api.qrserver.com/v1/create-qr-code/?data=' + response.source.wechat.qr_code_url + '&amp;size=150x150';
						 $(".qr_code img:first").attr('src',url);
						 $(".qr_code").show();

						  Stripe.source.poll(
                                response.source.id,
                                response.source.client_secret,
                                function(status, source) {
                                    if (source.status == "chargeable") {
										$('#stripe-ajax-loader-wechat, .qr_code').toggle();
                                        $.ajax({
											type: 'POST',
											dataType: 'json',
											url: baseDir + 'modules/stripejs/payment.php',
											data: {
												stripeToken: response.source.id,
												sourceType: response.source.type,
												ajax: true,
											},
											success: function(data) {
												if (data.code == '1') {
													// Charge ok : redirect the customer to order confirmation page
													location.replace(data.url);
												} else {
													//  Charge ko
													$('#stripe-ajax-loader-wechat').hide();
													$('.stripe-payment-errors-wechat').text(data.msg).fadeIn(1000);
													$('#payment-confirmation button[type=submit]').prop("disabled", false);
												}
											},
											error: function(err) {
												// AJAX ko
												$('#stripe-ajax-loader-wechat').hide();
												$('.stripe-payment-errors-wechat').text('An error occured during the request. Please contact us').fadeIn(1000);
												$('#payment-confirmation button[type=submit]').prop("disabled", false);
											}
										});
										return;
										
                                    } else if (source.status == "failed" || source.status == "canceled") {
										$(".qr_code img:first").attr('src','');
										$('.qr_code').hide();
                                        $('.stripe-payment-errors-wechat').text($('#stripe-wechat_declined').text()).fadeIn(1000);
										$('#payment-confirmation button[type=submit]').prop("disabled", false);
										return;
                                    }
                                }
                            );
				  }
			  });
   
	  ////////////////////////////////Redirect Methods//////////////////////////////
	  
		
	   }else if (methods_stripejs.indexOf(method_stripejs) != -1) {
		   	  
			  if(method_stripejs=='sofort' && !$('#payment-confirmation button[type=submit]').hasClass('sofort_country_selected')){
				 $('#sofort_available_countries').modalStripe({cloning: true, closeOnOverlayClick: true, closeOnEsc: true}).open();
				 $('#payment-confirmation button[type=submit]').removeClass('sofort_country_selected');
				 return false;
			  }
			  
			  $('#payment-confirmation button[type=submit]').prop("disabled", true);
			  			  
			  if (StripePubKey && typeof stripe_v3 !== 'object') {
				  var stripe_v3 = Stripe(StripePubKey);
			  }
	  
			  if (method_stripejs == 'sofort') {
				  var method_info  = {
					  country: $('select#sofort_country option').filter(":selected").val(),
					  statement_descriptor: 'PS cart id '+ps_cart_id,
				  };
				  $('#sofort_available_countries').modalStripe().close();
			  } else if(method_stripejs != 'p24' && method_stripejs != 'multibanco'){
				  var method_info  = {
					  statement_descriptor: 'PS cart id '+ps_cart_id,
				  };
			  }
			  source_params = {
				  type: method_stripejs,
				  amount: amount_ttl,
				  currency: currency,
				  metadata: {
					  cart_id: ps_cart_id,
				  },
				  owner: {
					  name: cu_name,
					  email: cu_email,
				  },
				  redirect: {
					  return_url: validation_url + '&source_type=' + method_stripejs
				  }
			  };
			  source_params[method_stripejs] = method_info;
			  stripe_v3.createSource(source_params).then(function(response) {
				  if (response.error) {
					  alert(response.error.message);
					  $('#payment-confirmation button[type=submit]').prop("disabled", false);
				  } else {
					  window.location.replace(response.source.redirect.url);
				  }
			  });
		  event.preventDefault();
		  event.stopPropagation();
		  return false;
        }
	 });
	 
	 
	 ////////////////////////////////// SEPA Direct Debit Payments ////////////////////
	
	$('.stripe-iban-number').keyup(function() {
		if ($(this).val().length >= 2)
		{
			$(this).val($(this).val().trim().replace(/[^a-z0-9]+/gi, ''));
		}
	});
	
	$('#stripe-sepa-form').submit(function(event) {

		if ($('.stripe-owner-name').val()=='')
			$('.stripe-payment-errors-sepa').text($('#stripe-incorrect_ownername').text() + ' ' + $('#stripe-please-fix').text());
		else if ($('.stripe-iban-number').val()=='')
			$('.stripe-payment-errors-sepa').text($('#stripe-incorrect_number_iban').text() + ' ' + $('#stripe-please-fix').text());
		else
		{
			$('.stripe-payment-errors-sepa').hide();
			$('#stripe-sepa-form').hide();
			$('#stripe-ajax-loader-sepa').show();
			$('#payment-confirmation button[type=submit]').attr('disabled', 'disabled');
						
			Stripe.source.create({
			  type: 'sepa_debit',
			  sepa_debit: {
				iban: $('.stripe-iban-number').val(),
			  },
			  currency: currency,
			  owner: {
                address: {
                    line1: billing_address.line1,
                    line2: billing_address.line2,
                    city: billing_address.city,
                    postal_code: billing_address.zip_code,
                    country: billing_address.country
                },
                name: $('.stripe-owner-name').val(),
                email: billing_address.email,
              }
			}, function (status, response) {
				
				 if (response.error)
				  {
					  $('.stripe-payment-errors-sepa').text(response.error.message).fadeIn(1000);
					  $('#payment-confirmation button[type=submit]').removeAttr('disabled');
					  $('#stripe-sepa-form').show();
					  $('#stripe-ajax-loader-sepa').hide();
				  }
				  else
				  {
					  $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: baseDir + 'modules/stripejs/payment.php',
                        data: {
                            stripeToken: response.id,
                            sourceType: "sepa_debit",
							StripLastDigits:parseInt($('.stripe-iban-number').val().slice(-4)),
							ajax: true,
                        },
                        success: function(data) {
                            if (data.code == '1') {
                                // Charge ok : redirect the customer to order confirmation page
                                location.replace(data.url);
                            } else {
                                //  Charge ko
                                $('#stripe-ajax-loader-sepa').hide();
                                $('#stripe-sepa-form').show();
                                $('.stripe-payment-errors-sepa').text(data.msg).fadeIn(1000);
                                $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                            }
                        },
                        error: function(err) {
                            // AJAX ko
                            $('#stripe-ajax-loader-sepa').hide();
                            $('#stripe-sepa-form').show();
                            $('.stripe-payment-errors-sepa').text('An error occured during the request. Please contact us').fadeIn(1000);
                            $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                        }
                    });
				  }
				
				});

			return false; /* Prevent the form from submitting with the default action */
		}

		$('.stripe-payment-errors-sepa').fadeIn(1000);
		return false;
	});
	
	$('#stripe-replace-sepa').click(function() {
		$('#stripe-sepa-form-cc').hide();
		$('#stripe-sepa-form').fadeIn(1000);
	});
	$('#stripe-use-saved-sepa').click(function() {
		$('#stripe-sepa-form-cc').fadeIn(1000);
		$('#stripe-sepa-form').hide();
	});
	
		 
	 ////////////////////////////////Card Pay//////////////////////////////
	 
	  if (typeof mode != 'undefined' && mode == 0) {
		$('.stripe-card-number').val('4242 4242 4242 4242');
		 var card_logo = document.createElement('img');
                card_logo.src = module_dir+'views/img/cc-visa.png';
                card_logo.id = "img-visa";
                card_logo.className = "img-card";
                $(card_logo).insertAfter('.stripe-card-number');
		$('.stripe-card-cvc').val(123);
		$('.stripe-card-expiry').val('12/25');
    }

     /* Catch callback errors */
    if ($('.stripe-payment-errors').text()) {
        $('.stripe-payment-errors').fadeIn(1000);
    }

    $('#stripe-payment-form input').keypress(function () {
        $('.stripe-payment-errors').fadeOut(500);
    });
	
    //Put our input DOM element into a jQuery Object
    var jqDate = document.getElementById('card_expiry');

    //Bind keyup/keydown to the input
    $(jqDate).bind('keyup','keydown', function(e){
        var value_exp = $(jqDate).val();
        var v = value_exp.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        var matches = v.match(/\d{2,4}/g);

        //To accomdate for backspacing, we detect which key was pressed - if backspace, do nothing:
        if(e.which !== 8) {
            var numChars = value_exp.length;
            if(numChars === 2){
                var thisVal = value_exp;
                thisVal += '/';
                $(jqDate).val(thisVal);
            }
            if (numChars === 5)
                return false;
        }
    });

    if (document.getElementById('card_number') != null) {
        document.getElementById('card_number').oninput = function() {
            this.value = cc_format(this.value);

            cardNmb = Stripe.card.validateCardNumber($('.stripe-card-number').val());

            var cardType = Stripe.card.cardType(this.value);
            if (cardType != "Unknown") {
                if (cardType == "American Express")
                    cardType = "amex";
                if (cardType == "Diners Club")
                    cardType = "diners";
                if ($('.img-card').length > 0) {
                    if ($('#img-'+cardType).length > 0) {
                        setTimeout(function(){
                            card_input = document.getElementById('card_number');
                            var strLength = card_input.value.length;
                            card_input.focus();
                            card_input.setSelectionRange(strLength, strLength);
                        }, 0);
                        return false;
                    } else {
                        $('.img-card').remove();
                    }
                }

                var card_logo = document.createElement('img');
                card_logo.src = module_dir+'views/img/cc-' + cardType.toLowerCase() +'.png';
                card_logo.id = "img-"+cardType;
                card_logo.className = "img-card";
                $(card_logo).insertAfter('.stripe-card-number');
            } else {
                if ($('.img-card').length > 0) {
                    $('.img-card').remove();
                }

            }
        }
    }

    $('#stripe-payment-form').submit(function (event) {
        var $form = $(this);
        if (!StripePubKey) {
            $('.stripe-payment-errors').show();
            $form.find('.stripe-payment-errors').text($('#stripe-no_api_key').text()).fadeIn(1000);
            return false;
        }
		if ($('.stripe-name').val() == '') {
            $('.stripe-payment-errors').show();
            $form.find('.stripe-payment-errors').text($('#stripe-incorrect_ownername').text()).fadeIn(1000);
            return false;
        }
        var cardNmb = Stripe.card.validateCardNumber($('.stripe-card-number').val());
        var cvcNmb = Stripe.card.validateCVC($('.stripe-card-cvc').val());
        if (cvcNmb == false) {
            $('.stripe-payment-errors').show();
            $form.find('.stripe-payment-errors').text($('#stripe-invalid_cvc').text()).fadeIn(1000);
            return false;
        }
        if (cardNmb == false) {
            $('.stripe-payment-errors').show();
            $form.find('.stripe-payment-errors').text($('#stripe-incorrect_number').text()).fadeIn(1000);
            return false;
        }
        /* Disable the submit button to prevent repeated clicks */
        $('#payment-confirmation button[type=submit]').attr('disabled', 'disabled');
        $('.stripe-payment-errors').hide();
        $('#stripe-payment-form').hide();
        $('#stripe-ajax-loader').show();

        exp_month = $('.stripe-card-expiry').val();
        exp_month_calc = exp_month.substring(0, 2);
        exp_year = $('.stripe-card-expiry').val();
        exp_year_calc = "20" + exp_year.substring(3);

        Stripe.source.create({
            type: 'card',
            card: {
                number: $('.stripe-card-number').val(),
                cvc: $('.stripe-card-cvc').val(),
                exp_month: exp_month_calc,
                exp_year: exp_year_calc,
            },
            owner: {
                address: {
                    line1: billing_address.line1,
                    line2: billing_address.line2,
                    city: billing_address.city,
                    postal_code: billing_address.zip_code,
                    country: billing_address.country
                },
                name: $('.stripe-name').val(),
                email: billing_address.email,
            }
        }, function (status, response) {
            var $form = $('#stripe-payment-form');

            if (response.error) {
                // Show error on the form
                $('#stripe-ajax-loader').hide();
                $('#stripe-payment-form').show();
                $('#payment-confirmation button[type=submit]').removeAttr('disabled');

                var err_msg = $('#stripe-'+response.error.code).val();
                if (!err_msg || err_msg == "undefined" || err_msg == '')
                    err_msg = response.error.message;
                $form.find('.stripe-payment-errors').text(err_msg).fadeIn(1000);
            } else {
                if ((secure_mode || response.card.three_d_secure == 'required') && response.card.three_d_secure != 'undefined' && response.card.three_d_secure != "not_supported") {
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
                            name: $('.stripe-name').val(),
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
                                        createCharge(source);
                                    } else if (source.status == "failed") {
                                        $('#result_3d iframe').remove();
                                        $('#modal_stripe').modalStripe().close();
                                        $('#stripe-ajax-loader').hide();
                                        $('#stripe-payment-form').show();
                                        $form.find('.stripe-payment-errors').text($('#stripe-card_declined').text()).fadeIn(1000);
                                        $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                                    }
                                }
                            );
							    
							    $("#3d_window_loading, #3d_window_loaded, #close_secure").toggle();
							    clearTimeout(secure_timeout);
							});
							
                            
                        } else if (response.status == "chargeable") {
                            $('#modal_stripe').modalStripe().close();
                            createCharge(response);
                        } else if (response.status == "failed") {
                            var cardType = Stripe.card.cardType($('.stripe-card-number').val());
                            if (cardType == "American Express") {
                                createCharge();
                            } else {
                                $('#stripe-ajax-loader').hide();
                                $('#stripe-payment-form').show();
                                $form.find('.stripe-payment-errors').text($('#stripe-3d_declined').text()).fadeIn(1000);
                                $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                            }
                        }
                    });
                    function callbackFunction3D(result) {
                        $('#modal_stripe').modalStripe().close();
                    }
                } else {
                    createCharge();
                }

                function createCharge(result) {
                    if (typeof(result) == "undefined") {
                        result = response;
                    }
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
                                $('#stripe-ajax-loader').hide();
                                $('#stripe-payment-form').show();
                                $('.stripe-payment-errors').text(data.msg).fadeIn(1000);
                                $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                            }
                        },
                        error: function(err) {
                            // AJAX ko
                            $('#stripe-ajax-loader').hide();
                            $('#stripe-payment-form').show();
                            $('.stripe-payment-errors').text('An error occured during the request. Please contact us').fadeIn(1000);
                            $('#payment-confirmation button[type=submit]').removeAttr('disabled');
                        }
                    });
                }
            }
        });
        return false;
    });

});
function timeout_secure3D() {
	
	alert($('#stripe-timeout').text());
	$('#result_3d iframe').remove();
	$('#stripe-ajax-loader,#stripe-ajax-loader-checkout').hide();
	$('#stripe-payment-form').show();
	$('#payment-confirmation button[type=submit]').removeAttr('disabled');
	$('#modal_stripe').modalStripe().close();
}
function cc_format(value) {
    var v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    var matches = v.match(/\d{4,16}/g);
    var match = matches && matches[0] || '';
    var parts = [];
    for (i=0, len=match.length; i<len; i+=4) {
        parts.push(match.substring(i, i+4));
    }
    if (parts.length) {
        return parts.join(' ');
    } else {
        return value;
    }
}