<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

/**
 * Extended Tools for RockPOS.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
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
                    if (!empty($tax->name)) {
                        $result[$tax->id] = [
                            'name' => $tax->name[$id_lang],
                            'tax_rate' => (float) $tax->rate,
                            'id' => $tax->id,
                            'active' => $tax->active,
                            'tax_amount' => Tools::ps_round($amount, PosConstants::POS_PRICE_COMPUTE_PRECISION),
                        ];
                    }
                }
            }
        } else {
            // combine
            foreach ($taxes as &$tax) {
                if (isset($result[$tax->id])) {
                    $result[$tax->id]['tax_amount'] = $result[$tax->id]['tax_amount'] + $price * (abs($tax->rate) / 100);
                } else {
                    if (!empty($tax->name)) {
                        $result[$tax->id] = [
                            'name' => $tax->name[$id_lang],
                            'tax_rate' => (float) $tax->rate,
                            'id' => $tax->id,
                            'active' => $tax->active,
                            'tax_amount' => Tools::ps_round($price * (abs($tax->rate) / 100), PosConstants::POS_PRICE_COMPUTE_PRECISION),
                        ];
                    }
                }
            }
        }

        foreach ($result as &$item) {
            $item['tax_amount'] = Tools::ps_round($item['tax_amount'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
        }
        return $result;
    }
}
