/*
* 2017 - 2015 Keyrnel
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author Keyrnel
* @copyright  2017 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

var original_url = window.location + '';
var first_url_check = true;
var selected_attributes = [];

$(document).ready(function() {
	initUrl();
	initBind();
	initAssets();
});

function initUrl()
{
	if (original_url != window.location || first_url_check)
	{
		first_url_check = false;
		var url = window.location + '';

		// if we need to load a specific combination
		if (url.indexOf('#/') != -1)
		{
			// get the params to fill from a "normal" url
			params = url.substring(url.indexOf('#') + 1, url.length);
			tabParams = params.split('/');
			tabValues = [];
			if (tabParams[0] == '')
				tabParams.shift();

			var len = tabParams.length;
			for (var i=0; i<len; i++)
				tabValues.push(tabParams[i].split('-'));

			$('#block_templates, #block_amounts').each(function() {
				var group_name = $(this).attr('data-rewrite-group-name');
				for (var a in tabValues) {
					if (group_name === decodeURIComponent(tabValues[a][0])) {
						var blockId = $(this).attr('id');
						if (blockId === "block_templates") {
							$('#block_templates input[type=radio]').prop('checked', false).closest('.img_attribute').find('.product-image-container').removeClass('selected');
							$('#block_templates input[type=radio][value='+tabValues[a][1]+']').prop('checked', true).closest('.img_attribute').find('.product-image-container').addClass('selected');
						} else if (blockId === "block_amounts") {
							var amount_fixed = false;
							$('#block_amounts select[name="amount_select"]').find('option').each(function() {
								var amount = $(this).val();
								if (amount == tabValues[a][1]) {
									amount_fixed = true;
									return;
								}
							});
							if (amount_fixed) {
								$('#block_amounts select[name="amount_select"]').val(tabValues[a][1]);
								$('#block_amounts input[name="amount_input"]').val('');
							} else {
								$('#block_amounts select[name="amount_select"]').val(-1);
								$('#block_amounts input[name="amount_input"]').val(tabValues[a][1]).closest('.form-group').show();
							}
							$('#block_amounts input[name="amount"]').val(tabValues[a][1]);
						}
					}
				}
			});
		}
	}

	getProductAttribute();
}

function initBind()
{
	if (!('ontouchstart' in window)){
		$(document).on({
			mouseover: function () {
				$(this).find('.view_larger').show();
			},

			mouseout: function () {
				$(this).find('.view_larger').hide();
			}
		}, '.product-image-container');
	}

	$(document).on({
		click: function() {
			var templateId = $(this).attr('data-id');
			$('.product-image-container').each(function() {
				$(this).removeAttr('id').removeClass('selected');
				if ('ontouchstart' in window) {
					$(this).find('.view_larger').hide();
				}
				$('#block_templates input[type=radio]').prop('checked', false);

			});
			$(this).attr('id', 'bigpic').addClass('selected');
			if ('ontouchstart' in window) {
				$(this).find('.view_larger').show();
			}
			$('#block_templates input[type=radio][value='+templateId+']').prop('checked', true);
			getProductAttribute();
		}
	}, '.product-image-container');

	$('#block_amounts select[name="amount_select"]').on('change', function() {
		$('#block_amounts input[name="amount_input"]').val('').closest('.form-group').hide();
		$('#block_amounts input[name="amount"]').val('');
		if ($(this).val() == -1) {
			$('#block_amounts input[name="amount"]').closest('.form-group').show();
		}
		else {
			$('#block_amounts input[name="amount"]').val($(this).val());
			getProductAttribute();
		}
	});

	$('#block_amounts input[name="amount_input"]').focusout(function() {
		var amount = $(this).val();

		$('#block_amounts input[name="amount"]').val(amount);
		getProductAttribute();
		
		if (!(amount >= custom_amount_from && amount <= custom_amount_to && amount % pitch == 0)) {
			showErrorMessage(invalidAmountMsg);
		}
	});

	$('#block_customization input[name="sending_method"]').on('change', function() {
		var sendingMethod = $(this).val();
		if (sendingMethod == printAtHome) {
			$('#card_text_fields').hide();
		} else {
			$('#card_text_fields').show();
		}
	});

	$('#block_button button[type=button]').on('click', function() {
		addToCart();
	});
}

function initAssets()
{
	$('.fancybox').fancybox({
		'hideOnContentClick': true,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic'
	});

	$(".datepicker").datepicker({
		dateFormat: "yy-mm-dd"
	});
}


function getProductAttribute()
{
	var request = '';
	var tab_attributes = [];

	$('#block_templates input[type=radio]:checked, #block_amounts input[name="amount"]').each(function() {
		var attribute = new Object();
		attribute['id_attribute_group'] = $(this).closest('.attributes').attr('data-id-attribute-group');
		attribute['group_name'] = $(this).closest('.attributes').attr('data-rewrite-group-name');
		attribute['value'] = $(this).val();
		tab_attributes.push(attribute);
	});

	selected_attributes = tab_attributes;

	// build new request
	for (var a in tab_attributes)
		request += '/'+tab_attributes[a]['group_name'] + attribute_anchor_separator + tab_attributes[a]['value'];

	request = request.replace(request.substring(0, 1), '#/');
	url = window.location + '';

	// redirection
	if (url.indexOf('#') != -1)
		url = url.substring(0, url.indexOf('#'));

	window.location = url + request;
}

function addToCart()
{
	var sendingMethod = $('#block_customization input[name="sending_method"]:checked').val();

	var customizationData = new Object();
	if ($('#card_text_fields').is(':visible') && sendingMethod == sendToFriend) {
		$('#card_text_fields').find('input[type=text], textarea').each(function() {
			customizationData[$(this).attr('name')] = this.value;
		});
	}

    var $form = $('#buy_block');

	var params = {
		sendingMethod: sendingMethod,
		attributes: selected_attributes,
		customizationData: customizationData,
		ajax: true,
		action: 'getCombination'
	};

	$.ajax({
		type: 'POST',
		url: $form.attr('data-action'),
		data: params,
		success : function(data) {
			data = $.parseJSON(data);
			if (!data.error) {
				var query = $form.serialize() + '&id_product_attribute='+data.giftcard_vars.id_combination+'&action=update';
				var actionURL = $form.attr('action');

				if (sendingMethod == sendToFriend && data.giftcard_vars.id_customization != 'undefined') {
					query += '&id_customization='+data.giftcard_vars.id_customization;
				}

				if (!ajax_allowed) {
					window.location.href = actionURL+'?'+query;
				} else {
					if (is17) {
						$.post(actionURL, query, null, 'json').then(function (resp) {
						 prestashop.emit('updateCart', {
							reason: {
							  idProduct: resp.id_product,
							  idProductAttribute: resp.id_product_attribute,
							  linkAction: 'add-to-cart',
							  cart: resp.cart
						  	},
						  	resp: resp
						  });
						}).fail(function (resp) {
						  prestashop.emit('handleError', { eventType: 'addProductToCart', resp: resp });
						});
					} else {
						ajaxCart.add( $('#product_page_product_id').val(), data.giftcard_vars.id_combination, true, null, 1, null);
					}
				}
			}
			else {
				showErrorMessage(data.error);
			}
		},
		error : function(data){
			alert("[TECHNICAL ERROR]");
		}
	});
}

function showErrorMessage(msg) {
	$.growl.error({ title: "", message:msg});
}
