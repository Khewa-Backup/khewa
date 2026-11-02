# Live shop on PrestaShop 9.2: what was done

## Where things are

- Code: `khewa-updates/live/`
  - This folder is a git worktree of the `khewa` repo, on branch `live-prestashop-9`. It isn't pushed.
- Database: `khewa_live` in AMPPS MySQL (user `root`, password `mysql`).
- Shop: http://localhost:8084/dev-prestashop/khewa-updates/live/
- Admin: http://localhost:8084/dev-prestashop/khewa-updates/live/admin556cvt7923/
- POS till: http://localhost:8084/dev-prestashop/khewa-updates/live/en/pos
- The `khewa` repo site stays on AMPPS, port 80, PHP 7.4. Nothing there was changed.

`git log` on the branch lists every step. Code changes and database changes are written down below, because database changes don't show in git.

## How to start it

The live shop does **not** run through AMPPS on port 80. It needs PHP 8.5, so it has its own small web server on port 8084.

1. Start MySQL. Use the AMPPS app as usual, or run:
   ```
   cd /Applications/AMPPS/apps/mysql && bin/mysqld_safe --defaults-file=etc/my.cnf &
   ```
2. Start PHP 8.5. It's a background service on port 9085 and usually starts with the Mac.
   ```
   brew services start php
   ```
   **PHP version (2 Nov 2026):** the shop runs on **PHP 8.5.12**, the newest release. That release has the October security fix for the "PHP2Shell" break-in flaw (CVE-2026-109618/109619). A second flaw (CVE-2026-115560) has no PHP fix yet, so `session.upload_progress.enabled` is off in `/opt/homebrew/etc/php/8.5/conf.d/zz-khewa-security.ini`. PrestaShop 9.2 supports PHP 8.1 to 8.5.
   - Checked before switching: every PHP file was scanned with 8.3 and 8.5. No file that works on 8.3 fails on 8.5; 8.5 only adds "deprecated" warnings, which change nothing. Shop, admin and POS pages were compared on both, and a POS cash sale was done on 8.5.
   - PHP 8.3.36 is still installed but switched off (`brew services start php@8.3` and port 9083 in the web server config to go back).
   - **The live server needs the same: PHP 8.5.12 (or at least 8.3.36), and upload progress off.**
3. Start the port-8084 web server:
   ```
   /Applications/AMPPS/apps/apache/bin/httpd -f /Users/hello/khewa-php83-apache/httpd.conf -k start
   ```
   To stop it, run the same command with `-k stop`.
   If it won't start, check that nothing else uses port 8084 (`lsof -iTCP:8084 -sTCP:LISTEN`). The shop moved from 8083 to 8084 on 2 Nov 2026 because a Docker container (llama.cpp) took 8083.

The web server's errors go to `/Users/hello/khewa-php83-apache/logs/error_log`. Admin errors also go to `live/var/logs/prod-<date>.log`.

## Modules updated or fixed for 9.2

Every change in a vendor module's code is marked in the code with a comment starting `Khewa PS 9.2`. **A future update of that module from its vendor will overwrite these changes**, so check each one again after updating.

| Module | Version | What was wrong on 9.2 | What was done | Tested |
|---|---|---|---|---|
| hspointofsalepro (POS) | 4.2.7 → **5.8.1** | 4.2.7 broke the whole site (PHP 8 syntax, `PrestaShopAutoload` removed) | Updated from `module files/v5.8.1-hspointofsalepro.zip`, with its own upgrade steps 4.2.8 and 5.6.0 run. Old files kept in `modules/hspointofsalepro_pre_9.2`. | Admin pages, till screen, 2 cash test sales |
| canadapostlabels (Canada Post, ZH Media) | 4.1.1 (no newer version) | `Tools::encrypt`, `Tools::displayPrice`, `Carrier::getCarrierNameFromShopName` removed; `ObjectPresenter` moved; manifests page used removed `l()`; tracking page list needs `currentIndex`; label PDF merge used PHP's removed `each()` | Fixed in its code (9 files). The secret key keeps the 1.7 value, so existing links still work. The 5 Canada Post carriers are switched back on. | Live rates at checkout, admin rate quote, all 9 admin pages + settings, order page panel, tracking lookup, merging 3 real label PDFs |
| kbetsy (Etsy, Knowband) | 4.0.4 (newest) | `Tools::encrypt`, `Tools::stripslashes`, `Tools::displayPrice` removed; advanced stock check removed | Fixed in its code (3 files). Also added 2 admin tabs the module defines but that were never installed (Return Policy, Customers Listing). | All 20 admin pages + settings |
| mailchimppro (Mailchimp) | 3.1.6 | Nothing | No change needed | 22 admin pages + settings (read real data), cart, checkout and a POS sale with it on |
| paypal (202 ecommerce) | 6.7.1 (newest) | 1) **PayPal is not shown at checkout on the live site either.** Its files are 6.7.1, but on the live server its upgrade never ran (the live database said 5.4.2). 6.x needs the account secret encrypted, and the live secret was still the old plain 5.x value. 2) Five old 5.x admin pages (2021 files) no longer work: Setup, Customize checkout, Help, Installment, Get credentials. | 1) The upgrade ran during the 9.2 update. The secret was encrypted the same way PayPal 6.x saves it (`ToolKit::encrypt`). **PayPal now shows at checkout.** 2) Their menu entries are hidden; PayPal 6.x uses Modules › PayPal › Configure instead. One harmless line fix in one of those old files (`jsonEncode` → `json_encode`). | Checkout shows "Pay with Paypal", settings and logs pages |
| thegiftcard (Keyrnel) | 1.18.1 → **1.18.3** | Choosing a gift card amount failed: 9.2 rejects the gift card variants' ISBN values (see "Catalog ISBN problem" below) | Updated from `module files/v1.18.3-thegiftcard.zip`. Cleared ISBN on the gift card product (4523) and its 23 variants only; old values saved in table `zz_giftcard_isbn_backup`. | Gift card page, picking $20 and $50, preview PDF, add to cart |
| amazzingfilter (Amazzing Filter, the product filter on category pages) | 3.2.6 (no newer version) | Admin › Catalog › Products crashed (`setMedia()` called on the new product page). Also: `Tools::encrypt` and `Tools::link_rewrite` removed (cron link, product links), `addColorsToProductList` removed (filter updates on the shop page would crash), the autoloader class moved (settings page, Overrides tab would crash), the attribute class is now `ProductAttribute` (its cache was not cleared when attributes change). The old product-page script does not work on the new product page. | Fixed in its code (7 files). The cron link keeps its old secret, so it still works. When a product's combinations are added, changed or removed on the new product page, the product is now re-indexed on the server at the end of that save. PHP 8.2+ warnings about added properties are switched off for its own classes. Module is on again. | Category pages (EN and FR): picking categories and colours, price slider, pages, sorting, links with filters, reload. Best sales page filter, search, home, product page, add to cart. Admin: settings page (all 15 tabs), edit and save a template, save settings, clear cache, re-index all products in all 3 shops, Catalog › Products list and product edit page, Catalog › Categories. Re-index after a product save and after a combination change (run directly) |
| Warehouse theme + its modules (iqit, ThemeForest) | 4.5.5 → **4.8.2** (the PrestaShop 9 version) | Elementor, mega menu, links, extended product, Revolution slider and the blog crashed (`Tools::encrypt`, `Tools::jsonDecode`, `Hook::getRetroHookName`, `_PS_PROD_IMG_DIR_` removed). Theme editor admin page crashed (`l()` removed). | Followed the vendor path 4.5.5 → 4.7.2 → 4.8.2 from `module files/themeforest-…zip` and `theme_4_8_2.zip`: new theme files, new module files copied over the old ones (their translations, uploads and generated CSS kept), each module's own upgrade steps run. Revolution slider 5.4.7.6 → 6.2.22.5 (folder replaced, `uploads/` kept). Khewa's own theme changes put back (list below). Small fix in 4 admin files: `$this->l(` → `$this->module->l(` (marked `Khewa PS 9.2`). Theme editor CSS made again from the saved settings (same as pressing Save). All 7 modules switched back on. New 4.8.2 modules (iqitproductflags, iqitproductvariants, iqitcustomwidget) are copied but not installed. | Home (EN, FR, and shops 2 and 3), menu, footer links, mobile 375px home + menu, category page with the filter (colour filter: 176 → 3), product page, add to cart, cart, checkout step 1 (guest and logged in), search box suggestions + results, blog list, customer login, account, credit slip codes page. Admin: Theme editor, Elementor pages, Content on hooks, Mega menu, Links manager, Revolution slider, Blog posts/categories/settings, product edit › Modules › extended product. No new PHP errors. |
| wpfooterslider (logo slider in the footer, shops 2 and 3) | 2.0.1 → **2.1.0** | Shops 2 (ENTREPOT) and 3 (Kisisam) came out blank: `Tools::encrypt()` removed. Dragging slides to change their order failed: PS 9 blocks direct calls to `.php` files inside `modules/` (403). | Updated from `module files/v2.1.0-wpfooterslider.zip` (no upgrade steps in the zip; uploaded logos, `config*.xml` and `translations/qc.php` kept). Fixed in `wpfooterslider.php`: the secret key is made the old 1.7 way, and the slide order is now saved through the module's own settings page. 2.1.0 adds a "Display heading" switch; it is left off, so the look is the same. | Home EN/FR of shops 2 and 3 (6 logos each), shop 1 not changed. Admin: settings page, save settings, edit and save a slide, drag order saved and put back |
| featuredproducts (home page product sliders) | 3.1.1 (same as the zip) | Home page crashed now and then: some random products have a default variant that is not in the current shop, so 9.2 finds no price (`formatPrice(null)`) | Fixed in `classes/SliderProduct.php`: products and their default variant are read for the current shop only, and the list is never `false`. | Every product of the 5 active sliders shown one by one in all 3 shops, EN and FR: 0 errors (731 each). Home EN/FR (120 slides with prices). Admin: slider list, edit, save (a deleted category id was dropped from slider 2's list on save; put back) |
| pm_crosssellingoncart (products under the cart) | 2.4.7.1 (same as the zip) | Every page crashed: the product query asked for `meta_keywords`, a column removed in 9; `Tools::displayPrice()` removed; an empty setting crashed on PHP 8; `.size()` removed in jQuery 3; admin product search used `ajax_products_list.php`, removed in 9 | Fixed in its code (6 files: `pm_crosssellingoncart.php`, `views/js/front.js`, `views/js/pm_crosssellingoncart.js`, `views/js/admin17.js`, `views/templates/admin/_configure/helpers/form/form.tpl`, `views/templates/admin/core/support.tpl`). Product search now runs inside the module settings page. Its calls to the Addons / Presta-Module sites and the logo and ads loaded from presta-module.com are switched off. | Cart EN/FR: 11 cards shown, add one to cart from the block. Product page, home pages of all shops. Admin: settings page, product pictures, product search, add a product (not saved), save |
| an_wishlist (wish list) | 2.2.8 (same as the zip) | Every page crashed: `addJquery()` removed. Also `ajaxDie()` removed (add/remove button) and `meta_keywords` column removed (admin Products tab) | Fixed in its code (3 files: `an_wishlist.php`, `controllers/front/ajax.php`, `classes/an_wish_products.php`). The wish list now reads the current shop's product data. | Category and product pages (hearts shown), heart as a visitor (asks to log in), add, wish list page EN/FR with price, wish list in cart, remove. Admin: Settings, Products and Customers tabs, save |
| bulkactions (Handy bulk actions) | 1.3.0 (same as the zip) | Admin › Catalog › Categories crashed: `setMedia()` does not exist on the new admin pages. Its actions also posted to a wrong address on these pages (404), and `Tools::displayPrice()` was removed (set price). | Fixed in `bulkactions.php`. | On a test category set: set parent, copy to parent, add and restrict group access. On a test product: add to / remove from category, set default category, set price, add / remove feature value, replace text. On a test customer: add to / remove from group. Settings page. All test data deleted. Not working: the old combination tools on the product edit page (PS 9 has a new product page with its own bulk edit for combinations). |

Database changes in this section (not in git):
- Canada Post carriers 377, 378, 383, 384, 430 switched back on.
- Etsy tabs `AdminEtsyReturnPolicy` and `AdminEtsyCustomersListing` added.
- PayPal: `PAYPAL_EC_SECRET_LIVE` encrypted. Menu entries `AdminPayPalSetup`, `AdminPayPalCustomizeCheckout` and `AdminPayPalHelp` hidden (`active = 0`).
- Gift card product 4523: ISBN cleared.
- amazzingfilter switched back on (rows moved back from `zz_module_shop_off` to `ps_module_shop`, `active = 1`). Its product index (`ps_af_index`) was rebuilt from its settings page.
- Warehouse theme update: module versions in `ps_module` raised by each module's upgrade steps (iqitthemeeditor 4.8.2, iqitelementor 1.2.4, iqitmegamenu 4.2.0, iqitextendedproduct 1.2.0, iqitadditionaltabs 1.3.0, iqitcompare 1.1.3, iqitcookielaw 1.0.2, iqitcrossselling 1.1.0, iqitfreedeliverycount 1.4.2, iqitpopup 1.1.2, iqitreviews 1.0.5, iqitwishlist 1.1.3, revsliderprestashop 6.2.22.5). These steps also moved hooks from `Header` to `displayHeader`, added a few hooks and admin tabs, and added new settings (theme editor mobile menu `iqitthemeed_mm_*`, `sm_tiktok`, `IQITCROSSSELLING_NBR` = 10, `IQITCROSSSELLING_HIDE_OOSP` = 0, `iqitextendedproduct_hook` = modal, `iqit_elementor_inst_token` emptied). Revolution slider: `ps_revslider_options` columns renamed, old tabs removed, table `ps_revslider_backup_slides` added (there are no sliders).
- New mobile menu text colours (`iqitthemeed_mm_main_tab_color`, `_arrow_icon_color`, `_accordion_tab2_color`, `_submenu_title_color`, `_submenu_text_color`) set to `#dcd3e0`, Khewa's old menu text colour, so the text shows on Khewa's black menu.
- iqitelementor, iqitmegamenu, iqitlinksmanager, iqitextendedproduct, revsliderprestashop, ph_simpleblog, ph_relatedposts switched back on (rows moved back from `zz_module_shop_off`, `active = 1`).
- wpfooterslider: `ps_module.version` set to 2.1.0 (the zip has no upgrade steps). Its new setting `WP_FOOTER_SHOWHEAD` is not saved, so the heading stays hidden as before.
- featuredproducts, pm_crosssellingoncart, an_wishlist, bulkactions switched back on (rows moved back from `zz_module_shop_off`, `active = 1`).


### Khewa's own Warehouse theme changes, put back on 4.8.2

Found by comparing the old theme and modules with the stock 4.5.5 files. Each one is marked `Khewa` in the code. **A new Warehouse version from the vendor will remove them again.**

- "Vouchers" is called "Credit slip codes" (account menu, account page, page title).
- Cart, side cart and checkout: a shipping line with no price in it (like "Free") shows no value.
- Newsletter block: "Subscribe to Newsletter" title added; GDPR box under it hidden.
- GDPR: the consent box is not shown, consent is sent as a hidden field; label tag left open as before.
- Product lists and product page: stock badge hidden.
- Product page: brand and reference moved below the add-to-cart part; specific references table (ISBN, UPC…) hidden.
- Contact block: map icon links to Khewa on Google Maps (module iqitcontactpage).
- `custom.css`: newsletter button colour, social icons spacing.
- `theme.css`: logo width 142px, menu tab max width, no arrow before footer links.
- New for 4.8.2 (to keep the old look, in `custom.css`): newsletter and "Follow us" side by side in the footer; h4 titles keep the old size on wide screens.
- Not carried: a commented-out block in the language selector and two whitespace edits (they changed nothing).

Things seen while testing the Warehouse update, not changed:
- Shops 2 (ENTREPOT) and 3 (Kisisam) show a blank page (even for an admin; normal visitors get the maintenance page). Cause: module `wpfooterslider` (not part of the theme) calls the removed `Tools::encrypt()`. **Fixed later: wpfooterslider updated to 2.1.0 and fixed (see the table above).** With it off for a moment, both home pages showed fine with the 4.8.2 theme. Shop 2 also uses an old purple test colour file from the vendor (`iqitthemeeditor/views/css/custom_s_2.css`), same as before.
- The home page "New Products" block is empty: the Elementor block shows module `newprodslider`, which is not switched on for any shop (same on the old live site).
- Admin product edit page shows the word "Array" at the very top. It is not from the theme modules (still there with them off).
- There are no blog posts and no Revolution sliders in the database, so related posts and sliders show nothing.

## What was NOT tested, on purpose

The local database holds the client's real accounts, so nothing was done that changes something outside this computer:
- **Canada Post:** creating labels, returns, manifests and end-of-day transmit. These create real shipments that cost money.
- **Etsy:** anything that talks to Etsy. It isn't connected anyway: there's no access token, and the last sync was in June 2021.
- **Mailchimp:** sending data. For the cart, checkout and POS tests the API key was blanked, then put back.
- **PayPal:** clicking "Pay with Paypal", which creates a real PayPal order.

## Test orders left in the local database

- 34244 (`UIESLVUMY`) and 34245 (`TALUPUPRK`): POS cash sales of $6.32 each, guest customer.
- 34246 (`WHPIFDLIT`): POS cash sale, $28.74. Its return was opened but not finished.
- 34247 (`FHEGATPIK`): POS cash sale with a $5 discount, $22.13, then returned with a credit slip (voucher `V2008C186P34247`).
- 34248 (`LVUKVATIN`): POS cash sale, $28.74, done on PHP 8.5.
- 34250 (`MLBQXDEKV`): POS cash sale, $28.74, khewacorechanges check.
- 34251 (`DKMBEQJMT`): POS cash sale with a $5 discount (cart rule 2023), $22.99, khewacorechanges check.

## POS (RockPOS)

POS 5.8.1 works on 9.2: the till opens, and cash sales complete with every fixed module switched on. Nothing is blocking it any more.

### Khewa's own POS changes, put back on 5.8.1

Every Khewa change in the old 4.2.7 files was put into the 5.8.1 files. Where 5.8.1 already had the same code, nothing was needed. Each change is marked `// Khewa` in the code. **A new RockPOS version from the vendor will remove them again.**

- `views/js/apps/sales.js`: receipt and cart changes (prices without tax, subtotal after discount, tax lines, discount types Voucher / Gift Card / Credit Slip). The file is minified. The readable copy and the script that builds it are in `khewa-docs/pos/`.
- `views/templates/front/sales.tpl`: loads the Khewa `sales.js` with a fixed version number, so browsers don't keep the old one.
- `hspointofsalepro.php`: labels (Full Refund, Credit Slip) and the order discount without tax.
- `controllers/front/sales.php`: discount checks and discount info for the receipt, extra payment line for voucher / gift card / credit slip, "CA Standard Rate" first in the tax list, quick-sale products get state 9, daily clean-up of quick-sale products off, loading an old order keeps its cart, credit slip codes use the letter P and no tax, refund date saved on the order.
- `classes/PosProduct.php`, `PosTax.php`, `PosPaymentModule.php`, `PosSalesSummary.php`, `order/PosOrderPayment.php`, `controllers/admin/AdminRockPosManage.php`: tax group fallback, tax rounding down, `isRock` flag for other modules, the day summary's tax columns, payment fields accept any value, PDF opens in the browser.

Tested on 9.2: login, barcode scan, cash sale, $5 discount sale, receipt data, return with credit slip, day summary.

## khewacorechanges module (installed)

`modules/khewacorechanges` is installed (2026-11-02, shop 1). The 9.2 update put every core file back to stock, so this module is now the only thing that carries Khewa's core changes. Each item was checked on 9.2 and proven by switching the module part off: see the module's `VERIFICATION.md`, section "PrestaShop 9.2".

What works (all checked): $10 tax-excluded voucher takes $10 off; POS $5 discount takes $5 before tax ($5.75 with tax, like the old shop; was $6.61); Orders list fast with a correct New customer column and filter; product URLs; PDF footer TPS/TVQ and Khewa invoice totals; customer-service total hidden; "Total spent" badge hidden; pickup sentence in order_conf; core "Product out of stock" mail and POS "new order" mail blocked (web orders still alerted); customer-service mails get `{link}`; specific references hidden; "Free" shipping label hidden; POS cash and discount sales.

New on 9.2: the gift card fix (old commit `edd95ec6b`) is now in the module too (item #18). Stock 9.2 took $64.39 off for a $60 gift card; now it takes $60 and the order still keeps its full tax.

Fixed in the module while installing: a `vendor/autoload.php` (without it the back office gave a 500 error), the cart totals (Cart `newCalculator()` override, merged into RockPOS's `override/classes/Cart.php`), an Order override for the tax lines, install no longer deletes RockPOS's `Cart.php`, the customer-service thread page template, and the two Warehouse cart templates the module keeps (were the old 4.5 files). Details in the module's `UPDATE_SAFETY.md`.

Good to know:
- Disabling the module does not switch off its Orders grid and customer-service mail services (9.2 loads services of every installed module). Uninstall it for that.
- Merging into RockPOS's `Cart.php` removes the comments in that file (PrestaShop does this). The code is the same.
- Not working / not tested: nothing open. A gift card bigger than the products total without tax gives a "Total (Tax excl.)" of $0.00 on the invoice while the tax line shows the full tax (same as the old shop).

## Catalog ISBN problem (needs a decision)

PrestaShop 9.2 checks ISBN values much more strictly than 1.7.
- **All 5,255 products and 6,193 variants that have an ISBN fail the 9.2 check.** Almost all of them are just a copy of the product's UPC (for example `754000003621`).
- Anything that saves those products through PrestaShop's normal save will fail with "Property ... isbn is not valid". That can include admin product edits and modules that update products.
- Possible fixes: clear ISBN where it equals UPC (UPC stays), or keep the old, looser ISBN rule with a small override. It's the client's choice. Only the gift card product was changed.

## Modules switched off

Each of these crashed a page on 9.2, so it's switched off. The files and the data are still there.

| Module | What broke | Error |
|---|---|---|
| ps_metrics | Whole site, cache clear | `LoggerAdapter::emergency()` doesn't match the new `Psr\Log` interface. **Folder renamed to `ps_metrics_pre_9.2`.** |
| rc_pgtagmanager (Google Tag Manager) | **Cart and checkout pages came out completely blank** | Constant `_PS_PRICE_COMPUTE_PRECISION_` was removed. The error was swallowed while the page rendered, so nothing showed in the logs. |
| directlabelprintproduct | Every admin page | `addJquery()` was removed |
| cronjobs | Every admin page | `Tools::encrypt()` was removed |
| wkhelpdesk | Admin help desk pages | Admin controller method `l()` was removed |
| wkinventory | Admin › Barcodes and Stocktake pages | `Translate::getAdminTranslation()` was removed |
| bestkit_log | Admin log pages | Admin controller method `l()` was removed |
| ntbackupandrestore | Admin backup page | `Tools::encrypt()` was removed |
| wktrash | Admin restore history page | `Tools::encrypt()` was removed |
| ps_facebook | Its admin page | Can't reach `billing-api.distribution.prestashop.net`. **This is a network problem, not a 9.2 code problem.** |

The same list, one name per line, is in `backups/modules-switched-off.txt`.

`iqitthemeeditor` (Warehouse theme settings) stayed on the whole time: switching it off makes **every category page blank**, because the theme's templates use its `add_url_param` modifier. Its admin page works again since the Warehouse 4.8.2 update.

The Warehouse theme modules (Elementor, mega menu, links, slider, blog) are on again since the Warehouse 4.8.2 update.

**To switch a module back on** (replace `NAME`):
```sql
INSERT IGNORE INTO ps_module_shop SELECT z.* FROM zz_module_shop_off z JOIN ps_module m USING(id_module) WHERE m.name='NAME';
DELETE z FROM zz_module_shop_off z JOIN ps_module m USING(id_module) WHERE m.name='NAME';
UPDATE ps_module SET active=1 WHERE name='NAME';
```
Then clear the cache by emptying `live/var/cache/`. For a renamed folder, also remove `_pre_9.2` from its name.

`zz_module_shop_off` is a table I added. It keeps the shop rows of every module I switched off.

**A page that comes out completely blank** with nothing in the logs is usually a template error swallowed during rendering, like the Tag Manager and theme editor cases above.

## Database changes the update tool missed

The update tool that came with the shop (autoupgrade 7.6.5) had an old copy of the 9.2.0 database step. I took the steps from the newer tool (7.6.6) and ran the missing ones with that tool's own functions:

- **9.2.0:**
  - `cart_rule`: `date_to` can be empty; new `total_quantity` column
  - `image_type.image_fitment` column. Without it, every page with images crashed.
  - `extra_property_definition` tables
  - `order_return_detail.cancelled` and `order_return_state.is_cancelling_return` columns
  - `shipment.deleted` column
  - more product `condition` values
  - new hooks, feature flags and admin tabs
- **9.1.5:** the `actionNotFound` and `displayOrderDetailProductLine` hooks
- **9.0.0:** `ps_900_init_feature_value_positions()`

The shop's update tool is now 7.6.6. The old one is in `backups/modules-moved-out/autoupgrade-7.6.5/`.

## Other things changed

- **1.7 override files moved out of `live/override/`** to `backups/override-1.7/`, because 9.2 can't start with them. The 33 core files that were edited directly on the live server were also replaced by the update. The old versions of both are in commit 1 and in the backup.
- **Overrides now in `live/override/`:** `classes/Cart.php` from POS 5.8.1 (installed by POS itself).
- **Not a module problem:** Admin › Advanced › Extra property definitions (a new 9.2 page) fails with "Illegal mix of collations". The new 9.2 tables use `utf8mb4_unicode_ci`, and the shop's older tables use `utf8mb4_general_ci`.
- **Local-only settings:**
  - the shop address is `localhost:8084`
  - https is off
  - emails are off (`PS_MAIL_METHOD = 3`)
  - `.htaccess` points to the local path; the server copy is in `backups/live-server-config/`

## Backups (`khewa-updates/backups/`)

- `live-code-2026-10-16.tar.gz`: live code before the update, with the repo changes after 22 Sep already applied
- `khewa_live-2026-10-16.sql.gz`: database before the update
- `khewa_live-after-9.2-before-sqlfix.sql.gz`: database after the update, before the missed 9.2 database changes
- `khewa_live-before-pos-5.8.1.sql.gz`, `khewa_live-before-giftcard-paypal-carriers.sql.gz`, `khewa_live-before-module-fixes.sql.gz`: database before each round of module work
- `update-to-8.2.8.log`, `update-to-9.2.0.log`: update tool output

## Security setup for the live upload (to discuss)

Why: in Aug 2026 someone logged into the admin with a real account and uploaded a module (`prestaeditor`) that let them change any file. An admin who can upload modules can run any code; PrestaShop can't stop that by design. So the protection has to stop the login, or limit what a backdoor can do. To do on the live server when 9.2 goes up:

1. **Stop the login**
   - 2-factor login for every admin account.
   - Strong, unique passwords (start with account #1).
   - Admin folder (`admin556cvt7923/`) only reachable from your IPs, via `.htaccess`.
2. **Fewer powerful accounts:** staff get limited profiles. Only 1–2 SuperAdmins can install modules. Check employee #19 "Suport Support".
3. **Read-only files:** the web server can't write to core and module folders. Writing is only switched on for a planned update, then off again. A backdoor then can't edit files like `AuthController.php`.
4. **Turn off dangerous PHP functions** in php.ini: `exec`, `system`, `shell_exec`, `passthru`, `proc_open`, `popen`. A backdoor then can't run server commands. Test first: some modules (backup tools, for example) may need them.
5. **Firewall:** Cloudflare or the host's WAF in front of the shop.
6. **File-change alerts:** get a message the same day a `.php` file is added or changed, not two months later.
7. **PHP:** 8.5.12 (or at least 8.3.36), with `session.upload_progress.enabled = Off` (see "How to start it").
