-- Runs on khewa_live right after the live DATA tables are copied in (merge.sh step 4).
-- Part A repeats the data changes the 1.7.8 -> 9.2 update tool makes (from modules/autoupgrade/upgrade/sql/*.sql).
-- Part B repeats our own data fixes. Part C copies live setting values that live keeps changing.
-- Every statement is safe to run twice.
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ===== A. update-tool data changes =====
-- 8.0.0: new unit_price column filled from price / unit_price_ratio
UPDATE ps_product      SET unit_price = IF(unit_price_ratio != 0, price / unit_price_ratio, 0);
UPDATE ps_product_shop SET unit_price = IF(unit_price_ratio != 0, price / unit_price_ratio, 0);

-- 8.1.0 / 8.1.1 / 9.0.0: product redirect '' or '404' becomes 'default'
UPDATE ps_product      SET redirect_type = 'default' WHERE redirect_type IN ('', '404') OR redirect_type IS NULL OR redirect_type + 0 = 0;
UPDATE ps_product_shop SET redirect_type = 'default' WHERE redirect_type IN ('', '404') OR redirect_type IS NULL OR redirect_type + 0 = 0;

-- 8.1.0: new table product_attribute_lang (one empty row per variant and language)
TRUNCATE ps_product_attribute_lang;
INSERT INTO ps_product_attribute_lang (id_product_attribute, id_lang, available_now, available_later)
  SELECT pa.id_product_attribute, l.id_lang, '', '' FROM ps_product_attribute pa CROSS JOIN ps_lang l;

-- 8.1.3: minimum quantity 0 becomes 1
UPDATE ps_product                SET minimal_quantity = 1 WHERE minimal_quantity = 0;
UPDATE ps_product_shop           SET minimal_quantity = 1 WHERE minimal_quantity = 0;
UPDATE ps_product_attribute      SET minimal_quantity = 1 WHERE minimal_quantity = 0;
UPDATE ps_product_attribute_shop SET minimal_quantity = 1 WHERE minimal_quantity = 0;

-- 8.1.7: stock reference NULL becomes ''
UPDATE ps_stock SET reference = '' WHERE reference IS NULL;

-- 9.0.0: category redirect (new columns get 301 / 0; root categories 404)
UPDATE ps_category SET redirect_type = '404' WHERE is_root_category = 1;

-- 9.0.0: feature value positions, numbered 0,1,2... per feature in id order (ps_900_init_feature_value_positions)
UPDATE ps_feature_value fv JOIN (
  SELECT id_feature_value, ROW_NUMBER() OVER (PARTITION BY id_feature ORDER BY id_feature_value) - 1 AS pos
  FROM ps_feature_value) p ON p.id_feature_value = fv.id_feature_value
SET fv.position = p.pos;

-- 9.2.0: cart_rule.total_quantity = uses left + uses in orders that are not in error
UPDATE ps_cart_rule cr
LEFT JOIN (
  SELECT ocr.id_cart_rule, COUNT(*) AS quantity_used
  FROM ps_order_cart_rule ocr JOIN ps_orders o ON ocr.id_order = o.id_order
  WHERE ocr.deleted = 0
    AND o.current_state != (SELECT CAST(value AS UNSIGNED) FROM ps_configuration WHERE name = 'PS_OS_ERROR' LIMIT 1)
  GROUP BY ocr.id_cart_rule
) used ON used.id_cart_rule = cr.id_cart_rule
SET cr.total_quantity = cr.quantity + COALESCE(used.quantity_used, 0)
WHERE cr.quantity IS NOT NULL;

-- ===== B. our own data fixes =====
-- Gift card product 4523: ISBN cleared (9.2 rejects these ISBN values, so choosing an amount failed)
UPDATE ps_product           SET isbn = '' WHERE id_product = 4523;
UPDATE ps_product_attribute SET isbn = '' WHERE id_product = 4523;

-- wkinventory: the unfinished 2021 stocktake stays locked, so its old counts can never be pushed into stock
UPDATE ps_wkinventory SET name = 'Inventory 2021 (disabled, never applied)', done = 1, stock_updated = 1
WHERE id_inventory = 2;

-- ===== C. live values copied over our settings =====
-- Direct Label Print: next automatic UPC and EAN numbers (so new barcodes do not repeat), printer choice
UPDATE ps_configuration l JOIN khewa_cutover.ps_configuration c
  ON c.name = l.name AND c.id_shop <=> l.id_shop AND c.id_shop_group <=> l.id_shop_group
SET l.value = c.value, l.date_upd = c.date_upd
WHERE l.name IN ('dlp_pb_auto_UPC_id', 'dlp_pb_auto_ean_id', 'dymoPrinterIndex_dlpp', 'selectedDymoIndex_dlpp');

-- Currency rates
UPDATE ps_currency l JOIN khewa_cutover.ps_currency c ON c.id_currency = l.id_currency
SET l.conversion_rate = c.conversion_rate;
UPDATE ps_currency_shop l JOIN khewa_cutover.ps_currency_shop c ON c.id_currency = l.id_currency AND c.id_shop = l.id_shop
SET l.conversion_rate = c.conversion_rate;

-- ===== D. staff accounts from live (20 Nov 2026) =====
-- The 3 Nov cleanup left only #1 and #2 here. The shop's staff and the two POS tills still use their
-- live accounts, so every ACTIVE live account except #1 and #2 is copied with its live password.
-- Switched-off live accounts are not copied. Their start page is matched to the 9.2 menu by name.
-- (live has 0000-00-00 stats dates, so strict date checks are off for this part)
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
DELETE FROM ps_employee_shop WHERE id_employee NOT IN (1, 2);
DELETE FROM ps_employee WHERE id_employee NOT IN (1, 2);
INSERT INTO ps_employee (id_employee,id_profile,id_lang,lastname,firstname,email,passwd,last_passwd_gen,stats_date_from,stats_date_to,stats_compare_from,stats_compare_to,stats_compare_option,preselect_date_range,bo_color,bo_theme,bo_css,default_tab,bo_width,bo_menu,active,optin,id_last_order,id_last_customer_message,id_last_customer,last_connection_date,reset_password_token,reset_password_validity,has_enabled_gravatar)
SELECT e.id_employee,e.id_profile,e.id_lang,e.lastname,e.firstname,e.email,e.passwd,e.last_passwd_gen,e.stats_date_from,e.stats_date_to,e.stats_compare_from,e.stats_compare_to,e.stats_compare_option,e.preselect_date_range,e.bo_color,e.bo_theme,e.bo_css,
  COALESCE(nt.id_tab, 0),e.bo_width,e.bo_menu,e.active,e.optin,e.id_last_order,e.id_last_customer_message,e.id_last_customer,e.last_connection_date,NULL,NULL,e.has_enabled_gravatar
FROM khewa_cutover.ps_employee e
LEFT JOIN khewa_cutover.ps_tab ot ON ot.id_tab = e.default_tab
LEFT JOIN ps_tab nt ON nt.class_name = ot.class_name
WHERE e.active = 1 AND e.id_employee NOT IN (1, 2)
  AND e.id_profile IN (SELECT id_profile FROM ps_profile) AND e.id_lang IN (SELECT id_lang FROM ps_lang);
INSERT IGNORE INTO ps_employee_shop (id_employee, id_shop)
SELECT es.id_employee, es.id_shop FROM khewa_cutover.ps_employee_shop es
JOIN ps_employee e ON e.id_employee = es.id_employee
WHERE es.id_employee NOT IN (1, 2) AND es.id_shop IN (SELECT id_shop FROM ps_shop);
-- (20 Nov, the client's wish) the copied staff accounts are kept but switched OFF:
-- only #1 and #2 can sign in. Switch one back on in Advanced Parameters > Team.
UPDATE ps_employee SET active = 0 WHERE id_employee NOT IN (1, 2);
-- the client's own admin account (#2) keeps its live password too
UPDATE ps_employee l JOIN khewa_cutover.ps_employee c ON c.id_employee = l.id_employee
SET l.passwd = c.passwd, l.last_passwd_gen = c.last_passwd_gen WHERE l.id_employee = 2;

SELECT 'post-fixes done' AS result;
