<?php
/**
 * KhewaReportsData - Centralized data fetching for Khewa Reports
 * 
 * This class handles all database queries for reports in a clean, maintainable way.
 * Key principle: Sales are based on order_date, Refunds are based on refund_date (from order_slip)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}



class KhewaReportsData
{
    protected $date_from;
    protected $date_to;
    protected $id_lang;
    protected $id_shop;
    protected $posModules;  // Cached POS module names
    
    // Tax IDs (Canadian specific)
    const TAX_ID_GST = 1;  // 5% Federal GST
    const TAX_IDS_QST = '25, 34, 32, 31, 28';  // Quebec QST 9.975%
    
    /**
     * Constructor
     * @param string $date_from Start date (Y-m-d)
     * @param string $date_to End date (Y-m-d)
     */
    public function __construct($date_from, $date_to)
    {
        $this->date_from = pSQL($date_from) . ' 00:00:00';
        $this->date_to = pSQL($date_to) . ' 23:59:59';
        $this->id_lang = (int)Context::getContext()->language->id;
        $this->id_shop = (int)Context::getContext()->shop->id;
        
        // Cache POS module names from configuration
        $patterns = Khewareports::getPaymentMethodPatterns();
        $this->posModules = $patterns['pos_module'];
    }
    
    /**
     * Get POS module SQL condition (for in-store detection)
     * @param string $column Column name (e.g., 'o.module')
     * @return string SQL condition
     */
    protected function getPosModuleCondition($column)
    {
        if (empty($this->posModules)) {
            return $column . ' = "hspointofsalepro"'; // Fallback
        }
        
        $conditions = array();
        foreach ($this->posModules as $module) {
            $conditions[] = $column . ' = "' . pSQL($module) . '"';
        }
        
        return '(' . implode(' OR ', $conditions) . ')';
    }
    
    /**
     * Get NOT POS module SQL condition (for online detection)
     * @param string $column Column name (e.g., 'o.module')
     * @return string SQL condition
     */
    protected function getNotPosModuleCondition($column)
    {
        if (empty($this->posModules)) {
            return $column . ' != "hspointofsalepro"'; // Fallback
        }
        
        $conditions = array();
        foreach ($this->posModules as $module) {
            $conditions[] = $column . ' != "' . pSQL($module) . '"';
        }
        
        return '(' . implode(' AND ', $conditions) . ')';
    }
    
    /**
     * Check if a module is a POS module
     * @param string $module Module name
     * @return bool
     */
    protected function isPosModule($module)
    {
        if (empty($this->posModules)) {
            return $module === 'hspointofsalepro'; // Fallback
        }
        
        return in_array($module, $this->posModules);
    }
    
    /**
     * Check if a payment method should be excluded from TOTAL calculations
     * Gift Card, Voucher, Credit Slip, Discount, Refund are NOT actual cash payments
     * They should be excluded from the TOTAL ONLINE / TOTAL IN-STORE sums
     * @param string $paymentMethod Payment method name
     * @return bool True if should be excluded from total
     */
    protected function isExcludedFromTotal($paymentMethod)
    {
        $paymentLower = strtolower(trim($paymentMethod));
        
        // Direct keyword check - these should ALWAYS be excluded
        // These are not actual cash payments, they reduce order totals
        $excludedKeywords = array(
            'gift',           // Gift Card, Gift card, InStore Gift Card
            'cadeau',         // Carte Cadeau (French)
            'voucher',        // Voucher
            'credit slip',    // Credit Slip
            'slip',           // Any slip reference
            'discount',       // Discount payment method
            'refund',         // Refund payment method
            'bon',            // French for voucher/coupon
            'coupon',         // Coupon
            'promo',          // Promotional
            'reduction'       // Reduction/discount
        );
        
        foreach ($excludedKeywords as $keyword) {
            if (strpos($paymentLower, $keyword) !== false) {
                return true;
            }
        }
        
        // Also check against configured patterns (user-defined patterns)
        $patterns = Khewareports::getPaymentMethodPatterns();
        
        // Check against Gift Card patterns
        if (!empty($patterns['gift_card'])) {
            foreach ($patterns['gift_card'] as $pattern) {
                $patternLower = strtolower(trim($pattern));
                if (!empty($patternLower) && strpos($paymentLower, $patternLower) !== false) {
                    return true;
                }
            }
        }
        
        // Check against Voucher patterns
        if (!empty($patterns['voucher'])) {
            foreach ($patterns['voucher'] as $pattern) {
                $patternLower = strtolower(trim($pattern));
                if (!empty($patternLower) && strpos($paymentLower, $patternLower) !== false) {
                    return true;
                }
            }
        }
        
        // Check against Credit Slip patterns
        if (!empty($patterns['credit_slip'])) {
            foreach ($patterns['credit_slip'] as $pattern) {
                $patternLower = strtolower(trim($pattern));
                if (!empty($patternLower) && strpos($paymentLower, $patternLower) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }


    /**
     * SQL fragment: cart-rule line amount aligned with invoice / PrestaShop rules.
     * - Percentage reduction: use value_tax_excl (discount applied on tax base).
     * - Fixed reduction: use value when reduction_tax applies (amount tax-incl), else value_tax_excl.
     *
     * @param string $ocrAlias ps_order_cart_rule alias
     * @param string $crAlias ps_cart_rule alias
     * @return string
     */
    protected function getSqlOrderCartRuleLineAmountSql($ocrAlias, $crAlias)
    {
        return 'CASE '
            . 'WHEN IFNULL(' . $crAlias . '.reduction_percent, 0) > 0 THEN ' . $ocrAlias . '.value_tax_excl '
            . 'WHEN IFNULL(' . $crAlias . '.reduction_amount, 0) > 0 THEN '
            . '(CASE WHEN IFNULL(' . $crAlias . '.reduction_tax, 0) = 1 THEN ' . $ocrAlias . '.value ELSE ' . $ocrAlias . '.value_tax_excl END) '
            . 'ELSE ' . $ocrAlias . '.value_tax_excl END';
    }

    /**
     * Get Sales Data - Orders within date range based on ORDER DATE
     */
    public function getSalesData()
    {
        $excludedStates = Khewareports::getSalesExcludedStatesSQL();
        $refundStates = Khewareports::getRefundStatesSQL();

        $ocrLineAmt = $this->getSqlOrderCartRuleLineAmountSql('ocr', 'cr');
        
        // Same payment method normalization as SBPM (Credit Card, Cash, Interac, etc.)
        $paymentMethodCase = Khewareports::buildPaymentMethodCase('op.payment_method');
        
        // English language id for order state name
        $id_lang_en = (int)Language::getIdByIso('en');
        if (!$id_lang_en) {
            $id_lang_en = 1;
        }
        
        $sql = '
        SELECT 
            o.id_order,
            o.reference,
            DATE_FORMAT(o.date_add, "%Y-%m-%d") as order_date,
            o.invoice_number,
            o.payment,
            o.module as payment_module,
            o.current_state,
            osl.name as order_state_name,
            IFNULL(osl_en.name, osl.name) as order_state_name_en,
            o.total_paid_tax_incl,
            o.total_paid_tax_excl,
            o.total_products_wt as total_products_tax_incl,
            o.total_products as total_products_tax_excl,
            o.total_shipping_tax_incl,
            o.total_shipping_tax_excl,
            o.total_discounts_tax_incl,
            o.total_discounts_tax_excl,
            od.id_order_detail,
            od.product_name,
            od.product_quantity,
            od.product_quantity_refunded,
            od.unit_price_tax_incl,
            od.unit_price_tax_excl,
            od.total_price_tax_incl,
            od.total_price_tax_excl,
            od.total_refunded_tax_incl,
            od.total_refunded_tax_excl,
            IFNULL(gst.unit_amount, 0) as gst_unit_amount,
            IFNULL(gst.total_amount, 0) as gst_total_amount,
            IFNULL(qst.unit_amount, 0) as qst_unit_amount,
            IFNULL(qst.total_amount, 0) as qst_total_amount,
            IFNULL(gst_ship.amount, 0) as shipping_gst_amount,
            IFNULL(qst_ship.amount, 0) as shipping_qst_amount,
            IFNULL(ocr.total_cart_rule_value, 0) as voucher_value,
            ocr.voucher_names,
            IFNULL(ocr.gift_card_value, 0) as gift_card_amount,
            IFNULL(ocr.voucher_only_value, 0) as voucher_amount_sales,
            GREATEST(
                IFNULL(ocr.total_cart_rule_value, 0)
                - IFNULL(ocr.gift_card_value, 0)
                - IFNULL(ocr.voucher_only_value, 0)
                - IFNULL(ocr.discount_value, 0),
                0
            ) as credit_slip_amount,
            IFNULL(slip.total_refund_tax_incl, 0) as total_refund_tax_incl,
            IFNULL(slip.refund_date, "") as refund_date,
            dcl.name as delivery_country,
            ds.name as delivery_state,
            IFNULL(pb.payment_breakdown, "") as payment_breakdown
            
        FROM ' . _DB_PREFIX_ . 'orders o
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl 
            ON o.current_state = osl.id_order_state AND osl.id_lang = ' . $this->id_lang . '
        LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl_en 
            ON o.current_state = osl_en.id_order_state AND osl_en.id_lang = ' . $id_lang_en . '
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail_tax gst 
            ON od.id_order_detail = gst.id_order_detail AND gst.id_tax = ' . self::TAX_ID_GST . '
        LEFT JOIN (
            SELECT id_order_detail, SUM(unit_amount) as unit_amount, SUM(total_amount) as total_amount
            FROM ' . _DB_PREFIX_ . 'order_detail_tax 
            WHERE id_tax IN (' . self::TAX_IDS_QST . ')
            GROUP BY id_order_detail
        ) qst ON od.id_order_detail = qst.id_order_detail
        LEFT JOIN ' . _DB_PREFIX_ . 'order_invoice_tax gst_ship 
            ON o.id_order = gst_ship.id_order_invoice 
            AND gst_ship.id_tax = ' . self::TAX_ID_GST . ' 
            AND gst_ship.type = "shipping"
        LEFT JOIN (
            SELECT id_order_invoice, SUM(amount) as amount
            FROM ' . _DB_PREFIX_ . 'order_invoice_tax 
            WHERE id_tax IN (' . self::TAX_IDS_QST . ') AND type = "shipping"
            GROUP BY id_order_invoice
        ) qst_ship ON o.id_order = qst_ship.id_order_invoice
        LEFT JOIN (
            SELECT ocr.id_order,
                   SUM(' . $ocrLineAmt . ') as total_cart_rule_value,
                   SUM(CASE WHEN LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%" THEN ' . $ocrLineAmt . ' ELSE 0 END) as gift_card_value,
                   SUM(CASE WHEN LOWER(ocr.name) LIKE "%voucher%" THEN ' . $ocrLineAmt . ' ELSE 0 END) as voucher_only_value,
                   SUM(CASE WHEN LOWER(ocr.name) LIKE "%promocode%" OR LOWER(ocr.name) LIKE "%point of sale%" THEN ' . $ocrLineAmt . ' ELSE 0 END) as discount_value,
                   GROUP_CONCAT(ocr.name SEPARATOR ", ") as voucher_names
            FROM ' . _DB_PREFIX_ . 'order_cart_rule ocr
            LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON cr.id_cart_rule = ocr.id_cart_rule
            GROUP BY ocr.id_order
        ) ocr ON o.id_order = ocr.id_order
        LEFT JOIN (
            SELECT id_order,
                   SUM(total_products_tax_incl + total_shipping_tax_incl) as total_refund_tax_incl,
                   MAX(date_add) as refund_date
            FROM ' . _DB_PREFIX_ . 'order_slip
            GROUP BY id_order
        ) slip ON o.id_order = slip.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'address da ON o.id_address_delivery = da.id_address
        LEFT JOIN ' . _DB_PREFIX_ . 'country_lang dcl ON da.id_country = dcl.id_country AND dcl.id_lang = ' . $this->id_lang . '
        LEFT JOIN ' . _DB_PREFIX_ . 'state ds ON da.id_state = ds.id_state
        LEFT JOIN (
            SELECT id_order,
                   GROUP_CONCAT(
                       CONCAT(payment_method, "($", ROUND(total_amount, 2), ")")
                       ORDER BY first_id ASC
                       SEPARATOR " - "
                   ) as payment_breakdown
            FROM (
                SELECT pb_ord.id_order,
                       ' . $paymentMethodCase . ' as payment_method,
                       SUM(op.amount) as total_amount,
                       MIN(op.id_order_payment) as first_id
                FROM (
                    SELECT MIN(o.id_order) as id_order, o.reference
                    FROM ' . _DB_PREFIX_ . 'orders o
                    WHERE o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                    AND o.current_state NOT IN (' . $excludedStates . ')
                    AND (
                        NOT(
                            o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                            AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                        )
                        OR (
                            (
                                o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                                AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                            )
                            AND o.current_state NOT IN (' . $refundStates . ')
                        )
                    )
                    GROUP BY o.reference
                ) pb_ord
                INNER JOIN ' . _DB_PREFIX_ . 'order_payment op ON op.order_reference = pb_ord.reference
                    AND op.amount > 0
                    AND op.date_add >= "' . $this->date_from . '" AND op.date_add <= "' . $this->date_to . '"
                GROUP BY pb_ord.id_order, payment_method
            ) grouped_payments
            GROUP BY id_order
        ) pb ON o.id_order = pb.id_order
        
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND (
            NOT(
                o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
            )
            OR (
                (
                    o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                    AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                )
                AND o.current_state NOT IN (' . $refundStates . ')
            )
        )
        
        ORDER BY o.date_add DESC, o.id_order DESC, od.id_order_detail ASC
        ';
        return Db::getInstance()->executeS($sql);
    }




    /**
     * Get partial refund orders (state = partial_refund e.g. 25, and possible_refund_date or date_add in range).
     * Source: orders table only (no order_slip). When an order is partially refunded it has current_state = 25
     * and possible_refund_date set. The whole amount of that order row (total_paid_tax_incl) is the refund.
     * Used for Refunds tab, SBPM refund/deduction, and Sales total.
     * POS may store possible_refund_date with 2-digit year (e.g. 25-12-30) so we match both DATE() and STR_TO_DATE(..., "%y-%m-%d").
     * @return array List of arrays: id_order, reference, refund_date, amount, is_online, payment_method_normalized, ...
     */
    public function getPartialRefundOrders()
    {

        $partialRefundStateId = (int)Khewareports::getConfiguredStates()['partial_refund'];
        if ($partialRefundStateId <= 0) {
            $partialRefundStateId = (int)Khewareports::DEFAULT_STATE_PARTIAL_REFUND;
        }
        
        $dateFromOnly = substr($this->date_from, 0, 10); // Y-m-d
        $dateToOnly = substr($this->date_to, 0, 10);
        
        $sql = '
        SELECT 
            o.id_order,
            o.reference,
            DATE_FORMAT(o.possible_refund_date, "%Y-%m-%d") as refund_date,
            o.total_paid_tax_incl as amount,
            o.invoice_number,
            DATE_FORMAT(o.date_add, "%Y-%m-%d") as order_date,
            o.payment,
            o.module,
            o.total_products_wt as refund_products_tax_incl,
            o.total_products as refund_products_tax_excl,
            o.total_shipping_tax_incl as refund_shipping_tax_incl,
            o.total_shipping_tax_excl as refund_shipping_tax_excl,
            dcl.name as delivery_country,
            ds.name as delivery_state,
            (
                SELECT op.payment_method 
                FROM ' . _DB_PREFIX_ . 'order_payment op 
                WHERE op.order_reference = o.reference AND op.amount > 0 
                ORDER BY op.id_order_payment ASC 
                LIMIT 1
            ) as first_payment_method
        FROM ' . _DB_PREFIX_ . 'orders o
        LEFT JOIN ' . _DB_PREFIX_ . 'address da ON o.id_address_delivery = da.id_address
        LEFT JOIN ' . _DB_PREFIX_ . 'country_lang dcl ON da.id_country = dcl.id_country AND dcl.id_lang = ' . $this->id_lang . '
        LEFT JOIN ' . _DB_PREFIX_ . 'state ds ON da.id_state = ds.id_state
        WHERE o.current_state = ' . $partialRefundStateId . '
        AND o.possible_refund_date IS NOT NULL
        AND (
            (DATE(o.possible_refund_date) >= "' . pSQL($dateFromOnly) . '" AND DATE(o.possible_refund_date) <= "' . pSQL($dateToOnly) . '")
            OR
            (STR_TO_DATE(o.possible_refund_date, "%y-%m-%d %H:%i:%s") >= "' . $this->date_from . '" AND STR_TO_DATE(o.possible_refund_date, "%y-%m-%d %H:%i:%s") <= "' . $this->date_to . '")
            OR
            (STR_TO_DATE(SUBSTRING(o.possible_refund_date, 1, 8), "%y-%m-%d") >= "' . pSQL($dateFromOnly) . '" AND STR_TO_DATE(SUBSTRING(o.possible_refund_date, 1, 8), "%y-%m-%d") <= "' . pSQL($dateToOnly) . '")
        )
        ORDER BY o.possible_refund_date ASC, o.id_order ASC
        ';
        


        


        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            return array();
        }
        
        $posModules = $this->posModules;
        $isPos = function ($module) use ($posModules) {
            if (empty($posModules)) {
                return (strtolower($module) === 'hspointofsalepro');
            }
            return in_array($module, $posModules);
        };
        
        $out = array();
        foreach ($rows as $r) {
            $paymentMethod = isset($r['first_payment_method']) ? $r['first_payment_method'] : $r['payment'];
            $refundDate = (isset($r['refund_date']) && $r['refund_date'] !== null && $r['refund_date'] !== '') ? $r['refund_date'] : $r['order_date'];
            $out[] = array(
                'id_order' => $r['id_order'],
                'reference' => $r['reference'],
                'refund_date' => $refundDate,
                'amount' => (float)$r['amount'],
                'is_online' => !$isPos($r['module']),
                'payment_method_normalized' => Khewareports::normalizePaymentMethod($paymentMethod),
                'invoice_number' => $r['invoice_number'],
                'order_date' => $r['order_date'],
                'payment' => $r['payment'],
                'module' => $r['module'],
                'refund_products_tax_incl' => (float)$r['refund_products_tax_incl'],
                'refund_products_tax_excl' => (float)$r['refund_products_tax_excl'],
                'refund_shipping_tax_incl' => (float)$r['refund_shipping_tax_incl'],
                'refund_shipping_tax_excl' => (float)$r['refund_shipping_tax_excl'],
                'delivery_country' => isset($r['delivery_country']) ? $r['delivery_country'] : '',
                'delivery_state' => isset($r['delivery_state']) ? $r['delivery_state'] : ''
            );
        }
        return $out;
    }
    

    /**
     * Total refund amount from partial refund orders (possible_refund_date in range).
     * Used to add to Sales sheet refund total (partial refund orders are excluded from getSalesData).
     * @return float
     */
    public function getPartialRefundTotal()
    {
        $list = $this->getPartialRefundOrders();
        $total = 0;
        foreach ($list as $partialRefund) {
            $total += $partialRefund['amount'];
        }
        return $total;
    }
    
    /**
     * Get Refunds Data - Refunds within date range based on REFUND DATE (order_slip.date_add).
     * Includes ALL refund types: full refunds and partial refunds (no filter by order state or os.partial).
     * Also merges in partial refund orders (detected by possible_refund_date, not order_slip).
     */
    public function getRefundsData()
    {
        $sql = '
        SELECT 
            o.id_order,
            o.reference,
            DATE_FORMAT(o.date_add, "%Y-%m-%d") as order_date,
            o.invoice_number,
            o.payment,
            o.module as payment_module,
            o.current_state,
            osl.name as order_state_name,
            os.id_order_slip,
            DATE_FORMAT(os.date_add, "%Y-%m-%d") as refund_date,
            os.total_products_tax_incl as refund_products_tax_incl,
            os.total_products_tax_excl as refund_products_tax_excl,
            os.total_shipping_tax_incl as refund_shipping_tax_incl,
            os.total_shipping_tax_excl as refund_shipping_tax_excl,
            (os.total_products_tax_incl + os.total_shipping_tax_incl) as total_refund_tax_incl,
            (os.total_products_tax_excl + os.total_shipping_tax_excl) as total_refund_tax_excl,
            os.partial as is_partial_refund,
            osd.id_order_detail,
            osd.product_quantity as refunded_quantity,
            osd.amount_tax_incl as product_refund_tax_incl,
            osd.amount_tax_excl as product_refund_tax_excl,
            od.product_name,
            od.unit_price_tax_incl,
            od.unit_price_tax_excl,
            ROUND(osd.amount_tax_excl * 0.05, 2) as refund_gst_amount,
            ROUND(osd.amount_tax_excl * 0.09975, 2) as refund_qst_amount,
            dcl.name as delivery_country,
            ds.name as delivery_state
            
        FROM ' . _DB_PREFIX_ . 'order_slip os
        INNER JOIN ' . _DB_PREFIX_ . 'orders o ON os.id_order = o.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'order_slip_detail osd ON os.id_order_slip = osd.id_order_slip
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON osd.id_order_detail = od.id_order_detail
        LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl 
            ON o.current_state = osl.id_order_state AND osl.id_lang = ' . $this->id_lang . '
        LEFT JOIN ' . _DB_PREFIX_ . 'address da ON o.id_address_delivery = da.id_address
        LEFT JOIN ' . _DB_PREFIX_ . 'country_lang dcl ON da.id_country = dcl.id_country AND dcl.id_lang = ' . $this->id_lang . '
        LEFT JOIN ' . _DB_PREFIX_ . 'state ds ON da.id_state = ds.id_state
        
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        
        ORDER BY os.date_add ASC, os.id_order_slip ASC
        ';
        
        $slipRows = Db::getInstance()->executeS($sql);
        if (!$slipRows) {
            $slipRows = array();
        }
         

        // Partial refunds: use state-25 order only; exclude slip rows for same reference to avoid double-count
        $partialRefunds = $this->getPartialRefundOrders();
        $partialRefReferences = array();
        foreach ($partialRefunds as $pr) {
            $partialRefReferences[$pr['reference']] = true;
        }
        if (!empty($partialRefReferences)) {
            $slipRows = array_values(array_filter($slipRows, function ($row) use ($partialRefReferences) {
                return empty($partialRefReferences[isset($row['reference']) ? $row['reference'] : '']);
            }));
        }
        
        // Merge partial refund orders (detected by state + possible_refund_date or date_add; no order_slip required)
        foreach ($partialRefunds as $partialRefund) {
            $totalIncl = $partialRefund['amount'];
            $totalExcl = $partialRefund['refund_products_tax_excl'] + $partialRefund['refund_shipping_tax_excl'];
            $slipRows[] = array(
                'id_order' => $partialRefund['id_order'],
                'reference' => $partialRefund['reference'],
                'order_date' => $partialRefund['order_date'],
                'invoice_number' => $partialRefund['invoice_number'],
                'payment' => $partialRefund['payment'],
                'payment_module' => $partialRefund['module'],
                'current_state' => 25,
                'order_state_name' => 'Partial refund',
                'id_order_slip' => 'partial-' . $partialRefund['id_order'],
                'refund_date' => $partialRefund['refund_date'],
                'refund_products_tax_incl' => $partialRefund['refund_products_tax_incl'],
                'refund_products_tax_excl' => $partialRefund['refund_products_tax_excl'],
                'refund_shipping_tax_incl' => $partialRefund['refund_shipping_tax_incl'],
                'refund_shipping_tax_excl' => $partialRefund['refund_shipping_tax_excl'],
                'total_refund_tax_incl' => $totalIncl,
                'total_refund_tax_excl' => $totalExcl,
                'is_partial_refund' => 1,
                'id_order_detail' => null,
                'refunded_quantity' => 1,
                'product_refund_tax_incl' => $totalIncl,
                'product_refund_tax_excl' => $totalExcl,
                'product_name' => 'Partial refund (state 25)',
                'unit_price_tax_incl' => $totalIncl,
                'unit_price_tax_excl' => $totalExcl,
                'refund_gst_amount' => round($totalExcl * 0.05, 2),
                'refund_qst_amount' => round($totalExcl * 0.09975, 2),
                'delivery_country' => $partialRefund['delivery_country'],
                'delivery_state' => $partialRefund['delivery_state']
            );
        }
        
        // Re-sort by refund_date so partial refund rows are interleaved by date
        usort($slipRows, function ($a, $b) {
            $da = isset($a['refund_date']) ? $a['refund_date'] : '';
            $db = isset($b['refund_date']) ? $b['refund_date'] : '';
            if ($da !== $db) {
                return strcmp($da, $db);
            }
            $ida = isset($a['id_order_slip']) ? $a['id_order_slip'] : '';
            $idb = isset($b['id_order_slip']) ? $b['id_order_slip'] : '';
            return strcmp((string)$ida, (string)$idb);
        });
        
        return $slipRows;
    }
    
    /**
     * Product tax from order_detail_tax per order (matches invoice / Sales tab product GST+QST).
     *
     * @param int[] $orderIds
     * @return array [id_order => [id_tax => float]]
     */
    protected function getProductTaxAmountsByOrderIds(array $orderIds)
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        if (empty($orderIds)) {
            return array();
        }
        $idList = implode(',', $orderIds);
        $sql = '
        SELECT od.id_order, odt.id_tax, SUM(odt.total_amount) as total_tax
        FROM ' . _DB_PREFIX_ . 'order_detail od
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE od.id_order IN (' . $idList . ')
        GROUP BY od.id_order, odt.id_tax
        ';
        $rows = Db::getInstance()->executeS($sql);
        $out = array();
        if ($rows) {
            foreach ($rows as $r) {
                $oid = (int)$r['id_order'];
                $tid = (int)$r['id_tax'];
                if (!isset($out[$oid])) {
                    $out[$oid] = array();
                }
                $out[$oid][$tid] = (float)$r['total_tax'];
            }
        }
        return $out;
    }

    /**
     * Tax-excl product base per order/tax, summed from the order_detail lines that
     * actually carry each tax id. Used by the POS tax formula so the percentage is
     * applied only to the lines that bear that tax (e.g. books — GST only — never
     * inflate the QST column). Returns [id_order => [id_tax => sum(total_price_tax_excl)]].
     *
     * @param int[] $orderIds
     * @return array
     */
    protected function getProductExclBaseByOrderIdsTax(array $orderIds)
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        if (empty($orderIds)) {
            return array();
        }
        $idList = implode(',', $orderIds);
        $sql = '
        SELECT od.id_order, odt.id_tax, SUM(od.total_price_tax_excl) as excl_base
        FROM ' . _DB_PREFIX_ . 'order_detail od
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE od.id_order IN (' . $idList . ')
        GROUP BY od.id_order, odt.id_tax
        ';
        $rows = Db::getInstance()->executeS($sql);
        $out = array();
        if ($rows) {
            foreach ($rows as $r) {
                $oid = (int)$r['id_order'];
                $tid = (int)$r['id_tax'];
                if (!isset($out[$oid])) {
                    $out[$oid] = array();
                }
                $out[$oid][$tid] = (float)$r['excl_base'];
            }
        }
        return $out;
    }

    /**
     * Shipping tax amount per order/tax from order_invoice_tax (type=shipping).
     *
     * @param int[] $orderIds
     * @return array [id_order => [id_tax => float]]
     */
    protected function getShippingTaxAmountsByOrderIds(array $orderIds)
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        if (empty($orderIds)) {
            return array();
        }
        $idList = implode(',', $orderIds);
        $sql = '
        SELECT oi.id_order, oit.id_tax,
               SUM(oit.amount) as total_tax
        FROM ' . _DB_PREFIX_ . 'order_invoice oi
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oi.id_order_invoice = oit.id_order_invoice
        WHERE oi.id_order IN (' . $idList . ')
        AND oit.type = "shipping"
        GROUP BY oi.id_order, oit.id_tax
        ';
        $rows = Db::getInstance()->executeS($sql);
        $out = array();
        if ($rows) {
            foreach ($rows as $r) {
                $oid = (int)$r['id_order'];
                $tid = (int)$r['id_tax'];
                if (!isset($out[$oid])) {
                    $out[$oid] = array();
                }
                $out[$oid][$tid] = (float)$r['total_tax'];
            }
        }
        return $out;
    }

    /**
     * Wrapping (gift wrap) tax amount per order/tax from order_invoice_tax (type=wrapping).
     * Mirrors the shipping-tax lookup; PrestaShop stores gift-wrapping tax in
     * order_invoice_tax with type="wrapping", separate from product (order_detail_tax)
     * and shipping rows.
     *
     * @param int[] $orderIds
     * @return array [id_order => [id_tax => float]]
     */
    protected function getWrappingTaxAmountsByOrderIds(array $orderIds)
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        if (empty($orderIds)) {
            return array();
        }
        $idList = implode(',', $orderIds);
        $sql = '
        SELECT oi.id_order, oit.id_tax,
               SUM(oit.amount) as total_tax
        FROM ' . _DB_PREFIX_ . 'order_invoice oi
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oi.id_order_invoice = oit.id_order_invoice
        WHERE oi.id_order IN (' . $idList . ')
        AND oit.type = "wrapping"
        GROUP BY oi.id_order, oit.id_tax
        ';
        $rows = Db::getInstance()->executeS($sql);
        $out = array();
        if ($rows) {
            foreach ($rows as $r) {
                $oid = (int)$r['id_order'];
                $tid = (int)$r['id_tax'];
                if (!isset($out[$oid])) {
                    $out[$oid] = array();
                }
                $out[$oid][$tid] = (float)$r['total_tax'];
            }
        }
        return $out;
    }



    /**
     * Refund amount per order (slip date in range + partial refunds), same filters as SBPM refund totals.
     *
     * @param int[] $orderIds
     * @return array [id_order => float]
     */
    protected function getRefundAmountsByOrderIdsForSbpm(array $orderIds, $excludePartialRefReferencesSql)
    {
        $orderIds = array_values(array_unique(array_map('intval', $orderIds)));
        if (empty($orderIds)) {
            return array();
        }
        $idSet = array_flip($orderIds);
        $out = array();
        $idList = implode(',', $orderIds);
        $sql = '
        SELECT o.id_order, SUM(os.total_products_tax_incl) as ref_amt
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND o.id_order IN (' . $idList . ')
        ' . $excludePartialRefReferencesSql . '
        GROUP BY o.id_order
        ';
        $rows = Db::getInstance()->executeS($sql);
        if ($rows) {
            foreach ($rows as $r) {
                $out[(int)$r['id_order']] = (float)$r['ref_amt'];
            }
        }
        $partialRefunds = $this->getPartialRefundOrders();
        foreach ($partialRefunds as $pr) {
            $oid = (int)$pr['id_order'];
            if (isset($idSet[$oid])) {
                $amt = (float)$pr['amount'];
                if (!isset($out[$oid])) {
                    $out[$oid] = 0;
                }
                $out[$oid] += $amt;
            }
        }
        return $out;
    }
    
    /**
     * Get SBPM Data - Complete Sales By Payment Method structure
     * Returns structured data for Combined summary, Online and In-Store sections
     */
    public function getSBPMData()
    {
        $excludedStates = Khewareports::getSalesExcludedStatesSQL();
        $refundStates = Khewareports::getRefundStatesSQL();
        $partialRefundStateId = (int)Khewareports::getConfiguredStates()['partial_refund'];
        if ($partialRefundStateId <= 0) {
            $partialRefundStateId = (int)Khewareports::DEFAULT_STATE_PARTIAL_REFUND;
        }
        $sbpmDateFromOnly = substr($this->date_from, 0, 10);
        $sbpmDateToOnly = substr($this->date_to, 0, 10);
        $excludePartialRefReferencesSql = '
            AND o.reference NOT IN (
                SELECT o2.reference FROM ' . _DB_PREFIX_ . 'orders o2
                WHERE o2.current_state = ' . $partialRefundStateId . '
                AND o2.possible_refund_date IS NOT NULL
                AND (
                    (DATE(o2.possible_refund_date) >= "' . pSQL($sbpmDateFromOnly) . '" AND DATE(o2.possible_refund_date) <= "' . pSQL($sbpmDateToOnly) . '")
                    OR (STR_TO_DATE(SUBSTRING(o2.possible_refund_date, 1, 8), "%y-%m-%d") >= "' . pSQL($sbpmDateFromOnly) . '" AND STR_TO_DATE(SUBSTRING(o2.possible_refund_date, 1, 8), "%y-%m-%d") <= "' . pSQL($sbpmDateToOnly) . '")
                )
            )';
        
        // possible_refund_date condition (mirrors ExportSales approach):
        // Exclude orders where BOTH date_add AND possible_refund_date fall within the
        // reporting period AND the order state is a refund state (refunded/partial refund).
        // If the refund happened outside this period, the order remains a valid sale.
        $refundDateCondition = '
            AND (
                NOT(
                    o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                    AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                )
                OR (
                    (
                        o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                        AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                    )
                    AND o.current_state NOT IN (' . $refundStates . ')
                )
            )';
        
            
        $result = array(
            'combined' => array(),  // Top summary table
            'online' => array(
                'payments' => array(),
                'gift_card' => 0,
                'voucher' => 0,
                'credit_slip' => 0,
                'discount' => 0,
                'shipping_incl' => 0,
                'shipping_taxes' => array(),
                'refund' => 0,
                'refund_products_excl' => 0,
                'refund_products_incl' => 0,
                'refund_shipping_incl' => 0,
                'refund_taxes' => array(),
                'total' => 0
            ),
            'instore' => array(
                'payments' => array(),
                'voucher' => 0,
                'credit_card' => 0,
                'cash' => 0,
                'interac' => 0,
                'gift_card' => 0,
                'credit_slip' => 0,
                'discount' => 0,
                'shipping_incl' => 0,
                'shipping_taxes' => array(),
                'refund' => 0,
                'refund_products_excl' => 0,
                'refund_products_incl' => 0,
                'refund_shipping_incl' => 0,
                'refund_taxes' => array(),
                'total' => 0
            ),
            'tax_columns' => array(),
            'has_wrapping' => false
        );

        // ==================== TOP PART: Combined Summary by Payment Method ====================
        // STEP 1: Get ALL orders with their totals (same filter as Sales tab)
        // Use orders.total_products_wt as the authoritative source (this is what payments are based on)
        $sql = '
        SELECT 
            o.id_order,
            o.reference,
            o.module,
            o.total_products as order_total_products_excl,
            o.total_products_wt as order_total_products_incl,
            o.total_shipping_tax_incl as order_total_shipping,
            o.total_wrapping_tax_excl as order_total_wrapping_excl,
            o.total_paid_tax_incl as order_total_paid
        FROM ' . _DB_PREFIX_ . 'orders o
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        ';
        $allOrders = Db::getInstance()->executeS($sql);
        
        // Build order reference map for quick lookup
        $orderMap = array();
        foreach ($allOrders as $order) {
            $orderMap[$order['reference']] = $order;
        }
        
        // STEP 2: Get payment records for these orders
        // Normalize payment methods using configurable patterns
        // Use subquery to get valid references to avoid JOIN inflation
        $paymentMethodCase = Khewareports::buildPaymentMethodCase('op.payment_method');
        
        $sql = '
        SELECT 
            op.order_reference,
            MIN(op.id_order_payment) as first_payment_id,
            ' . $paymentMethodCase . ' as payment_method,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (
            SELECT DISTINCT o.reference 
            FROM ' . _DB_PREFIX_ . 'orders o 
            WHERE o.date_add >= "' . $this->date_from . '"
            AND o.date_add <= "' . $this->date_to . '"
            AND o.current_state NOT IN (' . $excludedStates . ')
            ' . $refundDateCondition . '
        )
        AND op.amount > 0
        GROUP BY op.order_reference, payment_method
        ORDER BY op.order_reference, first_payment_id
        ';
        $paymentRecords = Db::getInstance()->executeS($sql);
        
        // Group payments by order reference, maintaining the order they were added
        $paymentsByOrder = array();
        foreach ($paymentRecords as $payment) {
            $ref = $payment['order_reference'];
            $paymentMethod = trim($payment['payment_method']);
            
            if (!isset($paymentsByOrder[$ref])) {
                $paymentsByOrder[$ref] = array();
            }
            
            // Add payment method in order (uses first_payment_id ordering from SQL)
            if (!isset($paymentsByOrder[$ref][$paymentMethod])) {
                $paymentsByOrder[$ref][$paymentMethod] = array(
                    'payment_method' => $paymentMethod,
                    'amount' => 0,
                    'order' => count($paymentsByOrder[$ref])
                );
            }
            $paymentsByOrder[$ref][$paymentMethod]['amount'] += (float)$payment['payment_amount'];
        }
        


        // STEP 3: Group orders by payment method(s)
        // - Single payment method orders → individual row (e.g., "Interac")
        // - Multiple payment methods → combined row with underscore (e.g., "Interac_Cash")
        $combinedBase = array();
        
        foreach ($allOrders as $order) {
            $ref = $order['reference'];
            $module = $order['module'];
            $orderId = $order['id_order'];
            
            if (isset($paymentsByOrder[$ref]) && !empty($paymentsByOrder[$ref])) {
                // Get all payment methods for this order in the order they were recorded
                $methodsWithOrder = array();
                foreach ($paymentsByOrder[$ref] as $method => $data) {
                    $methodsWithOrder[$method] = isset($data['order']) ? $data['order'] : 0;
                }
                asort($methodsWithOrder); // Sort by order value (maintains payment sequence)
                $methods = array_keys($methodsWithOrder);
                
                if (count($methods) == 1) {
                    // Single payment method - use it directly
                    $paymentMethod = $methods[0];
                } else {
                    // Multiple payment methods - combine with underscore in payment order
                    $paymentMethod = implode('_', $methods);
                }
                
                $key = $paymentMethod . '|' . $module;
                
                if (!isset($combinedBase[$key])) {
                    $combinedBase[$key] = array(
                        'payment_method' => $paymentMethod,
                        'module' => $module,
                        'order_count' => 0,
                        'total_products_tax_excl' => 0,
                        'total_products_tax_incl' => 0,
                        'total_shipping_tax_incl' => 0,
                        'total_paid_tax_incl' => 0,
                        'order_ids' => array()
                    );
                }
                
                // Count order once
                $combinedBase[$key]['order_ids'][] = $orderId;
                $combinedBase[$key]['order_count']++;
                
                // Add order-level product/shipping totals
                $combinedBase[$key]['total_products_tax_excl'] += (float)$order['order_total_products_excl'];
                $combinedBase[$key]['total_products_tax_incl'] += (float)$order['order_total_products_incl'];
                $combinedBase[$key]['total_shipping_tax_incl'] += (float)$order['order_total_shipping'];
                
                // Total Paid = order's total_paid_tax_incl (one value per order) so it stays consistent with Total Products + Shipping.
                // Using order total avoids Total Paid > Total Products when order_payment has duplicates or extra rows.
                $combinedBase[$key]['total_paid_tax_incl'] += (float)$order['order_total_paid'];
            } else {
                // Order has NO payment records - paid entirely by gift card/voucher
                // Add to "Gift Card_Voucher" category (using underscore for consistency)
                $paymentMethod = 'Gift Card_Voucher';
                $key = $paymentMethod . '|' . $module;
                
                if (!isset($combinedBase[$key])) {
                    $combinedBase[$key] = array(
                        'payment_method' => $paymentMethod,
                        'module' => $module,
                        'order_count' => 0,
                        'total_products_tax_excl' => 0,
                        'total_products_tax_incl' => 0,
                        'total_shipping_tax_incl' => 0,
                        'total_paid_tax_incl' => 0,
                        'order_ids' => array()
                    );
                }
                
                $combinedBase[$key]['order_ids'][] = $orderId;
                $combinedBase[$key]['order_count']++;
                $combinedBase[$key]['total_products_tax_excl'] += (float)$order['order_total_products_excl'];
                $combinedBase[$key]['total_products_tax_incl'] += (float)$order['order_total_products_incl'];
                $combinedBase[$key]['total_shipping_tax_incl'] += (float)$order['order_total_shipping'];
                $combinedBase[$key]['total_paid_tax_incl'] += (float)$order['order_total_paid'];
            }
        }
        

        
        // Convert to indexed array and ensure no duplicates
        // Group by payment_method + module to catch any edge cases
        $finalCombined = array();
        foreach ($combinedBase as $key => $row) {
            $normalizedKey = trim($row['payment_method']) . '|' . $row['module'];
            if (!isset($finalCombined[$normalizedKey])) {
                $finalCombined[$normalizedKey] = $row;
            } else {
                // If duplicate found, merge the data (shouldn't happen, but safety check)
                $finalCombined[$normalizedKey]['order_count'] += $row['order_count'];
                $finalCombined[$normalizedKey]['total_products_tax_excl'] += $row['total_products_tax_excl'];
                $finalCombined[$normalizedKey]['total_products_tax_incl'] += $row['total_products_tax_incl'];
                $finalCombined[$normalizedKey]['total_shipping_tax_incl'] += $row['total_shipping_tax_incl'];
                $finalCombined[$normalizedKey]['total_paid_tax_incl'] += $row['total_paid_tax_incl'];
                $finalCombined[$normalizedKey]['order_ids'] = array_merge(
                    isset($finalCombined[$normalizedKey]['order_ids']) ? $finalCombined[$normalizedKey]['order_ids'] : array(),
                    isset($row['order_ids']) ? $row['order_ids'] : array()
                );
            }
        }
        
        // Convert to indexed array and sort by total_paid_tax_incl DESC
        $combinedBase = array_values($finalCombined);
        usort($combinedBase, function($a, $b) {
            return $b['total_paid_tax_incl'] <=> $a['total_paid_tax_incl'];
        });
        
        // Get ordered list of all active tax types for column headers
        $sql = '
        SELECT DISTINCT t.id_tax,
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON odt.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        ORDER BY t.rate ASC
        ';
        $activeTaxes = Db::getInstance()->executeS($sql);
        $result['tax_columns'] = $activeTaxes ? $activeTaxes : array();
        $taxIds = array();
        foreach ($result['tax_columns'] as $tax) {
            $taxIds[] = (int)$tax['id_tax'];
        }
        
        // Combined rows: sum order_detail_tax per order in each bucket (matches invoice / Sales tab; not split by Total Paid).
        if ($combinedBase) {
            $allOrderIds = array();
            foreach ($combinedBase as $row) {
                if (!empty($row['order_ids'])) {
                    foreach ($row['order_ids'] as $oid) {
                        $allOrderIds[(int)$oid] = true;
                    }
                }
            }
            $allOrderIds = array_keys($allOrderIds);
            $productTaxByOrderId = $this->getProductTaxAmountsByOrderIds($allOrderIds);
            $productExclBaseByOrderId = $this->getProductExclBaseByOrderIdsTax($allOrderIds);
            $shippingTaxByOrderId = $this->getShippingTaxAmountsByOrderIds($allOrderIds);
            $wrappingTaxByOrderId = $this->getWrappingTaxAmountsByOrderIds($allOrderIds);
            $refundByOrderId = $this->getRefundAmountsByOrderIdsForSbpm($allOrderIds, $excludePartialRefReferencesSql);
            // Per-order tax-excl base for the POS formula (so a 0-tax order contributes 0)
            $orderExclById = array();
            // Per-order gift-wrapping cost (tax-excl) for the optional Wrapping Cost column.
            $orderWrappingExclById = array();
            foreach ($allOrders as $ord) {
                $orderExclById[(int)$ord['id_order']] = (float)$ord['order_total_products_excl'];
                $orderWrappingExclById[(int)$ord['id_order']] = (float)$ord['order_total_wrapping_excl'];
            }
            $taxRateById = array();
            foreach ($result['tax_columns'] as $tax) {
                $taxRateById[(int)$tax['id_tax']] = (float)$tax['rate'];
            }
            $qstTaxIds = array_map('intval', array_filter(array_map('trim', explode(',', self::TAX_IDS_QST))));

            foreach ($combinedBase as &$row) {
                $module = $row['module'];
                $row['taxes'] = array();
                foreach ($taxIds as $taxId) {
                    $row['taxes'][$taxId] = 0;
                }
                $rowProductTax = array();
                $rowShippingTax = array();
                $rowWrappingTax = array();
                foreach ($taxIds as $taxId) {
                    $rowProductTax[$taxId] = 0;
                    $rowShippingTax[$taxId] = 0;
                    $rowWrappingTax[$taxId] = 0;
                }
                if (!empty($row['order_ids'])) {
                    foreach ($row['order_ids'] as $oid) {
                        $oid = (int)$oid;
                        foreach ($taxIds as $taxId) {
                            $productTax = (isset($productTaxByOrderId[$oid]) && isset($productTaxByOrderId[$oid][$taxId]))
                                ? (float)$productTaxByOrderId[$oid][$taxId]
                                : 0;
                            $shippingTax = (isset($shippingTaxByOrderId[$oid]) && isset($shippingTaxByOrderId[$oid][$taxId]))
                                ? (float)$shippingTaxByOrderId[$oid][$taxId]
                                : 0;
                            $wrappingTax = (isset($wrappingTaxByOrderId[$oid]) && isset($wrappingTaxByOrderId[$oid][$taxId]))
                                ? (float)$wrappingTaxByOrderId[$oid][$taxId]
                                : 0;
                            $rowProductTax[$taxId] += $productTax;
                            $rowShippingTax[$taxId] += $shippingTax;
                            $rowWrappingTax[$taxId] += $wrappingTax;
                        }
                    }
                }

                // Match expected sheet behavior:
                // - In-store buckets: formula from product excl using display rates (QST normalized to 9.975)
                // - Online buckets: real product tax + gift-wrapping tax by same tax id (shipping excluded)
                if ($this->isPosModule($module)) {
                    // POS formula: apply the display rate to the tax-excl product base of ONLY the
                    // lines that actually carry this tax id (from order_detail joined to
                    // order_detail_tax). This keeps the percentage approach — which reproduces the
                    // verified report values, since PrestaShop's stored order_detail_tax.total_amount
                    // is per-unit-rounded-then-multiplied and over-reports — while ensuring that
                    // tax-exempt / single-tax lines (e.g. books are GST-only) never inflate another
                    // tax column. Contributions are summed unrounded and rounded once per bucket below.
                    foreach ($taxIds as $taxId) {
                        $displayRate = in_array((int)$taxId, $qstTaxIds, true) ? 9.975 : (isset($taxRateById[$taxId]) ? (float)$taxRateById[$taxId] : 0);
                        $bucketTax = 0;
                        if (!empty($row['order_ids'])) {
                            foreach ($row['order_ids'] as $oid) {
                                $oid = (int)$oid;
                                // Tax-excl base of the lines bearing THIS tax id in this order.
                                // Absent/zero => order contributes 0 to this column (the zero-gate).
                                $exclBase = (isset($productExclBaseByOrderId[$oid][$taxId]))
                                    ? (float)$productExclBaseByOrderId[$oid][$taxId]
                                    : 0;
                                if ($exclBase <= 0) {
                                    continue;
                                }
                                $bucketTax += ($displayRate > 0) ? ($exclBase * $displayRate / 100) : 0;
                            }
                        }
                        $row['taxes'][$taxId] = $bucketTax;
                    }
                } else {
                    foreach ($taxIds as $taxId) {
                        // Online buckets: product tax + gift-wrapping tax (shipping tax intentionally excluded).
                        // $row['taxes'][$taxId] = $rowProductTax[$taxId] + $rowShippingTax[$taxId];
                        $row['taxes'][$taxId] = $rowProductTax[$taxId] + $rowWrappingTax[$taxId];
                    }
                }
                foreach ($taxIds as $taxId) {
                    $row['taxes'][$taxId] = round($row['taxes'][$taxId], 2);
                }

                // Gift-wrapping cost (tax-excl) and combined wrapping tax for this bucket.
                // wrapping_tax = sum of per-rate wrapping tax already accumulated above.
                $row['wrapping_cost'] = 0;
                if (!empty($row['order_ids'])) {
                    foreach ($row['order_ids'] as $oid) {
                        $oid = (int)$oid;
                        $row['wrapping_cost'] += isset($orderWrappingExclById[$oid]) ? $orderWrappingExclById[$oid] : 0;
                    }
                }
                $rowWrappingTaxTotal = 0;
                foreach ($taxIds as $taxId) {
                    $rowWrappingTaxTotal += $rowWrappingTax[$taxId];
                }
                $row['wrapping_cost'] = round($row['wrapping_cost'], 2);
                $row['wrapping_tax'] = round($rowWrappingTaxTotal, 2);


                $row['refund_online'] = 0;
                $row['refund_instore'] = 0;
                if (!empty($row['order_ids'])) {
                    foreach ($row['order_ids'] as $oid) {
                        $oid = (int)$oid;
                        $refAmt = isset($refundByOrderId[$oid]) ? $refundByOrderId[$oid] : 0;
                        if ($refAmt <= 0) {
                            continue;
                        }
                        if ($this->isPosModule($module)) {
                            $row['refund_instore'] += $refAmt;
                        } else {
                            $row['refund_online'] += $refAmt;
                        }
                    }
                }
                $row['refund_online'] = round($row['refund_online'], 2);
                $row['refund_instore'] = round($row['refund_instore'], 2);

                unset($row['order_ids']);
            }
            unset($row);
            $result['combined'] = $combinedBase;

            // Only expose the Wrapping Cost / Wrapping Tax columns when at least one
            // order in the range actually had gift wrapping (cost or tax > 0).
            $hasWrapping = false;
            foreach ($combinedBase as $row) {
                if ((isset($row['wrapping_cost']) && $row['wrapping_cost'] > 0)
                    || (isset($row['wrapping_tax']) && $row['wrapping_tax'] > 0)) {
                    $hasWrapping = true;
                    break;
                }
            }
            $result['has_wrapping'] = $hasWrapping;
        }

        // ==================== BOTTOM PART: Specific Payment Amounts ====================
        // Direct SQL queries against order_payment, grouped by normalized payment method.
        // This matches the original ExportSales module's approach: GROUP BY payment_method
        // with SUM(amount), which correctly totals ALL payments by method across ALL orders
        // (including mixed-payment orders like Credit Card + Cash).
        // ==================== ==================== ====================
        
        // Subqueries: get DISTINCT qualifying order references split by online/instore
        // Using subqueries (not JOINs) avoids inflating SUM when multiple orders share a reference
        $onlineRefSubquery = '
            SELECT DISTINCT o.reference 
            FROM ' . _DB_PREFIX_ . 'orders o 
            WHERE o.date_add >= "' . $this->date_from . '"
            AND o.date_add <= "' . $this->date_to . '"
            AND o.current_state NOT IN (' . $excludedStates . ')
            ' . $refundDateCondition . '
            AND ' . $this->getNotPosModuleCondition('o.module');
        
        $instoreRefSubquery = '
            SELECT DISTINCT o.reference 
            FROM ' . _DB_PREFIX_ . 'orders o 
            WHERE o.date_add >= "' . $this->date_from . '"
            AND o.date_add <= "' . $this->date_to . '"
            AND o.current_state NOT IN (' . $excludedStates . ')
            ' . $refundDateCondition . '
            AND ' . $this->getPosModuleCondition('o.module');
        
        // ONLINE payments: only orders with module != POS; only payment rows with date_add in range (match ExportSales).
        $sql = '
        SELECT 
            ' . $paymentMethodCase . ' as payment_method,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $onlineRefSubquery . ')
        AND op.amount > 0
        AND op.date_add >= "' . $this->date_from . '" AND op.date_add <= "' . $this->date_to . '"
        GROUP BY payment_method
        ';
        $onlinePayments = Db::getInstance()->executeS($sql);
        
        $onlineByMethod = array();
        if ($onlinePayments) {
            foreach ($onlinePayments as $p) {
                $onlineByMethod[trim($p['payment_method'])] = (float)$p['payment_amount'];
            }
        }
        

        
        // IN-STORE payments: only orders with module = POS; only payment rows with date_add in range; exclude Paypal/Stripe (instore = Cash/Credit Card/Interac only, match ExportSales).
        // IMPORTANT: op.amount > 0 only — "Paid with Cash" must show total cash received (gross), never reduced by refunds.
        $sql = '
        SELECT 
            ' . $paymentMethodCase . ' as payment_method,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $instoreRefSubquery . ')
        AND op.amount > 0
        AND op.date_add >= "' . $this->date_from . '" AND op.date_add <= "' . $this->date_to . '"
        AND LOWER(op.payment_method) NOT LIKE "%paypal%" AND LOWER(op.payment_method) NOT LIKE "%stripe%"
        GROUP BY payment_method
        ';
        $instorePayments = Db::getInstance()->executeS($sql);
        
        $instoreByMethod = array();
        if ($instorePayments) {
            foreach ($instorePayments as $p) {
                $instoreByMethod[trim($p['payment_method'])] = (float)$p['payment_amount'];
            }
        }


        // In-store refunds by payment method (negative amounts in order_payment) for Cash in hand
        $sql = '
        SELECT 
            ' . $paymentMethodCase . ' as payment_method,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $instoreRefSubquery . ')
        AND op.amount < 0
        AND op.date_add >= "' . $this->date_from . '" AND op.date_add <= "' . $this->date_to . '"
        AND LOWER(op.payment_method) NOT LIKE "%paypal%" AND LOWER(op.payment_method) NOT LIKE "%stripe%"
        GROUP BY payment_method
        ';
        $instoreRefundsByMethod = Db::getInstance()->executeS($sql);
        $refundedInCash = 0;
        if ($instoreRefundsByMethod) {
            foreach ($instoreRefundsByMethod as $p) {
                if (trim($p['payment_method']) === 'Cash') {
                    $refundedInCash = abs((float)$p['payment_amount']);
                    break;
                }
            }
        }

        // Partial refunds are NOT deducted from individual payment methods (Cash, Credit Card, etc.).
        // They are included in the Refund Instore/Online line and thus reduce the total only.

        // Online specific amounts
        $result['online']['stripe_link'] = isset($onlineByMethod['Link via Stripe']) ? $onlineByMethod['Link via Stripe'] : 0;
        $result['online']['stripe_card'] = isset($onlineByMethod['Card via Stripe']) ? $onlineByMethod['Card via Stripe'] : 0;
        $result['online']['paypal'] = isset($onlineByMethod['PayPal']) ? $onlineByMethod['PayPal'] : 0;
        
        // In-Store specific amounts (Cash = gross cash received only, no deduction; Cash in hand = Cash − refunded in cash)
        $result['instore']['credit_card'] = isset($instoreByMethod['Credit Card']) ? $instoreByMethod['Credit Card'] : 0;
        $result['instore']['cash'] = isset($instoreByMethod['Cash']) ? $instoreByMethod['Cash'] : 0;
        $result['instore']['interac'] = isset($instoreByMethod['Interac']) ? $instoreByMethod['Interac'] : 0;
        $result['instore']['refunded_in_cash'] = $refundedInCash;
        $result['instore']['cash_in_hand'] = $result['instore']['cash'] - $refundedInCash;
        
        // Calculate totals by summing only actual cash payment methods (exclude gift cards, vouchers, etc.)
        $result['online']['total'] = 0;
        foreach ($onlineByMethod as $method => $amount) {
            if (!$this->isExcludedFromTotal($method)) {
                $result['online']['total'] += $amount;
            }
        }
        
        $result['instore']['total'] = 0;
        foreach ($instoreByMethod as $method => $amount) {
            if (!$this->isExcludedFromTotal($method)) {
                $result['instore']['total'] += $amount;
            }
        }
        
        // ========================================================================
        // GIFT CARD, VOUCHER, CREDIT SLIP - Check BOTH sources, eliminate duplicates
        // Strategy: Get from order_payment first, then add order_cart_rule EXCLUDING
        // orders that already have this type in order_payment (to avoid double-counting)
        // ========================================================================
        
        // --- ONLINE GIFT CARD ---


        // Source 1: From order_payment (primary - actual payment records)
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $onlineRefSubquery . ')
        AND (LOWER(op.payment_method) LIKE "%gift%" OR LOWER(op.payment_method) LIKE "%cadeau%")
        AND op.amount > 0
        ';
        $giftOnlinePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have gift card in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        AND (LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%")
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE (LOWER(op2.payment_method) LIKE "%gift%" OR LOWER(op2.payment_method) LIKE "%cadeau%")
            AND op2.amount > 0
        )
        ';
        $giftOnlineCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['online']['gift_card'] = $giftOnlinePayment + $giftOnlineCartRule;
        
        // --- IN-STORE GIFT CARD ---
        // Source 1: From order_payment
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $instoreRefSubquery . ')
        AND (LOWER(op.payment_method) LIKE "%gift%" OR LOWER(op.payment_method) LIKE "%cadeau%")
        AND op.amount > 0
        ';
        $giftInstorePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have gift card in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getPosModuleCondition('o.module') . '
        AND (LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%")
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE (LOWER(op2.payment_method) LIKE "%gift%" OR LOWER(op2.payment_method) LIKE "%cadeau%")
            AND op2.amount > 0
        )
        ';
        $giftInstoreCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['instore']['gift_card'] = $giftInstorePayment + $giftInstoreCartRule;
        
        // --- ONLINE VOUCHER ---
        // Source 1: From order_payment
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $onlineRefSubquery . ')
        AND LOWER(op.payment_method) LIKE "%voucher%"
        AND op.amount > 0
        ';
        $voucherOnlinePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have voucher in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        AND LOWER(ocr.name) LIKE "%voucher%"
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE LOWER(op2.payment_method) LIKE "%voucher%"
            AND op2.amount > 0
        )
        ';
        $voucherOnlineCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['online']['voucher'] = $voucherOnlinePayment + $voucherOnlineCartRule;
        
        // --- IN-STORE VOUCHER ---
        // Source 1: From order_payment
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $instoreRefSubquery . ')
        AND LOWER(op.payment_method) LIKE "%voucher%"
        AND op.amount > 0
        ';
        $voucherInstorePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have voucher in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getPosModuleCondition('o.module') . '
        AND LOWER(ocr.name) LIKE "%voucher%"
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE LOWER(op2.payment_method) LIKE "%voucher%"
            AND op2.amount > 0
        )
        ';
        $voucherInstoreCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['instore']['voucher'] = $voucherInstorePayment + $voucherInstoreCartRule;
        
        // --- ONLINE CREDIT SLIP ---
        // Source 1: From order_payment
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $onlineRefSubquery . ')
        AND (LOWER(op.payment_method) LIKE "%credit slip%" OR LOWER(op.payment_method) LIKE "%slip%")
        AND op.amount > 0
        ';
        $creditSlipOnlinePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have credit slip in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        AND LOWER(cr.description) LIKE "%slip%"
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE (LOWER(op2.payment_method) LIKE "%credit slip%" OR LOWER(op2.payment_method) LIKE "%slip%")
            AND op2.amount > 0
        )
        ';
        $creditSlipOnlineCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['online']['credit_slip'] = $creditSlipOnlinePayment + $creditSlipOnlineCartRule;
        
        // --- IN-STORE CREDIT SLIP ---
        // Source 1: From order_payment
        $sql = '
        SELECT IFNULL(SUM(op.amount), 0) as amount
        FROM ' . _DB_PREFIX_ . 'order_payment op
        WHERE op.order_reference IN (' . $instoreRefSubquery . ')
        AND (LOWER(op.payment_method) LIKE "%credit slip%" OR LOWER(op.payment_method) LIKE "%slip%")
        AND op.amount > 0
        ';
        $creditSlipInstorePayment = (float)Db::getInstance()->getValue($sql);
        
        // Source 2: From order_cart_rule, EXCLUDING orders that have credit slip in order_payment
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getPosModuleCondition('o.module') . '
        AND LOWER(cr.description) LIKE "%slip%"
        AND o.id_order NOT IN (
            SELECT DISTINCT o2.id_order 
            FROM ' . _DB_PREFIX_ . 'orders o2
            INNER JOIN ' . _DB_PREFIX_ . 'order_payment op2 ON o2.reference = op2.order_reference
            WHERE (LOWER(op2.payment_method) LIKE "%credit slip%" OR LOWER(op2.payment_method) LIKE "%slip%")
            AND op2.amount > 0
        )
        ';
        $creditSlipInstoreCartRule = (float)Db::getInstance()->getValue($sql);
        
        $result['instore']['credit_slip'] = $creditSlipInstorePayment + $creditSlipInstoreCartRule;
        
        // --- END OF GIFT CARD / VOUCHER / CREDIT SLIP ---
        
        // Get ONLINE Discount (promocode)
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        AND LOWER(ocr.name) LIKE "%promocode%"
        ';
        $discountOnline = Db::getInstance()->getValue($sql);
        $result['online']['discount'] = (float)$discountOnline;
        
        // Get IN-STORE Discount (Point of Sale discount)
        // Use CASE to select correct value based on reduction type
        $sql = '
        SELECT IFNULL(SUM(
            CASE
                WHEN IFNULL(cr.reduction_percent, 0) > 0 THEN ocr.value_tax_excl
                WHEN IFNULL(cr.reduction_amount, 0) > 0 THEN
                    (CASE WHEN IFNULL(cr.reduction_tax, 0) = 1 THEN ocr.value ELSE ocr.value_tax_excl END)
                ELSE ocr.value_tax_excl
            END
        ), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getPosModuleCondition('o.module') . '
        AND LOWER(ocr.name) LIKE "%point of sale%"
        ';
        $discountInstore = Db::getInstance()->getValue($sql);
        $result['instore']['discount'] = (float)$discountInstore;

        // ONLINE shipping totals (for SBPM top table "Shipping Online" row)
        $sql = '
        SELECT IFNULL(SUM(o.total_shipping_tax_incl), 0) as shipping_incl
        FROM ' . _DB_PREFIX_ . 'orders o
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        ';
        $result['online']['shipping_incl'] = (float)Db::getInstance()->getValue($sql);

        $sql = '
        SELECT oit.id_tax, IFNULL(SUM(oit.amount), 0) as shipping_tax
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice oi ON oi.id_order = o.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oit.id_order_invoice = oi.id_order_invoice
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCondition . '
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        AND oit.type = "shipping"
        GROUP BY oit.id_tax
        ';
        $shippingOnlineTaxRows = Db::getInstance()->executeS($sql);
        $result['online']['shipping_taxes'] = array();
        if ($shippingOnlineTaxRows) {
            foreach ($shippingOnlineTaxRows as $taxRow) {
                $result['online']['shipping_taxes'][(int)$taxRow['id_tax']] = (float)$taxRow['shipping_tax'];
            }
        }
        
        // Get ONLINE Refunds - products/shipping totals (from order_slip; exclude partial refund orders)
        $sql = '
        SELECT
            IFNULL(SUM(os.total_products_tax_excl), 0) as products_excl,
            IFNULL(SUM(os.total_products_tax_incl), 0) as products_incl,
            IFNULL(SUM(os.total_shipping_tax_incl), 0) as shipping_incl
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        ' . $excludePartialRefReferencesSql . '
        ';
        $onlineRefundRow = Db::getInstance()->getRow($sql);
        $result['online']['refund'] = (float)$onlineRefundRow['products_incl'];
        $result['online']['refund_products_excl'] = (float)$onlineRefundRow['products_excl'];
        $result['online']['refund_products_incl'] = (float)$onlineRefundRow['products_incl'];
        $result['online']['refund_shipping_incl'] = (float)$onlineRefundRow['shipping_incl'];

        // Get ONLINE Refund taxes - all tax IDs, proportional to refunded product amount
        $sql = '
        SELECT tx.id_tax,
            IFNULL(SUM(
                CASE WHEN o.total_products > 0
                THEN (os.total_products_tax_excl / o.total_products) * tx.order_tax
                ELSE tx.order_tax END
            ), 0) as refund_tax
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        INNER JOIN (
            SELECT od.id_order, odt.id_tax, SUM(odt.total_amount) as order_tax
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
            GROUP BY od.id_order, odt.id_tax
        ) tx ON tx.id_order = o.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND ' . $this->getNotPosModuleCondition('o.module') . '
        ' . $excludePartialRefReferencesSql . '
        GROUP BY tx.id_tax
        ';
        $onlineRefundTaxRows = Db::getInstance()->executeS($sql);
        $result['online']['refund_taxes'] = array();
        if ($onlineRefundTaxRows) {
            foreach ($onlineRefundTaxRows as $taxRow) {
                $result['online']['refund_taxes'][(int)$taxRow['id_tax']] = (float)$taxRow['refund_tax'];
            }
        }

        // Get IN-STORE Refunds - products/shipping totals
        $sql = '
        SELECT
            IFNULL(SUM(os.total_products_tax_excl), 0) as products_excl,
            IFNULL(SUM(os.total_products_tax_incl), 0) as products_incl,
            IFNULL(SUM(os.total_shipping_tax_incl), 0) as shipping_incl
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND ' . $this->getPosModuleCondition('o.module') . '
        ' . $excludePartialRefReferencesSql . '
        ';
        $instoreRefundRow = Db::getInstance()->getRow($sql);
        $result['instore']['refund'] = (float)$instoreRefundRow['products_incl'];
        $result['instore']['refund_products_excl'] = (float)$instoreRefundRow['products_excl'];
        $result['instore']['refund_products_incl'] = (float)$instoreRefundRow['products_incl'];
        $result['instore']['refund_shipping_incl'] = (float)$instoreRefundRow['shipping_incl'];

        // Get IN-STORE Refund taxes - all tax IDs, proportional to refunded product amount
        $sql = '
        SELECT tx.id_tax,
            IFNULL(SUM(
                CASE WHEN o.total_products > 0
                THEN (os.total_products_tax_excl / o.total_products) * tx.order_tax
                ELSE tx.order_tax END
            ), 0) as refund_tax
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        INNER JOIN (
            SELECT od.id_order, odt.id_tax, SUM(odt.total_amount) as order_tax
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
            GROUP BY od.id_order, odt.id_tax
        ) tx ON tx.id_order = o.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND ' . $this->getPosModuleCondition('o.module') . '
        ' . $excludePartialRefReferencesSql . '
        GROUP BY tx.id_tax
        ';
        $instoreRefundTaxRows = Db::getInstance()->executeS($sql);
        $result['instore']['refund_taxes'] = array();
        if ($instoreRefundTaxRows) {
            foreach ($instoreRefundTaxRows as $taxRow) {
                $result['instore']['refund_taxes'][(int)$taxRow['id_tax']] = (float)$taxRow['refund_tax'];
            }
        }

        // Add partial refund orders (state 25 + possible_refund_date in range) to Refund Online / Refund Instore
        $partialRefundsForSbpm = $this->getPartialRefundOrders();
        $partialRefundIds = array();
        foreach ($partialRefundsForSbpm as $partialRefund) {
            $partialRefundIds[] = (int)$partialRefund['id_order'];
            if ($partialRefund['is_online']) {
                $result['online']['refund'] += $partialRefund['amount'];
                $result['online']['refund_products_excl'] += $partialRefund['refund_products_tax_excl'];
                $result['online']['refund_products_incl'] += $partialRefund['refund_products_tax_incl'];
                $result['online']['refund_shipping_incl'] += $partialRefund['refund_shipping_tax_incl'];
            } else {
                $result['instore']['refund'] += $partialRefund['amount'];
                $result['instore']['refund_products_excl'] += $partialRefund['refund_products_tax_excl'];
                $result['instore']['refund_products_incl'] += $partialRefund['refund_products_tax_incl'];
                $result['instore']['refund_shipping_incl'] += $partialRefund['refund_shipping_tax_incl'];
            }
        }

        // Get all taxes for partial refund orders (all tax IDs)
        if (!empty($partialRefundIds)) {
            $idsStr = implode(',', $partialRefundIds);
            $sql = '
            SELECT o.id_order, o.total_products as total_products_excl,
                tx.id_tax, tx.order_tax
            FROM ' . _DB_PREFIX_ . 'orders o
            INNER JOIN (
                SELECT od.id_order, odt.id_tax, SUM(odt.total_amount) as order_tax
                FROM ' . _DB_PREFIX_ . 'order_detail od
                INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
                GROUP BY od.id_order, odt.id_tax
            ) tx ON tx.id_order = o.id_order
            WHERE o.id_order IN (' . $idsStr . ')
            ';
            $partialTaxRows = Db::getInstance()->executeS($sql);
            // Build map: $partialTaxMap[$id_order][$id_tax] = [total_products_excl, order_tax]
            $partialTaxMap = array();
            if ($partialTaxRows) {
                foreach ($partialTaxRows as $taxRow) {
                    $orderId = (int)$taxRow['id_order'];
                    $taxId = (int)$taxRow['id_tax'];
                    if (!isset($partialTaxMap[$orderId])) {
                        $partialTaxMap[$orderId] = array(
                            'total_products_excl' => (float)$taxRow['total_products_excl'],
                            'taxes' => array()
                        );
                    }
                    $partialTaxMap[$orderId]['taxes'][$taxId] = (float)$taxRow['order_tax'];
                }
            }
            foreach ($partialRefundsForSbpm as $partialRefund) {
                $orderId = (int)$partialRefund['id_order'];
                if (!isset($partialTaxMap[$orderId])) {
                    continue;
                }
                $orderData = $partialTaxMap[$orderId];
                $totalProductsExcl = $orderData['total_products_excl'];
                $refundProductsExcl = (float)$partialRefund['refund_products_tax_excl'];
                $ratio = ($totalProductsExcl > 0) ? min(1.0, $refundProductsExcl / $totalProductsExcl) : 1.0;
                foreach ($orderData['taxes'] as $taxId => $orderTax) {
                    $taxRefund = $ratio * $orderTax;
                    if ($partialRefund['is_online']) {
                        if (!isset($result['online']['refund_taxes'][$taxId])) {
                            $result['online']['refund_taxes'][$taxId] = 0;
                        }
                        $result['online']['refund_taxes'][$taxId] += $taxRefund;
                    } else {
                        if (!isset($result['instore']['refund_taxes'][$taxId])) {
                            $result['instore']['refund_taxes'][$taxId] = 0;
                        }
                        $result['instore']['refund_taxes'][$taxId] += $taxRefund;
                    }
                }
            }
        }
        
        // ========================================================================
        // DEDUCT REFUNDS FROM TOTALS
        // Refunds are money going back to customers, so they reduce the net total
        // TOTAL = Actual Cash Payments - Refunds
        // ========================================================================
        $result['online']['total'] = $result['online']['total'] - $result['online']['refund'];
        $result['instore']['total'] = $result['instore']['total'] - $result['instore']['refund'];
        
        return $result;
    }
    
    /**
     * Get Tax Summary - All taxes used in orders within date range.
     * Each row returns: tax_name, tax_rate, tax_collected, tax_refunded, tax_amount (=net).
     * Refunds are POS-only and dated by refund date (order_slip.date_add for full refunds,
     * possible_refund_date for partial refunds) — matches dating used elsewhere in the module.
     */
    public function getTaxSummary()
    {
        $excludedStates = Khewareports::getSalesExcludedStatesSQL();
        $refundStates = Khewareports::getRefundStatesSQL();

        // 1) Tax collected on sales in the period (original behavior)
        $sql = '
        SELECT
            t.id_tax,
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate as tax_rate,
            SUM(odt.total_amount) as tax_collected

        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON odt.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '

        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND (
            NOT(
                o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
            )
            OR (
                (
                    o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                    AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                )
                AND o.current_state NOT IN (' . $refundStates . ')
            )
        )

        GROUP BY t.id_tax, tl.name, t.rate
        ';
        $collectedRows = Db::getInstance()->executeS($sql);

        // 1b) Tax collected on gift wrapping in the period (order_invoice_tax type="wrapping").
        // Same order date/state filters as the product-tax query above; merged into the
        // collected totals by id_tax so wrapping tax is included in "Tax Collected".
        $sql = '
        SELECT
            t.id_tax,
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate as tax_rate,
            SUM(oit.amount) as tax_collected

        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice oi ON o.id_order = oi.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oi.id_order_invoice = oit.id_order_invoice
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON oit.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '

        WHERE oit.type = "wrapping"
        AND o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND (
            NOT(
                o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
            )
            OR (
                (
                    o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                    AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                )
                AND o.current_state NOT IN (' . $refundStates . ')
            )
        )

        GROUP BY t.id_tax, tl.name, t.rate
        ';
        $wrappingCollectedRows = Db::getInstance()->executeS($sql);

        // 2) Tax refunded via order_slip (full refunds) within the period — POS only, dated by os.date_add.
        // Pro-rate the original order's tax by refunded products ratio (same approach as SBPM refund_taxes).
        $sql = '
        SELECT tx.id_tax,
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate as tax_rate,
            IFNULL(SUM(
                CASE WHEN o.total_products > 0
                THEN (os.total_products_tax_excl / o.total_products) * tx.order_tax
                ELSE tx.order_tax END
            ), 0) as tax_refunded
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        INNER JOIN (
            SELECT od.id_order, odt.id_tax, SUM(odt.total_amount) as order_tax
            FROM ' . _DB_PREFIX_ . 'order_detail od
            INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
            GROUP BY od.id_order, odt.id_tax
        ) tx ON tx.id_order = o.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON tx.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND ' . $this->getPosModuleCondition('o.module') . '
        GROUP BY tx.id_tax, tl.name, t.rate
        ';
        $slipRefundRows = Db::getInstance()->executeS($sql);

        // 3) Tax refunded via partial refund orders (state 25, dated by possible_refund_date) — POS only.
        // For partial refunds the whole order amount is the refund, so subtract the full per-tax order_tax.
        $partialRefundStateId = (int)Khewareports::getConfiguredStates()['partial_refund'];
        if ($partialRefundStateId <= 0) {
            $partialRefundStateId = (int)Khewareports::DEFAULT_STATE_PARTIAL_REFUND;
        }
        $dateFromOnly = substr($this->date_from, 0, 10);
        $dateToOnly = substr($this->date_to, 0, 10);

        $sql = '
        SELECT odt.id_tax,
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate as tax_rate,
            IFNULL(SUM(odt.total_amount), 0) as tax_refunded
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON odt.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '
        WHERE o.current_state = ' . $partialRefundStateId . '
        AND ' . $this->getPosModuleCondition('o.module') . '
        AND o.possible_refund_date IS NOT NULL
        AND (
            (DATE(o.possible_refund_date) >= "' . pSQL($dateFromOnly) . '" AND DATE(o.possible_refund_date) <= "' . pSQL($dateToOnly) . '")
            OR
            (STR_TO_DATE(o.possible_refund_date, "%y-%m-%d %H:%i:%s") >= "' . $this->date_from . '" AND STR_TO_DATE(o.possible_refund_date, "%y-%m-%d %H:%i:%s") <= "' . $this->date_to . '")
            OR
            (STR_TO_DATE(SUBSTRING(o.possible_refund_date, 1, 8), "%y-%m-%d") >= "' . pSQL($dateFromOnly) . '" AND STR_TO_DATE(SUBSTRING(o.possible_refund_date, 1, 8), "%y-%m-%d") <= "' . pSQL($dateToOnly) . '")
        )
        GROUP BY odt.id_tax, tl.name, t.rate
        ';
        $partialRefundRows = Db::getInstance()->executeS($sql);

        // Merge by id_tax — a tax may appear in collected only, refunded only, or both.
        $byTax = array();
        foreach ((array)$collectedRows as $r) {
            $id = (int)$r['id_tax'];
            $byTax[$id] = array(
                'id_tax' => $id,
                'tax_name' => $r['tax_name'],
                'tax_rate' => (float)$r['tax_rate'],
                'tax_collected' => (float)$r['tax_collected'],
                'tax_refunded' => 0.0,
            );
        }
        // Add gift-wrapping tax to the collected totals (create the tax entry if a tax
        // appears only on wrapping and not on any product line).
        foreach ((array)$wrappingCollectedRows as $r) {
            $id = (int)$r['id_tax'];
            if (!isset($byTax[$id])) {
                $byTax[$id] = array(
                    'id_tax' => $id,
                    'tax_name' => $r['tax_name'],
                    'tax_rate' => (float)$r['tax_rate'],
                    'tax_collected' => 0.0,
                    'tax_refunded' => 0.0,
                );
            }
            $byTax[$id]['tax_collected'] += (float)$r['tax_collected'];
        }
        $applyRefunds = function ($rows) use (&$byTax) {
            foreach ((array)$rows as $r) {
                $id = (int)$r['id_tax'];
                if (!isset($byTax[$id])) {
                    $byTax[$id] = array(
                        'id_tax' => $id,
                        'tax_name' => $r['tax_name'],
                        'tax_rate' => (float)$r['tax_rate'],
                        'tax_collected' => 0.0,
                        'tax_refunded' => 0.0,
                    );
                }
                $byTax[$id]['tax_refunded'] += (float)$r['tax_refunded'];
            }
        };
        $applyRefunds($slipRefundRows);
        $applyRefunds($partialRefundRows);

        $out = array();
        foreach ($byTax as $row) {
            $row['tax_amount'] = $row['tax_collected'] - $row['tax_refunded'];
            $out[] = $row;
        }

        usort($out, function ($a, $b) {
            if ($a['tax_rate'] == $b['tax_rate']) {
                return $a['id_tax'] - $b['id_tax'];
            }
            return ($a['tax_rate'] < $b['tax_rate']) ? -1 : 1;
        });

        return $out;
    }
    



    /**
     * 
     * Get aggregated Sales Summary (totals only)
     */
    public function getSalesSummary()
    {
        $excludedStates = Khewareports::getSalesExcludedStatesSQL();
        $refundStates = Khewareports::getRefundStatesSQL();
        
        $refundDateCond = '
        AND (
            NOT(
                o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
            )
            OR (
                (
                    o.possible_refund_date >= "' . $this->date_from . '" AND o.possible_refund_date <= "' . $this->date_to . '"
                    AND o.date_add >= "' . $this->date_from . '" AND o.date_add <= "' . $this->date_to . '"
                )
                AND o.current_state NOT IN (' . $refundStates . ')
            )
        )';

        $ocrLineAmt = $this->getSqlOrderCartRuleLineAmountSql('ocr', 'cr');
        
        // Base order totals
        $sql = '
        SELECT 
            COUNT(DISTINCT o.id_order) as total_orders,
            SUM(o.total_paid_tax_incl) as total_sales_tax_incl,
            SUM(o.total_paid_tax_excl) as total_sales_tax_excl,
            SUM(o.total_products_wt) as total_products_tax_incl,
            SUM(o.total_products) as total_products_tax_excl,
            SUM(o.total_shipping_tax_incl) as total_shipping_tax_incl,
            SUM(o.total_shipping_tax_excl) as total_shipping_tax_excl,
            SUM(o.total_discounts_tax_incl) as total_discounts_tax_incl,
            SUM(o.total_discounts_tax_excl) as total_discounts_tax_excl
            
        FROM ' . _DB_PREFIX_ . 'orders o
        
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        ';
        
        $result = Db::getInstance()->getRow($sql);
        if (!$result) {
            $result = array();
        }
        
        // Gift Card totals (all orders, not just online)
        $sql = '
        SELECT IFNULL(SUM(' . $ocrLineAmt . '), 0) as total_gift_card
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON cr.id_cart_rule = ocr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND (LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%")
        ';
        $result['total_gift_card'] = (float)Db::getInstance()->getValue($sql);
        
        // Voucher totals (all orders)
        $sql = '
        SELECT IFNULL(SUM(' . $ocrLineAmt . '), 0) as total_voucher
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON cr.id_cart_rule = ocr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND LOWER(ocr.name) LIKE "%voucher%"
        ';
        $result['total_voucher'] = (float)Db::getInstance()->getValue($sql);
        
        // Shipping GST totals
        $sql = '
        SELECT IFNULL(SUM(oit.amount), 0) as total_shipping_gst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice oi ON o.id_order = oi.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oi.id_order_invoice = oit.id_order_invoice
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND oit.id_tax = ' . self::TAX_ID_GST . '
        AND oit.type = "shipping"
        ';
        $result['total_shipping_gst'] = (float)Db::getInstance()->getValue($sql);
        
        // Shipping QST totals
        $sql = '
        SELECT IFNULL(SUM(oit.amount), 0) as total_shipping_qst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice oi ON o.id_order = oi.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_invoice_tax oit ON oi.id_order_invoice = oit.id_order_invoice
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND oit.id_tax IN (' . self::TAX_IDS_QST . ')
        AND oit.type = "shipping"
        ';
        $result['total_shipping_qst'] = (float)Db::getInstance()->getValue($sql);
        
        // Total Refunded Products (from order_detail)
        $sql = '
        SELECT IFNULL(SUM(od.total_refunded_tax_incl), 0) as total_refunded_products
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ';
        $result['total_refunded_products'] = (float)Db::getInstance()->getValue($sql);
        
        // Total Refund Amount (from order_slip - all refunds regardless of refund date)
        $sql = '
        SELECT IFNULL(SUM(os.total_products_tax_incl + os.total_shipping_tax_incl), 0) as total_refund_amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ';
        $result['total_refund_amount'] = (float)Db::getInstance()->getValue($sql);
        
    
        // Product GST totals (from order_detail_tax)
        $sql = '
        SELECT IFNULL(SUM(odt.total_amount), 0) as total_product_gst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND odt.id_tax = ' . self::TAX_ID_GST . '
        ';
        $result['total_product_gst'] = (float)Db::getInstance()->getValue($sql);
        
        // Product QST totals (from order_detail_tax)
        $sql = '
        SELECT IFNULL(SUM(odt.total_amount), 0) as total_product_qst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        ' . $refundDateCond . '
        AND odt.id_tax IN (' . self::TAX_IDS_QST . ')
        ';
        $result['total_product_qst'] = (float)Db::getInstance()->getValue($sql);
        
        return $result;
    }
    
    /**
     * Get aggregated Refunds Summary (totals only)
     */
    public function getRefundsSummary()
    {
        $sql = '
        SELECT 
            COUNT(DISTINCT os.id_order_slip) as total_refunds,
            COUNT(DISTINCT os.id_order) as refunded_orders,
            SUM(os.total_products_tax_incl + os.total_shipping_tax_incl) as total_refund_tax_incl,
            SUM(os.total_products_tax_excl + os.total_shipping_tax_excl) as total_refund_tax_excl,
            SUM(os.total_products_tax_incl) as total_products_refund_tax_incl,
            SUM(os.total_shipping_tax_incl) as total_shipping_refund_tax_incl
            
        FROM ' . _DB_PREFIX_ . 'order_slip os
        
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        ';
        
        $result = Db::getInstance()->getRow($sql);
        return $result ? $result : array();
    }
}
