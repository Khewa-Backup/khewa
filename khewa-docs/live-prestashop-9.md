# Live shop on PrestaShop 9.2: what was done

## Where things are

- Code: `khewa-updates/live/`
  - This folder is a git worktree of the `khewa` repo, on branch `live-prestashop-9`. It isn't pushed.
- Database: `khewa_live` in AMPPS MySQL (user `root`, password `mysql`).
- Shop: http://localhost:8083/dev-prestashop/khewa-updates/live/
- Admin: http://localhost:8083/dev-prestashop/khewa-updates/live/admin556cvt7923/
- The `khewa` repo site stays on AMPPS, port 80, PHP 7.4. Nothing there was changed.

Branch commits, oldest first:
1. `live shop code from 22 Sep backup plus repo changes up to 10 Oct`
2. `update to PrestaShop 9.2.0`
3. `move out 1.7 overrides and ps_metrics so 9.2 loads`
4. `rename modules that break 9.2 even when switched off to <name>_pre_9.2`

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

## Modules updated

| Module | Before | Now | Result |
|---|---|---|---|
| hspointofsalepro (POS) | 4.2.7, broke the whole site on 9.2: PHP 8 can't read line 1267 (`a ? b : c ? d : e`), and it uses `PrestaShopAutoload`, which was removed in PS 8 | 5.8.1 (from `khewa-updates/module files/v5.8.1-hspointofsalepro.zip`), switched on | Works. These load with no errors: shop pages, admin dashboard, POS › Manage, and the till screen with all its script files. A cash test sale went through (see below). The old 4.2.7 files are kept in `modules/hspointofsalepro_pre_9.2`. |
| thegiftcard | 1.18.1 | 1.18.3 (from `module files/v1.18.3-thegiftcard.zip`), switched on | Works. There were no upgrade steps between 1.18.1 and 1.18.3. |
| paypal | 6.7.1, already the newest (PS 9 support since 6.5.0) | 6.7.1, one line fixed | `AdminPayPalSetupController.php` line 301: `\Tools::jsonEncode(...)` changed to `json_encode(...)`. That was the only removed call left. |

How POS was updated:
1. The zip was unpacked into `modules/hspointofsalepro`.
2. POS's own upgrade steps ran through PrestaShop's module functions (`Module::needUpgrade`, `initUpgradeModule`, `runUpgradeModule`), so 4.2.7's data moved to 5.8.1. Two steps were applied: 4.2.8 and 5.6.0.
3. `bin/console prestashop:module upgrade` couldn't be used. It loads every module and crashes on `iqitextendedproduct`.

The database backup from just before this is `backups/khewa_live-before-pos-5.8.1.sql.gz`.

## POS (RockPOS): separate job

Getting POS fully working on 9.2 is its own job. Where it stands:
- **Done:** updated to 5.8.1. The admin pages and the till screen open.
- **Done:** a cash test sale went through the till's own requests (add product, complete). It made order 34244 (reference `UIESLVUMY`, $6.32, guest customer), which opens in Admin › Orders. The order modules that are switched on (thegiftcard, paypal, stripe_official, ets_superspeed, ps_emailalerts and others) ran with no errors. **This is a local test order.**
- **Done:** add to cart works on the shop.
- **Not tested:** a full shop checkout. The only switched-on payment options are PayPal and Stripe, and both need their live online services.

What was holding POS back, and what was done:

| Module | Problem | Done for now | What to ask the client |
|---|---|---|---|
| canadapostlabels | Its 5 Canada Post carriers made every cart, POS carts included, load the module, and it crashed | **Its 5 carriers are switched off** (ids 377, 378, 383, 384, 430; `ps_carrier.active = 0`). Customers can't pick Canada Post shipping until it's replaced. | A 9.x version from ZH Media |
| kbetsy | Still uses `Tools::encrypt` (Etsy order import) and `Tools::displayPrice` (admin orders list) | Switched off | A fix from Knowband (4.0.4 claims 9.x support) |
| mailchimppro | PS 9 support unknown; it runs when orders and carts change | Switched off | The newest version, or its Addons page |

Canada Post carriers switched off (all run by the `canadapostlabels` module):

| id | Name |
|---|---|
| 377 | Canada Post (Tracked Packet Int'l) |
| 378 | Canada Post (Xpresspost International) Insured with Tracking |
| 383 | Canada Post (Expedited Parcel USA) Insured with Tracking |
| 384 | Canada Post (Tracked Packet USA) |
| 430 | Canada Post (Expedited) Insured with Tracking |

Still on, because they don't use the module: 382 "Canada Post - (Small Packet Int'l)" and 432 "Canada Post (Standard)".

To switch the 5 back on: `UPDATE ps_carrier SET active=1 WHERE id_carrier IN (377,378,383,384,430);`

## Modules switched off

Each of these crashed a page on 9.2, so it's switched off. The files and the data are still there.

| Module | What broke | Error |
|---|---|---|
| kbetsy | Etsy order import, admin Etsy orders list (switched off so it can't affect POS sales) | `Tools::encrypt()` and `Tools::displayPrice()` were removed |
| mailchimppro | Nothing seen yet; switched off so it can't affect POS sales | PS 9 support not confirmed |
| ps_metrics | Whole site, cache clear | `LoggerAdapter::emergency()` doesn't match the new `Psr\Log` interface. **Folder renamed to `ps_metrics_pre_9.2`.** |
| directlabelprintproduct | Every admin page | `addJquery()` was removed |
| cronjobs | Every admin page | `Tools::encrypt()` was removed |
| iqitextendedproduct | Admin dashboard | Constant `_PS_PROD_IMG_DIR_` was removed |
| bulkactions | Admin › Catalog › Categories | `setMedia()` call on the admin controller no longer exists |
| amazzingfilter | Admin › Catalog › Products (it's also the shop's product filter) | `setMedia()` call on the admin controller no longer exists |
| canadapostlabels | Shop home page, Admin › Orders › Carts. **Also add to cart, even while switched off:** PrestaShop still loads it to price its Canada Post carriers (`Cart::getPackageShippingCostFromModule`). Fixed for now by switching those 5 carriers off (see the POS section). | `Tools::encrypt()` was removed; class `PrestaShop\PrestaShop\Adapter\ObjectPresenter` was moved |
| featuredproducts | Shop home page (now and then) | Random products with no price break the product presenter (`formatPrice(null)`) |
| pm_crosssellingoncart | Shop home page | `prepareMultipleProductsForTemplate()` gets `false` instead of a list |
| iqitelementor | Shop home page (Elementor layout) | `Hook::getRetroHookName()` was removed |
| iqitmegamenu | Shop menu | `Tools::jsonDecode()` was removed |
| iqitlinksmanager | Shop footer links | `Tools::encrypt()` was removed |
| iqitthemeeditor | Admin › Theme editor | Admin controller method `l()` was removed |
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

With the Warehouse theme modules off (Elementor, mega menu, links, slider), the shop works but looks plainer.

**To switch a module back on** (replace `NAME`):
```sql
INSERT IGNORE INTO ps_module_shop SELECT z.* FROM zz_module_shop_off z JOIN ps_module m USING(id_module) WHERE m.name='NAME';
DELETE z FROM zz_module_shop_off z JOIN ps_module m USING(id_module) WHERE m.name='NAME';
UPDATE ps_module SET active=1 WHERE name='NAME';
```
Then clear the cache by emptying `live/var/cache/`. For a renamed folder, also remove `_pre_9.2` from its name.

`zz_module_shop_off` is a table I added. It keeps the shop rows of every module I switched off.

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
- `update-to-8.2.8.log`, `update-to-9.2.0.log`: update tool output
