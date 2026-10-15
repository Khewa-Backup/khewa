/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
$(document).ready( function () {
	var progressbar = $('#progressbar'),
	progressLabel = $('.progress-label');

	progressbar.progressbar({
		value: false,
		change: function() {
			progressLabel.text(progressbar.progressbar('value') + '%');
		},
		complete: function() {
			progressLabel.text(txt_waitlink);
		}
	});

	if (typeof(parts) != 'undefined') {
		var procent = 100 / parts;
	}
	var jsonPostData = JSON.stringify(post_data);
	var pageNum = 1;
	var pdfList = '';

	processCreatePDF(0);

	function processCreatePDF(index)
	{
		var num = index + 1;

		$.ajax({
			url: unescape(urlpdf),
			type: 'post',
			data: 'createpdf='+num +'&inventories_products_ids='+inventories_products_ids+'&post_data='+jsonPostData+'&page_num='+pageNum+'&pdf_length='+parts,
			dataType: 'json',
			success: function(json_data) {
				//console.log(JSON.stringify(json_data));
				if (json_data['page_num']) {
					pageNum = pageNum + parseInt(json_data['page_num']);
					progressbar.progressbar('value', Math.round(procent * num));
					if (num < parts) {
						processCreatePDF(index + 1);
					} else {
						$.ajax({
							url: $('input[name="url2getpdf"]').val(),
							data: 'post_data='+jsonPostData,
							type: 'post',
							dataType: 'json',
							success: function(json) {
								if (json['link']) {
									processEnd(json['link']);
								} else {
									processConcatePDF();
								}
							},
							error: function (xhr, ajaxOptions, thrownError) {
								alert(xhr.status);
								alert(thrownError);
							}
						});
					}
				} else if (json_data['error']) {
					alert(json_data['error']);
				} else {
					alert('Something went wrong');
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});
	}

	function processConcatePDF()
	{
		$.ajax({
			url: $('input[name="url2getpdf"]').val(),
			type: 'post',
			dataType: 'json',
			success: function(json) {
				processEnd(json['link']);
			}
		});
	}

	function processEnd(pdf_link)
	{
		$('.progress-label').text(txt_gencomplete);
		$('#progressbar').after('<div align="center"><a href="'+pdf_link+'" class="btn btn-primary" target="_blank">'+txt_download+'</a></div>');
		window.location = pdf_link; // make it to be downloadable
	}
});
