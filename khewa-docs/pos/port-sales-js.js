// Applies Khewa's sales.js changes (from the live 4.2.7 file) to the readable copy of RockPOS 5.8.1 sales.js.
// Every anchor must match exactly once, otherwise the script stops without writing anything.
// Usage: node port-sales-js.js <in: readable 5.8.1> <out: readable 5.8.1 + Khewa>
const fs = require('fs');
const [, , inFile, outFile] = process.argv;
let s = fs.readFileSync(inFile, 'utf8');

function replaceOnce(label, from, to) {
  const count = s.split(from).length - 1;
  if (count !== 1) {
    console.error(`STOP: anchor "${label}" found ${count} times`);
    process.exit(1);
  }
  s = s.replace(from, () => to);
  console.log(`ok: ${label}`);
}

// 1. onClickPrintReceipt: keep the order for the receipt builder
replaceOnce('print receipt: window.rockposorder',
`            d = parseInt(o) > 0;
          Object(b.isEmpty)(this.state.logoBase64) || (Object(F.a)(c, l, this.state.logoBase64, f, p, Object(O.k)(h), a, d, t, e), s.print_automatically && s.close_popup_after_print && this.closePopup())`,
`            d = parseInt(o) > 0;

          // new change (Khewa)
          window.rockposorder = n.order;
          // end

          Object(b.isEmpty)(this.state.logoBase64) || (Object(F.a)(c, l, this.state.logoBase64, f, p, Object(O.k)(h), a, d, t, e), s.print_automatically && s.close_popup_after_print && this.closePopup())`);

// 2. receipt builder (5.8.1: function i(t, e, n, i, a, u); t = template, e = receipt data, i = lang, a = currency)
replaceOnce('receipt builder: Khewa helpers',
`      return null != t ? t.replace(new RegExp("<p>", "g"), "").replace(new RegExp("<br />", "g"), "").replace(new RegExp("</p>", "g"), "") : ""
    }
    var w = t.headerReceipt,
      S = t.footerReceipt,
      x = t.productReceipt,
      _ = t.summaryReceipt;`,
`      return null != t ? t.replace(new RegExp("<p>", "g"), "").replace(new RegExp("<br />", "g"), "").replace(new RegExp("</p>", "g"), "") : ""
    }

    // new changes (Khewa)
    // Insert 'subTtlAfterDiscount' before 'tax' if not present
    if (!t.summaryReceipt.includes('subTtlAfterDiscount')) {
      const taxIndex = t.summaryReceipt.indexOf('tax');
      if (taxIndex > -1) {
        t.summaryReceipt.splice(taxIndex, 0, 'subTtlAfterDiscount');
      } else {
        t.summaryReceipt.push('subTtlAfterDiscount');
      }
    }

    const moveItemBeforeTax = (itemName) => {
      const index = t.summaryReceipt.indexOf(itemName);
      if (index > -1) {
        const [item] = t.summaryReceipt.splice(index, 1);
        const taxIndex = t.summaryReceipt.indexOf('tax');
        if (taxIndex > -1) {
          t.summaryReceipt.splice(taxIndex, 0, item);
        }
      }
    };

    moveItemBeforeTax('totalOrderDiscount');
    moveItemBeforeTax('subTtlAfterDiscount');

    // RockPOS 5.8.1 passes the currency to this builder (a); used when the order has none.
    const khewaCurrency = window.rockposorder?.printData?.currency || a;
    // Tax-excl order discount for this receipt. Use the receipt payload first (it is
    // authoritative for the exact receipt being printed, on both live sale and re-print);
    // fall back to the live-checkout global only if the payload field is absent.
    const tDiscountWoTax = (e.totalOrderDiscountTaxExcl != null) ?
      e.totalOrderDiscountTaxExcl :
      window.rockposorder?.total?.discount;

    function khewaFormatNum(value, currency) {
      if (value == null || currency == null) return value;
      const decimals = currency.decimals ?? 2;
      const sign = currency.sign ?? "";
      return sign + Number(value).toFixed(decimals);
    }

    let subTtlWoTax = 0;
    if (Array.isArray(e.product)) {
      subTtlWoTax = e.product.reduce((sum, product) => {
        return sum + (parseFloat(product.totalPriceTaxExcl) || 0);
      }, 0);
    }
    // end

    var w = t.headerReceipt,
      S = t.footerReceipt,
      x = t.productReceipt,
      _ = t.summaryReceipt;`);

// 3. product lines: prices without tax (5.8.1 names: field u, lines r, lang i, lodash c.a)
replaceOnce('receipt product lines',
`                var u = t.trim();
                "unitPrice" != u && "originalPrice" != u && ("name" == u ? r.push(e[u].join("\\n ")) : c.a.isEmpty(e[u]) || r.push(e[u])), "unitPrice" === u && (n = e.return ? e.gift ? i.gift : "- ".concat(e.productUnitPrice) : e.exchange ? e.gift ? i.gift : "+ ".concat(e.productUnitPrice) : e.gift ? i.gift : "".concat(e.productUnitPrice)), "originalPrice" === u && e.productUnitPrice !== e.productOriginalPrice && (n = e.gift ? i.gift : n + "\\n(".concat(e.productOriginalPrice, ")")), e.return ? (a = "- ".concat(e.quantity), o = e.gift ? i.gift : "- ".concat(e.productTotalPrice)) : e.exchange ? (a = "+ ".concat(e.quantity), o = e.gift ? i.gift : "+ ".concat(e.productTotalPrice)) : (a = "".concat(e.quantity), o = e.gift ? i.gift : "".concat(e.productTotalPrice))`,
`                var u = t.trim();
                // Khewa: unit price and line total shown without tax
                if ("unitPrice" !== u && "originalPrice" !== u) {
                  if ("name" === u) {
                    r.push(e[u].join("\\n "));
                  } else if (!c.a.isEmpty(e[u])) {
                    r.push(e[u]);
                  }
                }

                if ("unitPrice" === u) {
                  if (e.return) {
                    n = e.gift ? i.gift : "- " + khewaFormatNum(e.productPriceTaxExcl, khewaCurrency);
                  } else if (e.exchange) {
                    n = e.gift ? i.gift : "+ " + khewaFormatNum(e.productPriceTaxExcl, khewaCurrency);
                  } else {
                    n = e.gift ? i.gift : "" + khewaFormatNum(e.productPriceTaxExcl, khewaCurrency);
                  }
                }

                if ("originalPrice" === u && e.productUnitPrice !== e.productOriginalPrice) {
                  n = e.gift ? i.gift : n + "\\n(" + e.productOriginalPrice + ")";
                }

                if (e.return) {
                  a = "- " + e.quantity;
                  o = e.gift ? i.gift : "- " + khewaFormatNum(e.totalPriceTaxExcl, khewaCurrency);
                } else if (e.exchange) {
                  a = "+ " + e.quantity;
                  o = e.gift ? i.gift : "+ " + khewaFormatNum(e.totalPriceTaxExcl, khewaCurrency);
                } else {
                  a = "" + e.quantity;
                  o = e.gift ? i.gift : "" + khewaFormatNum(e.totalPriceTaxExcl, khewaCurrency);
                }`);

// 4. summary: new "Subtotal After Discount" row + subtotal always tax excl.
replaceOnce('summary: subtotal',
`              switch (s) {
                case "subtotal":
                  t.push([{
                    colSpan: 3,
                    text: e.useTax ? "".concat(i.subTotal, " (").concat(i.inclTax, ") ") : "".concat(i.subTotal, " (").concat(i.exclTax, ") "),
                    alignment: "right"
                  }, "", "", {
                    text: e[s],
                    alignment: "right"
                  }]);
                  break;
                case "tax":`,
`              switch (s) {
                // Khewa
                case "subTtlAfterDiscount":
                  t.push([{
                    colSpan: 3,
                    text: 'Subtotal After Discount',
                    alignment: "right"
                  }, "", "", {
                    text: khewaFormatNum(subTtlWoTax - tDiscountWoTax, khewaCurrency),
                    alignment: "right"
                  }]);
                  break;
                case "subtotal":
                  t.push([{
                    colSpan: 3,
                    // Khewa: always "(tax excl.)"
                    text: /*e.useTax ? "".concat(i.subTotal, " (").concat(i.inclTax, ") ") : "".concat(i.subTotal, " (").concat(i.exclTax, ") ")*/ "".concat(i.subTotal, " (").concat(i.exclTax, ") "),
                    alignment: "right"
                  }, "", "", {
                    text: subTtlWoTax ? khewaFormatNum(subTtlWoTax, khewaCurrency) : e[s],
                    alignment: "right"
                  }]);
                  break;
                case "tax":`);

// 5. tax rows: recomputed for percentage discounts (asks the shop with getDiscountInfo)
replaceOnce('summary: tax rows',
`                case "tax":
                  d ? c.a.forEach(e.taxDetail, function(e, n) {
                    t.push([{
                      colSpan: 3,
                      text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                      alignment: "right"
                    }, "", "", {
                      text: "-" + Object(o.c)(e.taxAmount, a),
                      alignment: "right"
                    }])
                  }) : c.a.forEach(e.taxDetail, function(e, n) {
                    t.push([{
                      colSpan: 3,
                      text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                      alignment: "right"
                    }, "", "", {
                      text: Object(o.c)(e.taxAmount, a),
                      alignment: "right"
                    }])
                  });
                  break;`,
`                case "tax":
                  // Khewa: for percentage discounts the tax is recomputed on the discounted subtotal
                  if (e.idOrder || e.idCart) {
                    // synchronous request to get the order's discount information
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, false);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    let formData = 'ajax=1&action=getDiscountInfo';
                    if (e.idOrder) {
                      formData += '&id_order=' + e.idOrder;
                    }
                    if (e.idCart) {
                      formData += '&id_cart=' + e.idCart;
                    }
                    xhr.send(formData);

                    let isPercentageDiscount = false;
                    if (xhr.status === 200) {
                      try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success && response.data) {
                          isPercentageDiscount = response.data.is_percentage;
                        }
                      } catch (err) {
                        // keep the vendor tax amounts
                      }
                    }

                    d ? c.a.forEach(e.taxDetail, function(e, n) {
                      t.push([{
                        colSpan: 3,
                        text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                        alignment: "right"
                      }, "", "", {
                        text: "-" + Object(o.c)(e.taxAmount, a),
                        alignment: "right"
                      }])
                    }) : c.a.forEach(e.taxDetail, function(e, n) {
                      let taxAmount;
                      if (isPercentageDiscount) {
                        // percentage discount: tax on the discounted amount
                        const txrate = e.taxRate.toFixed(2) / 100;
                        taxAmount = txrate * (subTtlWoTax - tDiscountWoTax);
                      } else {
                        // fixed amount discount: the original tax amount
                        taxAmount = e.taxAmount;
                      }

                      t.push([{
                        colSpan: 3,
                        text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                        alignment: "right"
                      }, "", "", {
                        text: khewaFormatNum(taxAmount, khewaCurrency),
                        alignment: "right"
                      }])
                    });
                  } else {
                    // no order id: tax recomputed on the discounted subtotal
                    d ? c.a.forEach(e.taxDetail, function(e, n) {
                      t.push([{
                        colSpan: 3,
                        text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                        alignment: "right"
                      }, "", "", {
                        text: "-" + Object(o.c)(e.taxAmount, a),
                        alignment: "right"
                      }])
                    }) : c.a.forEach(e.taxDetail, function(e, n) {
                      const txrate = e.taxRate.toFixed(2) / 100;
                      const taxAmount = txrate * (subTtlWoTax - tDiscountWoTax);

                      t.push([{
                        colSpan: 3,
                        text: "".concat(i.tax, " ").concat(e.taxRate.toFixed(2), "% (").concat(i.products, ") "),
                        alignment: "right"
                      }, "", "", {
                        text: khewaFormatNum(taxAmount, khewaCurrency),
                        alignment: "right"
                      }])
                    });
                  }
                  break;`);

// 6. order discount shown without tax
replaceOnce('summary: order discount',
`                    text: i.totalOrderDiscount,
                    alignment: "right"
                  }, "", "", {
                    text: Object(o.b)(e.totalOrderDiscount, a, !0),
                    alignment: "right"
                  }]);`,
`                    text: i.totalOrderDiscount,
                    alignment: "right"
                  }, "", "", {
                    // Khewa: order discount shown without tax
                    text: tDiscountWoTax ? khewaFormatNum(tDiscountWoTax, khewaCurrency) : Object(o.b)(e.totalOrderDiscount, a, !0),
                    alignment: "right"
                  }]);`);

// 7. discount form: Voucher / Gift Card / Credit Slip types (identical code in 4.2.7 and 5.8.1)
replaceOnce('discount form types',
`            p = ["order-discount-value left", Object(x.isNull)(s) ? "" : s ? "valid" : "invalid"];
          return v.a.createElement("div", {
            className: "clearfix discount-form"`,
`            p = ["order-discount-value left", Object(x.isNull)(s) ? "" : s ? "valid" : "invalid"];
          // Khewa: gift card and credit slip discount types
          t.discountTypes[2] = {
            name: "Voucher",
            value: "voucher",
            isDefault: true
          };
          t.discountTypes[3] = {
            name: "Gift Card",
            value: "gift-card",
            isDefault: true
          };
          t.discountTypes[4] = {
            name: "Credit Slip",
            value: "credit-slip",
            isDefault: true
          };
          return v.a.createElement("div", {
            className: "clearfix discount-form"`);

fs.writeFileSync(outFile, s);
console.log('written');
