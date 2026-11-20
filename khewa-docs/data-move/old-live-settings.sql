-- Settings for the OLD 1.7 shop copy on this Mac (khewa-updates/old-live, database khewa_old_live).
-- Run on khewa_old_live right after copying the live 1.7 dump (khewa_cutover) into it.
-- It makes the copy safe to look at: nothing goes out to customers, payment companies or other services.
-- Table prefix: ps_

-- 1. Address: all 3 shops on the tunnel address
UPDATE ps_shop_url SET domain = 'khewa.shopiman.com', domain_ssl = 'khewa.shopiman.com', physical_uri = '/';
UPDATE ps_configuration SET value = 'khewa.shopiman.com' WHERE name IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL');

-- 2. Emails off (3 = never send)
UPDATE ps_configuration SET value = '3' WHERE name = 'PS_MAIL_METHOD';

-- 3. Staff: only #1 (developer) and #2 (client) can log in. The others stay, switched off.
UPDATE ps_employee SET active = 0 WHERE id_employee NOT IN (1, 2);

-- 4. Modules that talk to outside services or take real money: switched off.
--    Their shop rows are kept in zz_module_shop_off so they can be put back.
--    invrecaptcha too: Google's key only works on khewa.com, so on the tunnel address it would block every sign in.
CREATE TABLE IF NOT EXISTS zz_module_shop_off LIKE ps_module_shop;
INSERT IGNORE INTO zz_module_shop_off
  SELECT ms.* FROM ps_module_shop ms JOIN ps_module m ON m.id_module = ms.id_module
  WHERE m.name IN ('mailchimppro', 'kbetsy', 'rc_pgtagmanager', 'ps_facebook', 'ps_eventbus', 'ps_accounts',
                   'ps_mbo', 'ps_metrics', 'cronjobs', 'ntbackupandrestore', 'paypal', 'stripe_official',
                   'virtualmerchant', 'ps_checkout', 'canadapostlabels', 'autoupgrade', 'ordersexportsalesreportpro',
                   'smartargetmessagebar', 'gamification', 'invrecaptcha');
DELETE ms FROM ps_module_shop ms JOIN ps_module m ON m.id_module = ms.id_module
  WHERE m.id_module IN (SELECT id_module FROM zz_module_shop_off);
UPDATE ps_module SET active = 0 WHERE id_module IN (SELECT id_module FROM zz_module_shop_off);

-- 5. Payment test modes too, in case a module is switched back on
UPDATE ps_configuration SET value = '1' WHERE name IN ('STRIPE_MODE', 'PAYPAL_SANDBOX', 'VM_DEMO');

-- Check
SELECT id_shop, domain, domain_ssl, physical_uri, virtual_uri FROM ps_shop_url;
SELECT id_employee, email, active FROM ps_employee WHERE active = 1;
SELECT m.name, m.active FROM ps_module m WHERE m.id_module IN (SELECT id_module FROM zz_module_shop_off) ORDER BY m.name;
