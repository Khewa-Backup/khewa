-- 20 Nov 2026: the local 9.2 shop (database khewa_live) is no longer on the tunnel.
-- It works only on this Mac at http://127.0.0.1:8087/. The tunnel now shows the old 1.7 shop (old-live).
-- Run: mysql khewa_live < local-only-settings.sql
-- Undo: restore backups/khewa_live-before-local-only.sql.gz, or run tunnel-settings.sql again.
-- Table prefix: ps_

UPDATE ps_shop_url SET domain = '127.0.0.1:8087', domain_ssl = '127.0.0.1:8087', physical_uri = '/';
UPDATE ps_configuration SET value = '127.0.0.1:8087' WHERE name IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL');
-- no certificate on 127.0.0.1, so plain http
UPDATE ps_configuration SET value = '0' WHERE name IN ('PS_SSL_ENABLED', 'PS_SSL_ENABLED_EVERYWHERE');

SELECT id_shop, domain, domain_ssl, physical_uri, virtual_uri FROM ps_shop_url;
SELECT name, value FROM ps_configuration WHERE name IN ('PS_SHOP_DOMAIN', 'PS_SHOP_DOMAIN_SSL', 'PS_SSL_ENABLED', 'PS_SSL_ENABLED_EVERYWHERE', 'PS_MAIL_METHOD', 'STRIPE_MODE', 'PAYPAL_SANDBOX');
