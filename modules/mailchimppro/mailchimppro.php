<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Mailchimppro
 */
class Mailchimppro extends Module
{
    public const MC_MIDDLEWARE_NEW_TO_CONNECT = 'https://prestashop.mailchimpapp.com/new-auth';

    // original OLD url - posts response back with token/api-key
    public const MC_MIDDLEWARE = 'https://prestashop.mailchimpapp.com';
    /**
     * @var DrewM\MailChimp\MailChimp MailChimp API client object
     *
     * @see https://github.com/drewm/mailchimp-api
     */
    protected $apiClient;

    protected $configuration;

    public $menus = [
        [
            'is_root' => true,
            'name' => 'Mailchimp Config',
            'class_name' => 'mailchimppro',
            'visible' => true,
            'parent_class_name' => 0,
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Config',
            'class_name' => 'AdminMailchimpProConfig',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Configuration',
            'class_name' => 'AdminMailchimpProConfiguration',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Setup Wizard',
            'class_name' => 'AdminMailchimpProWizard',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Queue Work',
            'class_name' => 'AdminMailchimpProQueue',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp List',
            'class_name' => 'AdminMailchimpProLists',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Batches',
            'class_name' => 'AdminMailchimpProBatches',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Carts',
            'class_name' => 'AdminMailchimpProCarts',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Customers',
            'class_name' => 'AdminMailchimpProCustomers',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Orders',
            'class_name' => 'AdminMailchimpProOrders',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Products',
            'class_name' => 'AdminMailchimpProProducts',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Stores',
            'class_name' => 'AdminMailchimpProStores',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Sync',
            'class_name' => 'AdminMailchimpProSync',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Sites',
            'class_name' => 'AdminMailchimpProSites',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Mailchimp Automations',
            'class_name' => 'AdminMailchimpProAutomations',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'List members',
            'class_name' => 'AdminMailchimpProListMembers',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Merge fields',
            'class_name' => 'AdminMailchimpProMergeFields',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Promo rules',
            'class_name' => 'AdminMailchimpProPromoRules',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Promo codes',
            'class_name' => 'AdminMailchimpProPromoCodes',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Email templates',
            'class_name' => 'AdminMailchimpProEmailTemplates',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Tags',
            'class_name' => 'AdminMailchimpProTags',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Segments',
            'class_name' => 'AdminMailchimpProSegments',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Campaigns',
            'class_name' => 'AdminMailchimpProCampaigns',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Statistics',
            'class_name' => 'AdminMailchimpProReports',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'PromoStats',
            'class_name' => 'AdminMailchimpProStats',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
    ];

    public function __construct()
    {
        $this->name = 'mailchimppro';
        $this->tab = 'administration';
        $this->version = '3.1.6';
        $this->author = 'Mailchimp';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = '793ebc5f330220c7fb7b817fe0d63a92';

        parent::__construct();

        $this->displayName = 'Mailchimp';
        $this->description = 'Official Mailchimp integration for PrestaShop';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        require_once $this->getLocalPath() . 'vendor/autoload.php';

        // Workaround for PrestaShop core bug (issue #40272): the BO
        // "Upload a module" flow re-uses the OLD module class definition
        // that PS loaded before the new zip was extracted, so upgrade
        // scripts run with stale class metadata — initUpgradeModule
        // compares $this->version (cached old) to ps_module.version,
        // sees equal, and skips. Result: files at new version but DB,
        // hooks, tables and config keys all stuck on the old version.
        //
        // Heals itself on the NEXT BO request because that request gets
        // a fresh PHP process: $this->version reads as the new value,
        // ps_module.version still has the old value, so we re-run the
        // missing upgrade-X.Y.Z.php scripts ourselves.
        $this->selfHealUpgradeIfNeeded();

        $this->configuration = MailchimpProConfig::getConfigurationValues();
    }

    /**
     * Re-run any upgrade-X.Y.Z.php scripts that PrestaShop's broken
     * upload-path skipped.
     *
     * Load-bearing compat shim for two distinct PS framework bugs:
     *
     *   1. PS 8.x cached-class skip: during a v3.0.x → v3.1.x+ BO Upload,
     *      PS's needUpgrade() reads $this->version from the cached OLD
     *      Mailchimppro class (loaded during preBoot, before the ZIP was
     *      extracted). version_compare(old, db, '>') returns false →
     *      runUpgradeModule() never fires → silent partial install.
     *      Self-heal Path B is the ONLY recovery on the next BO request.
     *   2. PS 1.7.0-1.7.7 Addons-cache leak: ModuleManager::install() calls
     *      ModuleDataUpdater::upgrade() which clobbers ps_module.version
     *      with the Addons-API "latest known" value instead of the actual
     *      disk version. Self-heal closes the resulting db-vs-disk gap.
     *
     * Cost on healthy installs is one Configuration::get hash lookup per
     * request (the marker fast-path). The heal block is gated behind both
     * the BO-context guard and the marker check, so it only does real work
     * the FIRST time a per-version mismatch is detected.
     *
     * Mirrors PS's own loadUpgradeVersionList selection criteria:
     *   ps_module.version  <  upgrade-X.Y.Z.php  <=  $this->version
     *
     * Defensive: BO-only, once per request, swallowed exceptions, no
     * effect on initial install (where $this->id is 0).
     */
    protected function selfHealUpgradeIfNeeded()
    {
        if (defined('MAILCHIMPPRO_SELFHEAL_DONE')) {
            return;
        }
        if (!defined('_PS_ADMIN_DIR_')) {
            return;
        }
        define('MAILCHIMPPRO_SELFHEAL_DONE', true);

        if (!$this->id) {
            return;
        }

        // Skip during the BO module-action flow (upload, install, upgrade,
        // enable/disable). PS is about to run its native upgradeMigration on
        // this same request, so doing it from __construct just wastes work
        // AND forces every upgrade script to be idempotent against TWO call
        // sites instead of one. If PS's native upgrade fails for any reason,
        // this method runs on the NEXT BO request (no longer in the upload
        // call stack) and rescues the partial state.
        //
        // Cross-PS-version markers verified empirically by grepping the
        // PrestaShop/PrestaShop repo at tags 1.7.0.0, 1.7.5.0, 1.7.8.10,
        // 8.0.5, 8.2.7, 9.0.3, 9.1.4:
        //   - importModuleAction lives on Symfony admin ModuleController
        //     and exists from PS 1.7.5.0 through 9.1.x (introduced with the
        //     Module Manager).
        //   - postProcessCallback lives on legacy AdminModulesController
        //     and exists from PS 1.7.0.0 through 8.2.x (covers 1.7.0-1.7.4
        //     which predate the Symfony Module Manager; dropped in PS 9.x).
        // Together they cover every PS version this module supports.
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15) as $frame) {
            $func = $frame['function'];
            if ($func === 'importModuleAction' || $func === 'postProcessCallback') {
                return;
            }
        }

        // Fast-path: a previous request already verified DB version against
        // disk version — no need to re-query. Configuration is loaded into
        // PS's bootstrap cache once per request, so this is a hash lookup,
        // not a DB query. The marker is set by every successful pass
        // through the heal logic below (including the "already up to date"
        // path) so a healthy install never re-runs the SELECT.
        if (Configuration::get('module-mailchimpproconfig-self-heal-ok-at-version') === $this->version) {
            return;
        }

        try {
            $dbVersion = Db::getInstance()->getValue(
                'SELECT `version` FROM `' . _DB_PREFIX_ . 'module` WHERE id_module = ' . (int) $this->id
            );

            if (!$dbVersion || version_compare($dbVersion, $this->version, '>=')) {
                // DB already current — mark to skip the SELECT on subsequent
                // requests this version is on disk.
                Configuration::updateValue('module-mailchimpproconfig-self-heal-ok-at-version', $this->version);

                return;
            }

            $upgradeDir = $this->getLocalPath() . 'upgrade/';
            $scripts = [];
            foreach ((array) glob($upgradeDir . 'upgrade-*.php') as $file) {
                if (!preg_match('/upgrade-(\d+\.\d+\.\d+)\.php$/', basename($file), $m)) {
                    continue;
                }
                $scriptVersion = $m[1];
                if (version_compare($scriptVersion, $dbVersion, '>')
                    && version_compare($scriptVersion, $this->version, '<=')) {
                    $scripts[$scriptVersion] = $file;
                }
            }

            uksort($scripts, 'version_compare');

            // OPcache may still serve the OLD compiled bytecode of our
            // src/*.php classes from the broken request. Invalidate any
            // module file that subsequent upgrade scripts could touch,
            // forcing OPcache to re-read from the post-extraction disk.
            if (function_exists('opcache_invalidate')) {
                foreach ((array) glob($this->getLocalPath() . 'src/*.php') as $file) {
                    opcache_invalidate($file, true);
                }
                foreach ((array) glob($this->getLocalPath() . 'src/PrestaChamps/MailchimpPro/*.php') as $file) {
                    opcache_invalidate($file, true);
                }
            }

            $appliedVersion = $dbVersion;
            $allOk = true;
            foreach ($scripts as $scriptVersion => $file) {
                include_once $file;
                $fn = 'upgrade_module_' . str_replace('.', '_', $scriptVersion);
                if (!function_exists($fn)) {
                    PrestaShopLogger::addLog(
                        'mailchimppro self-heal: function ' . $fn . ' missing in ' . basename($file),
                        2
                    );
                    $allOk = false;
                    break;
                }
                $ok = $fn($this);
                if (!$ok) {
                    PrestaShopLogger::addLog(
                        'mailchimppro self-heal: ' . basename($file) . ' returned false; halting chain',
                        3
                    );
                    $allOk = false;
                    break;
                }
                $appliedVersion = $scriptVersion;
            }

            // If every script in range succeeded — or if no script was in
            // range at all (pure version bump without a migration) — bump
            // ps_module.version up to the file version. Otherwise stop at
            // the last successfully-applied script so the next request can
            // pick up where this one halted.
            $finalVersion = $allOk ? $this->version : $appliedVersion;

            if (version_compare($finalVersion, $dbVersion, '>')) {
                Db::getInstance()->update(
                    'module',
                    ['version' => pSQL($finalVersion)],
                    'id_module = ' . (int) $this->id
                );
                PrestaShopLogger::addLog(
                    'mailchimppro self-heal: ' . $dbVersion . ' -> ' . $finalVersion
                        . ' (workaround for PrestaShop core issue #40272 — module upgrade-path cache mismatch)',
                    1
                );
            }

            // Mark verified ONLY if we ended up at the current disk version.
            // If we halted partway (allOk=false), leave the marker stale so
            // the next BO request retries the SELECT and resumes the chain.
            if ($allOk && version_compare($finalVersion, $this->version, '>=')) {
                Configuration::updateValue('module-mailchimpproconfig-self-heal-ok-at-version', $this->version);
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'mailchimppro self-heal: caught exception, will retry next request: ' . $e->getMessage(),
                3
            );
        }
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Install the required tabs, configs and stuff.
     *
     * @return bool
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     *
     * @since 0.0.1
     */
    public function install()
    {
        $tabRepository = new PrestaChamps\PrestaShop\Tab\TabRepository($this->menus, 'mailchimppro');
        $tabRepository->install();

        return parent::install()
            // The moduleRoutes hook is necessary in order to load the autoloader
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionObjectUpdateAfter')
            && $this->registerHook('actionObjectDeleteAfter')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('actionCartSave')
            && $this->registerHook('actionObjectCustomerAddAfter')
            && $this->registerHook('actionObjectCartRuleAddAfter')
            && $this->registerHook('actionObjectCartRuleDeleteBefore')
            // $this->registerHook('displayAdminOrderContentOrder') &&
            // $this->registerHook('displayAdminOrderTabOrder') &&
            // $this->registerHook('displayBackOfficeTop') &&
            && $this->registerHook('actionObjectCartRuleUpdateAfter')
            && $this->registerHook('displayFooterBefore')
            && $this->registerHook('actionNewsletterRegistrationAfter')
            && $this->registerHook('actionCustomerAccountAdd')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('actionCustomerAccountUpdate')

            && $this->registerHook('actionObjectSpecificPriceAddAfter')
            && $this->registerHook('actionObjectSpecificPriceUpdateAfter')
            && $this->registerHook('actionObjectSpecificPriceDeleteAfter')

            && $this->registerHook('actionObjectAddressAddAfter')

            // Pixel: emit PRODUCT_VIEWED to the bridge whenever the additional-
            // info partial is rendered (stock product page initial load, AJAX
            // refresh on combination change PS 1.7.8+, classic theme quickview).
            // The hook is one of THREE redundant PRODUCT_VIEWED sources — see
            // hookDisplayProductAdditionalInfo() docblock for the full strategy.
            && $this->registerHook('displayProductAdditionalInfo')

            // PURCHASED pixel event — primary trigger. PS dispatches this
            // hook from stock OrderConfirmationController on every PS
            // version 1.7.0.6 → 9.1 with $params['order'] = the Order
            // object. Handler builds server-side payload via Order::*
            // methods (authoritative finalized data) and emits inline
            // inline script push to MC_PIXEL_EVENTS_QUEUE. AJAX fallback
            // covers 3rd-party confirmation controllers that don't fire
            // this hook (via the 3-prong escape-hatch list).
            && $this->registerHook('displayOrderConfirmation')

            && $this->registerHook('dashboardZoneOne')
            && $this->installDb()
            && $this->seedPixelDefaults()
            && $this->updatePosition(Hook::getIdByName('dashboardZoneOne'), false, 1);
    }

    /**
     * Seed pixel-feature config defaults so FO reads match BO display from
     * the moment the storefront becomes pixel-eligible. Mirrors the pixel
     * delete list in uninstall(). Symmetric with upgrade/upgrade-3.1.0.php
     * — both delegate to MailchimpProConfig::seedPixelDefaultsIfMissing
     * and propagate its bool so install() / upgrade-script return values
     * faithfully reflect partial-seed failures.
     */
    protected function seedPixelDefaults()
    {
        return MailchimpProConfig::seedPixelDefaultsIfMissing();
    }

    protected function installDb()
    {
        $pf = _DB_PREFIX_ . "{$this->name}_";

        return Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}jobs` (
                `id_job` INT NOT NULL AUTO_INCREMENT,
                `id_entity` INT NULL,
                `type` VARCHAR(50)  NOT NULL,
                `channel` TINYTEXT  NOT NULL,
                `status` TINYINT NOT NULL DEFAULT 0,
                `attempts` SMALLINT DEFAULT 0,
                `body` MEDIUMTEXT NOT NULL,
                `error` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `locked_at` DATETIME NULL,
                `priority` SMALLINT DEFAULT 1,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_job`),
                CONSTRAINT entity_type_shop UNIQUE (id_entity,type,id_shop)
                ) ENGINE = InnoDB;
        ")
        && Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}specific_price` (
                `id_specific_price` INT NOT NULL,
                `id_product` INT NOT NULL,
                `start_date` DATETIME NOT NULL,
                `end_date` DATETIME NOT NULL,
                `needToRun` SMALLINT NOT NULL DEFAULT 0,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_specific_price`)
                ) ENGINE = InnoDB;
        ")
        && Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}api_log` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `request_type` VARCHAR(50) NOT NULL,
                `end_point` VARCHAR(255) NOT NULL,
                `back_trace` LONGTEXT NULL,
                `is_success` BOOLEAN,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ")
        && Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}campaign` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `prefix` varchar(255) DEFAULT NULL,
                `suffix` varchar(255) DEFAULT NULL,
                `reduction` varchar(255) NOT NULL,
                `reduction_type` int(11) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `campaign_id` varchar(255) DEFAULT NULL,
                `id_merge_field_mc` int(11) DEFAULT NULL,
                `tag_merge_field_mc` varchar(255) DEFAULT NULL,
                `end_date` datetime NOT NULL,
                `date_add` datetime DEFAULT NULL,
                `date_upd` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ")
        && Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}promocodes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_promo_main` int(11) NOT NULL,
                `code` varchar(255) NOT NULL,
                `status` tinyint(4) NOT NULL,
                `sent_to_mc` tinyint(4) NOT NULL,
                `id_cart` int(10) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `date_reddeem` datetime DEFAULT NULL,
                `date_add` datetime DEFAULT NULL,
                `date_upd` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB;
        ")
        && Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}pixel_log` (
                `id_log` INT NOT NULL AUTO_INCREMENT,
                `severity` TINYINT(1) NOT NULL DEFAULT 1,
                `source` ENUM('bo', 'fo') NOT NULL DEFAULT 'bo',
                `event_type` VARCHAR(50) NOT NULL,
                `message` TEXT NULL,
                `id_shop` INT NULL,
                `ip` VARCHAR(45) NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_log`),
                INDEX `idx_pixel_log_date_add` (`date_add`),
                INDEX `idx_pixel_log_source` (`source`),
                INDEX `idx_pixel_log_ip_date` (`ip`, `date_add`)
            ) ENGINE = InnoDB;
        ");
    }

    public function hookModuleRoutes()
    {
        return '';
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $tabRepository = new PrestaChamps\PrestaShop\Tab\TabRepository($this->menus, 'mailchimppro');
        $tabRepository->uninstall();

        $pf = _DB_PREFIX_ . "{$this->name}_";
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}jobs`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}specific_price`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}api_log`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}campaign`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}promocodes`");
        Db::getInstance()->execute("DROP TABLE IF EXISTS `{$pf}pixel_log`");

        // Pixel feature config keys — wipe on uninstall so a re-install starts
        // fresh. Pre-existing module keys (API_KEY, LIST_ID, STATUSES_FOR_*, …)
        // are intentionally left in place to preserve admin setup across
        // reinstalls.
        $pixelKeysToDelete = [
            MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED,
            MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE,
            MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS,
            MailchimpProConfig::MAILCHIMP_PIXEL_CSP_CHECKED_AT,
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED,
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_MAX_ROWS,
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN,
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_LEVEL,
            MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE,
            MailchimpProConfig::MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED,
            MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS,
            MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS,
            MailchimpProConfig::MAILCHIMP_PIXEL_REQUIRE_CONSENT,
            MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM,
        ];
        foreach ($pixelKeysToDelete as $key) {
            Configuration::deleteByName($key);
        }

        // Self-heal marker (v3.1.3+) — string literal to stay safe even on a
        // stale-class-table read path.
        Configuration::deleteByName('module-mailchimpproconfig-self-heal-ok-at-version');

        // Connection-health state flags — string literals (same stale-class-safe
        // pattern as the self-heal marker above).
        Configuration::deleteByName('module-mailchimpproconfig-health-state-flag');
        Configuration::deleteByName('module-mailchimpproconfig-health-state-set-at');
        Configuration::deleteByName('module-mailchimpproconfig-health-state-source');
        Configuration::deleteByName('module-mailchimpproconfig-health-last-check-at');
        Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-broken-until');
        Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-failure-log');
        Configuration::deleteByName('module-mailchimpproconfig-fo-cdn-last-success');

        // Cached COMPANY-field state — re-detected on the next connect. The
        // Mailchimp-side field is deliberately left alone: it is list-scoped
        // and may hold values from other stores sharing the audience.
        Configuration::deleteByName('module-mailchimpproconfig-company-field-status');

        return parent::uninstall();
    }

    /**
     * Check if the current PrestaShop installation is version 1.7 or below.
     *
     * @return bool
     */
    public static function isPs17()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7', '>=');
    }

    /**
     * Redirect to the custom config controller.
     *
     * @throws PrestaShopException
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMailchimpProConfiguration'));
    }

    /**
     * Place UTM tracking cookie when the user arrived via MailChimp.
     */
    public function hookDisplayHeader($params)
    {
        if (('mailchimp' === Tools::getValue('utm_source') || !empty(Tools::getValue('mc_cid')))
            && $this->isApiKeySet()) {
            $httpHost = isset($_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8') : '';
            $requestUri = isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') : '';
            // landing_site / utm_source aren't declared properties on Cookie;
            // they live in the magic-method content array. Explicit __set
            // keeps the call out of PHPStan's "undefined property" surface.
            $this->context->cookie->__set('landing_site', "http://{$httpHost}{$requestUri}");
            $mc_cid = Tools::getValue('mc_cid', false);
            $utm_source = Tools::getValue('utm_source', false);
            if ($mc_cid) {
                setcookie('mc_cid', preg_replace('/[^a-zA-Z0-9_\-]/', '', Tools::getValue('mc_cid')), 0, $this->context->shop->getBaseURI());
            }
            if ($utm_source) {
                setcookie('utm_source', preg_replace('/[^a-zA-Z0-9_\-]/', '', Tools::getValue('utm_source')), 0, $this->context->shop->getBaseURI());
            }
            $this->context->cookie->__set('utm_source', Tools::getValue('utm_source'));
            setcookie(
                'landing_site',
                (Tools::usingSecureMode() ? 'https' : 'http') . "://{$httpHost}{$requestUri}",
                0,
                $this->context->shop->getBaseURI()
            );
        }

        $mc_js_payload = '';

        if ($this->isApiKeySet() && $this->isStoreSynced() && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED)) {
            $mcjsscript = Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED);

            if (false !== strpos($mcjsscript, 'chimpstatic.com') || false !== strpos($mcjsscript, '!function(c,h,i,m,p)')) {
                // Strip CDATA
                $mcjsscript = str_replace(['<![CDATA[', ']]>'], '', $mcjsscript);

                // Remove PS HTML comment wrappers (no literal </script> here)
                $endScript = '</script>'; // avoid literal in source
                $mcjsscript = str_replace(['<!--//-->//><!--', '//--><!' . $endScript], ['', $endScript], $mcjsscript);

                // Remove outer script tag if present (build regex without literal "<script")
                $pat = '#^\s*<script[^>]*>(.*)</script>\s*$#si';
                $mcjsscript = preg_replace($pat, '$1', $mcjsscript);

                $mc_js_payload = trim($mcjsscript);

                // Extract the chimpstatic.com MC.js URL from the fragment so
                // the template can emit a <link rel="preload"> tag. Shrinks
                // the SDK init-race window by letting the browser fetch MC.js
                // in parallel with the rest of <head>. Extraction is best-
                // effort — if the fragment shape ever changes, $mc_js_preload_url
                // stays empty and the template falls back to preconnect only.
                $mc_js_preload_url = '';
                if (preg_match(
                    '#(https://chimpstatic\.com/[A-Za-z0-9/_.\-]+\.js)#',
                    $mc_js_payload,
                    $m
                )) {
                    $mc_js_preload_url = $m[1];
                }

                $this->context->smarty->assign([
                    'mc_js_payload' => $mc_js_payload,
                    'mc_js_preload_url' => $mc_js_preload_url,
                    // Autotrack on by default (matches the SDK's own default).
                    // When admin disables it in the BO Pixel tab, _displayHeader.tpl
                    // emits `window.disable_mailchimp_pixel_autotrack = true;` BEFORE
                    // the MC.js loader so PAGE_VIEWED events stop firing automatically.
                    'mc_pixel_autotrack_enabled' => !Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED)
                        ? true
                        : (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED),
                    // Strict-consent mode — when ON, _displayHeader.tpl emits an
                    // inline script BEFORE MC.js that writes mc_user_optin=false
                    // cookie. The SDK defaults to opted-out and the merchant's
                    // CMP must explicitly flip the cookie to "true" (or call
                    // $mcSite.enableOptIn()) on accept. Default: OFF — admin
                    // responsibility model (matches Mailchimp guidance: admins
                    // in prior-consent jurisdictions enable this explicitly).
                    'mc_pixel_require_consent' => (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_REQUIRE_CONSENT),
                ]);

                return $this->context->smarty->fetch(
                    $this->getLocalPath() . 'views/templates/hook/front/_displayHeader.tpl'
                );
            }
        }
    }

    /**
     * Register the pixel-bridge JS + emit MC_PIXEL runtime data for it.
     *
     * Lightweight by design — runs on every storefront page render:
     *   - 4 Configuration::get reads (cached internally by PS)
     *   - 1 Media::addJsDef call
     *   - 1 registerJavascript call
     * No remote calls, no DB writes.
     *
     * Gated on the same conditions as the chimpstatic loader in
     * hookDisplayHeader — only fires when the pixel script is cached AND
     * the store is synced AND the API key is set. Otherwise loading the
     * bridge would be wasted bytes (there'd be no window.$mcSite for the
     * bridge to talk to).
     *
     * @param array $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        // AJAX requests: PS forces display_header/display_footer to false in
        // FrontController::init() and the response is a JSON / HTML-fragment —
        // no <head> rendered, registerJavascript() output never reaches the
        // client. Skip the whole handler to avoid wasted Configuration::get /
        // getModuleLink / getTemplateVarProduct / registerJavascript work on
        // every cart-AJAX, combination-refresh, quickview-open, etc. hit.
        // $this->ajax is declared on the abstract Controller class (stable on
        // PS 1.7.0.6 → 9.1), populated in FrontController::init() from
        // Tools::getValue('ajax') / X-Requested-With.
        if (isset($this->context->controller)
            && property_exists($this->context->controller, 'ajax')
            && $this->context->controller->ajax) {
            return;
        }

        if (!$this->isApiKeySet() || !$this->isStoreSynced()) {
            return;
        }
        if (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED)) {
            return;
        }

        $identifyAjaxMode = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE);

        $mcPixel = [
            'telemetryUrl' => $this->context->link->getModuleLink(
                $this->name,
                'pixeltelemetry',
                [],
                null
            ),
            // Last-resort fallback used by the bridge when (a) the
            // displayProductAdditionalInfo hook is missing AND (b) DOM-parse
            // of the displayAjaxRefresh / quickview AJAX response can't
            // produce all 5 Mailchimp-required fields. Bridge GETs this
            // endpoint with ?id_product=X&id_product_attribute=Y and
            // forwards the server-built payload to api.track().
            'productInfoUrl' => $this->context->link->getModuleLink(
                $this->name,
                'pixelproductinfo',
                [],
                null
            ),
            // Fire-and-forget AJAX endpoint that returns the visitor's current
            // cart id (and a small cart summary). The bridge calls this on
            // PRODUCT_ADDED_TO_CART (and future cart-shaped events) when the
            // JsDef-emitted MC_PIXEL.cartId is 0 — meaning PS hadn't yet
            // persisted a cart row at pageload time, but a row exists now
            // (most commonly: first-add-of-session).
            'cartInfoUrl' => $this->context->link->getModuleLink(
                $this->name,
                'pixelcartinfo',
                [],
                null
            ),
            // Layer 2 fallback URL for PURCHASED. The bridge calls this
            // when the displayOrderConfirmation hook didn't fire (3rd-party
            // confirmation controller that doesn't extend stock
            // OrderConfirmationController). Bridge passes id_order (logged-
            // in) OR id_cart+key (guest) — sourced from setMedia's
            // MC_PIXEL_ORDER_CONTEXT JsDef. The endpoint auth-gates by
            // matching id_customer for logged-in OR secure_key for guest.
            'orderInfoUrl' => $this->context->link->getModuleLink(
                $this->name,
                'pixelorderinfo',
                [],
                null
            ),
            'pixelLogEnabled' => (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED),
            // Visitor's current cart id — needed for PRODUCT_ADDED_TO_CART,
            // CART_VIEWED, CHECKOUT_STARTED (Mailchimp's `cartId` field is
            // required for those events). PS doesn't expose id_cart on the
            // window.prestashop.cart object so we have to ship it via JsDef.
            // Value can be 0 when the visitor hasn't yet caused PS to persist
            // a cart row (lazy cart-create on first add). The bridge handles
            // the cartId=0 case by fetching from /pixelcartinfo at fire time.
            'cartId' => isset($this->context->cart) ? (int) $this->context->cart->id : 0,
            // displayProductAdditionalInfo hook gate — when false, our handler
            // is server-side no-op and the bridge silently skips its marker-
            // presence diagnostic (PIXEL_HOOK_WIRED/MISSING) since the marker
            // is correctly absent. Flip to true ONLY for edge-case stores
            // where the data-product JSON path can't extract a valid payload.
            'hookDisplayProductAdditionalInfoEnabled' => (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED),
            'identifyFormat' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT),
            'autotrackEnabled' => (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED),
            'identifyAjaxMode' => $identifyAjaxMode,
            'pixelLogLevel' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_LOG_LEVEL),
            // Milliseconds the bridge waits after pixel.installed===true
            // before firing the first track/identify. Constants on the
            // PHP side are the single source of truth — bridge JS reads
            // sdkSettleDelayMsMin/Max/Default to range-clamp and fallback.
            'sdkSettleDelayMs' => (int) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS),
            'sdkSettleDelayMsMin' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MIN,
            'sdkSettleDelayMsMax' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MAX,
            'sdkSettleDelayMsDefault' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT,
            // Form-identify watcher config. Master toggle gates the entire
            // watcher; per-form custom selectors are merged with the
            // bridge's built-in defaults. Values are emitted lower-case-
            // safe / XSS-safe via the controller's sanitizer (see
            // sanitizePixelFormSelectorsCustom in the admin controller).
            // hasKey fallback — protects fresh installs where the upgrade
            // hook hasn't seeded these yet. Default to ON for the master toggle.
            'identifyOnForms' => Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS)
                ? (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS)
                : true,
            // Per-form selector config. coreDefaults are the hardcoded
            // built-ins (always active — single source of truth
            // MailchimpProConfig::$pixelFormCoreDefaults). selectorsCustom
            // is the admin-added additive list from the BO Multiselect.
            // The bridge UNIONs the two at runtime.
            //
            // Module-enabled gating: when ps_emailsubscription is disabled,
            // there are no newsletter forms on the storefront to match —
            // so we emit empty arrays. The forms-bridge naturally skips
            // wiring a watcher for any form whose selector union is empty,
            // saving the listener cost on shops without the module.
            'identifyForms' => [
                'newsletter' => [
                    'coreDefaults' => call_user_func(['Module', 'isEnabled'], 'ps_emailsubscription')
                        && isset(MailchimpProConfig::$pixelFormCoreDefaults['newsletter'])
                        ? MailchimpProConfig::$pixelFormCoreDefaults['newsletter']
                        : [],
                    'selectorsCustom' => call_user_func(['Module', 'isEnabled'], 'ps_emailsubscription')
                        && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM)
                        ? (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM)
                        : '',
                ],
                // Restock-alert (ps_emailalerts) — gated identically to the
                // newsletter on the module being enabled. The DOM only renders
                // for out-of-stock products + guest visitors (ps_emailalerts'
                // hook sets $email=1 only when customer->id <= 0), so the
                // bridge naturally bails when input is absent (logged-in
                // case where the module uses session email server-side).
                'restock' => [
                    'coreDefaults' => call_user_func(['Module', 'isEnabled'], 'ps_emailalerts')
                        && isset(MailchimpProConfig::$pixelFormCoreDefaults['restock'])
                        ? MailchimpProConfig::$pixelFormCoreDefaults['restock']
                        : [],
                    'selectorsCustom' => call_user_func(['Module', 'isEnabled'], 'ps_emailalerts')
                        && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM)
                        ? (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM)
                        : '',
                ],
                // Contact (stock contactform module) — gated identically to
                // newsletter/restock on the module being enabled. Bridge
                // also appends a runtime selector from
                // window.prestashop.urls.pages.contact (multi-lingual safe)
                // so admin doesn't need to paste the translated /contact
                // URL slug. Form-POST by default; pending-flag handoff +
                // 500ms-clear handle the navigation race recovery.
                'contact' => [
                    'coreDefaults' => call_user_func(['Module', 'isEnabled'], 'contactform')
                        && isset(MailchimpProConfig::$pixelFormCoreDefaults['contact'])
                        ? MailchimpProConfig::$pixelFormCoreDefaults['contact']
                        : [],
                    'selectorsCustom' => call_user_func(['Module', 'isEnabled'], 'contactform')
                        && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM)
                        ? (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM)
                        : '',
                ],
                // Forgot-password — PS CORE feature (AuthController/
                // PasswordController), not a module. Always present, no
                // Module::isEnabled gate. The form-POST navigation is
                // unconditional (no AJAX variant in stock PS), so identify
                // lands via the two-phase delivery (best-effort same-page +
                // guaranteed post-reload via pending-flag handoff). Bridge
                // also appends a runtime URL selector from
                // window.prestashop.urls.pages.password (multi-lingual safe).
                'forgotPassword' => [
                    'coreDefaults' => isset(MailchimpProConfig::$pixelFormCoreDefaults['forgotPassword'])
                        ? MailchimpProConfig::$pixelFormCoreDefaults['forgotPassword']
                        : [],
                    'selectorsCustom' => Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM)
                        ? (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM)
                        : '',
                ],
            ],
        ];

        // Two delivery paths for the identify value:
        //   (A) INLINE — emit `identifyValue` directly into MC_PIXEL. Zero
        //       extra HTTP, cache-safe under PS native + LSCache + login-aware
        //       Varnish (dominant configurations).
        //   (B) AJAX — emit only `customerInfoUrl`; bridge fetches at runtime.
        //       For aggressive cache-everything setups (Varnish / Cloudflare
        //       page rules) where path A risks leaking one customer's
        //       identifyValue into another visitor's cached HTML.
        // Toggle: BO Pixel tab → "Cache-safe identify mode".
        //
        // Gate: emit whenever $context->customer->email is non-empty. PS
        // populates that field for both logged-in customers AND guests
        // mid-checkout (PS persists firstname/email on the session Customer
        // once the personal-info form is submitted on /order). isLogged()
        // stays false for guests, so the old isLogged-only gate silently
        // dropped every guest order from the pixel's identify graph —
        // trusting customer->email closes that gap without inventing a new
        // signal. Truly anonymous visitors have an empty email and get no
        // identifyValue; the bridge calls reset() to clear any prior
        // identification (handles logout + session-end correctly).
        if ($identifyAjaxMode) {
            $mcPixel['customerInfoUrl'] = $this->context->link->getModuleLink(
                $this->name,
                'customerinfo',
                [],
                null
            );
        } else {
            if (isset($this->context->customer)) {
                $email = strtolower(trim((string) $this->context->customer->email));
                if ('' !== $email) {
                    $format = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT);
                    $mcPixel['identifyValue'] = ('EMAIL_SHA256' === $format)
                        ? hash('sha256', $email)
                        : $email;

                    // Phone enrichment — Mailchimp uses PHONE_SHA256 alongside
                    // EMAIL_SHA256 for stronger subscriber matching. Same format
                    // toggle as email (EMAIL_SHA256 → PHONE_SHA256, EMAIL → PHONE).
                    // Address selection via CustomerAddressSelector so the value
                    // we hash matches ListMemberFormatter's merge_fields.PHONE
                    // on the audience subscriber — drift breaks the match.
                    // Strict-consent gating happens upstream of any identify call.
                    $phoneRaw = '';
                    $primaryAddress = PrestaChamps\MailchimpPro\Helpers\CustomerAddressSelector::pickPrimary(
                        $this->context->customer,
                        isset($this->context->cart) ? $this->context->cart : null
                    );
                    if ($primaryAddress) {
                        $mobile = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone_mobile);
                        $landline = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone);
                        $phoneRaw = '' !== $mobile ? $mobile : $landline;
                    }
                    if ('' !== $phoneRaw) {
                        $mcPixel['phoneIdentifyValue'] = ('EMAIL_SHA256' === $format)
                            ? hash('sha256', $phoneRaw)
                            : $phoneRaw;
                    }
                }
            }
        }

        // ===== PRODUCT_VIEWED — initial-load payload =================================
        // Emit MC_PIXEL.initialProduct when the current FO controller is the
        // product page (NOT a quickview AJAX call — that's caught separately by
        // the displayProductAdditionalInfo hook or the JS quickviewOpened
        // listener). This is the PRIMARY of three redundant PRODUCT_VIEWED
        // sources:
        //   1. actionFrontControllerSetMedia → MC_PIXEL.initialProduct (here)
        //      → fires once on bridge init via fireOnce dedupe.
        //      Universal: works on ANY theme (including custom themes that
        //      strip the displayProductAdditionalInfo hook) and ANY PS version.
        //   2. displayProductAdditionalInfo hook → inline script push to
        //      MC_PIXEL_EVENTS_QUEUE. Same fingerprint as #1 → deduped on init.
        //      Becomes the primary source for combination changes (PS 1.7.8+
        //      via jQuery .replaceWith()-executes-inline-scripts) and classic
        //      theme quickview (the partial is included in quickview.tpl).
        //   3. JS prestashop.on('updatedProduct') listener in the bridge.
        //      Fallback for PS 1.7.0.6 combination change (its displayAjaxRefresh
        //      doesn't include the product_additional_info fragment, so #2's
        //      script can't be re-injected) AND for custom themes that
        //      strip the hook from their templates entirely.
        //
        // Deduplication: bridge keeps an in-memory Set keyed by event_type +
        // product.id (variant id). The first path to fire for a fingerprint
        // wins; subsequent attempts no-op. Set is page-local — new page
        // navigation gets a fresh Set so the initial fire happens again.
        // Pull the SAME presented-product array PS uses for its template (and
        // also passes to displayProductAdditionalInfo's $params['product']).
        // Reusing the canonical pipeline guarantees field-name parity with
        // the hook path → identical fingerprints → dedupe works as designed.
        // PS 1.7+ returns an array; PS 9.1 returns a ProductLazyArray
        // (ArrayAccess), so the helper below treats either uniformly.
        $controller = isset($this->context->controller) ? $this->context->controller : null;
        $isQuickViewAjax = (1 === (int) Tools::getValue('quickview'));
        if (null !== $controller
            && property_exists($controller, 'php_self')
            && 'product' === $controller->php_self
            && !$isQuickViewAjax) {
            // Resolve the variant id (id_product_attribute) the user is
            // currently viewing. ProductController has a private resolver
            // for this (getIdProductAttributeByGroupOrRequestOrDefault),
            // not callable from outside. We replicate its priority: URL
            // param wins, fall back to Product::getDefaultAttribute (public
            // static, stable on PS 1.7.0.6 → 9.1) for the shop's configured
            // default combination.
            $idAttribute = (int) Tools::getValue('id_product_attribute', 0);
            if ($idAttribute <= 0
                && isset($controller->product)
                && Validate::isLoadedObject($controller->product)) {
                try {
                    $idAttribute = (int) Product::getDefaultAttribute(
                        (int) $controller->product->id
                    );
                } catch (Exception $e) {
                    $idAttribute = 0;
                }
            }
            if ($idAttribute < 0) {
                $idAttribute = 0;
            }

            $payload = null;

            // ===== PRIMARY PATH (~5-8ms): build from the already-loaded Product ===
            // ProductController loads $this->product in init() (before
            // setMedia fires), exposed via the public getProduct() accessor
            // (the property itself is protected since PS 1.7.0.6, so we use
            // the getter — stable on PS 1.7.0.6 → 9.1). We skip the heavy
            // ProductPresenter pipeline that getTemplateVarProduct() runs
            // (~15-25ms) and read trivial fields directly from Product props
            // + do 4 targeted lookups for the optional fields. SAME output
            // shape as the presenter path — JS fingerprints match for dedupe.
            if (method_exists($controller, 'getProduct')) {
                try {
                    $loadedProduct = $controller->getProduct();
                    if ($loadedProduct instanceof Product
                        && Validate::isLoadedObject($loadedProduct)) {
                        $payload = $this->buildPixelProductPayloadFromProduct(
                            $loadedProduct,
                            $idAttribute
                        );
                    }
                } catch (Exception $e) {
                    // Fall through to the presenter fallback below.
                }
            }

            // ===== FALLBACK PATH (~15-25ms): full getTemplateVarProduct() ==========
            // Only fires when the primary path couldn't produce a payload
            // with a non-empty id — meaning $controller->product wasn't a
            // loaded object (custom controller, unusual init state) or the
            // helper threw. Preserves PRE-OPTIMIZATION behavior — worst case
            // = previous baseline. If both paths fail, bridge layer-2
            // ([data-product] DOM parse) / layer-3 (AJAX pixelproductinfo.php)
            // recovers the event without loss.
            if ((!is_array($payload) || empty($payload['id']))
                && method_exists($controller, 'getTemplateVarProduct')) {
                try {
                    $presented = $controller->getTemplateVarProduct();
                    if ($presented) {
                        $payload = $this->buildPixelProductPayloadFromArray($presented);
                    }
                } catch (Exception $e) {
                    // Defensive: presenter can throw on edge-case controller
                    // states. Silently skip — bridge cascade still covers us.
                }
            }

            if (is_array($payload) && !empty($payload['id'])) {
                $mcPixel['initialProduct'] = $payload;
            }
        }

        Media::addJsDef(['MC_PIXEL' => $mcPixel]);

        // Cache-bust the bridge JS with `?v={module-version}` so every module
        // upgrade invalidates stale copies cached at every layer (browser /
        // Cloudflare / LSCache / Varnish / PS native cache / aggressive CDNs).
        // Without this, a merchant on any of those layers gets the OLD bridge
        // after upgrading the module — until the cache TTL expires or the
        // admin manually flushes it. Path-with-query-string trips the default
        // `server: 'local'` mode of registerJavascript() which validates the
        // relative path against on-disk files (the lookup fails silently and
        // the script element never renders). PS 8.2+ adds a `version` param
        // that handles this cleanly, but we target PS 1.7.0.6 as the floor —
        // and 1.7 has no such param. Solution: pass `server: 'remote'` which
        // skips the path-validation branch (verified in PS 1.7.0.6 →  9.1.0
        // JavascriptManager::register() — the 'remote' branch adds the URL
        // as-is, no getFullPath() lookup). __PS_BASE_URI__ handles subdir
        // installs (e.g. /store/ → "/store/modules/...").
        //
        // CCC interaction (PS native "Combine, Compress, Cache"): not at risk.
        // CccReducer::reduceJs() rolls a script into the combined bundle only
        // when BOTH (a) server === 'local' AND (b) no defer/async attribute.
        // We violate both — `server: 'remote'` AND `attributes: 'defer'` — so
        // the bridge is always emitted as its own script-src element,
        // intact, with the cache-buster preserved. Confirmed in PS 1.7.0.6
        // CccReducer.php:84 + PS 9.x CccReducer.php:107.
        $bridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
            . '/views/js/front/mc-pixel-bridge.js?v=' . $this->version;
        $this->context->controller->registerJavascript(
            'mailchimppro-pixel-bridge',
            $bridgeUrl,
            ['server' => 'remote', 'priority' => 200, 'attributes' => 'defer']
        );

        // FO CDN health probe — one HEAD request per browser session to the
        // merchant's chimpstatic MC.js URL, beaconed back to
        // controllers/front/reportcdnfailure.php. Powers the FoCdnHealth
        // threshold that gates MC.js emission in Phase 2 piece 4. Only wire
        // if we can extract the chimpstatic URL from the cached loader
        // fragment — otherwise the probe has nothing to check.
        $chimpstaticProbeUrl = '';
        $cachedScriptFragment = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED);
        if ('' !== $cachedScriptFragment
            && preg_match('#(https://chimpstatic\.com/[A-Za-z0-9/_.\-]+\.js)#', $cachedScriptFragment, $m)) {
            $chimpstaticProbeUrl = $m[1];
        }
        if ('' !== $chimpstaticProbeUrl) {
            // Distinct per browser session so the threshold works — session_id() first, then fallbacks.
            $sessionMarker = '';
            if (function_exists('session_id')) {
                $sessionMarker = (string) session_id();
            }
            if ('' === $sessionMarker && isset($this->context->cookie->id_guest)) {
                $sessionMarker = (string) $this->context->cookie->id_guest;
            }
            if ('' === $sessionMarker) {
                $sessionMarker = bin2hex(random_bytes(8));
            }
            $visitorSeed = $sessionMarker
                . '|' . (isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '');
            Media::addJsDef([
                'mcCdnHealthConfig' => [
                    'probeUrl' => $chimpstaticProbeUrl,
                    'beaconUrl' => $this->context->link->getModuleLink(
                        $this->name,
                        'reportcdnfailure',
                        [],
                        null
                    ),
                    'visitorHash' => substr(sha1($visitorSeed), 0, 8),
                ],
            ]);
            $cdnHealthUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                . '/views/js/front/mc-cdn-health.js?v=' . $this->version;
            $this->context->controller->registerJavascript(
                'mailchimppro-cdn-health',
                $cdnHealthUrl,
                ['server' => 'remote', 'priority' => 220, 'attributes' => 'defer']
            );
        }

        // PRODUCT_VIEWED — per-event JS file, loaded ONLY on the product
        // detail page (not quickview, not category, not anything else).
        // Depends on the core bridge being loaded first (uses
        // window.MC_PIXEL_BRIDGE.fireOnce / .sendTelemetry / .cfg).
        // Order: priority 200 (core) < priority 210 (product) → core's
        // defer script executes first, then product's — same order as
        // they appear in the registration array.
        //
        // Future per-event files (mc-pixel-bridge-cart.js,
        // mc-pixel-bridge-order.js, mc-pixel-bridge-addtocart.js) will
        // follow the same pattern: conditional registration here, IIFE
        // that reads window.MC_PIXEL_BRIDGE in the file.
        if (null !== $controller
            && property_exists($controller, 'php_self')
            && 'product' === $controller->php_self
            && !$isQuickViewAjax) {
            $productBridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                . '/views/js/front/mc-pixel-bridge-product.js?v=' . $this->version;
            $this->context->controller->registerJavascript(
                'mailchimppro-pixel-bridge-product',
                $productBridgeUrl,
                ['server' => 'remote', 'priority' => 210, 'attributes' => 'defer']
            );
        }

        // CART_VIEWED — per-event JS file, loaded ONLY on the cart page.
        // Same pattern as the product bundle above: priority 210 ensures
        // core (200) loads first so window.MC_PIXEL_BRIDGE is available.
        // The bundle's IIFE auto-fires onCartViewed() on load — no need
        // for a JS-side page_name check, the conditional registration here
        // gates it.
        if (null !== $controller
            && property_exists($controller, 'php_self')
            && 'cart' === $controller->php_self) {
            $cartBridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                . '/views/js/front/mc-pixel-bridge-cart.js?v=' . $this->version;
            $this->context->controller->registerJavascript(
                'mailchimppro-pixel-bridge-cart',
                $cartBridgeUrl,
                ['server' => 'remote', 'priority' => 210, 'attributes' => 'defer']
            );
        }

        // CHECKOUT_STARTED — per-event JS file. Loaded when the visitor is
        // on a checkout page, detected via THREE prongs (any-of):
        //   1. php_self === 'order'  — stock PS OrderController
        //   2. page_name === 'checkout'  — theme/PS-level convention; many
        //      3rd-party checkout modules (e.g., OnePageCheckoutPS) preserve
        //      this even when their php_self differs
        //   3. php_self ∈ MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS — comma-
        //      separated escape-hatch list, BO-configurable. Default 'opc'.
        //      Admin extends when running a custom checkout that matches
        //      neither (1) nor (2).
        //
        // The bundle auto-fires onCheckoutStarted() on load. fireOnce dedupes
        // step navigation within the same checkout (same chk_<cartId>
        // fingerprint → ONE mcTrack reaches Mailchimp).
        if (null !== $controller && property_exists($controller, 'php_self')) {
            $phpSelf = (string) $controller->php_self;
            $pageName = property_exists($controller, 'page_name')
                ? (string) $controller->page_name
                : '';
            $isCheckoutPage = false;
            if ('order' === $phpSelf) {
                $isCheckoutPage = true;
            } elseif ('checkout' === $pageName) {
                $isCheckoutPage = true;
            } else {
                $extraList = (string) Configuration::get(
                    MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS
                );
                if ('' !== $extraList) {
                    $extraSelves = array_map('trim', explode(',', $extraList));
                    if (in_array($phpSelf, $extraSelves, true)) {
                        $isCheckoutPage = true;
                    }
                }
            }
            if ($isCheckoutPage) {
                // Emit checkout-mode flag so the bridge can gate the OPC
                // identify watcher. Stock PS uses form-submit + page reload
                // between checkout steps — the existing identifyOrReset path
                // (re-running on each setMedia render) already handles those.
                // Non-stock checkouts (page_name='checkout' OR BO-list match,
                // i.e., OnePageCheckoutPS / SuperCheckout / etc.) submit
                // personal-info via AJAX without a page reload, so the
                // pageload-time identify never fires for guests until the
                // next navigation. The watcher subscribes to PS events +
                // falls back to /pixelcartinfo?with_customer_email=1 to
                // resolve the cart's associated customer email server-side
                // when the active module doesn't push it into
                // window.prestashop.customer.email.
                //
                // Separate addJsDef key (not nested into MC_PIXEL) because
                // MC_PIXEL is already addJsDef'd higher up the function;
                // PS's Media::addJsDef merges by top-level key, so we can
                // append a NEW top-level key but can't update existing ones.
                Media::addJsDef([
                    'MC_PIXEL_CHECKOUT_MODE' => ('order' === $phpSelf) ? 'stock' : 'non-stock',
                ]);
                $checkoutBridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                    . '/views/js/front/mc-pixel-bridge-checkout.js?v=' . $this->version;
                $this->context->controller->registerJavascript(
                    'mailchimppro-pixel-bridge-checkout',
                    $checkoutBridgeUrl,
                    ['server' => 'remote', 'priority' => 210, 'attributes' => 'defer']
                );
            }

            // PURCHASED — per-event JS file, loaded on the order-confirmation
            // page detected via the SAME 3-prong pattern as CHECKOUT_STARTED:
            //   1. php_self === 'order-confirmation' — stock OrderConfirmationController
            //   2. page_name === 'order-confirmation' — theme/PS convention
            //      preserved by most 3rd-party checkout modules even when
            //      their confirmation controller is custom
            //   3. php_self ∈ MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS
            //      — admin escape-hatch comma-list for modules that break
            //      both conventions above
            //
            // Layer 1 of the cascade is the displayOrderConfirmation PHP hook
            // (handler emits inline script push to MC_PIXEL_EVENTS_QUEUE).
            // The bridge JS registered here handles Layer 2 — AJAX fallback
            // to /pixelorderinfo when the queue is empty (3rd-party module
            // that doesn't fire the hook).
            //
            // fireOnce dedupes by order.id so even if BOTH the hook AND the
            // bridge fire (well-behaved 3rd-party module), Mailchimp gets
            // ONE PURCHASED per order render.
            $isOrderConfirmationPage = false;
            if ('order-confirmation' === $phpSelf) {
                $isOrderConfirmationPage = true;
            } elseif ('order-confirmation' === $pageName) {
                $isOrderConfirmationPage = true;
            } else {
                $extraOcList = (string) Configuration::get(
                    MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS
                );
                if ('' !== $extraOcList) {
                    $extraOcSelves = array_map('trim', explode(',', $extraOcList));
                    if (in_array($phpSelf, $extraOcSelves, true)) {
                        $isOrderConfirmationPage = true;
                    }
                }
            }
            if ($isOrderConfirmationPage) {
                // Emit the order-id context the bridge needs for its Layer 2
                // AJAX fallback. Covers both URL shapes PS uses:
                //   - Logged-in checkout: ?id_order=X
                //   - Guest checkout:     ?key=K&id_cart=C (no id_order)
                // The bridge picks whichever is available + passes it to
                // /pixelorderinfo, which server-side resolves the Order
                // (via id_order OR via Order::getByCartId for guests).
                Media::addJsDef([
                    'MC_PIXEL_ORDER_CONTEXT' => [
                        'id_order' => (int) Tools::getValue('id_order'),
                        'key' => (string) Tools::getValue('key'),
                        'id_cart' => (int) Tools::getValue('id_cart'),
                    ],
                ]);
                $purchaseBridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                    . '/views/js/front/mc-pixel-bridge-purchase.js?v=' . $this->version;
                $this->context->controller->registerJavascript(
                    'mailchimppro-pixel-bridge-purchase',
                    $purchaseBridgeUrl,
                    ['server' => 'remote', 'priority' => 210, 'attributes' => 'defer']
                );
            }
        }

        // Form-identify watcher — loaded on every storefront page when the
        // master toggle is on. Fires identify() when a visitor submits an
        // email-bearing form (newsletter, restock, contact, forgot-password).
        // Per-form selectors come from coreDefaults ∪ admin-supplied custom
        // selectors via MC_PIXEL.identifyForms.
        $identifyOnForms = Configuration::hasKey(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS)
            ? (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS)
            : true;
        if ($identifyOnForms) {
            $formsBridgeUrl = __PS_BASE_URI__ . 'modules/' . $this->name
                . '/views/js/front/mc-pixel-bridge-forms.js?v=' . $this->version;
            $this->context->controller->registerJavascript(
                'mailchimppro-pixel-bridge-forms',
                $formsBridgeUrl,
                ['server' => 'remote', 'priority' => 210, 'attributes' => 'defer']
            );
        }
    }

    /**
     * displayProductAdditionalInfo hook — emits an inline script that pushes
     * a PRODUCT_VIEWED event payload onto window.MC_PIXEL_EVENTS_QUEUE plus a
     * tiny `span.mc-pixel-hook-marker` element so the bridge can detect
     * whether the active theme actually rendered the hook output.
     *
     * Where this fires (and why we want each):
     *   - Initial main product page render (stock classic + hummingbird include
     *     this partial via product.tpl → product-additional-info.tpl). Same
     *     fingerprint as the MC_PIXEL.initialProduct path emitted from
     *     actionFrontControllerSetMedia → deduped, only one fire reaches mcTrack.
     *   - Combination change AJAX refresh on PS 1.7.8+ (product_additional_info
     *     fragment IS in displayAjaxRefresh, and jQuery .replaceWith() executes
     *     inline scripts inside the new HTML). New variant fingerprint → fires.
     *   - Classic theme quickview (quickview.tpl includes this partial). New
     *     product fingerprint → fires.
     *
     * Where this does NOT fire (covered by other paths):
     *   - PS 1.7.0.6 combination change — product_additional_info isn't in
     *     that version's displayAjaxRefresh response. JS listener for
     *     prestashop.on('updatedProduct') in the bridge catches it.
     *   - Hummingbird quickview — its quickview.tpl doesn't include this
     *     hook. JS listener for prestashop.on('quickviewOpened') catches it.
     *   - Custom themes that strip the hook call from their templates. The
     *     bridge detects the missing .mc-pixel-hook-marker and logs
     *     PIXEL_HOOK_MISSING telemetry; the actionFrontControllerSetMedia
     *     path covers initial load, and the JS listener covers combination
     *     change.
     *
     * Defensive: returns empty string when the pixel isn't ready (no API key,
     * no store sync, no cached script, log kill-switch off) so we don't inject
     * anything when the storefront wouldn't be tracking anyway.
     *
     * @param array $params hook params; expects $params['product'] = the
     *                      PS-presented product array (with id_product,
     *                      id_product_attribute, name, price_amount, …)
     *
     * @return string inline marker + script for the page, or ''
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        // ===== DEFAULT: NO-OP (handler disabled) ===============================
        // The bridge's [data-product] JSON parser in displayAjaxRefresh's
        // product_details fragment covers PRODUCT_VIEWED on every PS 1.7.0.6
        // → 9.1 × classic + hummingbird — so this hook's inline-script queue
        // push is redundant by default. We keep the implementation below
        // intact (NOT deleted) for the edge case where a custom theme strips
        // [data-product] AND the bridge cascade also fails on it; in that
        // case flip MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED=1
        // to restore the legacy server-side push path.
        if (!(bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED)) {
            return '';
        }

        if (!$this->isApiKeySet() || !$this->isStoreSynced()) {
            return '';
        }
        if (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED)) {
            return '';
        }
        // $params['product'] is a plain array on PS 1.7 and a ProductLazyArray
        // (ArrayAccess) on PS 9.1 — accept either; the helper handles both via
        // array-access syntax.
        if (!isset($params['product'])
            || (!is_array($params['product']) && !($params['product'] instanceof ArrayAccess))) {
            return '';
        }

        $payload = $this->buildPixelProductPayloadFromArray($params['product']);
        if (empty($payload['id'])) {
            return '';
        }

        // Determine the SOURCE — what the customer was doing when this hook
        // fired. The bridge surfaces this in telemetry messages so merchants
        // can tell at a glance whether an event was a fresh page load, a
        // combination switch, or a quickview open. Best-effort detection
        // via the standard PS controller query params; falls back to
        // 'initial pageload' if none match.
        //
        //   quickview=1                  → quickview AJAX call (any PS version)
        //   action=refresh + ajax=1      → combination change AJAX refresh
        //                                  (PS 1.7.8+ via displayAjaxRefresh)
        //   otherwise                    → initial product detail page render
        $source = 'initial pageload';
        if (1 === (int) Tools::getValue('quickview')) {
            $source = 'quickview';
        } elseif ('refresh' === Tools::getValue('action')
                  && Tools::getValue('ajax')) {
            $source = 'combination change';
        }

        // The marker is what the bridge looks for on init to decide whether
        // to log PIXEL_HOOK_WIRED or PIXEL_HOOK_MISSING. `hidden` keeps it
        // out of the document flow on every theme.
        // The script uses the same MC_PIXEL_EVENTS_QUEUE pattern as the
        // PURCHASED event (and any future server-emitted events) — a simple
        // array that the bridge converts into a push-interceptor on load so
        // late-arriving inline scripts still trigger fires. `_source` and
        // `_via` are meta hints the bridge strips before forwarding to
        // api.track — `_via` names the exact PHP hook that emitted this
        // push (vs JS event listeners which carry their event name instead).
        // Marker class + JSON-encoded payload are passed to the Smarty partial
        // _pixel_event_push.tpl which renders the marker span (always) + the
        // queue-push reader script (only when payload is non-empty). The
        // marker-only fallback matches the pre-extraction PHP behavior — the
        // bridge can still detect the marker for PIXEL_HOOK_WIRED logging
        // without an associated queue push.
        $json = json_encode(
            [
                'event_type' => 'PRODUCT_VIEWED',
                '_source' => $source,
                '_via' => 'hook (displayProductAdditionalInfo)',
                'product' => $payload,
            ],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
        $this->context->smarty->assign([
            'markerClass' => 'mc-pixel-hook-marker',
            'payload' => (false !== $json) ? $json : '',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/front/_pixel_event_push.tpl');
    }

    /**
     * displayOrderConfirmation hook — Layer 1 of the PURCHASED cascade.
     *
     * PS dispatches this hook from stock OrderConfirmationController via
     * `Hook::exec('displayOrderConfirmation', ['order' => $this->order, ...])`
     * on every PS version 1.7.0.6 → 9.1. The `$params['order']` argument
     * is the loaded Order object — we use it as the authoritative source
     * for the PURCHASED payload (no need to re-load).
     *
     * Emits an inline script that pushes the event payload onto
     * `window.MC_PIXEL_EVENTS_QUEUE`. The bridge's queue interceptor
     * (mc-pixel-bridge.js :: handleQueuedEvent) drains it through
     * `fireOnce('PURCHASED', payload, fp=order.id, ...)`.
     *
     * Layer 2 (AJAX `pixelorderinfo.php`) covers 3rd-party order-
     * confirmation controllers that DON'T fire this hook. The bridge
     * waits ~1 second; if no PURCHASED was queued via Layer 1, it
     * fetches the payload from the server using
     * `MC_PIXEL_ORDER_CONTEXT.id_order` (or key+id_cart for guests).
     *
     * fireOnce dedupes by `order.id` so even if BOTH layers happen to
     * produce a payload (well-behaved 3rd-party module that fires the
     * hook AND has a setMedia 3-prong match), Mailchimp gets ONE
     * PURCHASED per order render.
     *
     * Returns an empty string when:
     *   - API key not set or store not synced (pixel can't work without)
     *   - Script fragment not cached (CSP-blocked or not yet fetched)
     *   - `$params['order']` is missing or not a loaded Order object
     *   - Payload build threw any exception
     * In all those cases Layer 2 has a chance to recover via AJAX.
     *
     * @param array $params hook params; expects $params['order'] = Order
     *
     * @return string inline marker + script for the page, or ''
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (!$this->isApiKeySet() || !$this->isStoreSynced()) {
            return '';
        }
        if (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED)) {
            return '';
        }
        if (!isset($params['order']) || !($params['order'] instanceof Order)
            || !Validate::isLoadedObject($params['order'])) {
            return '';
        }

        try {
            $payload = $this->buildPixelOrderPayload($params['order']);
        } catch (Exception $e) {
            return '';
        }
        if (empty($payload['id'])) {
            return '';
        }

        // Marker class + JSON-encoded payload are passed to the same
        // _pixel_event_push.tpl partial used by displayProductAdditionalInfo.
        // The marker class differs (mc-pixel-order-confirmation-marker) so
        // the bridge can distinguish PURCHASED markers from PRODUCT_VIEWED
        // markers for PIXEL_HOOK_WIRED telemetry.
        $json = json_encode(
            [
                'event_type' => 'PURCHASED',
                '_source' => 'order-confirmation',
                '_via' => 'hook (displayOrderConfirmation)',
                'order' => $payload,
            ],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
        $this->context->smarty->assign([
            'markerClass' => 'mc-pixel-order-confirmation-marker',
            'payload' => (false !== $json) ? $json : '',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/front/_pixel_event_push.tpl');
    }

    /**
     * Build the Pixel-SDK PURCHASED payload from an Order object.
     *
     * Mailchimp 3P spec (Site Tracking Pixel Integration Guide v0.4):
     *
     *   { order: {
     *       id:           '<order id>',                   // REQUIRED
     *       lineItems: [{                                  // REQUIRED
     *         item: { id, productId, title, price, currency, sku, ... },
     *         quantity: N,
     *         price: <line subtotal = item.price × quantity>
     *       }, ...],
     *       subtotalPrice, totalTax, totalShipping, totalPrice,   // optional
     *       currency, customerId, discounts                        // optional
     *   } }
     *
     * order.id: `(string) $order->id` — matches what OrderFormatter sends
     * to Mailchimp's ecommerce REST API at `/ecommerce/stores/{store_id}
     * /orders` (see src/.../Formatters/OrderFormatter.php:108). Keeping
     * the same id across the two surfaces lets Mailchimp correlate pixel
     * events with their ecommerce data.
     *
     * customerId: sha256(email) for logged-in OR guest orders alike
     * (guest orders have a Customer record with is_guest=1 and an email).
     * Matches the format used by identify() and CHECKOUT_STARTED's
     * customerId. Only omitted when the order somehow has no email
     * (shouldn't happen — PS requires email on every order).
     *
     * Tax/totals: same tax-display-mode handling as CHECKOUT_STARTED.
     * Tax-included shops (`Group::getPriceDisplayMethod = PS_TAX_INC`)
     * keep subtotal/shipping tax-inclusive + totalTax=0; tax-excluded
     * shops break tax out. Mirrors the fix from commit 58fce8b.
     *
     * @param Order $order loaded Order object (Validate::isLoadedObject)
     *
     * @return array Mailchimp PURCHASED payload, or [] on failure
     */
    public function buildPixelOrderPayload(Order $order)
    {
        if (!Validate::isLoadedObject($order)) {
            return [];
        }

        $idLang = isset($this->context->language) ? (int) $this->context->language->id : (int) $order->id_lang;
        $currency = '';
        if ((int) $order->id_currency > 0) {
            $curObj = new Currency((int) $order->id_currency);
            if (Validate::isLoadedObject($curObj)) {
                $currency = (string) $curObj->iso_code;
            }
        }
        if ('' === $currency && isset($this->context->currency)) {
            $currency = (string) $this->context->currency->iso_code;
        }

        // Tax-display mode detection — mirror CHECKOUT_STARTED's logic so
        // PURCHASED + CHECKOUT_STARTED payloads stay arithmetically
        // consistent for the same merchant.
        $idDefaultGroup = (int) ((isset($this->context->customer) && (int) $this->context->customer->id_default_group)
            ? $this->context->customer->id_default_group
            : Configuration::get('PS_UNIDENTIFIED_GROUP'));
        if ($idDefaultGroup <= 0) {
            $idDefaultGroup = 1;
        }
        $priceDisplayMethod = (int) Group::getPriceDisplayMethod($idDefaultGroup);
        $taxIncConst = defined('PS_TAX_INC') ? PS_TAX_INC : 0;
        $isTaxIncl = ($priceDisplayMethod === $taxIncConst);

        // Line items — Order::getProducts() returns the order detail rows
        // (1 row per line item, NOT per unit). Fields available:
        //   - product_id, product_attribute_id, product_name, product_reference
        //   - product_quantity (line quantity)
        //   - unit_price_tax_incl / unit_price_tax_excl  (per-unit prices)
        //   - total_price_tax_incl / total_price_tax_excl (line totals)
        //
        // Cross-PS: Order::getProducts exists on PS 1.7.0.6 → 9.1.
        $lineItems = [];
        try {
            $rows = $order->getProducts();
            if (is_array($rows)) {
                $idShop = (int) $order->id_shop;
                foreach ($rows as $r) {
                    $idProduct = isset($r['product_id']) ? (int) $r['product_id'] : 0;
                    if ($idProduct <= 0) {
                        continue;
                    }
                    $idAttribute = isset($r['product_attribute_id']) ? (int) $r['product_attribute_id'] : 0;
                    $variantId = $idAttribute > 0 ? $idAttribute : $idProduct;
                    $unitPrice = $isTaxIncl
                        ? (isset($r['unit_price_tax_incl']) ? (float) $r['unit_price_tax_incl'] : 0.0)
                        : (isset($r['unit_price_tax_excl']) ? (float) $r['unit_price_tax_excl'] : 0.0);
                    $lineTotal = $isTaxIncl
                        ? (isset($r['total_price_tax_incl']) ? (float) $r['total_price_tax_incl'] : 0.0)
                        : (isset($r['total_price_tax_excl']) ? (float) $r['total_price_tax_excl'] : 0.0);
                    $qty = isset($r['product_quantity']) ? (int) $r['product_quantity'] : 1;

                    // Try to load the Product for richer fields (vendor,
                    // imageUrl, productUrl). Falls back to the Order line
                    // data if Product can't be loaded (deleted product etc.)
                    $title = isset($r['product_name']) ? (string) $r['product_name'] : '';
                    $sku = isset($r['product_reference']) ? (string) $r['product_reference'] : '';
                    $vendor = '';
                    $productUrl = '';
                    $imageUrl = '';
                    try {
                        $product = new Product($idProduct, false, $idLang, $idShop);
                        if (Validate::isLoadedObject($product)) {
                            // vendor
                            if (!empty($product->manufacturer_name)) {
                                $vendor = (string) $product->manufacturer_name;
                            } elseif ((int) $product->id_manufacturer > 0) {
                                $vendor = (string) Manufacturer::getNameById((int) $product->id_manufacturer);
                            }
                            // productUrl (hash-stripped, same as PRODUCT_VIEWED)
                            if (isset($this->context->link)) {
                                $productUrl = PrestaChamps\MailchimpPro\Formatters\PixelPayloadFormatter::stripCombinationAnchor(
                                    $this->context->link->getProductLink(
                                        $product,
                                        null,
                                        null,
                                        null,
                                        $idLang ?: null,
                                        null,
                                        $idAttribute > 0 ? $idAttribute : 0
                                    )
                                );
                            }
                            // imageUrl — combination-specific cover where possible
                            if (isset($this->context->link)) {
                                $coverRow = null;
                                if ($idAttribute > 0 && isset($this->context->shop)) {
                                    $coverRow = Image::getBestImageAttribute(
                                        (int) $this->context->shop->id,
                                        $idLang,
                                        $idProduct,
                                        $idAttribute
                                    );
                                }
                                if (!is_array($coverRow) || empty($coverRow['id_image'])) {
                                    $coverRow = Image::getCover($idProduct);
                                }
                                if (is_array($coverRow) && !empty($coverRow['id_image'])) {
                                    $linkRewrite = is_array($product->link_rewrite)
                                        ? (isset($product->link_rewrite[$idLang])
                                            ? (string) $product->link_rewrite[$idLang]
                                            : (string) reset($product->link_rewrite))
                                        : (string) $product->link_rewrite;
                                    $imageUrl = (string) $this->context->link->getImageLink(
                                        $linkRewrite,
                                        (int) $coverRow['id_image'],
                                        'large_default'
                                    );
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Defensive — fall back to order line data only.
                    }

                    $lineItems[] = [
                        'item' => [
                            'id' => (string) $variantId,
                            'productId' => (string) $idProduct,
                            'title' => $title,
                            'price' => $unitPrice,
                            'currency' => $currency,
                            'sku' => $sku,
                            'productUrl' => $productUrl,
                            'imageUrl' => $imageUrl,
                            'vendor' => $vendor,
                            // categories omitted — same data-quality concern
                            // as the cart-shape line items (see
                            // mc-pixel-bridge.js :: buildCartLineProductItem
                            // comment). Cart-shape exposes only the URL slug;
                            // Order-shape doesn't expose category names at
                            // all without an extra query per line.
                        ],
                        'quantity' => $qty,
                        'price' => $lineTotal,
                    ];
                }
            }
        } catch (Exception $e) {
            // No line items — payload will still be returned with empty
            // lineItems[]; bridge's fireOnce gate will block fire (Mailchimp
            // wouldn't accept PURCHASED with zero line items anyway).
        }

        // Totals — Order has the authoritative finalized numbers. Helper handles
        // the tax-incl vs tax-excl breakdown; Mailchimp's totalPrice is always
        // the actual amount paid (tax-incl).
        $totals = PrestaChamps\MailchimpPro\Formatters\PixelPayloadFormatter::deriveOrderTotals(
            [
                'total_paid_tax_incl' => (float) $order->total_paid_tax_incl,
                'total_paid_tax_excl' => (float) $order->total_paid_tax_excl,
                'total_products' => (float) $order->total_products,
                'total_products_wt' => (float) $order->total_products_wt,
                'total_shipping_tax_incl' => (float) $order->total_shipping_tax_incl,
                'total_shipping_tax_excl' => (float) $order->total_shipping_tax_excl,
            ],
            $isTaxIncl
        );
        $totalPrice = $totals['totalPrice'];
        $subtotalPrice = $totals['subtotalPrice'];
        $totalShipping = $totals['totalShipping'];
        $totalTax = $totals['totalTax'];

        // Applied discounts (vouchers) — Order::getCartRules returns the
        // discount entries linked to this order; the helper shapes each row
        // into the {code, amount, type:'amount'} Mailchimp expects.
        $discounts = [];
        try {
            $rules = method_exists($order, 'getCartRules') ? $order->getCartRules() : [];
            if (is_array($rules)) {
                foreach ($rules as $r) {
                    $entry = PrestaChamps\MailchimpPro\Formatters\PixelPayloadFormatter::buildDiscountEntry($r);
                    if (null !== $entry) {
                        $discounts[] = $entry;
                    }
                }
            }
        } catch (Exception $e) {
            // leave discounts empty
        }

        // customerId — sha256(email) for both logged-in AND guest orders.
        // The order has the customer's email regardless of login state
        // (PS creates a Customer record with is_guest=1 for guest orders).
        $customerId = '';
        try {
            $customer = new Customer((int) $order->id_customer);
            if (Validate::isLoadedObject($customer) && !empty($customer->email)) {
                $email = strtolower(trim((string) $customer->email));
                if ('' !== $email) {
                    $customerId = hash('sha256', $email);
                }
            }
        } catch (Exception $e) {
            // leave customerId empty
        }

        $payload = [
            'id' => (string) $order->id,
            'lineItems' => $lineItems,
            'subtotalPrice' => $subtotalPrice,
            'totalTax' => $totalTax,
            'totalShipping' => $totalShipping,
            'totalPrice' => $totalPrice,
            'currency' => $currency,
        ];
        if ('' !== $customerId) {
            $payload['customerId'] = $customerId;
        }
        if (!empty($discounts)) {
            $payload['discounts'] = $discounts;
        }

        return $payload;
    }

    /**
     * Build the Pixel-SDK product payload from a Product object directly,
     * bypassing the heavy getTemplateVarProduct() / ProductPresenter
     * pipeline that runs ~15-25ms of presenter work per call.
     *
     * Produces the SAME 10-field output shape as
     * buildPixelProductPayloadFromArray() (single source of truth, identical
     * fingerprints → JS-side dedupe matches), but reads trivial fields
     * directly off the loaded Product instance and does only 4 targeted
     * lookups for the optional fields:
     *
     *   - title / sku / vendor (manufacturer_name): direct Product props (~0ms)
     *   - price: Product::getPriceStatic(..., $with_tax=true, $idAttribute)
     *   - imageUrl: Image::getCover() + Link::getImageLink()
     *   - productUrl: Link::getProductLink()
     *   - categories: Product::getProductCategoriesFull()
     *
     * Total cost: ~5-8ms (vs ~15-25ms for the presenter path). Used as the
     * PRIMARY path in hookActionFrontControllerSetMedia for product pages,
     * with getTemplateVarProduct() kept as a safety-net fallback when this
     * fails for any reason (custom controller, edge case, exception). The
     * fallback preserves pre-optimization behavior — worst case = previous
     * baseline.
     *
     * Cross-PS portability (1.7.0.6 → 9.1):
     *   - Product::getPriceStatic / getProductCategoriesFull — stable
     *   - Image::getCover — stable
     *   - Manufacturer::getNameById — stable
     *   - Link::getProductLink / getImageLink — signature has grown but the
     *     positional args we use are stable
     *   - Combination reference — direct DB query with casts (no
     *     dependency on Combination::* methods that vary across versions)
     *
     * @param Product $product A loaded Product object
     *                         (Validate::isLoadedObject required)
     * @param int $idAttribute id_product_attribute (variant id) or 0
     *
     * @return array Pixel-SDK product payload, or [] on failure
     */
    public function buildPixelProductPayloadFromProduct(Product $product, $idAttribute = 0)
    {
        if (!Validate::isLoadedObject($product)) {
            return [];
        }

        $idProduct = (int) $product->id;
        $idAttribute = (int) $idAttribute;
        $idLang = isset($this->context->language) ? (int) $this->context->language->id : 0;
        $resolvedId = $idAttribute > 0 ? $idAttribute : $idProduct;

        // title — multi-lang Product loads return an array keyed by id_lang;
        // ProductController loads with a specific lang and gets a string.
        $title = '';
        if (is_array($product->name)) {
            $title = isset($product->name[$idLang])
                ? (string) $product->name[$idLang]
                : (string) reset($product->name);
        } else {
            $title = (string) $product->name;
        }

        // price — tax-incl per shop config + attribute-specific impact. The
        // canonical helper that drives PS's own presenter pipeline.
        $price = 0.0;
        try {
            $price = (float) Product::getPriceStatic(
                $idProduct,
                true,
                $idAttribute > 0 ? $idAttribute : null
            );
        } catch (Exception $e) {
            $price = (float) $product->price;
        }

        // sku — combination reference takes precedence over product reference
        // when a variant is selected (matches what merchant sees in BO).
        $sku = (string) $product->reference;
        if ($idAttribute > 0) {
            try {
                $combinationRef = (string) Db::getInstance()->getValue(
                    'SELECT `reference` FROM `' . _DB_PREFIX_ . 'product_attribute`
                     WHERE `id_product_attribute` = ' . (int) $idAttribute
                );
                if ('' !== $combinationRef) {
                    $sku = $combinationRef;
                }
            } catch (Exception $e) {
                // keep product-level reference as best-effort
            }
        }

        // productUrl — variant-specific link when an attribute is selected.
        // Strip the `#/<attr>-<val>/...` anchor PS appends by default — that
        // anchor encodes the ORIGINAL variant's display attributes and goes
        // stale the moment the customer switches combination (since the
        // bridge's URL rewrite swaps the slug but not the hash). Mailchimp
        // doesn't need the anchor (it's PS internal state for combination
        // restoration on page reload); the slug-only URL is correct +
        // clickable + tracking-stable.
        // The $addAnchor parameter to getProductLink could disable this
        // server-side but its positional index differs across PS versions
        // (PS 1.7.0.6 = arg 10, PS 1.7.8+ = arg 12), so we strip in PHP
        // instead — version-agnostic.
        $productUrl = '';
        if (isset($this->context->link)) {
            try {
                $productUrl = PrestaChamps\MailchimpPro\Formatters\PixelPayloadFormatter::stripCombinationAnchor(
                    $this->context->link->getProductLink(
                        $product,
                        null,
                        null,
                        null,
                        $idLang ?: null,
                        null,
                        $idAttribute > 0 ? $idAttribute : 0
                    )
                );
            } catch (Exception $e) {
                // leave productUrl empty
            }
        }

        // imageUrl — cover image URL construction. For combinations,
        // prefer the combination-specific image so the merchant's customer
        // sees the SAME image they clicked on reflected in the pixel
        // payload (e.g. "white t-shirt" vs "black t-shirt" — different
        // images for the same parent product).
        //
        // Image::getBestImageAttribute($idShop, $idLang, $idProduct, $idAttr)
        // is the right API:
        //   - Stable signature on PS 1.7.0.6 → 9.1
        //   - Internally falls back to product cover when the combination
        //     has no own image (so non-imaged variants still produce a URL)
        // For non-combination views ($idAttribute === 0) we go straight
        // to Image::getCover.
        $imageUrl = '';
        if (isset($this->context->link)) {
            try {
                $coverRow = null;
                if ($idAttribute > 0 && isset($this->context->shop)) {
                    $coverRow = Image::getBestImageAttribute(
                        (int) $this->context->shop->id,
                        $idLang,
                        $idProduct,
                        $idAttribute
                    );
                }
                if (!is_array($coverRow) || empty($coverRow['id_image'])) {
                    $coverRow = Image::getCover($idProduct);
                }
                if (is_array($coverRow) && !empty($coverRow['id_image'])) {
                    $linkRewrite = '';
                    if (is_array($product->link_rewrite)) {
                        $linkRewrite = isset($product->link_rewrite[$idLang])
                            ? (string) $product->link_rewrite[$idLang]
                            : (string) reset($product->link_rewrite);
                    } else {
                        $linkRewrite = (string) $product->link_rewrite;
                    }
                    $imageUrl = (string) $this->context->link->getImageLink(
                        $linkRewrite,
                        (int) $coverRow['id_image'],
                        'large_default'
                    );
                }
            } catch (Exception $e) {
                // leave imageUrl empty
            }
        }

        // vendor (Mailchimp's term) = PS's manufacturer. Product loads
        // populate manufacturer_name when $full=true (ProductController does
        // exactly that). Fallback to direct lookup for completeness.
        $vendor = '';
        if (!empty($product->manufacturer_name)) {
            $vendor = (string) $product->manufacturer_name;
        } elseif ((int) $product->id_manufacturer > 0) {
            try {
                $vendor = (string) Manufacturer::getNameById((int) $product->id_manufacturer);
            } catch (Exception $e) {
                // leave vendor empty
            }
        }

        // categories — flat array of unique category names. PS's
        // getProductCategoriesFull returns rows with `name` keyed by category
        // id; we collect the names in the order PS returns them.
        $categories = [];
        try {
            $catRows = Product::getProductCategoriesFull($idProduct, $idLang);
            if (is_array($catRows)) {
                foreach ($catRows as $row) {
                    if (isset($row['name']) && '' !== $row['name']
                        && !in_array((string) $row['name'], $categories, true)) {
                        $categories[] = (string) $row['name'];
                    }
                }
            }
        } catch (Exception $e) {
            // leave categories empty
        }

        $currency = isset($this->context->currency)
            ? (string) $this->context->currency->iso_code
            : '';

        return [
            'id' => (string) $resolvedId,
            'productId' => (string) $idProduct,
            'title' => $title,
            'price' => $price,
            'currency' => $currency,
            'sku' => $sku,
            'productUrl' => $productUrl,
            'imageUrl' => $imageUrl,
            'vendor' => $vendor,
            'categories' => $categories,
        ];
    }

    /**
     * Build the Pixel-SDK-shaped product object from the PS-presented
     * product. Accepts either a plain array (PS 1.7) or a ProductLazyArray
     * (PS 9.1, which implements ArrayAccess). Same shape PS hands to the
     * `displayProductAdditionalInfo` hook via $params['product'] AND that
     * the controller's getTemplateVarProduct() returns — single source of
     * truth, both code paths produce identical fingerprints (critical for
     * the JS-side dedupe to work).
     *
     * Output matches Mailchimp Pixel SDK's `product` schema (per the Site
     * Tracking Pixel Integration Guide v0.4):
     *   - `id`        = variant id (id_product_attribute when present,
     *                   falls back to id_product when there's no combination).
     *                   This is the field Mailchimp uses to identify the
     *                   specific SKU a visitor saw.
     *   - `productId` = parent product id (always id_product).
     *   - other camelCase fields per the SDK schema.
     *
     * NOTE: this is the Pixel SDK shape, NOT the REST e-commerce-API shape
     * the existing CartFormatter / ProductFormatter classes target (those
     * use snake_case product_id + product_variant_id). The underlying
     * VALUES are identical so Mailchimp can correlate pixel events with
     * the data its server-to-server sync also receives.
     *
     * @param array|ArrayAccess $product PS-presented product (array on
     *                                   PS 1.7, ProductLazyArray on 9.1)
     *
     * @return array Pixel-SDK product payload
     */
    public function buildPixelProductPayloadFromArray($product)
    {
        return PrestaChamps\MailchimpPro\Formatters\PixelPayloadFormatter::fromArray(
            $product,
            isset($this->context->currency) ? (string) $this->context->currency->iso_code : '',
            isset($this->context->link) ? $this->context->link : null,
            isset($this->context->language) ? (int) $this->context->language->id : 0
        );
    }

    /**
     * Mailchimp API client factory.
     *
     * @throws Exception
     */
    public function getApiClient($idStore = null)
    {
        if (!$idStore) {
            $idStore = PrestaChamps\MailchimpPro\Helpers\PsContext::getShopId();
        }

        if ($this->apiClient instanceof PrestaChamps\MailChimpAPI) {
            $reflectionClass = new ReflectionClass('\PrestaChamps\MailChimpAPI');
            $reflectionProperty = $reflectionClass->getProperty('api_key');
            $reflectionProperty->setAccessible(true);

            $this->apiClient->setUserAgent($this->version);

            if (Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY, null, null, $idStore) == $reflectionProperty->getValue($this->apiClient)) {
                return $this->apiClient;
            }
        }

        $this->apiClient = new PrestaChamps\MailChimpAPI(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY, null, null, $idStore));

        $this->apiClient->setUserAgent($this->version);

        return $this->apiClient;
    }

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function sendApiRequest($url, $method, $data = [])
    {
        if ('POST' === $method) {
            $this->getApiClient()->post($url, $data);
        } elseif ('PATCH' === $method) {
            $this->getApiClient()->patch($url, $data);
        } elseif ('PUT' === $method) {
            $this->getApiClient()->put($url, $data);
        } elseif ('DELETE' === $method) {
            $this->getApiClient()->delete($url, $data);
        } else {
            $this->getApiClient()->get($url, $data);
        }

        return $this->getApiClient()->getLastResponse();
    }

    /**
     * Display site MailChimp site verification.
     *
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                if ((bool) version_compare(_PS_VERSION_, '1.7', '<')) {
                    $subscriptionIsEnabled = call_user_func(['Module', 'isEnabled'], 'blocknewsletter');

                    if (Tools::isSubmit('submitNewsletter') && $subscriptionIsEnabled) {
                        try {
                            PrestaChamps\MailchimpPro\Hooks\Display\FooterBefore::run(
                                $params,
                                $this->getApiClient(),
                                $this->context
                            )->newsletterBlockRegistration();
                        } catch (Exception $exception) {
                            PrestaShopLogger::addLog("[MAILCHIMP-NW16] :{$exception->getMessage()}");
                        }
                    }
                }
            }
        }

        return '';
    }

    public function hookDashboardZoneOne($params)
    {
        if (MailchimpProConfig::getConfigurationValues()[MailchimpProConfig::SHOW_DASHBOARD_STATS]) {
            if ($this->isApiKeySet()) {
                $this->context->controller->addCSS($this->_path . 'views/css/backoffice_dashboard.css');

                $this->context->smarty->assign([
                    'configurationLink' => $this->context->link->getAdminLink('AdminMailchimpProConfiguration') . '#statistics',
                    'dashboardLink' => $this->context->link->getAdminLink('AdminDashboard', true, [], ['refreshMailchimpReports' => 1]),
                ]);
                $this->assignStatisticsVariables();

                return $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/front/_dashboard-statistics.tpl');
            }
        }
    }

    public function assignStatisticsVariables($params = [])
    {
        $this->context->smarty->assign(['statistics' => $this->getReportsVariables($params)]);
    }

    public function getReportsVariables($params = [])
    {
        if (Configuration::hasKey(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED)
            && ($campaingReports = Configuration::get(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED))
            && Configuration::hasKey(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE)
            && ($campaingReportsLastUpdate = Configuration::get(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE))
            && !Tools::getValue('refreshMailchimpReports')
            && !(isset($params['refresh']) && $params['refresh'])) {
            $campaingReports = json_decode($campaingReports, true);
            $campaingReports['last_update'] = $campaingReportsLastUpdate . ' (' . date_default_timezone_get() . ')';

            // daily update the campaing reports
            if ((time() - strtotime($campaingReportsLastUpdate)) <= 86400) {
                return $campaingReports;
            }
        }

        $reportsEndpoint = '/reports?count=5';
        $campaingReports = [
            'reports' => [],
            'total_items' => 0,
        ];
        try {
            $response = $this->getApiClient()->get($reportsEndpoint);

            if ($this->getApiClient()->success()) {
                $campaingReports = $response;

                // Keys to unset from response campaingReports array
                $keysToUnset = ['_links', 'timeseries'];
                $this->unsetKeysRecursive($campaingReports, $keysToUnset);

                $totalEmailsSent = 0;
                $totalEmailUniqueOpens = 0;
                $avgEmailUniqueOpens = 0;
                $totalEmailUniqueClicks = 0;
                $avgEmailUniqueClicks = 0;
                $totalEmailsBounced = 0;
                $avgEmailsBounced = 0;
                // $totalOpenRate = 0;
                $totalUnsubscribed = 0;
                $avgUnsubscribed = 0;

                foreach ($campaingReports['reports'] as $key => &$report) {
                    $totalEmailsSent += $report['emails_sent'];
                    $totalEmailsBounced += array_sum(array_values($report['bounces']));
                    $totalEmailUniqueOpens += $report['opens']['unique_opens'];
                    $totalEmailUniqueClicks += $report['clicks']['unique_clicks'];
                    // $totalOpenRate += $report['opens']['open_rate'];
                    $totalUnsubscribed += $report['unsubscribed'];

                    if (!empty($report['send_time'])) {
                        $send_time = new DateTime($report['send_time']);
                        $report['send_time'] = $send_time->format('Y-m-d H:i:s');
                    }
                }
                if ($totalEmailsSent && $totalEmailsBounced) {
                    // $avgEmailsBounced = $totalEmailsBounced / count($campaingReports['reports']) * 100;
                    $avgEmailsBounced = $totalEmailsBounced / $totalEmailsSent * 100;
                }
                // if ($totalOpenRate) {
                //    $avgEmailUniqueOpens = $totalOpenRate / count($campaingReports['reports']) * 100;
                // }
                if ($totalEmailUniqueOpens && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgEmailUniqueOpens = $totalEmailUniqueOpens / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }
                if ($totalEmailUniqueClicks && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgEmailUniqueClicks = $totalEmailUniqueClicks / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }
                if ($totalUnsubscribed && ($totalEmailsSent > $totalEmailsBounced)) {
                    $avgUnsubscribed = $totalUnsubscribed / ($totalEmailsSent - $totalEmailsBounced) * 100;
                }

                $campaingReports['total_emails_sent'] = $totalEmailsSent;
                $campaingReports['total_emails_bounced'] = $totalEmailsBounced;
                $campaingReports['total_emails_unique_opens'] = $totalEmailUniqueOpens;
                $campaingReports['total_emails_unique_clicks'] = $totalEmailUniqueClicks;
                $campaingReports['total_unsubscribed'] = $totalUnsubscribed;
                $campaingReports['avg_emails_bounce_rate'] = [
                    'value' => $avgEmailsBounced,
                    'text' => round($avgEmailsBounced, 2) . '%',
                ];
                $campaingReports['avg_email_unique_open_rate'] = [
                    'value' => $avgEmailUniqueOpens,
                    'text' => round($avgEmailUniqueOpens, 2) . '%',
                ];
                $campaingReports['avg_email_unique_click_rate'] = [
                    'value' => $avgEmailUniqueClicks,
                    'text' => round($avgEmailUniqueClicks, 2) . '%',
                ];
                $campaingReports['avg_unsubscribe_rate'] = [
                    'value' => $avgUnsubscribed,
                    'text' => round($avgUnsubscribed, 2) . '%',
                ];
            } else {
                $campaingReports = [
                    'campaign_error' => 'Status: ' . $response['status'] . ' - ' . $response['title'] . ' (' . $response['detail'] . ')',
                ];
            }
        } catch (Exception $e) {
            // Handle API errors
            $campaingReports = [
                'campaign_error' => $e->getMessage(),
            ];
        }

        // Store the value in the configuration
        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED, json_encode($campaingReports));
        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE, date('Y-m-d H:i:s'));

        $campaingReports['last_update'] = date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ')';

        return $campaingReports;
    }

    public static function unsetKeysRecursive(&$array, $keysToUnset)
    {
        foreach ($array as $key => &$value) {
            if (in_array($key, $keysToUnset)) {
                unset($array[$key]); // Unset the key
            } elseif (is_array($value)) {
                self::unsetKeysRecursive($value, $keysToUnset); // Recurse into subarrays
            }
        }
        unset($value); // Prevent reference issues
    }

    /**
     * Sync the newly created customer to MailChimp.
     */
    public function hookActionObjectCustomerAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    /**
                     * @var Customer $customer
                     */
                    $customer = $params['object'];
                    // check filter customers to sync
                    if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                        && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$customer->id]
                            );
                            $command->triggerDoubleOptIn(true);
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_PUT);
                            $command->execute();
                        } else {
                            $job = new PrestaChamps\Queue\Jobs\CustomerSyncJob();
                            $job->customerId = $customer->id;
                            $job->triggerDoubleOptIn(true);
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $queue = new PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-customer-add-after', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Sync the newly created address for customer to MailChimp.
     */
    public function hookActionObjectAddressAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    /**
                     * @var Address $address
                     */
                    $address = $params['object'];

                    $customer = new Customer($address->id_customer);
                    // check filter customers to sync
                    if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                        && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$address->id_customer]
                            );
                            $command->triggerDoubleOptIn(true);
                            if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                $command->setUpdateSubscriptionStatus(false);
                            }
                            $command->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $command->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $command->execute();
                        } else {
                            $job = new PrestaChamps\Queue\Jobs\CustomerSyncJob();
                            $job->customerId = $address->id_customer;
                            $job->triggerDoubleOptIn(true);
                            if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                $job->setUpdateSubscriptionStatus(false);
                            }
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                            $queue = new PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-address-add-after', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * @throws Exception
     *
     * @todo Refactor code to use a service pattern
     */
    public function hookActionObjectCartRuleAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                // Skip enqueue if the cart rule doesn't pass the merchant's filter
                // (e.g. draft created with active=0, or scheduled for a future date).
                // See cartRulePassesSyncFilter() for rationale — mirrors the product
                // hook pattern.
                if (!$this->cartRulePassesSyncFilter($object)) {
                    return;
                }
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_POST);
                    $command->execute();
                } else {
                    $job = new PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_POST);
                    $queue = new PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-add-after', $this->context->shop->id);
                }
            }
        }
    }

    /**
     * @throws Exception
     *
     * @todo Refactor code to use a service pattern
     */
    public function hookActionObjectCartRuleUpdateAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                // Skip enqueue if the cart rule doesn't pass the merchant's filter.
                // Prevents PATCH-then-404 pollution when a script bulk-touches
                // filter-excluded cart rules (inactive / expired) that were never
                // synced to Mailchimp at initial sync time. Same class of bug as
                // the product hook — see cartRulePassesSyncFilter() for rationale.
                if (!$this->cartRulePassesSyncFilter($object)) {
                    return;
                }
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PATCH);
                    $command->execute();
                } else {
                    $job = new PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_PATCH);
                    $queue = new PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-update-after', $this->context->shop->id);
                }
            }
        }
    }

    /**
     * @throws Exception
     *
     * @todo Refactor code to use a service pattern
     */
    public function hookActionObjectCartRuleDeleteBefore($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CART_RULES)) {
                $object = new CartRule($params['object']->id, $this->context->language->id);
                // No filter guard on purpose. Cart rule may have been on Mailchimp
                // (created active, later deactivated or expired) and now the merchant
                // is deleting it entirely — always propagate the DELETE. Mirrors the
                // product delete hook. A 404 on a never-synced cart rule is harmless
                // (job drops with no downstream effect).
                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_DELETE);
                    $command->execute();
                } else {
                    $job = new PrestaChamps\Queue\Jobs\CartRuleSyncJob();
                    $job->cartRuleId = $object->id;
                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand::SYNC_METHOD_DELETE);
                    $queue = new PrestaChamps\Queue\Queue();
                    $queue->push($job, 'hook-cartrule-delete-before', $this->context->shop->id);
                }
            }
        }
    }

    /**
     * Create or update the cart in Mailchimp.
     *
     * @throws Exception
     *
     * @todo Use command pattern instead
     */
    public function hookActionCartSave($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced() && ('adminaddresses' !== Tools::getValue('controller'))) {
            if (Configuration::get(MailchimpProConfig::SYNC_CARTS)) {
                try {
                    PrestaChamps\MailchimpPro\Hooks\Action\CartSave::run(
                        $this->context,
                        $this->getApiClient()
                    );
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Sync the order status update to MailChimp.
     */
    public function hookActionOrderStatusUpdate($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_ORDERS)) {
                try {
                    $orderId = null;
                    if (isset($params['id_order'])) {
                        $orderId = $params['id_order'];
                    }
                    if (isset($params['newOrderStatus']) && isset($params['newOrderStatus'], $params['newOrderStatus']->id_order)) {
                        $orderId = $params['newOrderStatus']->id_order;
                    }
                    if ($orderId) {
                        $order = new Order($orderId, $this->context->language->id);

                        if (in_array($order->getCustomer()->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                            && in_array($order->getCustomer()->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                $command = new PrestaChamps\MailchimpPro\Commands\OrderSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$orderId]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                if (!empty($params['newOrderStatus']->id)) {
                                    // Provide the new idOrderState to the command
                                    $command->setIdOrderStates([$orderId => (int) $params['newOrderStatus']->id]);
                                }
                                $command->setMethod($command::SYNC_METHOD_PATCH);

                                $result = $command->execute();

                                if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                    $command->setMethod($command::SYNC_METHOD_POST);
                                    $result = $command->execute();
                                }
                            } else {
                                $job = new PrestaChamps\Queue\Jobs\OrderSyncJob();
                                $job->orderId = $orderId;
                                $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\OrderSyncCommand::SYNC_MODE_REGULAR);
                                if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                                    $job->setCampaignId($_COOKIE['mc_cid']);
                                }
                                $queue = new PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-order-status-update', $this->context->shop->id);
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionValidateOrder($params)
    {
        if (isset($params['order']) && is_subclass_of($params['order'], 'OrderCore') && $this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_ORDERS)) {
                try {
                    $order = new Order($params['order']->id, $this->context->language->id);

                    if (in_array($order->getCustomer()->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                        && in_array($order->getCustomer()->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new PrestaChamps\MailchimpPro\Commands\OrderSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$params['order']->id]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);

                            $command->setMethod($command::SYNC_METHOD_PATCH);

                            $result = $command->execute();

                            if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                $command->setMethod($command::SYNC_METHOD_POST);
                                $result = $command->execute();
                            }

                            $command = new PrestaChamps\MailchimpPro\Commands\CartSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$order->id_cart]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_DELETE);
                            $command->execute();
                        } else {
                            $job = new PrestaChamps\Queue\Jobs\OrderSyncJob();
                            $job->orderId = $params['order']->id;
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\OrderSyncCommand::SYNC_MODE_REGULAR);
                            if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                                $job->setCampaignId($_COOKIE['mc_cid']);
                            }
                            $queue = new PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-action-validate-order', $this->context->shop->id);

                            $job = new PrestaChamps\Queue\Jobs\CartSyncJob();
                            $job->cartId = $order->id_cart;
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CartSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(PrestaChamps\MailchimpPro\Commands\CartSyncCommand::SYNC_METHOD_DELETE);

                            $queue->push($job, 'hook-action-validate-order', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = "[MAILCHIMP] :{$exception->getMessage()}";
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Delete the objects from the MailChimp account also.
     */
    /**
     * Check whether a product matches the merchant's product-sync filter
     * (active status + visibility). Same filter that EntitySyncRepository::products
     * applies at initial-sync time — replicated here so hook-based enqueues
     * don't create jobs for products that would never have been POSTed to
     * Mailchimp in the first place. Without this check, a PATCH job for an
     * excluded product 404s (product doesn't exist on MC), fails the job,
     * and — since products have the highest queue priority — burns cron
     * slots that would otherwise process customers / orders / carts.
     *
     * @return bool true if product should be synced under the current filter
     */
    public function productPassesSyncFilter($product)
    {
        if (!is_object($product)) {
            return false;
        }
        $activeAllowed = isset($this->configuration[MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE])
            ? $this->configuration[MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE]
            : null;
        $visibilityAllowed = isset($this->configuration[MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY])
            ? $this->configuration[MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY]
            : null;
        if (is_array($activeAllowed)
            && !in_array((int) $product->active, array_map('intval', $activeAllowed), true)) {
            return false;
        }
        if (is_array($visibilityAllowed) && property_exists($product, 'visibility')
            && !in_array((string) $product->visibility, $visibilityAllowed, true)) {
            return false;
        }

        return true;
    }

    /**
     * Check whether a cart rule matches the merchant's cart-rule-sync filter
     * (status + implicit quantity + expiration). Mirrors the DB filter that
     * EntitySyncRepository::cartRules applies at initial-sync time — replicated
     * here so hook-based enqueues don't create jobs for cart rules that would
     * never have been POSTed to Mailchimp in the first place. Without this
     * check, a PATCH job for an excluded cart rule 404s on Mailchimp (never
     * synced there), fails the job, and pollutes the cron queue.
     *
     * Filter semantics (same as EntitySyncRepository::cartRules):
     *   - STATUS filter: `active` must be in the allowed set (typically [1])
     *   - Implicit quantity gate: when STATUS excludes 0, also require quantity > 0
     *   - EXPIRATION filter: when EXPIRATION excludes 0, require date_from <= now
     *     AND date_to >= now (i.e. currently valid)
     *
     * @return bool true if cart rule should be synced under the current filter
     */
    public function cartRulePassesSyncFilter($cartRule)
    {
        if (!is_object($cartRule)) {
            return false;
        }
        $statusAllowed = isset($this->configuration[MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS])
            ? $this->configuration[MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS]
            : null;
        $expirationAllowed = isset($this->configuration[MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION])
            ? $this->configuration[MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION]
            : null;

        if (is_array($statusAllowed)
            && !in_array((int) $cartRule->active, array_map('intval', $statusAllowed), true)) {
            return false;
        }

        $statusExcludesZero = is_array($statusAllowed)
            && !in_array(0, array_map('intval', $statusAllowed), true);
        if ($statusExcludesZero && property_exists($cartRule, 'quantity')
            && (int) $cartRule->quantity <= 0) {
            return false;
        }

        $expirationExcludesZero = is_array($expirationAllowed)
            && !in_array(0, array_map('intval', $expirationAllowed), true);
        if ($expirationExcludesZero) {
            $now = time();
            if (property_exists($cartRule, 'date_from') && !empty($cartRule->date_from)
                && strtotime($cartRule->date_from) > $now) {
                return false;
            }
            if (property_exists($cartRule, 'date_to') && !empty($cartRule->date_to)
                && strtotime($cartRule->date_to) < $now) {
                return false;
            }
        }

        return true;
    }

    public function hookActionProductUpdate($params)
    {
        if (isset($params['product']) && $this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    /** @var Product $product */
                    $product = $params['product'];
                    if (is_a($product, 'ProductCore')) {
                        // Skip enqueue entirely if the product doesn't pass the
                        // merchant's sync filter. Rationale:
                        //   - Filter-excluded products were skipped at initial sync,
                        //     so they were never POSTed to Mailchimp
                        //   - Enqueueing anything (PATCH or DELETE) for them just
                        //     produces 404s that waste priority-1 cron slots and
                        //     starve customer/order queues (the queue-growing
                        //     symptom this fix targets)
                        //
                        // Trade-off: a product transitioning from filter-passing to
                        // filter-failing (e.g. deactivated under filter=[1]) won't
                        // get a DELETE enqueued to clean up its stale Mailchimp
                        // record. Merchant does a full store re-sync to clean.
                        // Product deletion from PS (not deactivation) is handled
                        // by hookActionObjectDeleteAfter, which correctly enqueues
                        // DELETE regardless of filter — untouched by this fix.
                        if (!$this->productPassesSyncFilter($product)) {
                            return;
                        }
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$product->id]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_PATCH);
                            $result = $command->execute();

                            if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                $command->setMethod($command::SYNC_METHOD_POST);
                                $result = $command->execute();
                            }
                        } else {
                            $job = new PrestaChamps\Queue\Jobs\ProductSyncJob();
                            $job->productId = $product->id;
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                            $queue = new PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-product-update', $this->context->shop->id);
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    /**
     * Delete the objects from the MailChimp account also.
     */
    public function hookActionObjectDeleteAfter($object)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (is_subclass_of($object['object'], 'ProductCore')) {
                if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                    try {
                        $objectId = $object['object']->id;
                        if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                            $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                $this->context,
                                $this->getApiClient(),
                                [$objectId]
                            );
                            $command->setSyncMode($command::SYNC_MODE_REGULAR);
                            $command->setMethod($command::SYNC_METHOD_DELETE);
                            $command->execute();
                        } else {
                            $job = new PrestaChamps\Queue\Jobs\ProductSyncJob();
                            $job->productId = $objectId;
                            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                            $job->setMethod(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_METHOD_DELETE);
                            $queue = new PrestaChamps\Queue\Queue();
                            $queue->push($job, 'hook-object-delete-after', $this->context->shop->id);
                        }
                    } catch (Exception $e) {
                        $this->context->controller->errors[] = "[MAILCHIMP] :{$e->getMessage()}";
                        PrestaShopLogger::addLog("[MAILCHIMP] :{$e->getMessage()}");
                    }
                }
            } elseif (is_subclass_of($object['object'], 'AddressCore')) {
                if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                    try {
                        $customerId = $object['object']->id_customer;
                        if ($customerId) {
                            $customer = new Customer($customerId);
                            // check filter customers to sync
                            if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                                && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    $command = new PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$customerId]
                                    );
                                    if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                        $command->setUpdateSubscriptionStatus(false);
                                    }
                                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                    $command->setMethod($command::SYNC_METHOD_PUT);
                                    $command->execute();
                                } else {
                                    $job = new PrestaChamps\Queue\Jobs\CustomerSyncJob();
                                    $job->customerId = $customerId;
                                    if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                        $job->setUpdateSubscriptionStatus(false);
                                    }
                                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $job->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $queue = new PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-customer-add-after', $this->context->shop->id);
                                }
                            }
                        }
                    } catch (Exception $exception) {
                        $this->context->controller->errors[] = $exception->getMessage();
                        PrestaShopLogger::addLog(
                            "[MAILCHIMP]: {$exception->getMessage()}",
                            1,
                            $exception->getCode(),
                            PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::class,
                            null,
                            true
                        );
                    }
                }
            }
        }
    }

    /**
     * Sync the object updates to Mailchimp.
     */
    public function hookActionObjectUpdateAfter($object)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                if (is_subclass_of($object['object'], 'CustomerCore') || is_subclass_of($object['object'], 'AddressCore')) {
                    try {
                        if (is_subclass_of($object['object'], 'CustomerCore')) {
                            $customerId = $object['object']->id;
                        } elseif (is_subclass_of($object['object'], 'AddressCore')) {
                            $customerId = $object['object']->id_customer;
                        }
                        if (isset($customerId) && $customerId) {
                            $customer = new Customer($customerId);
                            // check filter customers to sync
                            if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                                && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    $command = new PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$customerId]
                                    );
                                    if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                        $command->setUpdateSubscriptionStatus(false);
                                    }
                                    $command->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $command->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $command->execute();
                                } else {
                                    $job = new PrestaChamps\Queue\Jobs\CustomerSyncJob();
                                    $job->customerId = $customerId;
                                    if (isset($this->context->controller) && 'admin' == $this->context->controller->controller_type) {
                                        $job->setUpdateSubscriptionStatus(false);
                                    }
                                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_MODE_REGULAR);
                                    $job->setMethod(PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::SYNC_METHOD_PUT);
                                    $queue = new PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-object-update-after', $this->context->shop->id);
                                }
                            }
                        }
                    } catch (Exception $exception) {
                        $this->context->controller->errors[] = $exception->getMessage();
                        PrestaShopLogger::addLog(
                            "[MAILCHIMP]: {$exception->getMessage()}",
                            1,
                            $exception->getCode(),
                            PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand::class,
                            null,
                            true
                        );
                    }
                }
            }

            if (is_subclass_of($object['object'], 'ShopCore')) {
                try {
                    $command = new PrestaChamps\MailchimpPro\Commands\StoreSyncCommand(
                        $this->context,
                        $this->getApiClient(),
                        [$object['object']->id]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PATCH);

                    $command->execute();
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "[MAILCHIMP]: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\StoreSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    /**
     * @return string
     */
    // public function hookDisplayAdminOrderContentOrder($params)
    // {
    //     if ($this->isApiKeySet() && $this->isStoreSynced()) {
    //         try {
    //             /**
    //              * @var $order Order
    //              */
    //             $order = $params['order'];
    //          $shop = new Shop($order->id_shop);
    //          $idShop = static::shopIdTransformer($shop);
    //          $response = $this->getApiClient()->get("ecommerce/stores/{$idShop}/orders/{$order->id}");
    //             if ($this->getApiClient()->success()) {
    //                 $this->context->smarty->assign([
    //                     'order' => $response,
    //                 ]);
    //                 return $this->context->smarty->fetch(
    //                     $this->getLocalPath() . 'views/templates/admin/mc-order-detail-tab-content.tpl'
    //                 );
    //             }

    //             return $this->context->smarty->fetch(
    //                 $this->getLocalPath() . 'views/templates/admin/mc-order-detail-tab-content-empty.tpl'
    //             );

    //         } catch (Exception $exception) {
    //             $this->context->controller->errors[] =
    //                 $this->trans("Unable to fetch MailChimp order: %error%", ['%error%' => $exception->getMessage()], 'Modules.Mailchimppro.Mailchimppro');
    //         }
    //     }
    //     return '';
    // }

    /**
     * @return string
     *
     * @throws SmartyException
     */
    // public function hookDisplayAdminOrderTabOrder($params)
    // {
    //     return $this->context->smarty->fetch(
    //         $this->getLocalPath() . '/views/templates/admin/mc-order-detail-tab-title.tpl'
    //     );
    // }

    /**
     * @throws SmartyException
     */
    // public function hookDisplayBackOfficeTop()
    // {
    //     if ($this->context->controller->controller_name === 'AdminCarts' &&
    //         $this->isApiKeySet() &&
    //         $this->isStoreSynced()) {
    //         $cart = new Cart(Tools::getValue('id_cart'));
    //      $shop = new Shop($cart->id_shop);
    //      $idShop = static::shopIdTransformer($shop);
    //      $response = $this->getApiClient()->get("ecommerce/stores/{$idShop}/carts/{$cart->id}");

    //         if ($this->getApiClient()->success()) {
    //             $this->context->smarty->assign([
    //                 'cart' => $response,
    //             ]);
    //             $this->context->controller->content .=
    //                 $this->context->smarty->fetch(
    //                     $this->getLocalPath() . 'views/templates/admin/mc-cart-detail.tpl'
    //                 );
    //         }
    //     }
    // }

    public function hookDisplayFooterBefore($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                try {
                    PrestaChamps\MailchimpPro\Hooks\Display\FooterBefore::run(
                        $params,
                        $this->getApiClient(),
                        $this->context
                    )->newsletterBlockRegistration();
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionNewsletterRegistrationAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
                try {
                    $newsletterSubscriber['email'] = $params['email'];
                    if (isset($this->context->language->id) && $this->context->language->id) {
                        $newsletterSubscriber['id_lang'] = $this->context->language->id;
                    }
                    if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                        $command = new PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand(
                            $this->context,
                            $this->getApiClient(),
                            [$newsletterSubscriber]
                        );
                        $command->setSyncMode($command::SYNC_MODE_REGULAR);
                        $command->setMethod($command::SYNC_METHOD_PUT);
                        $command->execute();
                    } else {
                        $job = new PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob();
                        $job->newsletterSubscriber = $newsletterSubscriber;
                        $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand::SYNC_MODE_REGULAR);
                        $job->setMethod(PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand::SYNC_METHOD_PUT);
                        $queue = new PrestaChamps\Queue\Queue();
                        $queue->push($job, 'hook-newsletter-registration-after', $this->context->shop->id);
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    protected function isApiKeySet()
    {
        return !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY));
    }

    protected function isStoreSynced()
    {
        return !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) && !empty(Configuration::get(MailchimpProConfig::SELECTED_PRESET));
    }

    /**
     * Handles guest checkout to account conversion — syncs the cart.
     * Note: Customer sync itself is handled by hookActionObjectCustomerAddAfter,
     * so AccountAdd only needs to handle the cart sync part.
     */
    public function hookActionCustomerAccountAdd($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CARTS)) {
                try {
                    $customer = $this->getCustomerFromHookParam($params);
                    if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                        && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                        $cartId = isset($this->context->cart->id) ? $this->context->cart->id : false;
                        if ($cartId && $this->context->cart->nbProducts() && $customer->id) {
                            if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                $command = new PrestaChamps\MailchimpPro\Commands\CartSyncCommand(
                                    $this->context,
                                    $this->getApiClient(),
                                    [$cartId]
                                );
                                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                $command->setMethod($command::SYNC_METHOD_DELETE);
                                $command->execute();
                                $command->setMethod($command::SYNC_METHOD_POST);
                                $command->execute();
                            } else {
                                $job = new PrestaChamps\Queue\Jobs\CartSyncJob();
                                $job->cartId = $cartId;
                                $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\CartSyncCommand::SYNC_MODE_REGULAR);
                                if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                                    $job->setCampaignId($_COOKIE['mc_cid']);
                                }
                                $queue = new PrestaChamps\Queue\Queue();
                                $queue->push($job, 'hook-account-add', $this->context->shop->id);
                            }
                        }
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS)) {
                try {
                    $customer = $this->getCustomerFromHookParam($params);
                    // check filter customers to sync
                    if (in_array($customer->active, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])
                        && in_array($customer->newsletter, $this->configuration[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                        PrestaChamps\MailchimpPro\Hooks\Action\Customer\AccountUpdate::run(
                            $this->context,
                            $this->getApiClient(),
                            $customer
                        );
                    }
                } catch (Exception $exception) {
                    PrestaShopLogger::addLog("[MAILCHIMP] :{$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * @return CustomerCore
     *
     * @throws Exception
     */
    private function getCustomerFromHookParam($hookParams)
    {
        if (isset($hookParams['customer']) && $hookParams['customer'] instanceof CustomerCore) {
            return $hookParams['customer'];
        }

        if (isset($hookParams['newCustomer']) && $hookParams['newCustomer'] instanceof CustomerCore) {
            return $hookParams['newCustomer'];
        }

        throw new Exception("Can't get Customer from hook");
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                $productId = isset($params['id_product']) ? $params['id_product'] : Tools::getValue('id_product');
                if (Validate::isLoadedObject(new Product($productId))) {
                    $this->context->smarty->assign([
                        'productId' => $productId,
                        'regenerateLink' => $this->context->link->getAdminLink('AdminMailchimpProConfiguration'),
                    ]);

                    return $this->display(__FILE__, 'views/templates/hook/admin/_products-extra.tpl');
                }
            }
        }

        return '';
    }

    public function hookActionObjectSpecificPriceAddAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore')) {
                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;
                        $from = $params['object']->from;
                        $to = $params['object']->to;

                        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));
                        $from_date = new DateTime($from, new DateTimeZone(@date_default_timezone_get()));
                        $to_date = new DateTime($to, new DateTimeZone(@date_default_timezone_get()));

                        $needToRun = 0;
                        $unlimited = 0;

                        if ($from_date > $current_date) {
                            $needToRun = 2;
                        } elseif ($to_date > $current_date) {
                            $needToRun = 1;
                        } elseif ('0000-00-00 00:00:00' == $to) {
                            $unlimited = 1;
                        }

                        if ($needToRun > 0 && 0 == $unlimited) {
                            // Insert the data using the insert method, with INSERT_IGNORE option
                            Db::getInstance()->insert(
                                'mailchimppro_specific_price',
                                [
                                    'id_specific_price' => (int) $specific_price_id,
                                    'id_product' => (int) $product_id,
                                    'start_date' => pSQL($from),
                                    'end_date' => pSQL($to),
                                    'needToRun' => pSQL((string) $needToRun),
                                    'id_shop' => (int) $shopId,
                                ],
                                false,
                                true,
                                Db::INSERT_IGNORE
                            );
                        }

                        if (1 == $needToRun || (0 == $needToRun && 1 == $unlimited)) {
                            // Filter gate — see productPassesSyncFilter() for rationale.
                            // Skip enqueue for products the merchant's filter excludes,
                            // so PATCH doesn't 404 and starve customer/order queues.
                            $product = new Product((int) $product_id, false, null, (int) $shopId);
                            if (Validate::isLoadedObject($product) && $this->productPassesSyncFilter($product)) {
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    // sync live add sp price
                                    $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$product_id]
                                    );
                                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                    $command->setMethod($command::SYNC_METHOD_PATCH);
                                    $result = $command->execute();

                                    if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                        $command->setMethod($command::SYNC_METHOD_POST);
                                        $result = $command->execute();
                                    }
                                } else {
                                    // add job add sp price
                                    $job = new PrestaChamps\Queue\Jobs\ProductSyncJob();
                                    $job->productId = $product_id;
                                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                    $queue = new PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-specific-price-add-after', $shopId);
                                }
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public function hookActionObjectSpecificPriceUpdateAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore')) {
                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;
                        $from = $params['object']->from;
                        $to = $params['object']->to;

                        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));
                        $from_date = new DateTime($from, new DateTimeZone(@date_default_timezone_get()));
                        $to_date = new DateTime($to, new DateTimeZone(@date_default_timezone_get()));

                        $needToRun = 0;
                        $unlimited = 0;
                        $needToRunDB = 0;

                        if ($from_date > $current_date) {
                            $needToRun = 2;
                        } elseif ($to_date > $current_date) {
                            $needToRun = 1;
                        } elseif ('0000-00-00 00:00:00' == $to) {
                            $unlimited = 1;
                        }

                        $query = new DbQuery();
                        $query->select('*');
                        $query->from('mailchimppro_specific_price');
                        $query->where('id_specific_price = ' . (int) $specific_price_id);

                        // Execute the query and fetch the result
                        $db_specific_price = Db::getInstance()->getRow($query);

                        if ($db_specific_price) {
                            $needToRunDB = $db_specific_price['needToRun'];
                            $fromDB = $db_specific_price['start_date'];
                            $toDB = $db_specific_price['end_date'];

                            // Define the where condition
                            $where = 'id_specific_price = ' . (int) $specific_price_id;

                            // // Execute the delete query
                            $delete = Db::getInstance()->delete('mailchimppro_specific_price', $where);
                        }

                        if ($needToRun > 0 && 0 == $unlimited) {
                            // Insert the data using the insert method, with INSERT_IGNORE option
                            Db::getInstance()->insert(
                                'mailchimppro_specific_price',
                                [
                                    'id_specific_price' => (int) $specific_price_id,
                                    'id_product' => (int) $product_id,
                                    'start_date' => pSQL($from),
                                    'end_date' => pSQL($to),
                                    'needToRun' => pSQL((string) $needToRun),
                                    'id_shop' => (int) $shopId,
                                ],
                                false,
                                true,
                                Db::INSERT_IGNORE
                            );
                        }

                        if (1 == $needToRun || (0 == $needToRun && 1 == $unlimited) || $db_specific_price) {
                            $product = new Product((int) $product_id, false, null, (int) $shopId);
                            if (Validate::isLoadedObject($product) && $this->productPassesSyncFilter($product)) {
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    // sync live edit sp price
                                    $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$product_id]
                                    );
                                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                    $command->setMethod($command::SYNC_METHOD_PATCH);
                                    $result = $command->execute();

                                    if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                        $command->setMethod($command::SYNC_METHOD_POST);
                                        $result = $command->execute();
                                    }
                                } else {
                                    // add job edit sp price
                                    $job = new PrestaChamps\Queue\Jobs\ProductSyncJob();
                                    $job->productId = $product_id;
                                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                    $queue = new PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-specific-price-update-after', $shopId);
                                }
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public function hookActionObjectSpecificPriceDeleteAfter($params)
    {
        if ($this->isApiKeySet() && $this->isStoreSynced()) {
            if (Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
                try {
                    if (is_subclass_of($params['object'], 'SpecificPriceCore')) {
                        $product_id = $params['object']->id_product;
                        $specific_price_id = $params['object']->id;
                        $shopId = $params['object']->id_shop;

                        $query = new DbQuery();
                        $query->select('*');
                        $query->from('mailchimppro_specific_price');
                        $query->where('id_specific_price = ' . (int) $specific_price_id);

                        // Execute the query and fetch the result
                        $db_specific_price = Db::getInstance()->getRow($query);

                        $needToRunDB = 0;

                        if ($db_specific_price) {
                            $needToRunDB = $db_specific_price['needToRun'];

                            // Define the where condition
                            $where = 'id_specific_price = ' . (int) $specific_price_id;

                            // // Execute the delete query
                            $delete = Db::getInstance()->delete('mailchimppro_specific_price', $where);
                        }

                        if (1 == $needToRunDB || !$db_specific_price) {
                            $product = new Product((int) $product_id, false, null, (int) $shopId);
                            if (Validate::isLoadedObject($product) && $this->productPassesSyncFilter($product)) {
                                if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                                    // sync live delete sp price
                                    $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                                        $this->context,
                                        $this->getApiClient(),
                                        [$product_id]
                                    );
                                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                                    $command->setMethod($command::SYNC_METHOD_PATCH);
                                    $result = $command->execute();

                                    if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                                        $command->setMethod($command::SYNC_METHOD_POST);
                                        $result = $command->execute();
                                    }
                                } else {
                                    // add job edit sp price
                                    $job = new PrestaChamps\Queue\Jobs\ProductSyncJob();
                                    $job->productId = $product_id;
                                    $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                                    $queue = new PrestaChamps\Queue\Queue();
                                    $queue->push($job, 'hook-specific-price-delete-after', $shopId);
                                }
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->context->controller->errors[] = $exception->getMessage();
                    PrestaShopLogger::addLog(
                        "MAILCHIMP_ERROR: {$exception->getMessage()}",
                        1,
                        $exception->getCode(),
                        PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::class,
                        null,
                        true
                    );
                }
            }
        }
    }

    public static function shopIdTransformer(Shop $shop)
    {
        if (1 == Configuration::get(MailchimpProConfig::MULTI_INSTANCE_MODE)) {
            return $shop->domain . '_' . $shop->id;
        }

        return $shop->id;
    }

    protected function getShopId()
    {
        return static::shopIdTransformer($this->context->shop);
    }

    public static function getCustomerLanguageIsoCode($isoCode)
    {
        $iso_return = '';

        switch ((string) $isoCode) {
            case 'fr':
                $iso_return = 'fr';
                break;
            case 'qc':
                $iso_return = 'fr_CA';
                break;
            case 'pt':
                $iso_return = 'pt_PT';
                break;
            case 'br':
                $iso_return = 'pt';
                break;
            case 'es':
                $iso_return = 'es_ES';
                break;
            case 'mx':
                $iso_return = 'es';
                break;
            default:
                $iso_return = $isoCode;
        }

        return $iso_return;
    }
}
