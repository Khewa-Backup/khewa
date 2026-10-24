# Live shop on PrestaShop 9.2: what was done

## Where things are

- Code: `khewa-updates/live/`
  - This folder is a git worktree of the `khewa` repo, on branch `live-prestashop-9`. It isn't pushed.
- Database: `khewa_live` in AMPPS MySQL (user `root`, password `mysql`).
- Shop: http://localhost:8083/dev-prestashop/khewa-updates/live/
- Admin: http://localhost:8083/dev-prestashop/khewa-updates/live/admin556cvt7923/
- POS till: http://localhost:8083/dev-prestashop/khewa-updates/live/en/pos
- The `khewa` repo site stays on AMPPS, port 80, PHP 7.4. Nothing there was changed.

`git log` on the branch lists every step. Code changes and database changes are written down below, because database changes don't show in git.

## How to start it

The live shop does **not** run through AMPPS on port 80. It needs PHP 8.3, so it has its own small web server on port 8083.

1. Start MySQL. Use the AMPPS app as usual, or run:
   ```
   cd /Applications/AMPPS/apps/mysql && bin/mysqld_safe --defaults-file=etc/my.cnf &
   ```
2. Start PHP 8.3. It's a background service on port 9083 and usually starts with the Mac.
   ```
   brew services start php@8.3
   ```
3. Start the port-8083 web server:
   ```
   /Applications/AMPPS/apps/apache/bin/httpd -f /Users/hello/khewa-php83-apache/httpd.conf -k start
   ```
   To stop it, run the same command with `-k stop`.

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

Database changes in this section (not in git):
- Canada Post carriers 377, 378, 383, 384, 430 switched back on.
- Etsy tabs `AdminEtsyReturnPolicy` and `AdminEtsyCustomersListing` added.
- PayPal: `PAYPAL_EC_SECRET_LIVE` encrypted. Menu entries `AdminPayPalSetup`, `AdminPayPalCustomizeCheckout` and `AdminPayPalHelp` hidden (`active = 0`).
- Gift card product 4523: ISBN cleared.

## What was NOT tested, on purpose

The local database holds the client's real accounts, so nothing was done that changes something outside this computer:
- **Canada Post:** creating labels, returns, manifests and end-of-day transmit. These create real shipments that cost money.
- **Etsy:** anything that talks to Etsy. It isn't connected anyway: there's no access token, and the last sync was in June 2021.
- **Mailchimp:** sending data. For the cart, checkout and POS tests the API key was blanked, then put back.
- **PayPal:** clicking "Pay with Paypal", which creates a real PayPal order.

## Test orders left in the local database

- 34244 (`UIESLVUMY`) and 34245 (`TALUPUPRK`): POS cash sales of $6.32 each, guest customer.

## POS (RockPOS)

POS 5.8.1 works on 9.2: the till opens, and cash sales complete with every fixed module switched on. Nothing is blocking it any more.

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
| iqitextendedproduct | Admin dashboard | Constant `_PS_PROD_IMG_DIR_` was removed |
| bulkactions | Admin › Catalog › Categories | `setMedia()` call on the admin controller no longer exists |
| amazzingfilter | Admin › Catalog › Products (it's also the shop's product filter) | `setMedia()` call on the admin controller no longer exists |
| featuredproducts | Shop home page (now and then) | Random products with no price break the product presenter (`formatPrice(null)`) |
| pm_crosssellingoncart | Shop home page | `prepareMultipleProductsForTemplate()` gets `false` instead of a list |
| iqitelementor | Shop home page (Elementor layout) | `Hook::getRetroHookName()` was removed |
| iqitmegamenu | Shop menu | `Tools::jsonDecode()` was removed |
| iqitlinksmanager | Shop footer links | `Tools::encrypt()` was removed |
| revsliderprestashop | Shop home page slider | `Tools::encrypt()` was removed |
| an_wishlist | Shop home page | `addJquery()` was removed |
| ph_simpleblog | Shop home page, Admin blog pages | `Tools::encrypt()` was removed |
| ph_relatedposts | Shop product page | `Tools::encrypt()` was removed |
| wkhelpdesk | Admin help desk pages | Admin controller method `l()` was removed |
| wkinventory | Admin › Barcodes and Stocktake pages | `Translate::getAdminTranslation()` was removed |
| bestkit_log | Admin log pages | Admin controller method `l()` was removed |
| ntbackupandrestore | Admin backup page | `Tools::encrypt()` was removed |
| wktrash | Admin restore history page | `Tools::encrypt()` was removed |
| ps_facebook | Its admin page | Can't reach `billing-api.distribution.prestashop.net`. **This is a network problem, not a 9.2 code problem.** |

The same list, one name per line, is in `backups/modules-switched-off.txt`.

**Switched back on, with one broken page:** `iqitthemeeditor` (Warehouse theme settings). Switching it off made **every category page blank**, because the theme's templates use its `add_url_param` modifier. So it stays on. Only its own Admin › Theme editor page fails (removed `l()`).

With the other Warehouse theme modules off (Elementor, mega menu, links, slider), the shop works but looks plainer. All 47 shop pages I could reach from the home, category and product pages, plus the customer account pages, load fully.

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
  - the shop address is `localhost:8083`
  - https is off
  - emails are off (`PS_MAIL_METHOD = 3`)
  - `.htaccess` points to the local path; the server copy is in `backups/live-server-config/`

## Backups (`khewa-updates/backups/`)

- `live-code-2026-10-16.tar.gz`: live code before the update, with the repo changes after 22 Sep already applied
- `khewa_live-2026-10-16.sql.gz`: database before the update
- `khewa_live-after-9.2-before-sqlfix.sql.gz`: database after the update, before the missed 9.2 database changes
- `khewa_live-before-pos-5.8.1.sql.gz`, `khewa_live-before-giftcard-paypal-carriers.sql.gz`, `khewa_live-before-module-fixes.sql.gz`: database before each round of module work
- `update-to-8.2.8.log`, `update-to-9.2.0.log`: update tool output
