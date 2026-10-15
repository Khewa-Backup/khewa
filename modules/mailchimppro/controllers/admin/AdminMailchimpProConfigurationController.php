<?php
/**
 * PrestaChamps
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

use PrestaChamps\MailchimpPro\Commands\PromoMergeTagSyncCommand;
use PrestaChamps\MailchimpPro\Commands\SiteVerifyCommand;
use PrestaChamps\MailchimpPro\Commands\StoreSyncCommand;
use PrestaChamps\MailchimpPro\Helpers\PsContext;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PixelLog;
use PrestaChamps\MailchimpPro\Models\PromoCode;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;
use PrestaChamps\Queue\Jobs\CartRuleSyncJob;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\MergeTagPromoCodeSyncJob;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\Queue\Jobs\OrderSyncJob;
use PrestaChamps\Queue\Jobs\ProductSyncJob;

class AdminMailchimpProConfigurationController extends ModuleAdminController
{
    use ShopIdTrait;

    /** @var Mailchimppro */
    public $module;

    public static $Account_Info;

    public $bootstrap = true;

    private function getApiLogs()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('mailchimppro_api_log');
        $query->orderBy('id DESC');

        // Execute the query and fetch the results
        return Db::getInstance()->executeS($query);
    }

    public function initContent()
    {
        $logs = $this->getApiLogs();
        $promo = Campaign::getPromoCodes();
        $totalCodes = Campaign::getExpectedCodesCount();

        $multistore_on_store = false;

        if ((Shop::isFeatureActive() && null != Shop::getContextShopID(true)) || !Shop::isFeatureActive()) {
            $multistore_on_store = true;
        } elseif (null == Shop::getContextShopID(true)) {
            $multistore_on_store = false;
        }

        $multistore_php_command = false;
        if (true == Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC_FOR_MULTI_STORE)) {
            $multistore_php_command = true;
        }

        $jobs_deleted_message_show = true;
        $jobs_deleted_message_employee = Configuration::get(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE);
        $jobs_deleted_message_date = Configuration::get(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE);

        $jobs_deleted_message_count = Configuration::hasKey(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_COUNT);

        if ((null != $jobs_deleted_message_employee && null != $jobs_deleted_message_date) || empty($this->getAccountInfo()) || !$jobs_deleted_message_count) {
            $jobs_deleted_message_show = false;
        }

        $syncAutoConfirm = false;
        $syncAutoConfirmListName = '';

        if (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC)
            || (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC)) {
            // automatic list_id selection and synchronization
            if (!empty($this->getAccountInfo()) && (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_STORE_SYNCED) || null == Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED))) {
                try {
                    $lists = $this->module->getApiClient()->get('lists', ['fields' => 'lists.name,lists.id', 'count' => 1000])['lists'];

                    if (1 == count($lists)) {
                        $command = new StoreSyncCommand(
                            $this->context,
                            $this->module->getApiClient(),
                            [$this->context->shop->id]
                        );

                        $storeExists = $command->getStoreExists($this->getShopId(), true);

                        $syncAutoConfirm = true;
                        $syncAutoConfirmListName = $lists[0]['name'];

                        if (isset($storeExists['domain']) && $storeExists['domain'] !== $this->context->shop->getBaseURL(true)) {
                            // dump("same shop ID other domain sync - NO multi instance");
                            MailchimpProConfig::saveValue(MailchimpProConfig::MULTI_INSTANCE_MODE, true);

                            $command = new StoreSyncCommand(
                                $this->context,
                                $this->module->getApiClient(),
                                [$this->context->shop->id]
                            );

                            $storeExists = $command->getStoreExists($this->getShopId(), true);

                            if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                // dump("same domain sync");
                                $syncAutoConfirm = false;
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                                $this->cacheConnectedSiteScript($storeExists);
                            } else {
                                // dump("same shop ID other domain sync - multi instance");
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $lists[0]['id']);
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, $lists[0]['name']);

                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);

                                $resp = $this->autoSyncStore();
                            }
                        } elseif (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                            // dump("same domain sync");
                            $syncAutoConfirm = false;
                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                            $this->cacheConnectedSiteScript($storeExists);
                        } else {
                            // dump("no other domain, can be synched");

                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $lists[0]['id']);
                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, $lists[0]['name']);

                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);

                            $resp = $this->autoSyncStore();
                        }
                    } else {
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                    }
                } catch (Exception $exception) {
                    $this->errors[] = $exception->getMessage();
                }
            }
            // END automatic list_id selection and auto synchronization
        }

        if (Configuration::hasKey(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC) && (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC)) {
            $syncAutoConfirm = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC);
            $syncAutoConfirmListName = Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME);
        }

        // Catch-up fetch — legacy installs (or any install where SCRIPT_CACHED
        // was cleared) end up STORE_SYNCED=true but with no cached script. The
        // wizard auto-init paths above only fire when STORE_SYNCED is unset,
        // so they don't help here. One-time GET ecommerce/stores/{id} on BO
        // render back-fills the cache; once SCRIPT_CACHED is non-empty this
        // block becomes a no-op.
        if (!empty(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY))
            && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID))
            && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED))
            && !empty(Configuration::get(MailchimpProConfig::SELECTED_PRESET))
            && empty(Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED))
        ) {
            try {
                $command = new StoreSyncCommand(
                    $this->context,
                    $this->module->getApiClient(),
                    [$this->context->shop->id]
                );
                $storeExists = $command->getStoreExists($this->getShopId(), true);
                if ($storeExists) {
                    $this->cacheConnectedSiteScript($storeExists);
                    // scriptVerify short-circuits when connected_site.verified is
                    // already true server-side, so this is a no-op for legacy
                    // installs that previously verified — and a real verify POST
                    // for the (rare) legacy install that synced but never verified.
                    $this->scriptVerify($storeExists);
                }
            } catch (Exception $exception) {
                PrestaShopLogger::addLog(
                    '[MAILCHIMP] catch-up script fetch failed: ' . $exception->getMessage(),
                    3,
                    null,
                    'mailchimppro'
                );
            }
        }

        $this->initMainConfigValues($multistore_on_store);

        $this->initDefaultOrderStatuses();

        if (!empty(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY))) {
            $this->module->assignStatisticsVariables();
        }

        $currentDate = new DateTime();
        $currentDate->modify('+1 month');
        $defaultDate = $currentDate->format('Y-m-d H:i:s');

        $this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/configuration.css');
        Media::addJsDef([
            'queueUrl' => $this->context->link->getAdminLink('AdminMailchimpProQueue'),
            'middlewareUrl' => Mailchimppro::MC_MIDDLEWARE,
            'middlewareUrlUpgraded' => Mailchimppro::MC_MIDDLEWARE_NEW_TO_CONNECT,
            'configurationUrl' => $this->context->link->getAdminLink($this->controller_name),
            'statsUrl' => $this->context->link->getAdminLink('AdminMailchimpProStats', true, [], ['ajax' => true]),
            'mailchimp' => $this->getConfigValues(),
            'cronjobSecureToken' => Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
        ]);
        $this->context->smarty->assign([
            'multistore_on_store' => $multistore_on_store,
            'multistore_php_command' => $multistore_php_command,
            'cronjob_multiStore' => Shop::isFeatureActive(),
            'jobs_deleted_message_show' => $jobs_deleted_message_show,
            'validApiKey' => !empty($this->getAccountInfo()),
            'mainJsPath' => Media::getJSPath(
                $this->module->getLocalPath() . 'views/js/configuration/main.js'
            ) . '?v=' . $this->module->version,
            'JsLybraryPath' => Media::getJSPath(
                $this->module->getLocalPath() . 'views/js/'
            ),
            'configurationUrl' => $this->context->link->getAdminLink($this->controller_name),
            'listId' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID),
            'imageSizes' => $this->getImageSizes(),
            'cronjobLog' => $this->getCronjobLogContent(),
            'cronjobUrlLink' => $this->context->link->getModuleLink($this->module->name, 'cronjob') . '?secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'cronjobUrlLinkWget' => '* * * * * wget -O - ' . $this->context->link->getModuleLink($this->module->name, 'cronjob') . '?secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'cronjobUrlPath' => '* * * * * ' . (defined('PHP_BINDIR') ? PHP_BINDIR . '/' : '') . 'php ' . _PS_MODULE_DIR_ . $this->module->name . '/cronjob.php secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'lastCronjob' => Configuration::get(MailchimpProConfig::LAST_CRONJOB),
            'lastCronjobExecutionTime' => Configuration::get(MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME),
            'totalJobs' => $this->getNumberOfTotalJobs(),
            'autoSyncPopup' => $syncAutoConfirm,
            'autoSyncPopupListName' => $syncAutoConfirmListName,
            'logs' => $logs,
            'promos' => $promo,
            'totalCodes' => $totalCodes,
            'currency' => $this->context->currency,
            'defaultDate' => $defaultDate,
        ]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/configuration/main.tpl'
        );
        parent::initContent();
    }

    public function getDefinedPresets()
    {
        return [
            'advanced' => [
                'title' => $this->trans('Advanced', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset automatically synchronizes data for both abandoned cart recovery and newsletter signups. It includes extensive data synchronization to ensure marketing and cart recovery campaigns have full context.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active customers (last 6 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (last 6 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => '-6 months',  // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => '-6 months',  // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Suitable for businesses wanting to ensure comprehensive data coverage, focusing on both email marketing and recovering abandoned carts.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'basic' => [
                'title' => $this->trans('Basic', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to only manage basic email subscribers. It does not synchronize any cart, order, or cart rule data from your system.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups only (including subscribed customers)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => false,
                    'syncOrders' => false,
                    'syncCarts' => false,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for businesses that want to use Mailchimp solely for managing subscribers and sending newsletters, without integrating complex eCommerce data.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'free' => [
                'title' => $this->trans('Free (testing purposes)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to synchronize data to test the free Mailchimp plan for email sending. It syncs relevant information like customers, products, and orders. Allows the use of a single list.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Customers (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Orders (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('This is for new marketers with big ideas who want to get online, build an audience, and start growing on day one.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'list-member-limit' => 500,
            ],
            'abandoned-cart' => [
                'title' => $this->trans('Abandoned Cart', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to synchronize data for abandoned cart emails. It syncs relevant information like customers, products, and orders.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Customers (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Orders (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => false,
                ],
                'use-case' => $this->trans('Suitable for stores focused on recovering abandoned carts and optimizing revenue through reminder emails within a 90-day window.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'custom' => [
                'title' => $this->trans('Custom', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => sprintf(
                    $this->trans('This preset offers all features available in the %s"Advanced Package"%s with some additional options, but it is manually operated from the back office and is triggered by specific events or hooks.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    '<b>',
                    '</b>'
                ),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active customers (last 12 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (last 12 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => '-1 year', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => '-1 year', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for users who prefer manual control over data syncs, triggering updates via specific hooks or direct operations.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'full' => [
                'title' => $this->trans('Full', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset synchronizes all available data. It includes everything from customer interactions to orders, products, carts, and newsletters without any time restriction.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All customers (no time restriction)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (no time restriction)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All carts', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => true,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for businesses seeking full synchronization with Mailchimp, ensuring complete data availability for marketing and sales campaigns with no omissions.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
        ];
    }

    protected function initMainConfigValues($multistore_on_store)
    {
        if (!Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN)) {
            MailchimpProConfig::saveValue(MailchimpProConfig::CRONJOB_SECURE_TOKEN, bin2hex(openssl_random_pseudo_bytes(32)));
        }

        // Self-heal for installs connected before the COMPANY field existed:
        // one POST, once — ensure() caches a definitive status, so this stops
        // firing as soon as Mailchimp gives a real answer.
        if (PrestaChamps\MailchimpPro\CompanyMergeField::STATUS_UNKNOWN === PrestaChamps\MailchimpPro\CompanyMergeField::getStatus()
            && Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)
            && Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)
            && Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
            $this->ensureCompanyMergeField();
        }

        if ($multistore_on_store) {
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)) {
                $this->informations[] = $this->trans('Please log in to Mailchimp...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            } elseif (!Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID) || !Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
                try {
                    $command = new StoreSyncCommand(
                        $this->context,
                        $this->module->getApiClient(),
                        [$this->context->shop->id]
                    );
                    if ($storeExists = $command->getStoreExists($this->getShopId(), true)) {
                        if (isset($storeExists['list_id']) && $storeExists['list_id']) {
                            // MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                            if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                                $this->cacheConnectedSiteScript($storeExists);
                                Media::addJsDef([
                                    'storeAlreadySynced' => true,
                                ]);
                            } elseif (isset($storeExists['domain']) && $storeExists['domain'] !== $this->context->shop->getBaseURL(true)) {
                                MailchimpProConfig::saveValue(MailchimpProConfig::MULTI_INSTANCE_MODE, true);
                                $storeExists = $command->getStoreExists($this->getShopId(), true);
                                if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                    MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                                    MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                                    $this->cacheConnectedSiteScript($storeExists);
                                    Media::addJsDef([
                                        'storeAlreadySynced' => true,
                                    ]);
                                }
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->errors[] = $exception->getMessage();
                }
            }
        }
    }

    protected function initDefaultOrderStatuses()
    {
        if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING)
            || !Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED)
            || !Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED)
            || !Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED)
            || !Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID)) {
            $orderStatuses = [];
            $orderStates = OrderState::getOrderStates($this->context->language->id);
            foreach ($orderStates as $orderState) {
                switch (true) {
                    case 'bankwire' == $orderState['template'] || 'cashondelivery' == $orderState['template'] || 'cheque' == $orderState['template']:
                        $orderStatuses['pending'][] = $orderState['id_order_state'];
                        break;
                    case 'refund' == $orderState['template']:
                        $orderStatuses['refunded'][] = $orderState['id_order_state'];
                        break;
                    case 'order_canceled' == $orderState['template']:
                        $orderStatuses['cancelled'][] = $orderState['id_order_state'];
                        break;
                    case 'shipped' == $orderState['template']:
                        $orderStatuses['shipped'][] = $orderState['id_order_state'];
                        break;
                    case 'payment' == $orderState['template']:
                        $orderStatuses['paid'][] = $orderState['id_order_state'];
                        break;
                }
            }

            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING)) {
                if (isset($orderStatuses['pending'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_PENDING, json_encode($orderStatuses['pending']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED)) {
                if (isset($orderStatuses['refunded'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_REFUNDED, json_encode($orderStatuses['refunded']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED)) {
                if (isset($orderStatuses['cancelled'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_CANCELLED, json_encode($orderStatuses['cancelled']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED)) {
                if (isset($orderStatuses['shipped'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_SHIPPED, json_encode($orderStatuses['shipped']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) || '[]' == Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID)) {
                if (isset($orderStatuses['paid'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_PAID, json_encode($orderStatuses['paid']));
                }
            }
        }
    }

    protected function getOrderStates()
    {
        $orderStates = [];
        foreach (OrderState::getOrderStates($this->context->language->id) as $orderState) {
            $orderStates[] = [
                'label' => $orderState['name'],
                'value' => $orderState['id_order_state'],
                'color' => $orderState['color'],
            ];
        }

        return $orderStates;
    }

    /**
     * Get the available image sizes.
     *
     * @return array
     */
    private function getImageSizes()
    {
        $query = new DbQuery();
        $query->select('name, width, height');
        $query->from('image_type');
        $query->where('products = 1');

        try {
            $results = Db::getInstance()->executeS($query);
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();

            return [];
        }

        // init default product image size
        if (!Configuration::get(MailchimpProConfig::PRODUCT_IMAGE_SIZE) || 'null' == Configuration::get(MailchimpProConfig::PRODUCT_IMAGE_SIZE)) {
            $resultNames = array_column($results, 'name');

            $large_name = '';
            if ((bool) version_compare(_PS_VERSION_, '1.7', '>=')) {
                $large_name = ImageType::getFormattedName('large'); // from PS 1.7
            }

            $key = array_search($large_name, $resultNames);
            if (false !== $key) {
                MailchimpProConfig::saveValue(MailchimpProConfig::PRODUCT_IMAGE_SIZE, $large_name);
            } else {
                if (!empty($resultNames)) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::PRODUCT_IMAGE_SIZE, $resultNames[0]);
                }
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    private function getAccountInfo()
    {
        try {
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)) {
                return [];
            }

            if (null == static::$Account_Info) {
                $info = $this->module->getApiClient()->get('');
                if (!$this->module->getApiClient()->success()) {
                    return [];
                }
                static::$Account_Info = $info;

                return static::$Account_Info;
            }

            return static::$Account_Info;
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * @param mixed $value
     * @param string|null $controller
     * @param string|null $method
     * @param int $statusCode
     */
    public function ajaxDie($value = null, $controller = null, $method = null, $statusCode = 200)
    {
        // Auto-inject connection-health state into every array response so the
        // Vue app's updateHealthStateFromResponse() can refresh the reactive
        // banner without a page reload — regardless of which ajaxProcess*
        // handler emitted the response. Caller-supplied keys win on collision
        // (which shouldn't happen — health payload keys are namespaced).
        if (is_array($value)) {
            $value = array_merge($this->getHealthStatePayload(), $value);
        }

        header('Content-Type: application/json');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        http_response_code($statusCode);
        if ((bool) version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            exit;
        }
        // Legacy fallback for PS 1.7.0.6 → 1.7.4.x (pre-ajaxRender). Controller::ajaxDie
        // was removed in PS 9.0 — dynamic call hides it from PHPStan's static surface.
        call_user_func(['parent', 'ajaxDie'], $value, $controller, $method);
    }

    public function ajaxProcessDisconnect()
    {
        PrestaChamps\MailchimpPro\HealthState::setSourceContext('bo-action-disconnect');

        $accountInfo = $this->getAccountInfo();
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT, $accountInfo['account_id']);

        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_API_KEY, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
        // Account-bound caches that would lie to the merchant during the
        // disconnected window — both internal (BO health-panel + BO
        // dashboard) and external (gdprpro reads PIXEL_AVAILABLE.enabled
        // to decide whether to show the Mailchimp pixel row in its
        // consent modal). All three get correctly repopulated on
        // reconnect: same-account → re-probe / re-verify / re-fetch
        // reports; different-account → resetConfigForNewAccount handles
        // them explicitly. Internal connected_site state that doesn't
        // produce misleading display (SCRIPT_CACHED) is intentionally
        // preserved so same-account reconnect skips the re-fetch round-trip.
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE);
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED);
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED);
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_REPORTS_CACHED_DATE);
        // Audience-scoped: the next connection may point at a different list,
        // where the COMPANY field's presence says nothing about this one.
        Configuration::deleteByName(MailchimpProConfig::COMPANY_FIELD_STATUS);

        // Defensive clear of both connection-health flags. Log out is a clean
        // slate — any stale flag from before the disconnect would show a
        // "connection broken" banner even though the merchant is now correctly
        // logged out (no API calls firing = nothing to detect against).
        PrestaChamps\MailchimpPro\HealthState::clearFlag();
        PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();

        $accountInfo = $this->getAccountInfo();

        $this->ajaxDie([
            'accountInfo' => $accountInfo,
            'validApiKey' => !empty($accountInfo),
        ]);
    }

    public function ajaxProcessConnect()
    {
        PrestaChamps\MailchimpPro\HealthState::setSourceContext('bo-action-connect');

        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_API_KEY, $this->getJsonPayloadValue('token'));
        $accountInfo = $this->getAccountInfo();

        if (Configuration::hasKey(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT) && '' != Configuration::get(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT)) {
            if (Configuration::get(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT) !== $accountInfo['account_id']) {
                // reset the config variables to start again the setup process
                MailchimpProConfig::resetConfigForNewAccount();
            }
        }

        // Fresh account_info fetch just succeeded above — API key is verified
        // as valid, connection is live. Clear both health flags so any stale
        // "connection broken" banner from a previous session's failure state
        // disappears immediately. Recovery-via-reconnect is a legitimate path.
        if (!empty($accountInfo)) {
            PrestaChamps\MailchimpPro\HealthState::clearFlag();
            PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();
        }

        $this->ajaxDie([
            'accountInfo' => $accountInfo,
            'validApiKey' => !empty($accountInfo),
        ]);
    }

    protected function ajaxProcessExecuteCronjob()
    {
        $queue = new PrestaChamps\Queue\Queue();
        $queue->runCronjob('%%', true);
    }

    protected function ajaxProcessClearCronjobLog()
    {
        $hasError = false;
        $errorResponse = '';

        // Define the expected log file path
        $logFilePath = _PS_MODULE_DIR_ . $this->module->name . '/logs/cronjob.log';

        // Resolve the real path to avoid path traversal issues
        $resolvedFilePath = realpath($logFilePath);

        // Ensure the resolved file path is valid and within the expected directory
        if ($resolvedFilePath && 0 === strpos($resolvedFilePath, _PS_MODULE_DIR_ . $this->module->name . '/logs/')) {
            if (file_exists($resolvedFilePath)) {
                if (!unlink($resolvedFilePath)) {
                    $errorResponse = $this->trans('Cannot clear cronjob log. Please check the file permissions.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                    $hasError = true;
                }
            } else {
                $errorResponse = $this->trans('Cronjob log is already cleaned.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                $hasError = true;
            }
        } else {
            $errorResponse = $this->trans('Invalid file path.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            $hasError = true;
        }

        $this->ajaxDie([
            'hasError' => $hasError,
            'errorMessage' => $hasError ? $errorResponse : null,
            'successMessage' => $hasError ? null : $this->trans('Cleared cronjob log successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
        ]);
    }

    public function getNumberOfTotalJobs()
    {
        $queue = new PrestaChamps\Queue\Queue();

        return $queue->getNumberOfTotalJobs();
    }

    public function getJsonPayloadValue($key, $defaultValue = null)
    {
        $body = json_decode(Tools::file_get_contents('php://input'), true);

        return isset($body[$key]) ? $body[$key] : $defaultValue;
    }

    public function getConfigValues()
    {
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $accountInfo = $this->getAccountInfo();
        $configValues = MailchimpProConfig::getConfigurationValues();
        $lists = [];
        try {
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) || empty($accountInfo)) {
                $lists = [];
            } else {
                if (!empty(Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME))) {
                    $lists[] = [
                        'label' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME),
                        'value' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID),
                    ];
                } else {
                    $lists = $this->module->getApiClient()->get('lists', ['fields' => 'lists.name,lists.id', 'count' => 1000])['lists'];

                    if ($lists) {
                        $lists = array_map(function ($list) {
                            return [
                                'label' => $list['name'],
                                'value' => $list['id'],
                            ];
                        }, $lists);
                    } else {
                        $response = json_decode($this->module->getApiClient()->getLastResponse()['body'], true);
                        $this->errors[] = $response['title'] . ': ' . $response['detail'];
                    }
                }
            }
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            $lists = [];
        }

        return [
            'definedPresets' => $this->getDefinedPresets(),
            'selectedPreset' => $configValues[MailchimpProConfig::SELECTED_PRESET], // null, //advanced, basic, abandoned-cart, custom, full
            'multiInstanceMode' => (bool) $configValues[MailchimpProConfig::MULTI_INSTANCE_MODE],
            'cronjobBasedSync' => (bool) $configValues[MailchimpProConfig::CRONJOB_BASED_SYNC],
            'syncProducts' => (bool) $configValues[MailchimpProConfig::SYNC_PRODUCTS],
            'syncCustomers' => (bool) $configValues[MailchimpProConfig::SYNC_CUSTOMERS],
            'syncCartRules' => (bool) $configValues[MailchimpProConfig::SYNC_CART_RULES],
            'syncOrders' => (bool) $configValues[MailchimpProConfig::SYNC_ORDERS],
            'syncCarts' => (bool) $configValues[MailchimpProConfig::SYNC_CARTS],
            'syncCartsPassw' => (bool) $configValues[MailchimpProConfig::SYNC_CARTS_PASSW],
            'syncNewsletterSubscribers' => (bool) $configValues[MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS],
            'statusForPending' => $configValues[MailchimpProConfig::STATUSES_FOR_PENDING],
            'statusForRefunded' => $configValues[MailchimpProConfig::STATUSES_FOR_REFUNDED],
            'statusForCancelled' => $configValues[MailchimpProConfig::STATUSES_FOR_CANCELLED],
            'statusForShipped' => $configValues[MailchimpProConfig::STATUSES_FOR_SHIPPED],
            'statusForPaid' => $configValues[MailchimpProConfig::STATUSES_FOR_PAID],
            'orderStates' => $this->getOrderStates(),
            'productDescriptionField' => $configValues[MailchimpProConfig::PRODUCT_DESCRIPTION_FIELD],
            'existingOrderSyncStrategy' => $configValues[MailchimpProConfig::EXISTING_ORDER_SYNC_STRATEGY],
            'productSyncFilterActive' => $configValues[MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE],
            'productSyncFilterVisibility' => $configValues[MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY],
            'customerSyncFilterEnabled' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED],
            'customerSyncFilterNewsletter' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER],
            'customerSyncFilterPeriod' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_PERIOD],
            'customerSyncTagDefaultGroup' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_DEFAULT_GROUP],
            'customerSyncTagGender' => (bool) $configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_GENDER],
            'customerSyncTagLanguage' => (bool) $configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_LANGUAGE],
            'customerSyncCompanyFromAddress' => (bool) $configValues[MailchimpProConfig::CUSTOMER_SYNC_COMPANY_FROM_ADDRESS],
            // Without B2B mode the customer company field doesn't exist in the
            // shop, so COMPANY can only ever come from an address — the tab
            // says so rather than leaving the merchant with a silent no-op.
            'b2bModeEnabled' => (bool) Configuration::get('PS_B2B_ENABLE'),
            'companyFieldStatus' => PrestaChamps\MailchimpPro\CompanyMergeField::getStatus(),
            'cartRuleSyncFilterStatus' => $configValues[MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS],
            'cartRuleSyncFilterExpiration' => $configValues[MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION],
            'newsletterSubscriberSyncFilterPeriod' => $configValues[MailchimpProConfig::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD],
            'productImageSize' => $configValues[MailchimpProConfig::PRODUCT_IMAGE_SIZE],
            'token' => $configValues[MailchimpProConfig::MAILCHIMP_API_KEY],
            'listId' => $configValues[MailchimpProConfig::MAILCHIMP_LIST_ID],
            'lists' => $lists,
            'storeSynced' => (bool) $configValues[MailchimpProConfig::MAILCHIMP_STORE_SYNCED],
            'validApiKey' => !empty($accountInfo),
            'accountInfo' => $accountInfo,
            'numberOfCartRulesToSync' => $repository->getCartRulesCount(),
            'numberOfCustomersToSync' => $repository->getCustomersCount(),
            'numberOfProductsToSync' => $repository->getProductsCount(),
            'numberOfOrdersToSync' => $repository->getOrdersCount(),
            'numberOfNewsletterSubscribersToSync' => $repository->getNewsletterSubscribersCount(),
            'logQueue' => $configValues[MailchimpProConfig::LOG_QUEUE],
            'queueStep' => $configValues[MailchimpProConfig::QUEUE_STEP],
            'queueAttempt' => $configValues[MailchimpProConfig::QUEUE_ATTEMPT],
            'logCronjob' => $configValues[MailchimpProConfig::LOG_CRONJOB],
            'cronjobLogContent' => $this->getCronjobLogContent(),
            'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
            'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
            'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
            'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
            'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
            'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
            'lastCronjob' => $configValues[MailchimpProConfig::LAST_CRONJOB],
            'lastCronjobExecutionTime' => $configValues[MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME],
            'totalJobs' => $this->getNumberOfTotalJobs(),
            'showDashboardStats' => $configValues[MailchimpProConfig::SHOW_DASHBOARD_STATS],
            'promoOverridesEnabled' => (bool) $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED],
            'promoOverridesAutoInstalled' => (bool) $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED],
            'promoOverridesConflicts' => $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS],
            'manuallyInstalledOverrides' => (bool) $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED] && !(bool) $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED],
            'scriptCachedDate' => $configValues[MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE],
            'cspStatus' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS]) ? $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS] : null,
            'cspCheckedAt' => (int) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_CHECKED_AT),
            // Connection-health state — Phase 3 banner reads these to decide
            // whether to render a "connection broken" alert and which state-
            // specific copy + recovery action to show. Empty flag = healthy
            // (no banner rendered). foCdnBroken is a separate signal for
            // MC.js emission state (see FoCdnHealth); surfaced in the same
            // panel so merchant can see BOTH BO and FO breakage sources at
            // a glance. All values are safe strings / ints — no XSS risk.
            'healthStateFlag' => (string) PrestaChamps\MailchimpPro\HealthState::getFlag(),
            'healthStateSetAt' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SET_AT),
            'healthStateSource' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SOURCE),
            'foCdnBroken' => PrestaChamps\MailchimpPro\FoCdnHealth::isBroken(),
            'foCdnBrokenUntil' => PrestaChamps\MailchimpPro\FoCdnHealth::getBrokenUntil(),
            // NOTE: don't cast these to bool — radio "value=\"1\"/0\"" + v-model use
            // strict ===, so we keep PHP's raw string from Configuration::get to
            // match the value attributes. Same pattern as 'logQueue' / 'logCronjob'.
            'autotrackEnabled' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED],
            'identifyFormat' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT],
            'identifyAjaxMode' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE],
            // Master toggle for the form-identify watchers (newsletter / restock
            // / contact / forgot password). When enabled and a visitor submits
            // one of those forms with a valid email, the bridge fires
            // api.identify({type, value}) once — provided the visitor isn't
            // already identified via login or guest mid-checkout.
            'identifyOnForms' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS],
            // Admin-supplied CSS selector overrides for the newsletter form —
            // ADDITIVE to the hardcoded built-ins below. Defaults empty;
            // admin pastes in only theme/module-specific selectors that the
            // built-ins don't catch. saveSettings + getConfigValues both
            // sanitize against the safe-character allowlist (length cap +
            // CSS-punct-only) so window.mailchimp.* can't be a stored-XSS
            // vector.
            'identifyNewsletterSelectorsCustom' => $this->sanitizePixelFormSelectorsCustom(
                isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM])
                    ? (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM]
                    : ''
            ),
            // Restock-alert form additive selectors. Same shape and sanitizer
            // as newsletter — built-ins live in $pixelFormCoreDefaults['restock'],
            // this is just the admin's adds (3rd-party themes / wishlist mods
            // / custom restock UI that don't match either stock template's
            // selectors). Empty default; bridge unions with built-ins.
            'identifyRestockSelectorsCustom' => $this->sanitizePixelFormSelectorsCustom(
                isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM])
                    ? (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM]
                    : ''
            ),
            // Contact form additive selectors. Same shape + sanitizer as
            // newsletter/restock. Built-ins (select[name="id_contact"] +
            // section.contact-form form) live in $pixelFormCoreDefaults['contact'];
            // a runtime URL selector is also added by the bridge JS from
            // window.prestashop.urls.pages.contact (multi-lingual safe).
            'identifyContactSelectorsCustom' => $this->sanitizePixelFormSelectorsCustom(
                isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM])
                    ? (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM]
                    : ''
            ),
            // Forgot-password form additive selectors. Same shape +
            // sanitizer as the other 3. Built-ins
            // (form.forgotten-password + form:has(button#send-reset-link))
            // live in $pixelFormCoreDefaults['forgotPassword']; bridge JS
            // also appends a runtime URL selector from
            // window.prestashop.urls.pages.password.
            'identifyForgotPasswordSelectorsCustom' => $this->sanitizePixelFormSelectorsCustom(
                isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM])
                    ? (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM]
                    : ''
            ),
            // Hardcoded built-in selectors per form. Shipped to the BO so
            // Vue can render them as DISABLED tags in the Multiselect
            // (visible but admin can't remove — only the master toggle
            // disables them). The bridge reads its OWN copy from a JsDef
            // emission in mailchimppro.php for the storefront watcher; this
            // copy is purely for the BO UI surface. Single source of truth
            // is MailchimpProConfig::$pixelFormCoreDefaults.
            'pixelFormCoreDefaults' => MailchimpProConfig::$pixelFormCoreDefaults,
            'pixelLogEnabled' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED],
            'pixelLogLevel' => $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_LOG_LEVEL],
            // Available log-level options + their human-readable label/description.
            // Single source of truth in PixelLog::$logLevels — adding a new level
            // there auto-shows it in the BO select widget here, no template edits.
            'pixelLogLevels' => PixelLog::$logLevels,
            'pixelLogMaxRows' => (int) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_LOG_MAX_ROWS],
            'pixelLogRateLimitPerMin' => (int) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN],
            // Custom checkout controllers — stored as comma-separated string,
            // exposed verbatim. Vue splits to array for the Multiselect tags
            // input and re-joins on save (handled in saveSettings payload).
            //
            // Defense-in-depth: strip any char outside [a-zA-Z0-9_,-] before
            // exposing to window.mailchimp.* via Media::addJsDef. PS's
            // addJsDef pipeline serializes via json_encode without HEX flags,
            // so a stale DB value containing `</script>...` could XSS the BO
            // page even if the save-time filter (also enforced) is bypassed.
            // Legitimate PS php_self values are simple alphanumeric+hyphen +
            // comma-separators — anything else is suspicious and dropped.
            'pixelCheckoutControllers' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS])
                ? preg_replace('/[^a-zA-Z0-9_,\-]/', '', (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS])
                : 'opc',
            // Custom order-confirmation controllers — parallel of pixelCheckoutControllers
            // for the PURCHASED event's 3-prong detection. Default '' since
            // most 3rd-party modules extend OrderConfirmationController and
            // fire the displayOrderConfirmation hook naturally; the BO list
            // is the escape-hatch for modules with custom confirmation
            // controllers that break both conventions (php_self !==
            // 'order-confirmation' AND page_name !== 'order-confirmation').
            // Same allowlist filter as pixelCheckoutControllers — see note above.
            'pixelOrderConfirmationControllers' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS])
                ? preg_replace('/[^a-zA-Z0-9_,\-]/', '', (string) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS])
                : '',
            // Strict-consent mode toggle — when on, hookDisplayHeader emits an
            // inline cookie-set script BEFORE MC.js so the SDK defaults to
            // opted-out (required for strict-jurisdiction GDPR compliance).
            'pixelRequireConsent' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_REQUIRE_CONSENT])
                ? (int) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_REQUIRE_CONSENT]
                : 1,
            // SDK settle-delay — milliseconds the bridge waits after detecting
            // pixel.installed===true before firing the first track/identify.
            // Clamped server-side at save time; out-of-range values fall back
            // to the default. Cast to int here so the JS-side v-model.number
            // sees a proper integer. Range + default constants are the
            // single source of truth, exposed to the Vue admin SPA below as
            // sdkSettleDelayMs{Min,Max,Default} so the input attributes +
            // any JS-side clamp can bind to them.
            'sdkSettleDelayMs' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS])
                ? (int) $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS]
                : MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT,
            'sdkSettleDelayMsMin' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MIN,
            'sdkSettleDelayMsMax' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MAX,
            'sdkSettleDelayMsDefault' => MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT,
            'pixelAvailable' => isset($configValues[MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE]) ? $configValues[MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE] : null,
            // JSON cache of verify-script-installation state. Surfaced in the
            // BO Pixel tab health panel as "Site verified ✓ since DATE" / or
            // "Verification pending — last attempt MM failed: ERROR".
            'scriptVerified' => isset($configValues[MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED])
                && is_array($configValues[MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED])
                ? $configValues[MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED]
                : null,
            'translations' => [
                'clearPixelLogConfirm' => $this->trans(
                    'Clear all pixel log entries?',
                    [],
                    'Modules.Mailchimppro.Adminmailchimpproconfiguration'
                ),
            ],
        ];
    }

    public function ajaxProcessSaveSettings()
    {
        // Pixel-tab settings we audit-log when they change. Snapshot BEFORE
        // the save so we can diff after; the kill-switch in PixelLog::insert
        // silently drops the entry if MAILCHIMP_PIXEL_LOG_ENABLED was just
        // disabled in this same save — acceptable, the next re-enable still
        // lands. Friendly label keeps the log readable next to the existing
        // PIXEL_ENABLED / PIXEL_DISABLED rows.
        $pixelTrackedKeys = [
            MailchimpProConfig::MAILCHIMP_PIXEL_AUTOTRACK_ENABLED => 'Automatic page-view tracking',
            MailchimpProConfig::MAILCHIMP_PIXEL_REQUIRE_CONSENT => 'Require explicit consent',
            MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS => 'SDK settle delay (ms)',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT => 'Identify format',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_AJAX_MODE => 'Identify AJAX mode',
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_ENABLED => 'Pixel log enabled',
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_LEVEL => 'Pixel log level',
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_MAX_ROWS => 'Pixel log max rows',
            MailchimpProConfig::MAILCHIMP_PIXEL_LOG_RATE_LIMIT_PER_MIN => 'Pixel log rate limit (per min)',
            MailchimpProConfig::MAILCHIMP_PIXEL_HOOK_DISPLAYPRODUCTADDITIONALINFO_ENABLED => 'displayProductAdditionalInfo hook',
            MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS => 'Checkout controllers',
            MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS => 'Order-confirmation controllers',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_ON_FORMS => 'Form-based identify (master)',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM => 'Newsletter form custom selectors',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM => 'Restock form custom selectors',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM => 'Contact form custom selectors',
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM => 'Forgot-password form custom selectors',
        ];
        $oldPixelValues = [];
        foreach ($pixelTrackedKeys as $key => $label) {
            $oldPixelValues[$key] = (string) Configuration::get($key);
        }

        // Keys we strip to an alphanumeric/comma/hyphen/underscore allowlist on
        // save — these feed Media::addJsDef → window.mailchimp.* on next BO
        // page render, which goes through PHP's default json_encode (no HEX
        // flags). An admin entering `</script><script>...` here would XSS
        // the BO config page. Defense in depth: getConfigValues() also strips
        // on read, so even a stale DB row (from before this fix) is cleaned.
        $controllerListKeys = [
            MailchimpProConfig::MAILCHIMP_PIXEL_CHECKOUT_CONTROLLERS => true,
            MailchimpProConfig::MAILCHIMP_PIXEL_ORDER_CONFIRMATION_CONTROLLERS => true,
        ];

        // Keys whose value is a comma-separated list of CSS selectors. Pass
        // through sanitizePixelFormSelectorsCustom() — same safe-character
        // allowlist (alphanumeric + standard CSS selector punctuation) + 500
        // char length cap. Excludes `<` and `&` so a Stored-XSS injection
        // attempt can't break out via window.mailchimp.* JsDef.
        $selectorListKeys = [
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_NEWSLETTER_SELECTORS_CUSTOM => true,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_RESTOCK_SELECTORS_CUSTOM => true,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_CONTACT_SELECTORS_CUSTOM => true,
            MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORGOT_PASSWORD_SELECTORS_CUSTOM => true,
        ];

        foreach (MailchimpProConfig::$keyMap as $index => $item) {
            $value = $this->getJsonPayloadValue($index);
            // Skip keys missing from the payload — partial saves shouldn't
            // overwrite untouched config. Without this guard, the next line
            // would json_encode(null) → the literal string "null" (4 chars,
            // truthy) and persist that to ps_configuration, corrupting
            // boolean-shaped config values across the module.
            if (null === $value) {
                continue;
            }
            $value = is_scalar($value) ? $value : json_encode($value);
            if (is_bool($value)) {
                $value = (int) $value;
            }
            if (isset($controllerListKeys[$item])) {
                $value = preg_replace('/[^a-zA-Z0-9_,\-]/', '', (string) $value);
            }
            // SDK settle-delay: silent clamp to the [min, max] range.
            // Out-of-range OR non-numeric input falls back to the default.
            // Mirrors the JS-side getSdkSettleDelayMs() guard — server-side
            // clamp is the authoritative one (the bridge re-clamps on load,
            // defense-in-depth against direct ps_configuration tampering).
            // Range constants are the single source of truth on
            // MailchimpProConfig — same values land in $defaultValues, the
            // FO JsDef, and the BO template min/max attrs.
            if (MailchimpProConfig::MAILCHIMP_PIXEL_SDK_SETTLE_DELAY_MS === $item) {
                $intValue = (int) $value;
                if ($intValue < MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MIN
                    || $intValue > MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_MAX) {
                    $intValue = MailchimpProConfig::PIXEL_SDK_SETTLE_DELAY_MS_DEFAULT;
                }
                $value = $intValue;
            }
            if (isset($selectorListKeys[$item])) {
                $value = $this->sanitizePixelFormSelectorsCustom((string) $value);
            }
            MailchimpProConfig::saveValue($item, $value);
        }

        foreach ($pixelTrackedKeys as $key => $label) {
            $new = (string) Configuration::get($key);
            if ($new !== $oldPixelValues[$key]) {
                PixelLog::insert(
                    PixelLog::SEVERITY_INFO,
                    PixelLog::SOURCE_BO,
                    'PIXEL_SETTING_CHANGED',
                    $label . ': "' . $oldPixelValues[$key] . '" -> "' . $new . '"'
                );
            }
        }

        exit;
    }

    public function ajaxProcessAddNewPromo()
    {
        $data = $this->getJsonPayloadValue('data');

        if (empty($data['promoName']) || empty($data['promoPrefix']) || empty($data['promoExpiration']) || empty($data['promoReduction'])) {
            $this->ajaxDie(['success' => false, 'message' => 'Please fill in all the required fields.']);
        }

        if (false === strtotime($data['promoExpiration'])) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo expiration date.']);
        }

        $promoName = pSQL($data['promoName']);
        $promoPrefix = pSQL($data['promoPrefix']);
        $promoSuffix = pSQL($data['promoSuffix']);
        $promoExpiration = (new DateTime($data['promoExpiration']))->format('Y-m-d H:i:s');
        $promoCampaign = !empty($data['promoCampaign']) ? pSQL($data['promoCampaign']) : null;
        $promoReduction = !empty($data['promoReduction']) ? pSQL($data['promoReduction']) : null;
        $promoStatus = !empty($data['promoStatus']) ? (int) $data['promoStatus'] : 0;
        $promoReductionType = !empty($data['promoReductionType']) ? (int) $data['promoReductionType'] : 0;
        // $promoStart = !empty($data['promoStart']) ? (new DateTime($data['promoStart']))->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        $cartRule = new CartRule(PromoCode::getCartRuleId());
        // create new cart rule
        if (false == Validate::isLoadedObject($cartRule)) {
            $cartRule->name = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $cartRule->name[$lang['id_lang']] = $this->trans('MC PromoCode', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            }
            $cartRule->description = $this->module->name;
            $cartRule->code = 'MC' . Tools::passwdGen(15);
            $cartRule->quantity = 99999999;
            $cartRule->quantity_per_user = $cartRule->quantity;
            $cartRule->date_from = date('Y-m-d 00:00:00');
            $cartRule->date_to = date('Y-m-d 00:00:00', strtotime('+ 7 years'));
            $cartRule->active = true;

            if (!$cartRule->add()) {
                $this->ajaxDie([
                    'success' => false,
                    'message' => $this->trans('Failed to create the cart rule: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . Db::getInstance()->getMsgError(),
                ]);
            }

            PromoCode::setCartRuleId($cartRule->id);
        }

        $campaign = new Campaign();
        $campaign->name = $promoName;
        $campaign->prefix = $promoPrefix;
        $campaign->suffix = $promoSuffix;
        $campaign->status = $promoStatus;
        $campaign->reduction = $promoReduction;
        $campaign->reduction_type = $promoReductionType;
        $campaign->campaign_id = $promoCampaign;
        $campaign->end_date = $promoExpiration;

        if (!$campaign->add()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to add promo code.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        // / auto creation on promo save
        $promoId = $campaign->id;
        if ($campaign->status) {
            $response = $this->syncPromoToMailchimp($promoId);
        }
        // / auto creation on promo save

        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo code added successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
    }

    public function ajaxProcessGetPromoDetails()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promo = Campaign::getById($promoId);

        if (!$promo) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        // Check if the ps_emailsubscription table exists
        $tableExists = Db::getInstance()->getValue("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = '" . _DB_PREFIX_ . "emailsubscription'
        ");

        $totalQuery = '
            SELECT COUNT(*) FROM (
                SELECT email FROM ' . _DB_PREFIX_ . "customer WHERE email != ''
                "
            . ($tableExists ? '
                UNION
                SELECT email FROM ' . _DB_PREFIX_ . 'emailsubscription
                ' : '')
            . '
            ) combined
            WHERE email IN (
                SELECT email FROM ' . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $promoId
            )
        ";
        $total = Db::getInstance()->getValue($totalQuery);
        $totalAllQuery = '
            SELECT COUNT(*) FROM (
                SELECT email FROM ' . _DB_PREFIX_ . "customer WHERE email != ''
                "
            . ($tableExists ? '
                UNION
                SELECT email FROM ' . _DB_PREFIX_ . 'emailsubscription
                ' : '')
            . '
            ) combined
        ';
        $totalAll = Db::getInstance()->getValue($totalAllQuery);

        $remainingQuery = '
            SELECT COUNT(*) FROM (
                SELECT email FROM ' . _DB_PREFIX_ . "customer WHERE email != ''
                "
            . ($tableExists ? '
                UNION
                SELECT email FROM ' . _DB_PREFIX_ . 'emailsubscription
                ' : '')
            . '
            ) combined
            WHERE email NOT IN (
                SELECT email FROM ' . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $promoId
            )
        ";
        $remaining = Db::getInstance()->getValue($remainingQuery);

        $this->ajaxDie(['success' => true, 'data' => $promo, 'count' => ['remain' => $remaining, 'total' => $total, 'totalAll' => $totalAll]]);
    }

    public function ajaxProcessUpdatePromo()
    {
        $data = $this->getJsonPayloadValue('data');

        if (empty($data['promoId']) || empty($data['promoName']) || empty($data['promoPrefix']) || empty($data['promoExpiration']) || empty($data['promoReduction'])) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Please fill in all the required fields.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        if (false === strtotime($data['promoExpiration'])) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo expiration date.']);
        }

        $promoId = (int) $data['promoId'];
        $promoName = pSQL($data['promoName']);
        $promoPrefix = pSQL($data['promoPrefix']);
        $promoSuffix = pSQL($data['promoSuffix']);
        $promoExpiration = (new DateTime($data['promoExpiration']))->format('Y-m-d H:i:s');
        $promoReduction = !empty($data['promoReduction']) ? pSQL($data['promoReduction']) : null;
        $promoCampaign = !empty($data['promoCampaign']) ? pSQL($data['promoCampaign']) : null;
        $promoStatus = !empty($data['promoStatus']) ? (int) $data['promoStatus'] : 0;
        $promoReductionType = !empty($data['promoReductionType']) ? (int) $data['promoReductionType'] : 0;

        $campaign = new Campaign($promoId);

        if (!Validate::isLoadedObject($campaign)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        // Update campaign properties
        $campaign->name = $promoName;
        $campaign->prefix = $promoPrefix;
        $campaign->suffix = $promoSuffix;
        $campaign->end_date = $promoExpiration;
        $campaign->status = $promoStatus;
        $campaign->reduction_type = $promoReductionType;
        $campaign->reduction = $promoReduction;
        $campaign->campaign_id = $promoCampaign;

        if (!$campaign->update()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to update promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        // / auto creation on promo save
        $promoId = $campaign->id;

        if (null == $campaign->id_merge_field_mc || 0 == $campaign->id_merge_field_mc) {
            if ($campaign->status) {
                $response = $this->syncPromoToMailchimp($promoId);
            }
        }
        // / auto creation on promo save

        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo updated successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
    }

    public function ajaxProcessDeletePromo()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($promoId);

        if ($campaign->id_merge_field_mc > 0) {
            $promoCommand = new PromoMergeTagSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                $campaign,
                [],
                $this->context->shop->id
            );

            $delete_response = $promoCommand->deleteMergeFieldFromMailchimp();
        }

        if ($campaign->getCodesCount() > 0) {
            $campaign->deleteCodes();
        }

        if (!$campaign->delete()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to delete promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessDeletePromocode()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promocode ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promocode = new PromoCode($promoId);

        if (!$promocode->delete()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to delete promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessDeleteBulkPromocodes()
    {
        $promoIds = $this->getJsonPayloadValue('ids');
        if (!is_array($promoIds) || empty($promoIds)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('No promocodes selected.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        foreach ($promoIds as $promoId) {
            $promocode = new PromoCode((int) $promoId);
            if (!$promocode->delete()) {
                $this->ajaxDie([
                    'success' => false,
                    'message' => $this->trans('Failed to delete promo with ID %promoid%.', ['%promoid%' => $promoId], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
        }

        $this->ajaxDie(['success' => true, 'message' => $this->trans('Selected promocodes deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessGetPromoCodes()
    {
        $promoId = $this->getJsonPayloadValue('id');

        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo ID.']);
        }

        $promoCodes = PromoCode::getPromoCodes($promoId);

        if ($promoCodes) {
            $this->ajaxDie(['success' => true, 'codes' => $promoCodes]);
        } else {
            $this->ajaxDie(['success' => false, 'message' => 'No promo codes found for this promo ID.']);
        }
    }

    public function ajaxProcessGenerateCodes()
    {
        $id = (int) $this->getJsonPayloadValue('id');
        $batchSize = 50;
        $offset = (int) $this->getJsonPayloadValue('offset') ?: 0;

        if (!$id) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($id);
        if (!$campaign->id) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Campaign not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $prefix = $campaign->prefix;
        $suffix = $campaign->suffix;
        $date = date('Y-m-d H:i:s');

        // Check if emailsubscription table exists
        $tableExists = Db::getInstance()->getValue("
        SELECT COUNT(*) 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = '" . _DB_PREFIX_ . "emailsubscription'
    ");

        // Get total emails count
        $totalQuery = '
        SELECT COUNT(*) FROM (
            SELECT email FROM ' . _DB_PREFIX_ . "customer WHERE email != ''
            " . ($tableExists ? 'UNION SELECT email FROM ' . _DB_PREFIX_ . 'emailsubscription' : '') . '
        ) combined
        WHERE email NOT IN (
            SELECT email FROM ' . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $id
        )
    ";
        $total = Db::getInstance()->getValue($totalQuery);

        // Get batch of emails
        $emailQuery = '
        SELECT DISTINCT email FROM (
            SELECT email FROM ' . _DB_PREFIX_ . "customer WHERE email != ''
            " . ($tableExists ? 'UNION SELECT email FROM ' . _DB_PREFIX_ . 'emailsubscription' : '') . '
        ) combined
        WHERE email NOT IN (
            SELECT email FROM ' . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $id
        )
        LIMIT 0, $batchSize
    ";
        $emails = Db::getInstance()->executeS($emailQuery);

        if (empty($emails)) {
            $this->ajaxDie([
                'success' => true,
                'message' => $this->trans('No more emails to process.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'remaining' => 0,
                'total' => $total,
            ]);
        }

        $queue = new PrestaChamps\Queue\Queue();

        $generatedCount = 0;
        foreach ($emails as $emailData) {
            $email = $emailData['email'];
            $code = PromoCode::generateCode($prefix, $suffix);

            $data = [
                'id_promo_main' => (int) $campaign->id,
                'code' => pSQL($code),
                'status' => 0,
                'email' => pSQL($email),
                'date_add' => pSQL($date),
                'date_upd' => pSQL($date),
            ];

            if (Db::getInstance()->insert('mailchimppro_promocodes', $data)) {
                $lastInsertId = Db::getInstance()->Insert_ID();

                $job = new MergeTagPromoCodeSyncJob();
                $job->promoCodeId = $lastInsertId;

                $job->setCampaignId($campaign->id);
                $job->setPromoEmail($email);
                $job->setPromoCode($code);
                $job->setMergeTagPromoCode($campaign->tag_merge_field_mc);
                $job->setMethod(PromoMergeTagSyncCommand::SYNC_METHOD_PATCH);

                $queue->push($job, 'setup-promoCode', $this->context->shop->id);

                ++$generatedCount;
            }
        }

        $remaining = $total - $batchSize;

        $this->ajaxDie([
            'success' => true,
            'message' => $this->trans('Batch processed successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            'generated_count' => $generatedCount,
            'remaining' => $remaining,
            'total' => $total,
            'next_offset' => $offset + $batchSize,
        ]);
    }

    // ajax call by manual sending
    // public function ajaxProcessSyncPromo()
    // {
    //     $promoId = (int)$this->getJsonPayloadValue('promoId');

    //     if (!$promoId) {
    //         $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    //     }

    //     $campaign = new Campaign($promoId);

    //     $response = $this->syncPromoToMailchimp($promoId);

    //     $resp = $this->addPromoCodesToQueue($campaign);

    //     dump($response);
    //     die("tttt");

    // }

    public function syncPromoToMailchimp($promoId)
    {
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($promoId);

        if (!Validate::isLoadedObject($campaign)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promoCommand = new PromoMergeTagSyncCommand(
            $this->context,
            $this->module->getApiClient(),
            $campaign,
            [],
            $this->context->shop->id
        );

        if ($promoCommand->getMergeFieldLimitExceeded()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('You already have the maximum 30 merge fields. Please remove a field in order to add a new one.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        if ($promoCommand->createMergeFieldAtMailchimp()) {
            // $resp = $this->addPromoCodesToQueue($campaign);
            $this->ajaxDie(['success' => true, 'message' => $this->trans('Merge field successfully created at Mailchimp.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
        }
    }

    public function addPromoCodesToQueue($campaign)
    {
        // get all promo codes
        $allPromoCodes = PromoCode::getPromoCodes($campaign->id);

        $queue = new PrestaChamps\Queue\Queue();

        foreach ($allPromoCodes as $promoCode) {
            $job = new MergeTagPromoCodeSyncJob();
            $job->promoCodeId = $promoCode['id'];

            $job->setCampaignId($campaign->id);
            $job->setPromoEmail($promoCode['email']);
            $job->setPromoCode($promoCode['code']);
            $job->setMergeTagPromoCode($campaign->tag_merge_field_mc);
            $job->setMethod(PromoMergeTagSyncCommand::SYNC_METHOD_PATCH);

            $queue->push($job, 'setup-promoCode', $this->context->shop->id);
        }
    }

    public function ajaxProcessMarkReadJsonJobs()
    {
        Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE, date('Y-m-d H:i:s'));
        Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE, PsContext::getEmployeeId());
        exit;
    }

    public function ajaxProcessMarkReadAutoList()
    {
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE, date('Y-m-d H:i:s'));
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE, PsContext::getEmployeeId());
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
        exit;
    }

    public function ajaxProcessGetEntityCount()
    {
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $this->ajaxDie([
            'products' => $repository->getProductsCount(),
            'orders' => $repository->getOrdersCount(),
            'customers' => $repository->getCustomersCount(),
            'cartRules' => $repository->getCartRulesCount(),
            'newsletterSubscribers' => $repository->getNewsletterSubscribersCount(),
            'totalJobs' => $this->getNumberOfTotalJobs(),
        ]);
    }

    public function ajaxProcessUpdateStaticContent()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'cronjobLogContent' => $this->getCronjobLogContent(),
            'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
            'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
            'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
            'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
            'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
            'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
            'lastCronjob' => $configValues[MailchimpProConfig::LAST_CRONJOB],
            'lastCronjobExecutionTime' => $configValues[MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME],
            'totalJobs' => $this->getNumberOfTotalJobs(),
        ]);
    }

    public function getCronjobLogContent($maxLines = 500)
    {
        // Define the allowed directory
        $allowedDirectory = _PS_MODULE_DIR_ . $this->module->name . '/logs/';

        // Resolve the file path and sanitize it
        $filePath = realpath($allowedDirectory . 'cronjob.log');

        // Check if the file exists and is within the allowed directory
        if ($filePath && 0 === strpos($filePath, $allowedDirectory) && file_exists($filePath)) {
            // Only read the last X lines to prevent memory issues with large log files
            $file = new SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $startLine = max(0, $totalLines - $maxLines);
            $file->seek($startLine);

            $content = '';
            while (!$file->eof()) {
                $content .= $file->fgets();
            }

            if ($startLine > 0) {
                $content = "... (truncated, showing last {$maxLines} lines of {$totalLines} total) ...\n\n" . $content;
            }

            return $content;
        }

        return ''; // Fallback to an empty string if the file doesn't exist or is invalid
    }

    public function autoSyncStore()
    {
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_POST);

            $response = $command->execute();

            if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);

                $this->cacheConnectedSiteScriptFromCommandResponse($response);

                $this->scriptVerify($response);

                $this->ensureCompanyMergeField();
            }

            return $response;
        } catch (Exception $exception) {
            // $this->responses[$entityId] = $this->mailchimp->getLastResponse();
            // \PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
        }
    }

    /**
     * Cache TTL for the verify-script-installation result. After this many
     * seconds the next scriptVerify call re-POSTs even if previously verified.
     * Generous default since Mailchimp's verification rarely flips back to
     * false on its own — the merchant only "loses" verification on extreme
     * events (connected_site deletion, audience deletion, account migration).
     */
    public const SCRIPT_VERIFIED_CACHE_TTL_SECONDS = 2592000; // 30 days

    /**
     * Tell Mailchimp the connected_site's script is installed so it can flip
     * the site's `verified` flag and unlock downstream features. Result is
     * cached as JSON in MAILCHIMP_SCRIPT_VERIFIED so repeat calls within the
     * TTL skip the network round-trip.
     *
     * JSON cache shape:
     *   {
     *     verified: bool,                // last known server state
     *     last_checked_at: string,       // 'YYYY-MM-DD HH:MM:SS' of last success
     *     last_attempt_at: string,       // 'YYYY-MM-DD HH:MM:SS' of last try (any result)
     *     last_error: string|null        // error message from last failed attempt
     *   }
     *
     * TTL-based refresh keeps verification fresh after audience swaps or
     * connected_site recreation. Failures are logged via PrestaShopLogger
     * and the BO health panel shows last-attempt status.
     *
     * Optional `$apiBody` fast-path: if the API response exposes
     * `connected_site.verified === true` we skip the POST AND refresh the
     * cache. As of writing, /ecommerce/stores/{id} does not include
     * `verified` in the nested shape; kept dormant for /connected-sites/{id}
     * callers that may carry the field directly.
     *
     * @param array|null $apiBody parsed API response (GET store or command shape)
     *
     * @return bool true if verified (cached, short-circuited, or POST succeeded);
     *              false if the POST failed (cache updated with attempt + error)
     */
    public function scriptVerify($apiBody = null)
    {
        // Fast path 1: in-API verified flag (dormant today — see docblock).
        if (is_array($apiBody)) {
            $parsed = isset($apiBody['requestLastResponse']) && is_array($apiBody['requestLastResponse'])
                ? $apiBody['requestLastResponse']
                : $apiBody;
            if (isset($parsed['connected_site']['verified'])
                && true === $parsed['connected_site']['verified']) {
                $this->writeVerifiedCache(true, null);

                return true;
            }
        }

        // Fast path 2: cached verified state within TTL.
        $cached = $this->readVerifiedCache();
        if (is_array($cached)
            && isset($cached['verified'])
            && true === $cached['verified']
            && isset($cached['last_checked_at'])) {
            $age = time() - strtotime($cached['last_checked_at']);
            if ($age >= 0 && $age < self::SCRIPT_VERIFIED_CACHE_TTL_SECONDS) {
                return true;
            }
        }

        // Slow path: actually POST verify-script-installation.
        try {
            $command = new SiteVerifyCommand(
                $this->module->getApiClient(),
                $this->context->shop
            );
            $command->execute();
            $this->writeVerifiedCache(true, null);
            // Mirror to the Pixel log table so merchants see the audit trail
            // next to SCRIPT_CACHED / PIXEL_STATE_REFRESH entries on the
            // Pixel tab. Cache hits + API-body shortcuts stay silent to keep
            // the log free of "we skipped a redundant call" noise — only
            // actual network activity is logged.
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'SCRIPT_VERIFIED',
                'POST verify-script-installation succeeded'
            );

            return true;
        } catch (Exception $exception) {
            $msg = $exception->getMessage();
            PrestaShopLogger::addLog(
                '[MAILCHIMP] verify-script-installation failed: ' . $msg,
                3,
                null,
                'mailchimppro'
            );
            // Preserve any prior `verified=true` cache on transient failures —
            // only the attempt + error timestamps update, so the BO panel can
            // surface "verified, last attempt failed" without losing the last
            // good verification record.
            $this->writeVerifiedCache(null, $msg);
            PixelLog::insert(
                PixelLog::SEVERITY_ERROR,
                PixelLog::SOURCE_BO,
                'SCRIPT_VERIFIED',
                'verify-script-installation failed: ' . $msg
            );

            return false;
        }
    }

    /**
     * Read the verify-script-installation JSON cache. Returns the decoded
     * array, or null if the row is empty / legacy boolean / malformed.
     */
    protected function readVerifiedCache()
    {
        $raw = Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED);
        if (!is_string($raw) || '' === $raw || 'null' === $raw) {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Write the verify-script-installation JSON cache. Passing `null` for
     * `$verified` preserves the previous verified flag (used on transient
     * POST failures so a temporary error doesn't drop a known-good record).
     *
     * @param bool|null $verified true/false to set, null to preserve prior
     * @param string|null $lastError error message from a failed attempt, or null on success
     */
    protected function writeVerifiedCache($verified, $lastError)
    {
        $now = date('Y-m-d H:i:s');
        $prior = $this->readVerifiedCache();
        $next = [
            'verified' => null !== $verified
                ? (bool) $verified
                : (isset($prior['verified']) ? (bool) $prior['verified'] : false),
            'last_checked_at' => (true === $verified)
                ? $now
                : (isset($prior['last_checked_at']) ? $prior['last_checked_at'] : null),
            'last_attempt_at' => $now,
            'last_error' => $lastError,
        ];
        Configuration::updateValue(
            MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED,
            json_encode($next, JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Cache the Mailchimp pixel <script> fragment from a parsed ecommerce-store API body
     * (POST/PATCH/GET on /ecommerce/stores/{id} all return the same `connected_site.site_script` shape).
     * Called from every successful StoreSyncCommand path so the script is auto-cached without
     * the merchant needing to click "Fetch script" manually.
     *
     * @param mixed $apiBody parsed response body
     *
     * @return bool true if a non-empty fragment was cached
     */
    protected function cacheConnectedSiteScript($apiBody)
    {
        if (is_array($apiBody)
            && isset($apiBody['connected_site']['site_script']['fragment'])
            && is_string($apiBody['connected_site']['site_script']['fragment'])
            && '' !== $apiBody['connected_site']['site_script']['fragment']
        ) {
            $fragment = $apiBody['connected_site']['site_script']['fragment'];
            Configuration::updateValue(
                MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED,
                $fragment,
                true
            );
            Configuration::updateValue(
                MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE,
                date('Y-m-d H:i:s')
            );
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'SCRIPT_CACHED',
                'Pixel script cached (' . strlen($fragment) . ' bytes)'
            );
            // Refresh the local pixel-enabled cache from the connected_site
            // resource's `is_pixel_enabled` field. If $apiBody happens to BE a
            // /connected-sites/{id} response (has is_pixel_enabled at root),
            // we read it directly. Otherwise (caller passed an /ecommerce/stores
            // body where is_pixel_enabled isn't surfaced in the nested shape),
            // refreshPixelEnabledState makes a GET /connected-sites/{id} call.
            $this->refreshPixelEnabledState($apiBody);

            return true;
        }

        return false;
    }

    /**
     * Refresh the local MAILCHIMP_PIXEL_AVAILABLE cache from Mailchimp's
     * authoritative `is_pixel_enabled` flag on the connected_site resource.
     *
     * Optional `$apiBody` fast-path: if a caller already has the connected_site
     * body in hand (e.g., from a fresh GET /connected-sites/{id} response) and
     * it contains `is_pixel_enabled` at the root, skip the extra API call.
     * `$apiBody` from /ecommerce/stores/{id} responses doesn't have this field
     * at root (only `connected_site.site_script` is nested), so callers passing
     * a store response fall through to the API call.
     *
     * Result cached in MAILCHIMP_PIXEL_AVAILABLE (JSON):
     *   {enabled: bool, last_checked_at: 'YYYY-MM-DD HH:MM:SS', source: 'api'}
     *
     * @param array|null $apiBody parsed connected_site response (or store body)
     *
     * @return bool|null the freshly-cached enabled state, or null on API failure
     */
    protected function refreshPixelEnabledState($apiBody = null)
    {
        $enabled = null;

        // Fast path: API body already contains the field.
        if (is_array($apiBody) && array_key_exists('is_pixel_enabled', $apiBody)) {
            $enabled = (bool) $apiBody['is_pixel_enabled'];
        }

        // Slow path: GET /connected-sites/{id} for an authoritative read.
        if (null === $enabled) {
            try {
                $command = new PrestaChamps\MailchimpPro\Commands\GetConnectedSiteCommand(
                    $this->module->getApiClient(),
                    $this->context->shop
                );
                $response = $command->execute();
                if (is_array($response) && array_key_exists('is_pixel_enabled', $response)) {
                    $enabled = (bool) $response['is_pixel_enabled'];
                }
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    '[MAILCHIMP] GET /connected-sites/{id} failed: ' . $e->getMessage(),
                    3,
                    null,
                    'mailchimppro'
                );
                PixelLog::insert(
                    PixelLog::SEVERITY_ERROR,
                    PixelLog::SOURCE_BO,
                    'PIXEL_STATE_REFRESH',
                    'GET /connected-sites/{id} failed: ' . $e->getMessage()
                );

                return null;
            }
        }

        if (null === $enabled) {
            // API succeeded but didn't include the field — treat as unknown.
            return null;
        }

        $state = [
            'enabled' => $enabled,
            'last_checked_at' => date('Y-m-d H:i:s'),
            'source' => 'api',
        ];
        Configuration::updateValue(
            MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE,
            json_encode($state, JSON_UNESCAPED_SLASHES)
        );

        PixelLog::insert(
            PixelLog::SEVERITY_INFO,
            PixelLog::SOURCE_BO,
            'PIXEL_STATE_REFRESH',
            'Pixel ' . ($enabled ? 'enabled' : 'disabled')
                . ' (read from connected_site.is_pixel_enabled)'
        );

        return $enabled;
    }

    /**
     * AJAX action — POST /connected-sites/{id}/actions/enable-pixel.
     * Just flips the Mailchimp-side flag. The caller (Vue togglePixel)
     * chains this with ajaxProcessSyncStoresScript() to refresh the local
     * cached fragment + state in a separate request.
     */
    public function ajaxProcessEnablePixel()
    {
        try {
            $command = new PrestaChamps\MailchimpPro\Commands\EnablePixelCommand(
                $this->module->getApiClient(),
                $this->context->shop
            );
            $command->execute();
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'PIXEL_ENABLED',
                'Pixel enabled via BO toggle'
            );
            $this->ajaxDie([
                'hasError' => false,
                'errorMessage' => null,
                'successMessage' => $this->trans('Pixel enabled', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (Exception $e) {
            $msg = $e->getMessage() ?: $this->trans('Failed to enable the Mailchimp pixel — unknown API error. Check the module logs for details.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            PrestaShopLogger::addLog(
                '[MAILCHIMP] enable-pixel failed: ' . $msg,
                3,
                null,
                'mailchimppro'
            );
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $msg,
            ]);
        }
    }

    /**
     * AJAX action — POST /connected-sites/{id}/actions/disable-pixel.
     * Symmetric counterpart to ajaxProcessEnablePixel. Local refresh runs
     * through the syncStoresScript chain from JS.
     */
    public function ajaxProcessDisablePixel()
    {
        try {
            $command = new PrestaChamps\MailchimpPro\Commands\DisablePixelCommand(
                $this->module->getApiClient(),
                $this->context->shop
            );
            $command->execute();
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'PIXEL_DISABLED',
                'Pixel disabled via BO toggle'
            );
            $this->ajaxDie([
                'hasError' => false,
                'errorMessage' => null,
                'successMessage' => $this->trans('Pixel disabled', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (Exception $e) {
            $msg = $e->getMessage() ?: $this->trans('Failed to disable the Mailchimp pixel — unknown API error. Check the module logs for details.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            PrestaShopLogger::addLog(
                '[MAILCHIMP] disable-pixel failed: ' . $msg,
                3,
                null,
                'mailchimppro'
            );
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $msg,
            ]);
        }
    }

    /**
     * Same as cacheConnectedSiteScript() but takes the StoreSyncCommand::execute() $response shape.
     * The execute() method discards the parsed return value of $mailchimp->post()/patch() and only
     * keeps getLastResponse() — which returns ['headers' => [...], 'body' => '<json string>'].
     * So we json_decode the body to get the parsed connected_site.
     *
     * @param mixed $response StoreSyncCommand::execute() return
     *
     * @return bool
     */
    protected function cacheConnectedSiteScriptFromCommandResponse($response)
    {
        if (is_array($response)
            && isset($response['requestLastResponse']['body'])
            && is_string($response['requestLastResponse']['body'])
            && '' !== $response['requestLastResponse']['body']
        ) {
            $parsed = json_decode($response['requestLastResponse']['body'], true);
            if (is_array($parsed)) {
                return $this->cacheConnectedSiteScript($parsed);
            }
        }

        return false;
    }

    /**
     * Remove the locally cached pixel script fragment + its fetched-date,
     * and the connected_site-bound state caches (verify state, pixel-availability
     * state) tied to a specific connected_site that no longer exists after
     * delete-ecommerce-data. Called from ajaxProcessDeleteEcommerceData on success.
     */
    protected function clearConnectedSiteScript()
    {
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED);
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE);
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE);
        // Without this, a fresh ecommerce-store created after delete inherits
        // stale verify state (30-day TTL window short-circuits future verifies).
        Configuration::deleteByName(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED);
        PixelLog::insert(
            PixelLog::SEVERITY_INFO,
            PixelLog::SOURCE_BO,
            'SCRIPT_CLEARED',
            'Pixel script cache cleared (e-commerce data deletion)'
        );
    }

    /**
     * AJAX: return paginated/filterable rows from ps_mailchimppro_pixel_log.
     * Used by the BO Pixel-tab log viewer.
     */
    public function ajaxProcessGetPixelLogs()
    {
        try {
            $limit = (int) $this->getJsonPayloadValue('limit');
            if ($limit <= 0) {
                $limit = 100;
            }
            $offset = max(0, (int) $this->getJsonPayloadValue('offset'));

            $filters = [];
            $severity = $this->getJsonPayloadValue('severity');
            if (is_numeric($severity)) {
                $filters['severity'] = (int) $severity;
            }
            $source = $this->getJsonPayloadValue('source');
            if (is_string($source) && (PixelLog::SOURCE_BO === $source || PixelLog::SOURCE_FO === $source)) {
                $filters['source'] = $source;
            }
            $eventType = $this->getJsonPayloadValue('event_type');
            if (is_string($eventType) && '' !== $eventType) {
                $filters['event_type'] = $eventType;
            }
            // Free-text filters — input sanitization happens inside
            // PixelLog::buildFilterWhere() (truncate + pSQL). Empty strings
            // are dropped here so we don't end up with `LIKE '%%'` clauses
            // that match-everything-yet-look-like-a-filter.
            $messageFilter = $this->getJsonPayloadValue('message');
            if (is_string($messageFilter) && '' !== trim($messageFilter)) {
                $filters['message'] = trim($messageFilter);
            }
            $ipFilter = $this->getJsonPayloadValue('ip');
            if (is_string($ipFilter) && '' !== trim($ipFilter)) {
                $filters['ip'] = trim($ipFilter);
            }
            // Date range — accept HTML5 <input type="date"> (yyyy-mm-dd) AND
            // datetime variants. Format validated by regex; anything that
            // doesn't match is silently ignored to avoid SQL injection
            // surface (pSQL on top is belt-and-suspenders).
            $from = $this->getJsonPayloadValue('from');
            if (is_string($from) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', $from)) {
                $filters['from'] = $from;
            }
            $to = $this->getJsonPayloadValue('to');
            if (is_string($to) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', $to)) {
                $filters['to'] = $to;
            }

            $rows = PixelLog::getRecent($limit, $filters, $offset);
            $total = PixelLog::countAll($filters);

            $this->ajaxDie([
                'hasError' => false,
                'rows' => $rows,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (Exception $exception) {
            PrestaShopLogger::addLog('[MAILCHIMP-PIXEL-LOG] getPixelLogs failed: ' . $exception->getMessage(), 3, null, 'mailchimppro');
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Failed to fetch pixel log entries.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function ajaxProcessResetFoCdnHealth()
    {
        try {
            PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'FO_CDN_HEALTH_RESET',
                'FO CDN health check reset by admin'
            );
            $this->ajaxDie([
                'hasError' => false,
                'successMessage' => $this->trans('CDN health check reset. Next storefront visit determines the state fresh.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (Exception $exception) {
            PrestaShopLogger::addLog('[MAILCHIMP-CDN-HEALTH] reset failed: ' . $exception->getMessage(), 3, null, 'mailchimppro');
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Failed to reset CDN health check.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    /**
     * AJAX: clear all pixel log rows (BO "Clear log" button).
     */
    public function ajaxProcessClearPixelLog()
    {
        try {
            PixelLog::clear();
            PixelLog::insert(
                PixelLog::SEVERITY_INFO,
                PixelLog::SOURCE_BO,
                'LOG_CLEARED',
                'Pixel log cleared by admin'
            );
            $this->ajaxDie([
                'hasError' => false,
                'successMessage' => $this->trans('Pixel log cleared.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (Exception $exception) {
            PrestaShopLogger::addLog('[MAILCHIMP-PIXEL-LOG] clearPixelLog failed: ' . $exception->getMessage(), 3, null, 'mailchimppro');
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Failed to clear pixel log.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    /**
     * Returns true iff hookDisplayHeader will actually inject the pixel <script>
     * (matches the gate at mailchimppro.php:448). Used to skip CSP detection
     * when there's nothing for CSP to block.
     *
     * @return bool
     */
    protected function pixelIsInjecting()
    {
        return !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED));
    }

    /**
     * Sanitize an admin-supplied comma-separated CSS selector list for the
     * pixel form-identify watcher. The value ends up on every storefront
     * page via window.MC_PIXEL.identifyForms.* — Media::addJsDef uses
     * json_encode WITHOUT the JSON_HEX_* flags, so an attacker who could
     * inject `</script><script>` here would have stored-XSS on the FO.
     *
     * Two defenses, both enforced:
     *   - Length cap 500 chars (caller pastes a list of selectors, not a
     *     stylesheet — 500 is room for ~10 reasonable selectors)
     *   - Character allowlist — alphanumerics + the punctuation CSS
     *     selectors actually need:
     *       . # [ ] ( ) = " ' : * + ~ > ^ $ | , -  _ space
     *     The ^ $ | are required for attribute-selector match modes:
     *       [attr^=v] prefix match
     *       [attr$=v] suffix match
     *       [attr|=v] hyphen-prefixed match
     *     Excludes `<`, `&`, backslash, semicolon, curly braces, slash —
     *     anything that could enable HTML / JS injection if it leaked
     *     past json_encode on the way to window.MC_PIXEL.*.
     *
     * @param string $raw
     *
     * @return string sanitized + trimmed (may be '')
     */
    protected function sanitizePixelFormSelectorsCustom($raw)
    {
        $raw = (string) $raw;
        if (strlen($raw) > 500) {
            $raw = substr($raw, 0, 500);
        }
        // Allowed chars: a-z A-Z 0-9 _ - . # space comma [ ] ( ) = " ' : * + ~ > ^ $ |
        $clean = preg_replace('/[^a-zA-Z0-9_\-\.\#\s,\[\]\(\)\=\"\'\:\*\+\~\>\^\$\|]/', '', $raw);

        return trim((string) $clean);
    }

    // CSP parsing moved to \PrestaChamps\MailchimpPro\Helpers\CspParser so it
    // can be exercised in isolation by tests/Unit/Helpers/CspParserTest.php.

    /**
     * Probe the FO with a cURL HEAD, parse Content-Security-Policy header(s),
     * and analyze whether script-src + connect-src allow chimpstatic.com.
     *
     * @return array {checked: bool, csp_present?: bool, compatible?: bool,
     *               missing_directives?: string[], reason?: string,
     *               http_code?: int, curl_errno?: int}
     */
    protected function detectFrontOfficeCsp()
    {
        if (!function_exists('curl_init')) {
            return ['checked' => false, 'reason' => 'curl_unavailable'];
        }
        $url = $this->context->shop->getBaseURL(true);
        if (empty($url)) {
            return ['checked' => false, 'reason' => 'no_fo_url'];
        }
        $version = $this->module->version;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mailchimppro-CSP-Probe/' . $version,
        ]);
        $rawHeaders = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);

        if (false === $rawHeaders || $httpCode < 200 || $httpCode >= 400) {
            return [
                'checked' => false,
                'reason' => $curlErrNo ? 'curl_error' : 'http_error',
                'http_code' => $httpCode,
                'curl_errno' => (int) $curlErrNo,
            ];
        }

        if (!preg_match_all('/^Content-Security-Policy:\s*(.+?)\r?$/im', $rawHeaders, $matches)) {
            return ['checked' => true, 'csp_present' => false];
        }

        // Multiple CSP headers — concat with semicolon (browser intersects, our match-any check works either way).
        $csp = implode('; ', array_map('trim', $matches[1]));
        $scriptOk = PrestaChamps\MailchimpPro\Helpers\CspParser::allowsHost($csp, 'script-src', 'chimpstatic.com');
        $connectOk = PrestaChamps\MailchimpPro\Helpers\CspParser::allowsHost($csp, 'connect-src', 'chimpstatic.com');

        $missing = [];
        if (!$scriptOk) {
            $missing[] = 'script-src';
        }
        if (!$connectOk) {
            $missing[] = 'connect-src';
        }

        return [
            'checked' => true,
            'csp_present' => true,
            'compatible' => empty($missing),
            'missing_directives' => $missing,
        ];
    }

    /**
     * Called once the store is bound to an audience — gives customer company
     * values a field to land in. Non-fatal: the Customers tab surfaces a retry
     * when the audience is at Mailchimp's merge-field cap.
     */
    protected function ensureCompanyMergeField()
    {
        try {
            PrestaChamps\MailchimpPro\CompanyMergeField::ensure(
                $this->module->getApiClient(),
                Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)
            );
        } catch (Exception $exception) {
            PrestaShopLogger::addLog(
                '[MAILCHIMP] COMPANY merge field ensure failed: ' . $exception->getMessage(),
                2,
                null,
                'mailchimppro'
            );
        }
    }

    public function ajaxProcessCreateCompanyMergeField()
    {
        try {
            $result = PrestaChamps\MailchimpPro\CompanyMergeField::ensure(
                $this->module->getApiClient(),
                Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)
            );
            $this->ajaxDie([
                'hasError' => !$result['success'],
                'error' => $result['error'],
                'companyFieldStatus' => $result['status'],
            ]);
        } catch (Exception $exception) {
            $this->ajaxDie(['hasError' => true, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * AJAX: force a fresh CSP probe + persist + return the new state.
     * Called by Vue's mounted() hook (when cache is >24h stale) and by the
     * "Re-check now" link in the warning banner. Logs state-transitions
     * INTO warning states (unreachable / missing_directives) only once each
     * via PrestaShopLogger; recovery is silent.
     */
    public function ajaxProcessCheckPixelCspNow()
    {
        try {
            $now = time();

            if (!$this->pixelIsInjecting()) {
                $result = ['checked' => false, 'reason' => 'no_script_cached'];
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS, json_encode($result), true);
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_CHECKED_AT, $now);
                $this->ajaxDie([
                    'hasError' => false,
                    'cspStatus' => $result,
                    'cspCheckedAt' => $now,
                ]);
            }

            $result = $this->detectFrontOfficeCsp();

            if (empty($result['checked'])) {
                $state = 'unreachable';
            } elseif (empty($result['csp_present'])) {
                $state = 'no_csp';
            } elseif (!empty($result['compatible'])) {
                $state = 'compatible';
            } else {
                $state = 'missing_directives';
            }

            $prevRaw = Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS);
            $prev = (is_string($prevRaw) && '' !== $prevRaw) ? json_decode($prevRaw, true) : null;
            $lastLogged = (is_array($prev) && isset($prev['last_logged_state'])) ? $prev['last_logged_state'] : null;

            // Log only on transitions INTO warning states, never recoveries
            if ($state !== $lastLogged && in_array($state, ['unreachable', 'missing_directives'], true)) {
                $msg = '[MAILCHIMP-PIXEL-CSP] state=' . $state;
                if ('missing_directives' === $state && !empty($result['missing_directives'])) {
                    $msg .= ' missing_chimpstatic_in=' . implode(',', $result['missing_directives']);
                }
                if ('unreachable' === $state && isset($result['reason'])) {
                    $msg .= ' reason=' . $result['reason'];
                }
                PrestaShopLogger::addLog($msg, 2, null, 'mailchimppro');
                // Mirror to the pixel-specific log so the Pixel tab viewer surfaces it.
                PixelLog::insert(
                    PixelLog::SEVERITY_WARNING,
                    PixelLog::SOURCE_BO,
                    'CSP_' . strtoupper($state),
                    $msg
                );
                $result['last_logged_state'] = $state;
            } else {
                $result['last_logged_state'] = null !== $lastLogged ? $lastLogged : $state;
            }

            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_STATUS, json_encode($result), true);
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PIXEL_CSP_CHECKED_AT, $now);

            $this->ajaxDie([
                'hasError' => false,
                'cspStatus' => $result,
                'cspCheckedAt' => $now,
            ]);
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[MAILCHIMP-PIXEL-CSP] check failed: ' . $e->getMessage(), 3, null, 'mailchimppro');
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Snapshot of the connection-health state for embedding in any ajax
     * response. Lets the Vue app update its reactive banner without waiting
     * for a full page reload. Values match the keys the initial `assign()`
     * server-renders into window.mailchimp.*.
     *
     * @return array
     */
    protected function getHealthStatePayload()
    {
        return [
            'healthStateFlag' => (string) PrestaChamps\MailchimpPro\HealthState::getFlag(),
            'healthStateSetAt' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SET_AT),
            'healthStateSource' => (string) Configuration::get(MailchimpProConfig::MAILCHIMP_HEALTH_STATE_SOURCE),
            'queueBreakerActive' => PrestaChamps\MailchimpPro\HealthState::isBreakerActive()
                || !Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED),
            'foCdnBroken' => PrestaChamps\MailchimpPro\FoCdnHealth::isBroken(),
            'foCdnBrokenUntil' => PrestaChamps\MailchimpPro\FoCdnHealth::getBrokenUntil(),
        ];
    }

    public function ajaxProcessSyncStoresScript()
    {
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );

            $response = $command->getStoreExists($this->getShopId(), true);

            if ($this->cacheConnectedSiteScript($response)) {
                $this->scriptVerify($response);

                // Read back the freshly-written JSON caches so the Vue side
                // can update reactive state (pixelAvailable, scriptVerified)
                // without a full page reload — same data the BO health panel
                // reads on next render.
                $pixelAvailableRaw = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_PIXEL_AVAILABLE);
                $scriptVerifiedRaw = (string) Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED);

                $this->ajaxDie(array_merge([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'scriptCachedDate' => Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE),
                    'pixelAvailable' => '' === $pixelAvailableRaw ? null : json_decode($pixelAvailableRaw, true),
                    'scriptVerified' => '' === $scriptVerifiedRaw ? null : json_decode($scriptVerifiedRaw, true),
                    'successMessage' => $this->trans('Store script has been fetched successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ], $this->getHealthStatePayload()));
            } else {
                // getStoreExists returned false (either 404 on /ecommerce/stores or
                // no connected_site.site_script.fragment in the response). Peek at
                // the API client's last response to distinguish "resource missing"
                // (404) from other API errors, so the admin gets an actionable
                // message instead of the previous generic "syncing store script".
                $lastResponse = $this->module->getApiClient()->getLastResponse();
                $httpCode = isset($lastResponse['headers']['http_code']) ? (int) $lastResponse['headers']['http_code'] : 0;

                if (404 === $httpCode) {
                    $errorMessage = $this->trans(
                        'Mailchimp store or connected site not found (HTTP 404). Likely deleted on the Mailchimp side. Re-sync your store from the Store sync tab to restore the connection.',
                        [],
                        'Modules.Mailchimppro.Adminmailchimpproconfiguration'
                    );
                } elseif (200 === $httpCode) {
                    // Store OK but response has no site fragment — probe the site directly.
                    // A 404 flows through the classifier and fires SITE_MISSING.
                    try {
                        $siteCmd = new PrestaChamps\MailchimpPro\Commands\GetConnectedSiteCommand(
                            $this->module->getApiClient(),
                            $this->context->shop
                        );
                        $siteCmd->execute();
                    } catch (Exception $e) {
                        // Suppressed — the classifier already fired inside
                        // makeRequest; we're just probing for state, not
                        // consuming the result.
                    }
                    $siteProbeCode = 0;
                    $siteLastResponse = $this->module->getApiClient()->getLastResponse();
                    if (isset($siteLastResponse['headers']['http_code'])) {
                        $siteProbeCode = (int) $siteLastResponse['headers']['http_code'];
                    }
                    if (404 === $siteProbeCode) {
                        $errorMessage = $this->trans(
                            'Mailchimp connected site is missing. Storefront pixel, popups, newsletter forms and subscribe widgets are suppressed until the connection is rebuilt (back-office sync continues unaffected). Fix from the Mailchimp Config banner.',
                            [],
                            'Modules.Mailchimppro.Adminmailchimpproconfiguration'
                        );
                    } else {
                        $errorMessage = $this->trans('Error during syncing store script...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                    }
                } else {
                    $errorMessage = $this->trans('Error during syncing store script...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                }

                $this->ajaxDie(array_merge([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ], $this->getHealthStatePayload()));
            }
        } catch (Exception $exception) {
            $lastResponse = $this->module->getApiClient()->getLastResponse();
            $httpCode = isset($lastResponse['headers']['http_code']) ? (int) $lastResponse['headers']['http_code'] : 0;

            if (404 === $httpCode) {
                $errorMessage = $this->trans(
                    'Mailchimp store or connected site not found (HTTP 404). Likely deleted on the Mailchimp side. Re-sync your store from the Store sync tab to restore the connection.',
                    [],
                    'Modules.Mailchimppro.Adminmailchimpproconfiguration'
                );
            } else {
                $errorMessage = $exception->getMessage()
                    ?: $this->trans('Error during syncing store script...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            }

            $this->ajaxDie(array_merge([
                'hasError' => true,
                'errorMessage' => $errorMessage,
            ], $this->getHealthStatePayload()));
        }
    }

    public function ajaxProcessSyncStores()
    {
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_POST);
            $response = $command->execute();

            if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);

                $this->cacheConnectedSiteScriptFromCommandResponse($response);

                $this->scriptVerify($response);

                $this->ensureCompanyMergeField();

                PrestaChamps\MailchimpPro\HealthState::clearFlag();
                PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();

                $this->ajaxDie([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'scriptCachedDate' => Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE),
                    'successMessage' => $this->trans('Store has been synced!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            } else {
                if (isset($response['requestLastErrors'])) {
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(';', array_values($response['requestLastErrors']));
                    } else {
                        $errorMessage = $response['requestLastErrors'];
                    }
                } else {
                    $errorMessage = $this->trans('Error during syncing store...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                }
                $this->ajaxDie([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (Exception $exception) {
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Error during syncing store...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function ajaxProcessAddProductsToQueue()
    {
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $products = array_column($repository->getProducts(), 'id_product');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($products as $product) {
            $job = new ProductSyncJob();
            $job->productId = $product;
            // batch modifications
            $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_BATCH);
            // $job->setMethod(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_METHOD_PUT);
            // end of batch modifications
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessInitializeSpecificPrices()
    {
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $specific_prices = $repository->getSpecificPrices();

        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));

        foreach ($specific_prices as $specific_price) {
            $from_date = new DateTime($specific_price['from'], new DateTimeZone(@date_default_timezone_get()));
            $to_date = new DateTime($specific_price['to'], new DateTimeZone(@date_default_timezone_get()));

            if ($from_date > $current_date) {
                // Insert the data using the insert method, with INSERT_IGNORE option
                Db::getInstance()->insert(
                    'mailchimppro_specific_price',
                    [
                        'id_specific_price' => (int) $specific_price['id_specific_price'],
                        'id_product' => (int) $specific_price['id_product'],
                        'start_date' => pSQL($specific_price['from']),
                        'end_date' => pSQL($specific_price['to']),
                        'needToRun' => 2, // Direct value since it's static
                        'id_shop' => (int) $specific_price['id_shop'],
                    ],
                    false,
                    true,
                    Db::INSERT_IGNORE
                );
            } elseif ($to_date > $current_date) {
                // Insert the data using the insert method, with INSERT_IGNORE option
                Db::getInstance()->insert(
                    'mailchimppro_specific_price',
                    [
                        'id_specific_price' => (int) $specific_price['id_specific_price'],
                        'id_product' => (int) $specific_price['id_product'],
                        'start_date' => pSQL($specific_price['from']),
                        'end_date' => pSQL($specific_price['to']),
                        'needToRun' => 1, // Direct value since it's static
                        'id_shop' => (int) $specific_price['id_shop'],
                    ],
                    false,
                    true,
                    Db::INSERT_IGNORE
                );
            }
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddCustomersToQueue()
    {
        // $customers = array_column(Customer::getCustomers(true), 'id_customer');
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $customers = array_column($repository->getCustomers(), 'id_customer');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($customers as $customer) {
            $job = new CustomerSyncJob();
            $job->customerId = $customer;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddOrdersToQueue()
    {
        // $orders = $this->getOrderIds();
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $orders = array_column($repository->getOrders(), 'id_order');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($orders as $order) {
            $job = new OrderSyncJob();
            $job->orderId = $order;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddCartRulesToQueue()
    {
        // $cartRules = $this->getCartRules();
        $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
        $cartRules = array_column($repository->getCartRules(), 'id_cart_rule');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($cartRules as $cartRule) {
            $job = new CartRuleSyncJob();
            $job->cartRuleId = $cartRule;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddNewsletterSubscribersToQueue()
    {
        // Module::isEnabled() is deprecated since PS 9.0 but still functional
        // on every supported PS version. Dynamic call hides the method name
        // from PHPStan's deprecation surface — same pattern as Order::getOrderByCartId.
        if (call_user_func(['Module', 'isEnabled'], 'Ps_Emailsubscription')
            || call_user_func(['Module', 'isEnabled'], 'blocknewsletter')) {
            $repository = new PrestaChamps\MailchimpPro\EntitySyncRepository();
            $newsletterSubscribers = $repository->getNewsletterSubscribers();
            $queue = new PrestaChamps\Queue\Queue();
            foreach ($newsletterSubscribers as $newsletterSubscriber) {
                $job = new NewsletterSubscriberSyncJob();
                $job->newsletterSubscriber = $newsletterSubscriber;
                $queue->push($job, 'setup-wizard', $this->context->shop->id);
            }
        }

        $this->ajaxDie(['ok']);
    }

    /* protected function getOrderIds()
    {
        $shopId = Shop::getContextShopID();
        $query = new DbQuery();
        $query->from('orders');
        $query->select('id_order');
        if ($shopId) {
            $query->where("id_shop = {$shopId}");
        }

        return array_column(Db::getInstance()->executeS($query), 'id_order');
    } */

    /* protected function getCartRules()
    {
        $query = new DbQuery();
        $query->from('cart_rule');
        $query->select('id_cart_rule');
        $query->where('shop_restriction = 0');
        $ids = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');

        $query = new DbQuery();
        $query->from('cart_rule_shop');
        $query->select('id_cart_rule');
        $query->where('id_shop = ' . pSQL($this->context->shop->id));
        $result = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');
        $result = array_unique(array_merge($ids, $result));
        sort($result, SORT_NUMERIC);

        return $result;
    } */

    public function ajaxProcessDeleteEcommerceData()
    {
        try {
            /* $shops = array_column(Shop::getShops(true), 'id_shop');
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                $shops
            ); */
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_DELETE);
            $response = $command->execute();

            if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                $this->clearConnectedSiteScript();
                PrestaChamps\MailchimpPro\HealthState::clearFlag();
                PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();
                $this->ajaxDie([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'successMessage' => $this->trans('E-commerce data has been deleted', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            } else {
                if (isset($response['requestLastErrors'])) {
                    if (404 == $response['requestLastResponse']['headers']['http_code']) {
                        // DELETE 404 = idempotent success (store already gone MC-side).
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                        $this->clearConnectedSiteScript();
                        PrestaChamps\MailchimpPro\HealthState::clearFlag();
                        PrestaChamps\MailchimpPro\FoCdnHealth::clearBroken();
                        $this->ajaxDie([
                            'hasError' => false,
                            'errorMessage' => null,
                            'result' => $response,
                            'successMessage' => $this->trans('E-commerce data has been deleted', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                        ]);
                    }
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(';', array_values($response['requestLastErrors']));
                    } else {
                        $errorMessage = $response['requestLastErrors'];
                    }
                } else {
                    $errorMessage = $this->trans('Error during deleting e-commerce data', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                }
                $this->ajaxDie([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (Exception $exception) {
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Error during deleting e-commerce data', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function processSyncProduct()
    {
        if (Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) && Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED) && Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
            try {
                if ($productId = Tools::getValue('productId')) {
                    if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                        $command = new PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                            $this->context,
                            $this->module->getApiClient(),
                            [$productId]
                        );
                        $command->setSyncMode($command::SYNC_MODE_REGULAR);
                        $command->setMethod($command::SYNC_METHOD_PATCH);
                        $result = $command->execute();

                        if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                            $command->setMethod($command::SYNC_METHOD_POST);
                            $result = $command->execute();
                        }

                        $this->ajaxDie([
                            'hasError' => false,
                            'error' => null,
                            'command_result' => $result,
                            'result' => $this->trans('Product synced', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                        ]);
                    } else {
                        $job = new ProductSyncJob();
                        $job->productId = $productId;
                        $job->setSyncMode(PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                        $queue = new PrestaChamps\Queue\Queue();
                        $queue->push($job, 'hook-product-extra', $this->context->shop->id);
                        $this->ajaxDie([
                            'hasError' => false,
                            'error' => null,
                            'result' => $this->trans('Product job has been successfully added.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                        ]);
                    }
                }
            } catch (Exception $exception) {
                $this->ajaxDie(
                    [
                        'hasError' => true,
                        'error' => $exception->getMessage(),
                    ],
                    null,
                    null,
                    400
                );
            }
        }
    }

    /**
     * Process the toggle of promoOverridesEnabled option.
     *
     * @return void
     */
    public function ajaxProcessTogglePromoOverrides()
    {
        // Validate and sanitize input
        $enabled = (bool) $this->getJsonPayloadValue('enabled');

        try {
            // Save the setting using PrestaShop's Configuration class
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, (int) $enabled);

            // If enabling overrides, attempt to install them
            if ($enabled) {
                $autoInstalled = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);

                if (!$autoInstalled) {
                    // Create an instance of the ConflictChecker
                    $conflictChecker = new PrestaChamps\PrestaShop\Override\ConflictChecker($this->module);

                    // Check for conflicts
                    $pendingOverrides = $conflictChecker->getPendingOverrides();
                    $conflicts = [];

                    foreach ($pendingOverrides as $relativePath) {
                        $conflictResult = $conflictChecker->hasConflict($relativePath);

                        if ('conflict' === $conflictResult['status']) {
                            $conflicts[] = $conflictResult;
                        }
                    }

                    if (!empty($conflicts)) {
                        // Save conflicts in configuration
                        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, json_encode($conflicts));

                        // Return conflict information with detailed data
                        $this->ajaxDie([
                            'success' => true,
                            'overrideStatus' => 'conflicts',
                            'message' => $this->trans('Overrides could not be installed automatically due to conflicts. Manual installation is required.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                            'conflicts' => $conflicts,
                        ]);
                    } else {
                        // No conflicts, attempt to install overrides
                        try {
                            $overrideInstaller = new PrestaChamps\PrestaShop\Override\OverrideInstaller($this->module);
                            $result = $overrideInstaller->install();

                            if ($result) {
                                // Mark as auto-installed
                                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 1);
                                // Clear any saved conflicts
                                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, '');

                                $this->ajaxDie([
                                    'success' => true,
                                    'overrideStatus' => 'installed',
                                    'message' => $this->trans('Overrides installed successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                                ]);
                            } else {
                                $this->ajaxDie([
                                    'success' => true,
                                    'overrideStatus' => 'failed',
                                    'message' => $this->trans('Failed to install overrides. Manual installation may be required.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                                ]);
                            }
                        } catch (Exception $e) {
                            // Log the error using PrestaShop's logger
                            PrestaShopLogger::addLog(
                                'Error during override installation: ' . $e->getMessage(),
                                3, // Error severity
                                null,
                                'Mailchimppro',
                                null,
                                true
                            );

                            // Shape the failure as a single "install" entry so the Vue
                            // manual-install dialog (which expects [{file, methods}, ...])
                            // can render the actual error reason instead of being blank.
                            $installError = [['file' => 'install', 'methods' => [$e->getMessage()]]];
                            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, json_encode($installError));

                            $this->ajaxDie([
                                'success' => false,
                                'overrideStatus' => 'error',
                                'message' => $this->trans('Override installation failed: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
                                'conflicts' => $installError,
                                'requiresManualInstallation' => true,
                            ]);
                        }
                    }
                } else {
                    // Already auto-installed
                    $this->ajaxDie([
                        'success' => true,
                        'overrideStatus' => 'auto_installed',
                        'message' => $this->trans('Overrides are already installed.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    ]);
                }
            } else {
                // Overrides disabled
                $this->ajaxDie([
                    'success' => true,
                    'overrideStatus' => 'disabled',
                    'message' => $this->trans('Promo overrides have been disabled.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
        } catch (Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error toggling promo overrides: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );

            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Mark overrides as manually installed.
     *
     * @return void
     */
    public function ajaxProcessMarkOverridesInstalled()
    {
        try {
            // Update the configuration to mark overrides as manually installed
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 0);
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 1);
            // Clear any saved conflicts
            // Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, '');

            $this->ajaxDie([
                'success' => true,
                'message' => $this->trans('Overrides marked as installed.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error marking overrides as installed: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );

            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Remove overrides if they were auto-installed without conflicts.
     *
     * @return void
     */
    public function ajaxProcessRemovePromoOverrides()
    {
        try {
            $autoInstalled = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);
            $conflicts = Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS);

            // Only remove if auto-installed and no conflicts
            if ($autoInstalled && (!$conflicts || '[]' === $conflicts)) {
                $overrideInstaller = new PrestaChamps\PrestaShop\Override\OverrideInstaller($this->module);
                $result = $overrideInstaller->uninstall();

                if ($result) {
                    // Reset configuration
                    Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 0);
                    Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 0);

                    $this->ajaxDie([
                        'success' => true,
                        'message' => $this->trans('Overrides have been removed successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    ]);
                } else {
                    $this->ajaxDie([
                        'success' => false,
                        'message' => $this->trans('Failed to remove overrides.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    ]);
                }
            } else {
                // If not auto-installed or has conflicts, just disable
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 0);

                $this->ajaxDie([
                    'success' => true,
                    'message' => $this->trans('Promo overrides have been disabled.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
        } catch (Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error removing promo overrides: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );

            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Check override installation status.
     *
     * @return void
     */
    public function ajaxProcessCheckOverrideStatus()
    {
        try {
            $enabled = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED);
            $autoInstalled = (bool) Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);

            // Get saved conflicts
            $savedConflicts = Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS);
            $conflicts = $savedConflicts ? json_decode($savedConflicts, true) : [];

            // Check if overrides exist
            $conflictChecker = new PrestaChamps\PrestaShop\Override\ConflictChecker($this->module);
            $cartOverrideExists = $conflictChecker->overrideExists('classes/Cart.php');
            $cartRuleOverrideExists = $conflictChecker->overrideExists('classes/CartRule.php');

            // Check if they have our custom code
            $cartHasCustomCode = $conflictChecker->hasCustomCode('classes/Cart.php');
            $cartRuleHasCustomCode = $conflictChecker->hasCustomCode('classes/CartRule.php');

            $this->ajaxDie([
                'success' => true,
                'enabled' => $enabled,
                'autoInstalled' => $autoInstalled,
                'conflicts' => $conflicts,
                'cartOverrideExists' => $cartOverrideExists,
                'cartRuleOverrideExists' => $cartRuleOverrideExists,
                'cartHasCustomCode' => $cartHasCustomCode,
                'cartRuleHasCustomCode' => $cartRuleHasCustomCode,
                'allInstalled' => ($cartOverrideExists && $cartRuleOverrideExists && $cartHasCustomCode && $cartRuleHasCustomCode),
            ]);
        } catch (Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error checking override status: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );

            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    public function ajaxProcessRefreshReports()
    {
        $this->module->assignStatisticsVariables(['refresh' => true]);
        $this->ajaxDie([
            'statistics' => $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/configuration/_statistics-data.tpl'
            ),
        ]);
    }
}
