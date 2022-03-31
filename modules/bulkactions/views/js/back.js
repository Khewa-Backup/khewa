/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

var ba_selected_items = [],
	ba_chunk = 10;

$(window).on('load', function(){
	switch (ba_type) {
		case 'combinations':
			var selector = is_17 ? '#combinations-bulk-form' : '#add_new_combination',
				timer = setInterval(function(){
					if ($(selector).length){
						$(selector).after(ba_html);
						bindCombinationEvents();
						clearInterval(timer);
					}
				}, 500);
			break;
		case 'product':
		case 'category':
		case 'customer':
			var sf = is_17 && ba_type == 'product' ? true : false;
			if ($('.js-bulk-actions-btn').length) { // products, customers in PS 1.7.6+
				$('.js-bulk-actions-btn').closest('.btn-group').after(ba_html);
				sf = true;
			} else {
				$('.bulk-actions, [bulkurl]').after(ba_html);
			}
			$('.handy-bulk-actions').toggleClass('sf', sf);
			bindEvents(ba_type);
			break;
	}

	if (typeof displayFieldsManager != 'undefined') {
		/**
		* checkAccessVariations is called after generating/deleting combinations
		* inside displayFieldsManager.refresh
		* see /admin/themes/default/js/bundle/product/form.js and product-combinations.js
		**/
		var originalCheckAccessVariations = displayFieldsManager.checkAccessVariations;
		displayFieldsManager.checkAccessVariations = function() {
			// update product attributes selector
			var params = {
					action: 'getUpdatedProducAttributesOptions',
					id_product: $('#form_id_product').val(),
				},
				response = function(r){
					if ('options' in r) {
						var newHTML = '<option value="0">-</option>';
						for (var i in r.options) {
							newHTML += '<option value="'+r.options[i]['value']+'">'+r.options[i]['name']+'</option>';
						}
						$('.checkSelection').html(newHTML);
					}
				};
			ajaxRequest(params, response);
			return originalCheckAccessVariations();
		}
	}

	function bindCombinationEvents(){
		if (is_17) {
			$(document).on('change', '.js-combination, #toggle-all-combinations', function(){
				$('.assignImages').toggleClass('hidden', !$('.js-combination:checked').length);
			}).on('click', '.js-combination, #toggle-all-combinations', function(){
				$('.checkSelection').addClass('no-events').val('0').change().removeClass('no-events');
			});
		} else if (!$('.handy-bulk-actions.for-combinations').hasClass('ready')) {
			var $combinationsTable = $('#table-combinations-list').length ? $('#table-combinations-list') : $('#combinations-list');
			$('.bulk-selection-tools').prependTo($combinationsTable.closest('.panel'));
			// add checkboxes
			$combinationsTable.find('a.edit').each(function(){
				try {
					var id_combination = $(this).attr('href').split('id_product_attribute=')[1].split('&')[0];
				} catch (e){};
				if (typeof id_combination !== 'undefined') {
					var checkboxHTML = '<input type="checkbox" data-id="'+id_combination+'" class="js-combination">';
					$(this).closest('tr').find('td').first().prepend(checkboxHTML);
				}
			});

			$('.bulk-action-type').on('change', function(e){
				$('.bulk-action').addClass('hidden');
				$('.bulk-action.'+$(this).val()).removeClass('hidden');
			});

			$('#toggle-all-combinations').on('change', function(e) {
				var checked = $(this).prop('checked');
				$('.js-combination').prop('checked', checked);
			});
			$('.invertSelection').on('click', function(e) {
				e.preventDefault();
				$('.js-combination').each(function(){
					$(this).prop('checked', !$(this).prop('checked'));
				});
			});

			$('.handy-bulk-actions.for-combinations').addClass('ready');
		}

		$('.checkSelection').on('change', function(){
			if ($(this).hasClass('no-events')) {
				return;
			}
			// uncheck non-matching checkboxes later
			$('.js-combination').addClass('bulk-not-checked');
			var value = $(this).val(),
				combination_ids = value.split('-');
			for (var i in combination_ids) {
				$('.js-combination[data-id="'+combination_ids[i]+'"]').removeClass('bulk-not-checked').prop('checked', true);
			}
			$('.js-combination.bulk-not-checked').prop('checked', false);
			if (is_17) {
				$('.assignImages').toggleClass('hidden', value == '0');
			}
		});

		$('.runAction').on('click', function(e){
			e.preventDefault();
			$(this).addClass('loading');
			var params = {
					action: $('.bulk-action-type').val(),
					selected_images: [],
					selected_combinations: [],
					price_impact: $('.bulk-price-impact').val(),
					unit_price_impact: $('.bulk-unit-price-impact').val(),
					weight_impact: $('.bulk-weight-impact').val(),
				};
			$('.bulk-img-checkbox:checked').each(function(){
				params.selected_images.push($(this).data('id'));
			});
			$('.js-combination:checked').each(function(){
				params.selected_combinations.push($(this).data('id'));
			});

			var response = function(r){
				if (params.action != 'assignImages' && 'applied_impacts' in r) {
					var eq = params.action == 'setPriceImpact' ? '1' : '2';
					for (var i in r.applied_impacts) {
						$('.js-combination[data-id="'+i+'"]').closest('tr').find('td:eq('+eq+')').html(r.applied_impacts[i]);
					}
				} else if (params.action == 'assignImages') {
					if (is_17) {
						for (var i in params.selected_combinations) {
							var id_combination = params.selected_combinations[i],
								src = $('.bulk-img-checkbox:checked').first().next().attr('src');
							$('#combination_form_'+id_combination).find('.product-combination-image').each(function(){
								var $checkbox = $(this).find('input[type="checkbox"]'),
									id_image = parseInt($checkbox.val()),
									checked = $.inArray(id_image, params.selected_images) > -1;
								$checkbox.prop('checked', checked).parent().toggleClass('img-highlight', checked);
							});
							$('.combination.loaded#attribute_'+id_combination).find('img.img-responsive').attr('src', src);
						}
						$('.bulk-img-checkbox').prop('checked', false);
						$('.checkSelection').val('0').change();
					} else if (typeof combination_images != 'undefined') {
						// required for dynamic update of standard combination form
						for (var i in params.selected_combinations) {
							combination_images[params.selected_combinations[i]] = params.selected_images;
						}
					}
				}
			};
			ajaxRequest(params, response);
		});

		// tmp fix
		if (!is_17) {
			$('#ResetBtn').on('click', function(){
				$('.bulk-action-type').val(0).change();
			});
		}
	}

	function bindEvents(ba_type){
		if (ba_type == 'category' || ba_type == 'product') {
			var extraElementClass = ba_type == 'category' ? 'customer-groups' : 'price';
			$('[name="action_type"]').on('change', function() {
				var showExtraElement = $(this).find('option:selected').data('show') == extraElementClass;
				$('.inline-item.'+extraElementClass).toggleClass('hidden', !showExtraElement)
				.prev().toggleClass('hidden', showExtraElement);
			});
		}
		$('.runAction').on('click', function(e){
			e.preventDefault();
			$(this).addClass('loading');
			var params = {
					action: $('[name="action_type"]').val(),
					selected_items: [],
				},
				response = function(r){},
				checkBoxSelectors = [
					'[name="'+ba_type+'Box[]"]', // 1.6 all, 1.7 categories & customers
					'[name="bulk_action_selected_products[]"]', // 1.7 products
					'.js-bulk-action-checkbox' // 1.7.6+ categories & customers
				],
				$checkedItems = $(checkBoxSelectors.join(',')).filter(':checked');
			ba_selected_items = [];
			$checkedItems.each(function(){
				ba_selected_items.push($(this).val());
			});
			params.selected_items = ba_selected_items.splice(0, ba_chunk);
			switch (ba_type) {
				case 'product':
				case 'category':
					$('.handy-bulk-actions').find('[name]').each(function(){
						params[$(this).attr('name')] = $(this).val();
					});
					response = function(r){
						if (r.refresh_required) {
							$('.handy-bulk-actions').addClass('refresh-required');
						} else if ('remove_processed' in r && r.processed_items.length) {
							$checkedItems.each(function(){
								if ($.inArray(parseInt($(this).val()), r.processed_items) > -1) {
									$(this).closest('tr').remove();
								}
							});
						} else if (ba_type == 'product' && 'displayed_value' in r) {
							var tdIndex = getTdIndex(params.action);
							if (tdIndex) {
								$checkedItems.each(function(){
									var $td = $(this).closest('td').nextAll().eq(tdIndex),
										$a = $td.find('a').first(),
										$el = $a.length ? $a : $td;
									$el.html(utf8_decode(r.displayed_value));
									if (params.action == 'setPrice') {
										var finalPriceKey = 'final_price_'+$(this).val(),
											$finalPriceContainer = getFinalPriceContainer($td);
										if ($finalPriceContainer && finalPriceKey in r) {
											$finalPriceContainer.html(utf8_decode(r[finalPriceKey]));
										}
									}
								});
							}
						}
					};
					break;
				case 'customer':
					params.id_group = $('[name="id_group"]').val();
					break;
			}
			ajaxRequest(params, response);
		});
	}

	function getFinalPriceContainer($td) {
		if (!is_17) {
			return $td.next();
		} else {
			var $fpc = $td.next().find('a');
			if ($fpc.length && $fpc.attr('href').indexOf('#tab-step2') > -1) {
				return $fpc;
			}
		}
	}

	function getTdIndex(action) {
		var catInputName = is_17 ? 'filter_column_name_category' : 'productFilter_cl!name',
			catTdIndex = -1,
			i = 0;
		$('th').find('input[name="'+catInputName+'"]').closest('th').prevAll('th').each(function(){
			catTdIndex += parseInt($(this).attr('colspan')) || 1;
		});
		// NOTE: in All shops context default_shop column is displayed instead of category
		// filter input name is productFilter_shop!name
		// TODO: add compatibility with that column too
		if (action == 'setDefaultCategory') {
			i = catTdIndex;
		} else if (action == 'setPrice') {
			i = catTdIndex + 1; // price column is next after category. This is the only common point in all versions
		}
		return i;
	}

	$(document).on('click', '.runAction', function(e){
		$('.ba.thrown-errors, .ba.thrown-warnings').remove();
	});

	function ajaxRequest(params, response){
		$.ajax({
			type: 'POST',
			url: ba_ajax_path,
			data: params,
			dataType : 'json',
			success: function(r) {
				console.dir(r);
				if ('errors' in r) {
					$('.handy-bulk-actions').closest('.row, .panel').prepend(displayAlert('danger', utf8_decode(r.errors)));
					$('.runAction').removeClass('loading');
				} else {
					if ('warnings' in r) {
						if (!$('.ba.thrown-warnings').length) {
							$('.handy-bulk-actions').closest('.row, .panel').prepend(displayAlert('warning', ''));
						}
						$('.ba.thrown-warnings').append(utf8_decode(r.warnings));
					}
					response(r);
					if (ba_selected_items.length) {
						params.selected_items = ba_selected_items.splice(0, ba_chunk);
						ajaxRequest(params, response);
					} else {
						$('.runAction').removeClass('loading');
						if (r.processed_items.length) {
							$.growl.notice({ title: '', message: savedTxt});
						}
						if ($('.handy-bulk-actions').hasClass('refresh-required')) {
							location.reload();
						}
					}

				}
			},
			error: function(r) {
				$('.runAction').removeClass('loading');
				console.warn($(r.responseText).text() || r.responseText);
			}
		});
	}

	function displayAlert(type, content) {
		var alertHTML = '<div class="alert alert-'+type+' ba thrown-warnings'+(is_17 ? ' is-17' : '')+'">';
			alertHTML += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
			alertHTML += content;
			alertHTML += '</div>';
		return alertHTML;
	}

	function utf8_decode (utfstr) {
		var res = '';
		for (var i = 0; i < utfstr.length;) {
			var c = utfstr.charCodeAt(i);
			if (c < 128) {
				res += String.fromCharCode(c);
				i++;
			} else if((c > 191) && (c < 224)) {
				var c1 = utfstr.charCodeAt(i+1);
				res += String.fromCharCode(((c & 31) << 6) | (c1 & 63));
				i += 2;
			} else {
				var c1 = utfstr.charCodeAt(i+1);
				var c2 = utfstr.charCodeAt(i+2);
				res += String.fromCharCode(((c & 15) << 12) | ((c1 & 63) << 6) | (c2 & 63));
				i += 3;
			}
		}
		return res;
	}
});
/* since 1.2.2 */

