<?php
/**
 * Khewa Core Changes
 *
 * Holds every custom change Khewa made to PrestaShop core / third-party
 * module files so they survive PrestaShop, theme and module updates.
 *
 * Read CORE_CHANGES.md (what was changed and why) and UPDATE_SAFETY.md
 * (what is handled, how, and how to test it) before touching anything here.
 *
 * Three mechanisms are used, chosen per file:
 *   1. override/            PrestaShop class/controller overrides. PrestaShop
 *                           itself installs them into the root override/ dir.
 *   2. hooks + services.yml Native extension points (CSS injection, mail
 *                           template redirection, Symfony service swap).
 *   3. files/               "Managed files": golden copies of files PrestaShop
 *                           offers no override mechanism for. They are copied
 *                           into place on install and from the "Re-apply"
 *                           button on the module configuration page.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Khewacorechanges extends Module
{
    /** Mail templates served from this module's mails/ folder instead of the site's. */
    const REDIRECTED_MAIL_TEMPLATES = ['order_conf'];

    /**
     * Managed files.
     *  key      => short id used on the config page
     *  'src'    => path under modules/khewacorechanges/files/
     *  'target' => 'theme' (deployed into every active theme) or 'root' (site root)
     *  'path'   => destination relative to the theme dir / site root
     *  'change' => CORE_CHANGES.md item number(s) this file carries
     */
    const MANAGED_FILES = [
        // #5, #10 — PDF templates. HTMLTemplate::getTemplate() looks in themes/<theme>/pdf/ before pdf/.
        'pdf_footer' => ['src' => 'theme/pdf/footer.tpl', 'target' => 'theme', 'path' => 'pdf/footer.tpl', 'change' => '5'],
        'pdf_invoice_total' => ['src' => 'theme/pdf/invoice.total-tab.tpl', 'target' => 'theme', 'path' => 'pdf/invoice.total-tab.tpl', 'change' => '10'],
        'pdf_invoice_product' => ['src' => 'theme/pdf/invoice.product-tab.tpl', 'target' => 'theme', 'path' => 'pdf/invoice.product-tab.tpl', 'change' => '10'],
        // #15 — "Free" shipping label hidden in cart popup + checkout summary (theme templates).
        'cart_popup' => ['src' => 'theme/modules/ps_shoppingcart/ps_shoppingcart-content.tpl', 'target' => 'theme', 'path' => 'modules/ps_shoppingcart/ps_shoppingcart-content.tpl', 'change' => '15'],
        'cart_subtotals' => ['src' => 'theme/templates/checkout/_partials/cart-summary-subtotals.tpl', 'target' => 'theme', 'path' => 'templates/checkout/_partials/cart-summary-subtotals.tpl', 'change' => '15'],
        // #15 — order_conf product list partial (nofilter). PaymentModule::getEmailTemplateContent() checks themes/<theme>/mails/en/ first.
        'mail_product_list' => ['src' => 'theme/mails/en/order_conf_product_list.tpl', 'target' => 'theme', 'path' => 'mails/en/order_conf_product_list.tpl', 'change' => '15'],
        // #6 — Customer Service thread view, total amount blanked. Admin template override path honoured by AdminController/Helper::createTemplate().
        'admin_customer_thread_view' => ['src' => 'root/override/controllers/admin/templates/customer_threads/helpers/view/view.tpl', 'target' => 'root', 'path' => 'override/controllers/admin/templates/customer_threads/helpers/view/view.tpl', 'change' => '6'],
        // #16 — ps_emailalerts: skip employee "new order" alert for RockPOS sales + notify-me fix.
        'ps_emailalerts' => ['src' => 'root/modules/ps_emailalerts/ps_emailalerts.php', 'target' => 'root', 'path' => 'modules/ps_emailalerts/ps_emailalerts.php', 'change' => '16'],
        // #1 — RockPOS (hspointofsalepro) is deliberately NOT managed by this
        // module: its customisations stay inside the module itself and any
        // vendor update must be diffed/merged by hand.
    ];

    public function __construct()
    {
        $this->name = 'khewacorechanges';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Khewa';
        $this->bootstrap = true;
        $this->need_instance = 0;
        parent::__construct();

        $this->displayName = $this->l('Khewa Core Changes');
        $this->description = $this->l('Holds Khewa\'s custom overrides/core changes so they survive PrestaShop and module updates.');
    }

    /* ------------------------------------------------------------------ */
    /* Install / uninstall                                                 */
    /* ------------------------------------------------------------------ */

    public function install()
    {
        // Root override/ already contains hand-made copies of the classes this
        // module overrides (ProductController). PrestaShop refuses to install a
        // module override when the same method already exists in the root
        // override, so back those files up and remove them first; PrestaShop
        // then re-creates them from this module's override/ folder.
        $this->adoptExistingOverrides();

        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionEmailSendBefore')
            && $this->registerHook('actionPresentCart')
            && $this->registerHook('actionPresentProduct')
            && $this->deployManagedFiles() !== false;
    }

    public function uninstall()
    {
        // Managed files are deliberately left in place: removing them would
        // put stock (broken) behaviour back on a live shop. Overrides are
        // removed by PrestaShop itself (parent::uninstall).
        return parent::uninstall();
    }

    /**
     * Back up and remove root override files that this module is about to
     * provide, so Module::installOverrides() does not fail with
     * "method already overridden".
     */
    protected function adoptExistingOverrides()
    {
        $moduleOverrideDir = $this->getLocalPath() . 'override/';
        $removed = false;
        foreach (Tools::scandir($moduleOverrideDir, 'php', '', true) as $file) {
            if (basename($file) === 'index.php') {
                continue;
            }
            $rootFile = _PS_OVERRIDE_DIR_ . $file;
            if (!file_exists($rootFile)) {
                continue;
            }
            // Either a hand-made copy, or a leftover of ours from a failed
            // install: back it up and let PrestaShop regenerate it.
            $this->backupFile($rootFile, 'override/' . $file);
            @unlink($rootFile);
            $removed = true;
        }
        // The class index (var/cache/<env>/class_index.php) may still map
        // e.g. "CartRule" to a root override file that no longer exists
        // (removed just above, or by a previous uninstall). Module::addOverride()
        // would then try to merge into a missing file and silently produce
        // nothing. Always rebuild it so PrestaShop takes the "no override
        // yet, copy the module file" path.
        PrestaShopAutoload::getInstance()->generateIndex();
    }

    /* ------------------------------------------------------------------ */
    /* Hooks                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * CORE_CHANGES.md #7 / #8 — hide the "Total spent" badge in the
     * Customers list. Originally a rule added to the admin theme's theme.css,
     * which any upgrade would overwrite.
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        return '<style id="khewacorechanges-bo">.column-total_spent .badge-success{display:none;}</style>';
    }

    /**
     * CORE_CHANGES.md #9 — serve order_conf (with the pickup message) from
     * this module's mails/ folder. Mail::getTemplateBasePath() detects the
     * "modules/<name>/" part of $templatePath and looks in
     * modules/khewacorechanges/mails/<iso>/ for the template.
     */
    public function hookActionEmailSendBefore($params)
    {
        if (!isset($params['template'], $params['templatePath'])) {
            return true;
        }

        // CORE_CHANGES.md #11 — core's own StockManager emails employees
        // "Product out of stock" directly (no module involved; that's why the
        // mails survived uninstalling the mail-alert module). The original fix
        // commented the Mail::Send call out of src/Core/Stock/StockManager.php;
        // blocking it here survives core updates. Only the core sender is
        // matched — ps_emailalerts' own stock alerts are untouched.
        if ($params['template'] === 'productoutofstock'
            && strpos(str_replace('\\', '/', (string) $params['templatePath']), 'src/Core/Stock') !== false
        ) {
            return false;
        }

        // CORE_CHANGES.md #16 — no employee "new order" alert for RockPOS till
        // sales. Returning false makes Mail::send() abort, so this works no
        // matter which version of ps_emailalerts is installed — the module can
        // be updated freely.
        if ($params['template'] === 'new_order'
            && stripos($params['templatePath'], 'emailalert') !== false
            && $this->isPosOrderReference(isset($params['templateVars']['{order_name}']) ? $params['templateVars']['{order_name}'] : null)
        ) {
            return false;
        }

        if (!in_array($params['template'], self::REDIRECTED_MAIL_TEMPLATES, true)) {
            return true;
        }
        // Only take over the default site template, never another module's mail.
        if (rtrim($params['templatePath'], '/\\') !== rtrim(_PS_MAIL_DIR_, '/\\')) {
            return true;
        }
        $params['templatePath'] = $this->getLocalPath() . 'mails/';

        return true;
    }

    /**
     * CORE_CHANGES.md #15 — never show the word "Free" as a shipping cost in
     * the cart popup / checkout summary. Works at data level (the presented
     * cart array), so it holds even with a stock, un-edited theme template:
     * when the shipping subtotal value contains no digit (i.e. it is the
     * translated word "Free"/"Gratuit"), blank it — the templates then skip
     * the value (and warehouse's own edited templates agree).
     * Real prices ("$12.00") contain digits and are left alone.
     */
    public function hookActionPresentCart($params)
    {
        if (!isset($params['presentedCart']['subtotals']['shipping']['value'])) {
            return;
        }
        $value = (string) $params['presentedCart']['subtotals']['shipping']['value'];
        if ($value !== '' && !preg_match('/\d/', $value)) {
            $params['presentedCart']['subtotals']['shipping']['value'] = '';
        }
    }

    /**
     * CORE_CHANGES.md #14 — remove the "Specific References" block
     * (Ean13 / Isbn / Upc table) from the product page. Originally done by
     * commenting the block out in the warehouse theme's product-details.tpl;
     * done here at data level so it also holds on a stock/updated theme:
     * an empty specific_references makes every template's
     * {if $product.specific_references} guard skip the whole section.
     */
    public function hookActionPresentProduct($params)
    {
        if (!isset($params['presentedProduct'])) {
            return;
        }
        $product = $params['presentedProduct'];
        if ($product instanceof PrestaShop\PrestaShop\Adapter\Presenter\AbstractLazyArray) {
            // third argument forces replacing a method-backed index
            $product->offsetSet('specific_references', [], true);
        } elseif (is_array($product) && array_key_exists('specific_references', $product)) {
            $params['presentedProduct']['specific_references'] = [];
        }
    }

    /**
     * Is this order reference a RockPOS till sale (cart present in pos_cart)?
     */
    protected function isPosOrderReference($reference)
    {
        if (empty($reference) || !Module::isEnabled('hspointofsalepro')) {
            return false;
        }
        try {
            return (bool) Db::getInstance()->getValue(
                'SELECT 1 FROM `' . _DB_PREFIX_ . 'orders` o
                 INNER JOIN `' . _DB_PREFIX_ . 'pos_cart` pc ON pc.id_cart = o.id_cart
                 WHERE o.reference = "' . pSQL($reference) . '"'
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /* ------------------------------------------------------------------ */
    /* Managed files                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Distinct theme directories used by the active shops.
     *
     * @return string[] theme names
     */
    public function getActiveThemeNames()
    {
        $names = [];
        foreach (Shop::getShops(false) as $shop) {
            if (!empty($shop['theme_name'])) {
                $names[$shop['theme_name']] = true;
            }
        }
        if (empty($names) && isset($this->context->shop->theme)) {
            $names[$this->context->shop->theme->getName()] = true;
        }

        return array_keys($names);
    }

    /**
     * Resolve every (source, destination) pair for a managed file.
     *
     * @return array[] each ['src' => abs path, 'dest' => abs path, 'label' => relative dest]
     */
    public function resolveManagedFile($key)
    {
        $def = self::MANAGED_FILES[$key];
        $src = $this->getLocalPath() . 'files/' . $def['src'];
        $pairs = [];
        if ($def['target'] === 'theme') {
            foreach ($this->getActiveThemeNames() as $theme) {
                $pairs[] = [
                    'src' => $src,
                    'dest' => _PS_ALL_THEMES_DIR_ . $theme . '/' . $def['path'],
                    'label' => 'themes/' . $theme . '/' . $def['path'],
                ];
            }
        } else {
            $pairs[] = [
                'src' => $src,
                'dest' => _PS_ROOT_DIR_ . '/' . $def['path'],
                'label' => $def['path'],
            ];
        }

        return $pairs;
    }

    /**
     * Status of every managed file: identical / differs / missing / no_source.
     */
    public function getManagedFilesStatus()
    {
        $rows = [];
        foreach (self::MANAGED_FILES as $key => $def) {
            foreach ($this->resolveManagedFile($key) as $pair) {
                if (!file_exists($pair['src'])) {
                    $state = 'no_source';
                } elseif (!file_exists($pair['dest'])) {
                    $state = 'missing';
                } elseif (md5_file($pair['src']) === md5_file($pair['dest'])) {
                    $state = 'identical';
                } else {
                    $state = 'differs';
                }
                $rows[] = [
                    'key' => $key,
                    'change' => $def['change'],
                    'src' => 'files/' . $def['src'],
                    'dest' => $pair['label'],
                    'state' => $state,
                ];
            }
        }

        return $rows;
    }

    /**
     * Copy every managed file from the module into the site.
     * Existing, differing destinations are backed up first.
     *
     * @param string|null $onlyKey deploy a single managed file
     *
     * @return array|false list of deployed labels, false on any failure
     */
    public function deployManagedFiles($onlyKey = null)
    {
        $deployed = [];
        $ok = true;
        foreach (self::MANAGED_FILES as $key => $def) {
            if ($onlyKey !== null && $onlyKey !== $key) {
                continue;
            }
            foreach ($this->resolveManagedFile($key) as $pair) {
                if (!file_exists($pair['src'])) {
                    $ok = false;
                    continue;
                }
                if (file_exists($pair['dest'])) {
                    if (md5_file($pair['src']) === md5_file($pair['dest'])) {
                        continue;
                    }
                    $this->backupFile($pair['dest'], $pair['label']);
                }
                if (!$this->copyFile($pair['src'], $pair['dest'])) {
                    $ok = false;
                    continue;
                }
                $deployed[] = $pair['label'];
            }
        }

        return $ok ? $deployed : false;
    }

    /**
     * Reverse direction: refresh the module's golden copy from the live file.
     * Use after editing a managed file directly on the site (e.g. RockPOS sales.php).
     */
    public function pullManagedFile($key)
    {
        if (!isset(self::MANAGED_FILES[$key])) {
            return false;
        }
        $pairs = $this->resolveManagedFile($key);
        $pair = reset($pairs); // for theme files all themes share one source; take the first
        if (!$pair || !file_exists($pair['dest'])) {
            return false;
        }
        if (file_exists($pair['src'])) {
            $this->backupFile($pair['src'], 'module-files/' . self::MANAGED_FILES[$key]['src']);
        }

        return $this->copyFile($pair['dest'], $pair['src']);
    }

    protected function copyFile($src, $dest)
    {
        $dir = dirname($dest);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return false;
        }

        return @copy($src, $dest);
    }

    /**
     * Copy $file to modules/khewacorechanges/backup/<timestamp>/<label>.
     */
    protected function backupFile($file, $label)
    {
        static $stamp = null;
        if ($stamp === null) {
            $stamp = date('Ymd_His');
        }
        $dest = $this->getLocalPath() . 'backup/' . $stamp . '/' . ltrim($label, '/');

        return $this->copyFile($file, $dest);
    }

    /* ------------------------------------------------------------------ */
    /* Configuration page                                                  */
    /* ------------------------------------------------------------------ */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitKhewaApplyAll')) {
            $result = $this->deployManagedFiles();
            if ($result === false) {
                $output .= $this->displayError($this->l('Some files could not be copied. Check file permissions.'));
            } elseif (empty($result)) {
                $output .= $this->displayConfirmation($this->l('All managed files were already up to date.'));
            } else {
                $output .= $this->displayConfirmation(sprintf($this->l('Re-applied %d file(s): %s'), count($result), implode(', ', $result)));
            }
        } elseif (Tools::isSubmit('submitKhewaApplyOne')) {
            $key = Tools::getValue('managed_key');
            $result = isset(self::MANAGED_FILES[$key]) ? $this->deployManagedFiles($key) : false;
            if ($result === false) {
                $output .= $this->displayError($this->l('File could not be copied.'));
            } else {
                $output .= $this->displayConfirmation(sprintf($this->l('Applied: %s'), $key));
            }
        } elseif (Tools::isSubmit('submitKhewaPullOne')) {
            $key = Tools::getValue('managed_key');
            if ($this->pullManagedFile($key)) {
                $output .= $this->displayConfirmation(sprintf($this->l('Module copy of "%s" refreshed from the live file.'), $key));
            } else {
                $output .= $this->displayError($this->l('Could not refresh the module copy.'));
            }
        }

        $this->context->smarty->assign([
            'kcc_rows' => $this->getManagedFilesStatus(),
            'kcc_overrides' => $this->getOverridesStatus(),
            'kcc_form_action' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Whether each module override is currently installed in root override/.
     */
    public function getOverridesStatus()
    {
        $rows = [];
        $moduleOverrideDir = $this->getLocalPath() . 'override/';
        foreach (Tools::scandir($moduleOverrideDir, 'php', '', true) as $file) {
            if (basename($file) === 'index.php') {
                continue;
            }
            $rootFile = _PS_OVERRIDE_DIR_ . $file;
            if (!file_exists($rootFile)) {
                $state = 'missing';
            } elseif (strpos(file_get_contents($rootFile), '* module: ' . $this->name) !== false) {
                $state = 'installed';
            } else {
                $state = 'foreign';
            }
            $rows[] = ['file' => 'override/' . $file, 'state' => $state];
        }

        return $rows;
    }
}
