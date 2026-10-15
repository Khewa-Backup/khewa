# Presta File Editor

PrestaShop module for trusted Back Office administrators. File contents are shown encrypted and the editor unlocks only after a browser-console script is pasted.

## Install

Copy the `prestaeditor` folder into `modules/prestaeditor`, then install **Presta File Editor** in the Back Office module manager.

After install, open **Advanced Parameters -> Presta File Editor**.

The installer grants this module tab access to all employee profiles; any logged-in Back Office employee can open the editor.

## Features

- Browse from the PrestaShop root directory.
- Edit text files with UTF-8 / CP1251 handling.
- Console-unlocked XOR/base64 editor compatible with `FE_KEY`.
- Chunked encrypted save for larger files.
- Create files and folders.
- Delete files and empty folders.
- Search by filename and file contents.
- Search uses `grep` through `exec` when available, then complements results with PHP fallback.

## Console unlock

Paste the universal FE console script after opening a file. This module exposes `feCipher`, `feEditor`, `feForm`, `fePayload` and supports `a=save_chunk`/`a=save_finish` JSON `{ok:1}`.

Do not install this module for untrusted Back Office users.
