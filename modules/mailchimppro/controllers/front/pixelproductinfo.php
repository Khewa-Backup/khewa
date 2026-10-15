<?php
/**
 * MailChimp
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

/**
 * Public FO endpoint that returns the SERVER-BUILT Mailchimp Pixel product
 * payload for a given (id_product, id_product_attribute). Reachable as:
 *
 *   GET /module/mailchimppro/pixelproductinfo?id_product=X&id_product_attribute=Y
 *
 * Purpose: last-resort fallback used by the storefront bridge ONLY when the
 * cheaper paths can't produce all 5 required Mailchimp fields:
 *   1. server-rendered displayProductAdditionalInfo queue push (preferred), then
 *   2. DOM-parse of PS's displayAjaxRefresh / quickview AJAX response
 * If neither yields {id, productId, title, price, currency} the bridge calls
 * this endpoint to obtain the authoritative server-built payload — same data
 * path as the displayProductAdditionalInfo hook + the actionFrontControllerSetMedia
 * JsDef emit. No DOM scraping or guesswork.
 *
 * Always returns HTTP 200 + JSON. On any failure (invalid ids, kill-switch,
 * product not in shop) returns {ok:false} with no detail — same opacity policy
 * as pixeltelemetry: an attacker probing the endpoint can't distinguish
 * failure modes from "doesn't exist".
 *
 * Defenses against abuse on a public endpoint:
 *   1. Strict (int) cast on both ids before any DB use
 *   2. Product must exist AND be active AND visible in the current shop
 *   3. Cache headers prevent intermediary caches from holding per-visitor
 *      responses (price/currency depend on context)
 *
 * Why a separate endpoint vs. extending pixeltelemetry: pixeltelemetry is
 * write-only (logs to ring buffer); this is read-only (returns data). Mixing
 * verbs muddles the kill-switch + rate-limit semantics. Cleaner split.
 */
class MailchimpproPixelproductinfoModuleFrontController extends ModuleFrontController
{
    /** @var bool no auth required — public FO endpoint by design */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    /**
     * PS dispatch routes here when Controller::isAjax() returns true.
     */
    public function displayAjax()
    {
        $this->respond($this->buildResponse());
    }

    /**
     * PS dispatch routes here when Controller::isAjax() returns false.
     * Same logic as displayAjax — both paths must produce identical output.
     */
    public function display()
    {
        $this->respond($this->buildResponse());
    }

    /**
     * Resolves the payload for the requested product + variant, or returns
     * {ok:false} on any failure. Failures are silent by design — see class
     * docblock for the opacity policy rationale.
     *
     * @return array
     */
    private function buildResponse()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $idProductAttribute = (int) Tools::getValue('id_product_attribute');
        if ($idProduct <= 0) {
            return ['ok' => false];
        }

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        try {
            // Build a Product object scoped to current language + shop. This
            // is the SAME entry point the displayProductAdditionalInfo path
            // takes when PS renders the product page — guarantees field
            // shape parity with that hook's payload.
            $product = new Product($idProduct, true, $idLang, $idShop);
            if (!Validate::isLoadedObject($product) || !$product->active) {
                return ['ok' => false];
            }
            // Visibility check — `none` means the product is API/cart-only,
            // not browsable. The pixel endpoint mirrors public-visibility
            // semantics (the visitor shouldn't be able to retrieve pixel
            // metadata for a product they can't see in the catalog).
            if ('none' === $product->visibility) {
                return ['ok' => false];
            }

            // Variant must belong to this product. Caller-supplied ids are
            // untrusted: silently falling back to the default variant on a
            // bad attribute would emit a payload with a nonsense id and
            // price=0 — Mailchimp would reject it. Better to reject upfront
            // so the bridge logs PIXEL_DATA_INCOMPLETE and skips mcTrack.
            if ($idProductAttribute > 0) {
                $combo = new Combination($idProductAttribute);
                if (!Validate::isLoadedObject($combo)
                    || (int) $combo->id_product !== $idProduct) {
                    return ['ok' => false];
                }
            }

            // Build a presented-product-shaped array manually, mirroring the
            // shape PS's product presenter produces (the same shape
            // buildPixelProductPayloadFromArray already accepts from the
            // displayProductAdditionalInfo hook). We avoid the presenter
            // chain itself because its namespaces shifted slightly between
            // PS 1.7.0.6 → 9.x; reading off Product + Context + Image is
            // stable across the whole target matrix.
            $presented = $this->buildPresentedProductLike($product, $idProductAttribute, $idLang);

            // $this->module is set by ModuleFrontController's constructor; we
            // wouldn't be inside the controller if the module weren't loaded.
            /** @var Mailchimppro $module */
            $module = $this->module;
            $payload = $module->buildPixelProductPayloadFromArray($presented);
            if (empty($payload['id'])) {
                return ['ok' => false];
            }

            return ['ok' => true, 'product' => $payload];
        } catch (Exception $e) {
            // Defensive — buildPixelProductPayloadFromArray + the presenter
            // chain can throw on edge cases (deleted images, broken
            // categories). Treat as 'not available', no leak.
            return ['ok' => false];
        }
    }

    /**
     * Build an associative array that LOOKS like PS's presented product (the
     * shape buildPixelProductPayloadFromArray already accepts). We don't use
     * PS's presenter chain itself because its namespaces have shifted across
     * PS 1.7.0.6 → 9.x and the Adapter classes aren't part of the stable
     * public API; reading off Product + Combination + Image + Link is
     * stable across the whole target matrix.
     *
     * Field shape matches what hookDisplayProductAdditionalInfo would receive
     * from PS's presenter:
     *   - id_product, id_product_attribute     — int
     *   - name, reference                       — string (language-resolved)
     *   - price_amount                          — float (variant-aware)
     *   - url                                   — string (with variant id baked in)
     *   - cover.large.url                       — string (variant-aware image)
     *   - manufacturer_name                     — string
     *   - category_name, categories[].name      — strings
     *
     * @param Product $product loaded for $idLang + $idShop
     * @param int $idProductAttribute 0 = default variant
     * @param int $idLang
     *
     * @return array
     */
    private function buildPresentedProductLike($product, $idProductAttribute, $idLang)
    {
        $idShop = (int) $this->context->shop->id;
        $idProduct = (int) $product->id;

        // Variant-aware fields ----------------------------------------------
        $price = (float) Product::getPriceStatic(
            $idProduct,
            true,              // with tax
            $idProductAttribute > 0 ? $idProductAttribute : null
        );

        $linkCategory = Category::getLinkRewrite($product->id_category_default, $idLang);
        $productUrl = (string) $this->context->link->getProductLink(
            $product,
            null,
            $linkCategory,
            $product->ean13,
            $idLang,
            $idShop,
            $idProductAttribute > 0 ? $idProductAttribute : null
        );

        // SKU — combinations can carry their own reference (variant SKU);
        // fall back to the product's reference when the combination has none.
        $reference = (string) $product->reference;
        if ($idProductAttribute > 0) {
            try {
                $combo = new Combination($idProductAttribute);
                if (Validate::isLoadedObject($combo) && !empty($combo->reference)) {
                    $reference = (string) $combo->reference;
                }
            } catch (Exception $e) {
                // keep $product->reference
            }
        }

        // Variant-aware image (falls back to default cover).
        $imageUrl = '';
        $linkRewrite = $product->link_rewrite;
        if (is_array($linkRewrite)) {
            $linkRewrite = isset($linkRewrite[$idLang])
                ? $linkRewrite[$idLang]
                : reset($linkRewrite);
        }
        $imageSize = Configuration::get(MailchimpProConfig::PRODUCT_IMAGE_SIZE);
        if (!$imageSize) {
            $imageSize = 'large_default';
        }
        $image = null;
        if ($idProductAttribute > 0) {
            $image = Image::getBestImageAttribute($idShop, $idLang, $idProduct, $idProductAttribute);
        }
        if (!$image) {
            $image = Image::getCover($idProduct);
        }
        if (is_array($image) && !empty($image['id_image'])) {
            $imageUrl = (string) $this->context->link->getImageLink(
                $linkRewrite,
                $idProduct . '-' . $image['id_image'],
                $imageSize
            );
        }

        // Manufacturer (vendor) + categories — stable per product.
        $vendor = '';
        if (!empty($product->id_manufacturer)) {
            $vendor = (string) Manufacturer::getNameById((int) $product->id_manufacturer);
        }
        $categoryName = '';

        try {
            $defaultCategory = new Category((int) $product->id_category_default, $idLang);
            if (Validate::isLoadedObject($defaultCategory)) {
                $categoryName = (string) $defaultCategory->name;
            }
        } catch (Exception $e) { /* skip */
        }

        $categoriesAssoc = [];

        try {
            $ids = $product->getCategories();
            if (is_array($ids)) {
                foreach ($ids as $catId) {
                    $cat = new Category((int) $catId, $idLang);
                    if (Validate::isLoadedObject($cat) && !empty($cat->name)) {
                        $categoriesAssoc[] = ['name' => (string) $cat->name];
                    }
                }
            }
        } catch (Exception $e) { /* skip */
        }

        return [
            'id_product' => $idProduct,
            'id_product_attribute' => $idProductAttribute,
            'id' => $idProduct,
            'name' => (string) $product->name,
            'reference' => $reference,
            'price_amount' => $price,
            'url' => $productUrl,
            'cover' => '' !== $imageUrl
                ? ['large' => ['url' => $imageUrl], 'url' => $imageUrl]
                : null,
            'manufacturer_name' => $vendor,
            'category_name' => $categoryName,
            'categories' => $categoriesAssoc,
        ];
    }

    /**
     * JSON respond with cache-prevention headers. Visitor context (price,
     * currency conversion, taxes) varies per request — intermediaries must
     * not pin a single visitor's data and serve it to others.
     *
     * @param array $body
     */
    private function respond($body)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        echo json_encode($body, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            exit;
        }
    }
}
