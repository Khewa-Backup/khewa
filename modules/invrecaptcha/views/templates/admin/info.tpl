{**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2025 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 *}

<style>
#content .panel-footer { min-height: 98px; }
#content .grecaptcha-logo iframe,
#content .grecaptcha-badge {
	min-width: 302px;
	min-height: 76px;
	box-shadow: none !important;
}
#content button[name=submitInvReCaptcha] { margin-top: 10px; }
#content button[name=submitInvReCaptcha][disabled].btn i::before {
	content: "\25DC";
	position: absolute;
	width: 100%;
	margin: 0 -50%;
	animation:spin 2s linear infinite;
}
@keyframes spin { 100% { transform:rotate(360deg); } }
</style>
<script>
var form = document.getElementById('configuration_form');
form.removeAttribute('novalidate');

jQuery.ajax('https://www.google.com/recaptcha/api.js?onload=ircInit&render=explicit', {
	dataType: 'script',
	cache: true
});

function ircInit() {
	jQuery($ => {
		var $submit = $(':submit', form);
		var $wrapper = $();
		var loading, keyChanged;

		$([form.sitekey, form.secretkey]).on('input.irc', function onInputKey() {
			form.sitekey.setCustomValidity('');
			form.secretkey.setCustomValidity('');
			$wrapper.remove();
			$wrapper = $();
		}).on('change.irc', function onChangeKey() {
			this.value = this.value.trim();
			keyChanged = true;
		});

		$(form).on('submit.irc', function onSubmitForm(e) {
			$submit.attr('disabled', true);
			if (!keyChanged) return;
			e.preventDefault();
			$submit[0].blur();
			$wrapper.remove();
			$wrapper = $('<div class="irc-wrapper">').appendTo('#content .panel-footer');
			var id = grecaptcha.render($wrapper[0], {
				sitekey: form.sitekey.value,
				theme: $(form.theme).val(),
				badge: 'inline',
				size: 'invisible',
				callback: function onValidSiteKey(token) {
					clearTimeout(loading);
					$submit.attr('disabled', true);
					$(form.sitekey).trigger('input.irc');
					$.post(form.action, {
						checkSecretKey: form.secretkey.value,
						'inv-recaptcha-response': token
					}).always(function onValidateSecretKey(resp) {
						var res = JSON.parse(resp.split(")]}'\n")[1] || '{ "error-codes": ["unknown-error"] }');
						if (res.success) {
							$(form).off('submit.irc');
							form.version.value = res.score ? 3 : 2;
						} else if (~res['error-codes'].indexOf('invalid-input-secret')) {
							form.secretkey.setCustomValidity("{l s='Invalid secret key!' mod='invrecaptcha'}");
						} else {
							$submit[0].setCustomValidity("{l s='Captcha error:' mod='invrecaptcha'} " + res['error-codes'].join(', '));
							setTimeout(() => $submit[0].setCustomValidity(''), 3000);
						}
						$submit.removeAttr('disabled');
						$submit[0].click();
						res.success && $submit.attr('disabled', true);
					});
				}
			});
			$wrapper.find('iframe').one('load.irc', function onLoadIFrame() {
				form.sitekey.setCustomValidity("{l s='Invalid site key!' mod='invrecaptcha'}");
				grecaptcha.execute(id);
				loading = setTimeout(function onTimeoutLoading() {
					$submit.removeAttr('disabled');
				}, 1000);
			});
		});

		$(form.offset).addClass('form-control').attr('type', 'number');
	});
}
</script>
{l s='Please register your webshop on Google reCaptcha admin page to get your own Site key and Secret key:' mod='invrecaptcha'}
<a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a>
