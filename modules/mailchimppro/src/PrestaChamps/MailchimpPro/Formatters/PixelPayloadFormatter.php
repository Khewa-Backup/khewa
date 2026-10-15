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

namespace PrestaChamps\MailchimpPro\Formatters;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Pure-function payload shaping for Mailchimp Pixel SDK events. Extracted
 * out of mailchimppro.php so the data-normalization branches can be exercised
 * in isolation by tests/Unit/Formatters/PixelPayloadFormatterTest.php.
 *
 * The pixel SDK shape (camelCase) is intentionally distinct from the REST
 * ecommerce-API shape (snake_case) the existing CartFormatter / ProductFormatter
 * classes produce. The underlying VALUES match so Mailchimp can correlate
 * pixel events with the data its server-to-server sync also receives.
 */
class PixelPayloadFormatter
{
    /**
     * Shape a Pixel-SDK product payload from a PS-presented product row
     * (array on PS 1.7, ProductLazyArray on 9.1 — both array-accessible).
     *
     * Source shapes the input can carry:
     *   - Presenter output (displayProductAdditionalInfo, getTemplateVarProduct):
     *     `price_amount` (numeric, with-tax — preferred), `cover.bySize.large_default.url`
     *   - Cart::getProducts() raw rows: `price_wt` / `price_tax_exc` / `price` (numeric),
     *     `id_image` + `link_rewrite` (used to construct imageUrl via Link::getImageLink)
     *   - Some PS 1.7 paths: `price` is a formatted string like "€12.99"
     *
     * @param array|\ArrayAccess $product
     * @param string $currencyIsoCode ISO code of the active currency (caller resolves)
     * @param object|null $link Context Link instance for imageUrl reconstruction
     *                          from raw cart-row fields (id_image + link_rewrite);
     *                          pass null to skip that fallback
     * @param int $idLang Active language id (used to pick the right entry
     *                    out of a multi-language link_rewrite array)
     *
     * @return array 10-field pixel-SDK product payload
     */
    public static function fromArray($product, $currencyIsoCode = '', $link = null, $idLang = 0)
    {
        $variantId = isset($product['id_product_attribute']) ? (int) $product['id_product_attribute'] : 0;
        $productId = isset($product['id_product']) ? (int) $product['id_product']
                   : (isset($product['id']) ? (int) $product['id'] : 0);

        if ($productId <= 0) {
            return [
                'id' => '', 'productId' => '', 'title' => '', 'price' => 0.0,
                'currency' => '', 'sku' => '', 'productUrl' => '',
                'imageUrl' => '', 'vendor' => '', 'categories' => [],
            ];
        }

        $resolvedId = $variantId > 0 ? $variantId : $productId;

        $price = self::resolvePrice($product);
        $imageUrl = self::resolveImageUrl($product, $link, $idLang);
        $vendor = isset($product['manufacturer_name']) ? (string) $product['manufacturer_name'] : '';
        $categories = self::resolveCategories($product);
        $productUrl = self::resolveProductUrl($product);

        return [
            'id' => (string) $resolvedId,
            'productId' => (string) $productId,
            'title' => isset($product['name']) ? (string) $product['name'] : '',
            'price' => $price,
            'currency' => (string) $currencyIsoCode,
            'sku' => isset($product['reference']) ? (string) $product['reference'] : '',
            'productUrl' => $productUrl,
            'imageUrl' => $imageUrl,
            'vendor' => $vendor,
            'categories' => $categories,
        ];
    }

    /**
     * Price-chain fallback. With-tax fields preferred over tax-excluded; numeric
     * over formatted string; explicit 0 over missing.
     *
     * @param array|\ArrayAccess $product
     *
     * @return float
     */
    public static function resolvePrice($product)
    {
        if (isset($product['price_amount']) && is_numeric($product['price_amount'])) {
            return (float) $product['price_amount'];
        }
        if (isset($product['price_wt']) && is_numeric($product['price_wt'])) {
            return (float) $product['price_wt'];
        }
        if (isset($product['price_tax_exc']) && is_numeric($product['price_tax_exc'])) {
            return (float) $product['price_tax_exc'];
        }
        if (isset($product['price'])) {
            if (is_numeric($product['price'])) {
                return (float) $product['price'];
            }

            // formatted string like "€12.99" — strip non-numeric
            return (float) preg_replace('/[^0-9.\-]/', '', (string) $product['price']);
        }

        return 0.0;
    }

    /**
     * Image-URL resolution. Presenter shape (cover.large.url / cover.bySize.large_default.url /
     * cover.url) tried first, then construct from raw cart-row id_image + link_rewrite via
     * Link::getImageLink when a Link instance is provided.
     *
     * @param array|\ArrayAccess $product
     * @param object|null $link
     * @param int $idLang
     *
     * @return string
     */
    public static function resolveImageUrl($product, $link, $idLang)
    {
        if (isset($product['cover']) && is_array($product['cover'])) {
            $cover = $product['cover'];
            if (isset($cover['large']['url'])) {
                return (string) $cover['large']['url'];
            }
            if (isset($cover['bySize']['large_default']['url'])) {
                return (string) $cover['bySize']['large_default']['url'];
            }
            if (isset($cover['url'])) {
                return (string) $cover['url'];
            }
        }
        if (isset($product['id_image']) && (int) $product['id_image'] > 0 && is_object($link)) {
            $linkRewrite = isset($product['link_rewrite']) ? $product['link_rewrite'] : '';
            if (is_array($linkRewrite)) {
                $linkRewrite = isset($linkRewrite[$idLang]) ? $linkRewrite[$idLang] : reset($linkRewrite);
            }

            try {
                return (string) $link->getImageLink(
                    (string) $linkRewrite,
                    (int) $product['id_image'],
                    'large_default'
                );
            } catch (\Exception $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * Categories — Mailchimp expects an array of category names. PS exposes the
     * default category name + a categories collection in the presented array.
     * Returns deduped (case-sensitive) list, preserving insertion order.
     *
     * @param array|\ArrayAccess $product
     *
     * @return string[]
     */
    public static function resolveCategories($product)
    {
        $categories = [];
        if (isset($product['category_name']) && $product['category_name']) {
            $categories[] = (string) $product['category_name'];
        }
        if (isset($product['categories']) && is_array($product['categories'])) {
            foreach ($product['categories'] as $cat) {
                if (is_array($cat) && isset($cat['name']) && '' !== $cat['name']
                    && !in_array((string) $cat['name'], $categories, true)) {
                    $categories[] = (string) $cat['name'];
                } elseif (is_string($cat) && '' !== $cat
                          && !in_array($cat, $categories, true)) {
                    $categories[] = $cat;
                }
            }
        }

        return $categories;
    }

    /**
     * Product-URL resolution. Presenter uses `url`, Cart::getProducts() raw uses
     * `link` (both contain Link::getProductLink output). Anchor-stripped via
     * stripCombinationAnchor() for the same reason described there.
     *
     * @param array|\ArrayAccess $product
     *
     * @return string
     */
    public static function resolveProductUrl($product)
    {
        $productUrl = '';
        if (isset($product['url'])) {
            $productUrl = (string) $product['url'];
        } elseif (isset($product['link'])) {
            $productUrl = (string) $product['link'];
        }

        return self::stripCombinationAnchor($productUrl);
    }

    /**
     * Strip the `#/<attr>-<val>/...` anchor PS appends to product links.
     * The anchor encodes the ORIGINAL variant's display attributes and goes
     * stale the moment the customer switches combination (the bridge swaps
     * the slug but not the hash). Mailchimp doesn't need it — slug-only is
     * correct, clickable, and tracking-stable.
     *
     * Shared across all three Pixel surfaces (cart-row, product object,
     * order line item) so the URL value sent in PRODUCT_VIEWED / CART_VIEWED /
     * PURCHASED payloads stays byte-identical for the same product.
     *
     * @param string $url
     *
     * @return string
     */
    public static function stripCombinationAnchor($url)
    {
        $url = (string) $url;
        if ('' === $url) {
            return '';
        }
        $hashAt = strpos($url, '#');
        if (false !== $hashAt) {
            return substr($url, 0, $hashAt);
        }

        return $url;
    }

    /**
     * Build a Mailchimp Pixel "discounts[]" entry from a raw
     * Order::getCartRules() row. Returns null when the row is missing both
     * a usable code and a positive amount (signal to caller to drop the entry).
     *
     * Per Mailchimp Pixel SDK shape — entries must have:
     *   {code: string, amount: float, type: 'amount'}
     *
     * @return array|null
     */
    public static function buildDiscountEntry(array $rule)
    {
        $code = '';
        if (!empty($rule['name'])) {
            $code = (string) $rule['name'];
        } elseif (!empty($rule['code'])) {
            $code = (string) $rule['code'];
        }
        if ('' === $code) {
            return null;
        }

        $amount = 0.0;
        if (isset($rule['value']) && (float) $rule['value'] > 0) {
            $amount = (float) $rule['value'];
        } elseif (isset($rule['value_tax_excl']) && (float) $rule['value_tax_excl'] > 0) {
            $amount = (float) $rule['value_tax_excl'];
        }
        if ($amount <= 0) {
            return null;
        }

        return [
            'code' => $code,
            'amount' => $amount,
            'type' => 'amount',
        ];
    }

    /**
     * Order-totals derivation in the merchant's configured tax-display mode.
     * Mirrors CHECKOUT_STARTED's logic so PURCHASED + CHECKOUT_STARTED payloads
     * stay arithmetically consistent for the same merchant.
     *
     * Tax-included mode (B2C convention): products subtotal + shipping carry tax,
     * totalTax reported as 0 (the customer never saw a separate tax line).
     *
     * Tax-excluded mode (B2B convention): subtotal + shipping tax-excluded,
     * totalTax broken out explicitly. The equation balances:
     *   subtotal + tax + shipping − Σdiscounts = totalPrice
     *
     * @param array $orderFields Subset of Order properties needed:
     *                           total_paid_tax_incl, total_paid_tax_excl, total_products,
     *                           total_products_wt, total_shipping_tax_incl, total_shipping_tax_excl
     * @param bool $isTaxIncl
     *
     * @return array{totalPrice: float, subtotalPrice: float, totalShipping: float, totalTax: float}
     */
    public static function deriveOrderTotals(array $orderFields, $isTaxIncl)
    {
        $totalPrice = isset($orderFields['total_paid_tax_incl']) ? (float) $orderFields['total_paid_tax_incl'] : 0.0;

        if ($isTaxIncl) {
            return [
                'totalPrice' => $totalPrice,
                'subtotalPrice' => isset($orderFields['total_products_wt']) ? (float) $orderFields['total_products_wt'] : 0.0,
                'totalShipping' => isset($orderFields['total_shipping_tax_incl']) ? (float) $orderFields['total_shipping_tax_incl'] : 0.0,
                'totalTax' => 0.0,
            ];
        }

        $totalPaidExcl = isset($orderFields['total_paid_tax_excl']) ? (float) $orderFields['total_paid_tax_excl'] : 0.0;

        return [
            'totalPrice' => $totalPrice,
            'subtotalPrice' => isset($orderFields['total_products']) ? (float) $orderFields['total_products'] : 0.0,
            'totalShipping' => isset($orderFields['total_shipping_tax_excl']) ? (float) $orderFields['total_shipping_tax_excl'] : 0.0,
            'totalTax' => round($totalPrice - $totalPaidExcl, 2),
        ];
    }
}
