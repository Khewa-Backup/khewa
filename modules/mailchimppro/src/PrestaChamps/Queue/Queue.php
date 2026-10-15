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

namespace PrestaChamps\Queue;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Commands\PromoMergeTagSyncCommand;
use PrestaChamps\MailchimpPro\Helpers\PsContext;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\Queue\Exceptions\InvalidJobException;
use PrestaChamps\Queue\Jobs\CartRuleSyncJob;
use PrestaChamps\Queue\Jobs\CartSyncJob;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\MergeTagPromoCodeSyncJob;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\Queue\Jobs\OrderSyncJob;
use PrestaChamps\Queue\Jobs\ProductSyncJob;

class Queue
{
    // protected $table = "ps_mailchimppro_jobs";
    protected $table = _DB_PREFIX_ . 'mailchimppro_jobs';

    protected $max_try;

    protected $max_jobs;

    public const STATUS_AVAILABLE = 0;

    public const STATUS_PROCESSED = 100;

    public const STATUS_PROCESSING = 10;

    public const STATUS_FAILED = -1;

    public const PRIORITIES_BY_TYPE = [
        'product' => 1,
        'customer' => 2,
        'cartRule' => 3,
        'order' => 4,
        'cart' => 5,
        'newsletterSubscriber' => 6,
        'mergeTagPromoCode' => 7,
    ];

    public function __construct()
    {
        $this->max_try = ($max = (int) \Configuration::get(\MailchimpProConfig::QUEUE_ATTEMPT)) && $max > 0 && \Validate::isUnsignedInt($max) ? $max : 2;
        $this->max_jobs = ($limit = (int) \Configuration::get(\MailchimpProConfig::QUEUE_STEP)) && $limit > 0 && \Validate::isUnsignedInt($limit) ? $limit : 10;
    }

    public function push(JobInterface $job, $channel, $id_shop)
    {
        $class = $this->escape($job->getJobType());

        $bodyJson = $job->convertToArrayJson();

        if (!$entityId = $job->getJobId()) {
            $entityId = 0;
        }
        $priority = self::PRIORITIES_BY_TYPE[$class] ?? 1;

        // Special handling for product jobs only
        if ('product' === $class) {
            $newJobData = json_decode($bodyJson, true);
            $newMethod = isset($newJobData['method']) ? $newJobData['method'] : null;

            $query = new \DbQuery();
            $query->select('id_job, body');
            $query->from('mailchimppro_jobs');
            $query->where('id_entity = ' . (int) $entityId);
            $query->where('type = \'' . pSQL($class) . '\'');
            $query->where('id_shop = ' . (int) $id_shop);

            $existingJob = \Db::getInstance()->getRow($query);

            if ($existingJob) {
                $existingJobData = json_decode($existingJob['body'], true);
                $existingMethod = isset($existingJobData['method']) ? $existingJobData['method'] : null;
                if ($existingMethod !== $newMethod) {
                    $where = 'id_job = ' . (int) $existingJob['id_job'];
                    \Db::getInstance()->delete('mailchimppro_jobs', $where);
                }
            }
        }

        // Use PrestaShop's insert method with INSERT_IGNORE option
        \Db::getInstance()->insert(
            'mailchimppro_jobs',
            [
                'channel' => pSQL($channel),
                'body' => \Db::getInstance()->escape($bodyJson),
                'type' => pSQL($class),
                'id_entity' => (int) $entityId,
                'priority' => (int) $priority,
                'id_shop' => (int) $id_shop,
            ],
            false,
            true,
            \Db::INSERT_IGNORE
        );
    }

    protected function escape($value)
    {
        $search = ['\\', "\x00", "\n", "\r", "'", '"', "\x1a"];
        $replace = ['\\\\', '\\0', '\\n', '\\r', "\'", '\"', '\\Z'];

        return str_replace($search, $replace, $value);
    }

    public function getNumberOfAvailableJobs($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        // Create a new DbQuery object
        $query = new \DbQuery();
        $query->select('COUNT(*)');
        $query->from('mailchimppro_jobs');
        $query->where('`status` = ' . (int) self::STATUS_AVAILABLE);
        $query->where('`id_shop` = ' . (int) $idShop);

        // Execute the query and get the count
        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function getNumberOfTotalJobs($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        // Create a new DbQuery object
        $query = new \DbQuery();
        $query->select('COUNT(*)');
        $query->from('mailchimppro_jobs');
        $query->where('`id_shop` = ' . (int) $idShop);

        // Execute the query and get the count
        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function getNumberOfAvailableJobsPerType($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        $query = new \DbQuery();
        $query->select('`type`, COUNT(*) as count');
        $query->from('mailchimppro_jobs');
        $query->where('`status` = ' . (int) self::STATUS_AVAILABLE);
        $query->where('`id_shop` = ' . (int) $idShop);
        $query->groupBy('`type`');

        // Execute the query and fetch the results
        $results = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        // Convert the results into a key-value pair array
        $keyValuePair = [];
        foreach ($results as $row) {
            $keyValuePair[$row['type']] = $row['count'];
        }

        return $keyValuePair;
    }

    // //////////////////////
    private function countAvailableProductJobsForBatch($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        $query = new \DbQuery();
        $query->select('COUNT(*)');
        $query->from('mailchimppro_jobs');
        $query->where('`status` = ' . (int) self::STATUS_AVAILABLE);
        $query->where('`id_shop` = ' . (int) $idShop);
        $query->where('`type` = \'' . pSQL(ProductSyncJob::JOB_TYPE) . '\'');
        $query->where('`channel` LIKE \'' . pSQL($channel) . '\'');

        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
    // /////////////////////////

    public function getNumberOfJobsInFlight($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        $query = new \DbQuery();
        $query->select('COUNT(*)');
        $query->from('mailchimppro_jobs');
        $query->where('`status` = ' . (int) self::STATUS_PROCESSING);
        $query->where('`id_shop` = ' . (int) $idShop);

        return (int) \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    protected function reserveJob($jobId)
    {
        // Prepare the data to be updated
        $data = [
            'status' => (int) self::STATUS_PROCESSING,
            'locked_at' => ['type' => 'sql', 'value' => 'CURRENT_TIMESTAMP'],  // Using SQL function directly
        ];

        // Define the where condition
        $where = 'id_job = ' . (int) $jobId;

        // Execute the update query
        return \Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }

    protected function unReserveJob($jobId, $message = 'Unknown error')
    {
        // Prepare the data to be updated
        $data = [
            'status' => (int) self::STATUS_AVAILABLE,
            'error' => pSQL($message),
        ];

        // Define the where condition
        $where = 'id_job = ' . (int) $jobId;

        // Execute the update query
        return \Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }

    /**
     * Defer a job back to AVAILABLE without burning a retry attempt. Used when
     * the failure was transient — specifically: an order/cart depended on a
     * product whose batch is still being processed asynchronously by Mailchimp.
     * The pop() call already incremented attempts, so we decrement it back so
     * the job can retry indefinitely until the race window closes.
     */
    protected function deferJobForRetry($jobId, $message = 'Deferred — product batch still in progress')
    {
        $data = [
            'status' => (int) self::STATUS_AVAILABLE,
            'error' => pSQL($message),
            'attempts' => ['type' => 'sql', 'value' => 'GREATEST(`attempts` - 1, 0)'],
        ];

        return \Db::getInstance()->update(
            'mailchimppro_jobs',
            $data,
            'id_job = ' . (int) $jobId
        );
    }

    /**
     * Detects the race-condition failure: an order/cart sync rejected because
     * a referenced product hasn't finished syncing on Mailchimp's side yet
     * (their batch endpoint is async). Used by the worker path to recognise
     * the race and defer the job rather than counting it as a real failure.
     */
    protected function isProductRaceError($response)
    {
        if (empty($response['requestLastErrors'])) {
            return false;
        }
        $errText = is_array($response['requestLastErrors'])
            ? implode(' ', array_map(function ($v) {
                return is_scalar($v) ? (string) $v : json_encode($v);
            }, $response['requestLastErrors']))
            : (string) $response['requestLastErrors'];

        return false !== stripos($errText, 'product')
            && false !== stripos($errText, 'does not exist');
    }

    protected function deleteJob($jobId)
    {
        // Define the where condition
        $where = 'id_job = ' . (int) $jobId;

        // Execute the delete query
        return \Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    protected function deleteUnsuccessfulJobs()
    {
        // Define the where condition
        $where = 'attempts >= ' . (int) $this->max_try;

        // Execute the delete query
        return \Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    public function clearChannel($channel = '%%')
    {
        $idShop = PsContext::getShopId();

        // Prepare the where condition
        $where = '`id_shop` = ' . (int) $idShop;

        // Execute the delete query
        return \Db::getInstance()->delete('mailchimppro_jobs', $where);
    }

    protected function increaseAttemptsCounter($jobId)
    {
        // Prepare the data to be updated
        $data = [
            'attempts' => ['type' => 'sql', 'value' => '`attempts` + 1'],  // SQL expression to increment attempts
        ];

        // Define the where condition
        $where = 'id_job = ' . (int) $jobId;

        // Execute the update query
        return \Db::getInstance()->update('mailchimppro_jobs', $data, $where);
    }

    protected function getAttemptsCounter($jobId)
    {
        $query = new \DbQuery();
        $query->select('`attempts`');
        $query->from('mailchimppro_jobs');
        $query->where('`id_job` = ' . (int) $jobId);

        // Execute the query and get the result
        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function pop($channel = '%%', $limit = false, $jobType = null)
    {
        $idShop = PsContext::getShopId();
        $max_try = pSQL($this->max_try);

        if (!$limit) {
            $query = new \DbQuery();
            $query->select('*');
            $query->from('mailchimppro_jobs');
            $query->where('(`status` = ' . (int) self::STATUS_AVAILABLE . ' AND `attempts` < ' . (int) \Db::getInstance()->escape(pSQL($max_try)) . ') 
			              OR (`status` = ' . (int) self::STATUS_PROCESSING . ' AND TIMESTAMPDIFF(SECOND, `locked_at`, CURRENT_TIMESTAMP) > 60)');
            $query->where('`id_shop` = ' . (int) $idShop);

            $query->where('`channel` LIKE \'' . pSQL($channel) . '\'');

            // Add job type filter if specified
            if (null !== $jobType) {
                $query->where('`type` = \'' . pSQL($jobType) . '\'');
            }

            $query->orderBy('`priority` ASC, `id_entity` ASC');

            // Execute the query and fetch the result
            $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        } else {
            $query = new \DbQuery();
            $query->select('*');
            $query->from('mailchimppro_jobs');
            $query->where('(`status` = ' . (int) self::STATUS_AVAILABLE . ' AND `attempts` < ' . (int) \Db::getInstance()->escape(pSQL($max_try)) . ') 
			              OR (`status` = ' . (int) self::STATUS_PROCESSING . ' AND TIMESTAMPDIFF(SECOND, `locked_at`, CURRENT_TIMESTAMP) > 60)');
            $query->where('`id_shop` = ' . (int) $idShop);

            $query->where('`channel` LIKE \'' . pSQL($channel) . '\'');

            // Add job type filter if specified
            if (null !== $jobType) {
                $query->where('`type` = \'' . pSQL($jobType) . '\'');
            }

            $query->orderBy('`priority` ASC, `id_entity` ASC');
            $query->limit((int) \Db::getInstance()->escape(pSQL($limit)));

            // Execute the query and fetch all results
            $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        }

        if (empty($result)) {
            return null;
        }

        if (!$limit) {
            $this->increaseAttemptsCounter($result['id_job']);
        } else {
            foreach ($result as $queue) {
                $this->increaseAttemptsCounter($queue['id_job']);
            }
        }

        return $result;
    }

    /**
     * @throws InvalidJobException
     */
    public function runJob($channel = '%%')
    {
        if (!\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
            return [
                'skipped' => true,
                'reason' => 'not-connected',
                'flag' => '',
                'message' => 'Sync skipped — module not connected. Click Initialize connection in BO to set up.',
            ];
        }

        // Circuit-breaker: same rationale as runCronjob's run-start check.
        // Queue Work UI polls this endpoint every ~400ms; without this gate,
        // a broken-state install would keep firing doomed API calls until the
        // merchant stops the worker. Returning a `skipped` marker also gives
        // the JS worker something to react to (e.g. stop polling).
        if (\PrestaChamps\MailchimpPro\HealthState::isBreakerActive()) {
            $flag = \PrestaChamps\MailchimpPro\HealthState::getFlag();

            return [
                'skipped' => true,
                'reason' => 'health-breaker',
                'flag' => $flag,
                'message' => "Sync skipped — connection-health flag active ({$flag}). Fix the connection in BO to resume.",
            ];
        }

        $errorMessage = '';
        $requestSuccess = true;
        $jobType = '';

        // Product batch path — mirrors runCronjob's product-batching behaviour so the
        // admin Queue UI gets the same throughput as cron. Pops up to 50 product jobs
        // in one go and submits them as a single Mailchimp batch via PUT (create-or-
        // update), which avoids the per-product 404s that PATCH causes when a product
        // hasn't been created on Mailchimp yet.
        $productJobs = $this->pop($channel, 50, ProductSyncJob::JOB_TYPE);
        if (!empty($productJobs)) {
            foreach ($productJobs as $productJob) {
                $this->reserveJob($productJob['id_job']);
            }

            $shopId = !empty($productJobs[0]['id_shop'])
                ? (int) $productJobs[0]['id_shop']
                : (int) PsContext::getShopId();

            try {
                $batchResponse = ProductSyncJob::handleBatch($productJobs, $shopId);
            } catch (\Exception $exception) {
                $batchResponse = [
                    'requestSuccess' => false,
                    'requestLastErrors' => $exception->getMessage(),
                ];
            }

            $batchSuccess = isset($batchResponse['requestSuccess']) && $batchResponse['requestSuccess'];
            $batchError = '';
            if (!$batchSuccess && isset($batchResponse['requestLastErrors'])) {
                $batchError = is_array($batchResponse['requestLastErrors'])
                    ? implode('; ', array_filter(array_map(function ($v) { return is_scalar($v) ? (string) $v : json_encode($v); }, $batchResponse['requestLastErrors'])))
                    : (string) $batchResponse['requestLastErrors'];
            }

            if ($batchSuccess) {
                foreach ($productJobs as $productJob) {
                    $this->deleteJob($productJob['id_job']);
                }
            } else {
                foreach ($productJobs as $productJob) {
                    $this->unReserveJob($productJob['id_job'], $batchError ?: 'Batch product sync failed');
                }
            }

            $this->deleteUnsuccessfulJobs();

            return [
                'success' => $batchSuccess,
                'message' => $batchSuccess ? '' : ($batchError ?: 'Batch product sync failed'),
                'type' => ProductSyncJob::class,
                'data' => null,
                'jobType' => ProductSyncJob::JOB_TYPE,
                'processedCount' => $batchSuccess ? count($productJobs) : 0,
            ];
        }

        $jobData = $this->pop($channel);

        if (empty($jobData)) {
            $this->deleteUnsuccessfulJobs();

            return [
                'success' => true,
                'message' => 'No job to sync!',
                'type' => null,
                'data' => null,
            ];
        }

        try {
            $job = $this->getJobObject($jobData['type'], $jobData['body']);

            $this->reserveJob($jobData['id_job']);

            if (!$job instanceof JobInterface) {
                $resp = 'Deserialized object is not an instance of ' . JobInterface::class;
                $this->unReserveJob($jobData['id_job'], $resp);

                throw new InvalidJobException($resp);
            }

            $response = $job->execute($jobData['id_shop']);

            // Reactive race deferral: order/cart sync rejected because Mailchimp
            // hasn't finished processing a referenced product (their batch endpoint
            // is async). Defer the job back to AVAILABLE without burning an attempt.
            // Worker keeps polling — on the next pop, the products will exist and
            // the order/cart will sync cleanly.
            if (isset($response['requestSuccess']) && !$response['requestSuccess']
                && ($job instanceof OrderSyncJob || $job instanceof CartSyncJob)
                && $this->isProductRaceError($response)) {
                $this->deferJobForRetry($jobData['id_job']);

                return [
                    'success' => false,
                    'deferred' => true,
                    'message' => 'Deferred — product batch still in progress',
                    'type' => get_class($job),
                    'data' => $jobData,
                    'jobType' => $job->getJobType(),
                    'processedCount' => 0,
                ];
            }

            if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                $this->deleteJob($jobData['id_job']);
                $requestSuccess = true;
                $jobType = $job->getJobType();
            } else {
                $requestSuccess = false;
                if (isset($response['requestLastErrors'])) {
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(';', array_values($response['requestLastErrors']));
                    } else {
                        $errorMessage = $response['requestLastErrors'];
                    }
                    if ($attempsCount = $this->getAttemptsCounter($jobData['id_job'])) {
                        $errorMessage .= ' (' . $attempsCount . '. attemp(s))';
                    }
                    $this->unReserveJob($jobData['id_job'], $errorMessage);
                } else {
                    $this->unReserveJob($jobData['id_job']);
                }
            }
            $this->deleteUnsuccessfulJobs();
        } catch (\Exception $exception) {
            $this->unReserveJob($jobData['id_job'], $exception->getMessage());

            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'type' => null,
                'data' => null,
            ];
        }

        return [
            'success' => $requestSuccess,
            'message' => $errorMessage,
            'type' => get_class($job),
            'data' => $jobData,
            'jobType' => $jobType,
        ];
    }

    public function addSpecificPriceProductJobs()
    {
        $current_date = new \DateTime('now', new \DateTimeZone(@date_default_timezone_get()));

        $query = new \DbQuery();
        $query->select('*');
        $query->from('mailchimppro_specific_price');
        $query->where('(`needToRun` = 2 AND `start_date` < \'' . \Db::getInstance()->escape(pSQL($current_date->format('Y-m-d H:i:s'))) . '\') 
		              OR (`needToRun` = 1 AND `end_date` < \'' . \Db::getInstance()->escape(pSQL($current_date->format('Y-m-d H:i:s'))) . '\')');

        // Execute the query and fetch the results
        $specific_prices = \Db::getInstance()->executeS($query);

        if (count($specific_prices) > 0) {
            foreach ($specific_prices as $specific_price) {
                $queue = new Queue();
                $job = new ProductSyncJob();
                $job->productId = $specific_price['id_product'];
                $queue->push($job, 'setup-wizard', $specific_price['id_shop']);

                if (1 == $specific_price['needToRun']) {
                    // Define the where condition
                    $where = 'id_specific_price = ' . (int) $specific_price['id_specific_price'];

                    // Execute the delete query
                    $delete = \Db::getInstance()->delete('mailchimppro_specific_price', $where);
                }

                if (2 == $specific_price['needToRun']) {
                    // Prepare the data to be updated
                    $data = [
                        'needToRun' => 1,
                    ];

                    // Define the where condition
                    $where = 'id_specific_price = ' . (int) $specific_price['id_specific_price'];

                    // Execute the update query
                    $update = \Db::getInstance()->update('mailchimppro_specific_price', $data, $where);
                }
            }
        }
    }

    public function checkAndDeleteExpiredPromoMergeFields()
    {
        $campaigns = Campaign::getExpiredSyncedCampaigns();

        foreach ($campaigns as $campaign) {
            $campaign = new Campaign($campaign['id']);

            if ($campaign->id_merge_field_mc > 0) {
                /** @var \Mailchimppro $module */
                $module = \Module::getInstanceByName('mailchimppro');
                $promoCommand = new PromoMergeTagSyncCommand(
                    PsContext::getContext(),
                    $module->getApiClient(),
                    $campaign,
                    [],
                    PsContext::getShopId()
                );

                $delete_response = $promoCommand->deleteMergeFieldFromMailchimp();

                $campaign->id_merge_field_mc = null;
                $campaign->tag_merge_field_mc = null;

                $campaign->update();
            }
        }
    }

    /**
     * @param string $channel
     * @param bool $forceJsonOutput when true, early-exit branches emit JSON
     *                              regardless of Tools::isSubmit('ajax'). BO controller callers
     *                              (ajaxProcess*) pass true — axios expects JSON. Direct cron URL
     *                              callers (wget from crontab) leave the default false — the token
     *                              check + skip messages come back as plain text.
     */
    public function runCronjob($channel = '%%', $forceJsonOutput = false)
    {
        \Configuration::updateValue(\MailchimpProConfig::LAST_CRONJOB, date('Y-m-d H:i:s'), true);
        \Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', true, true);

        $startTime = new \DateTime();
        $wantJson = $forceJsonOutput || \Tools::isSubmit('ajax');

        // getValue returns whatever the query string held, so ?secure[] arrives
        // as an array and trim() would fatal on it.
        $submitted = \Tools::getValue('secure');
        $token = is_string($submitted) ? trim($submitted) : '';
        $expected = (string) \Configuration::get(\MailchimpProConfig::CRONJOB_SECURE_TOKEN);

        if (!$token || !\Validate::isCleanHtml($token) || !hash_equals($expected, $token)) {
            if ($wantJson) {
                exit(json_encode([
                    'errors' => 'Access denied',
                    'result' => '',
                ]));
            }
            exit('Access denied');
        }

        // Readiness gate — skip when the module isn't connected.
        if (!\Configuration::get(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
            $this->writeCronjobLog(sprintf(
                "\n%s\n  SKIPPED — module not connected (no store synced). Click Initialize connection in BO to set up.\n",
                date('Y-m-d H:i:s')
            ));
            \Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', false, true);
            if ($wantJson) {
                exit(json_encode([
                    'result' => 'Cronjob skipped — module not connected. Click Initialize connection in BO to set up.',
                ]));
            }
            exit('Cronjob skipped — module not connected');
        }

        // Circuit-breaker: if the connection-health flag is in a state where every
        // API call would fail (STATE_*_MISSING / STATE_ACCOUNT_INVALID), abort this
        // cron tick instead of hammering Mailchimp with the queue's ~35 doomed jobs.
        // STATE_MAILCHIMP_DOWN is deliberately NOT in the breaker set — that's transient,
        // we keep firing so we detect recovery on the next successful call.
        if (\PrestaChamps\MailchimpPro\HealthState::isBreakerActive()) {
            $flag = \PrestaChamps\MailchimpPro\HealthState::getFlag();
            $this->writeCronjobLog(sprintf(
                "\n%s\n  SKIPPED — health flag: %s\n  Merchant must run recovery from BO before sync resumes.\n",
                date('Y-m-d H:i:s'),
                $flag
            ));
            \Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', false, true);

            if ($wantJson) {
                exit(json_encode([
                    'result' => "Cronjob skipped — connection-health flag active ({$flag}). Fix the connection in BO to resume sync.",
                ]));
            }
            exit("Cronjob skipped — connection-health flag active ({$flag})");
        }

        $this->addSpecificPriceProductJobs();

        $this->checkAndDeleteExpiredPromoMergeFields();

        $count = 0;
        $countByType = [];
        $deferredOrders = 0;
        $deferredCarts = 0;
        $errors = [];

        $max_try = $this->max_try ? $this->max_try : 2;
        $max_jobs = $this->max_jobs ? $this->max_jobs : 10;
        $status = self::STATUS_AVAILABLE;

        // //////////////////////////////////////////////////////////////
        // Product batch processing
        // //////////////////////////////////////////////////////////////

        $max_batch_jobs = 50;
        $start_batch = new \DateTime();

        $channel = 'setup-wizard'; // for product batch jobs
        $totalProductJobs = $this->countAvailableProductJobsForBatch($channel);
        $maxProductJobsPerMinute = 50 * $max_batch_jobs; // 2500 jobs max

        $processedProductJobs = 0;
        $batchNum = 0; // Fixed: Declare outside the loop
        $shouldProcessNonProductJobs = ($totalProductJobs < $maxProductJobsPerMinute);

        // Determine how many product jobs to process
        $targetProductJobs = $shouldProcessNonProductJobs ? $totalProductJobs : $maxProductJobsPerMinute;
        $batchesNeeded = ceil($targetProductJobs / 50);

        // Single batch processing loop
        for ($batchNum = 0; $batchNum < $batchesNeeded; ++$batchNum) {
            $remainingJobs = $targetProductJobs - $processedProductJobs;
            $batchSize = min(50, $remainingJobs);

            if ($productJobs = $this->pop($channel, $batchSize, ProductSyncJob::JOB_TYPE)) {
                // Reserve jobs
                foreach ($productJobs as $job) {
                    $this->reserveJob($job['id_job']);
                }

                // Process batch
                $response = ProductSyncJob::handleBatch($productJobs, PsContext::getShopId());

                if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                    foreach ($productJobs as $job) {
                        $this->deleteJob($job['id_job']);
                        ++$processedProductJobs;
                    }
                } else {
                    $errors[] = "Batch $batchNum product sync failed.";
                    foreach ($productJobs as $job) {
                        $this->unReserveJob($job['id_job'], 'Batch processing failed');
                    }
                }
            } else {
                break; // No more product jobs available
            }
        }

        $end_batch = new \DateTime();
        $batchProcessingTime = $start_batch->diff($end_batch)->format('%H:%i:%s');

        // //////////////////////////////////////////////////////////////
        // Process non-product jobs if needed and we have capacity
        // //////////////////////////////////////////////////////////////

        // Fixed: Use actual processed jobs count instead of batch number
        if ($shouldProcessNonProductJobs && $batchesNeeded < $max_jobs) {
            // Calculate how many non-product jobs we can process
            $remainingJobSlots = $max_jobs - $batchesNeeded;

            if ($remainingJobSlots > 0) {
                $channel = '%%';
                if ($queues = $this->pop($channel, $remainingJobSlots)) {
                    // reserve all the selected jobs
                    foreach ($queues as $queue) {
                        $this->reserveJob($queue['id_job']);
                    }

                    foreach ($queues as $queue) {
                        // Mid-run breaker: the run-start check at the top only sees
                        // the flag AS-OF run-start. Non-product (regular-mode) jobs
                        // route each response through the API-client classifier, so
                        // the first-failed job's 400/404 can flip the flag DURING
                        // this loop. Without this gate, remaining jobs (customers,
                        // orders, carts, newsletters) would keep firing doomed API
                        // calls in the same tick before the next tick's run-start
                        // check finally halts things. Unreserve puts jobs back to
                        // AVAILABLE so next tick picks them up cleanly. (No matching
                        // gate in the product-batch loop above: batch submissions
                        // return 200 to POST /batches — the classifier never sees
                        // the individual op errors, so the flag can't flip there.)
                        if ($this->breakerTrippedMidRun()) {
                            $this->unReserveJob($queue['id_job'], 'Aborted mid-run — connection-health breaker tripped');
                            continue;
                        }

                        $errorMessage = '';
                        $requestSuccess = true;

                        $job = $this->getJobObject($queue['type'], $queue['body']);

                        if (!$job instanceof JobInterface) {
                            $resp = 'Deserialized object is not an instance of ' . JobInterface::class;
                            $this->unReserveJob($queue['id_job'], $resp);

                            continue; // Fixed: Add missing continue statement
                        }

                        // Proactive race deferral: orders/carts depend on products that we
                        // batched earlier in this same cron run. Mailchimp's batch endpoint
                        // is async, so those products are still being created on their side.
                        // Defer to the next cron run where they will exist.
                        if ($processedProductJobs > 0
                            && ($job instanceof OrderSyncJob
                                || $job instanceof CartSyncJob)) {
                            $this->deferJobForRetry($queue['id_job']);
                            if ($job instanceof OrderSyncJob) {
                                ++$deferredOrders;
                            } else {
                                ++$deferredCarts;
                            }

                            continue;
                        }

                        $response = $job->execute($queue['id_shop']); // parameter is store ID from PS multi store configuration

                        if (isset($response['requestSuccess']) && true == $response['requestSuccess']) {
                            ++$count;
                            $jobTypeKey = $job->getJobType();
                            if (!isset($countByType[$jobTypeKey])) {
                                $countByType[$jobTypeKey] = 0;
                            }
                            ++$countByType[$jobTypeKey];
                            $this->deleteJob($queue['id_job']);
                        } else {
                            // Reactive race deferral: cross-cron edge case where a product
                            // batch from a *previous* cron run hasn't finished on Mailchimp's
                            // side yet, so this order/cart's product reference 404s. Defer
                            // back to AVAILABLE without burning a retry attempt.
                            if (($job instanceof OrderSyncJob
                                    || $job instanceof CartSyncJob)
                                && $this->isProductRaceError($response)) {
                                $this->deferJobForRetry($queue['id_job']);
                                if ($job instanceof OrderSyncJob) {
                                    ++$deferredOrders;
                                } else {
                                    ++$deferredCarts;
                                }

                                continue;
                            }

                            $requestSuccess = false;
                            if (isset($response['requestLastErrors'])) {
                                if (is_array($response['requestLastErrors'])) {
                                    $errorMessage = implode(';', array_values($response['requestLastErrors']));
                                } else {
                                    $errorMessage = $response['requestLastErrors'];
                                }
                                if ($attempsCount = $this->getAttemptsCounter($queue['id_job'])) {
                                    $errorMessage .= ' (' . $attempsCount . '. attempt(s))';
                                }

                                if (!empty($response['requestLastResponse']['body'])) {
                                    $responseBody = json_decode($response['requestLastResponse']['body'], true);
                                    if (!empty($responseBody['errors']) && is_array($responseBody['errors'])) {
                                        $additionalErrors = [];
                                        foreach ($responseBody['errors'] as $bodyErrors) {
                                            if (!empty($bodyErrors['message'])) {
                                                $additionalErrors[] = $bodyErrors['message'];
                                            }
                                        }
                                        if (!empty($additionalErrors)) {
                                            $errorMessage .= "\n**********\n • " . implode("\n • ", $additionalErrors) . "\n**********\n";
                                        }
                                    }
                                }

                                $this->unReserveJob($queue['id_job'], $errorMessage);
                                $errors[] = '− Job type: ' . $job->getJobType() . ' | Id: ' . $queue['id_entity'] . ' | Message: ' . $errorMessage;
                            } else {
                                $this->unReserveJob($queue['id_job']);
                            }
                        }
                    }
                }
            }
        }

        $this->deleteUnsuccessfulJobs();
        $totalJobsProcessed = 0;
        if ((int) \Configuration::get(\MailchimpProConfig::LOG_CRONJOB)) {
            $log = "================================================================\n";
            $log .= date(PsContext::getLanguage()->date_format_full);

            // Track total jobs processed
            $totalJobsProcessed = $processedProductJobs + $count;

            // Build the main message
            if ($totalJobsProcessed > 0) {
                $log .= '  ' . sprintf('There were %s job(s) successfully synced to the MailChimp!', $totalJobsProcessed);

                // Add breakdown details — readable label per job type
                $jobTypeLabels = [
                    'product' => 'product',
                    'customer' => 'customer',
                    'cartRule' => 'cart rule',
                    'order' => 'order',
                    'cart' => 'cart',
                    'newsletterSubscriber' => 'newsletter subscriber',
                    'mergeTagPromoCode' => 'merge tag promo code',
                ];
                $breakdown = [];
                if ($processedProductJobs > 0) {
                    $breakdown[] = sprintf('%s product sync job(s)', $processedProductJobs);
                }
                foreach ($countByType as $typeKey => $typeCount) {
                    $label = isset($jobTypeLabels[$typeKey]) ? $jobTypeLabels[$typeKey] : $typeKey;
                    $breakdown[] = sprintf('%s %s sync job(s)', $typeCount, $label);
                }
                if (count($breakdown) > 0) {
                    $log .= "\n  Breakdown: " . implode(', ', $breakdown);
                }

                // Add batch processing details if product jobs were processed
                if ($processedProductJobs > 0) {
                    $log .= "\n  Product batch processing: {$batchesNeeded} batch(es) in {$batchProcessingTime}";
                }

                // Note jobs that were deferred to the next cron run because they depend
                // on products that were just batched (Mailchimp's batch endpoint is async,
                // so the products may not exist on their side yet at this exact moment).
                if ($deferredOrders > 0 || $deferredCarts > 0) {
                    $deferredParts = [];
                    if ($deferredOrders > 0) {
                        $deferredParts[] = sprintf('%s order(s)', $deferredOrders);
                    }
                    if ($deferredCarts > 0) {
                        $deferredParts[] = sprintf('%s cart(s)', $deferredCarts);
                    }
                    $log .= "\n  Deferred to next cron run (waiting for product batches to finish on Mailchimp): " . implode(', ', $deferredParts);
                }
            } else {
                $log .= '  No job has been synced';
            }

            if ($errors) {
                $log .= "\n";
                $log .= 'Errors: ';
                foreach ($errors as $error) {
                    $log .= " \n " . $error;
                }
            }
            $log .= "\n";

            // Define the destination directory
            $dest = _PS_MODULE_DIR_ . 'mailchimppro/logs';

            // Resolve the destination path
            $resolvedDest = realpath($dest) ?: $dest; // Use the original path if realpath fails

            // Ensure the directory exists, create it if it doesn't
            if (!is_dir($resolvedDest)) {
                if (!mkdir($resolvedDest, 0755, true)) {
                    // Handle error if directory creation fails
                    throw new \Exception('Failed to create log directory.');
                }

                $resolvedDest = realpath($resolvedDest) ?: $dest; // Use the original path if realpath fails
            }

            // Ensure the resolved path is within the intended directory
            if (0 === strpos(realpath($resolvedDest), realpath(_PS_MODULE_DIR_ . 'mailchimppro'))) {
                if ($count || $processedProductJobs || $errors) {
                    // Safely append the log to the file
                    $logFilePath = $resolvedDest . '/cronjob.log';
                    if (false === file_put_contents($logFilePath, $log . PHP_EOL, FILE_APPEND)) {
                        // Handle error if writing to the log file fails
                        throw new \Exception('Failed to write to log file.');
                    }
                }
            } else {
                // Handle error if the path is not valid
                throw new \Exception('Invalid log directory path.');
            }
        }

        \Configuration::updateGlobalValue('MAILCHIMPPRO_IS_CRONJOB_RUNNING', false, true);

        $endTime = new \DateTime();
        \Configuration::updateValue(\MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME, $startTime->diff($endTime)->format('%Y-%m-%d %H:%i:%s'), true);

        $jsonArr = [
            'result' => 'Cronjob ran successfully: ' . ($totalJobsProcessed <= 0 ? 'No job has been synced!' : sprintf('%s job(s) was synced to the MailChimp!', $totalJobsProcessed)),
        ];

        exit(json_encode($jsonArr));
    }

    /**
     * Append a line to logs/cronjob.log, path-safety-checked the same way as
     * the main log write in runCronjob(). Swallows any filesystem error — the
     * caller (circuit-breaker) needs to abort the cron whether the log line
     * lands or not.
     */
    private function writeCronjobLog($line)
    {
        $dest = _PS_MODULE_DIR_ . 'mailchimppro/logs';
        if (!is_dir($dest) && !mkdir($dest, 0755, true)) {
            return;
        }
        $resolvedDest = realpath($dest) ?: $dest;
        if (0 !== strpos((string) realpath($resolvedDest), (string) realpath(_PS_MODULE_DIR_ . 'mailchimppro'))) {
            return;
        }
        @file_put_contents($resolvedDest . '/cronjob.log', $line, FILE_APPEND);
    }

    /**
     * Keep the indirection: called inline, this is textually identical to the
     * run-start breaker check, and static analysis then reports it as always
     * false — it can't see the flag flip mid-loop.
     *
     * @return bool
     */
    private function breakerTrippedMidRun()
    {
        return \PrestaChamps\MailchimpPro\HealthState::isBreakerActive();
    }

    public function getJobObject($type, $body)
    {
        $job = null;

        switch ($type) {
            case CartRuleSyncJob::JOB_TYPE:
                $job = new CartRuleSyncJob($body);

                break;

            case CartSyncJob::JOB_TYPE:
                $job = new CartSyncJob($body);

                break;

            case CustomerSyncJob::JOB_TYPE:
                $job = new CustomerSyncJob($body);

                break;

            case NewsletterSubscriberSyncJob::JOB_TYPE:
                $job = new NewsletterSubscriberSyncJob($body);

                break;

            case OrderSyncJob::JOB_TYPE:
                $job = new OrderSyncJob($body);

                break;

            case ProductSyncJob::JOB_TYPE:
                $job = new ProductSyncJob($body);

                break;

            case MergeTagPromoCodeSyncJob::JOB_TYPE:
                $job = new MergeTagPromoCodeSyncJob($body);

                break;
        }

        return $job;
    }
}
