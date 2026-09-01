# Khewa Core Changes Log

## Purpose

The Khewa site runs a heavily customized PrestaShop 1.7.8.7 install — over time, core PrestaShop files (classes, `src/`, `pdf/` templates, mail templates) and vendor module files (RockPOS / `hspointofsalepro`) were edited directly, without any tracking system for what was changed or why. Any PrestaShop reinstall/upgrade, theme update or module update brings in stock versions of those files and risks silently wiping out the undocumented customizations.

This `khewacorechanges` module **holds and re-applies those customizations** (as overrides, hooks, a service swap, and managed file copies — see `UPDATE_SAFETY.md`) so they survive future PrestaShop/module updates. This file, `CORE_CHANGES.md`, is the inventory of what was actually changed, in which files, and why — reconstructed from a list of 17 Trello "Core Change--..." cards (from the "khewa Tasks Done" board) that described the changes only vaguely, cross-referenced against a fresh 1.7.8.7 install and this repo's git history (`github.com/Khewa-Backup/khewa`).

**Status:** the module now carries every change that is a real code change (see the "Carried by module" line under each item, and `UPDATE_SAFETY.md` for the mechanism). Items with no code change (#12, #13, #17) and the unresolved #14 are documentation only.

Confidence is noted per item below — some are confirmed with a file diff + matching commit, some are commit-only (no fresh-install equivalent to diff against), and a few turned out not to be code changes at all (backend config, or unresolved). Items flagged "upgrade-breaking" are the ones most likely to silently regress when core files are replaced by an update — check those first on the module's configuration page after any upgrade.

The card "Credit slip disappears after created" was excluded from this pass per instruction. The original, verbatim Trello card text this document was built from is preserved in the **Appendix** at the bottom, in case the source cards are later archived or edited.

---

## 1. Rock (hspointofsalepro / RockPOS)

**Trello note:** "rocks changes are in sales.php, paymentmodule.php, pospayment.php and main module file — if we compare those files with the latest one then we will know."

**Files:**
- `modules/hspointofsalepro/controllers/front/sales.php`
- `modules/hspointofsalepro/classes/PosPaymentModule.php`
- `modules/hspointofsalepro/classes/PosPayment.php`
- `modules/hspointofsalepro/hspointofsalepro.php`

**Status: no vendor baseline available.** RockPOS is a paid third-party module (Hamsa Technologies) — there's no original vendor build in this repo (`hspointofsalepro.zip` doesn't exist) and no copy in the fresh PS 1.7.8.7 install (expected, it's third-party). The only available reference is this repo's own git history:

- `sales.php` — 12 commits (earliest `ee4d5fbff`, latest `e6de3ad25` "coucher fix"). Themes: refunds tab, discount-column/loading fixes, shipping/credit-slip fixes, voucher/gift-card dropdown fixes, tax fixes, cart/mail-template issues.
- `PosPaymentModule.php` — 3 commits (lightly touched: baseline import + a JS-related update).
- `PosPayment.php` — 1 commit only (essentially untouched since import).
- `hspointofsalepro.php` — 6 commits: tax fixes (left-side tax, voucher text, export), French/theme additions, JS updates, most recent "reciept fix" (receipt output logic, paired with `sales.js`/`sales.tpl` changes).

**Carried by module:** managed golden copies `files/root/modules/hspointofsalepro/…` (4 files), re-applied from the config page. These are whole-file snapshots, not diffs — click **Pull** after editing `sales.php`, and merge before re-applying over a newer vendor version.

**Real conflict found (not a Trello card, but relevant):** both `modules/hspointofsalepro/override/classes/Cart.php` (module-bundled, 1180 lines, untouched since import) and root `override/classes/Cart.php` (1204 lines, **the one PrestaShop actually loads**) override `Cart::getProducts()`. The root copy was patched in commit `edd95ec6b` ("core change online gift card issue") to handle fixed-amount/gift-card discounts exceeding product total — a fix **absent from the module-bundled copy**. If the module is ever reinstalled/reset (which typically re-copies its bundled override into `override/`), this gift-card fix would be silently overwritten. See Housekeeping Notes below.

---

## 2. classes/CartRule.php

**Status: confirmed via diff + git history.**

**Carried by module:** `override/classes/CartRule.php` (PrestaShop-native class override, installed into root `override/` on module install).

Core file `classes/CartRule.php` was edited directly (no override wrapper used). The change alters how a fixed-amount ("¤") cart-rule discount is taxed when `reduction_tax` is false but a tax-included total is requested. Stock PrestaShop always routes this through a VAT-rate-adjustment calculation; the khewa version adds a special case (inside `getContextualValue()`, ~line 1174-1450): amount-based discounts are applied to the tax-included cart total directly rather than through the normal per-product/per-cart VAT-rate math. All original logic remains as the `else` fallback for every other case.

**Commit history:**
- `99f2dc776` ("classes/CartRule.php core change", 2025-10-03) — introduced the amount-discount tax-split logic described above.
- `c7e7fc5cb` ("OLD ISSUE - Tax issue again...", 2025-11-20) — extended the same treatment to **percentage** discounts too.
- `f9dae8e23` ("undo", 2025-11-23) — fully reverted the `c7e7fc5cb` percent-discount change. **Current file only contains the original amount-discount fix from `99f2dc776`.**

---

## 3. src/Core/Grid/Query/OrderQueryBuilder.php

**Status: confirmed via diff + git history.**

**Carried by module:** `config/services.yml` re-declares `prestashop.core.grid.query_builder.order` → `src/Grid/Query/KhewaOrderQueryBuilder.php` (debug leftovers `ini_set('display_errors')` and dead `__` methods dropped from the module copy).

Core file edited directly. Adds a "New Customer" column/filter to the back-office Orders grid using a different SQL strategy than stock. Stock uses a correlated subselect per row to determine if an order is a customer's first order (`getNewCustomerSubSelect()`). The khewa version instead builds a derived table of each customer's minimum `id_order` (`fo`) and LEFT JOINs it in `getBaseQueryBuilder()` (~line 154), comparing `fo.first_order = o.id_order` in both `addNewCustomerField()` (~line 133) and `applyNewCustomerFilter()` (~line 306) — a performance fix, since the correlated subselect scaled poorly on a large orders table. The old subselect methods remain in the file renamed with `__` prefixes (dead code). Debug leftovers also remain: `ini_set('display_errors', '1')` in the constructor and commented-out `dump()`/pagination experiment lines.

**Commit:** `85d3d4ed1` ("Order list long time loading issue solved", 2024-12-13).

---

## 4. URL issue (duplicate link-rewrite between products 12705 and 750)

**Status: confirmed via full override read + git history.**

**Carried by module:** `override/controllers/front/ProductController.php` (PrestaShop-native controller override). On install the existing hand-made root override is backed up to `backup/` and regenerated by PrestaShop from the module copy.

`override/controllers/front/ProductController.php` (360 lines) is a full override of `ProductController::init()`. It adds custom logic at the top of `init()` (lines ~14-58) that manually parses `$_SERVER['REQUEST_URI']` with regex to extract the numeric product ID and `link_rewrite` slug from the URL, then looks up `ps_product_lang` directly. Critically, if the numeric ID embedded in the URL disagrees with whatever `id_product` was resolved via the (possibly colliding) `link_rewrite` slug, the code forces `$_POST['id_product']` to the numeric ID from the URL — i.e. the numeric ID is trusted as the tiebreaker over a non-unique text slug. This directly fixes the reported bug where products #12705 and #750 shared/collided on `link_rewrite`, causing the wrong product page to load.

**Commit:** `f49bb3ffb` ("front controllers overrides are downloaded for use in stage", 2024-12-19) — added the full 361-line override in one shot.

**Orphan file found:** `override/controllers/front/ProductController00.php` (385 lines) sits in the same directory. It is **not** loaded by PrestaShop's autoloader (wrong filename) and differs from the active file by ~60 lines — looks like a superseded backup left behind. See Housekeeping Notes.

---

## 5. Added tax information in pdf/footer.tpl

**Status: confirmed via diff.**

**Carried by module:** managed file `files/theme/pdf/footer.tpl` → `themes/<theme>/pdf/footer.tpl` (theme pdf folder is checked before core `pdf/`).

`pdf/footer.tpl` has additive-only changes vs. stock: Quebec tax registration numbers (GST `TPS 143581395RT0001`, QST `TVQ 1023112902TQ0001`) plus bilingual exchange-policy/thank-you text ("Pour échange seulement avec le reçu / For exchange only with receipt", "Merci! Thank you!"). No stock content was removed.

**Commit:** `56c3d491c` — this content is present from very early in tracked history (second commit in the repo), so it can't be attributed to one isolated "fix" commit; it's effectively baseline.

---

## 6-8. Guest customer "Total sales" hidden (3 cards, one underlying change)

- "customer service guest khewa is hidden"
- "Guest khewa khewa total sales is hidden" (Customers list)
- "Total sales from customer account from orders list is hidden"

**Status: confirmed via git history.**

**Carried by module:** #6 via managed file `files/root/override/controllers/admin/templates/customer_threads/helpers/view/view.tpl` (admin template override path); #7/#8 via `hookDisplayBackOfficeHeader` injecting the CSS rule — no admin theme file edit needed any more.

**Commit:** `17e4f7683` ("hidding sales from customer service and customer page for guest khewa account", 2022-12-15).

Two files changed:
1. `admin556cvt7923/themes/default/template/controllers/customer_threads/helpers/view/view.tpl` — the `%total%` placeholder in the "order(s) validated for a total amount of ..." badge (Customer Service thread view) was hard-changed from `$total_ok` to a literal single space, so the amount no longer renders.
2. `admin556cvt7923/themes/new-theme/public/theme.css` — added `.column-total_spent .badge-success{ display: none; }`, hiding the "total spent" badge in the admin Customers list grid.

**Important nuance:** despite the "guest" wording in the card titles, neither change is conditional on `is_guest` — both are blanket admin-theme-level suppressions (template text blanked unconditionally, CSS class hidden universally) affecting **all** customers in those two admin screens, not guests specifically.

---

## 9. order_conf template — custom pickup message

**Status: confirmed via diff. Upgrade-breaking — read this one carefully.**

**Carried by module:** `hookActionEmailSendBefore` redirects `order_conf` to `modules/khewacorechanges/mails/<lang>/` (EN, FR, QC — FR sentence added by the module; it was missing on the live site).

Custom sentence added directly after "Thank you for shopping on {shop_name}!" in the order confirmation email:

> "Please note that for pickup orders we will contact you when your order is ready."

Found in:
- `mails/en/order_conf.html:281` and `mails/en/order_conf.txt:6`
- `mails/qc/order_conf.html:281` and `mails/qc/order_conf.txt:6` (French-Canadian: *"Nous vous contacterons lorsque votre commande sera prête."*)

**Missing from FR:** `mails/fr/order_conf.html`/`.txt` do **not** have the equivalent sentence — appears to have been missed when this was added.

**Upgrade impact:** Confirmed the fresh PrestaShop 1.7.8.7 install has **no `mails/<lang>/order_conf.html` files at all**. 1.7.8.7 replaced the old static per-language mail files with a Twig-based mail theme system (`mails/themes/classic/core/order_conf.html.twig`, `mails/themes/modern/core/order_conf.html.twig`), with all text pulled via `|trans()` from translation catalogs (`Emails.Body` domain) instead of hardcoded files. There is no equivalent static text block to drop the sentence into. **This customization will be lost on upgrade** and needs to be re-implemented either as a new translation string added to the `Emails.Body` catalog (en/fr/qc) and inserted into the twig template, or reworked as a conditional block if it should only show for pickup-type orders.

---

## 10. Modifying invoice for discounts

**Status: confirmed via diff + git history. Also upgrade-breaking (not covered by the mail-theme migration).**

**Carried by module:** managed files `files/theme/pdf/invoice.total-tab.tpl` and `invoice.product-tab.tpl` → `themes/<theme>/pdf/`.

Two files with real, substantial diffs vs. stock:

- **`pdf/invoice.total-tab.tpl`** — restructured into two branches: when `product_discounts_tax_excl > 0`, shows Total Products → Shipping → Total Discounts → Total (Tax Excl.) → Total Tax (explicitly using `footer.total_taxes`, commented as "correctly handles fixed amount discounts") → final Total; falls back to a simpler stock-like block when there's no discount.
- **`pdf/invoice.product-tab.tpl`** — the tax-rate column is now always shown (stock suppresses it via `{if $isTaxEnabled}`), plus some cell-alignment and customization-label formatting changes.
- `pdf/invoice.style-tab.tpl` is identical to stock — no change there.

**Commits (iterative fixes over time):**
- `31124518e` ("core changes commit after update") — original large rewrite of both tab templates.
- `c472aa6c0` ("All new changes / slip fixing / shipping refund fix / credit slip fix")
- `5938051a8` ("wrong discount patern issue") — 3-line fix to invoice.product-tab.tpl
- `edd95ec6b` ("core change online gift card issue") — 9-line fix to invoice.total-tab.tpl specifically for orders paid with gift cards

**Upgrade impact:** these are core PDF templates, not part of the mail-theme migration — they will be silently overwritten by stock files on upgrade and must be reapplied.

---

## 11. Disabling mail send from core

**Trello note:** "there was a bug in prestashop that even after uninstalling the mail alert, mails were sent to people. The solution is within src folder — search `Mail::send(`."

**Status: investigated — the real fix is NOT a code change.**

**Carried by module:** nothing to carry — the fix is the `mailalerts_old` folder rename, which lives outside any upgraded path. Keep that folder as-is.

All three `src/` candidates (`SendProcessOrderEmailHandler.php`, `SendCartToCustomerHandler.php`, `MailThemeController.php`) are **byte-for-byte identical** to stock PrestaShop 1.7.8.7. No core `src/` file was ever patched for this bug — the Trello note describes how the bug was *traced*, not where the fix ended up.

The actual fix: the stock `mailalerts` module folder was **renamed to `mailalerts_old`**, but its `config.xml` still internally declares `<name>mailalerts</name>`. Since PrestaShop's module loader requires the folder name to match the registered module name, this mismatch permanently prevents the module class from ever being instantiated again — breaking any leftover hook/cron references from an incomplete uninstall, which silently stops the stray mail sends. This rename **predates the repo's git history entirely** (already present in the very first tracked commits) — there's no commit to point to.

**Separate, unrelated finding along the way:** commit `2a5628fea` ("new order email alerts for employees disabled for rockpos seller orders", 2024-07-28) modifies the currently-active `modules/ps_emailalerts/ps_emailalerts.php` to skip the employee "new order" alert email for RockPOS/in-store POS orders (checks `ps_pos_cart` table). This is a real, deliberate change but belongs with card #16 (Email Alert Module) below, not this one.

---

## 12. Remove email (Contact us / Store Information block)

**Status: investigated — likely not a code change.**

**Carried by module:** nothing — back-office setting (`PS_CONTACT_INFO_DISPLAY_EMAIL`), stored in the database.

`modules/ps_contactinfo/ps_contactinfo.tpl`, `ps_contactinfo-rich.tpl`, and `ps_contactinfo.php` are all unmodified stock files. The email line is already conditionally guarded in stock code by `{if $contact_infos.email && $display_email}`, where `$display_email` is controlled by the module's own back-office setting `PS_CONTACT_INFO_DISPLAY_EMAIL` (default enabled). No override, no theme-level override, and no git history touching `modules/ps_contactinfo/` was found.

**Conclusion:** this was very likely done as a back-office configuration toggle (Modules → Contact Information → uncheck "Display" for the email field) rather than a code edit — a DB `ps_configuration` change, not something version control would ever show. Nothing to migrate for this one; flagging as "probably mis-filed as a code change."

---

## 13. Extra Small

**Status: confirmed — no code change.**

**Carried by module:** nothing — backend product/attribute data.

The Trello card itself states this needed no code ("this solution dont need code. it can be done from backend.") — it was a product/attribute configuration change (adding the "XS" size option) done entirely through the PrestaShop admin. No git matches found, consistent with this. Nothing to migrate.

---

## 14. Remove Specific References

**Status: unresolved — likely a mismatched Trello card/commit link. Needs confirmation.**

**Carried by module:** nothing yet — pending confirmation of what this card actually refers to. (The QST 9.976% fix it was mistakenly matched to lives in `khewareports`, our own module, so it is not at risk from a core upgrade anyway.)

The only plausible commit match by search, `4e061647d` ("All remaining referencess", 2026-06-25), turns out to be a **Quebec sales tax (QST) rate correction** — updating the hardcoded QST rate from 9.975% to 9.976% across three `khewareports` module files (`classes/KhewaReportsData.php`, `controllers/admin/AdminKhewaReportsReportsController.php`, `khewareports.php`), affecting SQL/PHP multipliers and report column labels. Nothing in that diff touches product references/SKUs.

Either this is the wrong commit for the card, or "references" in the card title refers to something other than product reference numbers. **This needs a human check** (e.g. re-opening the original Trello screenshot, if the prnt.sc link is still accessible) before it can be documented with confidence.

---

## 15. Removed Free (shipping label)

**Status: confirmed via git history.**

**Carried by module:** managed files `files/theme/modules/ps_shoppingcart/ps_shoppingcart-content.tpl`, `files/theme/templates/checkout/_partials/cart-summary-subtotals.tpl` → theme; `files/theme/mails/en/order_conf_product_list.tpl` → `themes/<theme>/mails/en/` (checked before `mails/_partials/`).

**Commit:** `9618980a5` ("Free shipping text was removed from card, checkout", 2023-02-18). Touches three files:
- `themes/warehouse/modules/ps_shoppingcart/ps_shoppingcart-content.tpl` and `themes/warehouse/templates/checkout/_partials/cart-summary-subtotals.tpl` — both wrap the shipping subtotal value in a check: if the subtotal type is `shipping` **and** the formatted value contains no digits (i.e. PrestaShop is about to print the literal word "Free"), the value is suppressed and nothing is shown. Numeric shipping costs are untouched — only the "Free" text label is hidden.
- `mails/_partials/order_conf_product_list.tpl` — unrelated change bundled into the same commit: adds `nofilter` so HTML in product customization text renders instead of being escaped.

---

## 16. Email Alert Module

**Status: confirmed via git history.**

**Carried by module:** the POS-sale alert skip is re-implemented in khewacorechanges itself (`hookActionEmailSendBefore` returns false for `new_order` mails whose order is in `pos_cart`), so it survives any ps_emailalerts update. A golden copy of `ps_emailalerts.php` is also kept (covers the Notify-me fix).

The change is a **modification of the stock `ps_emailalerts` module** (back-in-stock/out-of-stock notifications), not a custom replacement:

- `90c571fad` ("Product Notify me button issue fixed", 2023-02-19) — bug fix to the back-in-stock "Notify me" subscribe button.
- `2a5628fea` ("new order email alerts for employees disabled for rockpos seller orders", 2024-07-28) — in `hookActionValidateOrder`, adds a lookup against the `pos_cart` table; if the order's cart is a RockPOS/in-store sale, the employee new-order-alert email is skipped (`return false`). This stops staff being emailed for every till sale. (Also mentioned under #11 above — same commit, different card.)

**Note:** `modules/khewamails/` (introduced separately in commit `69173c4e1`, "new mail module", 2025-06-05) is an **unrelated, bespoke module** — a front-office visitor email-collection/signup form, not an order/stock alert replacement. Don't conflate it with this card.

---

## 17. Reinstate nathaliecoutou.com

**Status: unresolved — no code trace found.**

**Carried by module:** nothing — not a code change.

No matching commit anywhere in git history (`git log --all --grep="nathaliecoutou"` returns nothing). This is very likely a domain/hosting/DNS-level change (or a Shop URL entry in the `ps_shop_url` DB table) rather than a code change — nothing in this repo reflects it. Flagging for user confirmation on scope; there's nothing to document as a "core change" here unless more detail surfaces.

---

## Housekeeping notes (found along the way, not Trello cards)

These aren't customizations to migrate, but matter for the upgrade and are worth acting on separately:

1. **Divergent Cart.php overrides (real risk):** `override/classes/Cart.php` (root, actually loaded) and `modules/hspointofsalepro/override/classes/Cart.php` (module-bundled) both override `Cart::getProducts()` and have **diverged** — the root copy has a gift-card discount fix (`edd95ec6b`) that the module-bundled copy lacks. If hspointofsalepro is ever reinstalled/reset, its bundled override could silently replace the root one, losing the gift-card fix. Recommend syncing these two files.
2. **Orphan file:** `override/controllers/front/ProductController00.php` — not autoloaded (wrong filename), differs from the active `ProductController.php` by ~60 lines. Looks like a stale backup; candidate for cleanup during the upgrade (not touched in this pass).
3. **Duplicate theme directory:** `themes/classic-` (trailing dash) exists alongside the active `themes/classic`/`themes/warehouse` — appears to be an old/renamed copy. Also stray zip archives sit in `themes/` root (`classic (2).zip`, `classic_old.zip`, `theme_4_5_3.zip`). Worth deciding whether these are needed before the upgrade.
4. **Module folder naming quirks:** `modules/mailalerts_old/` still internally identifies as `mailalerts` in its `config.xml` (this is what makes card #11's fix work — see above, don't "fix" this naming mismatch, it's load-bearing). Similarly `modules/ps_emailalerts_old/` and a `productoutofstock.html.twig_old` exist as disabled backups.
5. **Card #14 commit mismatch** (see above) — needs a human check against the original Trello card before trusting the documentation for that one.

---

## Appendix: raw Trello source (verbatim)

Source: "khewa Tasks Done" board, August list, 18 cards total. Card 1 ("Credit slip dissappears after created.") was skipped per instruction and is not documented above. Most cards were bulk-copied into the "august" list by Md. Masudur Rahman on Aug 11, 2026 (re-copies of older "Core Change--" cards); several have no description beyond a Trello screenshot link (prnt.sc URLs) that could not be opened directly.

**1. Core Change--Rock**
Comment (from card "Rock"): "rocks changes are in sales.php, paymentmodule.php, pospayment.php and main module file — if we compare those files with the latest one then we will know."
Files: sales.php, paymentmodule.php, pospayment.php, + main module file (unnamed)

**2. Core Change--classes/CartRule.php**
No description, no comments. File: classes/CartRule.php

**3. Core Change--src/Core/Grid/Query/OrderQueryBuilder.php**
No description, no comments. File: src/Core/Grid/Query/OrderQueryBuilder.php

**4. Core Change--URL issue**
Description: Duplicate link-rewrite bug between two products (12705 and 750) causing wrong product URLs. Resolved by Rakibul: "the problem was duplicate link rewrite between 12705 and 750 products... I corrected it by adding some new codes... the product front controller was the source of the problem." His changes will be overwritten on update — his suggestion: back up the `override` directory before upgrading PrestaShop, then restore it after and clear cache.
Files: product front controller (unnamed specific file), override directory

**5. Core Change--Added tax information in pdf/footer.tpl**
No description, no comments. File: pdf/footer.tpl

**6. Core Change--customer service guest khewa is hidden**
No description/comments.

**7. Core Change--customers> Guest khewa khewa total sales is hidden**
No description/comments.

**8. Core Change--Total sales from customer account from orders list is hidden**
No description/comments.

**9. Core Change--order_conf template changes for custom message**
Description: screenshot link (prnt.sc/DMos2EFINKID) + text "we will contact you when pickup is ready" — custom message added to order confirmation template.

**10. Core Change--modifying invoice for discounts**
Description: "These are the changes for the invoice we did in core" + screenshot link (prnt.sc/V-1k1Z8xyc-z) + "we can find the code from git."
Comment (from Malcolm): "Does this mean that this change has already been implemented?"

**11. Core Change--Disabling mail send from core**
Description: "there was a bug in prestashop that even after uninstalling the mail alert, mails were sent to people. The solution is within src folder in. if we search with Mail::send( function then we will find it."
Note: search for `Mail::send(` inside the `src` folder.

**12. Core Change--Remove email**
No description. 2 image attachments — both screenshots of the "Store Information / Contact us" block with the "Email us: info@khewa.com" line circled → task was to remove that email from the contact/store info section.

**13. Core Change--Extra Small**
Description: "this solution dont need code. it can be done from backend."
1 image attachment — product page screenshot with the "XS" size button circled (Palazzo Carla product) → adding/enabling Extra Small size, done via backend config, no code.

**14. Core Change--Remove Specific References**
Description: screenshot link (prnt.sc/NWEAw667XZdc). 1 image attachment (same screenshot, not fully legible in preview — appears to be a product/reference listing).

**15. Core Change--Removed Free**
Description: "here is the fix" + screenshot link (prnt.sc/yRnSIToINDNr). 2 image attachments — both show the shopping cart / "added to cart" popup with "Shipping: Free" circled → task was removing the "Free" shipping label text from the cart display.

**16. Core Change--Email Alert Module**
Description: "here is the details of the changes. we can retrieve from git" + screenshot link (prnt.sc/5vv3xUefBOTC). No comments.
