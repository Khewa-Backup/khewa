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
 * Public FO endpoint that returns the VISITOR's CURRENT cart id (and a small
 * summary). Reachable as:
 *
 *   POST /module/mailchimppro/pixelcartinfo
 *
 * Why POST for a read?
 *   Cache-everything Varnish / Cloudflare page rules treat GET as cacheable
 *   even with `Cache-Control: no-store`. POST is treated as "always fresh"
 *   by basically every cache layer (HTTP semantics: POST changes state).
 *   We prefer POST here so per-visitor responses can never be served from a
 *   shared cache to a different visitor. Same rationale customerinfo.php
 *   uses for its identify endpoint; verb-semantic abuse is acceptable when
 *   needed for cache-busting (Mailchimp / Shopify / Stripe APIs all do this
 *   in places).
 *
 * Purpose: AJAX fallback used by the storefront bridge when MC_PIXEL.cartId
 * (emitted via JsDef at pageload) is 0 — typically the "first add of session"
 * case where PS lazy-creates the cart row AFTER setMedia has already fired.
 * Mailchimp's PRODUCT_ADDED_TO_CART, CART_VIEWED, CHECKOUT_STARTED events all
 * require a `cartId`; this endpoint gives the bridge a way to recover when the
 * cached value is stale.
 *
 * Returns:
 *   {ok: true, cartId: 1234, productsCount: 1, currency: "EUR"}
 *   {ok: false}   on any failure / kill-switch off — caller can't distinguish modes
 *
 * Defenses against abuse:
 *   1. Per-visitor (session-scoped) data — never exposes another visitor's cart
 *   2. Cache-prevention headers + POST verb — visitor's cart never enters
 *      shared caches
 */
class MailchimpproPixelcartinfoModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = false;
    /** @var bool */
    public $ajax = true;
    /** @var bool */
    public $ssl = true;

    public function displayAjax()
    {
        $this->respond($this->buildResponse());
    }

    public function display()
    {
        $this->respond($this->buildResponse());
    }

    /**
     * Resolves the visitor's current cart id from the request context, or
     * returns {ok:false} on any failure.
     *
     * @return array
     */
    private function buildResponse()
    {
        try {
            if (!isset($this->context->cart)) {
                return ['ok' => false];
            }
            $cartId = (int) $this->context->cart->id;
            if ($cartId <= 0) {
                // Genuinely no cart yet — caller will treat as PIXEL_DATA_INCOMPLETE.
                // We still return ok:false (not ok:true with cartId:0) so the
                // bridge can distinguish "endpoint reachable but no cart" from
                // "endpoint reachable, cart found".
                return ['ok' => false];
            }

            $payload = [
                'ok' => true,
                'cartId' => $cartId,
                'currency' => isset($this->context->currency) ? (string) $this->context->currency->iso_code : '',
            ];
            // Best-effort cart summary — useful for CART_VIEWED / CHECKOUT_STARTED
            // (PRODUCT_ADDED_TO_CART doesn't need either field; cheap to include
            // since the cart object is already loaded in context).
            //
            //   productsCount = total units across all line items (sum of
            //                   cart_quantity per line) — matches PS's user-
            //                   facing "There are N items in your cart" label
            //                   and `window.prestashop.cart.products_count`.
            //                   For a cart with 1 line × 5 units this returns 5.
            //   lineItemCount = number of distinct line items (1 in the same case).
            //
            // Cart::nbProducts() exists on every PS 1.7.0.6 → 9.1 (Cart class
            // is core, not bundled module).
            if (method_exists($this->context->cart, 'nbProducts')) {
                $payload['productsCount'] = (int) $this->context->cart->nbProducts();
            }
            $products = null;
            if (method_exists($this->context->cart, 'getProducts')) {
                $products = $this->context->cart->getProducts();
                $payload['lineItemCount'] = count($products);
            }

            // Extended mode for CART_VIEWED's Layer 2 fallback (when the
            // bridge's frontend data — window.prestashop.cart — is missing or
            // incomplete). Returns the full Mailchimp-shaped lineItems[]
            // (each entry { item, quantity }) + totalPrice. Triggered only by
            // explicit ?with_items=1 so the default response stays cheap.
            //
            // The extra cost is exactly one Cart::getProducts() call (which
            // returns already-cached cart line data) + buildPixelProductPayloadFromArray
            // per line — same shape PRODUCT_ADDED_TO_CART builds client-side.
            if (1 === (int) Tools::getValue('with_items') && is_array($products) && !empty($products)) {
                // $this->module is set by ModuleFrontController's constructor; we
                // wouldn't be inside the controller if the module weren't loaded.
                /** @var Mailchimppro $module */
                $module = $this->module;
                $idLang = (int) $this->context->language->id;
                $idShop = (int) $this->context->shop->id;
                $lineItems = [];
                foreach ($products as $line) {
                    // Enrich raw cart line with productUrl + categories
                    // BEFORE calling the shared payload builder. Cart::
                    // getProducts() returns enriched data via getProduct
                    // Properties() but lacks getProductLink() + category
                    // list — we add those here so the resulting payload
                    // matches the quality of the frontend (window.
                    // prestashop.cart) path.
                    $idProduct = isset($line['id_product']) ? (int) $line['id_product'] : 0;
                    $idProductAttribute = isset($line['id_product_attribute']) ? (int) $line['id_product_attribute'] : 0;
                    if ($idProduct <= 0) {
                        continue;
                    }

                    try {
                        $product = new Product($idProduct, true, $idLang, $idShop);
                        if (Validate::isLoadedObject($product)) {
                            // productUrl — variant-aware via id_product_attribute
                            if (empty($line['url']) && empty($line['link'])) {
                                $linkCategory = Category::getLinkRewrite($product->id_category_default, $idLang);
                                $line['url'] = (string) $this->context->link->getProductLink(
                                    $product,
                                    null,
                                    $linkCategory,
                                    $product->ean13,
                                    $idLang,
                                    $idShop,
                                    $idProductAttribute > 0 ? $idProductAttribute : null
                                );
                            }
                            // categories list — Cart::getProducts() exposes
                            // only the single `category` string; resolve all
                            // category names this product is in.
                            if (empty($line['categories'])) {
                                $catIds = $product->getCategories();
                                $cats = [];
                                if (is_array($catIds)) {
                                    foreach ($catIds as $catId) {
                                        $cat = new Category((int) $catId, $idLang);
                                        if (Validate::isLoadedObject($cat) && !empty($cat->name)) {
                                            $cats[] = ['name' => (string) $cat->name];
                                        }
                                    }
                                }
                                if (!empty($cats)) {
                                    $line['categories'] = $cats;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Defensive — Product load failures shouldn't drop
                        // the whole line; buildPixelProductPayloadFromArray
                        // will produce what it can from the raw line below.
                    }

                    $item = $module->buildPixelProductPayloadFromArray($line);
                    if (empty($item['id'])) {
                        continue;
                    }
                    $lineItems[] = [
                        'item' => $item,
                        'quantity' => isset($line['cart_quantity']) ? (int) $line['cart_quantity'] : 1,
                    ];
                }
                if (!empty($lineItems)) {
                    $payload['lineItems'] = $lineItems;
                }
                // totalPrice — PS's Cart::getOrderTotal(true, Cart::BOTH)
                // returns with-tax total including products + shipping + taxes.
                // Same numeric Mailchimp's totalPrice expects.
                if (method_exists($this->context->cart, 'getOrderTotal')) {
                    $payload['totalPrice'] = (float) $this->context->cart->getOrderTotal(true);
                }
            }

            // Checkout-specific extras (CHECKOUT_STARTED's Layer 2 recovery
            // path). Adds subtotalPrice / totalTax / totalShipping / discounts
            // / customerId on top of with_items=1's lineItems + totalPrice.
            // Same PS APIs (Cart::getOrderTotal with different breakdown flags)
            // — already cached after the with_items pass, so the marginal
            // server cost is small.
            if (1 === (int) Tools::getValue('with_checkout')) {
                if (method_exists($this->context->cart, 'getOrderTotal')) {
                    // Detect the customer's tax-display mode — matches PS's
                    // own CartPresenter logic that sets subtotals.tax = null
                    // for tax-INCLUDED display shops (PS_TAX_INC = 0) and
                    // populates it for tax-EXCLUDED shops (PS_TAX_EXC = 1).
                    // Mirroring keeps Layer-1 (JS presenter) and Layer-2
                    // (this controller) payloads consistent for the same
                    // cart.
                    //
                    // Group::getPriceDisplayMethod() exists on PS 1.7.0.6 → 9.1.
                    // 1.7.0.6 returns a raw Db::getValue string; later
                    // versions cast inside core. Always cast at call site.
                    $idDefaultGroup = 0;
                    if ($this->context->customer && (int) $this->context->customer->id_default_group) {
                        $idDefaultGroup = (int) $this->context->customer->id_default_group;
                    }
                    if ($idDefaultGroup <= 0) {
                        $idDefaultGroup = (int) Configuration::get('PS_UNIDENTIFIED_GROUP');
                    }
                    if ($idDefaultGroup <= 0) {
                        // Final fallback — every PS install seeds group 1 (Visitor).
                        $idDefaultGroup = 1;
                    }
                    $priceDisplayMethod = (int) Group::getPriceDisplayMethod($idDefaultGroup);
                    // PS_TAX_INC = 0 (tax-included display).
                    // PS_TAX_EXC = 1 (tax-excluded display).
                    $taxIncConst = defined('PS_TAX_INC') ? PS_TAX_INC : 0;
                    $isTaxIncl = ($priceDisplayMethod === $taxIncConst);

                    // Cart::ONLY_PRODUCTS = 7 — products subtotal (no shipping/tax).
                    // Cart::ONLY_SHIPPING = 5 — shipping cost.
                    $onlyProducts = defined('Cart::ONLY_PRODUCTS') ? Cart::ONLY_PRODUCTS : 7;
                    $onlyShipping = defined('Cart::ONLY_SHIPPING') ? Cart::ONLY_SHIPPING : 5;
                    $totalWithTax = isset($payload['totalPrice']) ? $payload['totalPrice'] : (float) $this->context->cart->getOrderTotal(true);

                    if ($isTaxIncl) {
                        // Tax-INCLUDED display: subtotal/shipping carry the
                        // tax already; reporting a separate totalTax would
                        // double-count. Honor PS's display intent — matches
                        // CartPresenter's subtotals.tax = null on the JS side.
                        $payload['subtotalPrice'] = (float) $this->context->cart->getOrderTotal(true, $onlyProducts);
                        $payload['totalShipping'] = (float) $this->context->cart->getOrderTotal(true, $onlyShipping);
                        $payload['totalTax'] = 0.0;
                    } else {
                        // Tax-EXCLUDED display: break tax out, send tax-excl
                        // subtotal/shipping. Equation balances against
                        // totalPrice (tax-incl): subtotal + tax + shipping − discounts = total.
                        $payload['subtotalPrice'] = (float) $this->context->cart->getOrderTotal(false, $onlyProducts);
                        $payload['totalShipping'] = (float) $this->context->cart->getOrderTotal(false, $onlyShipping);
                        $totalWithoutTax = (float) $this->context->cart->getOrderTotal(false);
                        $payload['totalTax'] = round($totalWithTax - $totalWithoutTax, 2);
                    }
                }
                // Applied discount codes (vouchers). PS exposes:
                //   Cart::getCartRules() — returns active cart rules for this cart
                if (method_exists($this->context->cart, 'getCartRules')) {
                    $rules = $this->context->cart->getCartRules();
                    $discounts = [];
                    if (is_array($rules)) {
                        foreach ($rules as $r) {
                            $entry = [];
                            if (!empty($r['code'])) {
                                $entry['code'] = (string) $r['code'];
                            } elseif (!empty($r['name'])) {
                                // Auto-rules can lack a code — use the rule name
                                $entry['code'] = (string) $r['name'];
                            }
                            // Reduction shape varies — capture amount + type best-effort
                            if (isset($r['reduction_percent']) && (float) $r['reduction_percent'] > 0) {
                                $entry['amount'] = (float) $r['reduction_percent'];
                                $entry['type'] = 'percentage';
                            } elseif (isset($r['reduction_amount']) && (float) $r['reduction_amount'] > 0) {
                                $entry['amount'] = (float) $r['reduction_amount'];
                                $entry['type'] = 'amount';
                            } elseif (isset($r['value_real']) && (float) $r['value_real'] > 0) {
                                $entry['amount'] = (float) $r['value_real'];
                                $entry['type'] = 'amount';
                            }
                            if (!empty($entry['code'])) {
                                $discounts[] = $entry;
                            }
                        }
                    }
                    if (!empty($discounts)) {
                        $payload['discounts'] = $discounts;
                    }
                }
                // customerId — hashed email per Mailchimp's identify convention
                // (matches what the bridge's identifyOrReset path sends). Only
                // populated when visitor is logged in.
                if (isset($this->context->customer)
                    && method_exists($this->context->customer, 'isLogged')
                    && $this->context->customer->isLogged()) {
                    $email = strtolower(trim((string) $this->context->customer->email));
                    if ('' !== $email) {
                        $payload['customerId'] = hash('sha256', $email);
                    }
                }
            }

            // OPC identify extra — returns formatted identifyValue (sha256
            // or plain email per MAILCHIMP_PIXEL_IDENTIFY_FORMAT config) for
            // the bridge's OPC identify watcher. Source of truth is the
            // Customer record bound to the cart (`$cart->id_customer`).
            // PS associates the cart with a Customer (including guest
            // Customer with is_guest=1) the moment the personal-info step
            // submits server-side. Solves the case where the active
            // 3rd-party checkout module doesn't push the new email into
            // window.prestashop.customer.email after its AJAX step.
            //
            // Cross-PS verified: Cart has NO getCustomer() method on PS
            // 1.7.0.6 → 9.1 — load Customer directly from $cart->id_customer
            // (Cart::id_customer is a public property on every version).
            if (1 === (int) Tools::getValue('with_customer_email')
                && (int) $this->context->cart->id_customer > 0) {
                try {
                    $customer = new Customer((int) $this->context->cart->id_customer);
                    if (Validate::isLoadedObject($customer)
                        && !empty($customer->email)) {
                        $email = strtolower(trim((string) $customer->email));
                        if ('' !== $email) {
                            $format = (string) Configuration::get(
                                MailchimpProConfig::MAILCHIMP_PIXEL_IDENTIFY_FORMAT
                            );
                            $payload['identifyValue'] = ('EMAIL' === $format)
                                ? $email
                                : hash('sha256', $email);

                            // Phone enrichment — mirrors customerinfo.php. OPC
                            // modules that don't reload between the address-step
                            // and the rest of checkout would otherwise have to
                            // wait until the next navigation to fire phone
                            // identify; returning phoneValue here lets the OPC
                            // watcher fire it alongside email immediately.
                            $primaryAddress = PrestaChamps\MailchimpPro\Helpers\CustomerAddressSelector::pickPrimary(
                                $customer,
                                $this->context->cart
                            );
                            if ($primaryAddress) {
                                $mobile = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone_mobile);
                                $landline = PrestaChamps\MailchimpPro\Helpers\PhoneNormalizer::normalize($primaryAddress->phone);
                                $phoneRaw = '' !== $mobile ? $mobile : $landline;
                                if ('' !== $phoneRaw) {
                                    $payload['phoneValue'] = ('EMAIL' === $format) ? $phoneRaw : hash('sha256', $phoneRaw);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // leave identifyValue absent — bridge falls through gracefully
                }
            }

            return $payload;
        } catch (Exception $e) {
            return ['ok' => false];
        }
    }

    /**
     * JSON response with cache-prevention headers — visitor cart is per-session.
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
