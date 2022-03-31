/**
 * 2007-2020 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
window.onload = function() {
    var config_image_optimize = {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [percent_optimized_images,percent_unoptimized_images],
					backgroundColor: [
						'#12addd',
						'#ff718c',
					],
                    borderWidth: percent_optimized_images==0|| percent_unoptimized_images==0 ? 0:1,
					label: image_text,
                    padding:20,
				}],
				labels: [
					Optimized_text,
					Unoptimized_text,
				]
			},
			options: {
                tooltips: {
                  callbacks: {
                        label: function(tooltipItem, data) {
                          return data['labels'][tooltipItem['index']]+':'+data['datasets'][0]['data'][tooltipItem['index']]+'%';
                        }
                    }
                },
				responsive: true,
                legend: {
                    display: false,
                },
                cutoutPercentage:65,
			}
		};
	var ctx_image_optimize = document.getElementById('sp-image-chart-area').getContext('2d');
	chart_image_optimize = new Chart(ctx_image_optimize, config_image_optimize);
};