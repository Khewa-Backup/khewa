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
    if (typeof stripe_source != "undefined" && stripe_source != ""
        && typeof stripe_client_secret != "undefined" && stripe_client_secret != "") {
        if (StripePubKey) {
            Stripe.setPublishableKey(StripePubKey);
        }
			
		source_chargeable = false;
		Stripe.source.poll(
			stripe_source,
			stripe_client_secret,
			function(status, source) {
				
				if (source.status == "pending" && stripe_source_type == "multibanco") {
					createCharge(source);
				} else if (source.status == "chargeable") {
					source_chargeable = true;
					createCharge(source);
				} else if (source.status == "failed" || source.status == "canceled") {
					location.replace(order_page);
				} else if (source.status == "consumed" && !source_chargeable) {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: baseDir + 'modules/stripejs/payment.php',
						data: {
							stripeToken: source.id,
							checkOrder: true,
							cart_id: source.metadata.cart_id,
							ajax: true,
						},
						success: function(data) {
							if (data.code == '1') {
								location.replace(data.url);
							}else{
								location.replace(order_page);
							}
						},
						error: function(err) {
							alert(err);
						}
					});
				}
			}
		);
	}

    function createCharge(result) {
		
		if (result.status == "pending" && result.type == "multibanco") {
			 var res_data = {stripeToken: result.id, sourceType: result.type,checkOrder: true,
						mb_entity: result.multibanco.entity,
						mb_ref: result.multibanco.reference,
						status: result.status, ajax: true,}; 
		} else
		{
             var res_data = {stripeToken: result.id,sourceType: result.type,ajax: true,};
		}
       
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: baseDir + 'modules/stripejs/payment.php',
            data: res_data,
            success: function(data) {
                if (data.code == '1') {
                    // Charge ok : redirect the customer to order confirmation page
                    location.replace(data.url);
                } else {
                    location.replace(order_page+'?stripe_error='+data.msg);
                }
            },
            error: function(err) {
                location.replace(order_page+'?stripe_error='+err);
            }
        });
    }
});