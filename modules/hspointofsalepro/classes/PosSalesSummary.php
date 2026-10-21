<?php
/**
 * RockPOS Sales Summary
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class PosSalesSummary
{

    /**
     * @var string // refer to Validate::isModuleName
     */
    public $module_name;

    /**
     * @var string
     */
    public $date_from;

    /**
     * @var string
     */
    public $date_to;
    protected $date_format = 'Y-m-d';

    /**
     * @param string $module_name
     * @param string $date_from
     * @param string $date_to
     */
    public function __construct($module_name, $date_from, $date_to)
    {
        $this->module_name = Validate::isModuleName($module_name) ? $module_name : null;
        $this->date_from = $this->isDate($date_from, $this->date_format) ? $date_from : null;
        $this->date_to = $this->isDate($date_to, $this->date_format) ? $date_to : null;
    }

    /**
     * @param boolean $valid
     * @return \DbQuery
     */
    protected function getOrderReferencesQuery($valid = true)
    {
        $query = new DbQuery();
        $query->select('reference');
        $query->from('orders', 'o');
        $query->where('o.`module` = "' . pSQL($this->module_name) . '"');
        if ($valid) {
            $query->where('o.`valid` = 1' . Shop::addSqlRestriction(false, 'o'));
        }
        $query->where('o.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        return $query;
    }

    /**
     *
     * @return \DbQuery
     */
    protected function getOrderPaymentReferencesQuery()
    {
        $query = new DbQuery();
        $query->select('DISTINCT(op.`order_reference`)');
        $query->from('orders', 'o');
        $query->leftJoin('order_payment', 'op', 'o.`reference` = op.`order_reference`');
        $query->where('o.`module` = "' . pSQL($this->module_name) . '"');
        $query->where('o.`valid` = 1' . Shop::addSqlRestriction(false, 'o'));
        $query->where('op.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        return $query;
    }

    /**
     *
     * @return array
     * <pre>
     * array(
     *       total_real_paid => float,
     * )
     */
    public function getTotalRealPaidSummary()
    {
        $query = new DbQuery();
        $query->select('IFNULL(SUM(op.`amount` / op.`conversion_rate`), 0) AS `total_real_paid`');
        $query->from('order_payment', 'op');
        $query->where('op.`order_reference` IN (' . $this->getOrderPaymentReferencesQuery()->build() . ')');
        $query->where('op.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        return Db::getInstance()->getValue($query);
    }

    /**
     *
     * @param string $type_of_id
     * @return DbQuery $query
     */
    public function getReturnedOrderIds()
    {
        $query = new DbQuery();
        $query->select("IFNULL(GROUP_CONCAT(CONCAT(id_returned_order,','),id_new_order), 0) AS id_returned_order");
        $query->from('pos_returned_order', 'ro');
        $query->where('ro.`date_add` < "' . pSQL($this->date_from) . ' 00:00:00" OR ro.`date_add` > "' . pSQL($this->date_to) . ' 23:59:59"');
        $id_orders = Db::getInstance()->getRow($query);
        if (!empty($id_orders)) {
            foreach ($id_orders as $key => $value) {
                if (substr($value, -1, 1) == ',') {
                    $id_orders[$key] = substr($value, 0, -1);
                }
            }
        }
        return $id_orders;
    }

    /**
     *
     * @param bool $valid
     */
    public function getOrdersSummary($valid = false)
    {
        $not = $valid ? 'NOT' : '';
        $valid_order = (int)$valid;
        $returned_order_ids = $this->getReturnedOrderIds();
        $query = new DbQuery();
        $query->select('o.`total_paid_tax_incl`');
        $query->select('o.`total_paid_tax_excl`');
        $query->select('o.`conversion_rate`');
        $query->select('o.`id_order`');
        $query->select('SUM(od.`total_price_tax_excl`) AS `total_price_tax_excl`');
        $query->select('COUNT(o.`id_order`) AS `total_transaction`');
        $query->select('SUM(od.`product_quantity`) AS `sold_items`');
        $query->from('orders', 'o');
        $query->leftJoin('order_detail', 'od', 'o.`id_order` = od.`id_order`');
        $query->where('o.`id_order` ' . $not . ' IN (' . $returned_order_ids['id_returned_order'] . ')');
        $query->where('o.`valid` = ' . $valid_order);
        $query->where('o.`module` = "' . pSQL($this->module_name) . '"');
        $query->where('o.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        $query->groupBy('o.`id_order`');
        return $query->build();
    }


    /**
     *
     * @return array
     * <pre>
     * array(
     *       total_tax_incl    => float,
     *       total_tax_excl    => float,
     *       total_real_paid   => float,
     *       refund            => float,
     *       total_products    => int,
     *       total_transaction => int,
     *       sold_items        => int
     * )
     */
    public function getSalesSummary_old()
    {
        $sql = 'SELECT
                  IFNULL(SUM(o.`total_paid_tax_incl` / o.`conversion_rate`), 0) AS `total_tax_incl`,
                  IFNULL(SUM(o.`total_paid_tax_excl` / o.`conversion_rate`), 0) AS `total_tax_excl`,
                  IFNULL(SUM(o.`total_price_tax_excl` / o.`conversion_rate`), 0) AS `total_products`,
                  COUNT(o.`id_order`) AS `total_transaction`,
                  IFNULL(SUM(`sold_items`), 0) AS `sold_items`
                FROM (' . $this->getOrdersSummary(true) . ' UNION ' . $this->getOrdersSummary() . ') AS o';

        $sales = Db::getInstance()->getRow($sql);
        $sales['total_real_paid'] = $this->getTotalRealPaidSummary();
        $sales['refund'] = abs($this->getRefundSummary());
        return $sales;
    }
    
    public function getSalesSummary()
    {
        $sql = 'SELECT
                  IFNULL(SUM(o.`total_paid_tax_incl` / o.`conversion_rate`), 0) AS `total_tax_incl`,
                  IFNULL(SUM(o.`total_paid_tax_excl` / o.`conversion_rate`), 0) AS `total_tax_excl`,
                  IFNULL(SUM(o.`total_price_tax_excl` / o.`conversion_rate`), 0) AS `total_products`,
                  COUNT(o.`id_order`) AS `total_transaction`,
                  IFNULL(SUM(`sold_items`), 0) AS `sold_items`
                FROM (' . $this->getOrdersSummary(true) . ') AS o';
        $sales = Db::getInstance()->getRow($sql);
        $sales['total_real_paid'] = $this->getTotalRealPaidSummary();
        $sales['refund'] = abs($this->getRefundSummary());
        return $sales;
    }


    /**
     * @param boolean $valid
     * @return float
     */
    public function getShippingCostSummary($valid = true)
    {
        $query = new DbQuery();
        $query->select('IFNULL(SUM(o.`total_shipping_tax_excl` / o.`conversion_rate`), 0) AS `shipping_cost`');
        $query->from('orders', 'o');
        $query->leftJoin('order_cart_rule', 'ocr', '(o.`id_order` = ocr.`id_order`)');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery($valid)->build() . ')');
        $query->where('ocr.`free_shipping` IS NULL');
        if ($valid) {
            $query->where('o.`valid` = 1');
        }
        return (float)Db::getInstance()->getValue($query);
    }

    /**
     *
     * @return array
     * <pre>
     * array(
     *    array (
     *      amount => float,
     *      name => string
     *    )
     * .....
     * )
     */
    public function getPaymentSummary()
    {
        $query = new DbQuery();
        $query->select('SUM(op.`amount` / op.`conversion_rate`) as `amount`');
        $query->select('payment_method as `name`');
        $query->from('order_payment', 'op');
        $query->where('op.`order_reference` IN (' . $this->getOrderPaymentReferencesQuery()->build() . ')');
        $query->where('op.`amount` > 0 ');
        $query->where('op.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        $query->groupBy('op.`payment_method`');
        return Db::getInstance()->executeS($query);
    }

    /**
     *
     * @return array
     * <pre>
     * array(
     *  array(
     *      total => string,
     *      iso_code => string,
     *      sign => string
     *  ),
     *  ...................
     * )
     */
    public function getCurrencySummary()
    {
        $query = new DbQuery();
        $query->select('SUM(op.`amount`) AS `total`');
        $query->select('c.`id_currency`');
        $query->select('c.`iso_code`');
        $query->from('order_payment', 'op');
        $query->leftJoin('currency', 'c', 'op.`id_currency` = c.`id_currency`');
        $query->where('op.`order_reference` IN (' . $this->getOrderPaymentReferencesQuery()->build() . ')');
        $query->where('op.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        $query->groupBy('op.`id_currency`');
        $currency_summary = Db::getInstance()->executeS($query);
        foreach ($currency_summary as &$value) {
            $currency = new Currency($value['id_currency']);
            $value['sign'] = $currency->sign;
            $value['total'] = PosTools::posDisplayPrice($value['total'], $currency, false);
            unset($value['id_currency']);
        }
        return $currency_summary;
    }

    /**
     * @param int $id_lang
     * @return array
     * <pre>
     * array(
     *  0 => array(
     *      id_category_default => int,
     *      name => string,
     *      total_price => float
     *  ),
     *  ..........
     * )
     */
    public function getCategorySummary($id_lang)
    {
        $query = new DbQuery();
        $query->select('p.`id_category_default`');
        $query->select('SUM(od.`total_price_tax_excl` / o.`conversion_rate`) AS `total_price`');
        $query->from('product', 'p');
        $query->leftJoin('order_detail', 'od', 'od.`product_id` = p.`id_product`');
        $query->leftJoin('orders', 'o', 'o.`id_order` = od.`id_order`');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ')');
        $query->where('o.`valid`= 1');
        $query->groupBy('p.`id_category_default`');
        $sql = 'SELECT  DISTINCT p.`id_category_default`, cl.`name`, p.`total_price`
                    FROM `' . _DB_PREFIX_ . 'category_lang` cl
                INNER JOIN
                    (' . $query->build() . ') p
                    ON cl.`id_category` = p.`id_category_default`
                WHERE cl.`id_lang` = ' . (int)$id_lang . '
                ORDER BY p.`total_price` DESC';
        return Db::getInstance()->executeS($sql);
    }


    /**
     *
     * @return float
     */
    public function getTotalSummary()
    {
        $sql = "SELECT
                SUM(total_paid_real)
                FROM
                (
                    SELECT
                            o.`total_paid_real`
                    FROM
                            " . _DB_PREFIX_ . "orders o
                    WHERE
                            o.`date_add` BETWEEN '" . pSQL($this->date_from) . " 00:00:00'  AND '" . pSQL($this->date_to) . " 23:59:59'
                    GROUP BY
                            o.`reference`
                ) as x";
        return Db::getInstance()->getValue($sql);
    }

    /**
     *
     * @return float
     */
    public function getRefundSummary()
    {
        $query = new DbQuery();
        $query->select('IFNULL(SUM(op.`amount` / op.`conversion_rate`), 0)');
        $query->from('order_payment', 'op');
        $query->where('op.`date_add` BETWEEN "' . pSQL($this->date_from) . ' 00:00:00" AND "' . pSQL($this->date_to) . ' 23:59:59"');
        $query->where('op.`amount` < 0');
        return Db::getInstance()->getValue($query);
    }

    /**
     *
     * @return array
     * <pre>
     * array (
     *   array(
     *      total => float,
     *      rate => float
     *   ),
     *   ................
     * )
     */
    public function getTaxSummary_Old()
    {
        $query = new DbQuery();
        $query->select('SUM((od.`total_price_tax_incl` - od.`total_price_tax_excl`)  /  o.`conversion_rate`) AS `total`');
        $query->select('t.`rate`', 'rate');
        $query->from('order_detail', 'od');
        $query->innerJoin('orders', 'o', 'o.`id_order` = od.`id_order`');
        $query->innerJoin('order_detail_tax', 'odt', 'odt.`id_order_detail` = od.`id_order_detail`');
        $query->innerJoin('tax', 't', 't.`id_tax` = odt.`id_tax`');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ')');
        $query->where('o.`valid` = 1');
        $query->groupBy('t.`rate`');
        return Db::getInstance()->executeS($query);
    }
    
    public function getTaxSummary()
    {
        $tax_summary = array();

        // Get product taxes (only active taxes)
        $product_taxes = $this->getProductTaxes();
        $this->mergeTaxesIntoSummary($tax_summary, $product_taxes);
        $active_rates = $this->getActiveRates($product_taxes);

        // Get and merge shipping taxes
        $shipping_taxes = $this->getShippingTaxes();
        $this->mergeShippingTaxes($tax_summary, $shipping_taxes, $active_rates);

        // Get and distribute wrapping/discount taxes
        $order_taxes = $this->getOrderWrappingDiscountTaxes();
        $this->distributeWrappingDiscountTaxes($tax_summary, $order_taxes);

        // Adjust to match expected total
        $this->adjustTaxSummaryToMatchTotal($tax_summary);

        // Convert to indexed array and sort
        return $this->formatTaxSummaryResult($tax_summary);
    }

    /**
     * Get product taxes from order_detail (only active taxes)
     */
    protected function getProductTaxes()
    {
        $query = new DbQuery();
        $query->select('SUM(odt.`total_amount` / o.`conversion_rate`) AS `total`, t.`rate`');
        $query->from('order_detail', 'od');
        $query->innerJoin('orders', 'o', 'o.`id_order` = od.`id_order`');
        $query->innerJoin('order_detail_tax', 'odt', 'odt.`id_order_detail` = od.`id_order_detail`');
        $query->innerJoin('tax', 't', 't.`id_tax` = odt.`id_tax`');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ')');
        $query->where('o.`valid` = 1 AND t.`active` = 1');
        $query->groupBy('t.`rate`');
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get shipping taxes from orders
     */
    protected function getShippingTaxes()
    {
        $query = new DbQuery();
        $query->select('SUM((o.`total_shipping_tax_incl` - o.`total_shipping_tax_excl`) / o.`conversion_rate`) AS `total`, o.`carrier_tax_rate` AS `rate`');
        $query->from('orders', 'o');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ')');
        $query->where('o.`valid` = 1 AND (o.`total_shipping_tax_incl` - o.`total_shipping_tax_excl`) != 0');
        $query->groupBy('o.`carrier_tax_rate`');
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get wrapping and discount taxes per order
     */
    protected function getOrderWrappingDiscountTaxes()
    {
        $query = new DbQuery();
        $query->select('o.`id_order`, (o.`total_wrapping_tax_incl` - o.`total_wrapping_tax_excl`) / o.`conversion_rate` AS `wrapping_tax`, (o.`total_discounts_tax_incl` - o.`total_discounts_tax_excl`) / o.`conversion_rate` AS `discount_tax`');
        $query->from('orders', 'o');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ')');
        $query->where('o.`valid` = 1');
        return Db::getInstance()->executeS($query);
    }

    /**
     * Merge taxes into summary array
     */
    protected function mergeTaxesIntoSummary(&$tax_summary, $taxes)
    {
        if ($taxes) {
            foreach ($taxes as $tax) {
                $rate = (float)$tax['rate'];
                if (!isset($tax_summary[$rate])) {
                    $tax_summary[$rate] = array('total' => 0, 'rate' => $rate);
                }
                $tax_summary[$rate]['total'] += (float)$tax['total'];
            }
        }
    }

    /**
     * Get list of active tax rates
     */
    protected function getActiveRates($product_taxes)
    {
        $active_rates = array();
        if ($product_taxes) {
            foreach ($product_taxes as $tax) {
                $active_rates[(float)$tax['rate']] = true;
            }
        }
        return $active_rates;
    }

    /**
     * Merge shipping taxes into summary
     */
    protected function mergeShippingTaxes(&$tax_summary, $shipping_taxes, $active_rates)
    {
        if (!$shipping_taxes) {
            return;
        }

        foreach ($shipping_taxes as $tax) {
            $rate = (float)$tax['rate'];
            $amount = (float)$tax['total'];

            if (isset($active_rates[$rate])) {
                // Rate matches active product tax rate
                if (!isset($tax_summary[$rate])) {
                    $tax_summary[$rate] = array('total' => 0, 'rate' => $rate);
                }
                $tax_summary[$rate]['total'] += $amount;
            } else {
                // Distribute proportionally to active rates
                $this->distributeTaxProportionally($tax_summary, $amount);
            }
        }
    }

    /**
     * Distribute wrapping and discount taxes
     */
    protected function distributeWrappingDiscountTaxes(&$tax_summary, $order_taxes)
    {
        if (!$order_taxes) {
            return;
        }

        foreach ($order_taxes as $order_tax) {
            $id_order = (int)$order_tax['id_order'];
            $wrapping_tax = (float)$order_tax['wrapping_tax'];
            $discount_tax = (float)$order_tax['discount_tax'];

            if ($wrapping_tax == 0 && $discount_tax == 0) {
                continue;
            }

            // Get product tax rates for this order
            $order_product_taxes = $this->getOrderProductTaxes($id_order);

            if ($order_product_taxes) {
                $order_total = 0;
                foreach ($order_product_taxes as $opt) {
                    $order_total += (float)$opt['total'];
                }
                if ($order_total > 0) {
                    // Distribute based on order's product tax rates
                    foreach ($order_product_taxes as $opt) {
                        $rate = (float)$opt['rate'];
                        $proportion = (float)$opt['total'] / $order_total;
                        $this->addToTaxSummary($tax_summary, $rate, $wrapping_tax * $proportion);
                        $this->addToTaxSummary($tax_summary, $rate, $discount_tax * $proportion);
                    }
                    continue;
                }
            }

            // Fallback: distribute to all active rates proportionally
            $this->distributeTaxProportionally($tax_summary, $wrapping_tax);
            $this->distributeTaxProportionally($tax_summary, $discount_tax);
        }
    }

    /**
     * Get product tax rates for a specific order
     */
    protected function getOrderProductTaxes($id_order)
    {
        $query = new DbQuery();
        $query->select('SUM(odt.`total_amount` / o.`conversion_rate`) AS `total`, t.`rate`');
        $query->from('order_detail', 'od');
        $query->innerJoin('orders', 'o', 'o.`id_order` = od.`id_order`');
        $query->innerJoin('order_detail_tax', 'odt', 'odt.`id_order_detail` = od.`id_order_detail`');
        $query->innerJoin('tax', 't', 't.`id_tax` = odt.`id_tax`');
        $query->where('o.`id_order` = ' . (int)$id_order . ' AND t.`active` = 1');
        $query->groupBy('t.`rate`');
        return Db::getInstance()->executeS($query);
    }

    /**
     * Add tax amount to summary for a specific rate
     */
    protected function addToTaxSummary(&$tax_summary, $rate, $amount)
    {
        if ($amount == 0) {
            return;
        }
        if (!isset($tax_summary[$rate])) {
            $tax_summary[$rate] = array('total' => 0, 'rate' => $rate);
        }
        $tax_summary[$rate]['total'] += $amount;
    }

    /**
     * Distribute tax amount proportionally to all rates in summary
     */
    protected function distributeTaxProportionally(&$tax_summary, $amount)
    {
        if ($amount == 0 || empty($tax_summary)) {
            return;
        }

        $total = 0;
        foreach ($tax_summary as $tax_data) {
            $total += $tax_data['total'];
        }
        if ($total <= 0) {
            return;
        }

        foreach ($tax_summary as $rate => &$tax_data) {
            $tax_data['total'] += $amount * ($tax_data['total'] / $total);
        }
        unset($tax_data);
    }

    /**
     * Adjust tax summary to match expected total from orders
     */
    protected function adjustTaxSummaryToMatchTotal(&$tax_summary)
    {
        $expected_total = $this->getExpectedTotalTax();
        $actual_total = 0;
        foreach ($tax_summary as $tax_data) {
            $actual_total += $tax_data['total'];
        }

        if (abs($expected_total - $actual_total) > 0.01 && $expected_total != 0 && $actual_total != 0) {
            $factor = $expected_total / $actual_total;
            foreach ($tax_summary as &$tax_data) {
                $tax_data['total'] *= $factor;
            }
            unset($tax_data);
        }
    }

    /**
     * Get expected total tax from orders
     */
    protected function getExpectedTotalTax()
    {
        $query = new DbQuery();
        $query->select('SUM((o.`total_paid_tax_incl` - o.`total_paid_tax_excl`) / o.`conversion_rate`) AS `total_tax`');
        $query->from('orders', 'o');
        $query->where('o.`reference` IN (' . $this->getOrderReferencesQuery()->build() . ') AND o.`valid` = 1');
        return (float)Db::getInstance()->getValue($query);
    }

    /**
     * Format tax summary result
     */
    protected function formatTaxSummaryResult($tax_summary)
    {
        $result = array_values($tax_summary);
        usort($result, function ($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });
        return $result;
    }

    /**
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    protected function isDate($date, $format = 'Y-m-d')
    {
        return date($format, strtotime($date)) == $date;
    }
}
