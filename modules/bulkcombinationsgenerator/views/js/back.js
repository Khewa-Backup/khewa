/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

var ajax_path = window.location.href.split('#')[0]+'&ajax=1',
	blockUpdateSelectedOptionsTxt= false,
	blockAjax = false,
	getProducTimer;

$(document).ready(function() {

	$(document).on('click', 'a[href="#"]', function(e) {
		e.preventDefault();
	})
	$('[data-toggle="tooltip"]').tooltip();

	// activate tabs
	$('.tab-option').on('click', function(e){
		e.preventDefault();
		$(this).addClass('active').siblings('.tab-option').removeClass('active');
		$('.tab-content'+$(this).attr('href')).addClass('active').siblings('.tab-content').removeClass('active');
		if (!$(this).hasClass('stop-propagation')) {
			var actionType = $(this).attr('href') == '#duplicate-combinations' ? 'duplicate' : 'update';
			$('.processAction').val(actionType+'Combinations').change().
			find('option[value="duplicateCombinations"]').toggleClass('hidden', actionType == 'update').
			siblings().toggleClass('hidden', actionType != 'update');
		}
	});

	// fitler options
	$('.selected-options-inline').on('click', function(e){
		var $availableOptions = $(this).closest('.form-group').find('.available-options');
		$availableOptions.toggleClass('hidden');
		if (!$availableOptions.hasClass('hidden')) {
			onClickOutSide($availableOptions, function(){$availableOptions.addClass('hidden')});
			e.stopPropagation();
		}

	});

	function onClickOutSide($el, action) {
		$(document).trigger('click.outside'); // reset possible events attached previously to other $elements
		$(document).on('click.outside', function(e) {
			if (!$el.is(e.target) && $el.has(e.target).length === 0) {
				action();
				$(document).off('click.outside');
			}
		});
	}

	$('.toggleChildren').on('click', function(e){
		e.preventDefault();
		var $opt = $(this).closest('.opt');
		$opt.toggleClass('closed');
		markCheckedChildren($opt);
	});
	$('.opt-checkbox').on('change', function(){
		$(this).closest('label').toggleClass('checked', $(this).prop('checked'));
	});

	$('[data-bulk-action]').on('click', function(e){
		var $group = $(this).closest('.form-group'),
			action = $(this).data('bulk-action'),
			toggleOtherOption = $(this).data('toggle');
		switch (action) {
			case 'open':
			case 'close':
				var selector = action == 'open' ? '.opt.closed' : '.opt:not(.closed)';
				$group.find(selector).children('.opt-label').children('.toggleChildren').click();
				break;
			case 'check':
			case 'uncheck':
			case 'invert':
				blockUpdateSelectedOptionsTxt = true;
				$group.find('.opt-checkbox').each(function (){
					var checked = action == 'check' ? true : action == 'uncheck' ? false : !$(this).prop('checked');
					$(this).prop('checked', checked).change();
				});
				$('.opt.closed').each(function(){
					markCheckedChildren($(this));
				});
				blockUpdateSelectedOptionsTxt = false;
				updateSelectedOptionsTxt($group);
				break;
		}
		if (toggleOtherOption) {
			$(this).addClass('hidden');
			$(this).siblings('[data-bulk-action="'+toggleOtherOption+'"]').removeClass('hidden');
		}
	});

	$('.toggleIDs').on('change', function(){
		$(this).closest('.form-group').find('.opt-id').toggleClass('hidden', !$(this).prop('checked'));
	});

	$('.resetFilter').on('click', function(){
		var $group = $(this).closest('.form-group'),
			$textInput = $group.find('.text-input');
		if ($textInput.length) {
			$textInput.val('').change();
		} else {
			$group.find('.opt-action[data-bulk-action="uncheck"]').click();
		}
	})

	$('.opt-checkbox').on('change', function(){
		updateSelectedOptionsTxt($(this).closest('.form-group'));
	});

	function updateSelectedOptionsTxt($group) {
		if (blockUpdateSelectedOptionsTxt) {
			return;
		}
		var $checked = $group.find('.opt-checkbox:checked'),
			total = $checked.length,
			displayedNum = 7,
			selectedTxt = $group.find('.selected-options-inline').find('.all').text(),
			extra = '';
		if ($group.find('.dynamic-name').length) {
			selectedTxt = [];
			$checked.each(function(){
				if (selectedTxt.length < displayedNum) {
					selectedTxt.push($(this).closest('.opt-label').find('.opt-name').text());
				} else {
					extra = ' ... + '+(total - displayedNum);
					return false;
				}
			});
			selectedTxt = selectedTxt.join(', ')+extra;
			$group.find('.item-names').html(selectedTxt);
			// .siblings('.total').html(total);
		}
		$group.toggleClass('has-selection', !!total);
		if ($group.closest('form').hasClass('products-form')) {
			getFilteredProductsNum();
		}
	}

	$('.filter-value').on('change', '.text-input', function() {
		$(this).closest('.form-group').toggleClass('has-selection', !!$(this).val());
		getFilteredProductsNum();
	}).on('keyup', '.numeric', function(e){
		var val = $(this).val(), requiredVal = val.replace(/[^\d,]/g,'');
		if (val != requiredVal) {
			$(this).val(requiredVal);
		}
		if (e.keyCode == 13) {
			$(this).blur();
		}
	});

	$('.products-form').on('submit', function(e) {
		e.preventDefault();
	})

	function markCheckedChildren($opt) {
		var childrenChecked = $opt.find('.opt-level').find('.opt-checkbox:checked').length,
			showNum = childrenChecked && $opt.hasClass('closed');
		$opt.children('.checked-num').toggleClass('hidden', !showNum).find('.dynamic-num').html(childrenChecked);
	}

	function getFilteredProductsNum() {
		var params = $('.products-form').serialize()+'&action=getFilteredProductsNum',
			response = function(r) {
				if ('log_txt' in r) {
					updateLog(utf8_decode(r.log_txt));
				}
			};
		if ($('#duplicate-combinations').hasClass('active')) {
			params += '&exclude_ids='+$('input.id_product_original').val();
		}
		updateLog(l.loading);
		ajaxRequest(params, response);
	}

	getFilteredProductsNum();

	$('.showAttributes').on('click', function(){
			var params = 'action=showAttributes',
				response = function(r) {
					$('#dynamic-popup').find('.dynamic-content').html(utf8_decode(r.content));
					$('#dynamic-popup').find('.modal-title').html(utf8_decode(r.title));
				};
			ajaxRequest(params, response);
	});

	$(document).on('click', '.addSelectedItems', function(){
		if ($(this).hasClass('btn-blocked')) {
			return;
		}
		var ids = [];
		$('#dynamic-popup').find('.item.selected').not('.blocked').each(function(){
			ids.push($(this).data('id'));
		});
		fillDynamicRows(ids, {}, false);
		$('#dynamic-popup').find('.close').click();
	});

	function fillDynamicRows(ids, impacts, eraseAll) {
		var params = 'action=getDynamicRows&ids='+ids.join(','),
			response = function(r) {
				var $newRows = $(utf8_decode(r.rows_html)),
					$selectedRows = [];
				$.each(ids, function(i, id) {
					var $row = $('.dynamic-att-rows').find('.att-row[data-id="'+id+'"]');
					if (!$row.length || eraseAll) {
						$row = $newRows.filter('.att-row[data-id="'+id+'"]');
					}
					$selectedRows.push($row);
				});
				$('.dynamic-att-rows').html('');
				$.each($selectedRows, function(i, $row) {
					$row.appendTo('.dynamic-att-rows');
				});
				$.each(impacts, function(i_name, i_values) {
					$.each(i_values, function(id_att, i) {
						if (i.value) {
							$('.dynamic-att-rows').find('[name="a[impacts]['+i_name+']['+id_att+'][prefix]"]').val(i.prefix).change();
							$('.dynamic-att-rows').find('[name="a[impacts]['+i_name+']['+id_att+'][suffix]"]').val(i.suffix).change();
							$('.dynamic-att-rows').find('[name="a[impacts]['+i_name+']['+id_att+'][value]"]').val(i.value).keyup();
						}
					});
				});
				toggleComplexPersentage();
				updateSelectedAttsSummary();
				$newRows = {};
			};
		ajaxRequest(params, response);
	}

	$('.removeAllRows').on('click', function(){
		$(this).closest('.selected-atts').find('.att-row').remove();
		toggleComplexPersentage();
		updateSelectedAttsSummary();
	});

	$('.dynamic-att-rows').on('click', '.removeRow', function(){
		$(this).closest('.att-row').remove();
		toggleComplexPersentage();
		updateSelectedAttsSummary();
	}).on('click', '.resetImpacts', function(){
		$(this).closest('.att-row').find('.input').each(function(){
			$(this).find('.impact-value').val('').keyup();
			$(this).find('.input-prefix, .input-suffix').each(function(){
				var val = $(this).find('.first').val();
				$(this).val(val).change();
			});
		});
	}).on('change', '.input-suffix', function(){
		toggleComplexPersentage();
	}).on('focusin', '.impact-value', function(e){
		$(this).closest('.input').addClass('focused');
	}).on('focusout', '.impact-value', function(e){
		$(this).closest('.input').removeClass('focused');
	}).on('keyup', '.impact-value', function(){
		var formattedValue = $(this).val().replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'),
			$row = $(this).closest('.att-row');
		$(this).val(formattedValue).closest('.attribute-impact').toggleClass('has-value', !!formattedValue);
		$row.toggleClass('has-impacts', !!$row.find('.attribute-impact.has-value').length);
	});

	function toggleComplexPersentage() {
		$('.complex-percentage').toggleClass('hidden', !$('option.percentage:selected').length);
	}

	function updateSelectedAttsSummary() {
		var groupedItems = {}, totalAtts = 0, totalCombs = 0;
		$('.dynamic-att-rows').find('.att-row').each(function(){
			var id_group = $(this).data('group');
			groupedItems[id_group] = groupedItems.hasOwnProperty(id_group) ? groupedItems[id_group] + 1 : 1;
			totalAtts++;
		});
		$.each(groupedItems, function(id_group, atts_num) {
			totalCombs = totalCombs ? totalCombs * atts_num : atts_num;
		});
		$('.total-atts').html(totalAtts);
		$('.total-combs').html(totalCombs);
		$('.removeAllRows').toggleClass('hidden', !totalAtts);
	}

	/* duplicate combinations */
	$('#duplicate-combinations').find('input.id_product_original').on('keyup', function(){
		var $el = $(this), $spinner = $el.closest('.form-group').find('.icon-spin');
		clearTimeout(getProducTimer);
		getProducTimer = setTimeout(function() {
			params = 'action=getCombinationsSummary&id_product='+$el.val(),
			response = errorResponse = function(r){
				var summary = ('summary' in r) ? utf8_decode(r.summary) : ''
				$spinner.addClass('hidden').siblings().html(summary);
			};
			response({});
			if ($el.val()) {
				$spinner.removeClass('hidden');
				ajaxRequest(params, response, errorResponse);
			}
		}, 300);
	}).on('focusout', function(){
		getFilteredProductsNum();
	});

	$('.processAction').on('change', function(){
		getFilteredProductsNum();
	});

	$('.runAction').on('click', function(){
		processSelectedItems($('.processAction').val(), 0, 0);
	});

	/* reference variables */
	$('.toggleVariables').on('click', function(){
		$(this).closest('.variables-container').toggleClass('show-list');
	});

	/* import/export */
	$('.exportSettings').on('click', function(){
		var $form = $(this).closest('form'),
			serializedData = $('.attributes-form, .products-form, .process-form').serialize();
		$form.find('input[name="serialized_data"]').val(serializedData);
		$form.submit();
	});
	$('.importSettings').on('click', function(){
		$(this).closest('form').find('input[type="file"]').first().click();
	});
	$('input[name="importSettings"]').on('change', function(){
		var files = !!this.files ? this.files : [];
		if (!files.length || files[0].type != 'text/plain' || !window.FileReader)
			return;
		var reader = new FileReader();
		reader.readAsText(files[0]);
		reader.onloadend = function(){
			fillSettings($.parseJSON(this.result));
		};
		$(this).val('');
	});

	function fillSettings(settings) {
		// reset data
		blockAjax = true;
		$('.override-label').find('input[type="checkbox"]').prop('checked', false);
		$('.resetFilter').click();
		if ('a' in settings) {
			var a = settings.a;
			if ('values' in a) {
				var ids = [], impacts = 'impacts' in a ? a.impacts : {};
				$.each(a.values, function(id_group, attributes) {
					$.each(attributes, function(id_att) {
						ids.push(id_att);
					});
				});
				blockAjax = false;
				fillDynamicRows(ids, impacts, true);
				blockAjax = true;
			}
			if ('options' in a) {
				for (var opt_name in a.options) {
					$('.selected-att-options').find('[name="a[options]['+opt_name+']"]').val(a.options[opt_name]);
				}
				if ('override_options' in a) {
					for (var override_name in a.override_options) {
						$('.selected-att-options').find('input[name="a[override_options]['+override_name+']"]')
						.prop('checked', !!a.override_options[override_name]);
					}
				}
			}
			$.each(['id_product_original', 'new_reference'], function(i, name){
				if (name in a) {
					$('#duplicate-combinations').find('[name="a['+name+']"]').val(a[name]).keyup();
				}
			})
		}
		if ('filters' in settings) {
			$.each(settings.filters, function(filter_name, filter_values) {
				if (typeof filter_values == 'string') {
					$('.products-form').find('input[name="filters['+filter_name+']"]').val(filter_values).change();
				} else {
					var lastIndex = filter_values.length - 1;
					$.each(filter_values, function(i, id) {
						var $el = $('.products-form').find('input[name="filters['+filter_name+'][]"][value="'+id+'"]');
						$el.prop('checked', true);
						if (i == lastIndex) {
							$el.change();
						}
					});
				}
			});
		}
		if ('action' in settings) {
			var href = settings.action == 'duplicateCombinations' ? 'duplicate-combinations' : 'manual-assign';
			$('.att-actions').find('a[href="#'+href+'"]').click();
			$('.processAction').val(settings.action);
		}
		blockAjax = false;
		$('.processAction').change();
	}

	function processSelectedItems(action, time, identifier) {
		var timeStart = t(),
			identifier = identifier ? identifier : timeStart,
			params = $('.tab-content.active').find('.attributes-form').serialize()+
			'&'+$('.products-form').serialize()+'&action='+action+'&identifier='+identifier,
			response = function(r){
				time += t() - timeStart;
				var total = r.processed + r.to_process,
					remainingTime = r.to_process && r.processed ? time / r.processed * r.to_process : 0,
					log = l.products_processed.replace('%s', r.processed+'/'+total)+'<br>';
				log += l.time_spent.replace('%s', timeTxt(time))+'<br>';
				if (r.to_process) {
					if (r.processed) {
						log += l.time_remaining.replace('%s', timeTxt(remainingTime))+'<br>';
					}
					log += '<span class="note">----------<br>'+l.dont_close+'</span>';
					var $resume = $('.runAction').find('[data-command="resume"]');
					if (!$resume.hasClass('active')) {
						processSelectedItems(action, time, identifier);
					} else { // $resume may be used in one of upcoming versions
						$resume.data('time', time);
					}
				} else {
					log = l.complete+'<br>'+log;
					$('.bcg-container').removeClass('blocked');
				}
				updateLog(log);
			},
			errorResponse = function(r) {
				$('.bcg-container').removeClass('blocked');
			};
		$('.bcg-container').addClass('blocked');
		updateLog(l.loading);
		ajaxRequest(params, response, errorResponse);
	}

	function timeTxt(seconds) {
		seconds = Math.round(seconds);
		var h = Math.floor(seconds / 3600),
			m = Math.floor((seconds / 60) % 60),
			s = seconds % 60,
			txt = (h ? h+' h ' : '')+(m ? m+' m ' : '')+(s ? s+' s' : '');
		return txt ? txt : '0 s';
	}

	function t() {
		return new Date().getTime()/1000;
	}

	function updateLog(content, prepend) {
		var $log = $('.dynamic-log-content');
		if (typeof prepend === 'undefined') {
			$log.html('');
		} else {
			content += '<br>';
		}
		$log.prepend(content);
	}

	function ajaxRequest(params, response, errorResponse){
		if (blockAjax) {
			return;
		}
		errorResponse = typeof errorResponse == 'undefined' ? function(r){} : errorResponse;
		$.ajax({
			type: 'POST',
			url: ajax_path,
			data: params,
			dataType : 'json',
			success: function(r) {
				$('.dynamic-log').removeClass('loading');
				console.dir(r);
				if ('error' in r) {
					updateLog('<span class="'+r.class+'">'+utf8_decode(r.error)+'</span>');
					errorResponse(r);
				} else {
					response(r);
				}
			},
			error: function(r) {
				console.warn($(r.responseText).text() || r.responseText);
				updateLog('<span class="error">'+(l.check_console)+'</span>', 1);
				errorResponse(r);
			}
		});
	}
});

function utf8_decode(utfstr) {
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
/* since 2.1.1 */

