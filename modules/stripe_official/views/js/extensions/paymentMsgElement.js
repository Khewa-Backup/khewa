/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
let messagingElement;

class PaymentMsgElement {
  msgElement = null;
  stripe_currency = '';
  stripe_amount = 0;

  constructor(stripe_currency, stripe_amount) {
    this.stripe_currency = stripe_currency;
    this.stripe_amount = stripe_amount;
  }

  init() {
    const amount = this.getAmount();

    if (typeof Stripe == 'undefined' || amount == 0 || typeof stripe_pk == 'undefined') {
      return;
    }

    if (document.body.id == 'product') {
      this.initOnProductPage()
    } else {
      this.initOnCartPage();
    }

    this.msgElement = this.mountMessagingElement({
      amount: amount,
      currency: this.stripe_currency.toUpperCase()
    });

    this.watchQuantity();
    this.watchAttribute();
  }

  getAmount() {
    if (document.body.id == 'product') {
      const quantitySelector = document.querySelector('#quantity_wanted');
      if (quantitySelector) {
        let $quantity = quantitySelector ? quantitySelector.value : '0';
        return this.stripe_amount * parseInt($quantity);
      }
    } else if (document.body.id == 'cart') {
      return this.stripe_amount;
    }
    return 0;
  }

  initOnProductPage() {
    const quantitySelector = document.querySelector('#quantity_wanted');
    const messagingElement = document.getElementById('payment-method-messaging-element');
    if (quantitySelector && messagingElement) {
      const parent = quantitySelector.closest('.product-add-to-cart'); // get a safer container
      if (parent && messagingElement) {
        parent.insertBefore(messagingElement, parent.firstChild); // insert it at the top
      }
    }
  }

  initOnCartPage() {
    const checkoutBtn = document.querySelector('.checkout');
    const messagingElement = document.getElementById('payment-method-messaging-element');
    if (checkoutBtn && messagingElement) {
      const parent = checkoutBtn.closest('.checkout');
      if (parent && messagingElement) {
        parent.parentElement.insertBefore(messagingElement, parent);
      }
    }
    //messagingElement.style.marginBottom = '16px';  // space above checkout button
    //messagingElement.style.marginTop = '12px';     // space below cart totals
    messagingElement.classList.add("card-block");
  }

  mountMessagingElement(params) {
    let stripe = Stripe(stripe_pk)
    const appearance = {
      variables: {
        borderRadius: '0px',
      }
    }
    const messagingOptions = {
      amount: params.amount,
      currency: params.currency,
    };

    const options = {
      mode: 'payment',
      amount: params.amount,
      currency: params.currency.toLowerCase(),
      appearance,
    }
    let elements = stripe.elements(options);
    const paymentMessageElement = elements.create('paymentMethodMessaging', messagingOptions);
    paymentMessageElement.mount('#payment-method-messaging-element');

    return paymentMessageElement;
  }

  watchQuantity() {
    let vm = this;
    if (document.body.id == 'product') {
      const quantitySelector = document.querySelector('#quantity_wanted');
      if (quantitySelector) {
        $(quantitySelector).on('input change', function () {
          const amount = vm.getAmount();
          if (amount > 0) {
            vm.msgElement.update({amount: amount});
          } else {
            vm.msgElement.unmount();
          }
        });
      }
    } else if (document.body.id == 'cart') {
      if (typeof prestashop !== 'undefined' && typeof prestashop.on === 'function') {
        prestashop.on('updateCart', (args) => {
          if (!args.resp || !args.resp.cart) {
            return;
          }
          const amount = args.resp.cart.totals.total.amount * 100;
          if (amount > 0) {
            vm.msgElement.update({amount: amount});
          } else {
            vm.msgElement.unmount();
          }
        });
      }
    }
  }

  watchAttribute() {
    if (document.body.id !== 'product') return;

    if (window.prestashop && typeof window.prestashop.on === 'function') {
      prestashop.on('updatedProduct', () => {
        try {
          let vm = this;
          let quantitySelector = document.querySelector('#quantity_wanted');
          let $quantity = quantitySelector ? quantitySelector.value : '0';
          let amountProductStringElement = document.querySelector('.current-price span') || document.querySelector('.product__current-price');
          let productAmount = amountProductStringElement ? this.parseLocalizedAmountToNumber(amountProductStringElement.textContent) : 0;
          const amount = productAmount * parseInt($quantity);

          if (amount > 0) {
            vm.msgElement.update({amount: amount});
          } else {
            const container = document.getElementById('payment-method-messaging-element');
            if (container) container.style.display = 'none';
          }
        } catch (e) {
          console.error('watchAttribute interval error:', e);
        }
      });
    }
  }

  parseLocalizedAmountToNumber(raw) {
    let s = String(raw)
      .replace(/\u00A0/g, ' ')          // NBSP -> space
      .replace(/[^\d.,-]/g, '')         // keep digits and separators
      .trim();

    const lastComma = s.lastIndexOf(',');
    const lastDot = s.lastIndexOf('.');
    const decimalIsComma = lastComma > lastDot;

    // remove thousand separators
    if (decimalIsComma) {
      s = s.replace(/\./g, '');         // dot used as thousands in many locales
      s = s.replace(',', '.');          // comma -> dot decimal
    } else {
      s = s.replace(/,/g, '');          // comma thousands
    }

    const n = Number(s);
    return Number.isFinite(n) ? Math.round(n * 100) : 0;
  }
}

$(function () {
  if (stripe_express_amount && stripe_express_currency_iso) {
    const msgElement = new PaymentMsgElement(stripe_express_currency_iso, stripe_express_amount);
    msgElement.init();
  }
});
