<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Extended Tools for RockPOS.
 */
class PosTax extends Tax
{

    public static function getProductsTaxDetail($products = [])
    {
    }

    /**
     * @param TaxCalculator $tax_calculator
     * @param $price
     * @param int $quantity
     * @param $id_lang
     * @return array
     */
    public static function getProductTaxDetail(TaxCalculator $tax_calculator, $price, $quatity, $id_lang)
    {
        $taxes = (array) $tax_calculator->taxes;
        $result = array();
        if ($tax_calculator->computation_method == TaxCalculator::ONE_AFTER_ANOTHER_METHOD) {
            // apply after another
            $total = $price;
            foreach ($taxes as $tax) {
                $amount = $total * (abs($tax->rate) / 100);
                $total += $amount;
                if (isset($result[$tax->id])) {
                    $result[$tax->id]['tax_amount'] = Tools::ps_round($result[$tax->id]['tax_amount'] + $amount, PosConstants::POS_PRICE_COMPUTE_PRECISION);
                } else {
                    $result[$tax->id] = [
                        'name' => $tax->name[$id_lang],
                        'tax_rate' => (float) $tax->rate,
                        'id' => $tax->id,
                        'active' => $tax->active,
                        'tax_amount' => Tools::ps_round($amount, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                    ];
                }
            }
        } else {
            // combine
            foreach ($taxes as &$tax) {
                if (isset($result[$tax->id])) {
                    $result[$tax->id]['tax_amount'] = $result[$tax->id]['tax_amount'] + $price * (abs($tax->rate) / 100);
                } else {
                    $taxAmount = strlen($tax->rate) > 4 && substr($tax->rate, -1) != 0 ? self::roundDown($price * (abs(self::roundDown($tax->rate, 2))) / 100, 2) : $price * (abs($tax->rate)) / 100;

                    $result[$tax->id] = [
                        'name' => $tax->name[$id_lang],
                        'tax_rate' => (float) $tax->rate,
                        'id' => $tax->id,
                        'active' => $tax->active,
                        'tax_amount' => Tools::ps_round($taxAmount, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                    ];
                }
            }
        }

        foreach ($result as &$item) {
            $item['tax_amount'] = Tools::ps_round($item['tax_amount'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
        }
        return $result;
    }

    public static function roundDown($decimal, $precision)
    {
        $sign = $decimal > 0 ? 1 : -1;
        $base = pow(10, $precision);
        return floor(abs($decimal) * $base) / $base * $sign;
    }
}
