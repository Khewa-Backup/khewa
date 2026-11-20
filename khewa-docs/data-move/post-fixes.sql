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

SELECT 'post-fixes done' AS result;
