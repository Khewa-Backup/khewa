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

namespace PrestaChamps\Queue\Jobs;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Commands\ProductSyncCommand;
use PrestaChamps\MailchimpPro\Exceptions\MailChimpException;
use PrestaChamps\MailchimpPro\Helpers\PsContext;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;
use PrestaChamps\Queue\JobInterface;

class ProductSyncJob implements JobInterface
{
    use ShopIdTrait;

    public $productId;

    protected $syncMode;

    protected $method;

    public const JOB_TYPE = 'product';

    protected $idStore;

    /**
     * @throws \PrestaShopException
     * @throws MailChimpException
     * @throws \PrestaShopDatabaseException
     */
    public function execute($idStore = null)
    {
        if ($idStore) {
            $this->idStore = $idStore;
        } else {
            $this->idStore = PsContext::getShopId();
        }

        /** @var \Mailchimppro $module */
        $module = \Module::getInstanceByName('mailchimppro');
        $command = new ProductSyncCommand(
            PsContext::getContext(),
            $module->getApiClient($this->idStore),
            [$this->productId],
            $this->idStore
        );

        if (isset($this->syncMode) && $this->syncMode) {
            $command->setSyncMode($this->syncMode);
        } else {
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
        }

        if (isset($this->method) && $this->method) {
            $command->setMethod($this->method);
            $result = $command->execute();
        } else {
            // $command->setMethod(
            //     $command->getProductExists($this->productId)
            //     ? $command::SYNC_METHOD_PATCH
            //     : $command::SYNC_METHOD_POST
            // );

            $command->setMethod($command::SYNC_METHOD_PATCH);

            $result = $command->execute();

            if (isset($result['requestSuccess']) && false == $result['requestSuccess']) {
                $command->setMethod($command::SYNC_METHOD_POST);
                $result = $command->execute();
            }
        }

        return $result;
    }

    /**
     * Set sync mode.
     *
     * @param int $syncMode
     */
    public function setSyncMode($syncMode = ProductSyncCommand::SYNC_MODE_REGULAR)
    {
        $this->syncMode = $syncMode;
    }

    /**
     * Set sync method.
     *
     * @param string $method
     */
    public function setMethod($method = ProductSyncCommand::SYNC_METHOD_PUT)
    {
        $this->method = $method;
    }

    public function getJobType()
    {
        return self::JOB_TYPE;
    }

    public function getJobId()
    {
        return $this->productId;
    }

    public function convertToArrayJson()
    {
        $arrayProperties = [
            // "jobType" => $this->getJobType(),
            'productId' => $this->productId,
            'syncMode' => $this->syncMode,
            'method' => $this->method,
            'idStore' => is_null($this->idStore) ? PsContext::getShopId() : $this->idStore,
        ];

        return json_encode($arrayProperties);
    }

    public function __construct($arrayPropertiesJson = null)
    {
        if ($arrayPropertiesJson) {
            $arrayProperties = json_decode($arrayPropertiesJson);

            $this->productId = $arrayProperties->productId;
            $this->syncMode = $arrayProperties->syncMode;
            $this->method = $arrayProperties->method;
            $this->idStore = $arrayProperties->idStore;
        }
    }

    public static function handleBatch(array $jobs, $shopId)
    {
        // Group product IDs by the method their queue row specified so each
        // Mailchimp batch sends the CORRECT operation type. Pre-fix behaviour
        // hardcoded PUT for the whole batch, which silently converted every
        // DELETE-method job to an UPSERT on Mailchimp — merchants deactivating
        // products (or the sync-filter fix's DELETE-on-filter-fail path)
        // would see their products stay on Mailchimp instead of being removed.
        //
        // Jobs with no explicit method fall back to PUT (upsert) — same
        // default the pre-fix code used for the whole batch. Matches
        // ProductSyncJob::execute()'s null-method fallback which tries PATCH
        // first then POST; PUT achieves the equivalent result in one shot
        // via Mailchimp's upsert semantics.
        $productIdsByMethod = [];
        foreach ($jobs as $job) {
            $jobData = json_decode($job['body'], true);
            if (empty($jobData['productId'])) {
                continue;
            }
            $method = !empty($jobData['method'])
                ? (string) $jobData['method']
                : ProductSyncCommand::SYNC_METHOD_PUT;
            $productIdsByMethod[$method][] = (int) $jobData['productId'];
        }

        if (empty($productIdsByMethod)) {
            return ['requestSuccess' => true]; // Nothing to sync
        }

        /** @var \Mailchimppro $module */
        $module = \Module::getInstanceByName('mailchimppro');
        $perMethodResponses = [];
        $overallSuccess = true;
        $aggregatedErrors = [];

        foreach ($productIdsByMethod as $method => $productIds) {
            $command = new ProductSyncCommand(
                PsContext::getContext(),
                $module->getApiClient(),
                array_unique($productIds),
                $shopId
            );
            $command->setSyncMode(ProductSyncCommand::SYNC_MODE_BATCH);
            $command->setMethod($method);
            $r = $command->execute();
            $perMethodResponses[$method] = $r;

            $methodOk = isset($r['requestSuccess']) && $r['requestSuccess'];
            if (!$methodOk) {
                $overallSuccess = false;
                if (!empty($r['requestLastErrors'])) {
                    $aggregatedErrors[$method] = $r['requestLastErrors'];
                }
            }
        }

        // Return the FLAT shape the callers expect (requestSuccess/requestLastErrors
        // at the top level). Per-method detail goes under perMethodResponses for
        // any caller that wants to inspect the individual batches — the current
        // Queue::runCronjob call sites only check top-level requestSuccess.
        return [
            'requestSuccess' => $overallSuccess,
            'requestLastErrors' => $aggregatedErrors ?: null,
            'perMethodResponses' => $perMethodResponses,
        ];
    }
}
