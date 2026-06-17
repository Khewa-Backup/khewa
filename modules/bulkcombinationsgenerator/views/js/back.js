/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

var bcg = {
		defineVars: function () {
			bcg.identifier = bcg.getUniqueID();
			bcg.ajax_path = window.location.href.split('#')[0]+'&ajax=1';
			bcg.total = {atts: 0, products_num: 0};
			bcg.currentAction = '';
			bcg.blockAjax = false;
		},
		init: function() {
			bcg.defineVars();
			bcg.log.init();
			bcg.bindEvents();
		},
		bindEvents: function () {
			$(document).on('click', 'a[href="#"]', function(e) {
				e.preventDefault();
			});
			$('[data-toggle="tooltip"]').tooltip();
			$('.tax-incl').on('change', function() {
				bcg.storage.save('bcg_tax_incl', $(this).val());
				$.growl.notice({title: '', message: l.saved});
			}).val(bcg.storage.get('bcg_tax_incl') || 0);
		},
		updFilteredProductsNum: function() {
			let params = $('.products-form').serialize()+'&action=getFilteredProductsNum',
				response = function(r) {
					if ('log_txt' in r) {
						bcg.log.upd('<span class="ready-to-process">'+r.log_txt+'</span>');
					}
					bcg.log.updInfo();
				};
			if ($('#duplicate-combinations').hasClass('active')) {
				params += '&exclude_ids='+$('input.id_product_original').val();
			}
			bcg.log.upd(l.loading);
			bcg.ajaxRequest(params, response);
		},
		log: {
			init: function() {
				this.$el = $('.dynamic-log-content');
				this.iconCheck = '<i class="u-check"></i> ';
			},
			isReady: function() {
				return this.$el.find('.ready-to-process').length;
			},
			updInfo: function() {
				if (this.$el.find('.process-complete').length || this.$el.find('.log-error').length) {
					bcg.updFilteredProductsNum();
				}
				if (!this.isReady()) {
					return;
				}
				if (!this.$el.find('.log-info').length) {
					this.$el.append('<div class="log-info"></div>');
				}
				let info = '';
				switch (bcg.currentAction) {
					case 'update':
						if (bcg.total.atts) {
							info += this.iconCheck+l.upd_existing+'<br>';
							info += this.iconCheck+l.add_new_maybe+'<br>';
						}
						info += this.getOverrideTxt();
						break;
					case 'updateByAtts':
						info += this.getOverrideTxt();
						break;
					case 'addNew':
						if (bcg.total.atts) {
							info += this.iconCheck+l.add_new+' ('+l.if_dont_exist+')';
						}
						break;
					case 'regenerate':
						info += this.iconCheck+l.delete_all+'<br>';
						info += this.iconCheck+l.add_new;
						break;
					case 'deleteByAtts':
						info += this.iconCheck+l.delete_selected;
						break;
					case 'delete':
						info += this.iconCheck+l.delete_all;
						break;
				}
				$('.log-info').html(info);
			},
			getOverrideTxt: function() {
				let txt = '',
					overrideFields = [];
				$('.override-label').not('.hidden').find('input:checked').each(function() {
					overrideFields.push('<span class="u">'+$.trim($(this).parent().text())+'</span>');
				});
				if (overrideFields.length) {
					txt = bcg.currentAction == 'update' && !bcg.total.atts ? l.override_all : l.override_selected;
					txt = this.iconCheck+txt.replace('%s', overrideFields.join(', '));
				}
				return txt;
			},
			upd: function(content, append) {
				if (typeof append === 'undefined') {
					this.$el.html('');
				}
				this.$el.append(content);
			},
		},
		time: {
			now: function() {
				return new Date().getTime()/1000;
			},
			txt: function(seconds) {
				seconds = Math.round(seconds);
				var h = Math.floor(seconds / 3600),
					m = Math.floor((seconds / 60) % 60),
					s = seconds % 60,
					txt = (h ? h+' h ' : '')+(m ? m+' m ' : '')+(s ? s+' s' : '');
				return txt ? txt : '0 s';
			},
		},
		onClickOutSide: function($el, action) {
			setTimeout(function() {
				if (!$el.data('clickEvt')) {
					$el.data('clickEvt', 'click.'+bcg.getUniqueID());
				}
				$(document).off($el.data('clickEvt')).on($el.data('clickEvt'), function(e) {
					if (!$el.is(e.target) && $el.has(e.target).length === 0) {
						action();
						$(document).off($el.data('clickEvt'));
					}
				});
			}, 0);
		},
		getUniqueID: function() {
			return (Math.floor(Math.random() * 1000) + bcg.time.now()) * 1000;
		},
		ajaxRequest: function (params, response, errorResponse) {
			if (bcg.blockAjax) {
				return;
			}
			errorResponse = errorResponse || function(r) {};
			$.ajax({
				type: 'POST',
				url: bcg.ajax_path,
				data: params,
				dataType : 'json',
				success: function(r) {
					console.dir(r);
					if ('error' in r) {
						bcg.log.upd('<span class="log-'+r.class+'">'+r.error+'</span>');
						errorResponse(r);
					} else {
						response(r);
					}
				},
				error: function(r) {
					console.warn($(r.responseText).text() || r.responseText);
					bcg.log.upd('<span class="log-error">'+(l.check_console)+'</span>');
					errorResponse(r);
					let eraseDataParams = 'action=eraseData';
					if (params != eraseDataParams) {
						bcg.ajaxRequest(eraseDataParams, function(){}, function(){});
					}
				}
			});
		},
		storage: {
			get: function(key) {
				if (typeof localStorage != 'undefined') {
					return localStorage[key];
				}
			},
			save: function(key, value) {
				if (typeof localStorage != 'undefined') {
					localStorage[key] = value;
				}
			}
		},
	},
	blockUpdateSelectedOptionsTxt= false,
	getProducTimer;

$(document).ready(function() {

	bcg.init();

	// activate tabs
	$('.tab-option').on('click', function(e) {
		e.preventDefault();
		$(this).addClass('active').siblings('.tab-option').removeClass('active');
		$('.tab-content'+$(this).attr('href')).addClass('active').siblings('.tab-content').removeClass('active');
		if (!$(this).hasClass('stop-propagation')) {
			var actionType = $(this).attr('href') == '#duplicate-combinations' ? 'duplicate' : 'update';
			$('.processAction').val(actionType).change().
			find('option[value="duplicate"]').toggleClass('hidden', actionType == 'update').
			siblings().toggleClass('hidden', actionType != 'update');
			bcg.updFilteredProductsNum();
		}
	});

	// filter options
	$('.selected-options-inline').on('click', function() {
		let $availableOptions = $(this).closest('.form-group').find('.available-options').toggleClass('hidden');
		if (!$availableOptions.hasClass('hidden')) {
			bcg.onClickOutSide($availableOptions, function() {
				$availableOptions.addClass('hidden');
			});
		}
	});

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

	$('.toggleIDs').on('change', function() {
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
			$checked.each(function() {
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
			bcg.updFilteredProductsNum();
		}
	}

	$('.filter-value').on('change', '.text-input', function() {
		$(this).closest('.form-group').toggleClass('has-selection', !!$(this).val());
		bcg.updFilteredProductsNum();
	}).on('keyup', '.numeric', function(e){
		var val = $(this).val(), requiredVal = val.replace(/[^\d,-]/g,'');
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

	$('.showAttributes').on('click', function() {
		var $modal = $('#bcg-dynamic-popup').addClass('loading'),
			params = 'action=showAttributes',
			response = function(r) {
				$modal.removeClass('loading').find('.dynamic-content').html(r.content);
				$modal.find('.modal-title').html(r.title);
			};
		bcg.ajaxRequest(params, response);
	});

	$(document).on('click', '.addSelectedItems', function(){
		if ($(this).hasClass('btn-blocked')) {
			return;
		}
		var ids = [];
		$('#bcg-dynamic-popup').find('.item.selected').not('.blocked').each(function(){
			ids.push($(this).data('id'));
		});
		fillDynamicRows(ids, {}, false);
		$('#bcg-dynamic-popup').find('.close').click();
	});

	function fillDynamicRows(ids, impacts, eraseAll) {
		var params = 'action=getDynamicRows&ids='+ids.join(','),
			response = function(r) {
				var $newRows = $(r.rows_html),
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
				updateSelectedAttsSummary();
			};
		bcg.ajaxRequest(params, response);
	}

	$('.removeAllRows').on('click', function(){
		$(this).closest('.selected-atts').find('.att-row').remove();
		updateSelectedAttsSummary();
	});

	$('.dynamic-att-rows').on('click', '.removeRow', function(){
		$(this).closest('.att-row').remove();
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
		toggleImpactRelatedFields(1, 0, 0);
	}).on('focusin', '.impact-value', function(e){
		$(this).closest('.input').addClass('focused');
	}).on('focusout', '.impact-value', function(e){
		$(this).closest('.input').removeClass('focused');
	}).on('keyup', '.impact-value', function(){
		var formattedValue = $(this).val().replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'),
			$row = $(this).closest('.att-row');
		$(this).val(formattedValue).closest('.attribute-impact').toggleClass('has-value', !!formattedValue);
		$row.toggleClass('has-impacts', !!$row.find('.attribute-impact.has-value').length);
		toggleImpactRelatedFields(0, 1, 1);
		if (bcg.currentAction == 'updateByAtts') {
			bcg.log.updInfo();
		}
	});

	function toggleImpactRelatedFields(percentage, impacts, eraseImpacts) {
		if (percentage) {
			$('.complex-percentage').toggleClass('hidden', !$('option.percentage:selected').length);
		}
		if (impacts) {
			$('.override-label.for-impacts').toggleClass('hidden', !$('.has-impacts').length);
		}
		if (eraseImpacts) {
			$('.override-impacts').toggleClass('hidden', !$('.toggleOverrideImpactOptions:visible:checked').length);
		}
	}
	toggleImpactRelatedFields(1, 1, 1);

	function updateSelectedAttsSummary() {
		bcg.total.atts = 0;
		var groupedItems = {},
			possibleCombsNum = 0;
		$('.dynamic-att-rows').find('.att-row').each(function() {
			var id_group = $(this).data('group');
			groupedItems[id_group] = groupedItems.hasOwnProperty(id_group) ? groupedItems[id_group] + 1 : 1;
		});
		$.each(groupedItems, function(id_group, atts_num) {
			bcg.total.atts += atts_num;
			possibleCombsNum = possibleCombsNum ? possibleCombsNum * atts_num : atts_num;
		});
		$('.total-atts').html(bcg.total.atts);
		$('.total-combs').html(possibleCombsNum);
		$('.removeAllRows').toggleClass('hidden', !bcg.total.atts);
		toggleImpactRelatedFields(1, 1, 1);
		bcg.log.updInfo();
	}

	/* duplicate combinations */
	$('#duplicate-combinations').find('input.id_product_original').on('keyup', function() {
		var $el = $(this),
			$spinner = $el.closest('.form-group').find('.icon-spin');
		clearTimeout(getProducTimer);
		getProducTimer = setTimeout(function() {
			let params = 'action=getCombinationsSummary&id_product='+$el.val(),
				response = errorResponse = function(r) {
					$spinner.addClass('hidden').siblings().html(r.summary || '');
				};
			response({});
			if ($el.val()) {
				$spinner.removeClass('hidden');
				bcg.ajaxRequest(params, response, errorResponse);
			}
		}, 300);
	}).on('focusout', function() {
		bcg.updFilteredProductsNum();
	});

	$('.processAction').on('change', function() {
		bcg.currentAction = $(this).val();
		let showOverridePanel = bcg.currentAction == 'update' || bcg.currentAction == 'updateByAtts';
		$('.override-settings').toggleClass('hidden', !showOverridePanel)
			.find('input[type="checkbox"]').prop('checked', bcg.currentAction == 'updateByAtts');
		bcg.updFilteredProductsNum();
	}).change();

	$('.override-settings-form').find('input[type="checkbox"]').on('change', function() {
		if ($(this).hasClass('toggleOverrideImpactOptions')) {
			toggleImpactRelatedFields(0, 0, 1);
		}
		bcg.log.updInfo();
	});

	$('.runAction').on('click', function() {
		processCombinations(0);
	});

	/* reference variables */
	$('.toggleVariables').on('click', function() {
		let $varsContainer = $(this).closest('.variables-container').toggleClass('show-list');
		if ($varsContainer.hasClass('show-list')) {
			bcg.onClickOutSide($varsContainer.closest('.att-option, .form-group'), function() {
				$varsContainer.removeClass('show-list');
			});
		}
	});

	/* import/export */
	$('.exportSettings').on('click', function(){
		var $form = $(this).closest('form'),
			serializedData = $('.attributes-form, .products-form, .process-form, .override-settings-form').serialize();
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
		bcg.blockAjax = true;
		$('.override-label').find('input[type="checkbox"]').prop('checked', false);
		$('.resetFilter').click();
		if ('a' in settings) {
			var a = settings.a;
			if ('values' in a) {
				let ids = [],
					impacts = a.impacts || {};
				$.each(a.values, function(id_group, attributes) {
					$.each(attributes, function(id_att) {
						ids.push(id_att);
					});
				});
				bcg.blockAjax = false;
				fillDynamicRows(ids, impacts, true);
				bcg.blockAjax = true;
			}
			if ('options' in a) {
				for (var opt_name in a.options) {
					$('.attributes-form').find('[name="a[options]['+opt_name+']"]').val(a.options[opt_name]);
				}
				bcg.storage.save('bcg_tax_incl', $('.tax-incl').val());
				if ('override_options' in a) {
					$.each(a.override_options, function(name, value) {
						let $el = $('.override-settings-form').find('[name="a[override_options]['+name+']"]');
						$el.is('select') ? $el.val(value) : $el.prop('checked', !!value);
					});
				}
			}
			$.each(['id_product_original', 'new_reference'], function(i, name) {
				if (name in a) {
					$('#duplicate-combinations').find('[name="a['+name+']"]').val(a[name]).keyup();
				}
			});
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
			var href = settings.action.startsWith('duplicate') ? 'duplicate-combinations' : 'manual-assign';
			$('.att-actions').find('a[href="#'+href+'"]').click();
			$('.processAction').val(settings.action);
		}
		bcg.blockAjax = false;
		$('.processAction').change();
	}

	function processCombinations(time) {
		var timeStart = bcg.time.now(),
			params = $('.tab-content.active').find('.attributes-form')
			.add('.products-form, .process-form, .override-settings-form').serialize()+'&identifier='+bcg.identifier,
			response = function(r) {
				time += bcg.time.now() - timeStart;
				var total = r.num.processed + r.num.to_process,
					timePerItem = r.num.processed ? time / r.num.processed : 0,
					remainingTime = timePerItem * r.num.to_process,
					log = l.products_processed.replace('%s', r.num.processed+'/'+total)+'<br>',
					requiredActionStats = {
						update: 'updated',
						addNew: 'added',
						regenerate: 'added',
						deleteByAtts: 'deleted',
						delete: 'deleted',
					};
				$.each(r.combs_num, function(key, num) {
					if (num || key == requiredActionStats[bcg.currentAction]) {
						log += l['combs_'+key].replace('%s', num)+'<br>';
					}
				});
				log += l.time_spent.replace('%s', bcg.time.txt(time));
				if (r.num.to_process) {
					if (remainingTime) {
						log += ' ('+l.time_remaining.replace('%s', bcg.time.txt(remainingTime))+')';
					}
					log += '<br><span class="log-note">----------<br>'+l.dont_close+'</span>';
					var $resume = $('.runAction').find('[data-command="resume"]');
					if (!$resume.hasClass('active')) {
						processCombinations(time);
					} else { // $resume may be used in one of upcoming versions
						$resume.data('time', time);
					}
				} else {
					log = '<div class="process-complete">'+l.complete+'<br>'+log+'</div>';
					$('.bcg-container').removeClass('blocked');
				}
				bcg.log.upd(log);
			},
			errorResponse = function(r) {
				$('.bcg-container').removeClass('blocked');
			};
		$('.bcg-container').addClass('blocked');
		bcg.log.upd(l.loading);
		bcg.ajaxRequest(params, response, errorResponse);
	}

});
/* since 2.1.4 */
