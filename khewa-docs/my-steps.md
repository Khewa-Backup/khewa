# My steps (only what I do; Claude does the rest)

When a step says "tell Claude", stop there and wait until Claude says it's done.

---

## A. Today: staging with the live data (stage.khewa.com)

1. **Export the live database.** cPanel → phpMyAdmin → live database (`kbklzsah_newdatabase`) → Export → Custom → Format SQL, Compression **gzipped** → Go.
2. Put the file in `khewa-updates/backups/` as `live-db-2026-11-04.sql.gz`. **Tell Claude.**
   - Claude merges the live data into the 9.2 shop, tests it locally, then makes a new `for-stage/` database file and the code zip.
3. When Claude says ready: cPanel → **MultiPHP Manager** → `stage.khewa.com` → **PHP 8.5**.
   cPanel → **MultiPHP INI Editor** → `stage.khewa.com` → `session.upload_progress.enabled` = **Off**.
4. cPanel → **File Manager** → empty `stage.khewa.com/public_html/` (delete everything inside).
5. Upload the zip Claude gives you into `stage.khewa.com/public_html/` and **Extract** it there. Delete the zip afterwards.
6. **Database:** follow `for-stage/README-stage.md`:
   - empty the staging database
   - import the `.sql.gz`
   - run `stage-settings.sql` in phpMyAdmin → SQL
7. **Tell Claude.** Claude sets the staging files (database login, `.htaccess`, `robots.txt`, cache).
8. **Pictures:** File Manager → `public_html/img` (live) → **Copy** to `stage.khewa.com/public_html/img` (let it overwrite). **Tell Claude.** Claude checks there are only pictures in it and rebuilds missing thumbnails.
9. Give the client the link: `https://stage.khewa.com/`. Admin: `https://stage.khewa.com/admin556cvt7923/`.
   - On staging, don't create Canada Post labels or add cron jobs. Stripe is in test mode (card 4242 4242 4242 4242).

---

## B. Go-live day (khewa.com)

**Before you start:** have new passwords ready for the database, cPanel/FTP, admin #1 and admin #2.

1. **Close the shop:** live Admin → Shop Parameters → General → Maintenance → Enable shop: **No**. Add your IP and the client's IP to the maintenance IP list. Save.
2. **Full backup:** cPanel → **Backup** → download a full backup (or at least the home folder and the live database).
3. **Export the live database** again (same as A1), save it as `khewa-updates/backups/live-db-final.sql.gz`. **Tell Claude.**
   - Claude merges the final data, tests it, and gives you the final database file, `live-settings.sql` and the final zip.
4. **Keep the pictures:** File Manager → **move** `public_html/img` to the home folder as `~/img-live` (so it isn't deleted in the next step).
5. cPanel → **MultiPHP Manager** → `khewa.com` → **PHP 8.5**. **MultiPHP INI Editor** → `khewa.com` → `session.upload_progress.enabled` = **Off**.
6. File Manager → **empty `public_html/`** (delete everything inside; `img` is safe in `~/img-live`).
7. Upload the final zip into `public_html/` and **Extract** it. Delete the zip.
8. **Pictures back:** move `~/img-live` back to `public_html/img` (overwrite the one from the zip).
9. **Database:**
   - cPanel → MySQL Databases → change the live database user's **password**.
   - phpMyAdmin → live database → empty it (select all tables → Drop).
   - Import the final `.sql.gz` (phpMyAdmin Import, or cPanel Terminal as in `README-stage.md`, with the live database name and user).
   - Run `live-settings.sql` in phpMyAdmin → SQL.
10. **Tell Claude** (and give the new database password). Claude sets the live files (database login, `.htaccess`, `robots.txt`, cache), checks pictures and thumbnails, and runs the security scan on the server.
11. **Cron jobs:** cPanel → Cron Jobs → do the table in `live-prestashop-9.md`, section "Cron jobs for the live server". First delete every line you don't recognise.
12. **Passwords:**
    - change cPanel/FTP
    - change admin #1 and #2
    - create new staff accounts (the old ones were removed)
    - ask the client to change the **Virtual Merchant PIN** with Elavon/Converge
13. **Test with Claude:** one web order, one POS sale, the admin order list.
14. **Open the shop:** Admin → Maintenance → Enable shop: **Yes**.
15. Later, from the security checklist in `live-prestashop-9.md`: 2FA, admin folder open only to your IPs, firewall, file-change alerts.
