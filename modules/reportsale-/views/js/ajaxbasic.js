/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

var response_data;
var url_export;
var ba_prefix;
var ba_formdata;
var count_data;
var ba_url_replace;
var maximum_order_export = 5;
var set_time_out = 100;
var total_item  = 0;
var total_item_calc  = 0;

$(document).ready(function(){/*BASIC*/
	ba_url_replace=$('#url_report_hidden').html();
	ba_prefix=$('#hidden_prefix').val();
	ba_formdata='#'+ba_prefix+'form_data';
	var time = Date.now();
	$( "#"+ba_prefix+"save_filter" ).click(function() {
		$('#ba_load_hidden').addClass('ba_load_hidden');
		$('#ba_load_hidden').css("display","block");
		var arr_data=$( ba_formdata ).serialize();
		var url_getdata='../modules/reportsale/ajaxreportsale.php' + '?time=' + time;
		url_export='../modules/reportsale/ajaxexprot.php';
	    $.ajax({
	        url : url_getdata, 
	        type : "post",
	        crossDomain  : true,
	        data : arr_data,
	        datatype:'json',
	        success : function (result){
	        	response_data = $.parseJSON(result);
				total_item = response_data.length;
	        	caculateReport();
	        }
	    });
	});
});							
/*END BASIC*/
function closeReportLoading(){
	window.location.reload();
}
function caculateReport(){
	count_data=response_data.length;
	if (count_data==0) {
		setTimeout("closeReportLoading()",1000);
	}
	var str_send_ajax="";
	if (count_data > maximum_order_export) {

		for (var i = 0; i < maximum_order_export; i++) {
			var id;
			id = response_data.indexOf(response_data[0]);
			if(response_data[0] != undefined)
			{
				if (i == maximum_order_export-1) {
					str_send_ajax=str_send_ajax+response_data[0];
				} else {
					str_send_ajax=str_send_ajax+response_data[0]+',';
				}
			}
			if (id > -1) {
				response_data.splice(0,1);
			}	
		}
	}
	if (count_data <= maximum_order_export && count_data >0) {
		for (var i = 0; i <= (count_data); i++) {
			var id;
			id = response_data.indexOf(response_data[0]);
			if(response_data[0] != undefined)
			{
				if (i == (count_data - 1)) {
					str_send_ajax=str_send_ajax+response_data[0];
				} else {
					str_send_ajax=str_send_ajax+response_data[0]+',';
				}
			}
			if (id > -1) {
				response_data.splice(0,1);
			}
		}
	}
	
	if (str_send_ajax != "") {
			var time = Date.now();
	        $.ajax({
	        url : url_export + '?time=' + time, 
	        type : "post",
	        data : {
                ba_str_data : str_send_ajax,
                prefix_ba : ba_prefix,
            },
	        datatype:'text',
	        success : function (result){
	        	// calcalated item
				total_item_calc = total_item - count_data;
				$(".report_counter").text("("+total_item_calc+"/"+total_item+")");
				if (count_data>0) {
					setTimeout("caculateReport()",set_time_out);
				}
	        	status=true;
	        }
	    });
	}
	
}
/******************************/
$(document).ready(function(){
	var a=0;
	$("#report_click_opent_timeline").click(function(){
		$("#timeline_filtering").slideToggle("slow");
		if ((a%2)==0) {
			$( "#timeline_icon" ).removeClass("fa-plus-square").addClass("fa-minus-square");
			$("#report_click_opent_timeline").addClass("click_open");
		} else{
			$( "#timeline_icon" ).removeClass("fa-minus-square").addClass("fa-plus-square");
			$("#report_click_opent_timeline").removeClass("click_open");
		}
		a=a+1
	});
});

$(document).ready(function(){
	var a=0;
	$("#report_click_opent_status").click(function(){
		$("#status_filtering").slideToggle("slow");
		if ((a%2)==0) {
			$( "#status_icon" ).removeClass("fa-plus-square").addClass("fa-minus-square");
			$("#report_click_opent_status").addClass("click_open");
		} else{
			$( "#status_icon" ).removeClass("fa-minus-square").addClass("fa-plus-square");
			$("#report_click_opent_status").removeClass("click_open");
		}
		a=a+1
	});
});
$(document).ready(function(){
	var a=0;
	$("#report_click_opent_country").click(function(){
		$("#Country_filtering").slideToggle("slow");
		if ((a%2)==0) {
			$( "#contry_icon" ).removeClass("fa-plus-square").addClass("fa-minus-square");
			$("#report_click_opent_country").addClass("click_open");
		} else{
			$( "#contry_icon" ).removeClass("fa-minus-square").addClass("fa-plus-square");
			$("#report_click_opent_country").removeClass("click_open");
		}
		a=a+1
	});
});
$(document).ready(function(){
	var a=0;
	$("#report_click_opent_product").click(function(){
		$("#product_filtering").slideToggle("slow");
		if ((a%2)==0) {
			$( "#product_filtering_icon" ).removeClass("fa-plus-square").addClass("fa-minus-square");
			$("#report_click_opent_product").addClass("click_open");
		} else{
			$( "#product_filtering_icon" ).removeClass("fa-minus-square").addClass("fa-plus-square");
			$("#report_click_opent_product").removeClass("click_open");
		}
		a=a+1
	});
});
$(document).ready(function(){
	$("#checkall").change(function () {
		$(".checkbox_check").prop('checked', $(this).prop("checked"));
	});
	// custom date
	$(".reportsale_range").on("change ", function(){
		reportsale_chooseDate(this);
	});
});
function reportsale_chooseDate(ob) {
	var rev = $(ob).attr("rev");
	var range = parseInt($(ob).val());
	var d_from, d_to;
	var now = new Date();
	switch(range) {
		case 2:
			d_from = reportsale_formatDate(now);
			d_to = d_from;
			break;
		case 3:
			now.setDate(now.getDate() - 1);
			d_from = reportsale_formatDate(now);
			d_to = d_from;
			break;
		case 4:
			d_to = reportsale_formatDate(now);
			var diff = now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1);
			var startweek = new Date(now.setDate(diff));
			d_from = reportsale_formatDate(startweek);
			break;
		case 5:
			var beforeOneWeek = new Date(new Date().getTime() - 60 * 60 * 24 * 7 * 1000)
			  , day = beforeOneWeek.getDay()
			  , diffToMonday = beforeOneWeek.getDate() - day + (day === 0 ? -6 : 1)
			  , lastMonday = new Date(beforeOneWeek.setDate(diffToMonday))
			  , lastSunday = new Date(beforeOneWeek.setDate(diffToMonday + 6));
			d_from = reportsale_formatDate(lastMonday);
			d_to = reportsale_formatDate(lastSunday);
			break;
		case 6:
			var days7 = new Date(new Date().getTime() - 60 * 60 * 24 * 7 * 1000);
			var yesterday = new Date(new Date().getTime() - 60 * 60 * 24 * 1 * 1000);
			d_from = reportsale_formatDate(days7);
			d_to = reportsale_formatDate(yesterday);
			break;
		case 7:
			var days14 = new Date(new Date().getTime() - 60 * 60 * 24 * 14 * 1000);
			var yesterday = new Date(new Date().getTime() - 60 * 60 * 24 * 1 * 1000);
			d_from = reportsale_formatDate(days14);
			d_to = reportsale_formatDate(yesterday);
			break;
		case 8:
			var firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
			d_from = reportsale_formatDate(firstDay);
			d_to = reportsale_formatDate(now);
			break;
		case 9:
			now.setDate(0);
			d_to = reportsale_formatDate(now);
			now.setDate(1);
			d_from = reportsale_formatDate(now);
			break;
		case 10:
			var days30 = new Date(new Date().getTime() - 60 * 60 * 24 * 30 * 1000);
			var yesterday = new Date(new Date().getTime() - 60 * 60 * 24 * 1 * 1000);
			d_from = reportsale_formatDate(days30);
			d_to = reportsale_formatDate(yesterday);
			break;
		default: 
			d_from = '';
			d_to = '';
			break;
	}
	$("." + rev+" .from").val(d_from);
	$("." + rev+" .to").val(d_to);
}
function reportsale_formatDate(date) {
	var day, month, year;
	year = date.getFullYear();
	month = date.getMonth() + 1;
	month = month > 9 ? month : '0'+month;
	day = date.getDate() > 9 ? date.getDate(): '0'+date.getDate();
	return year + '-' + month + '-' + day;
}

function reportsale_alignSummaryTable()
{
	var x, y, col, parent_left, parent_right, w;
	if (jQuery(".summary_parent").length == 0) {
		return false;
	}
	var sidebar = jQuery("#nav-sidebar").width();
	if(_PS_VERSION_.indexOf('1.6.1.') >=0 || _PS_VERSION_.indexOf('1.7.3') >=0 || _PS_VERSION_.indexOf('1.7.0')>=0 || _PS_VERSION_.indexOf('1.7.1')>=0 || _PS_VERSION_.indexOf('1.7.2')>=0) {
		sidebar -= 17;
	}
	$(".summary_parent .summary_wrapper").css("left", sidebar + "px");
	parent_left = jQuery(".summary_parent").offset().left;
	var parent_width = jQuery("form.form-horizontal").width();
	var empty_element = 0;
	if(jQuery("form.form-horizontal table.table tbody td.list-empty").length == 0) {
		jQuery("form.form-horizontal table.table thead tr:first th").each(function(index){
			x = $(this).offset().left + 8 - 7;
			w = $(this).width();
			if (w == 0) {
				empty_element++;
			} else {
				col = index + 1 - empty_element;
				if (x < parent_left || x > (parent_left + parent_width - 15)) {
					$(".summary_col_"+col).hide();
				} else{
					$(".summary_col_"+col).show();
				}
				$(".summary_col_"+col).css("left", x + "px");
				$(".summary_col_"+col).css("width", w + "px");
			}
		});
	} else {
		jQuery(".summary_parent").remove();
	}
}
// since 1.0.20
function reportsale_hideModal(){
	$("#reportsale_bgoverlay").remove();
	$("#reportsale_modal").remove();
}
function reportsale_viewCustomerData(ob){
	var id_report = $(ob).attr("data-id-report");
	var token = $(ob).attr("data-ajax-token");
	    $.ajax({
	        url : 'index.php?controller=AdminReportSale&action=viewcustomer&token='+token, 
	        type : "post",
	        crossDomain  : true,
	        data : {'id_report': id_report},
	        datatype:'json',
	        success : function (result){
				if (result == "") {
					return false;
				}
	        	// remove old Modal
				reportsale_hideModal();
				// add new modal
				$("body").append('<div id="reportsale_bgoverlay"></div><div id="reportsale_modal"></div>');
				$("#reportsale_modal").html(result);
				// center modal
				$("#reportsale_modal").css({
					top: (($(window).height()  - $("#reportsale_modal").outerHeight()) / 2) - 50,
					left: ($(window).width() - $("#reportsale_modal").outerWidth()) / 2
				});
				// set close event
				$("#reportsale_bgoverlay").click(function(){
					reportsale_hideModal();
				});
	        }
	    });
}
function reportsale_searchCustomerData(){
	var keyword = $('#reportsale_searchmodal').val();
	if (keyword == ""){
		return false;
	}
	keyword = keyword.toLowerCase();
	var txt;
	var n;
	$("#reportsale_searchcontent li").each(function(){
		txt = $(this).find("a span.reportsale_name").text();
		n = txt.toLowerCase().indexOf(keyword);
		if (n === -1) {
			$(this).hide();
		} else {
			$(this).show();
		}
	});
}
function reportsale_resetCustomerData(){
	$('#reportsale_searchmodal').val('');
	$("#reportsale_searchcontent li").show();
}
function reportsale_viewOrderData(ob){
	var id_report = $(ob).attr("data-id-report");
	var token = $(ob).attr("data-ajax-token");
	    $.ajax({
	        url : 'index.php?controller=AdminReportSale&action=vieworder&token='+token, 
	        type : "post",
	        crossDomain  : true,
	        data : {'id_report': id_report},
	        datatype:'json',
	        success : function (result){
				if (result == "") {
					return false;
				}
	        	// remove old Modal
				reportsale_hideModal();
				// add new modal
				$("body").append('<div id="reportsale_bgoverlay"></div><div id="reportsale_modal"></div>');
				$("#reportsale_modal").html(result);
				// center modal
				$("#reportsale_modal").css({
					top: (($(window).height()  - $("#reportsale_modal").outerHeight()) / 2) - 50,
					left: ($(window).width() - $("#reportsale_modal").outerWidth()) / 2
				});
				// set close event
				$("#reportsale_bgoverlay").click(function(){
					reportsale_hideModal();
				});
	        }
	    });
}
