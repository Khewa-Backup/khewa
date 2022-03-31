/**
 * Spam Protection - Invisible reCaptcha
 *
 * @author    WebshopWorks
 * @copyright 2018-2019 WebshopWorks.com
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */

invReCaptcha = (function($, irc) {
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
			register: ':input[name=submitAccount],:input[name=submitCreate]',
			login: ':submit[name=SubmitLogin], :input[name=submitLogin]',
			resetpass: '#password input[name=email]',
			jmsBlogComment: 'form[action*="module=jmsblog"] :submit[name=submitComment]',
			ybcBlogComment: ':submit[name=bcsubmit]'
		},
		ajaxForm: {
			contact: 'action=add_comment',
			review: 'submitMessage',
			newsletter: 'submitNewsletter',
			register: ['submitAccount', 'submitCreate'].join('\\b|\\b'),
			login: 'submitLogin'
		},

		init: function(config) {
			$.extend(irc, config);
			$.each(irc.forms, function(i, form) {
				irc.$forms = irc.$forms.add($(irc.selector[form]).closest('form'));
			});
			irc.$forms.one('focus.irc', ':input', $.proxy(irc, 'loadApi'));

			if (~irc.forms.indexOf('register')) {
				location.hash == '#account-creation' && (location.hash = '');
				$(window).on('hashchange.irc', irc.onHashChange);
			}

			$(document.body)
				.on('popupDidOpen.irc', '.cp-container', irc.onPopupDidOpen)
				.on('popupWillClose.irc', irc.hide)
			;
		},

		onPopupDidOpen: function() {
			var $form = $(this),
					data = $form.data();
			if (data.ircForm === undefined) {
				data.ircForm = false;
				$.each(irc.forms, function(i, form) {
					if ($form.find(irc.selector[form]).length) {
						data.ircForm = true
						irc.$forms = irc.$forms.add($form);
						window[irc.callback] ? irc.initForms($form) : irc.loadApi();
						return false;
					}
				});
			}
		},

		loadApi: function() {
			window[irc.callback] = irc.onLoadApi;
			$.ajax('https://www.google.com/recaptcha/api.js?render=explicit&onload=' + irc.callback, {
				dataType: 'script',
				cache: true
			});
		},

		reset: function() {
			grecaptcha.reset(irc.id);
			$(document.forms).find('input[name="'+ irc.name +'"]').val('');
			irc.$badge = irc.$wrapper.children();
		},

		hide: function() {
			irc.$badge.hasClass('irc-hidden') || irc.$badge.css(irc.pos, irc.hiddenX).addClass('irc-hidden');
		},

		initForms: function($forms) {
			$forms.each(function() {
				var data = $._data(this);
				$(this)
					.off('focus.irc submit.irc click.irc')
					.on('focus.irc', ':input', irc.onFocusInput)
					.on('submit.irc', irc.onSubmitForm)
					.on('click.irc', irc.onClickForm)
				;
				data.events.submit.unshift(data.events.submit.pop());
				this[irc.name] || $(this).append('<input type="hidden" name="'+ irc.name +'">');
			}).find(':submit').each(function() {
				var data = $._data(this);
				$(this)
					.off('click.irc')
					.on('click.irc', irc.onClickSubmit)
				;
				data.events.click.unshift(data.events.click.pop());
			});
		},

		onLoadApi: function() {
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
			irc.$badge.one('transitionend webkitTransitionEnd oTransitionEnd', function() {
				irc.$wrapper.css('opacity', '');
			});
			opacity || irc.hide();
			irc.initForms(irc.$forms);

			$(document)
				.ajaxSend(irc.onAjaxSend)
				.ajaxComplete(irc.onAjaxComplete)
				.on('click.irc', irc.onClickDoc)
			;
		},

		onClickDoc: function(ev) {
			irc.preventHide || irc.hide();
			delete irc.preventHide;
		},

		onClickForm: function(ev) {
			irc.preventHide = true;
		},

		onClickSubmit: function(ev) {
			this.form[irc.name].value || ev.stopImmediatePropagation();
		},

		isAjaxMatch: function(data) {
			for (var i = 0, form; i < irc.forms.length; i++) {
				form = irc.forms[i];

				if (irc.ajaxForm[form] && new RegExp('\\b'+ irc.ajaxForm[form] +'\\b').test(data)) {
					return true;
				}
			}
		},

		onAjaxSend: function(ev, req, opts) {
			irc.isAjaxMatch(opts.url +'&'+ opts.data) && irc.reset();
		},

		onAjaxComplete: function(ev, req, opts) {
			irc.isAjaxMatch(opts.url +'&'+ opts.data) && irc.hide();
			delete irc.form;
		},

		onHashChange: function(ev) {
			// compatibility fix for PS 1.6 registration
			location.hash == '#account-creation' && $(':submit[name=submitAccount]').each(function() {
				irc.$forms = irc.$forms.add(this.form);
				window[irc.callback] ? irc.initForms($(this.form)) : irc.loadApi();
			});
		},

		onFocusInput: function(ev) {
			if (irc.$badge.hasClass('irc-hidden') && document.activeElement == this) {
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

		onSubmitForm: function(ev) {
			irc.form = this;

			if (!irc.form[irc.name].value) {
				ev.preventDefault();
				ev.stopImmediatePropagation();
				grecaptcha.execute(irc.id);
			}
		},

		onSuccess: function(token) {
			irc.form[irc.name].value = token;
			$(':submit', irc.form)[0].click();
		}
	};
})(jQuery);

jQuery(function() { invReCaptcha.init(ircConfig) });

