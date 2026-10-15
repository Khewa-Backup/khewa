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

namespace PrestaChamps\MailchimpPro\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Centralized access to PrestaShop's runtime context object.
 *
 * BACKWARDS COMPATIBILITY NOTE
 * ============================
 * This module supports PS 1.7.0.6 -> 9.x. PS 9.0 introduced Symfony "split"
 * context services (ShopContext, EmployeeContext, CurrencyContext,
 * LanguageContext, CountryContext, ApiClientContext, LegacyControllerContext)
 * and now strongly discourages direct legacy context access in favor of
 * those DI services. The new services DON'T EXIST on PS 1.7 / 8, so this
 * module cannot use them directly while supporting legacy versions.
 *
 * Every call site in this module goes through these helpers instead of
 * touching the legacy context class directly. When PS 1.7 / 8 support is
 * eventually dropped, swap each helper's internal implementation to the
 * new DI services -- no call sites need to change.
 *
 * Validator note: the PrestaShop Addons validator's "Use of class" Errors
 * finding is REDUCED but not eliminated by this helper -- the ctx() helper
 * still touches the legacy class internally (one usage in this file vs ~59
 * across 22 files previously). Full elimination requires dropping legacy
 * PS support.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/core-updates/9.0/#symfony-context-refactorization
 */
final class PsContext
{
    /**
     * Single choke point for legacy Context access. Every other helper in
     * this class goes through here, so swapping to PS 9's Symfony DI
     * (when legacy support drops) is a one-line change. Minimizing this
     * call site also keeps the "Use of class Context" validator finding
     * count down to one.
     */
    private static function ctx()
    {
        return \Context::getContext();
    }

    /**
     * Get the current Context. Use only when the whole Context object must
     * be passed to a downstream API; prefer the specific helpers below.
     */
    public static function getContext()
    {
        return self::ctx();
    }

    /**
     * Get the current shop ID. (PS guarantees shop is set on any request.)
     */
    public static function getShopId()
    {
        return (int) self::getShop()->id;
    }

    /**
     * Get the current Shop object.
     */
    public static function getShop()
    {
        return self::ctx()->shop;
    }

    /**
     * Get the current language ID. (PS guarantees language is set on any
     * request.)
     */
    public static function getLanguageId()
    {
        return (int) self::getLanguage()->id;
    }

    /**
     * Get the current Language object (for accessing properties like
     * date_format_full, iso_code, etc).
     */
    public static function getLanguage()
    {
        return self::ctx()->language;
    }

    /**
     * Get the current employee ID (BO admin) or null in FO / unauth context.
     * Prefer this over getEmployee()->id — avoids the PHP 8+ "Attempt to
     * read property on null" fatal when the employee isn't set (e.g. in
     * tests, FO requests, or before BO init completes).
     */
    public static function getEmployeeId()
    {
        $emp = self::getEmployee();

        return $emp ? (int) $emp->id : null;
    }

    /**
     * Get the current employee (BO admin user) or null in FO context.
     * Defensive isset() guard means tests without an employee stub don't
     * crash here — they get null. Callers that need only the ID should
     * use getEmployeeId() to avoid null-property-access fatals on PHP 8+.
     */
    public static function getEmployee()
    {
        $ctx = self::ctx();

        return isset($ctx->employee) && $ctx->employee instanceof \Employee ? $ctx->employee : null;
    }

    /**
     * Get the current Link helper or null when unavailable.
     */
    public static function getLink()
    {
        $ctx = self::ctx();

        return isset($ctx->link) ? $ctx->link : null;
    }

    /**
     * Get the computing precision (PS 1.7.7+) or fall back to 6 on older PS.
     * This is used by Cart calculations for rounding.
     */
    public static function getComputingPrecision()
    {
        $ctx = self::ctx();

        return method_exists($ctx, 'getComputingPrecision') ? $ctx->getComputingPrecision() : 6;
    }

    /**
     * Clone the current Context — used when downstream code must mutate
     * Context-like data (e.g. virtual cart for promo-rule evaluation)
     * without affecting the page's real Context.
     */
    public static function cloneContext()
    {
        return self::ctx()->cloneContext();
    }
}
