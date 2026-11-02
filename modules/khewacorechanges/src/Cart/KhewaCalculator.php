<?php
/**
 * khewacorechanges — Khewa PS 9.2: cart calculator that uses Khewa's cart rule maths.
 *
 * CORE_CHANGES.md #2 and #18. The module's Cart::newCalculator() override builds the
 * stock 9.2 calculator (all products and cart rules filled in by core code) and hands
 * it to fromCalculator(), which returns this class with the same content. Only two
 * things differ from stock 9.2:
 *   1. cart rules are applied by KhewaCartRuleCalculator (after-tax fixed amounts);
 *   2. getDiscountTotal(): the "discount can't be more than the cart" limit is checked
 *      for the with-tax and the without-tax total separately. An after-tax gift card can
 *      be bigger than the products total without tax; that total then stops at 0
 *      (like the 1.7 site), while the with-tax total still drops by the full card.
 *      (Stock compares the two totals as one object, which would leave the without-tax
 *      total below 0 and stop the order from being saved.)
 * Carts without such a cart rule give exactly the stock 9.2 numbers.
 */

namespace KhewaCoreChanges\Cart;

use PrestaShop\PrestaShop\Core\Cart\AmountImmutable;
use PrestaShop\PrestaShop\Core\Cart\Calculator;
use ReflectionClass;

class KhewaCalculator extends Calculator
{
    /**
     * Same calculator, Khewa class. Copies the state of the stock instance (we are a
     * subclass, so its protected properties are readable here); no core code is copied.
     */
    public static function fromCalculator(Calculator $stock)
    {
        /** @var static $calculator */
        $calculator = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();
        foreach (get_object_vars($stock) as $name => $value) {
            $calculator->$name = $value;
        }
        $calculator->cartRuleCalculator = KhewaCartRuleCalculator::fromCartRuleCalculator($stock->cartRuleCalculator);

        return $calculator;
    }

    /**
     * Stock 9.2 total of all discounts, then each part (with / without tax) limited on its own.
     */
    public function getDiscountTotal()
    {
        $amount = parent::getDiscountTotal();

        $hasAfterTaxAmount = false;
        foreach ($this->cartRules as $cartRuleData) {
            if (KhewaCartRuleCalculator::isAfterTaxAmount($cartRuleData->getCartRule())) {
                $hasAfterTaxAmount = true;
                break;
            }
        }
        if (!$hasAfterTaxAmount) {
            return $amount;
        }

        // Same limit as stock 9.2: products before discounts + the shipping discount.
        $allowedMaxDiscount = $this->getRowTotalWithoutDiscount();
        if (null !== $this->getFees()->getFinalShippingFees()) {
            $allowedMaxDiscount = $allowedMaxDiscount
                ->add($this->getFees()->getInitialShippingFees())
                ->sub($this->getFees()->getFinalShippingFees());
        }

        return new AmountImmutable(
            min($amount->getTaxIncluded(), $allowedMaxDiscount->getTaxIncluded()),
            min($amount->getTaxExcluded(), $allowedMaxDiscount->getTaxExcluded())
        );
    }
}
