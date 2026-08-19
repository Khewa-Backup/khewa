# Project Overview

This is a **PrestaShop 1.7.8.5** installation for Khewa. A lot of work in this repo concerns two custom modules:

1. **hspointofsalepro** (RockPOS) — the Point of Sale module by Hamsa Technologies. Version 4.2.7.
2. **khewareports** — internal Khewa Reports & Quick Actions module.

But this is not a hard restriction — the repo is a full PrestaShop install and any module, override, or core file may be the right place to work, depending on what the user is actually describing (e.g. a cart rule / checkout / payment bug can live in `stripe_official`, core `classes/`, or elsewhere).

## Default assumption when starting a chat

Only default to **hspointofsalepro** or **khewareports** when the user's description actually points there — POS/till behavior, receipts, RockPOS admin screens, or the Khewa Reports/Quick Actions admin screens. If the request names or clearly implicates a different module or area (e.g. "cart rule", "Stripe payment", "shipping calculation"), follow the evidence there instead — do not force it into one of the two default modules just because they're the usual ones. If it's genuinely ambiguous which module is involved, ask rather than guessing.

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

## Uploading to production / staging

The user uses the VSCode "SFTP" extension with two profiles defined in [.vscode/sftp.json](.vscode/sftp.json): `production` and `staging`. Both point at the same FTP host with different credentials and both use remote base path `/public_html`. Local project root maps directly to that remote path (e.g. `modules/khewareports/classes/Foo.php` → `/public_html/modules/khewareports/classes/Foo.php`).

When the user says something like:
- "upload this to production" / "push to prod" / "deploy to live" → use the `production` profile
- "upload to staging" / "push to stage" / "send to test" → use the `staging` profile

Claude cannot trigger the VSCode extension (no hotkey access). Instead, upload via `curl --ftp-create-dirs -T <local> ftp://<host><remotePath>/<relative> --user <user>:<pass>`, reading credentials from `.vscode/sftp.json`. Do not hard-code creds in commands that get logged.

**Rules every time:**

1. **List the files first.** Before any upload, show the user the exact list of local files about to be sent and the resolved remote paths. Wait for confirmation.
2. **Production always requires explicit confirmation, even mid-session.** A previous "upload to prod" approval does NOT cover later uploads. Ask each time. Staging can proceed once confirmed for the file list.
3. **Only upload files the user clearly intended.** Default to just the files edited in the current task. Never `find`/glob the whole tree.
4. **Never upload `.vscode/`, `.git/`, `.claude/`, `*.zip`, `node_modules/`, or `.DS_Store`.** These are in the SFTP extension's ignore list for a reason.
5. **Never commit `.vscode/sftp.json` to git** — it contains plaintext FTP credentials. If it's not already gitignored, flag it.
6. **After uploading, report back** which files went up to which profile so the user can verify on the server.

If `curl` upload fails (e.g. 530 auth), don't retry blindly — surface the error to the user; creds may have rotated.

### Always ask about uploading after edits

After completing any task that edited files inside `modules/`, `override/`, `controllers/`, `classes/`, `themes/`, `mails/`, `pdf/`, or other server-deployable folders, **always end the response by asking the user where to upload**:

> "Upload these changes to **staging**, **production**, or **skip**?"

Include the list of files that would be uploaded so the user can verify the scope before answering.

Exceptions (do NOT auto-ask):
- Edits only to local-only files: `CLAUDE.md`, `.vscode/*`, `.claude/*`, `.gitignore`, `MEMORY.md`, etc.
- Read-only tasks (research, explanations, diagnosing without editing).
- The user explicitly said "don't deploy" or "local only" for this task.

If the user picks production, still apply the standard production-confirmation rule above (re-list files, wait for "go").
