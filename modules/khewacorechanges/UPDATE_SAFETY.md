# What is safe to update, and what is not

Quick reference: for each thing you might update (PrestaShop core, a theme, a module), does `khewacorechanges` protect our customizations or not? "Safe" never means "do nothing" — it means the module can restore everything with the standard routine below, no manual merging.

**Standard routine after any update:** Modules → Khewa Core Changes → Configure → **Re-apply all**, make sure both Class overrides show *installed* (reset the module if not), then clear the cache. Details in `HOW_IT_WORKS.md`.

---

## SAFE to update — the module makes these independent

### PrestaShop core (e.g. 1.7.8.5 → 1.7.8.7)

Every core file we ever edited is now duplicated somewhere the upgrade does not touch, so the upgrade may freely overwrite the originals:

| Your old edit (gets overwritten by upgrade — that's fine) | What takes over |
|---|---|
| `classes/CartRule.php` (#2) | module override |
| `src/Core/Grid/Query/OrderQueryBuilder.php` (#3) | module service swap |
| `pdf/footer.tpl`, `pdf/invoice.total-tab.tpl`, `pdf/invoice.product-tab.tpl` (#5, #10) | theme `pdf/` copies (checked first by PrestaShop) |
| `mails/en|fr|qc/order_conf.html/.txt` (#9) | module `mails/` via mail hook |
| `mails/_partials/order_conf_product_list.tpl` (#15) | theme `mails/en/` copy (checked first) |
| root `override/controllers/front/ProductController.php` (#4) | module override (regenerated on install) |

### Admin theme (`admin.../themes/default` and `new-theme`)

| Old edit | What takes over |
|---|---|
| `customer_threads/helpers/view/view.tpl` — total blanked (#6) | copy in `override/controllers/admin/templates/` (checked before the admin theme) |
| `theme.css` — total-spent badge hidden (#7/#8) | CSS injected by hook |

### Front theme (warehouse) — safe WITH one click

The "Free" label templates and the theme `pdf/` + `mails/` copies (#5, #10, #15) live **inside** the theme folder, so a theme update wipes them — but **Re-apply all** puts them all back in seconds. No merging needed as long as the theme update didn't restructure those templates (glance at the cart page after re-applying).

---

## NOT independent — updating these still endangers your changes

The module only holds **whole-file snapshots** of these; it cannot merge. If you update the module below and then click Apply, you replace the vendor's *new* file with our *old* snapshot — losing whatever the update brought.

### hspointofsalepro (RockPOS) — ⚠ do NOT update blindly

- Snapshotted: `controllers/front/sales.php`, `classes/PosPaymentModule.php`, `classes/PosPayment.php`, `hspointofsalepro.php` (the four files from the Trello card).
- **Not snapshotted but also customized** (per git history): `views/js/sales.js`, `views/templates/front/sales.tpl`, `classes/ExportSales.php`, the refunds tab work, French translations, and the module-bundled `override/classes/Cart.php`. A vendor update overwrites those with no safety net here.
- Correct procedure for a RockPOS update: diff the vendor's new files against ours, merge our changes in by hand, then **Pull** each merged file into the module.

### ps_emailalerts — ⚠ same rule

- Snapshotted: `ps_emailalerts.php` (POS-alert skip + notify-me fix). A vendor update requires a manual merge, then Pull.

### Root `override/` folder — mostly NOT carried by this module

This module carries only `CartRule` and `ProductController`. The site has ~15 other pre-existing root overrides (`Cart.php` — including the gift-card fix, `Hook.php`, `Dispatcher.php`, `FrontController.php`, `AdminCartRulesController.php`, category/manufacturer/supplier controllers, …). A normal PS upgrade leaves `override/` alone, but a **fresh-install style migration must copy the whole `override/` folder over manually** — this module will not recreate those.

### Other things the module cannot help with

- **`modules/mailalerts_old/`** (#11): the fix *is* the folder rename. Never rename it back, never "reinstall" mailalerts. Nothing to update here — just don't touch it.
- **Email theme regeneration** (Design → Email Theme): regenerating emails is harmless — the module's `order_conf` still wins — but a *redesign* won't reach `order_conf` until someone regenerates it, re-adds the pickup sentence, and copies it into `modules/khewacorechanges/mails/`.
- **Database-level customizations** (#12 contact email hidden, #13 XS size, order states, cart rules): not files at all; upgrades don't touch them, and neither does this module.
- **Undocumented edits beyond the 17 Trello cards**: this module carries exactly what the cards + git archaeology surfaced. If other core edits exist that nobody wrote down, they have no protection. When in doubt before a big upgrade, diff the whole tree against a stock copy first.

---

## One-line summary

PrestaShop core, the admin theme, and (with one Re-apply click) the warehouse theme can be updated safely. **RockPOS and ps_emailalerts cannot** — their updates always need a manual diff-and-merge, and RockPOS has customized files this module doesn't even snapshot. The root `override/` folder and anything never documented remain your responsibility during a fresh-install migration.
