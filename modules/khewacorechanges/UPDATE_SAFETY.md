# Core changes — handled or not

Site version: PrestaShop 1.7.8.7. "Handled" = this module carries the change and re-applies it after an update. Full history per item in `CORE_CHANGES.md`. Routine after any update: Modules → Khewa Core Changes → Configure → **Re-apply all**, check both Class overrides say *installed*, clear the cache.

| # | Core change | What was actually changed | How this module handles it | Handled? |
|---|---|---|---|---|
| 1 | Rock (RockPOS module) | Years of custom edits inside `hspointofsalepro`: `controllers/front/sales.php` (refunds tab, discounts, credit slips, vouchers, tax fixes), `classes/PosPaymentModule.php`, `classes/PosPayment.php`, `hspointofsalepro.php` (receipt output, exports, French) | Whole-file snapshots in `files/root/modules/hspointofsalepro/` — restore with **Apply**, refresh with **Pull** after editing. No merge capability. | **PARTIALLY** — sales.js, sales.tpl, ExportSales.php and the bundled Cart.php override are NOT snapshotted; a RockPOS update always needs manual merge |
| 2 | classes/CartRule.php | In `getContextualValue()`: a fixed-amount voucher stored tax-excluded, when a tax-included total is asked for, is applied to the tax-included cart total directly instead of being inflated by the average VAT rate (commit `99f2dc776`) | PrestaShop class override: `override/classes/CartRule.php` redefines `getContextualValue()` and shadows the core file. Installed into root `override/` when the module is installed. | **HANDLED** |
| 3 | src/Core/Grid/Query/OrderQueryBuilder.php | "New customer" column on the BO Orders list computed via one derived table (`fo` = each customer's first order id) LEFT JOINed once, instead of a slow per-row subquery — the Orders page slow-loading fix (commit `85d3d4ed1`) | Symfony service swap: `config/services.yml` re-declares service `prestashop.core.grid.query_builder.order` to the module's copied class `src/Grid/Query/KhewaOrderQueryBuilder.php`. Core file becomes unused. | **HANDLED** |
| 4 | URL issue (duplicate link-rewrite) | `ProductController::init()` parses the URL itself and trusts the numeric product id in the URL over a colliding link-rewrite slug (products 12705 vs 750 opened the wrong page) | PrestaShop controller override: `override/controllers/front/ProductController.php`. The old hand-made root override is backed up and regenerated from the module on install. | **HANDLED** |
| 5 | pdf/footer.tpl | Added TPS/TVQ tax registration numbers + bilingual "exchange only with receipt / thank you" lines to receipt/invoice PDF footer | Class override of `HTMLTemplate::getTemplate()` makes every PDF look in `modules/khewacorechanges/pdf/` FIRST — the customized `footer.tpl` lives inside the module, so **core and theme updates cannot touch it**. A theme copy is kept as fallback. | **HANDLED** |
| 6 | Customer service guest khewa hidden | In the admin Customer Service thread view, the "…orders validated for a total amount of X" badge has its amount blanked (template's `%total%` replaced with a space) | Copy of the edited `view.tpl` deployed to `override/controllers/admin/templates/customer_threads/helpers/view/` — PrestaShop checks that path before the admin theme. | **HANDLED** |
| 7 | Customers > guest total sales hidden | CSS rule `.column-total_spent .badge-success{display:none}` added to the admin theme's theme.css, hiding the Total spent badge in the Customers list | The module's `hookDisplayBackOfficeHeader` injects the same CSS rule on every admin page — no theme file needed. | **HANDLED** |
| 8 | Total sales from customer account / orders list hidden | Same single CSS rule as #7 covers this screen too | Same hook as #7. | **HANDLED** |
| 9 | order_conf custom message | Order confirmation email got the extra sentence "Please note that for pickup orders we will contact you when your order is ready." (EN + QC; FR was missing) | `hookActionEmailSendBefore` redirects the `order_conf` template to the module's own `mails/en|fr|qc/order_conf.html/.txt` (FR sentence added). Site `mails/` folder no longer matters for this email. | **HANDLED** |
| 10 | Invoice modified for discounts | `pdf/invoice.total-tab.tpl` rewritten (correct Total Discounts / Total Tax when fixed-amount or gift-card discounts apply) and `pdf/invoice.product-tab.tpl` (tax-rate column always shown, alignment) | Same `HTMLTemplate::getTemplate()` override as #5 — `invoice.total-tab.tpl` and `invoice.product-tab.tpl` are served from `modules/khewacorechanges/pdf/`, immune to core and theme updates. Theme copies kept as fallback. | **HANDLED** |
| 11 | Disabling mail send from core | Uninstalled mail-alert module kept sending mails; fixed by renaming the folder `mailalerts` → `mailalerts_old` (its config.xml still says `mailalerts`, so PrestaShop can never load it again) | Nothing to carry — the rename lives outside every update path. Rule: never rename that folder back, never reinstall mailalerts. | **NOT HANDLED — nothing to handle** |
| 12 | Remove email (contact/store info block) | Shop email removed from the "Store information" block via the back-office toggle (`PS_CONTACT_INFO_DISPLAY_EMAIL` = 0 in the database), not by code | Nothing to carry — database setting; updates don't touch it. | **NOT HANDLED — nothing to handle** |
| 13 | Extra Small | XS size attribute added from the back office (product/attribute data) | Nothing to carry — database data. | **NOT HANDLED — nothing to handle** |
| 14 | Remove Specific References | The "Specific References" block (Ean13 / Isbn / Upc table) on the product page was removed — commented out in the warehouse theme's `catalog/_partials/product-details.tpl` (lines 68-79) | The module's `hookActionPresentProduct` empties `specific_references` before any template renders it — the section stays hidden **even on a stock or updated theme**. | **HANDLED** |
| 15 | Removed Free (shipping label) | Cart popup + checkout summary templates suppress the shipping value when it contains no digits (i.e. the word "Free"); plus `nofilter` on customization text in the order email product list | The module's `hookActionPresentCart` blanks the shipping value whenever it contains no digits (the word "Free"/"Gratuit") before any template renders it — **works even with a stock, un-edited theme, so theme updates can't bring "Free" back**. The edited theme templates + the `nofilter` mail partial are also kept as deployable copies. | **HANDLED** |
| 16 | Email Alert Module (ps_emailalerts) | In `hookActionValidateOrder`: if the order's cart is in `pos_cart` (a RockPOS till sale), skip the employee "new order" alert email (commit `2a5628fea`); plus the earlier "Notify me" button fix (`90c571fad`) | The module's own `hookActionEmailSendBefore` blocks the `new_order` email whenever the order is a RockPOS till sale (cart in `pos_cart`) — the mail is stopped before sending, outside ps_emailalerts. **ps_emailalerts can be updated freely; the skip keeps working.** A snapshot of `ps_emailalerts.php` is also kept for the Notify-me fix. | **HANDLED** |


**Also not handled by this module:** the other pre-existing files in the root `override/` folder (Cart.php with the gift-card fix, Hook.php, Dispatcher.php, etc.) — this module only carries CartRule and ProductController. If the site is ever rebuilt from a fresh copy, the whole `override/` folder must be carried over manually.

---

## How to test each change

The idea of every test: **disable or spoil the OLD in-place edit, and check the behaviour is still there** — that proves the module's copy is the one doing the work. Don't actually delete anything permanently; rename to `.bak` or add a marker, test, then put it back. Stock files for comparison/swap are in the fresh copy at `untitled folder/prestashop_1.7.8.7/prestashop/`. After any file swap: clear the cache (Advanced Parameters → Performance).

**1. Rock (RockPOS) — restore test only (snapshots, not behaviour):**
Rename `modules/hspointofsalepro/controllers/front/sales.php` to `sales.php.bak`. Open Modules → Khewa Core Changes → Configure — the row shows **missing**. Click **Apply** — the file is back. `diff` it against the `.bak`: identical. Delete the `.bak`. (Same test works for the other 3 RockPOS files and for #16.)

**2. classes/CartRule.php:**
Replace `classes/CartRule.php` with the stock 1.7.8.7 copy (the custom block is lines **1398–1413**, the `// Special handling for tax-excluded discounts...` branch — swapping the whole file is cleaner than cutting lines). Clear cache. In BO create a cart rule: fixed amount **$10**, "tax excluded". Apply it to a cart in the front office. The total must drop by exactly **$10.00**. If the module were not working you would see ≈ **$11.50** off (10 × 1.14975 QC tax). Put the original file back afterwards (or leave stock — the module override is now the one in charge anyway).

**3. src/Core/Grid/Query/OrderQueryBuilder.php:**
Replace `src/Core/Grid/Query/OrderQueryBuilder.php` with the stock 1.7.8.7 copy. Clear cache. Open BO → Orders: the list must still load fast and the "New customer" column must still be correct. Definitive check from terminal: `bin/console debug:container prestashop.core.grid.query_builder.order` must print class `KhewaCoreChanges\Grid\Query\KhewaOrderQueryBuilder`.

**4. Product URL fix:**
Rename root `override/controllers/front/ProductController.php` to `.bak`, then reset the module (uninstall + install in Module Manager) — the file is recreated from the module. Then open both colliding product pages (product id **12705** and **750**) from their front-office URLs: each must show its own product.

**5. pdf/footer.tpl:**
Add a marker line `TEST123` to core `pdf/footer.tpl` AND to `themes/warehouse/pdf/footer.tpl`. Generate any invoice PDF from BO → Orders. The PDF must show the TPS/TVQ numbers and **no** `TEST123` — proof the copy inside `modules/khewacorechanges/pdf/` is the one used. Remove the markers.

**6. Customer service total blanked:**
Add a marker to the admin theme original: `admin556cvt7923/themes/default/template/controllers/customer_threads/helpers/view/view.tpl` (the edit is on line **74**, `'%total%' => ' '`). Open BO → Customer Service → any thread: the "order(s) validated for a total amount of" badge shows no amount and no marker — proof the copy in `override/controllers/admin/templates/` is used. Remove the marker.

**7 / 8. Total spent badge hidden:**
Remove (comment out) the rule at line **550** of `admin556cvt7923/themes/new-theme/public/theme.css` (`.column-total_spent .badge-success{display:none;}`), force-reload BO → Customers. The Sales/Total spent badges must **still be invisible** — the module's hook injects the same rule (view page source: `<style id="khewacorechanges-bo">`). Put the css rule back or leave it out, both fine.

**9. order_conf pickup message:**
Edit site `mails/en/order_conf.html` line **281**: delete the sentence "Please note that for pickup orders we will contact you when your order is ready." Place a test order (or resend an order confirmation). The email must **still contain** the sentence — it comes from `modules/khewacorechanges/mails/en/`. Also check a French customer gets "Nous vous contacterons lorsque votre commande sera prête." (that was missing before this module). Restore the file or leave it — it is no longer used for this email.

**10. Invoice discounts:**
Add a marker `TEST123` to core `pdf/invoice.total-tab.tpl` and to the theme copy. Download the invoice PDF of an order that used a fixed-amount/gift-card discount. Totals must be correct (Total Discounts, Total Tax lines present) and no `TEST123` — the module's copy wins. Remove the markers.

**11. Disabling mail send:** Not done — nothing to test. Just never rename `modules/mailalerts_old/` back.

**12. Remove email:** Not done — nothing to test (BO setting).

**13. Extra Small:** Not done — nothing to test (backend data).

**14. Remove Specific References:**
Behaviour: open any product page in the front office — no "Specific References" section (Ean13/Isbn/Upc) below "In stock". Update-proof test: in `themes/warehouse/templates/catalog/_partials/product-details.tpl` lines 70-77, remove the `{* ... *}` Smarty comment markers so the old block is active again — the section must STILL not appear, because the hook hands the template an empty list. Put the comments back.

**15. "Free" shipping label:**
Behaviour: front office, add a product with free shipping to the cart — the cart popup and checkout summary must show an empty shipping value, not the word "Free". Update-proof test: replace `themes/warehouse/templates/checkout/_partials/cart-summary-subtotals.tpl` with the stock version (from the fresh 1.7.8.7 classic theme) — "Free" must STILL not appear, because `hookActionPresentCart` blanks the value before the template sees it. Put the file back (or Apply). The `nofilter` mail partial stays copy-based.

**16. Email alert module:**
Behaviour: make a POS till sale — employees must get **no** "new order" email; place a normal online order — the email must still arrive. Update-proof test: replace `modules/ps_emailalerts/ps_emailalerts.php` with the stock vendor file (no pos_cart code in it), make a POS sale — still **no** employee email, because the module blocks it at `Mail::send` level. Put the file back (or Apply). The snapshot copy additionally protects the "Notify me" button fix.


---

## What code was added, in which file

All new code lives inside `modules/khewacorechanges/`. Nothing outside the module was edited by hand — files outside it are only *generated* (overrides on install) or *copied* (managed files on Apply).

| File in the module | What is in it |
|---|---|
| `khewacorechanges.php` | The module itself. `install()` = adopt existing root overrides (backup + let PS regenerate them) → register the 2 hooks → deploy managed files. `hookDisplayBackOfficeHeader()` = the total-spent CSS (#7/#8). `hookActionEmailSendBefore()` = redirects `order_conf` to the module's mails (#9). `MANAGED_FILES` map + `deployManagedFiles()` / `pullManagedFile()` / `getManagedFilesStatus()` = the Apply/Pull/status machinery. `getContent()` = the configuration page. |
| `override/classes/CartRule.php` | Copy of `getContextualValue()` with your tax fix (#2). PrestaShop installs it into root `override/classes/` on module install. |
| `override/controllers/front/ProductController.php` | Copy of the hand-made URL-fix override (#4). Installed into root `override/controllers/front/` on module install. |
| `override/classes/pdf/HTMLTemplate.php` | Makes every PDF template resolve from `modules/khewacorechanges/pdf/` first (#5, #10). Installed into root `override/classes/pdf/` on module install. |
| `pdf/footer.tpl`, `pdf/invoice.total-tab.tpl`, `pdf/invoice.product-tab.tpl` | The customized PDF templates themselves (#5, #10), served via the override above. |
| `config/services.yml` | Re-declares the Symfony service `prestashop.core.grid.query_builder.order` to point at the module's class (#3). |
| `src/Grid/Query/KhewaOrderQueryBuilder.php` | Full copy of your fast Orders-grid query builder (#3), renamed/namespaced; the `ini_set('display_errors')` and dead `__`-prefixed methods were removed. |
| `mails/en|fr|qc/order_conf.html + .txt` | Your customized order confirmation emails (#9). The FR pair got the pickup sentence added (it was missing on the site). |
| `files/theme/…` | Byte-for-byte copies of the customized theme-level files: pdf footer + invoice tabs (#5, #10), the two "Free"-label cart templates and the `nofilter` mail partial (#15). No new code — snapshots for Apply. |
| `files/root/…` | Byte-for-byte copies: admin customer-thread `view.tpl` (#6), `ps_emailalerts.php` (#16), the 4 RockPOS files (#1). No new code — snapshots for Apply. |
| `views/templates/admin/configure.tpl` | The configuration page UI: managed-files table with identical/differs/missing states, Apply/Pull buttons, Re-apply all, overrides status. |
| `CORE_CHANGES.md`, `UPDATE_SAFETY.md` | Documentation (this file). |
