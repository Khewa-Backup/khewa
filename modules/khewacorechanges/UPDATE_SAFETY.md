# Core changes — handled or not

Site version: PrestaShop 1.7.8.7. "Handled" = this module carries the change and re-applies it after an update. Details per item are in `CORE_CHANGES.md`, mechanism in `HOW_IT_WORKS.md`.

| # | Core change | Handled? |
|---|---|---|
| 1 | Rock (sales.php, paymentmodule, pospayment, main file) | **PARTIALLY** — those 4 files are snapshotted and restorable. Other customized RockPOS files (sales.js, sales.tpl, ExportSales.php, bundled Cart.php override) are NOT. A RockPOS update always needs manual merge. |
| 2 | classes/CartRule.php | **HANDLED** |
| 3 | src/Core/Grid/Query/OrderQueryBuilder.php | **HANDLED** |
| 4 | URL issue (duplicate link-rewrite) | **HANDLED** |
| 5 | Tax information in pdf/footer.tpl | **HANDLED** |
| 6 | Customer service guest khewa hidden | **HANDLED** |
| 7 | Customers > guest khewa total sales hidden | **HANDLED** |
| 8 | Total sales from customer account / orders list hidden | **HANDLED** |
| 9 | order_conf custom message | **HANDLED** |
| 10 | Invoice modified for discounts | **HANDLED** |
| 11 | Disabling mail send from core | **NOT HANDLED — nothing to handle.** The fix is the `mailalerts_old` folder rename. Just never rename it back. |
| 12 | Remove email (contact/store info block) | **NOT HANDLED — nothing to handle.** Back-office setting in the database, updates don't touch it. |
| 13 | Extra Small | **NOT HANDLED — nothing to handle.** Backend data, no code. |
| 14 | Remove Specific References | **NOT HANDLED.** Still unclear what this card refers to — waiting for confirmation. |
| 15 | Removed Free (shipping label) | **HANDLED** |
| 16 | Email Alert Module (ps_emailalerts) | **HANDLED** — as a whole-file snapshot; a ps_emailalerts update needs manual merge first. |
| 17 | Reinstate nathaliecoutou.com | **NOT HANDLED.** Not in this codebase (domain/hosting level). |

**Also not handled by this module:** the other pre-existing files in the root `override/` folder (Cart.php with the gift-card fix, Hook.php, Dispatcher.php, etc.) — this module only carries CartRule and ProductController. If the site is ever rebuilt from a fresh copy, the whole `override/` folder must be carried over manually.
