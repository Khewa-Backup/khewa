<?php
/**
 * khewacorechanges — Khewa PS 9.2: cart rule maths for "after tax" fixed amounts.
 *
 * CORE_CHANGES.md #2 and #18. A cart rule with a fixed amount, set "tax excluded"
 * (reduction_tax = 0), on the whole order, is how Khewa stores gift cards, vouchers,
 * credit slip codes and RockPOS "amount" discounts. Khewa wants that amount taken
 * off the tax-INCLUDED total as it is, like a payment:
 *   - a $10 voucher takes exactly $10 off the total (stock 9.2: $11.50, it adds tax);
 *   - a gift card can be bigger than the products total without tax, and then also
 *     covers the tax (up to the products total with tax).
 * The tax on the products is not lowered by such a discount.
 *
 * On the 1.7 site this lived in core files (classes/CartRule.php commit 99f2dc776,
 * src/Core/Cart/CartRuleCalculator.php + Calculator.php commit edd95ec6b). PrestaShop
 * 9.2 computes every cart and order total with these Calculator classes, so the fix
 * now lives here. Every other kind of cart rule goes to the stock 9.2 code untouched.
 *
 * Used through KhewaCalculator, which the module's Cart::newCalculator() override returns.
 */

namespace KhewaCoreChanges\Cart;

use CartRule;
use Currency;
use Hook;
use PrestaShop\PrestaShop\Core\Cart\AmountImmutable;
use PrestaShop\PrestaShop\Core\Cart\CartRuleCalculator;
use PrestaShop\PrestaShop\Core\Cart\CartRuleData;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagStateCheckerInterface;
use ReflectionProperty;

class KhewaCartRuleCalculator extends CartRuleCalculator
{
    /**
     * Build one from the stock instance, keeping its feature flag checker.
     */
    public static function fromCartRuleCalculator(CartRuleCalculator $stock)
    {
        $featureFlagManager = null;
        if (property_exists(CartRuleCalculator::class, 'featureFlagManager')) {
            $featureFlagManager = (new ReflectionProperty(CartRuleCalculator::class, 'featureFlagManager'))->getValue($stock);
        }

        return new static($featureFlagManager instanceof FeatureFlagStateCheckerInterface ? $featureFlagManager : null);
    }

    /**
     * Is this a fixed amount, "tax excluded", whole-order cart rule (Khewa: taken off after tax)?
     *
     * @param CartRule|object $cartRule
     */
    public static function isAfterTaxAmount($cartRule)
    {
        return (float) $cartRule->reduction_amount > 0
            && (float) $cartRule->reduction_percent == 0
            && !(bool) $cartRule->reduction_tax
            && (int) $cartRule->reduction_product === 0
            && (int) $cartRule->gift_product === 0;
    }

    /**
     * Khewa PS 9.2: after-tax fixed amounts are applied here; everything else by stock 9.2.
     */
    protected function applyCartRule(CartRuleData $cartRuleData, $withFreeShipping = true)
    {
        $cartRule = $cartRuleData->getCartRule();
        if (!CartRule::isFeatureActive() || !static::isAfterTaxAmount($cartRule)) {
            parent::applyCartRule($cartRuleData, $withFreeShipping);

            return;
        }

        $cart = $this->calculator->getCart();

        // Same module hook as stock 9.2: a module may still take over this rule.
        $isAppliedByModules = null;
        Hook::exec(
            'actionApplyCartRule',
            [
                'cart_rule_calculator' => $this,
                'cart_rule_data' => $cartRuleData,
                'cart_rule' => $cartRule,
                'cart' => $cart,
                'with_free_shipping' => $withFreeShipping,
                'is_applied_by_modules' => &$isAppliedByModules,
            ]
        );
        // @phpstan-ignore-next-line
        if ($isAppliedByModules) {
            return;
        }

        // Free shipping part: same as stock 9.2.
        if ($cartRule->free_shipping && $withFreeShipping) {
            $initialShippingFees = $this->calculator->getFees()->getInitialShippingFees();
            $this->calculator->getFees()->subDiscountValueShipping($initialShippingFees);
            $cartRuleData->addDiscountApplied($initialShippingFees);
        }

        $amount = $this->convertAmountBetweenCurrencies(
            $cartRule->reduction_amount,
            new Currency($cartRule->reduction_currency),
            new Currency($cart->id_currency)
        );

        // What is left to pay on the products, tax included (earlier rules already applied).
        $totalTaxIncl = 0;
        foreach ($this->cartRows as $cartRow) {
            $totalTaxIncl += $cartRow->getFinalTotalPrice()->getTaxIncluded();
        }
        // Khewa: the amount is taken off the tax-included total and may go past the
        // products total without tax (so a gift card also covers the tax), but not past
        // the products total with tax.
        $used = min($amount, $totalTaxIncl);
        if ($used <= 0) {
            return;
        }

        foreach ($this->cartRows as $cartRow) {
            $rowTaxIncl = $cartRow->getFinalTotalPrice()->getTaxIncluded();
            if ($rowTaxIncl <= 0) {
                continue;
            }
            $share = min($used * $rowTaxIncl / $totalTaxIncl, $rowTaxIncl);
            // Same value with and without tax: the discount does not lower the tax.
            $discount = new AmountImmutable($share, $share);
            $cartRow->applyFlatDiscount($discount);
            $cartRuleData->addDiscountApplied($discount);
        }
    }
}
