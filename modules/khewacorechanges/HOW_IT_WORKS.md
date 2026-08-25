# How khewacorechanges works

Plain-language guide to what this module does, why it is built the way it is, and what to do after an update. The *list* of changes and their history lives in `CORE_CHANGES.md` — this file explains the *mechanism*.

## The problem in one paragraph

Over the years we fixed things by editing PrestaShop's own files (`classes/CartRule.php`, `pdf/*.tpl`, `src/...`), the admin theme, the front theme, and files inside third-party modules (RockPOS, ps_emailalerts). Every PrestaShop upgrade, theme update or module update ships a fresh copy of those files and silently throws our edits away. This module is the one place that keeps every edit, and puts it back.

## Three ways a change can be carried

PrestaShop offers different extension points depending on the kind of file. We use the most "official" one available for each change, because official mechanisms are the ones an upgrade is designed not to break.

### 1. Class overrides — `override/` folder (PrestaShop-native)

PrestaShop lets a module ship replacement methods for core classes and controllers. When the module is **installed**, PrestaShop copies them into the site's root `override/` folder and tags each method with a `* module: khewacorechanges` comment. When the module is **uninstalled**, PrestaShop removes them again.

| File in module | Replaces | Change |
|---|---|---|
| `override/classes/CartRule.php` | `CartRule::getContextualValue()` | #2 — fixed-amount discount tax handling |
| `override/controllers/front/ProductController.php` | `ProductController::init()` and friends | #4 — duplicate link-rewrite URL fix |

Why this is safe across upgrades: the root `override/` folder is not touched by a PrestaShop upgrade, and even if it were, reinstalling the module recreates it.

One detail worth knowing: the site already had a hand-made `override/controllers/front/ProductController.php`. PrestaShop refuses to install a module override when the same method is already overridden, so `install()` first backs that file up (to `modules/khewacorechanges/backup/<timestamp>/`) and removes it, then lets PrestaShop regenerate it from the module's copy. The content is identical; only the `module:` markers are new.

### 2. Hooks and Symfony services (PrestaShop-native)

Some changes don't need a file replaced at all — PrestaShop provides a plug-in point.

| Mechanism | What it does | Change |
|---|---|---|
| `hookDisplayBackOfficeHeader` | Injects one CSS rule (`.column-total_spent .badge-success{display:none}`) into every admin page, hiding the "Total spent" badge in the Customers list. Previously this rule lived in the admin theme's `theme.css`, which upgrades overwrite. | #7, #8 |
| `hookActionEmailSendBefore` | When PrestaShop is about to send the `order_conf` email, the hook points it at `modules/khewacorechanges/mails/<lang>/order_conf.*` instead of the site's `mails/<lang>/`. Those module copies carry the *"for pickup orders we will contact you when your order is ready"* sentence (EN, FR and QC). | #9 |
| `config/services.yml` | Re-declares the Symfony service `prestashop.core.grid.query_builder.order` so the back-office Orders list uses `src/Grid/Query/KhewaOrderQueryBuilder.php` — the fast "New customer" query. The core class is `final` so it can't be extended; the module ships a full copy with the fix applied. PrestaShop 1.7.8 loads every active module's `config/services.yml` after its own, so ours wins. | #3 |

Why this is safe across upgrades: nothing in the core tree is modified. As long as the module is installed and enabled, these are active.

### 3. Managed files — `files/` folder (copy-in-place)

For everything else — PDF templates, theme templates, admin templates, and files *inside other modules* — PrestaShop has no override mechanism. So the module keeps a "golden copy" of each file under `files/` and copies it into place:

- automatically on **install**, and
- whenever you click **Re-apply all** on the module's configuration page (Modules → Khewa Core Changes → Configure).

`files/theme/...` is deployed into **every active shop theme** (`themes/warehouse/...` today; the module reads the theme name from each shop, so it also works if the theme is renamed). `files/root/...` is deployed relative to the site root.

| Golden copy | Deployed to | Change |
|---|---|---|
| `files/theme/pdf/footer.tpl` | `themes/<theme>/pdf/footer.tpl` | #5 — GST/QST numbers + exchange text on receipts |
| `files/theme/pdf/invoice.total-tab.tpl`, `invoice.product-tab.tpl` | `themes/<theme>/pdf/` | #10 — invoice discount/tax totals |
| `files/theme/modules/ps_shoppingcart/ps_shoppingcart-content.tpl` | `themes/<theme>/modules/ps_shoppingcart/` | #15 — hide "Free" shipping label (cart popup) |
| `files/theme/templates/checkout/_partials/cart-summary-subtotals.tpl` | `themes/<theme>/templates/checkout/_partials/` | #15 — hide "Free" shipping label (checkout) |
| `files/theme/mails/en/order_conf_product_list.tpl` | `themes/<theme>/mails/en/` | #15 — `nofilter` on customization text in order emails |
| `files/root/override/controllers/admin/templates/customer_threads/helpers/view/view.tpl` | same path | #6 — total amount blanked in Customer Service thread view |
| `files/root/modules/ps_emailalerts/ps_emailalerts.php` | same path | #16 — no employee "new order" email for RockPOS till sales |
| `files/root/modules/hspointofsalepro/…` (4 files) | same paths | #1 — RockPOS customisations (see caveat below) |

Why the PDF/mail files go into the **theme** folder rather than back into `pdf/` or `mails/`: PrestaShop checks `themes/<theme>/pdf/` before `pdf/`, and `themes/<theme>/mails/en/` before `mails/_partials/`. So the theme copy wins, and a core upgrade (which replaces `pdf/` and `mails/`) can't undo it. A *theme* update could — that's what the Re-apply button is for.

**Backups:** before overwriting any live file that differs from the golden copy, the module saves the live version to `modules/khewacorechanges/backup/<date_time>/…`. That folder is git-ignored and should not be uploaded.

## The configuration page

Modules → Khewa Core Changes → Configure shows three panels:

1. **Managed files** — one row per deployed file with its state:
   - `identical` — live file matches the golden copy. Nothing to do.
   - `differs` — the live file changed. Either an update overwrote it (→ click **Apply** to push the golden copy back) or someone edited the live file on purpose (→ click **Pull** to refresh the golden copy from the live file, so the module carries the new version).
   - `missing` — the live file doesn't exist (e.g. a fresh theme). Click **Apply**.
   - **Re-apply all** pushes every golden copy at once.
2. **Class overrides** — whether each override is present in root `override/` with our marker. If one says `missing`, reset the module (uninstall + install).
3. **Hooks & services** — informational.

## What to do after…

- **A PrestaShop upgrade (e.g. to 1.7.8.7):** install/keep this module enabled, open its configuration page, click **Re-apply all**, then clear the cache (Advanced Parameters → Performance → Clear cache). Check the Class overrides panel shows both `installed`. Read the "Known limits" section below for the order_conf case.
- **A theme (warehouse) update:** Re-apply all.
- **A RockPOS / ps_emailalerts module update:** first *compare* — the vendor's new version may have moved the code our edits sit in. Re-applying blindly would replace the vendor's new file with our old snapshot. Diff, merge, then **Pull** the merged file into the module.
- **You edit a managed file directly (e.g. `sales.php`):** click **Pull** on that row so the module's copy is updated too. Otherwise the next Re-apply will revert your edit.
- **Setting up a brand-new site copy:** upload the whole `modules/khewacorechanges/` folder, install the module from Modules → Module Manager. Install does everything (overrides + managed files + hooks). Then clear cache.

## Uninstalling

Uninstalling removes the two class overrides and the hooks (so the CartRule tax fix, the product-URL fix, the hidden "Total spent" badge, the pickup sentence, and the fast Orders grid all revert to stock behaviour). Managed files are **left in place** on purpose — deleting them would put stock, broken files back on a live shop.

## Known limits / honest caveats

- **order_conf on 1.7.8.7.** The module serves its own `order_conf.html/.txt` (generated from the 1.7.8.5 mail theme). PrestaShop 1.7.8.7 regenerates `mails/<lang>/` from Twig mail themes; those regenerated files are simply ignored for `order_conf` because the hook redirects to the module. If the mail *design* is ever changed via Design → Email Theme, `order_conf` will keep the old look until the module copies are regenerated by hand (Design → Email Theme → generate, then copy the result into `modules/khewacorechanges/mails/<lang>/` and re-add the pickup sentence).
- **RockPOS golden copies are snapshots, not diffs.** There is no vendor-original to compare against, so the module keeps whole-file copies of the four customised files. `sales.php` is edited often — always **Pull** after editing it, and never Re-apply over a *newer* vendor version without merging first.
- **`ps_emailalerts.php` is also a whole-file snapshot** (the vendor file at the version currently installed, plus our two edits). Same rule: on a module update, merge, then Pull.
- **The two "Total sales hidden" changes are not guest-specific.** They hide the amount for *every* customer in those two admin screens, exactly as the original edit did.
- **Directly-edited core files were not reverted.** `classes/CartRule.php`, `src/Core/Grid/Query/OrderQueryBuilder.php`, `pdf/*.tpl` and `mails/<lang>/order_conf.*` on the live site still contain the old in-place edits. They are now redundant (the module provides the same behaviour) and harmless; the 1.7.8.7 upgrade will replace them with stock files, and the module takes over from there.
- **Install needs write access** to `override/`, `themes/<theme>/`, `modules/ps_emailalerts/`, `modules/hspointofsalepro/` and the module's own `backup/` folder.
