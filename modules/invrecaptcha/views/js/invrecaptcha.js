/**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2025 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */

invReCaptcha = (function ($, irc) {
	return irc = {
		id: null,
		name: 'inv-recaptcha-response',
		callback: 'ircOnLoadApi',
		hiddenX: 0,
		visibleX: 0,
		zIndex: 999999,
		$wrapper: $(),
		$badge: $(),
		$forms: $(),
		selector: {
			contact: ':submit[name=submitMessage]:not(#submitNewMessage)',
			review: '#submitNewMessage:submit[name=submitMessage]',
			newsletter: ':submit[name=submitNewsletter]',
			register: ':input[name=submitAccount], :input[name=submitGuestAccount], :input[name=submitCreate]',
			login: ':submit[name=SubmitLogin], :input[name=submitLogin]',
			resetpass: '#password input[name=email]',
			jmsBlogComment: 'form[action*="module=jmsblog"] :submit[name=submitComment]',
			ybcBlogComment: ':submit[name=bcsubmit]'
		},
		ajaxForm: {
			contact: 'submitMessage',
			review: 'submitMessage',
			newsletter: 'submitNewsletter',
			register: ['submitAccount', 'submitCreate'].join('\\b|\\b'),
			login: 'submitLogin'
		},

		init: function (config) {
			$.extend(irc, config);
			irc.forms.forEach(form => {
				irc.$forms = irc.$forms.add($(irc.selector[form]).closest('form'));
			});

			irc.$forms.one('focus.irc', ':input', $.proxy(irc, 'loadApi'));
			irc.$forms.each((i, form) => {
				var data = $._data(form);
				$(form).off('submit.irc').on('submit.irc', irc.onSubmitForm);
				data.events.submit.unshift(data.events.submit.pop());
			});

			if (~irc.forms.indexOf('register')) {
				'#account-creation' === location.hash && (location.hash = '');
				$(window).on('hashchange.irc', irc.onHashChange);
			}

			$(document.body)
				.on('popupDidOpen.irc', '.cp-container', irc.onPopupDidOpen)
				.on('popupWillClose.irc', irc.hide)
			;
		},

		onPopupDidOpen: function () {
			var $form = $(this),
					formData = $form.data();
			if (formData.ircForm === undefined) {
				formData.ircForm = false;
				irc.forms.some(form => {
					if ($form.find(irc.selector[form]).length) {
						formData.ircForm = true;
						irc.$forms = irc.$forms.add(this);
						var data = $._data(this);
						$form.off('submit.irc').on('submit.irc', irc.onSubmitForm);
						data.events.submit.unshift(data.events.submit.pop());
						window[irc.callback] ? irc.initForms($form) : irc.loadApi();
						return true;
					}
				});
			}
		},

		loadApi: function () {
			window[irc.callback] = irc.onLoadApi;
			$.ajax('https://www.google.com/recaptcha/api.js?render=explicit&onload=' + irc.callback, {
				dataType: 'script',
				cache: true
			});
		},

		reset: function () {
			grecaptcha.reset(irc.id);
			$(document.forms).find('input[name="'+ irc.name +'"]').val('');
			irc.$badge = irc.$wrapper.children();
		},

		hide: function () {
			irc.$badge.hasClass('irc-hidden') || irc.$badge.css(irc.pos, irc.hiddenX).addClass('irc-hidden');
		},

		initForms: function ($forms) {
			$forms.each((i, form) => {
				$(form)
					.off('focus.irc click.irc')
					.on('focus.irc', ':input', irc.onFocusInput)
					.on('click.irc', irc.onClickForm);
				form[irc.name] || $(form).append('<input type="hidden" name="'+ irc.name +'">');
			}).find(':submit').each((i, submit) => {
				var data = $._data(submit);
				$(submit).off('click.irc').on('click.irc', irc.onClickSubmit);
				data.events.click.unshift(data.events.click.pop());
			});
		},

		onLoadApi: function () {
			var opacity = irc.$forms.find(document.activeElement).length;

			irc.$wrapper = $('<div class="irc-wrapper">')
				.css('opacity', opacity)
				.appendTo(document.body)
			;
			irc.id = grecaptcha.render(irc.$wrapper[0], {
				sitekey: irc.sitekey,
				theme: irc.theme,
				badge: 'bottom'+irc.pos,
				size: 'invisible',
				callback: irc.onSuccess
			});

			irc.$badge = irc.$wrapper.children();
			irc.hiddenX = -irc.$badge.outerWidth() - 5;
			irc.visibleX = parseInt(irc.$badge.css(irc.pos));
			irc.$badge.one('transitionend webkitTransitionEnd oTransitionEnd', () => {
				irc.$wrapper.css('opacity', '');
			});
			opacity && irc.$badge.css({
				zIndex: irc.zIndex,
				bottom: irc.offset + 'px'
			}).length || irc.hide();

			irc.initForms(irc.$forms);

			$(document)
				.ajaxSend(irc.onAjaxSend)
				.ajaxComplete(irc.onAjaxComplete)
				.on('click.irc', irc.onClickDoc)
			;
			$(irc).trigger('afterLoadApi');
		},

		onClickDoc: function (ev) {
			irc.preventHide || irc.hide();
			delete irc.preventHide;
		},

		onClickForm: function (ev) {
			irc.preventHide = true;
		},

		onClickSubmit: function (ev) {
			this.form[irc.name].value || ev.stopImmediatePropagation();
		},

		isAjaxMatch: function (data) {
			for (var i = 0, form; i < irc.forms.length; i++) {
				form = irc.forms[i];

				if (irc.ajaxForm[form] && new RegExp('\\b'+ irc.ajaxForm[form] +'\\b').test(data)) {
					return true;
				}
			}
		},

		onAjaxSend: function (ev, req, opts) {
			irc.isAjaxMatch(opts.url +'&'+ new URLSearchParams(opts.data)) && irc.reset();
		},

		onAjaxComplete: function (ev, req, opts) {
			irc.isAjaxMatch(opts.url +'&'+ new URLSearchParams(opts.data)) && irc.hide();
			delete irc.form;
		},

		onHashChange: function (ev) {
			// compatibility fix for PS 1.6 registration
			'#account-creation' === location.hash && $(':submit[name=submitAccount]').each((i, submit) => {
				irc.$forms = irc.$forms.add(submit.form).off('submit.irc').on('submit.irc', irc.onSubmitForm);
				window[irc.callback] ? irc.initForms($(submit.form)) : irc.loadApi();
			});
		},

		onFocusInput: function (ev) {
			if (irc.$badge.hasClass('irc-hidden') && document.activeElement === this) {
				irc.$badge
					.css({
						zIndex: irc.zIndex,
						bottom: irc.offset + 'px'
					})
					.css(irc.pos, irc.visibleX)
					.removeClass('irc-hidden')
				;
			}
		},

		onSubmitForm: function (ev) {
			irc.form = this;

			if (!irc.form[irc.name]) {
				ev.preventDefault();
				ev.stopImmediatePropagation();

				$(irc).on('afterLoadApi', irc.onSubmitForm.bind(this, ev));
			} else if (!irc.form[irc.name].value) {
				ev.preventDefault();
				ev.stopImmediatePropagation();

				grecaptcha.execute(irc.id);
			}
		},

		onSuccess: function (token) {
			irc.form[irc.name].value = token;
			$(':submit', irc.form)[0].click();
		}
	};
})(jQuery);

jQuery($ => invReCaptcha.init(ircConfig));
