{**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 *}
<style>
.irc-wrapper {
	position: relative;
	z-index: 1000;
}
#content .panel-footer { min-height: 98px; }
#content .grecaptcha-logo iframe,
#content .grecaptcha-badge {
	min-width: 302px;
	min-height: 76px;
	box-shadow: none !important;
}
#content .provider a {
	font-style: normal;
}
#content button[name=submitInvReCaptcha] { margin-top: 10px; }
#content button[name=submitInvReCaptcha][disabled].btn i { display: block; }
#content button[name=submitInvReCaptcha][disabled].btn i::before {
	content: "\25DC";
	position: absolute;
	width: 100%;
	margin: 0 -50%;
	animation: spin 2s linear infinite;
}
@keyframes spin {
	to { transform:rotate(360deg); }
}
</style>
<script>
const hl = document.body.className.match(/lang-(\w+)/)?.pop() || 'en';
const API = {
	grecaptcha: {
		url: 'https://www.google.com/recaptcha/api.js?render=explicit&onload=onLoadGCaptcha&hl=' + hl,
		load: null,
		keyChanged: false,
		sitekey: '',
		secretkey: '',
		widgetId: 0,
	},
	hcaptcha: {
		url: 'https://js.hcaptcha.com/1/api.js?render=explicit&onload=onLoadHCaptcha&recaptchacompat=off&hl=' + hl,
		load: null,
		keyChanged: false,
		sitekey: '',
		secretkey: '',
		widgetId: 0,
	},
	altcha: {
		url: '',
		load: $.Deferred().resolve(),
		keyChanged: false,
		sitekey: '',
		secretkey: '',
		widgetId: 0,
	},
};
const form = document.getElementById('irc-tab-settings');
form.removeAttribute('novalidate');
var $wrapper = $();

function onLoadGCaptcha() {
	API.grecaptcha.load.resolve();
}

function onLoadHCaptcha() {
	API.hcaptcha.load.resolve();
}

jQuery($ => {
	const $submit = $(':submit', form);
	const $gCaptchaConfigs = $('.g-captcha');
	const $hCaptchaConfigs = $('.h-captcha');
	const $aCaptchaConfigs = $('.a-captcha');

	var provider = $(form.provider).filter(':checked').val();
	// Store sitekey & secretkey initial values
	API[provider].sitekey = form.sitekey.value;
	API[provider].secretkey = form.secretkey.value;

	// Fix: textarea height isn't correct
	setTimeout(() => $('form textarea').css({
		height: '',
		resize: 'vertical',
	}).off('input'));

	function loadCaptchaApi(captcha) {
		const api = API[captcha];

		if (api.load && api.load.state() !== 'rejected') {
			return api.load.promise();
		}
		api.load = $.Deferred();

		const script = document.createElement('script');
		script.src = api.url;
		script.onerror = function() {
			console.error("Failed to load captcha API script for: " + captcha);
			api.load.reject({l|json_encode s='Failed to load CAPTCHA API script. Check network or adblockers.' mod='invrecaptcha'});
		};
		document.head.appendChild(script);

		return api.load.promise();
	}

	function renderWidget(captcha) {
		const api = API[captcha];
		if (captcha !== provider) {
			return;
		}
		$wrapper.remove();
		$wrapper = $('<div class="irc-wrapper">').appendTo(document.body);

		if (!form.sitekey.value.trim()) {
			return;
		}

		try {	
			api.widgetId = window[captcha].render($wrapper[0], {
				sitekey: form.sitekey.value,
				theme: form.theme.value,
				badge: 'bottom' + form.pos.value,
				size: 'invisible',
				callback: function onSuccessResponse(token) {
					$.post(location.href, {
						ajax: 'challenge',
						provider: captcha,
						sitekey: form.sitekey.value,
						secretkey: form.secretkey.value,
						invrecaptcha_response: token
					}).always(function onValidateProvider(resp) {
						var res = JSON.parse(resp.split(")]}'\n")[1] || '{}');
						if (res.success) {
							api.keyChanged = false;
							form.version.value = 'hcaptcha' === captcha ? 1 : (res.score ? 3 : 2);
							form.submit();
						} else {
							var errorCodes = (res['error-codes'] || ['unknown-error']).join(', ');
							if (errorCodes.includes('invalid-input-secret') || errorCodes.includes('invalid-secret-key')) {
								form.secretkey.setCustomValidity({l|json_encode s='Invalid secret key!' mod='invrecaptcha'} + " (" + errorCodes + ")");
							} else {
								form.sitekey.setCustomValidity({l|json_encode s='Captcha error:' mod='invrecaptcha'} + " " +  errorCodes);
							}
							$submit.removeAttr('disabled');
							form.reportValidity();
						}
					});
				},
				'error-callback': function onErrorCaptchaApi(error) {
					$submit.removeAttr('disabled');
					form.sitekey.setCustomValidity({l|json_encode s='Captcha error:' mod='invrecaptcha'} + ' ' + error);
					form.reportValidity();
				}
			});
			'hcaptcha' === captcha && $wrapper.append(`
				<div id="hc-badge" class="hc-badge--${ form.theme.value } hc-badge--${ form.pos.value }">
					<img width="44" src="{$smarty.const.__PS_BASE_URI__|escape:'html':'UTF-8'}modules/invrecaptcha/views/img/h-captcha.svg" alt="hCaptcha logo">
					<div class="hc-text">
						<span>${ i18n.protected }</span>
						<ul class="hc-links">
							<li><a href="https://hcaptcha.com/privacy" target="_blank">${ i18n.privacy }</a></li>
							<li><a href="https://hcaptcha.com/terms" target="_blank">${ i18n.terms }</a></li>
						</ul>
					</div>
				</div>
			`);
			$wrapper.children().css('bottom', parseInt(form.offset.value));
		} catch (error) {
			$submit.removeAttr('disabled');
			$submit[0].setCustomValidity({l|json_encode s='Captcha render failed:' mod='invrecaptcha'} + ' ' + error);
			form.reportValidity();
		}
	}

	$(form.provider).on('change.irc', function onChangeProvider() {
		provider = this.value;
		$gCaptchaConfigs.addClass('hide');
		$hCaptchaConfigs.addClass('hide');
		$aCaptchaConfigs.addClass('hide');
		$('.irc-wrapper').remove();

		if (provider === 'hcaptcha') {
			$hCaptchaConfigs.removeClass('hide');
			$('.grecaptcha-badge').remove();
		} else if (provider === 'altcha') {
			$aCaptchaConfigs.removeClass('hide');
			$('.grecaptcha-badge').remove();
		} else { // grecaptcha (default)
			$gCaptchaConfigs.removeClass('hide');
		}
		form.sitekey.value = API[provider].sitekey;
		form.sitekey.required = 'altcha' !== provider;
		form.sitekey.setCustomValidity('');
		form.secretkey.value = API[provider].secretkey;
		form.secretkey.required = true;
		form.secretkey.setCustomValidity('');

		const captcha = provider;
		loadCaptchaApi(provider).done(() => renderWidget(captcha)).fail((error) => {
			this.setCustomValidity(error || {l|json_encode s='Captcha API failed to load or timed out. Check network.' mod='invrecaptcha'});
			form.reportValidity();
		});
	}).filter(':checked').triggerHandler('change.irc');

	$([form.sitekey, form.secretkey]).on('input.irc', function onInputKey() {
		this.setCustomValidity('');
	}).on('change.irc', function onChangeKey() {
		const api = API[provider];
		api[this.id] = this.value = this.value.trim();
		api.keyChanged = true;

		'sitekey' === this.name && renderWidget(provider);
	});

	$(form.getkeys).on('click.irc', function onClickGetKeys() {
		window.open('hcaptcha' === provider
			? 'https://dashboard.hcaptcha.com/sites'
			: 'https://www.google.com/recaptcha/admin/create'
		);
	});

	$(form.generate).on('click.irc', function onClickGenerate() {
		form.secretkey.value = Array.from({ length: 40 }, () => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'[Math.random() * 62 | 0]).join('');
		$(form.secretkey).triggerHandler('change.irc');
	});

	$([form.theme, form.pos]).on('change.irc', function onChangeStyle() {
		loadCaptchaApi(provider).then(renderWidget.bind(null, provider));
	});

	$(form.offset).on('input.irc', function onInputOffset() {
		$wrapper.children().css('bottom', parseInt(this.value));
	}).addClass('form-control').attr('type', 'number');

	$(form).on('submit.irc', function onSubmitForm(e) {
		const captcha = provider;
		const api = API[captcha];

		if (!api.keyChanged && form.checkValidity()) {
			$submit.attr('disabled', true);
			return;
		}
		if (!form.checkValidity() || 'altcha' === captcha) {
			$submit.removeAttr('disabled');
			return;
		}

		e.preventDefault();
		$submit.attr('disabled', true).blur();

		loadCaptchaApi(captcha).then(() => {
			window[captcha].execute(api.widgetId);
		});
	});
});
</script>
{l s='Select the captcha service you want to use.' mod='invrecaptcha'}<br>
<a class="g-captcha hide" href="https://docs.webshopworks.com/spam-protection/119-getting-your-captcha-keys/494-how-to-get-google-recaptcha-v3-keys" target="_blank">
	<i class="icon-info-sign" aria-hidden="true"></i> {l s='How to get Google reCAPTCHA v3 Keys' mod='invrecaptcha'}
</a>
<a class="h-captcha hide" href="https://docs.webshopworks.com/spam-protection/119-getting-your-captcha-keys/495-how-to-get-hcaptcha-keys" target="_blank">
	<i class="icon-info-sign" aria-hidden="true"></i> {l s='How to get hCaptcha Keys' mod='invrecaptcha'}
</a>
<a class="a-captcha hide" href="https://docs.webshopworks.com/spam-protection/119-getting-your-captcha-keys/500-how-to-configure-altcha" target="_blank">
	<i class="icon-info-sign" aria-hidden="true"></i> {l s='How to configure ALTCHA' mod='invrecaptcha'}
</a>