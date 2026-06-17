/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function(){
	hideRemoveButton();
    $("#ticketAttachment").on("change", function() {
        if ($("#ticketAttachment").val() != "") {
            $("#removeImage").attr("style", "display:block");
        } else {
            hideRemoveButton();
        }
    });
    $("#removeImage").click(function() {
        $('.bootstrap-filestyle > input[type="text"]').val('');
        $("#ticketAttachment").val('');
        hideRemoveButton();
    })
    // Customization #1012187
    $(document).on('click', '.wk_change_ticket_status', function(e) {
        e.preventDefault();
        var wk_checked = [];
        $('.wk-select-check').each(function() {
            if ($(this).is(':checked')) {
                wk_checked.push($(this).val())
            }
        });
        if (wk_checked.length <= 0) {
            $.growl.error({title: "", message:atleastoneprd});
        } else {
            var status = $(this).data('status');
			var idTicket = wk_checked;
			var idAgent = $("#idAgent").val();
			$("#ajax_loader_img").show();
			$("body").css('opacity', '0.5');
			$.ajax({
				type: "POST",
				url: wk_admin_url,
				data: {
					ajax:true,
					action: 'changeSelectedTicketStatus',
					idTicket:idTicket,
					status: status,
					idAgent: idAgent
				},
				dataType: "json",
				success: function(result) {
					if (result.status == 'success') {
						// window.location.href = all_ticket_link+"&updatewk_hd_ticket&id="+idTicket+"&conf=4";
						$("#ajax_loader_img").hide();
						$("body").css('opacity', '1');
                        $.growl.notice({title: "", message:result.msg});
                        setTimeout(() => {
                            location.reload()
                        }, 1000);
					} else {
                        $.growl.notice({title: "", message:result.msg});
                        setTimeout(() => {
                            $("#ajax_loader_img").hide();
                            $("body").css('opacity', '1');
                            $("#status_error_msg").text(result.msg);
                            $("#status_error_msg").show();
                        }, 100);
					}
				},
				error: function(){
					$("#ajax_loader_img").hide();
					$("body").css('opacity', '1');
					// $("#status_error_msg").text(status_error);
					$("#status_error_msg").show();
				}
			});
        }

    });
    $(document).on('click', '#wk-check-all', function() {
        if ($(this).is(':checked')) {
            $('.wk-select-check').prop( "checked", true)
        } else {
            $('.wk-select-check').prop( "checked", false)
        }
    });
    // Customization end #1012187
});

function hideRemoveButton() {
    $("#removeImage").attr("style", "display:none");
}