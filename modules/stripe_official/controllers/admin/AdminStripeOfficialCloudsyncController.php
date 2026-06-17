<?php

/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use StripeOfficial\Classes\services\PrestashopAdminTokenService;
use StripeOfficial\Controllers\admin\cloudsync\CloudSyncTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminStripeOfficialCloudsyncController extends ModuleAdminController
{
    use CloudSyncTrait;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function postProcess()
    {
        $action = Tools::getValue('action');
        $data = Tools::getAllValues();
        $token = $data['token'] ?? null;
        if (!PrestashopAdminTokenService::verifyToken('AdminStripeOfficialCloudsync', $token)) {
            return new JsonResponse(['error' => 'Invalid token'], 403);
        }
        $response = null;
        switch ($action) {
            case 'skipAction':
                $response = $this->skipCloudsync($data);
                break;
            case 'getAction':
                $response = $this->getCloudsync($data);
                break;
            case 'clearCacheAction':
                $this->clearCache();
                break;
        }
        if ($response) {
            echo json_encode($response->getContent());
            exit;
        }

        return parent::postProcess();
    }
}
