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

namespace StripeOfficial\Classes\dtos;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartDiscountDetailsDto
{
    /** @var bool */
    public $free_shipping;

    /** @var float|int */
    public $percentage_discount;

    /** @var float|int */
    public $fixed_discount;

    /** @var float|int */
    public $total_discount;

    public function __construct()
    {
        $this->free_shipping = false;
        $this->percentage_discount = 0;
        $this->fixed_discount = 0;
        $this->total_discount = 0;
    }

    public function toArray()
    {
        return [
            'free_shipping' => $this->free_shipping,
            'percentage_discount' => $this->percentage_discount,
            'fixed_discount' => $this->fixed_discount,
            'total_discount' => $this->total_discount,
        ];
    }
}
