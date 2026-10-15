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

interface JobInterface
{
    /**
     * @param int|null $idStore shop ID to scope the job to (multi-store)
     *
     * @return array|false Response shape: ['requestSuccess' => bool, 'requestLastErrors' => array|null, ...]
     */
    public function execute($idStore = null);

    public function getJobType();

    public function getJobId();

    public function convertToArrayJson();
}
