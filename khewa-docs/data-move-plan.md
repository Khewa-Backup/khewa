# Plan: bring the new live data into the 9.2 shop

## The problem
The 9.2 shop's database is the live database as it was on **16 Oct 2026**, upgraded to 9.2, plus our module and security work.
Since then the live shop (still PrestaShop 1.7.8) has kept going:
- new orders, carts and POS sales
- new customers and addresses
- new products (with images), and changed prices and stock
- new and used cart rules, vouchers, gift cards and credit slips
- customer messages and help desk tickets

On upload day the shop goes into maintenance for a few hours, and this data has to come across.

## Why not a module
This is a one-time move, done while the shop is closed. A PrestaShop module would run inside the shop it is changing, which is the wrong place. A **script run from the command line** is safer: it can be tested first, timed, and run again from the start if something fails.

## Why not "copy only the new rows"
- **Old rows changed too:** order statuses, customer details, stock, prices, how much of a gift card or voucher is left. "New rows only" would miss all of these.
- **Ids clash.** Our test sales here used ids that live has also used since (orders 34244–34262, carts, cart rules, customers). Copying live's new rows would hit the same ids.

## The way: data tables from live, setup tables from here
Every table falls into one of two kinds:
- **Data** (comes from live): orders and everything under them (details, taxes, payments, invoices, history, slips, returns, carriers, order cart rules), carts, customers, addresses, groups of customers, guests, products and everything under them (shop/lang rows, combinations, stock, prices, specific prices, categories of products, features, images, attachments, tags), cart rules and their limits, gift cards, POS sales tables (`ps_pos_*`), customer threads and messages, help desk tickets, loyalty points, stock movements, Etsy/Mailchimp sync tables, connection/stats rows.
- **Setup** (stays as it is here): modules and their versions, hooks, admin menu (tabs) and rights, admin accounts, theme and theme editor settings, mega menu, Elementor pages, module settings, carriers, taxes, payment setup, `ps_configuration`, and everything else we fixed for 9.2.

Each table is put on one list or the other, written down in a file (`data-move-tables.txt`). Tables nobody owns are checked by hand once.

### Steps on upload day
1. **Put live into maintenance.** Nothing new can come in.
2. **Dump the live database** (1.7.8 structure) and copy it here.
3. **Load the dump into a spare database** (`khewa_cutover`) and upgrade only its structure to 9.2. That means the same database steps the update tool ran, plus the 9.x steps it missed, which are already written down in the main doc ("Database changes the update tool missed"). Its data tables then have the 9.2 columns.
4. **Replace every "data" table in the 9.2 shop** with the one from `khewa_cutover`. Columns are matched by name; new 9.2 columns get their defaults. This also removes all our test orders, carts and customers.
5. **A few setup values come from live too**, because live keeps changing them: the next invoice / delivery slip / credit slip / order numbers, and the gift card and loyalty counters in `ps_configuration`. They are listed in the script.
6. **Redo the data fixes we made here**, since the data tables get replaced:
   - ISBN cleared on the gift card product 4523 and its variants
   - the 2021 stocktake (inventory #2) locked
   - any other data fix listed in the main doc's "Database changes" lists
7. **Files that go with the data:** the new product images (`img/p`), category images, customer uploads (`upload/`) and download files (`download/`) come from the live server. **The `img/` folder is moved by hand** (by you). Copy **only image and document types** (jpg, png, gif, webp, pdf, docx…), never `.php` or other scripts, because the attacker hid files before. After the copy, I check the folders for anything that isn't an image or document, then rebuild the image thumbnails for the new products if any are missing.
8. **Security check on the new data:** run the same database scan as on 3 Nov (hidden scripts, odd admin accounts, odd modules) and the file scan on the copied images.
9. **Checks before opening:**
   - The row count of every data table matches live.
   - The newest order, customer and product are the same as on live.
   - Order totals for the last 7 days match.
   - Stock spot-check on 10 products.
   - Gift card and voucher balances spot-check.
   - The next invoice number continues from live.
   - One POS sale, one web order up to payment, and the admin order list.
10. **Upload and open** (the upload steps are in the main doc).

### Practice run first
The day before, run the whole thing once with a fresh dump of live (the shop stays open; this is only to test). Fix what breaks and time each step. On upload day, the same script runs again on the final dump.

## What I need on upload day
- A dump of the live database at the moment maintenance starts (phpMyAdmin or cPanel export, or I make it if I get access).
- The `img/` folder copied by hand from the live server; access to copy `upload/` and `download/` (or those by hand too).
- About 1–2 hours of maintenance (based on the practice run).

## Open questions to settle in the practice run
- Which module tables hold "data" for modules that only exist here, or that changed version (wkinventory, wkhelpdesk, bestkit_log, updateproducts)? Each one is decided while making `data-move-tables.txt`.
- Customers' sessions and carts in progress at the moment of the switch are lost (normal for a maintenance window).
- If the cookie key is changed at the same time, every customer is logged out (fine during the switch). Customers with very old passwords may need a password reset.
