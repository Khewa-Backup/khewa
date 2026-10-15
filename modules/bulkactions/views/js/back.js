/**
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

var ba_selected_items = [],
	ba_chunk = 10,
	hba = {
		getTdIndex: function(action) {
			var catTdIndex = -1,
				i = 0,
				catInputName = !is_16 ? 'filter_column_name_category' : 'productFilter_cl!name',
				$input = $('table').find('input[name="'+catInputName+'"]');
			if (!$input.length && is_16 && action == 'setPrice') {
				$shopInput = $('th').find('input[name="productFilter_shop!name"]');
				if ($shopInput.length) {
					// in All shops context default_shop column is displayed instead of category in PS 1.6
					$input = $shopInput;
				}
			}
			$input.parent().prevAll().each(function() {
				catTdIndex += parseInt($(this).attr('colspan')) || 1;
			});
			if (action == 'setDefaultCategory') {
				i = catTdIndex;
			} else if (action == 'setPrice') {
				i = catTdIndex + 1; // price column is next after category. This is the only common point in all versions
			}
			return i;
		},
		displayAlert: function(type, content) {
			content = Array.isArray(content) ? content.join('<br>') : content;
			let $container = $('.handy-bulk-actions').closest('.card-body, .content').first();
			if ($container.length) {
				let alertHTML = '<div class="alert alert-'+type+' ba">';
					alertHTML += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
					alertHTML += content;
					alertHTML += '</div>';
				$container.prepend(alertHTML)
			} else {
				displayType = type == 'danger' ? 'error' : 'warning';
				$.growl[displayType]({title: '', message: content, duration: 5000});
			}
		},
		retro: {
			combinationEvents: function() {
				var $combinationsTable = $('#table-combinations-list').length ? $('#table-combinations-list') : $('#combinations-list');
				$('.bulk-selection-tools').prependTo($combinationsTable.closest('.panel'));
				// add checkboxes
				$combinationsTable.find('a.edit').each(function() {
					try {
						var id_combination = $(this).attr('href').split('id_product_attribute=')[1].split('&')[0];
					} catch (e) {};
					if (typeof id_combination !== 'undefined') {
						var checkboxHTML = '<input type="checkbox" data-id="'+id_combination+'" class="js-combination">';
						$(this).closest('tr').find('td').first().prepend(checkboxHTML);
					}
				});
				$('.bulk-action-type').on('change', function(e) {
					$('.bulk-action').addClass('hidden');
					$('.bulk-action.'+$(this).val()).removeClass('hidden');
				});
				$('#toggle-all-combinations').on('change', function(e) {
					var checked = $(this).prop('checked');
					$('.js-combination').prop('checked', checked);
				});
				$('.invertSelection').on('click', function(e) {
					e.preventDefault();
					$('.js-combination').each(function() {
						$(this).prop('checked', !$(this).prop('checked'));
					});
				});
				$('#ResetBtn').on('click', function() {
					$('.bulk-action-type').val(0).change();
				});
			}
		},
	};

$(window).on('load', function() {
	switch (ba_type) {
		case 'combinations':
			var selector = !is_16 ? '#combinations-bulk-form' : '#add_new_combination',
				timer = setInterval(function() {
					if ($(selector).length) {
						$(selector).after(ba_html);
						bindCombinationEvents();
						clearInterval(timer);
					}
				}, 500);
			break;
		case 'product':
		case 'category':
		case 'customer':
			var sf = !is_16 && ba_type == 'product';
			if ($('.js-bulk-actions-btn').length) { // categories, customers in PS 1.7.6+, 8.0+
				$('.js-bulk-actions-btn').closest('.btn-group').first().after(ba_html);
				sf = true;
			} else {
				$('.bulk-actions, [bulkurl]').first().after(ba_html);
			}
			$('.handy-bulk-actions').toggleClass('sf', sf);
			bindEvents(ba_type);
			break;
	}

	if (typeof refreshTotalCombinations != 'undefined') {
		// check /admin/themes/default/js/bundle/product/product-combinations.js
		var refreshTotalCombinationsOrig = refreshTotalCombinations;
		refreshTotalCombinations = function(sign, number) {
			var params = {
					action: 'getUpdatedProducAttributesOptions',
					id_product: $('#form_id_product').val(),
				},
				response = function(r) {
					if ('html' in r) {
						$('.multiselect.atts').replaceWith(r.html); // update product attributes selector
					}
				};
			ajaxRequest(params, response);
			return refreshTotalCombinationsOrig(sign, number);
		}
	}

	function bindCombinationEvents() {
		if (is_16) {
			hba.retro.combinationEvents();
		} else {
			$(document).on('change', '.js-combination, #toggle-all-combinations', function() {
				$('.assignImages').toggleClass('hidden', !$('.js-combination:checked').length);
			});
			$('.combinations-list').on('change', '.no-propagation', function(e) {
				e.stopImmediatePropagation();
			});
		}
		$(document).on('click', '.js-combination, #toggle-all-combinations', function() {
			$('.multiselect-input:checked').addClass('no-events').parent().click();
			$('.multiselect-input.no-events').removeClass('no-events');
		});
		// select combinations by attributes
		$(document).on('click', '.multiselect-value', function() {
			var $parent = $(this).parent().toggleClass('open');
			if ($parent.hasClass('open')) {
				onClickOutSide($parent, function() {
					$parent.removeClass('open');
				});
			}
		}).on('change', '.multiselect-input', function() {
			$(this).parent().toggleClass('checked', $(this).prop('checked'));
			if ($(this).hasClass('no-events')) {
				return;
			}
			let $checkboxes = $('.js-combination'),
				selection = {};
			$(this).closest('.multiselect').find('.multiselect-group').each(function(i) {
				$(this).find('.multiselect-input:checked').each(function() {
					$.each($(this).val().split('-'), function(key, id_combination) {
						if (!selection[i]) {
							selection[i] = {};
						}
						selection[i][id_combination] = id_combination;
					});
				});
			});
			$checkboxes.not(':last').addClass('no-propagation');
			$checkboxes.each(function() {
				let id = $(this).data('id'),
					matching = !$.isEmptyObject(selection);
				$.each(selection, function(i, combination_ids) {
					matching &= id in combination_ids;
				});
				$(this).prop('checked', matching);
			}).removeClass('no-propagation');
		});
		$('.runAction').on('click', function(e) {
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
			$('.bulk-img-checkbox:checked').each(function() {
				params.selected_images.push($(this).data('id'));
			});
			$('.js-combination:checked').each(function() {
				params.selected_combinations.push($(this).data('id'));
			});
			var response = function(r) {
				if (params.action == 'assignImages') {
					if (!is_16) {
						for (var i in params.selected_combinations) {
							var id_combination = params.selected_combinations[i],
								src = $('.bulk-img-checkbox:checked').first().next().attr('src');
							$('#combination_form_'+id_combination).find('.product-combination-image').each(function() {
								var $checkbox = $(this).find('input[type="checkbox"]'),
									id_image = parseInt($checkbox.val()),
									checked = $.inArray(id_image, params.selected_images) > -1;
								$checkbox.prop('checked', checked).parent().toggleClass('img-highlight', checked);
							});
							$('.combination.loaded#attribute_'+id_combination).find('img.img-responsive').attr('src', src);
						}
					} else if (typeof combination_images != 'undefined') {
						// required for dynamic update of standard combination form
						for (var i in params.selected_combinations) {
							combination_images[params.selected_combinations[i]] = params.selected_images;
						}
					}
					$('.bulk-img-checkbox').prop('checked', false);
				} else if ('applied_impacts' in r) {
					var eq = params.action == 'setPriceImpact' ? '1' : '2';
					for (var i in r.applied_impacts) {
						$('.js-combination[data-id="'+i+'"]').closest('tr').find('td:eq('+eq+')').html(r.applied_impacts[i]);
					}
				}
			};
			ajaxRequest(params, response);
		});
	}

	function bindEvents(ba_type) {
		if (ba_type == 'category' || ba_type == 'product') {
			$('.bulk-action-type').on('change', function() {
				let $selectedOption = $(this).find('option:selected'),
					confirmTxt = $selectedOption.data('confirm') || '';
				$(this).closest('.inline-item').nextAll('.inline-item').not('.last').each(function() {
					$(this).toggleClass('hidden', !$(this).hasClass('ba-'+$selectedOption.data('show')));
				});
				$(this).closest('.handy-bulk-actions').find('.runAction').data('confirm', confirmTxt);
			});
			if (ba_type == 'product') {
				$('.f-list').on('change', function() {
					let id_group = $(this).val(),
						html = $('.fv-list').find('option.first').prop('outerHTML');
					if (id_group in ba_feature_values) {
						$.each(ba_feature_values[id_group], function(id, name) {
							html += '<option value="'+id+'">'+name+'</option>'
						});
					}
					$('.fv-list').html(html);
				});
			}
		}
		$('.runAction').on('click', function(e) {
			e.preventDefault();
			if ($(this).data('confirm')) {
				if (!confirm($(this).data('confirm'))) {
					return;
				}
				$(this).data('confirm', ''); // 1 confirmation is enough
			}
			$(this).addClass('loading');
			var params = {
					action: $('[name="action_type"]').val(),
					selected_items: [],
				},
				response = function(r) {},
				checkBoxSelectors = [
					'[name="'+ba_type+'Box[]"]', // 1.6 all, 1.7 categories & customers
					'[name="bulk_action_selected_products[]"]', // 1.7, 8.0+ products
					'.js-bulk-action-checkbox' // 1.7.6+, 8.0+ categories & customers
				],
				$checkedItems = $(checkBoxSelectors.join(',')).filter(':checked');
			ba_selected_items = [];
			$checkedItems.each(function() {
				ba_selected_items.push($(this).val());
			});
			params.selected_items = ba_selected_items.splice(0, ba_chunk);
			switch (ba_type) {
				case 'product':
				case 'category':
					$('.handy-bulk-actions').find('[name]').each(function() {
						params[$(this).attr('name')] = $(this).val();
					});
					response = function(r) {
						if (r.refresh_required) {
							$('.handy-bulk-actions').addClass('refresh-required');
						} else if ('remove_processed' in r && r.processed_items.length) {
							$checkedItems.each(function() {
								if ($.inArray(parseInt($(this).val()), r.processed_items) > -1) {
									$(this).closest('tr').remove();
								}
							});
						} else if (ba_type == 'product') {
							if ('displayed_value' in r) {
								var tdIndex = hba.getTdIndex(params.action);
								if (tdIndex) {
									$checkedItems.each(function() {
										var $td = $(this).closest('td').nextAll().eq(tdIndex),
											$a = $td.find('a').first(),
											$el = $a.length ? $a : $td;
										$el.html(r.displayed_value);
										if (params.action == 'setPrice') {
											var finalPriceKey = 'final_price_'+$(this).val(),
												$finalPriceContainer = getFinalPriceContainer($td);
											if ($finalPriceContainer && finalPriceKey in r) {
												$finalPriceContainer.html(r[finalPriceKey]);
											}
										}
									});
								}
							} else if (params.action == 'replaceText' && 'upd_names' in r) {
								$checkedItems.each(function() {
									let id = $(this).val();
									if (id in r.upd_names) {
										let $td = $(this).closest('td').nextAll().eq(2),
											$a = $td.find('a');
										$a.length ? $a.html(r.upd_names[id]) : $td.html(r.upd_names[id]);
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

	var cosCounter = 0;
	function onClickOutSide($el, action) {
		var identifier = cosCounter++;
		$(document).off('click.'+identifier).on('click.'+identifier, function(e) {
			if (!$el.is(e.target) && $el.has(e.target).length === 0) {
				action();
				$(document).off('click.'+identifier);
			}
		});
	}

	function getFinalPriceContainer($td) {
		if (is_16) {
			return $td.next();
		} else {
			var $fpc = $td.next().find('a');
			if ($fpc.length && $fpc.attr('href').indexOf('#tab-step2') > -1) {
				return $fpc;
			}
		}
	}

	$(document).on('click', '.runAction', function(e) {
		$('.alert.ba').remove();
	});

	function ajaxRequest(params, response) {
		$.ajax({
			type: 'POST',
			url: ba_ajax_path,
			data: params,
			dataType : 'json',
			success: function(r) {
				console.dir(r);
				if ('errors' in r) {
					hba.displayAlert('danger', r.errors);
					$('.runAction').removeClass('loading');
				} else {
					if ('warnings' in r) {
						hba.displayAlert('warning', r.warnings);
					}
					response(r);
					if (ba_selected_items.length) {
						params.selected_items = ba_selected_items.splice(0, ba_chunk);
						ajaxRequest(params, response);
					} else {
						$('.runAction').removeClass('loading');
						$.growl.notice({title: '', message: ba_savedTxt});
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
});
/* since 1.3.0 */
