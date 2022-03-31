Module is installed in a regular way - simply upload your archive and click install.

CHANGELOG:
===========================
v 2.1.1 (March 1, 2019)
===========================
- [+] Optionally include taxes in combination price impacts
- [*] Sort {att_names_xx} by positions of attribute groups
- [*] Fixed issue with blocked text inputs on some laptops with non-English keyboards

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/configure.tpl

===========================
v 2.1.0 (February 5, 2019)
===========================
- [+] New pattern variable for references: {att_names_5}
- [*] Minor fixes

Files modified:
-----
- /bulkcombinationsgenerator.php
- /readme_en.pdf
- /views/templates/admin/reference-variables.tpl

===========================
v 2.0.1 (June 16, 2018)
===========================
- [*] Optimized performance for stores with many attributes
- [*] Minor fixes

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/js/back.js
- /views/templates/admin/available-items.tpl
- /views/templates/admin/configure.tpl

Files modified:
-----
- /views/templates/admin/dynamic-rows.tpl

===========================
v 2.0.0 (May 18, 2018)
===========================
- [+] Optionally update existing combinations without erasing them
- [+] Configure unit price impacts, Wholesale price impacts, Weight impacts, Min quantity for order
- [+] Configure references for new combinations basing on patterns with variables
- [+] Filter products by Manufacturers, Suppliers or specified IDs
- [*] Compatibility with PS 1.7
- [*] Improved admin interface
- [*] Misc improvements

Files modified:
-----
All files

===========================
v 1.7.0 (October 14, 2017)
===========================
- [*] Misc fixes and optimizations

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/js/back.js
- /views/templates/admin/configure.tpl

===========================
v 1.6.1 (May 29, 2016)
===========================
- [*] Don't force product references to new combinations
- [*] Don't duplicate combination references to other products

Files modified:
-----
- /bulkcombinationsgenerator.php

===========================
v 1.6.0 (May 29, 2016)
===========================
- [*] Clear colorlist cache after creating new combinations
- [*] Make sure combinations are processed for all shops

Files modified:
-----
- /bulkcombinationsgenerator.php

===========================
v 1.5.9 (April 3, 2016)
===========================
- [*] Fixed bug with large-sized temporary generation data. Writing permissions required.
- [+] Added documentation

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/templates/admin/configure.tpl
- /views/css/back.css
- /views/js/back.js

Files added:
- /documentation_en.pdf

===========================
v 1.5.8 (December 3, 2015)
===========================
- [*] Removed local files for saving temporary data, because of permission issues on some servers
- [*] Minor code optimizations

Files modified:
-----
- /bulkcombinationsgenerator.php

Directories removed:
-----
- /tmp

===========================
v 1.5.7 (November 10, 2015)
===========================
- [*] Fixed timeout errors for 1000++ combinations. Generation flow is now based on max combinations num

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/js/back.js

===========================
v 1.5.6 (October 27, 2015)
===========================
- [+] Keep or delete existing combinations during the regeneration process

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/templates/admin/configure.tpl
- /views/css/back.css

===========================
v 1.5.5 (October 22, 2015)
===========================
- [+] Clone combinations from one product to other selected products
- [*] Minor code optimizations
- [*] PSR-2

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/templates/admin/configure.tpl
- /views/js/back.js
- /views/css/back.css
===========================
v 1.5.4 (June 03, 2015)
===========================
Fixed
-----
- minor fix for displaying catgory tree properly on PS 1.6.1.0+

Files modified:
-----
- /bulkcombinationsgenerator.php

===========================
v 1.5.3 (March 4, 2015)
===========================
Added
-----
- Possibility to save/upload combination sets

Changed
-----
- Some files where moved to comply with new validator requirements

Directories moved:
-----
- /js/ -> /views/js/
- /css/ -> /views/css/

Files modified:
-----
- /bulkcombinationsgenerator.php
- /views/templates/admin/configure.tpl
- /views/js/back.js
- /views/css/back.css

===========================
v 1.5.2 (February 13, 2015)
===========================

Added
-----
- Possibility to use complex percentage when calculating price impacts

Files modified:
-----
- bulkcombinationsgenerator.php
- views/templates/admin/configure.tpl
- js/back.js

===========================
v 1.5.0 (january 22, 2015)
===========================

Added
-----
- Possibility to set combination with lowest price as default
- Autosave products properly for getting indexed by amazzingfilter
- Minor code fixes

Files modified:
-----
- bulkcombinationsgenerator.php
- views/templates/admin/configure.tpl
- js/back.js

===========================
v 1.1.0 (December 2, 2014)
===========================

Added
-----
- Possibility to set impact prices basing on base price percentage (e.g. 10% of base price)
- Blocking parallel attempts from different users to launch generator at the same time
- Minor code fixes

Files modified:
-----
- bulkcombinationsgenerator.php
- views/templates/admin/configure.tpl

===========================
v 1.0.0 (November 17, 2014)
===========================
Initial relesase
