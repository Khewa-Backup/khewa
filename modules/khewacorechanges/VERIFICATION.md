# khewacorechanges — Verification

Method, per item: back up the file carrying the core change, revert that file to
stock (`/Applications/AMPPS/www/dev-prestashop/prestashop_1.7.8.7`) or to its
pre-change git state, clear cache, exercise the behaviour with the module
installed. Module carries it = accepted behaviour still holds. Restore backup.

Status legend: `TODO` / `PASS` / `FAIL`.

---

## 2 — classes/CartRule.php (fixed-amount discount tax)

**Accepted:** cart rule with `reduction_tax = 0` (discount entered tax-excluded)
and an amount-type reduction — the discount is subtracted from the
**tax-included** cart total as-is, so the order total drops by exactly the
voucher's face value.

**Rejected:** the amount is multiplied by the average cart VAT rate
(`reduction_amount * (1 + vat_rate)`), so a $10 voucher removes ~$11.50 and the
invoice total is short.

**Core change:** `classes/CartRule.php::getContextualValue()`, the
`$this->reduction_product == 0` branch (~line 1395). Added a leading
`if (!$this->reduction_tax && $use_tax)` case: percentage reductions use
`$cart_amount_te`, amount reductions use `$cart_amount_ti`, both capped by
`min()`. All stock per-product / average-VAT-rate math moved into the `else`.

**Module replacement:** `override/classes/CartRule.php` — a PrestaShop-native
class override installed into root `override/classes/CartRule.php` on install.
Same method, same logic; core file needs no edit.

**Test (automated, CLI):** bootstrap PS, build a cart (customer 2, address 50 —
QC, product 240 ×2 = 112.00 tax-excl / 128.78 tax-incl, effective 14.982%),
attach an amount cart rule with `reduction_amount = 10`, `reduction_tax = 0`,
and read `getContextualValue(true, ...)`. Accepted = 10.00, rejected = 11.50.

**Result:**

| core file | override | getContextualValue(TI) | |
|---|---|---|---|
| khewa (as shipped) | present | 10.00 | accepted |
| **stock 1.7.8.7** | **removed** | **11.50** | rejected — proves the test discriminates |
| **stock 1.7.8.7** | **present** | **10.00** | **accepted — module carries it** |

Core file restored afterwards; test cart + cart rule deleted, no residue.

**Status:** PASS (2026-09-21)

---

## 3 — src/Core/Grid/Query/OrderQueryBuilder.php (Orders grid "New customer")

**Accepted:** Orders grid loads quickly with the New-customer column correct,
and filtering on New customer = Yes/No returns the right rows.

**Rejected:** grid takes tens of seconds / times out on the full orders table
(stock runs a correlated subselect per row), or the New-customer column and
filter disagree.

**Core change:** core file edited directly. Stock `getNewCustomerSubSelect()`
correlated subselect replaced by a derived table of `MIN(id_order)` per customer
(`fo`) LEFT JOINed in `getBaseQueryBuilder()`; `addNewCustomerField()` and
`applyNewCustomerFilter()` compare `fo.first_order = o.id_order`. Stock methods
left as dead `__`-prefixed code. Debug leftovers (`ini_set('display_errors')`,
commented `dump()` lines) also present.

**Module replacement:** `config/services.yml` re-declares the core service id
`prestashop.core.grid.query_builder.order` → `KhewaCoreChanges\Grid\Query\KhewaOrderQueryBuilder`
(`src/Grid/Query/`), same constructor arguments. Module services.yml loads after
core, so the id is replaced; core class is never called.

**Test:** revert the core file to stock, clear cache,
`bin/console debug:container prestashop.core.grid.query_builder.order` must
still report the Khewa class; then load the Orders grid and filter on New
customer.

**Status:** TODO

---

## 4 — override/controllers/front/ProductController.php (duplicate link-rewrite)

**Accepted:** a product URL whose numeric id and `link_rewrite` slug disagree
(products 12705 vs 750 collide on slug) loads the product matching the **numeric
id in the URL**.

**Rejected:** the slug wins and the wrong product page renders.

**Core change:** not a core-file edit — a hand-written full override of
`ProductController::init()` dropped into root `override/controllers/front/`
(commit `f49bb3ffb`). It regex-parses `$_SERVER['REQUEST_URI']` for the numeric
id + slug, queries `ps_product_lang`, and forces `$_POST['id_product']` to the
URL's numeric id when it disagrees with the slug-resolved id.

**Module replacement:** the same override now ships inside the module
(`modules/khewacorechanges/override/controllers/front/ProductController.php`).
`install()` backs up the hand-made root copy to `backup/`, deletes it, rebuilds
the class index, and PrestaShop regenerates root `override/` from the module
copy. The current root file carries the `module: khewacorechanges` banner, so
it is already module-generated.

**Test (automated, HTTP):** products 12705/750 no longer collide (750 is gone),
but the slug `ladies-mukluks` is shared by 25 products — a plain slug lookup
resolves to 1108. Fetched 6 real URLs over HTTP (3 canonical with `-ean13`
suffix, 3 in the colliding `<id>-ladies-mukluks.html` form) and read back the
resolved id from the canonical link / "page has moved" redirect.

**Result:**

| controller in place | init() actually running | all 6 URLs |
|---|---|---|
| module override | `override/.../ProductController.php` | resolve to URL's numeric id ✓ |
| **original hand-made** (`f49bb3ffb`) | `override/.../ProductController.php` | resolve to URL's numeric id ✓ |
| stock (override removed) | `ProductControllerCore` | resolve to URL's numeric id ✓ |

**Decisive check — the module copy and the original hand-made override are the
same code.** PHP-token hash (comments/whitespace stripped) of
`git show f49bb3ffb:override/controllers/front/ProductController.php` and the
installed override are both `72d0811099…`. The module carries the original
change byte-for-byte in logic; nothing was lost in the move.

Stock also passes these URLs — PS 1.7.8.7 already trusts the numeric id in
`{id}-{rewrite}.html` and self-corrects via canonical redirect — so the original
bug (products 12705/750, 750 now deleted) is no longer reproducible. That makes
the override unprovable-by-behaviour but also provably *equivalent to what it
replaced*, which is what this exercise asks.

**Status:** PASS (2026-09-21) — module override is code-identical to the original
core change; behaviour identical across all three configurations

---

## 5 — pdf/footer.tpl (Quebec tax info)

**Accepted:** every generated PDF footer shows `TPS 143581395RT0001`,
`TVQ 1023112902TQ0001`, and the bilingual exchange/thank-you lines.

**Rejected:** footer shows only the stock free-text block — no registration
numbers, no exchange policy.

**Core change:** additive block appended to `pdf/footer.tpl` after the
`$free_text` `{if}` (6 lines). Nothing stock removed.

**Module replacement:** `override/classes/pdf/HTMLTemplate.php` overrides
`getTemplate()` to look in `modules/khewacorechanges/pdf/` **before** the theme
and core `pdf/` folders; the customized `footer.tpl` lives there. A theme copy
(`files/theme/pdf/footer.tpl`) is also deployed as fallback.

**Test (automated, CLI):** render invoice 33538 via `HTMLTemplateInvoice`,
report which file `getTemplate('footer')` resolves to, and grep the rendered
footer HTML for the TPS number, TVQ number and both exchange lines.

**Result:**

| core `pdf/footer.tpl` | theme copy | override | getTemplate('footer') → | markers |
|---|---|---|---|---|
| khewa | present | present | `modules/khewacorechanges/pdf/footer.tpl` | all 4 ✓ accepted |
| **stock** | **removed** | **present** | **`modules/khewacorechanges/pdf/footer.tpl`** | **all 4 ✓ accepted** |
| stock | removed | **removed** | `pdf/footer.tpl` (core) | all 4 missing — rejected |

Row 2 is the verification: with core reverted to stock 1.7.8.7 and the theme
copy deleted, the module alone still supplies the customized footer. Row 3
proves the test discriminates. All three files restored afterwards.

**Status:** PASS (2026-09-21)

---

## 6 — Customer Service thread view, order total hidden

**Accepted:** in Customer Service → thread view, the "N order(s) validated for a
total amount of ___" badge shows no amount.

**Rejected:** the customer's total order amount is printed in the badge.

**Core change:** `admin556cvt7923/themes/default/template/controllers/customer_threads/helpers/view/view.tpl:74`
— the `%total%` sprintf value hard-changed from `$total_ok` to a literal `' '`.

**Module replacement:** managed file `admin_customer_thread_view` — the same
template is deployed by the module to
`override/controllers/admin/templates/customer_threads/helpers/view/view.tpl`,
the admin template override path that `Helper::createTemplate()` honours before
the admin theme. Redeployable from the module's config page.

**Test (automated, CLI):** replicate `AdminController`'s two Smarty template
dirs (`AdminController.php:471` — admin theme, then
`override/controllers/admin/templates`) and `Helper::createTemplate()`'s
resolution, report which file wins, and read its `%total%` sprintf value.
`PS_DISABLE_OVERRIDES` is false, so dir[1] is honoured.

**Result:**

| admin theme `view.tpl` | module copy in `override/` | template chosen | `%total%` |
|---|---|---|---|
| khewa (blanked) | present | `override/controllers/admin/.../view.tpl` | `' '` ✓ accepted |
| **stock (`$total_ok`)** | **present** | **`override/controllers/admin/.../view.tpl`** | **`' '` ✓ accepted** |
| stock (`$total_ok`) | **removed** | admin theme copy | `$total_ok` — rejected |

Row 2 is the verification: with the admin theme restored to stock 1.7.8.7
(amount printing again), the module's override-path copy still wins and blanks
it. Row 3 proves the test discriminates. Both files restored afterwards.

**Status:** PASS (2026-09-21)

---

## 7 / 8 — "Total spent" badge hidden (Customers list, Orders-list customer link)

**Accepted:** the Customers grid shows no green "total spent" badge.

**Rejected:** the badge renders with the customer's lifetime total.

**Core change:** `admin556cvt7923/themes/new-theme/public/theme.css:550` — added
`.column-total_spent .badge-success{ display: none; }`. Any admin-theme rebuild
overwrites it.

**Module replacement:** `hookDisplayBackOfficeHeader` injects
`<style id="khewacorechanges-bo">.column-total_spent .badge-success{display:none;}</style>`
on every back-office page — no admin theme file involved.

**Test (automated, CLI):** strip the rule out of the admin `theme.css`, then ask
PrestaShop's real dispatcher (`Hook::getHookModuleExecList('displayBackOfficeHeader')`)
whether the module is invoked, and `Hook::callHookOn()` what it emits.
Note: `displayBackOfficeHeader` is explicitly **excluded** from the hook
registration cache (`Hook::getAllHookRegistrations()`), so the list is queried
live — no cache to clear.

**Result:**

| `theme.css` rule | module on hook | badge hidden by | |
|---|---|---|---|
| present | registered | theme.css + hook | accepted |
| **removed** | **registered** | **module hook alone** | **accepted** |
| removed | **unregistered** (`ps_hook_module` rows deleted) | nothing | rejected |

Row 2 is the verification: with the original core change deleted from
`theme.css`, the module alone still hides the badge. Dispatcher confirms
`khewacorechanges` (id_module 230) is in the exec list among 33 modules, and
emits `<style id="khewacorechanges-bo">.column-total_spent .badge-success{display:none;}</style>`.
Row 3 proves the test discriminates.

`theme.css` and the three `ps_hook_module` rows (shops 1/2/3, position 24) were
restored afterwards; `ps_module_shop` back to `230 | 1 | 7`.

**Status:** PASS (2026-09-21)

---

## 9 — order_conf pickup message

**Accepted:** order confirmation email (EN and QC) contains "Please note that
for pickup orders we will contact you when your order is ready." / the French
equivalent, in both `.html` and `.txt`.

**Rejected:** stock order_conf goes out with no pickup sentence.

**Core change:** the sentence was typed directly into `mails/en/order_conf.html`
(line 281), `mails/en/order_conf.txt` (line 6) and the `qc` pair. FR was missed.

**Module replacement:** `hookActionEmailSendBefore` rewrites `templatePath` to
`modules/khewacorechanges/mails/` when the template is `order_conf` **and** the
path is the default `_PS_MAIL_DIR_` (so another module's mail is never hijacked).
`Mail::getTemplateBasePath()` then resolves `modules/khewacorechanges/mails/<iso>/`.
The module ships EN, FR (sentence added — it was missing live) and QC.

**Test (automated, CLI):** strip every line containing the pickup sentence out
of the four site mail files (`mails/{en,qc}/order_conf.{html,txt}`), then run
`Mail::send()`'s by-reference `actionEmailSendBefore` call (`Mail.php:155`
passes `templatePath` by reference) and resolve the template it would use.

**Result:**

| site `mails/<iso>/order_conf.*` | hook registered | template resolved to | sentence |
|---|---|---|---|
| khewa (has sentence) | yes | `modules/khewacorechanges/mails/en/…` | present ✓ |
| **stripped** | **yes** | **`modules/khewacorechanges/mails/en/…`** | **present ✓** |
| stripped | **no** (`ps_hook_module` rows deleted) | `mails/en/order_conf.html` | missing — rejected |

Row 2 is the verification: with the original core change deleted from all four
site files, the module still serves the template and the rendered body contains
*"Please note that for pickup orders we will contact you when your order is
ready."* Row 3 proves the test discriminates.

Verified for **en** and **qc**; `fr` is not installed on this shop, so the
module's added FR copy could not be exercised.

Restored afterwards: 4 mail files, 3 `ps_hook_module` rows (hook 140), and a
stray shop-scoped `PS_MAIL_METHOD` row the harness created (deleted; global
value back to 2/SMTP).

**Status:** PASS (2026-09-21)

---

## 10 — Invoice PDF discount tabs

**Accepted:** an invoice for an order with a discount shows Total Products →
Shipping → Total Discounts → Total (Tax Excl.) → Total Tax (from
`footer.total_taxes`) → Total, and the tax-rate column is always present in the
product table.

**Rejected:** stock layout — discount handling that misreports tax on
fixed-amount / gift-card discounts, and the tax-rate column suppressed when
`$isTaxEnabled` is false.

**Core change:** `pdf/invoice.total-tab.tpl` rewritten into a two-branch layout
keyed on `product_discounts_tax_excl > 0`; `pdf/invoice.product-tab.tpl` always
renders the tax-rate column plus alignment/customization-label changes.
(`invoice.style-tab.tpl` untouched.)

**Module replacement:** same `HTMLTemplate::getTemplate()` override as #5 —
both templates served from `modules/khewacorechanges/pdf/`. Theme copies
deployed as fallback.

**Test (automated, CLI):** render invoice 33533 (order 33578, $41.00 discount,
free shipping) through the real `HTMLTemplateInvoice::getContent()` with the
Symfony kernel booted, then assert on the **row order** in the totals block —
khewa prints Shipping *before* Total Discounts, stock prints Discounts first.
That ordering is the reliable discriminator; the row labels themselves appear
in both templates.

**Result:**

| core tpls | theme copies | override | tabs served from | totals row order | size |
|---|---|---|---|---|---|
| khewa | present | present | module | Shipping → Discounts (khewa) | 8885 B |
| **stock** | **removed** | **present** | **module** | **Shipping → Discounts (khewa)** | **8885 B** |
| stock | removed | **removed** | core `pdf/` | Discounts → Shipping (stock) | 9257 B |

Row 2 is the verification: both core tpls reverted to stock 1.7.8.7 and both
theme copies deleted, the module alone still serves the customized tabs —
byte-identical render (8885 B) to the as-shipped state. Row 3 proves the test
discriminates (different ordering, 9257 B).

Rendered totals confirm correct arithmetic on a discounted order:
Total Products $164.00 · Shipping Free · Total Discounts −$41.00 ·
Total (Tax Excl.) $123.00 · Total Tax $18.42 · Total $141.42.
Tax-rate column present in the product table (stock suppresses it behind
`$isTaxEnabled`, which the khewa copy drops — 6 guards in stock, 0 in khewa).

All four files restored afterwards.

**Status:** PASS (2026-09-21)

---

## 11a — Core "Product out of stock" employee mail

**Accepted:** no "Product out of stock" email reaches employees when stock hits
zero, regardless of whether a mail-alert module is installed.

**Rejected:** every employee is emailed by core on each out-of-stock event.

**Core change:** `src/Core/Stock/StockManager.php` (~lines 319-333) — the
`Mail::Send(... 'productoutofstock' ...)` call commented out in place.

**Module replacement:** `hookActionEmailSendBefore` returns `false` (aborting
`Mail::send`) when `template === 'productoutofstock'` **and** `templatePath`
contains `src/Core/Stock` — only the core sender is blocked; ps_emailalerts'
own stock alerts still work.

**Test (automated, CLI):** replicate `Mail::send()`'s decision exactly — run
`Hook::exec('actionEmailSendBefore', …, $array_return = true)` (`Mail.php:155`)
and apply the `array_reduce` at `Mail.php:179`. A `false` from any module sets
`keepGoing = false` and the send is aborted.

**Result:**

| root `override/classes/Hook.php` (ets_superspeed) | core StockManager mail | ps_emailalerts stock mail | unrelated core mail |
|---|---|---|---|
| present (**live state**) | **WOULD SEND — guard defeated** | would send | would send |
| removed | **BLOCKED ✓** | would send ✓ | would send ✓ |

**The module's guard is correct but is being defeated in production.**
`$module->hookActionEmailSendBefore()` returns `false` as designed. But the
**ets_superspeed** module installs a root override of `Hook::callHookOn()`
(`override/classes/Hook.php:106-131`, banner `module: ets_superspeed`,
2026-01-17) which ends with:

```php
$html = '';
$html .= $content;   // false becomes ''
return $html;
```

so `false` is cast to `''` before it reaches `Mail::send`'s `array_reduce`.
`keepGoing` therefore stays `true` and the mail is sent. Verified directly:
`Hook::exec(...)` returns `['khewacorechanges' => '']` (string), not `false`.

The guard only returns `false` intact when `!Module::isEnabled('ets_superspeed')`
short-circuits that block (line 118) — i.e. when ets_superspeed is disabled.

**Why it isn't currently breaking the shop:** core
`src/Core/Stock/StockManager.php` still has the original `Mail::Send` call
commented out (lines ~319-333), so no mail is generated in the first place. The
module's hook is a no-op safety net that would *not* hold if core were restored
by an upgrade — which is exactly the scenario this module exists for.

**Same defect affects #16** (`new_order` POS-sale skip), which uses the same
`return false` mechanism through the same hook.

**Status:** FAIL (2026-09-21) — module guard is ineffective while
ets_superspeed's `Hook::callHookOn` override is installed. Core's commented-out
`Mail::Send` is currently doing the work; the update-proofing is not.

---

## 11b — Customer-service reply `{link}` variable

**Accepted:** the order-message reply notification email contains a `{link}`
pointing to the contact page with `id_customer_thread` + `token`; when
`wkhelpdesk` is enabled the thread reply instead links to the helpdesk ticket
view.

**Rejected:** `{link}` is unresolved/absent, so the customer has no direct way
back into the thread.

**Core change:** two `src/Adapter/CustomerService/CommandHandler/` files edited
directly — `AddOrderCustomerMessageHandler.php` ("Customization By Ram Chandra":
passes `$customerServiceThreadId` into `sendMail()`, builds `{link}` from
`CustomerThread`), and `ReplyToCustomerThreadHandler.php` (2024-07-12: if
`wkhelpdesk` is enabled, `{link}` → `WkHdTicketManager` ticket view).

**Module replacement:** `config/services.yml` re-declares both handler service
ids with the same arguments and `tactician.handler` tags, pointing at the
module's copies in `src/CustomerService/CommandHandler/`. The command bus
dispatches to the module classes; core files are never loaded.

**Test:** revert both core files to stock, clear cache, send a reply from
Customer Service and from an order's message box, confirm `{link}` is still
resolved in the outgoing mail.

**Status:** TODO

---

## 14 — Specific References block removed from product page

**Accepted:** the product page shows no Ean13 / Isbn / Upc "specific references"
table.

**Rejected:** the references table renders under product details.

**Core change:** `themes/warehouse/templates/catalog/_partials/product-details.tpl`
lines 68-79 — the block's markup commented out with Smarty comments. Theme-level,
lost on any theme update.

**Module replacement:** `hookActionPresentProduct` force-empties
`specific_references` on the presented product (`offsetSet(..., [], true)` on the
LazyArray), so every theme's `{if $product.specific_references}` guard skips the
section — works on a stock theme too.

**Test (automated, HTTP):** un-comment the block in the warehouse theme template
(making it stock-equivalent), clear the ets_superspeed page cache
(`var/cache/dev/ss_pagecache`), then fetch real product pages and look for the
`.specific-references` div and the rendered `ean13`/`upc`/`isbn` labels.

**Test products matter:** `ProductLazyArray::getSpecificReferences()` returns
`null` unless the product has **combinations** (`isset($this->product['attributes'])`).
Products 14/17/20 (EAN set, no combinations) never render the section at all, so
they cannot discriminate. Used 502 / 501 / 535 — active, with combinations and
EAN/UPC.

**Result:**

| theme template block | hook registered | `.specific-references` | labels |
|---|---|---|---|
| commented out (khewa) | yes | absent | 0 ✓ accepted |
| **active (stock-equivalent)** | **yes** | **absent** | **0 ✓ accepted** |
| active (stock-equivalent) | **no** (`ps_hook_module` id_hook 675 deleted) | PRESENT | 3 — rejected |

Row 2 is the verification: with the theme template's block fully restored, the
module's `hookActionPresentProduct` alone still empties `specific_references`
and the section does not render. Row 3 proves the test discriminates.

Theme template and the three `ps_hook_module` rows restored afterwards.

**Status:** PASS (2026-09-21)

---

## 15 — "Free" shipping label removed

**Accepted:** cart popup and checkout summary show an empty shipping value when
shipping is free. Numeric shipping costs still print normally.

**Rejected:** the literal word "Free" / "Gratuit" appears as the shipping value.

**Core change:** two theme templates wrap the shipping subtotal in
`{if $subtotal.type == 'shipping' && !preg_match('/[\d]+/', $subtotal.value)}{else}{$subtotal.value}{/if}`
— `themes/warehouse/modules/ps_shoppingcart/ps_shoppingcart-content.tpl` and
`themes/warehouse/templates/checkout/_partials/cart-summary-subtotals.tpl`.
Same commit also added `nofilter` to `mails/_partials/order_conf_product_list.tpl`.

**Module replacement:** `hookActionPresentCart` blanks the shipping subtotal
value at data level when it contains no digit, so stock templates hide it too.
The edited theme templates and the mail partial are additionally kept as
managed files (`cart_popup`, `cart_subtotals`, `mail_product_list`).

**Test (automated, CLI):** strip the `preg_match` guard out of both theme
templates (making them stock), build a cart with carrier 324 "Pick up in-store"
(`is_free = 1`, shipping 0.00), run the real `CartPresenter::present()` — which
dispatches `actionPresentCart` with the cart **by reference**
(`CartPresenter.php:491`) — and read `subtotals.shipping.value`. Then render the
stock `cart-summary-subtotals.tpl` with that presented cart.

**Result:**

| theme templates | hook registered | presented shipping value | rendered markup |
|---|---|---|---|
| khewa (guard present) | yes | `''` | — ✓ accepted |
| **stock (guard stripped)** | **yes** | **`''`** | **"Sous-total 56,00 $", no "Gratuit" ✓ accepted** |
| stock (guard stripped) | **no** (`ps_hook_module` id_hook 672 deleted) | `'Gratuit'` | word shown — rejected |

Row 2 is the verification: with both theme templates reverted to stock, the
module's `hookActionPresentCart` alone blanks the value and the rendered markup
contains no "Free"/"Gratuit". Row 3 proves the test discriminates.

Both theme templates and the three `ps_hook_module` rows restored; test carts
deleted (0 remaining).

**Status:** PASS (2026-09-21)

---

## 16 — ps_emailalerts: no employee "new order" alert for POS sales

**Accepted:** an order created through RockPOS sends no employee new-order alert
email; a web order still does.

**Rejected:** staff are emailed for every till sale.

**Core change:** `modules/ps_emailalerts/ps_emailalerts.php::hookActionValidateOrder`
— lookup against `ps_pos_cart`; `return false` for POS carts (commit `2a5628fea`).
Same file also carries the "Notify me" subscribe-button fix (`90c571fad`).
Lost on any ps_emailalerts update.

**Module replacement:** `hookActionEmailSendBefore` returns `false` for
`new_order` mails whose `templatePath` contains `emailalert` and whose
`{order_name}` resolves to a cart present in `ps_pos_cart` — version-independent.
A golden copy of `ps_emailalerts.php` is also kept as managed file
`ps_emailalerts` (covers the Notify-me fix, which has no hook equivalent).

**Test (automated, CLI):** this item has **two independent carriers** — test
each. POS order 33583 (cart 38030, in `pos_cart`) vs web order 33497
(cart 37937, not in `pos_cart`).

**Carrier A — managed file `ps_emailalerts` (golden copy):** deleted the
`// new change … //end` POS-skip block from the live
`modules/ps_emailalerts/ps_emailalerts.php` (simulating a vendor update), then
ran the module's `deployManagedFiles('ps_emailalerts')` ("Re-apply" on the
config page). The file was restored **byte-identical** to its pre-test state and
the POS skip came back. **Works.**

**Carrier B — `hookActionEmailSendBefore` returning `false`:** same defect as
**#11a**.

| root `override/classes/Hook.php` (ets_superspeed) | POS order `new_order` | web order `new_order` |
|---|---|---|
| present (**live state**) | **WOULD SEND — guard defeated** | would send ✓ |
| removed | **BLOCKED ✓** | would send ✓ |

`$module->hookActionEmailSendBefore()` returns `false` correctly (verified
directly), but ets_superspeed's `Hook::callHookOn()` override casts it to `''`
before `Mail::send`'s `array_reduce` (`Mail.php:179`), so `keepGoing` stays
`true`. See #11a for the mechanism.

**Net effect:** POS-sale alerts are currently suppressed — but by the *file*
(carrier A), i.e. the original core change, not by the update-proof hook. If a
ps_emailalerts update overwrote that file and the module were relied on to hold
the line via the hook alone, alerts would resume until "Re-apply" is run.

The Notify-me fix (`90c571fad`) has no hook equivalent and rests entirely on
carrier A, which tested good.

**Status:** PARTIAL (2026-09-21) — managed file PASSES; hook guard FAILS while
ets_superspeed's `Hook::callHookOn` override is installed.

---

## Not verified (no code change / out of scope)

- **1 Rock (hspointofsalepro)** — module no longer carries it, by decision 2026-09-02.
- **12 Remove email** — back-office setting `PS_CONTACT_INFO_DISPLAY_EMAIL`.
- **13 Extra Small** — product/attribute data.
- **17 nathaliecoutou.com** — domain/DNS, no code trace.
