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

use PrestaChamps\Queue\Queue;

class MailchimpproWorkerModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $queue = new Queue();
        $result = $queue->runJob();

        $this->ajaxDie([
            'message' => $result,
            'numberOfJobsAvailable' => $queue->getNumberOfAvailableJobs(),
            'numberOfJobsInFlight' => $queue->getNumberOfJobsInFlight(),
        ]);
    }

    /**
     * @param mixed $value
     * @param string|null $controller
     * @param string|null $method
     */
    public function ajaxDie($value = null, $controller = null, $method = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }
        if ((bool) version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            exit;
        }
        // Legacy fallback for PS 1.7.0.6 → 1.7.4.x (pre-ajaxRender). Controller::ajaxDie
        // was removed in PS 9.0 — dynamic call hides it from PHPStan's static surface.
        call_user_func(['parent', 'ajaxDie'], $value, $controller, $method);
    }
}
