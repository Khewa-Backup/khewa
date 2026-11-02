# Bestkit Admin Log

Audit module for PrestaShop 9. It records:

- changes made to legacy `ObjectModel` records in the back office;
- successful and failed employee login attempts;
- administration pages visited by authenticated employees.

## Version history

### 2.0.0

- Added PrestaShop 9 and PHP 8.1+ compatibility.
- Replaced `AdminLoginController` and `ObjectModel` overrides with supported hooks and Symfony Security events.
- Fixed strict-mode SQL errors in action, login-attempt, and visit logging.
- Added a schema migration from 1.8.1 and complete database cleanup on uninstall.
- Prevented credentials, tokens, and secret fields from being written to the audit log.
- Escaped stored audit values in Back Office templates.

After upgrading an already installed 1.8.x copy, clear the PrestaShop cache. If the old release had installed overrides, verify that no `bestkit_log` override block remains under the shop's global `override/` directory.
