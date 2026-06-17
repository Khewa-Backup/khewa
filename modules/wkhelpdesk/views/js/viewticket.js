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
	var i =1;
	var isSubmitted = false;
	var msg;
	// if image selected
	$(document).on("click", "#hd_btn_other_attachment", function(e){
		e.preventDefault();
		var cover_img = $("#ticketAttachment").val();
		if (!cover_img) {
			$('#ticketAttachment').focus();
            $.growl.error({
                title: err,
                size: "large",
                message: prev_img
            });
			return false;
		} else {
			showOtherImage();
		}
	});

	$(document).on('submit', '#replyform', function(e){
        var error = 0;
        msg = $("#hd_message").val();
		if (!isSubmitted) {
			if (msg == '') {
                error = 1;
                $.growl.error({
                    title: err,
                    size: "large",
                    message: msgError
                });
			} else if ((captchaEnabled == "1") && (grecaptcha.getResponse() == "")) {
                error = 1;
                $.growl.error({
                    title: err,
                    size: "large",
                    message: noCaptchaError
                });
			}
            if (error == 1) {
                e.preventDefault();
            } else {
                isSubmitted = true;
            }
		} else {
			return false;
		}
	});

	$('input[type="file"]').on('change', function() {
		checkFileSize(this);
	});

	$(document).on('change', '.hd_other_file_attachment', function() {
	    checkFileSizeOther(this);
	});

	function checkFileSize(elem)
	{
		if (getTotalUploadSize() > maxSizeAllowed*1000000) {
            $('.bootstrap-filestyle > input[type="text"]').val('');
            $("#ticketAttachment").val('');
            return $.growl.error({
                title: err,
                size: "large",
                message: filesizeError
            });
		}
	}

	function checkFileSizeOther(elem)
	{
		if (getTotalUploadSize() > maxSizeAllowed*1000000) {
            $(elem).val('');
            return $.growl.error({
                title: err,
                size: "large",
                message: filesizeError
            });
		}
	}

	function getTotalUploadSize()
	{
		var size = 0;
		if ($('#ticketAttachment').length > 0) {
			if (typeof $('#ticketAttachment')[0].files[0] != 'undefined') {
				var s = parseInt($('#ticketAttachment')[0].files[0].size);
				if (isNaN(s) || (s < 0)) {
					s = 0;
				}
				size += s;
			}
		}
		if ($('.hd_other_file_attachment').length > 0) {
			$('.hd_other_file_attachment').each(function(idx, input) {
				if (typeof $(input)[0].files[0] != 'undefined') {
					var s = parseInt($(input)[0].files[0].size);
					if (isNaN(s) || (s < 0)) {
						s = 0;
					}
					size += s;
				}
			});
		}
		return size;
	}

	//code for showing other attachment upload link
	function showOtherImage()
	{
	    var newdiv = document.createElement('div');
	    newdiv.setAttribute("id", "childDiv" + i);
	    newdiv.setAttribute("class", "hdChildDivClass");
	    newdiv.innerHTML = "<div class='col-md-4'><input type='file' id='ticketOtherAttachment"+i+"' name='ticketOtherAttachment[]' class='hd_other_file_attachment'/></div><a class='hd_btn_other_remove btn btn-primary button button-small'><span>"+img_remove+"</span></a>";
	    var ni = document.getElementById('hd_other_files');
	    ni.appendChild(newdiv);
	    i++;
	}

	// Other image div remove event
	$(document).on("click", ".hd_btn_other_remove", function(){
		$(this).parent(".hdChildDivClass").remove();
	});
});