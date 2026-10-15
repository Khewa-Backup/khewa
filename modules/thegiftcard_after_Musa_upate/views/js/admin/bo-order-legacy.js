/*
* 2022 Keyrnel
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
* @copyright  2022 - Keyrnel SARL
* @license commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

$(function() {
	Kl_TheGiftCard_Order.init();
});

var Kl_TheGiftCard_Order = (function() {
	var discounts = (function() {
		var _settings = {};

		function initData(gift_cards) {
			$.each(gift_cards, function() {
				var discountLine = getDiscountLine(this.id_order_cart_rule);

				if (discountLine) {
					addButton(discountLine, this.id_order_cart_rule)
					addGiftCardDetails(discountLine, this)
				}
			})
		};

		function addButton(discountLine, id_order_cart_rule) {
			var html = '<span data-id-gift-card="'+id_order_cart_rule+'" class="show-gift-card-details btn btn-text" style="margin-left:5px; margin-right:-5px">';
			html += '<i class="icon-eye"></i>';
			html += '</span>';

			discountLine.find('td').first().append(html);
		}

		function addGiftCardDetails(discountLine, gift_card) {
			var html = '<tr data-id-gift-card="'+gift_card.id_order_cart_rule+'" style="display:none">';
			html += '<td colspan="3"><div style="background-color: #f8f8f8; padding: 5px 0;"><div class="row">';
			html += '<div class="col-xs-4 text-center">';
			html += '<p class="text-muted mb-0"><strong>Discount code</strong></p>';
			html += '<strong><a href="'+gift_card.cart_rule_url+'">'+gift_card.code+'</a></strong>';
			html += '</div>';
			html += '<div class="col-xs-4 text-center">';
			html += '<p class="text-muted mb-0"><strong>Remaining amount</strong></p>';
			html += '<span class="badge rounded badge-dark font-size-100">'+gift_card.remaining_amount+'</span>';
			html += '</div>';
			html += '<div class="col-xs-4 text-center">';
			html += '<p class="text-muted mb-0"><strong>Related order</strong></p>';
			html += '<strong><a href="'+gift_card.order_url+'">'+gift_card.reference+'</a></strong>';
			html += '</div>';
			html += '</div></div></td></tr>';

			discountLine.after(html)
		}

		function getDiscountLine(id_order_cart_rule) {
			var discountLine = null;

			_settings.table.find('a').each(function() {
				var href = $(this).attr('href');
				var match = href.match(/(id_order_cart_rule=(\d+))/)

				if (match && match.length === 3 && Number(match[2]) === Number(id_order_cart_rule)) {
					discountLine = $(this).closest('tr');
					return false;
				}
			})

			return discountLine;
		};

		function initBinds() {
			$(document).on('click', '.show-gift-card-details', function() {
				var id_gift_card = $(this).attr('data-id-gift-card');
				_settings.table.find('tr[data-id-gift-card="'+id_gift_card+'"]').toggle();
			})
		}

		return {
			init: function(ocr_gift_cards) {
				_settings = $.extend({}, _settings, {
					table : $('.panel-vouchers .table'),
				});

				initData(ocr_gift_cards);
				initBinds();
			}
		}
	})();

	return {
		init: function() {
			if (typeof ocr_gift_cards !== 'undefined' && ocr_gift_cards) {
				discounts.init(JSON.parse(ocr_gift_cards));
			}
		}
	}
})();
