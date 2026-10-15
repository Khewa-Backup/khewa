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

declare(strict_types=1);

// needed for next validation required by prestashop validator
define('_PS_VERSION_', '0');

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'vendor/autoload.php';

return [
    'prefix' => 'StripePsModule',
    'finders' => [
        // Include only symfony/lock from vendor
        Isolated\Symfony\Component\Finder\Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/lock'),
    ],
    'exclude-namespaces' => ['/^(?!Symfony\\\\Component\\\\Lock(?:\\\\|$)).+/'],
    'exclude-files' => [],
    'exclude-classes' => [],
];
