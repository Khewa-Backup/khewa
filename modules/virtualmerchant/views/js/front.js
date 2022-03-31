$(document).ready(function () {

	$("a#single_image").insertAfter(".cvc-container .cvc-wrapper div.icon");
	$("a#single_image").fancybox();

	$(".cart_navigation #paymentSubmit").click(function(e){
		e.preventDefault();
		$(this).attr('disabled', 'disabled');
		$(this).attr('readonly', 'true');

		var myCard = $('#vmcard');
		var cardNumber = myCard.CardJs('cardNumber');
		var cardNumberWithoutSpaces = CardJs.numbersOnlyString(cardNumber);

		$('#creditForm').append('<input type="hidden" name="card-number-nospaces" value="'+cardNumberWithoutSpaces+'" />');
		$('#creditForm').submit();
	});

});

