<?php
/**
 * khewacorechanges — Khewa PS 9.2: override of Cart::newCalculator().
 *
 * CORE_CHANGES.md #2 and #18 (after-tax fixed amounts: $10 voucher = $10 off,
 * gift card can cover the tax). PrestaShop 9.2 computes every cart and order total
 * through Cart::newCalculator(); this returns Khewa's calculator instead of stock.
 * The stock method still builds it (parent::newCalculator()), so no core code is copied.
 *
 * The root override/classes/Cart.php already belongs to RockPOS. PrestaShop merges this
 * method into that file on install (RockPOS doesn't override newCalculator()), and takes
 * it out again on uninstall. When merged, PrestaShop drops comments and `use` lines,
 * so this file uses full class names only.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Cart extends CartCore
{
    public function newCalculator($products, $cartRules, $id_carrier = null, $computePrecision = null, bool $keepOrderPrices = false)
    {
        $calculator = parent::newCalculator($products, $cartRules, $id_carrier, $computePrecision, $keepOrderPrices);

        // Khewa PS 9.2: hand the filled stock calculator to the module's class.
        $dir = _PS_MODULE_DIR_ . 'khewacorechanges/src/Cart/';
        if (!class_exists('KhewaCoreChanges\Cart\KhewaCalculator')) {
            if (!is_file($dir . 'KhewaCalculator.php') || !is_file($dir . 'KhewaCartRuleCalculator.php')) {
                return $calculator;
            }
            require_once $dir . 'KhewaCartRuleCalculator.php';
            require_once $dir . 'KhewaCalculator.php';
        }

        return \KhewaCoreChanges\Cart\KhewaCalculator::fromCalculator($calculator);
    }
}
