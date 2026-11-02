<?php
/**
 * khewacorechanges — Khewa PS 9.2: override of Order::getProductTaxesDetails().
 *
 * CORE_CHANGES.md #18 (gift card can cover the tax), the tax part. On the 1.7 site this
 * was an edit in classes/order/Order.php (commit edd95ec6b): a fixed-amount discount is
 * taken off after tax, so it must not lower the tax of the order.
 *
 * Stock 9.2 lowers the tax by (discounts with tax - discounts without tax). For Khewa's
 * after-tax amounts both values are the same, so most orders need nothing. The only
 * case that differs: a gift card bigger than the products total without tax. Then the
 * order's discount without tax stops at the products total, the two values differ, and
 * stock would take that gap off the tax (the invoice and the order_detail_tax table would
 * show too little tax). Here, only for that gap, the method is run with the gap removed;
 * all other discounts (percent etc.) still lower the tax as in stock 9.2. No core code
 * is copied: the stock method does all the work.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Order extends OrderCore
{
    /*
    * module: khewacorechanges
    * date: 2026-11-01 20:05:43
    * version: 1.0.0
    */
    public function getProductTaxesDetails($limitToOrderDetails = false)
    {
        $afterTaxValue = 0;
        $otherValueTaxExcl = 0;
        $hasAfterTax = false;
        foreach ($this->getCartRules() as $orderCartRule) {
            $cartRule = new CartRule((int) $orderCartRule['id_cart_rule']);
            if (Validate::isLoadedObject($cartRule)
                && (float) $cartRule->reduction_amount > 0
                && (float) $cartRule->reduction_percent == 0
                && !(bool) $cartRule->reduction_tax
                && (int) $cartRule->reduction_product === 0
                && (int) $cartRule->gift_product === 0
                && !(bool) $cartRule->free_shipping
            ) {
                $hasAfterTax = true;
                $afterTaxValue += (float) $orderCartRule['value'];
            } else {
                $otherValueTaxExcl += (float) $orderCartRule['value_tax_excl'];
            }
        }
        if (!$hasAfterTax) {
            return parent::getProductTaxesDetails($limitToOrderDetails);
        }
        $otherDiscountsTax = ((float) $this->total_discounts_tax_incl - $afterTaxValue) - $otherValueTaxExcl;
        $khewaTaxIncl = (float) $this->total_discounts_tax_excl + max(0, $otherDiscountsTax);
        if (abs($khewaTaxIncl - (float) $this->total_discounts_tax_incl) < 0.000001) {
            return parent::getProductTaxesDetails($limitToOrderDetails);
        }
        $saved = $this->total_discounts_tax_incl;
        $this->total_discounts_tax_incl = $khewaTaxIncl;
        try {
            return parent::getProductTaxesDetails($limitToOrderDetails);
        } finally {
            $this->total_discounts_tax_incl = $saved;
        }
    }
}
