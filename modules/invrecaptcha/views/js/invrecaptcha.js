/**
 * Spam Protection - Google reCAPTCHA, hCaptcha & GDPR Captcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2026 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */

invReCaptcha = (self => self = {
	id: null,
	name: 'invrecaptcha_response',
	callback: 'ircOnLoadApi',
	apiLoad: null,
	hiddenX: 0,
	visibleX: 0,
	zIndex: 999999,
	$wrapper: $(),
	$badge: $(),
	$forms: $(),
	selector: {
		contact: ':submit[name=submitMessage]:not(#submitNewMessage)',
		newsletter: ':submit[name=submitNewsletter]',
		register: ':input[name=submitAccount], :input[name=submitGuestAccount], :input[name=submitCreate]',
		login: ':submit[name=SubmitLogin], :input[name=submitLogin]',
		resetpass: '#password input[name=email]',
		mailalert: '.js-mailalert-add, button[data-ps-action=emailalerts-subscribe]',
		review: '#post-product-comment-form :submit, #submitNewMessage:submit[name=submitMessage]',
		jmsBlogComment: 'form[action*="module=jmsblog"] :submit[name=submitComment]',
		ybcBlogComment: ':submit[name=bcsubmit]'
	},
	mailalertContainer: '.js-mailalert, .ps-emailalerts[data-url]',

	init(config) {
		$.extend(self, config);

		const mailalertIndex = self.forms.indexOf('mailalert');
		if (~mailalertIndex) {
			self.forms.splice(mailalertIndex, 1);
			$(self.mailalertContainer)
				.one('focus.irc', ':input', self.loadApi)
				.on('focus.irc', ':input', self.onFocusInput)
				.on('click.irc', self.onClickForm)
				.on('click.irc', self.selector.mailalert, self.onClickMailalertAdd)
			;
		}

		self.forms.forEach(form => {
			self.$forms = self.$forms.add($(self.selector[form]).prop('form'));
		});

		self.$forms.one('focus.irc', ':input', self.loadApi);

		self.$forms.each((i, form) => {
			var data = $._data(form);
			$(form).off('submit.irc').on('submit.irc', self.onSubmitForm);
			data.events.submit.unshift(data.events.submit.pop());
		});

		if (~self.forms.indexOf('register')) {
			'#account-creation' === location.hash && (location.hash = '');
			$(window).on('hashchange.irc', self.onHashChange);
		}

		$(document.body)
			.on('popupDidOpen.irc', '.cp-container', self.onPopupDidOpen)
			.on('popupWillClose.irc', self.hide)
		;
	},

	onPopupDidOpen() {
		var $form = $(this),
				formData = $form.data();
		if (formData.ircForm === undefined) {
			formData.ircForm = false;
			self.forms.some(form => {
				if ($form.find(self.selector[form]).length) {
					formData.ircForm = true;
					self.$forms = self.$forms.add(this);
					var data = $._data(this);
					$form.off('submit.irc').on('submit.irc', self.onSubmitForm);
					data.events.submit.unshift(data.events.submit.pop());
					self.loadApi().then(() => self.initForms($form));
					return true;
				}
			});
		}
	},

	loadApi() {
		if (self.apiLoad && self.apiLoad.state() !== 'rejected') {
			return self.apiLoad.promise();
		}
		self.apiLoad = $.Deferred().done(self.onLoadApi);

		window[self.callback] = () => self.apiLoad.resolve();

		var xhr = $.ajax({
			grecaptcha: `https://www.google.com/recaptcha/api.js?onload=${self.callback}&render=explicit&hl=${self.lang}`,
			hcaptcha: `https://js.hcaptcha.com/1/api.js?onload=${self.callback}&render=explicit&hl=${self.lang}&recaptchacompat=off`,
			altcha: (window.img_dir || prestashop.urls.img_url).split('/themes/')[0] + '/modules/invrecaptcha/views/lib/altcha.min.js?2.5.0'
		}[self.provider], {
			dataType: 'script',
			cache: true,
			error: () => {
				console.error('Failed to load CAPTCHA API.');
				self.apiLoad.reject();
			}
		});

		'altcha' === self.provider && xhr.done(() => {
			customElements.whenDefined('altcha-widget').then(window[self.callback]);
		});

		return self.apiLoad.promise();
	},

	reset() {
		if (!self.id || 'altcha' === self.provider) {
			return;
		}
		window[self.provider].reset(self.id);
		$(document.forms).find(`input[name="${self.name}"]`).val('');
		self.$badge = self.$wrapper.children();
	},

	hide() {
		if (self.$badge.hasClass('irc-hidden') || 'altcha' === self.provider) {
			return;
		}
		'hcaptcha' === self.provider
			? self.$badge.css('left' === self.pos ? 'right' : 'left', '102%')
			: self.$badge.css(self.pos, self.hiddenX);
		self.$badge.addClass('irc-hidden');
	},

	initForms($forms) {
		$forms.each((i, form) => {
			$(form)
				.off('focus.irc click.irc')
				.on('focus.irc', ':input', self.onFocusInput)
				.on('click.irc', self.onClickForm);
			form[self.name] || $(form).append('<input type="hidden" name="'+ self.name +'">');
		}).find(':submit').each((i, submit) => {
			var data = $._data(submit);
			$(submit).off('click.irc').on('click.irc', self.onClickSubmit);
			
			if (data.events && data.events.click) {
				data.events.click.unshift(data.events.click.pop());
			}
		});
	},

	onLoadApi() {
		self.$wrapper = $('<div class="irc-wrapper">').appendTo(document.body);

		if (self.provider === 'altcha') {
			var el = document.createElement('altcha-widget');
			el.setAttribute('challengeurl', self.challengeurl);
			el.setAttribute('auto', 'onload');
			el.style.display = 'none';
			self.$wrapper.append(el);
			el.addEventListener('statechange', self.onStateChangeAltcha);
			self.$badge = $(el);
		} else if (self.provider === 'hcaptcha') {
			var cdn = (window.img_dir || prestashop.urls.img_url).split('/themes/')[0];
			self.id = hcaptcha.render(self.$wrapper[0], {
				sitekey: self.sitekey,
				size: 'invisible',
				callback: self.onSuccess,
				'expired-callback': self.reset
			});
			self.$wrapper.append(`
<div id="hc-badge" class="hc-badge--${self.theme} hc-badge--${self.pos}">
	<img width="44" src="${cdn}/modules/invrecaptcha/views/img/h-captcha.svg" alt="hCaptcha logo">
	<div class="hc-text">
		<span>${self.i18n.protected}</span>
		<ul class="hc-links">
			<li><a href="https://hcaptcha.com/privacy" target="_blank">${self.i18n.privacy}</a></li>
			<li><a href="https://hcaptcha.com/terms" target="_blank">${self.i18n.terms}</a></li>
		</ul>
	</div>
</div>`);
			self.$badge = self.$wrapper.find('#hc-badge').on('click.irc', ev => ev.stopPropagation());
		} else { // greCAPTCHA
			self.id = grecaptcha.render(self.$wrapper[0], {
				sitekey: self.sitekey,
				theme: self.theme,
				badge: 'bottom' + self.pos,
				size: 'invisible',
				callback: self.onSuccess
			});
			self.$badge = self.$wrapper.children();
		}

		if (self.pos) {
			var opacity = self.$forms.find(document.activeElement).length || $(document.activeElement).closest(self.mailalertContainer).length;
			self.$wrapper.css('opacity', opacity ? 1 : 0);

			self.hiddenX = -self.$badge.outerWidth() - 5;
			self.visibleX = parseInt(self.$badge.css(self.pos)) || 0;
			self.$badge.one('transitionend webkitTransitionEnd oTransitionEnd', () => {
				self.$wrapper.css('opacity', '');
			});
			opacity && self.$badge.css({
				zIndex: self.zIndex,
				bottom: self.offset + 'px'
			}).length || self.hide();
		}

		self.initForms(self.$forms);

		$(document)
			.ajaxSend(self.onAjaxSend)
			.ajaxComplete(self.onAjaxComplete)
			.on('click.irc', self.onClickDoc)
		;
	},

	onStateChangeAltcha(ev) {
		if (ev.detail.state === 'verified') {
			self.altchaPayload = ev.detail.payload;
			// Altcha auto-verifies. Populate all forms.
			self.$forms.find(`input[name="${self.name}"]`).val(ev.detail.payload);
			// Only trigger submit if we are in a submit flow
			(self.form || self.mailalertAdd) && self.onSuccess(ev.detail.payload);
		}
	},

	onClickDoc(ev) {
		self.preventHide || self.hide();
		delete self.preventHide;
	},

	onClickForm(ev) {
		self.preventHide = true;
	},

	onClickSubmit(ev) {
		this.form[self.name].value || ev.stopImmediatePropagation();
	},

	onClickMailalertAdd(ev) {
		if (self.mailalertBypassed) {
			self.mailalertBypassed = false;
			return;
		}
		ev.preventDefault();
		ev.stopImmediatePropagation();

		self.mailalertAdd = this;
		self.loadApi().then(() => {
			if ('altcha' === self.provider) {
				self.altchaPayload && self.onSuccess(self.altchaPayload);
			} else
			if (window[self.provider]) {
				window[self.provider].execute(self.id);
			}
		});
	},

	onAjaxSend(ev, req, opts) {
		(opts.url +'&'+ new URLSearchParams(opts.data)).indexOf(self.name) < 0 || self.reset();
	},

	onAjaxComplete(ev, req, opts) {
		(opts.url +'&'+ new URLSearchParams(opts.data)).indexOf(self.name) < 0 || self.hide();
		delete self.form;
	},

	onHashChange() {
		// compatibility fix for PS 1.6 registration
		'#account-creation' === location.hash && $(':submit[name=submitAccount]').each((i, submit) => {
			self.$forms = self.$forms.add(submit.form).off('submit.irc').on('submit.irc', self.onSubmitForm);
			window[self.callback] ? self.initForms($(submit.form)) : self.loadApi();
		});
	},

	onFocusInput() {
		if ('altcha' === self.provider) {
			return;
		}
		if (self.$badge.hasClass('irc-hidden') && document.activeElement === this) {
			self.$badge.css({
				zIndex: self.zIndex,
				bottom: self.offset + 'px'
			}).removeClass('irc-hidden');
			'hcaptcha' === self.provider
				? self.$badge.css({left: '', right: ''})
				: self.$badge.css(self.pos, self.visibleX)
			;
		}
	},

	onSubmitForm(ev) {
		self.form = this;

		if (!self.form[self.name]) {
			ev.preventDefault();
			ev.stopImmediatePropagation();

			self.loadApi().then(self.onSubmitForm.bind(this, ev));
		} else if (!self.form[self.name].value) {
			ev.preventDefault();
			ev.stopImmediatePropagation();

			window[self.provider] && window[self.provider].execute(self.id);
		}
	},

	onSuccess(token) {
		if (self.mailalertAdd) {
			const $mailalert = $(self.mailalertAdd).closest(self.mailalertContainer);
			var url = $mailalert.data('url').split('&' + self.name)[0];
			$mailalert.data('url', url + '&' + self.name + '=' + token);
			$mailalert.attr('data-url', url + '&' + self.name + '=' + token);

			self.mailalertBypassed = true;
			self.mailalertAdd.click();
			delete self.mailalertAdd;
		} else if (self.form) {
			self.form[self.name].value = token;
			$(':submit', self.form)[0].click();
		}
	}
})();

jQuery($ => invReCaptcha.init(ircConfig));
