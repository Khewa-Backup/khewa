# Staff login code (module khewatwofactor)

This module adds a second step to the back office login.
After the right password, the shop emails a 6-digit code to the staff member.
The back office stays closed until that code is typed in.
Customers are not touched. Only staff.

## What it does

- The code works for 10 minutes, and only once.
- After 5 wrong tries the code is dead. Click "Send a new code".
- "Send a new code" works once every 60 seconds, and at most 5 times an hour.
- The box "Trust this computer for 30 days" skips the code on that computer.
  A password change or a switched-off staff member ends that trust.
- Every code sent, every good code, every wrong code and every lockout is in
  Advanced Parameters > Logs. Look for "Staff login code".
- Settings: Modules > Khewa staff login code > Configure.
  There you can turn it on or off, set the trust days, and remove trusted computers.

## Before you install: make sure live email works

The code goes out by email. If email does not work, nobody can log in.

1. Go to Advanced Parameters > E-mail.
2. Check that email is turned on.
   In the database, `PS_MAIL_METHOD` must be `1` (PHP mail) or `2` (SMTP), not `3`.
   `3` means email is turned off.
3. Send a test email to yourself from that page. Check that it arrives.
4. Check that every staff member has a real email address that they can read.

## How to install

1. Upload `khewatwofactor.zip` in Modules > Module Manager > Upload a module.
   Or unzip it by FTP into `modules/`, so you get `modules/khewatwofactor/`,
   then click Install in the Module Manager.
2. The check is on right after the install.
   Your next page will ask for a code. Check your email.
3. Keep this page open, and know the ways to turn it off (below) before you start.

## Turn it off without the back office (cPanel or FTP)

Use any one of these three ways. Each works at once. No cache clearing is needed.

### Way 1: rename the module folder

1. In cPanel File Manager (or FTP), open `public_html/modules/`.
2. Rename the folder `khewatwofactor` to something else, for example `khewatwofactor-off`.
3. Log in. No code is asked.
4. To turn it back on, rename the folder back to `khewatwofactor`.

Deleting the folder works too. The back office keeps working.

### Way 2: add a file named DISABLE_2FA

1. In cPanel File Manager (or FTP), open `public_html/modules/khewatwofactor/`.
2. Make a new empty file named exactly `DISABLE_2FA` (capital letters, no dot, no ending).
3. Log in. No code is asked.
4. To turn it back on, delete the file `DISABLE_2FA`.

### Way 3: change one setting in phpMyAdmin

1. In cPanel, open phpMyAdmin and pick the shop database.
2. Click the SQL tab and run:

   ```sql
   UPDATE ps_configuration SET value = '0' WHERE name = 'KHEWA2FA_ENABLED';
   ```

   (If your table names do not start with `ps_`, use your own start.)
3. Log in. No code is asked.
4. To turn it back on, run the same line with `'1'` instead of `'0'`,
   or switch it on again in the module settings page.

## How to remove it

Modules > Module Manager > Khewa staff login code > Uninstall.
This removes its two tables and its settings. The back office keeps working.

## For a developer

- PrestaShop 9.2 does the back office login with Symfony security.
  The check is a Symfony `kernel.request` listener (priority 5, right after the
  firewall and PrestaShop's own session check). Every back office request goes
  through it: Symfony pages, old AdminControllers and admin ajax calls.
  Page views get the code page. Ajax and POST calls get a 403 JSON answer.
- A `LoginSuccessEvent` listener keeps the staff id out of the old admin cookie
  until the code is right. Some old scripts (like the admin file manager) trust
  that cookie alone.
- The hook `actionAdminControllerInitBefore` is a second check for old
  AdminControllers. PrestaShop 9.2 starts those while it matches the route,
  before the firewall.
- The listeners are in `config/admin/services.yml`. They are built so that a
  renamed or deleted module folder does not break the cached container.
- The code page lives at the module route `khewatwofactor_code`
  (`/modules/khewa-login-code`). Its controller is a core one.
- Only hashes are stored: the code (HMAC with the shop key) and the trust token
  (SHA-256). Codes are compared in constant time.
- Tables: `ps_khewatwofactor_code`, `ps_khewatwofactor_trusted`.
  Settings: `KHEWA2FA_ENABLED`, `KHEWA2FA_TRUST_DAYS`.
