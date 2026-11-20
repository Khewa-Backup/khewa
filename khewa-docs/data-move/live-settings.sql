-- Settings for the REAL shop at https://khewa.com/ (go-live day).
-- Already applied to the database file made for go-live (for-live/khewa_live_9.2-*.sql.gz),
-- so it does NOT need to be run again on the server. It is kept here so the go-live file can be made again:
--   copy khewa_live into khewa_golive, run this on khewa_golive, export khewa_golive.
-- Values are the ones the old live shop had (read from its database on 20 Nov 2026).
-- Table prefix: ps_

-- 1. Shop address: all 3 shops on khewa.com, as on the old live shop
UPDATE ps_shop_url SET domain = 'khewa.com', domain_ssl = 'khewa.com', physical_uri = '/';
UPDATE ps_configuration SET value = 'khewa.com' WHERE name IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL');

-- 2. https on
UPDATE ps_configuration SET value = '1' WHERE name IN ('PS_SSL_ENABLED', 'PS_SSL_ENABLED_EVERYWHERE');

-- 3. Emails ON (SMTP, mail.khewa.com, the same login as the old live shop)
UPDATE ps_configuration SET value = '2' WHERE name = 'PS_MAIL_METHOD';

-- 4. Real payments: Stripe live mode, PayPal live (the live keys are the same as on the old shop)
UPDATE ps_configuration SET value = '0' WHERE name = 'STRIPE_MODE';
UPDATE ps_configuration SET value = '0' WHERE name = 'PAYPAL_SANDBOX';

-- 5. Switch back on the modules that were off only for the copies: Mailchimp, Tag Manager, Etsy.
--    Their shop rows come back from zz_module_shop_off.
INSERT IGNORE INTO ps_module_shop
  SELECT o.* FROM zz_module_shop_off o JOIN ps_module m ON m.id_module = o.id_module
  WHERE m.name IN ('mailchimppro', 'rc_pgtagmanager', 'kbetsy');
DELETE o FROM zz_module_shop_off o JOIN ps_module m ON m.id_module = o.id_module
  WHERE m.name IN ('mailchimppro', 'rc_pgtagmanager', 'kbetsy');
UPDATE ps_module SET active = 1 WHERE name IN ('mailchimppro', 'rc_pgtagmanager', 'kbetsy');
-- Left off on purpose (see live-prestashop-9.md): ps_metrics (breaks 9.2), ps_facebook,
-- smartargetmessagebar, ordersexportsalesreportpro.

-- Check
SELECT id_shop, domain, domain_ssl, physical_uri, virtual_uri FROM ps_shop_url;
SELECT name, value FROM ps_configuration
  WHERE name IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL', 'PS_SSL_ENABLED', 'PS_SSL_ENABLED_EVERYWHERE',
                 'PS_MAIL_METHOD', 'STRIPE_MODE', 'PAYPAL_SANDBOX');
SELECT m.name, m.active, COUNT(ms.id_shop) AS shops FROM ps_module m
  LEFT JOIN ps_module_shop ms ON ms.id_module = m.id_module
  WHERE m.name IN ('mailchimppro', 'rc_pgtagmanager', 'kbetsy') GROUP BY m.name, m.active;
