# Project Overview

This is a **PrestaShop 1.7.8.5** installation for Khewa. Most work in this repo concerns two custom modules:

1. **hspointofsalepro** (RockPOS) — the Point of Sale module by Hamsa Technologies. Version 4.2.7.
2. **khewareports** — internal Khewa Reports & Quick Actions module.

Other modules are touched occasionally, but unless the user says otherwise, assume any new chat is about one of the two above.

## Default assumption when starting a chat

When the user opens a new conversation without naming a module, the request is most likely about **hspointofsalepro** or **khewareports**. If it is ambiguous, ask which one — do not guess and start editing the wrong module.

## Module locations

- POS: [modules/hspointofsalepro/](modules/hspointofsalepro/)
  - Admin controllers: [modules/hspointofsalepro/controllers/admin/](modules/hspointofsalepro/controllers/admin/) (e.g. `AdminRockPosCommon.php`, `AdminRockPosManage.php`, `AdminRockPosSales.php`)
  - Front controllers: [modules/hspointofsalepro/controllers/front/](modules/hspointofsalepro/controllers/front/) (e.g. `sales.php`, `searchCron.php`)
  - Classes: [modules/hspointofsalepro/classes/](modules/hspointofsalepro/classes/) — prefixed `Pos*` (PosCart, PosCustomer, PosPayment, PosReceipt, …)
  - Views: [modules/hspointofsalepro/views/](modules/hspointofsalepro/views/) (templates, css, js)
  - Overrides: [modules/hspointofsalepro/override/](modules/hspointofsalepro/override/)
  - Main file: [modules/hspointofsalepro/hspointofsalepro.php](modules/hspointofsalepro/hspointofsalepro.php)
- Reports: [modules/khewareports/](modules/khewareports/)
  - Admin controllers: [modules/khewareports/controllers/admin/](modules/khewareports/controllers/admin/) (`AdminKhewaReportsQuickActionController.php`, `AdminKhewaReportsReportsController.php`)
  - Classes: [modules/khewareports/classes/](modules/khewareports/classes/)
  - Views: [modules/khewareports/views/](modules/khewareports/views/)
  - Main file: [modules/khewareports/khewareports.php](modules/khewareports/khewareports.php)

## Related/companion modules in this repo

These show up around POS work — touch only when relevant:

- [modules/khewabackend/](modules/khewabackend/)
- [modules/khewamails/](modules/khewamails/)
- [modules/ordersexportsalesreportpro/](modules/ordersexportsalesreportpro/)
- [modules/reportsale/](modules/reportsale/)

## Platform context

- PrestaShop 1.7.8.5 — follow PS 1.7 conventions: `ObjectModel`-based classes, `ModuleAdminController` / `ModuleFrontController`, Smarty templates (`.tpl`), hooks registered in the module's main `install()`.
- PHP entry points expect the PS bootstrap (`config/config.inc.php`) — do not run module files standalone.
- Admin folder is obfuscated: `admin556cvt7923/`. Use this when constructing admin URLs in examples.
- Core overrides live in [override/](override/) at the project root; module-specific overrides live inside each module's `override/` subfolder and are copied on install.

## Conventions

- Match the existing style of the file being edited — class prefixes (`Pos*` for POS, `AdminKhewaReports*` for reports), Smarty syntax in `.tpl`, and existing JS patterns under each module's `views/js/`.
- Do not run `composer install`, `npm install`, or any build steps unless asked — this is a live site checkout.
- Do not bump module version numbers in `config.xml` unless explicitly asked.
- `.zip` files alongside module folders (e.g. `hspointofsalepro.zip`, `khewareports.zip`) are packaged builds — leave them alone unless told to rebuild.

## Trello / issue references

Commit messages sometimes reference Trello cards (e.g. `https://trello.com/c/...`). When a bug fix lands, include the Trello link in the commit message in that style if the user provides one.

## Workflow notes

- Production-style checkout — be conservative with destructive operations. Confirm before deleting files, dropping tables, or running migrations.
- When editing a controller, also check whether a matching template under the module's `views/templates/` needs updating.
