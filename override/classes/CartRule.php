<?php
/**
 * khewacorechanges — override of CartRule::getContextualValue()
 *
 * Carries Khewa's custom tax handling for fixed-amount (¤) discounts:
 * when the discount is stored tax-excluded (reduction_tax = 0) but a
 * tax-included value is requested (use_tax = 1), the amount is applied
 * against the tax-included cart total directly instead of being inflated
 * by the average VAT rate. See modules/khewacorechanges/CORE_CHANGES.md #2.
 *
 * Origin: classes/CartRule.php core edit, commit 99f2dc776 (2025-10-03).
 * PrestaShop 9.2: rebuilt from the 9.2 core method (it has the new
 * actionGetCartRuleContextualValue hook and the new discount system), with
 * only the Khewa branch added (marked "Khewa"). Everything else is a verbatim
 * copy of PrestaShop 9.2 core. The 1.7.8 version is in the module's git history.
 */
use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountType;
if (!defined('_PS_VERSION_')) {
    exit;
}
class CartRule extends CartRuleCore
{
    /**
     * The reduction value is POSITIVE.
     *
     * @param bool $use_tax Apply taxes
     * @param Context $context Context instance
     * @param bool $use_cache Allow using cache to avoid multiple free gift using multishipping
     *
     * @return float|int|string
     */
    /*
    * module: khewacorechanges
    * date: 2026-11-01 20:05:43
    * version: 1.0.0
    */
    public function getContextualValue($use_tax, ?Context $context = null, $filter = null, $package = null, $use_cache = true)
    {
        if (!CartRule::isFeatureActive()) {
            return 0;
        }
        /*
         * Custom cart rule value from modules. Allows to create infinite possibilities of rules.
         *
         * If a module is applying a custom value using actionApplyCartRule, it should also apply
         * the same value here.
         */
        $contextualValueFromModules = null;
        Hook::exec(
            'actionGetCartRuleContextualValue',
            [
                'cart_rule' => $this,
                'use_tax' => $use_tax,
                'context' => $context,
                'filter' => $filter,
                'package' => $package,
                'use_cache' => $use_cache,
                'contextualValueFromModules' => &$contextualValueFromModules,
            ]
        );
        if ($contextualValueFromModules !== null) {
            return $contextualValueFromModules;
        }
        if (!empty($context->virtualTotalTaxIncluded) && !empty($context->virtualTotalTaxExcluded)) {
            $basePriceForPercentReduction = $use_tax ? $context->virtualTotalTaxIncluded : $context->virtualTotalTaxExcluded;
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$filter) {
            $filter = CartRule::FILTER_ACTION_ALL;
        }
        $all_products = $context->cart->getProducts();
        $package_products = (null === $package ? $all_products : $package['products']);
        $all_cart_rules_ids = $context->cart->getOrderedCartRulesIds();
        $cartId = (int) $context->cart->id;
        if (!array_key_exists($cartId, static::$cartAmountCache)) {
            if (!Configuration::get('PS_TAX')) {
                static::$cartAmountCache[$cartId]['te'] = $context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
                static::$cartAmountCache[$cartId]['ti'] = static::$cartAmountCache[$cartId]['te'];
            } else {
                static::$cartAmountCache[$cartId]['ti'] = $context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                static::$cartAmountCache[$cartId]['te'] = $context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
            }
        }
        $cart_amount_te = static::$cartAmountCache[$cartId]['te'];
        $cart_amount_ti = static::$cartAmountCache[$cartId]['ti'];
        $reduction_value = 0;
        $cache_id = 'getContextualValue_' . (int) $this->id . '_' . (int) $use_tax . '_' . $cartId . '_' . (int) $filter;
        foreach ($package_products as $product) {
            $cache_id .= '_' . (int) $product['id_product'] . '_' . (int) $product['id_product_attribute'] . (isset($product['in_stock']) ? '_' . (int) $product['in_stock'] : '');
        }
        if (Cache::isStored($cache_id)) {
            return Cache::retrieve($cache_id);
        }
        $reduction_carrier = 0;
        if ($this->free_shipping && in_array($filter, [CartRule::FILTER_ACTION_ALL, CartRule::FILTER_ACTION_ALL_NOCAP, CartRule::FILTER_ACTION_SHIPPING])) {
            if (!$this->carrier_restriction) {
                $reduction_carrier += $context->cart->getOrderTotal($use_tax, Cart::ONLY_SHIPPING, null === $package ? null : $package['products'], null === $package ? null : $package['id_carrier']);
            } else {
                $data = Db::getInstance()->executeS('
					SELECT crc.id_cart_rule, c.id_carrier
					FROM ' . _DB_PREFIX_ . 'cart_rule_carrier crc
					INNER JOIN ' . _DB_PREFIX_ . 'carrier c ON (c.id_reference = crc.id_carrier AND c.deleted = 0)
					WHERE crc.id_cart_rule = ' . (int) $this->id . '
					AND c.id_carrier = ' . (int) $context->cart->id_carrier);
                if ($data) {
                    foreach ($data as $cart_rule) {
                        $reduction_carrier += $context->cart->getCarrierCost((int) $cart_rule['id_carrier'], $use_tax, $context->country);
                    }
                }
            }
            $reduction_value += $reduction_carrier;
        }
        if (in_array($filter, [CartRule::FILTER_ACTION_ALL, CartRule::FILTER_ACTION_ALL_NOCAP, CartRule::FILTER_ACTION_REDUCTION])) {
            $order_package_products_total = 0;
            if (self::isDiscountFeatureFlagEnabled()) {
                if ($this->getType() === DiscountType::ORDER_LEVEL && $this->reduction_percent > 0.00 && $this->reduction_product == 0) {
                    $order_products_total = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS, $package_products);
                    $order_shipping_total = $context->cart->getOrderTotal($use_tax, Cart::ONLY_SHIPPING, $package_products);
                    $order_total = $order_products_total + $order_shipping_total;
                    $reduction_value += $order_total * $this->reduction_percent / 100;
                    return $reduction_value;
                }
            }
            if ((float) $this->reduction_amount > 0
                || (float) $this->reduction_percent && $this->reduction_product == 0) {
                $order_package_products_total = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS, $package_products);
            }
            if ((float) $this->reduction_percent && $this->reduction_product == 0) {
                $order_total = $order_package_products_total;
                $basePriceContainsDiscount = isset($basePriceForPercentReduction) && $order_total === $basePriceForPercentReduction;
                foreach ($context->cart->getCartRules(CartRule::FILTER_ACTION_GIFT, false) as $cart_rule) {
                    $freeProductsPrice = Tools::ps_round($cart_rule['obj']->getContextualValue($use_tax, $context, CartRule::FILTER_ACTION_GIFT, $package), Context::getContext()->getComputingPrecision());
                    if ($basePriceContainsDiscount && isset($basePriceForPercentReduction)) {
                        $basePriceForPercentReduction -= $freeProductsPrice;
                    }
                    $order_total -= $freeProductsPrice;
                }
                if ($this->reduction_exclude_special) {
                    foreach ($package_products as $product) {
                        if ($product['reduction_applies']) {
                            $roundTotal = $use_tax ? $product['total_wt'] : $product['total'];
                            $excludedReduction = Tools::ps_round($roundTotal, Context::getContext()->getComputingPrecision());
                            $order_total -= $excludedReduction;
                            if ($basePriceContainsDiscount && isset($basePriceForPercentReduction)) {
                                $basePriceForPercentReduction -= $excludedReduction;
                            }
                        }
                    }
                }
                $basePriceForPercentReduction = $basePriceForPercentReduction ?? $order_total;
                $reduction_value += $basePriceForPercentReduction * $this->reduction_percent / 100;
            }
            if ((float) $this->reduction_percent && $this->reduction_product > 0) {
                foreach ($package_products as $product) {
                    if ($product['id_product'] == $this->reduction_product && (($this->reduction_exclude_special && !$product['reduction_applies']) || !$this->reduction_exclude_special)) {
                        $reduction_value += ($use_tax ? $product['total_wt'] : $product['total']) * $this->reduction_percent / 100;
                    }
                }
            }
            if ((float) $this->reduction_percent && $this->reduction_product == -1) {
                $cheapestProduct = $this->getCheapestProduct($all_products, $package_products, $use_tax);
                if ($cheapestProduct) {
                    $cheapestProductPrice = $use_tax ? $cheapestProduct['price_with_reduction'] : $cheapestProduct['price_with_reduction_without_tax'];
                    if (self::isDiscountFeatureFlagEnabled() && $this->getType() === DiscountType::PRODUCT_LEVEL) {
                        $reduction_value += $cheapestProduct['cart_quantity'] * $cheapestProductPrice * $this->reduction_percent / 100;
                    } else {
                        $reduction_value += $cheapestProductPrice * $this->reduction_percent / 100;
                    }
                }
            }
            if ((float) $this->reduction_percent && $this->reduction_product == -2) {
                $selected_products_reduction = 0;
                $selectedProducts = $this->getProductsMatchingSelection($package_products, $context->cart);
                foreach ($selectedProducts as $product) {
                    $productPrice = $use_tax ? $product['price_with_reduction'] : $product['price_with_reduction_without_tax'];
                    $selected_products_reduction += $productPrice * $product['cart_quantity'];
                }
                $reduction_value += $selected_products_reduction * $this->reduction_percent / 100;
            }
            if ((float) $this->reduction_amount > 0) {
                $prorata = 1;
                if (null !== $package && count($all_products)) {
                    $total_products = $use_tax ? $cart_amount_ti : $cart_amount_te;
                    if ($total_products) {
                        $prorata = $order_package_products_total / $total_products;
                    }
                }
                $reduction_amount = (float) $this->reduction_amount;
                if ($this->reduction_product > 0) {
                    foreach ($all_products as $product) {
                        if ($product['id_product'] == $this->reduction_product) {
                            $productPrice = $this->reduction_tax ? $product['price_wt'] : $product['price'];
                            $max_reduction_amount = (int) $product['cart_quantity'] * (float) $productPrice;
                            $reduction_amount = min($reduction_amount, $max_reduction_amount);
                            break;
                        }
                    }
                }
                if (isset($context->currency) && $this->reduction_currency != $context->currency->id) {
                    $voucherCurrency = new Currency($this->reduction_currency);
                    if ($reduction_amount == 0 || $voucherCurrency->conversion_rate == 0) {
                        $reduction_amount = 0;
                    } else {
                        $reduction_amount /= $voucherCurrency->conversion_rate;
                    }
                    $reduction_amount *= $context->currency->conversion_rate;
                    $reduction_amount = Tools::ps_round($reduction_amount, Context::getContext()->getComputingPrecision());
                }
                if (self::isDiscountFeatureFlagEnabled() && $this->getType() === DiscountType::PRODUCT_LEVEL && ($this->reduction_product == -1 || $this->reduction_product == -2)) {
                    if ($this->reduction_product == -1) {
                        $cheapestProduct = $this->getCheapestProduct($all_products, $package_products, $use_tax);
                        $selectedProducts = $cheapestProduct ? [$cheapestProduct] : [];
                    } else {
                        $selectedProducts = $this->getProductsMatchingSelection($package_products, $context->cart);
                    }
                    foreach ($selectedProducts as $product) {
                        $productReduction = $reduction_amount;
                        if ($this->reduction_tax !== $use_tax) {
                            $productTaxRate = ($product['rate'] ?? 0) / 100;
                            if ($this->reduction_tax && !$use_tax) {
                                $productReduction = $productReduction / (1 + $productTaxRate);
                            } elseif (!$this->reduction_tax && $use_tax) {
                                $productReduction = $productReduction * (1 + $productTaxRate);
                            }
                        }
                        $productPrice = $use_tax ? $product['price_with_reduction'] : $product['price_with_reduction_without_tax'];
                        $productReduction = min($productReduction, $productPrice);
                        $reduction_value += $productReduction * $product['cart_quantity'];
                    }
                } // If it has the same tax application that you need, then it's the right value, whatever the product!
                elseif ($this->reduction_tax == $use_tax) {
                    if ($filter != CartRule::FILTER_ACTION_ALL_NOCAP) {
                        $cart_amount = $use_tax ? $cart_amount_ti : $cart_amount_te;
                        $reduction_amount = min($reduction_amount, $cart_amount);
                    }
                    $reduction_value += $prorata * $reduction_amount;
                } else {
                    if (!$this->reduction_tax && $use_tax) {
                        if ($this->reduction_percent > 0) {
                            $cart_amount = $cart_amount_te; // Use tax-excluded amount for calculation
                            $reduction_amount = min($reduction_amount, $cart_amount);
                            $reduction_value += $prorata * $reduction_amount;
                        } else {
                            $cart_amount = $cart_amount_ti; // Use tax-included amount for calculation
                            $reduction_amount = min($reduction_amount, $cart_amount);
                            $reduction_value += $prorata * $reduction_amount;
                        }
                    } else {
                        if ($this->reduction_product > 0) {
                            foreach ($all_products as $product) {
                                if ($product['id_product'] == $this->reduction_product) {
                                    $product_price_ti = $product['price_wt'];
                                    $product_price_te = $product['price'];
                                    $product_vat_amount = $product_price_ti - $product_price_te;
                                    if ($product_vat_amount == 0 || $product_price_te == 0) {
                                        $product_vat_rate = 0;
                                    } else {
                                        $product_vat_rate = $product_vat_amount / $product_price_te;
                                    }
                                    if ($this->reduction_tax && !$use_tax) {
                                        $reduction_value += $prorata * $reduction_amount / (1 + $product_vat_rate);
                                    } elseif (!$this->reduction_tax && $use_tax) {
                                        $reduction_value += $prorata * $reduction_amount * (1 + $product_vat_rate);
                                    }
                                }
                            }
                        } elseif ($this->reduction_product == 0) {
                            $cart_amount_te = null;
                            $cart_amount_ti = null;
                            $cart_average_vat_rate = $context->cart->getAverageProductsTaxRate($cart_amount_te, $cart_amount_ti);
                            if ($filter != CartRule::FILTER_ACTION_ALL_NOCAP) {
                                if (self::isDiscountFeatureFlagEnabled() && $this->getType() === DiscountType::ORDER_LEVEL) {
                                    $max_reduction_amount = $this->reduction_tax
                                        ? $cart_amount_ti + $context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING, $package_products)
                                        : $cart_amount_te + $context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING, $package_products);
                                    $reduction_amount = min(
                                        $reduction_amount,
                                        $max_reduction_amount,
                                    );
                                } else {
                                    $reduction_amount = min($reduction_amount, $this->reduction_tax ? $cart_amount_ti : $cart_amount_te);
                                }
                            }
                            if ($this->reduction_tax && !$use_tax) {
                                $reduction_value += $prorata * $reduction_amount / (1 + $cart_average_vat_rate);
                            } elseif (!$this->reduction_tax && $use_tax) {
                                $reduction_value += $prorata * $reduction_amount * (1 + $cart_average_vat_rate);
                            }
                        }
                        /*
                         * Reduction on the cheapest or on the selection is not really meaningful and has been disabled in the backend, it only applies with percent
                         * Please keep this code, so it won't be considered as a bug
                         * elseif ($this->reduction_product == -1)
                         * elseif ($this->reduction_product == -2)
                         */
                    }
                }
                if ($filter != CartRule::FILTER_ACTION_ALL_NOCAP) {
                    $cart = Context::getContext()->cart;
                    if (!Validate::isLoadedObject($cart)) {
                        $cart = new Cart();
                    }
                    $cart_average_vat_rate = $cart->getAverageProductsTaxRate();
                    $current_cart_amount = $use_tax ? $cart_amount_ti : $cart_amount_te;
                    foreach ($all_cart_rules_ids as $current_cart_rule_id) {
                        if ((int) $current_cart_rule_id['id_cart_rule'] == (int) $this->id) {
                            break;
                        }
                        $previous_cart_rule = new CartRule((int) $current_cart_rule_id['id_cart_rule']);
                        $previous_reduction_amount = $previous_cart_rule->reduction_amount;
                        if ($previous_cart_rule->reduction_tax && !$use_tax) {
                            $previous_reduction_amount = $prorata * $previous_reduction_amount / (1 + $cart_average_vat_rate);
                        } elseif (!$previous_cart_rule->reduction_tax && $use_tax) {
                            $previous_reduction_amount = $prorata * $previous_reduction_amount * (1 + $cart_average_vat_rate);
                        }
                        $current_cart_amount = max($current_cart_amount - (float) $previous_reduction_amount, 0);
                    }
                    if (self::isDiscountFeatureFlagEnabled() && $this->getType() === DiscountType::ORDER_LEVEL) {
                        $current_cart_amount += $this->reduction_tax
                            ? $context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING, $package_products)
                            : $context->cart->getOrderTotal(false, Cart::ONLY_SHIPPING, $package_products);
                    }
                    $reduction_value = min($reduction_value, $current_cart_amount);
                }
            }
        }
        if ((int) $this->gift_product && in_array($filter, [CartRule::FILTER_ACTION_ALL, CartRule::FILTER_ACTION_ALL_NOCAP, CartRule::FILTER_ACTION_GIFT])) {
            $id_address = (null === $package ? 0 : $package['id_address']);
            foreach ($package_products as $product) {
                if ($product['id_product'] == $this->gift_product && ($product['id_product_attribute'] == $this->gift_product_attribute || !(int) $this->gift_product_attribute)) {
                    if (!isset(CartRule::$only_one_gift[$this->id . '-' . $this->gift_product])
                        || CartRule::$only_one_gift[$this->id . '-' . $this->gift_product] == $id_address
                        || CartRule::$only_one_gift[$this->id . '-' . $this->gift_product] == 0
                        || $id_address == 0
                        || !$use_cache) {
                        $reduction_value += Tools::ps_round($use_tax ? $product['price_wt'] : $product['price'], Context::getContext()->getComputingPrecision());
                        if ($use_cache && (!isset(CartRule::$only_one_gift[$this->id . '-' . $this->gift_product]) || CartRule::$only_one_gift[$this->id . '-' . $this->gift_product] == 0)) {
                            CartRule::$only_one_gift[$this->id . '-' . $this->gift_product] = $id_address;
                        }
                        break;
                    }
                }
            }
        }
        Cache::store($cache_id, $reduction_value);
        if ($use_tax && !empty($context->virtualTotalTaxIncluded)) {
            $context->virtualTotalTaxIncluded -= $reduction_value;
            if ($this->free_shipping) {
                $context->virtualTotalTaxIncluded += $reduction_carrier;
            }
        } elseif (!$use_tax && !empty($context->virtualTotalTaxExcluded)) {
            $context->virtualTotalTaxExcluded -= $reduction_value;
            if ($this->free_shipping) {
                $context->virtualTotalTaxExcluded += $reduction_carrier;
            }
        }
        return $reduction_value;
    }
}
