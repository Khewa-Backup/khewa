# Data move log: live data into the 9.2 shop

This is the record of every data move, so the next one (go-live day) is the same steps again.
Plan and reasons: `data-move-plan.md`. Table list: `data-move-tables.txt`. Scripts: `data-move/`.

## How to run it again (go-live day)

1. Put the live dump in `khewa-updates/backups/` (for example `live-db-final.sql.gz`).
2. Run:
   ```
   cd live/khewa-docs/data-move
   bash merge.sh ../../../backups/live-db-final.sql.gz final > run-final.log 2>&1
   ```
3. Read `out-final/checks.txt` (see "What the checks must show" below).
4. Test in the browser: home, a category, a product, add to cart, checkout up to payment, admin order list, a POS sale.
5. Export for the server (see "Export for the server" below).

The script does, in order:
1. backup of `khewa_live` → `backups/khewa_live-before-merge-<label>.sql.gz`
2. loads the dump into a spare database `khewa_cutover` (about 1.5 minutes)
3. replaces every DATA table in `khewa_live` with the live one; columns are matched by name, new 9.2 columns get their defaults
4. `post-fixes.sql` (below)
5. rebuilds the filter index (`reindex-filter.php`) and empties `var/cache`
6. `checks.sh`: row counts, newest rows, last 7 days' totals, links to setup rows, new admins/modules, security scan

To undo: import `backups/khewa_live-before-merge-<label>.sql.gz` back into `khewa_live`.

## What is different between live (1.7.8) and our tables (9.2)

Found on 4 Nov by comparing the columns of all 251 DATA tables.
- **Columns only on live (dropped by 9.2, left behind):** `meta_keywords` on category, cms, cms category, manufacturer, product and supplier texts; `ps_orders.shipping_number`; `ps_product_attribute.quantity` and `location`. 9.2 dropped them too, so nothing is lost that 9.2 would keep.
- **Columns only in 9.2 (get their defaults, some are filled by post-fixes):** `product.unit_price`, `product_shop.unit_price`, `category.redirect_type` / `id_type_redirected`, `category_lang.additional_description`, `feature_value.position`, `cart_rule.total_quantity` / `minimum_product_quantity` / `id_cart_rule_type`, `cart_rule_product_rule_group.type`, `customer_message.id_product`, `order_payment.id_employee`, `order_return_detail.cancelled`.
- **About 60 columns are only wider in 9.2** (text → mediumtext, longer varchar, more enum values). Nothing to do.
- **Tables not on live:** `ps_product_attribute_lang` (rebuilt), `ps_cart_rule_type`, `ps_cart_rule_type_lang`, `ps_cart_rule_compatible_types` (new in 9.1; the first two are kept from here, the last is empty, as after the update).
- **Kept from here although on the DATA list:** `ps_feature_flag` (9.2 setup, not shop data).
- **Tables only on live, not moved:** `ps_attribute_impact`, `ps_order_slip_detail_tax`, `ps_referrer*` (dropped by 9.x), `ps_ewrelatedproducts*`, `ps_pwfavorites` (old modules, not in this shop).

## post-fixes.sql: what it does and why

A. The data changes the update tool makes from 1.7.8 to 9.2 (read from `modules/autoupgrade/upgrade/sql/*.sql`):
- 8.0.0: `unit_price` = price / unit price ratio
- 8.1.0, 8.1.1, 9.0.0: product redirect `''` / `404` → `default`
- 8.1.0: `product_attribute_lang` rows (one empty row per variant and language)
- 8.1.3: minimum quantity 0 → 1
- 8.1.7: stock reference NULL → ''
- 9.0.0: root category redirect `404`; feature value positions 0,1,2… per feature
- 9.2.0: cart rule `total_quantity` = uses left + uses in good orders

B. Our own data fixes:
- gift card product 4523 and its variants: ISBN cleared
- wkinventory: the 2021 stocktake (inventory #2) stays locked ("done", "stock updated", renamed)

C. Live values copied over our settings:
- `dlp_pb_auto_UPC_id` (Direct Label Print's next automatic UPC, so barcodes don't repeat) and `dymoPrinterIndex_dlpp`
- currency rates

**Checked on 4 Nov and not copied:**
- No new order states, carriers, groups, countries, taxes, zones, languages, shops, currencies or image types on live since 16 Oct.
- Invoice, delivery slip and credit slip numbers: PrestaShop takes the next number from the highest number in the copied invoice/slip tables, so they continue on their own. No counter changed on live.
- Gift card and loyalty counters: no change on live.
- Settings that changed on live but stay as here: Mailchimp report cache, PrestaShop Accounts login tokens, the update tool's last check, CSS/JS cache numbers.

## What the checks must show (`out-<label>/checks.txt`)
- "none: every data table has the same row count as live"
- newest order / customer / product / cart / invoice: the same on both sides
- last 7 days: the same number of orders and the same total
- stock, price and cart rule rows that differ: none
- data pointing at missing setup rows: 0 everywhere
- employees: only #1 and #2; no module that exists only on live
- security scan: only `ps_log.message b64blob` (as on 3 Nov), or each new hit is read by eye

## Export for the server
Database (MySQL-8-only collations changed so it imports on the server's MariaDB):
```
mysqldump -uroot -pmysql --single-transaction --quick --routines --triggers --no-tablespaces \
  --set-gtid-purged=OFF --column-statistics=0 --default-character-set=utf8mb4 --hex-blob khewa_live \
  | sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' | gzip > for-stage/<name>.sql.gz
```
Then on the server run `stage-settings.sql` (staging) or `live-settings.sql` (go-live).

Code zip (from inside `live/`; product pictures come from the live `img` folder, so `img/p` is left out):
```
zip -r -q -y ../for-stage/<name>.zip . -x '.git' '.git/*' 'khewa-docs/*' 'var/cache/*' 'var/logs/*' 'var/sessions/*' 'img/p/[0-9]*' '*.DS_Store'
```

**After the live `img` folder is copied onto the server:** PrestaShop 9 keeps category pictures differently from 1.7 (it needs a `<id>_thumb.jpg` next to each `<id>.jpg`). The update tool's step `ps_900_migrate_category_images()` must run once on the server's `img/c`, then thumbnails are rebuilt for anything missing. Also check that `img` has only picture and document files.

## Runs

### 4 Nov 2026: staging data (live dump of 3 Nov 19:37, `backups/live-db-2026-11-04.sql.gz`)
- Dump: phpMyAdmin export from MariaDB 10.5, 49 MB, 620 tables, version 1.7.8.7.
- Backup before: `backups/khewa_live-before-merge-2026-11-04.sql.gz`.
- **First try stopped at table `ps_featuredproducts`:** the column list was cut at 1,024 characters (MySQL's default limit for `GROUP_CONCAT`). Fixed in `merge.sh` (`group_concat_max_len`). The script also never overwrites an existing backup now, so a second run keeps the real "before" copy. The second run went through.
- Time: about 3 minutes (load 1.5 min, copy 1 min, the rest seconds).
- 246 tables copied; 3 kept from here, 1 rebuilt, 1 emptied (see list).
- Checks: every data table has the same row count as live. Newest order #35084 (3 Nov 16:00), customer 1263, product 18430, cart 39629, cart rule 2029, invoice 35039: the same on both sides. Last 7 days: 96 orders, $6,165.35 on both. No stock, price or voucher differences. No data pointing at missing order states, carriers, currencies, groups, countries or taxes.
- Filter index rebuilt: shop 1: 907 products, shop 2: 222, shop 3: 296.
- Security scan of 148 data tables (504 text columns): only `ps_log.message` b64blob (1 row), as on 3 Nov. Clean.
- Modules only on live (`welcome`, `psaddonsconnect`, `ps_buybuttonlite`, `ewrelatedproductspro`, `pwfavorites`): all were already there on 16 Oct and were left out of the 9.2 shop on purpose. Nothing new.
- Live's visit stats (`ps_connections`, `ps_guest`) only go back to 27 Oct (46,341 rows instead of 3.4 million), so the database is 46 MB packed instead of 100 MB. That matches live.
- Tested: home, product page (newest product), add to cart; admin orders, customers, products, cart rules, carts, gift cards, RockPOS pages, and order #35084 (products, invoice, totals). No new errors in the logs. The temporary test admin was deleted after; only admins #1 and #2 remain.
- Made: `for-stage/khewa_stage_9.2-live-data-2026-11-04.sql.gz` (46 MB) and `for-stage/khewa-stage-code-2026-11-04.zip` (653 MB, 97,750 files). The old 9.2 database file without live data moved to `backups/old-for-stage/`.
