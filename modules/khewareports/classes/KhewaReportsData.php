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
    
    // POS module name - used to distinguish online vs in-store
    const POS_MODULE = 'hspointofsalepro';
    
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
    }
    
    /**
     * Get Sales Data - Orders within date range based on ORDER DATE
     */
    public function getSalesData()
    {
        $states = Khewareports::getConfiguredStates();
        $excludedStates = implode(',', array(
            (int)$states['canceled'],
            (int)$states['payment_error']
        ));
        
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
            IFNULL(ocr.voucher_value, 0) as voucher_value,
            ocr.voucher_names,
            CASE WHEN LOWER(ocr.voucher_names) LIKE "%gift%" OR LOWER(ocr.voucher_names) LIKE "%cadeau%" 
                 THEN IFNULL(ocr.voucher_value, 0) ELSE 0 END as gift_card_amount,
            IFNULL(slip.total_refund_tax_incl, 0) as total_refund_tax_incl,
            IFNULL(slip.refund_date, "") as refund_date,
            dcl.name as delivery_country,
            ds.name as delivery_state
            
        FROM ' . _DB_PREFIX_ . 'orders o
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl 
            ON o.current_state = osl.id_order_state AND osl.id_lang = ' . $this->id_lang . '
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
            SELECT id_order, 
                   SUM(value) as voucher_value,
                   GROUP_CONCAT(name SEPARATOR ", ") as voucher_names
            FROM ' . _DB_PREFIX_ . 'order_cart_rule
            GROUP BY id_order
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
        
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        
        ORDER BY o.date_add ASC, o.id_order ASC, od.id_order_detail ASC
        ';
        
        return Db::getInstance()->executeS($sql);
    }
    
    /**
     * Get Refunds Data - Refunds within date range based on REFUND DATE
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
        
        return Db::getInstance()->executeS($sql);
    }
    
    /**
     * Get SBPM Data - Complete Sales By Payment Method structure
     * Returns structured data for Combined summary, Online and In-Store sections
     */
    public function getSBPMData()
    {
        $states = Khewareports::getConfiguredStates();
        $excludedStates = implode(',', array(
            (int)$states['canceled'],
            (int)$states['payment_error']
        ));
        
        $result = array(
            'combined' => array(),  // Top summary table
            'online' => array(
                'payments' => array(),
                'gift_card' => 0,
                'voucher' => 0,
                'credit_slip' => 0,
                'discount' => 0,
                'refund' => 0,
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
                'refund' => 0,
                'total' => 0
            )
        );
        
        // ==================== TOP PART: Combined Summary by Payment Method ====================
        // Simpler approach: get base data and join taxes separately
        $sql = '
        SELECT 
            CASE 
                WHEN op.payment_method LIKE "%Credit Card%" OR op.payment_method LIKE "%Carte de crédit%" OR op.payment_method = "Credit Card(instore)" THEN "Credit Card"
                WHEN op.payment_method LIKE "%Cash%" OR op.payment_method LIKE "%Comptant%" THEN "Cash"
                WHEN op.payment_method LIKE "%Interac%" THEN "Interac"
                ELSE op.payment_method
            END as payment_method,
            o.module,
            COUNT(DISTINCT o.id_order) as order_count,
            SUM(DISTINCT o.total_products) as total_products_tax_excl,
            SUM(DISTINCT o.total_products_wt) as total_products_tax_incl,
            SUM(DISTINCT o.total_shipping_tax_incl) as total_shipping_tax_incl,
            SUM(DISTINCT o.total_paid_tax_incl) as total_paid_tax_incl
            
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_payment op ON o.reference = op.order_reference
        
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND op.amount > 0
        
        GROUP BY payment_method, o.module
        ORDER BY total_paid_tax_incl DESC
        ';
        $combinedBase = Db::getInstance()->executeS($sql);
        
        // Get GST by module
        $sql = '
        SELECT o.module, SUM(odt.total_amount) as total_gst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE odt.id_tax = ' . self::TAX_ID_GST . '
        AND o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        GROUP BY o.module
        ';
        $gstByModule = Db::getInstance()->executeS($sql);
        $gstMap = array();
        if ($gstByModule) {
            foreach ($gstByModule as $row) {
                $gstMap[$row['module']] = (float)$row['total_gst'];
            }
        }
        
        // Get QST by module
        $sql = '
        SELECT o.module, SUM(odt.total_amount) as total_qst
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        WHERE odt.id_tax IN (' . self::TAX_IDS_QST . ')
        AND o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        GROUP BY o.module
        ';
        $qstByModule = Db::getInstance()->executeS($sql);
        $qstMap = array();
        if ($qstByModule) {
            foreach ($qstByModule as $row) {
                $qstMap[$row['module']] = (float)$row['total_qst'];
            }
        }
        
        // Get Refunds by module
        $sql = '
        SELECT o.module, SUM(os.total_products_tax_incl) as total_refund
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        GROUP BY o.module
        ';
        $refundByModule = Db::getInstance()->executeS($sql);
        $refundMap = array();
        if ($refundByModule) {
            foreach ($refundByModule as $row) {
                $refundMap[$row['module']] = (float)$row['total_refund'];
            }
        }
        
        // Combine the data - distribute tax proportionally by paid amount
        if ($combinedBase) {
            // First calculate total paid per module
            $totalPaidByModule = array();
            foreach ($combinedBase as $row) {
                $module = $row['module'];
                if (!isset($totalPaidByModule[$module])) {
                    $totalPaidByModule[$module] = 0;
                }
                $totalPaidByModule[$module] += (float)$row['total_paid_tax_incl'];
            }
            
            // Now distribute taxes proportionally
            foreach ($combinedBase as &$row) {
                $module = $row['module'];
                $paidAmount = (float)$row['total_paid_tax_incl'];
                $moduleTotalPaid = $totalPaidByModule[$module];
                
                // Calculate proportion for this payment method
                $proportion = ($moduleTotalPaid > 0) ? ($paidAmount / $moduleTotalPaid) : 0;
                
                // Distribute GST and QST proportionally
                $row['total_gst'] = isset($gstMap[$module]) ? round($gstMap[$module] * $proportion, 2) : 0;
                $row['total_qst'] = isset($qstMap[$module]) ? round($qstMap[$module] * $proportion, 2) : 0;
                
                // Refunds by module type
                $row['refund_online'] = ($module != self::POS_MODULE && isset($refundMap[$module])) ? $refundMap[$module] : 0;
                $row['refund_instore'] = ($module == self::POS_MODULE && isset($refundMap[$module])) ? $refundMap[$module] : 0;
            }
            $result['combined'] = $combinedBase;
        }
        
        // Get ONLINE payment methods from order_payment
        $sql = '
        SELECT 
            op.payment_method,
            o.module,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_payment op ON o.reference = op.order_reference
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module != "' . self::POS_MODULE . '"
        AND op.amount > 0
        GROUP BY op.payment_method, o.module
        ORDER BY payment_amount DESC
        ';
        $onlinePayments = Db::getInstance()->executeS($sql);
        if ($onlinePayments) {
            $result['online']['payments'] = $onlinePayments;
            foreach ($onlinePayments as $p) {
                $result['online']['total'] += (float)$p['payment_amount'];
            }
        }
        
        // Get IN-STORE payment methods from order_payment
        $sql = '
        SELECT 
            op.payment_method,
            o.module,
            SUM(op.amount) as payment_amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_payment op ON o.reference = op.order_reference
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module = "' . self::POS_MODULE . '"
        AND op.amount > 0
        GROUP BY op.payment_method, o.module
        ORDER BY payment_amount DESC
        ';
        $instorePayments = Db::getInstance()->executeS($sql);
        if ($instorePayments) {
            $result['instore']['payments'] = $instorePayments;
            foreach ($instorePayments as $p) {
                $result['instore']['total'] += (float)$p['payment_amount'];
            }
        }
        
        // Get ONLINE Gift Card usage (cart rule with "gift" or "cadeau")
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module != "' . self::POS_MODULE . '"
        AND (LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%")
        ';
        $giftOnline = Db::getInstance()->getValue($sql);
        $result['online']['gift_card'] = (float)$giftOnline;
        
        // Get IN-STORE Gift Card usage
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module = "' . self::POS_MODULE . '"
        AND (LOWER(ocr.name) LIKE "%gift%" OR LOWER(ocr.name) LIKE "%cadeau%")
        ';
        $giftInstore = Db::getInstance()->getValue($sql);
        $result['instore']['gift_card'] = (float)$giftInstore;
        
        // Get ONLINE Voucher usage
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module != "' . self::POS_MODULE . '"
        AND LOWER(ocr.name) LIKE "%voucher%"
        ';
        $voucherOnline = Db::getInstance()->getValue($sql);
        $result['online']['voucher'] = (float)$voucherOnline;
        
        // Get IN-STORE Voucher usage
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module = "' . self::POS_MODULE . '"
        AND LOWER(ocr.name) LIKE "%voucher%"
        ';
        $voucherInstore = Db::getInstance()->getValue($sql);
        $result['instore']['voucher'] = (float)$voucherInstore;
        
        // Get ONLINE Credit Slip usage (cart_rule description contains "slip")
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module != "' . self::POS_MODULE . '"
        AND LOWER(cr.description) LIKE "%slip%"
        ';
        $creditSlipOnline = Db::getInstance()->getValue($sql);
        $result['online']['credit_slip'] = (float)$creditSlipOnline;
        
        // Get IN-STORE Credit Slip usage
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON ocr.id_cart_rule = cr.id_cart_rule
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module = "' . self::POS_MODULE . '"
        AND LOWER(cr.description) LIKE "%slip%"
        ';
        $creditSlipInstore = Db::getInstance()->getValue($sql);
        $result['instore']['credit_slip'] = (float)$creditSlipInstore;
        
        // Get ONLINE Discount (promocode)
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module != "' . self::POS_MODULE . '"
        AND LOWER(ocr.name) LIKE "%promocode%"
        ';
        $discountOnline = Db::getInstance()->getValue($sql);
        $result['online']['discount'] = (float)$discountOnline;
        
        // Get IN-STORE Discount (Point of Sale discount)
        $sql = '
        SELECT IFNULL(SUM(ocr.value), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_cart_rule ocr ON o.id_order = ocr.id_order
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        AND o.module = "' . self::POS_MODULE . '"
        AND LOWER(ocr.name) LIKE "%point of sale%"
        ';
        $discountInstore = Db::getInstance()->getValue($sql);
        $result['instore']['discount'] = (float)$discountInstore;
        
        // Get ONLINE Refunds
        $sql = '
        SELECT IFNULL(SUM(os.total_products_tax_incl), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND o.module != "' . self::POS_MODULE . '"
        ';
        $refundOnline = Db::getInstance()->getValue($sql);
        $result['online']['refund'] = (float)$refundOnline;
        
        // Get IN-STORE Refunds
        $sql = '
        SELECT IFNULL(SUM(os.total_products_tax_incl), 0) as amount
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_slip os ON o.id_order = os.id_order
        WHERE os.date_add >= "' . $this->date_from . '"
        AND os.date_add <= "' . $this->date_to . '"
        AND o.module = "' . self::POS_MODULE . '"
        ';
        $refundInstore = Db::getInstance()->getValue($sql);
        $result['instore']['refund'] = (float)$refundInstore;
        
        return $result;
    }
    
    /**
     * Get Tax Summary - All taxes used in orders within date range
     */
    public function getTaxSummary()
    {
        $states = Khewareports::getConfiguredStates();
        $excludedStates = implode(',', array(
            (int)$states['canceled'],
            (int)$states['payment_error']
        ));
        
        $sql = '
        SELECT 
            IFNULL(tl.name, CONCAT("Tax ", t.id_tax)) as tax_name,
            t.rate as tax_rate,
            SUM(odt.total_amount) as tax_amount
            
        FROM ' . _DB_PREFIX_ . 'orders o
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
        INNER JOIN ' . _DB_PREFIX_ . 'order_detail_tax odt ON od.id_order_detail = odt.id_order_detail
        INNER JOIN ' . _DB_PREFIX_ . 'tax t ON odt.id_tax = t.id_tax
        LEFT JOIN ' . _DB_PREFIX_ . 'tax_lang tl ON t.id_tax = tl.id_tax AND tl.id_lang = ' . $this->id_lang . '
        
        WHERE o.date_add >= "' . $this->date_from . '"
        AND o.date_add <= "' . $this->date_to . '"
        AND o.current_state NOT IN (' . $excludedStates . ')
        
        GROUP BY t.id_tax, tl.name, t.rate
        ORDER BY t.rate ASC
        ';
        
        return Db::getInstance()->executeS($sql);
    }
    
    /**
     * Get aggregated Sales Summary (totals only)
     */
    public function getSalesSummary()
    {
        $states = Khewareports::getConfiguredStates();
        $excludedStates = implode(',', array(
            (int)$states['canceled'],
            (int)$states['payment_error']
        ));
        
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
        ';
        
        $result = Db::getInstance()->getRow($sql);
        return $result ? $result : array();
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
