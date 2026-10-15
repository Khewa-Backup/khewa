<?php
/**
 * This file provides the Smartarget Message Bar module for PrestaShop.
 *
 * @author Smartarget
 * @copyright Smartarget 2023
 * @license MIT
 */
use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class SmartargetMessageBar extends Module
{
    /**
     * The app this module installs, as Smartarget knows it. Part of every hash and of the
     * integration URL, so it has to match the key used on the Smartarget side exactly.
     */
    public const SMARTARGET_APP = 'message_bar';

    /**
     * Where the admin iframe is served from.
     */
    public const SMARTARGET_INTEGRATION_HOST = 'https://integration-v2.smartarget.online';

    /**
     * Upper bound on the JSON we hand to the integration URL.
     *
     * The merchant's shop can sit behind any web server, and a lot of them cap the request line
     * well below what a full PrestaShop Accounts context serialises to - the merchant then gets
     * the server's 414 page where the module should be. Everything past this limit is dropped
     * down to the two fields the Smartarget side cannot work without.
     */
    public const SMARTARGET_PLATFORM_DATA_MAX = 1024;

    /**
     * @var ServiceContainer
     */
    private $container;

    public function __construct()
    {
        $this->name = 'smartargetmessagebar';
        $this->tab = 'front_office_features';
        $this->version = '1.0.6';
        $this->module_key = '79a3b973934fbb585e1fe7887aadc307';
        $this->author = 'Smartarget';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Smartarget Message Bar');
        $this->description = $this->l('Display a message bar on your website.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if ($this->container === null) {
            $this->container = new ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->registerHook('displayHeader')) {
            return false;
        }

        $tab = new Tab();
        $tab->active = true;
        $tab->class_name = 'AdminSmartargetMessageBar';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Smartarget Message Bar';
        }

        // Set parent as the "Modules" section.
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentModulesSf');
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

    public function uninstall()
    {
        try {
            $tabId = (int) Tab::getIdFromClassName('AdminSmartargetMessageBar');
            if ($tabId) {
                $tab = new Tab($tabId);
                $tab->delete();
            }
        } catch (Exception $e) {
            // a tab that cannot be deleted must not keep the module installed
        }

        return parent::uninstall();
    }

    /**
     * Loads the storefront widget.
     *
     * @return string
     */
    public function hookDisplayHeader()
    {
        $this->context->smarty->assign('storefrontHash', $this->getStorefrontHash());

        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    /**
     * PrestaShop 8 and 9 dispatch the front controller head under the short hook name. Kept as a
     * separate entry point so a shop that registered either name still renders the widget.
     *
     * @return string
     */
    public function hookHeader()
    {
        return $this->hookDisplayHeader();
    }

    /**
     * The module configuration screen: the PrestaShop Accounts / Billing widgets plus the
     * Smartarget settings iframe.
     *
     * @return string
     */
    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);

        // Load dependencies manager
        $mboInstaller = new DependencyBuilder($this);

        if (!$mboInstaller->areDependenciesMet()) {
            $this->context->smarty->assign('dependencies', $mboInstaller->handleDependencies());

            return $this->display(__FILE__, 'views/templates/admin/dependency_builder.tpl');
        }

        $accountsFacade = null;
        $accountsService = null;

        try {
            $accountsFacade = $this->getService('smartargetmessagebar.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            $accountsInstaller = $this->getService('smartargetmessagebar.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->getService('smartargetmessagebar.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        $accountsContext = [];

        try {
            $accountsContext = (array) $accountsFacade->getPsAccountsPresenter()->present($this->name);

            Media::addJsDef(['contextPsAccounts' => $accountsContext]);

            // Retrieve Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            return '';
        }

        $this->assignEventbusContext();

        // PrestaShop Billing
        $billingFacade = $this->getService('smartargetmessagebar.ps_billings_facade');
        $billingContext = (array) $billingFacade->present([
            'logo' => $this->getLocalPath() . 'logo.png',
            'tosLink' => 'https://smartarget.online/page_terms.html',
            'privacyLink' => 'https://smartarget.online/page_privacy.html',
            'emailSupport' => 'support@smartarget.online',
        ]);

        Media::addJsDef($billingContext);

        $this->context->smarty->assign('urlBilling', 'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js');
        $this->context->smarty->assign(
            'smartargetIframeUrl',
            $this->getSmartargetIframeUrl($accountsContext, $billingContext)
        );

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Retrieve the service
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    /**
     * Exposes the CloudSync consent widget when ps_eventbus is new enough to present it.
     *
     * @return void
     */
    private function assignEventbusContext()
    {
        if (!Module::isInstalled('ps_eventbus')) {
            return;
        }

        $eventbusModule = Module::getInstanceByName('ps_eventbus');

        if (!$eventbusModule || version_compare($eventbusModule->version, '1.9.0', '<')) {
            return;
        }

        // getService() is declared by ps_eventbus itself, not by the Module base class
        if (!method_exists($eventbusModule, 'getService')) {
            return;
        }

        try {
            $eventbusPresenterService = call_user_func(
                [$eventbusModule, 'getService'],
                'PrestaShop\Module\PsEventbus\Service\PresenterService'
            );

            Media::addJsDef([
                'contextPsEventbus' => $eventbusPresenterService->expose($this, ['info', 'modules', 'themes']),
            ]);

            $this->context->smarty->assign(
                'urlCloudsync',
                'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js'
            );
        } catch (Exception $e) {
            // CloudSync is optional - a shop without it still configures the widget normally
        }
    }

    /**
     * The shop domain, stripped of scheme, www and any path, as every Smartarget hash is built
     * from it. Kept in one place because the admin hash, the storefront hash and the Smartarget
     * side all have to agree on the exact same string.
     *
     * @return string
     */
    private function getShopDomain()
    {
        return preg_replace("/www\.|https?:\/\/|\/$|\/?\?.+|\/.+|^\./", '', Tools::getShopDomain());
    }

    /**
     * Identifies the shop to Smartarget on the configuration screen.
     *
     * @return string
     */
    private function getAdminHash()
    {
        return sha1('prestashop_' . self::SMARTARGET_APP . '_' . $this->getShopDomain());
    }

    /**
     * Identifies the shop to the storefront loader. Deliberately a second hash over the first:
     * the public storefront must not carry the value that authenticates the admin screen.
     *
     * @return string
     */
    private function getStorefrontHash()
    {
        return sha1($this->getAdminHash() . '_script');
    }

    /**
     * The settings iframe URL, with the shop identity Smartarget needs attached to it.
     *
     * @param array $accountsContext
     * @param array $billingContext
     *
     * @return string
     */
    private function getSmartargetIframeUrl(array $accountsContext, array $billingContext)
    {
        return self::SMARTARGET_INTEGRATION_HOST
            . '/api/prestashop/' . self::SMARTARGET_APP . '/' . $this->getAdminHash() . '/iframe?'
            . http_build_query([
                'url' => Tools::getShopDomain(),
                'platformData' => $this->getPlatformData($accountsContext, $billingContext),
            ]);
    }

    /**
     * The shop identity, as JSON, reduced to what Smartarget actually reads.
     *
     * The PrestaShop Accounts context this is built from runs to several kilobytes - on a
     * multistore it carries a full record, RSA public key included, for every shop, plus install
     * links carrying CSRF tokens. None of that is read on the Smartarget side, and all of it used
     * to travel in the query string of the URL below, which is what pushed that URL past what some
     * shops' web servers accept. Only these four fields are sent now.
     *
     * @param array $accountsContext
     * @param array $billingContext
     *
     * @return string
     */
    private function getPlatformData(array $accountsContext, array $billingContext)
    {
        $currentShop = isset($accountsContext['currentShop']) && is_array($accountsContext['currentShop'])
            ? $accountsContext['currentShop']
            : [];

        $billing = isset($billingContext['psBillingContext']['context'])
            ? $billingContext['psBillingContext']['context']
            : [];

        $billingShop = isset($billing['shop']) && is_array($billing['shop']) ? $billing['shop'] : [];
        $billingOrganization = isset($billing['organization']) && is_array($billing['organization'])
            ? $billing['organization']
            : [];

        // shopId is what the PrestaShop Billing webhooks call customer_id - it is the only field
        // that lets an incoming subscription event find this shop's integration, so it is never
        // dropped, whatever the length budget below decides.
        $required = [
            'shopId' => $this->firstNonEmpty([
                isset($billingShop['uuid']) ? $billingShop['uuid'] : null,
                isset($currentShop['uuid']) ? $currentShop['uuid'] : null,
            ]),
            'email' => $this->firstNonEmpty([
                isset($accountsContext['user']['email']) ? $accountsContext['user']['email'] : null,
                isset($accountsContext['superAdminEmail']) ? $accountsContext['superAdminEmail'] : null,
                isset($billingOrganization['email']) ? $billingOrganization['email'] : null,
            ]),
        ];

        $optional = [
            'shopName' => $this->firstNonEmpty([
                isset($currentShop['name']) ? $currentShop['name'] : null,
            ]),
            'domain' => $this->firstNonEmpty([
                isset($currentShop['domain']) ? $currentShop['domain'] : null,
                isset($billingShop['domain']) ? $billingShop['domain'] : null,
            ]),
        ];

        $present = function ($value) {
            return is_scalar($value) && (string) $value !== '';
        };

        $full = json_encode(array_filter(array_merge($required, $optional), $present), JSON_FORCE_OBJECT);

        if ($full !== false && Tools::strlen($full) <= self::SMARTARGET_PLATFORM_DATA_MAX) {
            return $full;
        }

        $minimal = json_encode(array_filter($required, $present), JSON_FORCE_OBJECT);

        return $minimal === false ? '{}' : $minimal;
    }

    /**
     * @param array $candidates
     *
     * @return string|null
     */
    private function firstNonEmpty(array $candidates)
    {
        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && (string) $candidate !== '') {
                return (string) $candidate;
            }
        }

        return null;
    }
}
