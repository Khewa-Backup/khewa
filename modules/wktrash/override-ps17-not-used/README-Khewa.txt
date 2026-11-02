Khewa PS 9.2: this folder was called "override". It is renamed so PrestaShop does not copy these files
into the shop's override/ folder when the module is switched on or reset. On 9.2 they would break the shop
(the Attribute class is ProductAttribute now) and could clash with other overrides.
The module now does the same job with hooks (see wktrash.php, search "Khewa PS 9.2").
Kept only for reference.
