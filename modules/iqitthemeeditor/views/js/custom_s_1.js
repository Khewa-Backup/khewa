 /* Checkout account-creation clarity (2026-09-09) */
(function () {
    function initAccountNotice() {
        var form = document.getElementById('customer-form');
        if (!form) { return; }

        var pw = form.querySelector('input[name="password"]');
        if (pw && pw.getAttribute('autocomplete') !== 'new-password') {
            pw.setAttribute('autocomplete', 'new-password');
            if (pw.value) { pw.value = ''; }
        }

        if (!form.parentNode.querySelector('.js-account-required-notice')) {
            var notice = document.createElement('div');
            notice.className = 'js-account-required-notice';
            notice.textContent = 'Creating an account is required to complete your order — it only takes a minute.';
            form.parentNode.insertBefore(notice, form);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccountNotice);
    } else {
        initAccountNotice();
    }
})();

/* Checkout: surface social login next to manual signup (2026-09-09) */
(function () {
    function moveSocialLogin() {
        var form = document.getElementById('customer-form');
        if (!form) { return; }

        var socialWidget = form.querySelector('.ets_solo_social_wrapper.brp');
        var fieldsContainer = socialWidget ? socialWidget.parentNode : null;
        if (!socialWidget || !fieldsContainer) { return; }

        if (fieldsContainer.firstElementChild !== socialWidget) {
            fieldsContainer.insertBefore(socialWidget, fieldsContainer.firstElementChild);
        }
        if (!fieldsContainer.querySelector('.js-manual-signup-divider')) {
            var divider = document.createElement('p');
            divider.className = 'js-manual-signup-divider';
            divider.textContent = 'Or fill in your details manually:';
            socialWidget.parentNode.insertBefore(divider, socialWidget.nextSibling);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', moveSocialLogin);
    } else {
        moveSocialLogin();
    }
})();

/* Stripe updatedTransaction fix v4: restore real payment options + un-hide the Stripe row (2026-09-11) */
/* stripe_official 3.9.0 bug: on some renders the module hides .payment-options, hides its own
   "Pay online" row, and shows a stray legacy card form. We move the real options into a container
   the module does not control, hide the stray form, and make sure the Stripe row is visible.
   One-time relocation + bounded visibility polling: cannot loop or freeze the page. */
(function () {
    var moved = false;
    function showRows(holder) {
        var rows = holder.querySelectorAll('.payment-option');
        for (var i = 0; i < rows.length; i++) {
            if (getComputedStyle(rows[i]).display === 'none') {
                rows[i].style.setProperty('display', 'block', 'important');
            }
        }
    }
    function apply() {
        var step = document.getElementById('checkout-payment-step');
        if (!step) { return; }
        var legacy = step.querySelector('.sp-form.sp-one-line');
        if (!legacy) { return; }
        legacy.style.setProperty('display', 'none', 'important');
        var holder = document.getElementById('khewa-payment-options-restored');
        if (!moved) {
            var opts = step.querySelector('.payment-options');
            if (!opts || !opts.children.length || getComputedStyle(opts).display !== 'none') { return; }
            holder = document.createElement('div');
            holder.id = 'khewa-payment-options-restored';
            while (opts.firstChild) { holder.appendChild(opts.firstChild); }
            opts.parentNode.insertBefore(holder, opts.nextSibling);
            moved = true;
        }
        if (holder) { showRows(holder); }
    }
    function runBounded() {
        var ticks = 0;
        var t = setInterval(function () { ticks++; apply(); if (ticks > 40) { clearInterval(t); } }, 250);
    }
    function start() {
        runBounded();
        if (window.prestashop && typeof prestashop.on === 'function') {
            prestashop.on('changedCheckoutStep', function () { moved = false; runBounded(); });
            prestashop.on('updatedDeliveryForm', function () { moved = false; runBounded(); });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();