/**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */

jQuery($ => {
	// ─── Tab Switching ───
	let statsLoaded = false;

	$('.irc-tabs-left .btn-group a').on('click', function onClickTab(e) {
		e.preventDefault();
		
		const panel = this.href.split('panel=')[1].split('&')[0];
		history.replaceState(null, '', this.href);

		$(this).addClass('active').siblings().removeClass('active');
		$('#irc-tab-settings, #irc-tab-stats, #irc-tab-advanced').css('display', 'none');
		$('#irc-tab-' + panel).css('display', '');

		// Sync form actions to current panel to stay on same tab after submit
		$('form[id^="configuration_form"]').each(function () {
			let action = $(this).attr('action');
			if (action.indexOf('panel=') > -1) {
				action = action.replace(/panel=\w+/, 'panel=' + panel);
			} else {
				action += '&panel=' + panel;
			}
			$(this).attr('action', action);
		});

		if (panel === 'stats' && !statsLoaded) {
			statsLoaded = true;
			loadStats();
		}
	});

	if (!$('#irc-tab-stats').is(':hidden')) {
		statsLoaded = true;
		loadStats();
	}

	// ─── Stats ───
	let chart = null;
	const $preset = $('#irc-date-preset');
	const $dateFrom = $('#irc-date-from');
	const $dateTo = $('#irc-date-to');

	$preset.on('change', function () {
		const preset = $(this).val();
		if (preset === 'custom') return;

		const today = new Date();
		const from = new Date();
		const to = new Date();
		const fmt = d => d.toISOString().split('T')[0];

		switch (preset) {
			case '7days':
				from.setDate(today.getDate() - 7);
				break;
			case 'thisweek':
				const dayOfWeek = today.getDay() || 7;
				from.setDate(today.getDate() - dayOfWeek + 1);
				break;
			case 'lastweek':
				const dayOfLastWeek = today.getDay() || 7;
				from.setDate(today.getDate() - dayOfLastWeek - 6);
				to.setDate(today.getDate() - dayOfLastWeek);
				break;
			case '30days':
				from.setDate(today.getDate() - 30);
				break;
			case 'thismonth':
				from.setDate(1);
				break;
			case 'lastmonth':
				from.setMonth(today.getMonth() - 1);
				from.setDate(1);
				to.setDate(0);
				break;
			case '3months':
				from.setMonth(today.getMonth() - 3);
				break;
		}

		$dateFrom.val(fmt(from));
		$dateTo.val(fmt(to));
		loadStats();
	});

	$dateFrom.add($dateTo).on('change', () => $preset.val('custom'));

	function loadStats() {
		$.getJSON(location.href, {
			ajax: 'getStats',
			date_from: $('#irc-date-from').val(),
			date_to: $('#irc-date-to').val(),
			form: $('#irc-form-filter').val()
		}).done(renderStats)
			.fail(() => {
			$('#irc-stats-chart').closest('.irc-chart-wrapper').html(
				'<div class="irc-no-data"><i class="icon-warning-sign"></i>Failed to load statistics</div>'
			);
		});
	}

	function renderStats(data) {
		// Cards
		$('#irc-stat-total').text(formatNumber(data.totals.total));
		$('#irc-stat-legit').text(formatNumber(data.totals.legitimate));
		$('#irc-stat-blocked').text(formatNumber(data.totals.blocked));
		$('#irc-stat-rate').text(data.totals.block_rate + '%');

		// Table
		const $tbody = $('#irc-stats-table tbody').empty();
		const $filter = $('#irc-form-filter');
		if (data.byForm && data.byForm.length) {
			data.byForm.forEach(row => {
				const rate = row.total > 0 ? Math.round(row.blocked / row.total * 100) : 0;
				$tbody.append(`<tr>
					<td><strong>${$filter.find('[value="' + row.form + '"]').text()}</strong></td>
					<td class="text-center">${formatNumber(row.total)}</td>
					<td class="text-center"><span class="badge irc-badge-legit">${formatNumber(row.legitimate)}</span></td>
					<td class="text-center"><span class="badge irc-badge-blocked">${formatNumber(row.blocked)}</span></td>
					<td class="text-center">${rate}%</td>
				</tr>`);
			});
		} else {
			$tbody.append('<tr><td colspan="5" class="irc-no-data"><i class="icon-bar-chart"></i>No data for the selected period</td></tr>');
		}

		// Chart
		renderChart(data.daily || []);
	}

	function renderChart(daily) {
		const ctx = document.getElementById('irc-stats-chart');
		if (!ctx) return;

		const labels = daily.map(d => d.date);
		const legit = daily.map(d => parseInt(d.legitimate) || 0);
		const blocked = daily.map(d => parseInt(d.blocked) || 0);

		if (chart) {
			chart.data.labels = labels;
			chart.data.datasets[0].data = legit;
			chart.data.datasets[1].data = blocked;
			chart.update();
			return;
		}

		chart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [
					{
						label: i18n.legitimate,
						data: legit,
						backgroundColor: 'rgba(114, 194, 121, 1)',
						borderRadius: 3
					},
					{
						label: i18n.blocked,
						data: blocked,
						backgroundColor: 'rgba(224, 143, 149, 1)',
						borderRadius: 3
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				datasets: {
					bar: {
						barPercentage: 0.95,
						categoryPercentage: 0.7
					}
				},
				interaction: {
					intersect: false,
					mode: 'index'
				},
				plugins: {
					legend: {
						position: 'top',
						labels: { padding: 16, usePointStyle: true, pointStyleWidth: 16 }
					},
					tooltip: {
						backgroundColor: 'rgba(0,0,0,.8)',
						padding: 12,
						cornerRadius: 6,
						titleFont: { size: 13 }
					}
				},
				scales: {
					x: {
						grid: { display: false },
						ticks: { maxRotation: 45 }
					},
					y: {
						beginAtZero: true,
						ticks: { precision: 0 },
						grid: { color: 'rgba(0,0,0,.05)' }
					}
				}
			}
		});
	}

	$('#irc-apply-filters').on('click', () => {
		loadStats();
	});

	function formatNumber(n) {
		return parseInt(n || 0).toLocaleString();
	}

	function escHtml(s) {
		return $('<span>').text(s).html();
	}
});
