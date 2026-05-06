<?php
/**
 * Khewa Reports - Reports Controller
 * Handles Sales, Refunds, SBPM, and Taxes export
 */

if (!defined('_PS_VERSION_')) {
    exit;
}



require_once _PS_MODULE_DIR_ . 'khewareports/classes/KhewaReportsData.php';

class AdminKhewaReportsReportsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function init()
    {
        parent::init();
        
        // Handle export action
        if (Tools::isSubmit('submitKhewaReportsExport')) {
            $this->processExport();
        }
    }

    public function initContent()
    {
        parent::initContent();
        
        // Add jQuery UI datepicker
        $this->addJqueryUI('ui.datepicker');
        
        // Get date values from POST or set defaults
        $date_from = Tools::getValue('date_from', date('Y-m-d', strtotime('-30 days')));
        $date_to = Tools::getValue('date_to', date('Y-m-d'));
        
        $this->context->smarty->assign(array(
            'action_url' => $this->context->link->getAdminLink('AdminKhewaReportsReports'),
            'date_from' => $date_from,
            'date_to' => $date_to
        ));
        
        $this->content = $this->context->smarty->fetch($this->getTemplatePath().'reports.tpl');
        $this->context->smarty->assign('content', $this->content);
    }

    public function processExport($text_delimiter = '"')
    {
        $date_from = Tools::getValue('date_from');
        $date_to = Tools::getValue('date_to');
        
        // Validate dates
        if (empty($date_from) || empty($date_to)) {
            $this->errors[] = $this->module->l('Please select both date from and date to.');
            return;
        }
        
        // Generate Excel file
        $this->generateExcelExport($date_from, $date_to);
    }

    public function generateExcelExport($date_from, $date_to)
    {
        // Use PhpSpreadsheet from the ordersexportsalesreportpro module
        $phpspreadsheet_path = _PS_MODULE_DIR_ . 'ordersexportsalesreportpro/vendor/autoload.php';
        
        if (!file_exists($phpspreadsheet_path)) {
            $this->errors[] = $this->module->l('PhpSpreadsheet library not found.');
            return;
        }
        
        require_once $phpspreadsheet_path;
        
        // Initialize data fetcher
        $dataFetcher = new KhewaReportsData($date_from, $date_to);
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Khewa Reports')
            ->setLastModifiedBy('Khewa Reports')
            ->setTitle('Khewa Reports Export')
            ->setSubject('Reports Export')
            ->setDescription('Generated report from Khewa Reports module');
        
        // Create Sales sheet (first/default sheet)
        $salesSheet = $spreadsheet->getActiveSheet();
        $salesSheet->setTitle('Sales');
        $this->populateSalesSheet($salesSheet, $dataFetcher, $date_from, $date_to);
        
        // Create Refunds sheet
        $refundsSheet = $spreadsheet->createSheet();
        $refundsSheet->setTitle('Refunds');
        $this->populateRefundsSheet($refundsSheet, $dataFetcher, $date_from, $date_to);
        
        // Create SBPM sheet (Sales By Payment Method)
        $sbpmSheet = $spreadsheet->createSheet();
        $sbpmSheet->setTitle('Sales by Payment Methods');
        $this->populateSBPMSheet($sbpmSheet, $dataFetcher, $date_from, $date_to);
        
        // Create Taxes sheet
        $taxesSheet = $spreadsheet->createSheet();
        $taxesSheet->setTitle('Taxes');
        $this->populateTaxesSheet($taxesSheet, $dataFetcher, $date_from, $date_to);
        
        // Set first sheet (Sales) as active
        $spreadsheet->setActiveSheetIndex(0);
        
        // Clear output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output file
        $filename = 'khewa_reports_' . $date_from . '_to_' . $date_to . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        die();
    }
    
    /**
     * Safe cell value setter - converts all values to strings to avoid PhpSpreadsheet type issues
     */
    protected function setCellValueSafe($sheet, $cell, $value)
    {
        // Convert value to string to avoid type issues with older PhpSpreadsheet
        if ($value === null || $value === '') {
            $sheet->setCellValue($cell, '');
        } elseif (is_numeric($value)) {
            $sheet->setCellValueExplicit($cell, (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        } else {
            $sheet->setCellValue($cell, (string)$value);
        }
    }
    
    /**
     * Set numeric cell value with proper formatting (2 decimal places, no trailing zeros)
     * Rounds value to 2 decimals to avoid floating point precision issues
     * Uses clean string approach to avoid PhpSpreadsheet float type detection issues
     */
    protected function setNumericValue($sheet, $cell, $value)
    {
        // Round to 2 decimal places to fix floating point precision issues
        $numericValue = is_numeric($value) ? round((float)$value, 2) : 0;
        
        // Use number_format to create a clean string representation with exactly 2 decimals
        $cleanString = number_format($numericValue, 2, '.', '');
        
        // Simply set the clean string - PhpSpreadsheet will auto-detect it as numeric
        // This avoids the float precision issues that cause DefaultValueBinder errors
        $sheet->setCellValue($cell, $cleanString);
        
        // Apply number format that shows up to 2 decimals but not trailing zeros
        $sheet->getStyle($cell)->getNumberFormat()
            ->setFormatCode('#,##0.##');
    }

    /**
     * Populate Sales sheet with real data
     */
    protected function populateSalesSheet($sheet, $dataFetcher, $date_from, $date_to)
    {
        // Add header row (width = top table columns A–X for print)
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Sales Report', 'X');
        
        // Column headers (Row 2) - Order State after Invoice Number; Payment Breakdown just before Total Products With Tax
        $headers = array(
            'A' => 'Ordered At',
            'B' => 'Order ID',
            'C' => 'Invoice Number',
            'D' => 'Order State',
            'E' => 'Gift Card Payment',
            'F' => 'Credit Slip',
            'G' => 'Voucher',
            'H' => 'Total Shipping (Tax incl)',
            'I' => 'Shipping Tax (CA 5%)',
            'J' => 'Shipping Tax (CA-QC 9.975%)',
            'K' => 'Total Refunded Products (Tax incl)',
            'L' => 'Refunded Amount',
            'M' => 'Total Refunds ROCK (Tax incl)',
            'N' => 'Payment Breakdown',
            'O' => 'Total Products With Tax',
            'P' => 'Payment Method',
            'Q' => 'Product Name',
            'R' => 'Total Price (Tax incl)',
            'S' => 'Total Price (Tax excl)',
            'T' => 'Total Amount (CA 5%)',
            'U' => 'Total Amount (CA-QC 9.975%)',
            'V' => 'Total Shipping Price (Tax excl)',
            'W' => 'Delivery Country',
            'X' => 'Delivery State'
        );
        
        foreach ($headers as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        
        // Style header row
        $this->styleHeaderRow($sheet, 'A2:X2');
        
        // Get sales data
        $salesData = $dataFetcher->getSalesData();
        
        // Populate data rows (starting from row 3)
        // Initialize running totals - calculated from loop data to ensure consistency
        $row = 3;
        $lastOrderId = null;
        $processedOrders = array(); // Track which orders we've counted for order-level totals
        $paymentMethodTotals = array(); // Track total amounts per payment method for summary
        $totals = array(
            'gift_card' => 0,
            'credit_slip' => 0,
            'voucher' => 0,
            'shipping_incl' => 0,
            'shipping_gst' => 0,
            'shipping_qst' => 0,
            'refunded_products' => 0,
            'refund_amount' => 0,
            'products_incl' => 0,      // Will use order-level total_products_tax_incl (from orders.total_products_wt)
            'products_excl' => 0,      // Will use order-level total_products_tax_excl (from orders.total_products)
            'product_gst' => 0,
            'product_qst' => 0,
            'shipping_excl' => 0
        );
        
        foreach ($salesData as $data) {
            // Show order-level data only on first product row
            $showOrderData = ($lastOrderId != $data['id_order']);
            
            $this->setCellValueSafe($sheet, 'A' . $row, $showOrderData ? $data['order_date'] : '');
            $this->setCellValueSafe($sheet, 'B' . $row, $showOrderData ? $data['id_order'] : '');
            $this->setCellValueSafe($sheet, 'C' . $row, $showOrderData ? '#ND' . $data['invoice_number'] : '');
            
            // Column D: Order State - name in English with state id in brackets (e.g. "Payment accepted (2)")
            $orderStateDisplay = '';
            if ($showOrderData && isset($data['current_state']) && $data['current_state'] !== '' && $data['current_state'] !== null) {
                $stateId = (int)$data['current_state'];
                $nameEn = isset($data['order_state_name_en']) ? trim((string)$data['order_state_name_en']) : '';
                $nameDefault = isset($data['order_state_name']) ? trim((string)$data['order_state_name']) : '';
                $stateName = $nameEn !== '' ? $nameEn : ($nameDefault !== '' ? $nameDefault : 'State ' . $stateId);
                $orderStateDisplay = $stateName . ' (' . $stateId . ')';
            }
            $this->setCellValueSafe($sheet, 'D' . $row, $orderStateDisplay);
            
            // Column E: Gift Card - order level, add only once per order
            if ($showOrderData && $data['gift_card_amount']) {
                $this->setNumericValue($sheet, 'E' . $row, $data['gift_card_amount']);
                $totals['gift_card'] += (float)$data['gift_card_amount'];
            } else {
                $this->setCellValueSafe($sheet, 'E' . $row, '');
            }
            
            // Column F: Credit Slip - order level
            if ($showOrderData && !empty($data['credit_slip_amount'])) {
                $this->setNumericValue($sheet, 'F' . $row, $data['credit_slip_amount']);
                $totals['credit_slip'] += (float)$data['credit_slip_amount'];
            } else {
                $this->setCellValueSafe($sheet, 'F' . $row, '');
            }

            // Column G: Voucher - order level
            if ($showOrderData && !empty($data['voucher_amount_sales'])) {
                $this->setNumericValue($sheet, 'G' . $row, $data['voucher_amount_sales']);
                $totals['voucher'] += (float)$data['voucher_amount_sales'];
            } else {
                $this->setCellValueSafe($sheet, 'G' . $row, '');
            }
            
            // Column H: Shipping (Tax incl) - order level
            if ($showOrderData && $data['total_shipping_tax_incl']) {
                $this->setNumericValue($sheet, 'H' . $row, $data['total_shipping_tax_incl']);
                $totals['shipping_incl'] += (float)$data['total_shipping_tax_incl'];
            } else {
                $this->setCellValueSafe($sheet, 'H' . $row, '');
            }
            
            // Column I: Shipping GST - order level
            if ($showOrderData && $data['shipping_gst_amount']) {
                $this->setNumericValue($sheet, 'I' . $row, $data['shipping_gst_amount']);
                $totals['shipping_gst'] += (float)$data['shipping_gst_amount'];
            } else {
                $this->setCellValueSafe($sheet, 'I' . $row, '');
            }
            
            // Column J: Shipping QST - order level
            if ($showOrderData && $data['shipping_qst_amount']) {
                $this->setNumericValue($sheet, 'J' . $row, $data['shipping_qst_amount']);
                $totals['shipping_qst'] += (float)$data['shipping_qst_amount'];
            } else {
                $this->setCellValueSafe($sheet, 'J' . $row, '');
            }
            
            // Column K: Refunded Products - product level (sum all)
            $this->setNumericValue($sheet, 'K' . $row, $data['total_refunded_tax_incl']);
            $totals['refunded_products'] += (float)$data['total_refunded_tax_incl'];
            
            // Column L: Refund Amount - order level (displayed per product but count once)
            $this->setNumericValue($sheet, 'L' . $row, $data['total_refund_tax_incl']);
            
            // Column M: Total Refunds ROCK - order level
            if ($showOrderData && $data['total_refund_tax_incl']) {
                $this->setNumericValue($sheet, 'M' . $row, $data['total_refund_tax_incl']);
                $totals['refund_amount'] += (float)$data['total_refund_tax_incl'];
            } else {
                $this->setCellValueSafe($sheet, 'M' . $row, '');
            }
            
            // Column N: Payment Breakdown - always shows payment method(s) with amounts (just before Total Products With Tax)
            $this->setCellValueSafe($sheet, 'N' . $row, $showOrderData ? $data['payment_breakdown'] : '');
            
            // Track payment method totals (parse from breakdown, once per order)
            if ($showOrderData && !empty($data['payment_breakdown'])) {
                $parts = explode(' - ', $data['payment_breakdown']);
                foreach ($parts as $part) {
                    if (preg_match('/^(.+?)\(\$([0-9.]+)\)$/', trim($part), $matches)) {
                        $methodName = trim($matches[1]);
                        $methodAmount = (float)$matches[2];
                        if (!isset($paymentMethodTotals[$methodName])) {
                            $paymentMethodTotals[$methodName] = 0;
                        }
                        $paymentMethodTotals[$methodName] += $methodAmount;
                    }
                }
            }
            
            // Column O: Total Products With Tax - show product level detail
            $this->setNumericValue($sheet, 'O' . $row, $data['total_price_tax_incl']);
            
            // For TOTALS, use order-level values (orders.total_products_wt) - only count each order once
            if ($showOrderData) {
                $totals['products_incl'] += (float)$data['total_products_tax_incl'];  // order-level (from orders.total_products_wt)
                $totals['products_excl'] += (float)$data['total_products_tax_excl'];  // order-level (from orders.total_products)
            }
            
            $this->setCellValueSafe($sheet, 'P' . $row, $showOrderData ? $data['payment'] : '');
            $this->setCellValueSafe($sheet, 'Q' . $row, $data['product_name']);
            
            // Column R: Total Price (Tax incl) - show product level detail
            $this->setNumericValue($sheet, 'R' . $row, $data['total_price_tax_incl']);
            
            // Column S: Total Price (Tax excl) - show product level detail
            $this->setNumericValue($sheet, 'S' . $row, $data['total_price_tax_excl']);
            
            // Column T: Product GST - product level (sum all)
            $this->setNumericValue($sheet, 'T' . $row, $data['gst_total_amount']);
            $totals['product_gst'] += (float)$data['gst_total_amount'];
            
            // Column U: Product QST - product level (sum all)
            $this->setNumericValue($sheet, 'U' . $row, $data['qst_total_amount']);
            $totals['product_qst'] += (float)$data['qst_total_amount'];
            
            // Column V: Shipping (Tax excl) - order level
            if ($showOrderData && $data['total_shipping_tax_excl']) {
                $this->setNumericValue($sheet, 'V' . $row, $data['total_shipping_tax_excl']);
                $totals['shipping_excl'] += (float)$data['total_shipping_tax_excl'];
            } else {
                $this->setCellValueSafe($sheet, 'V' . $row, '');
            }
            
            $this->setCellValueSafe($sheet, 'W' . $row, $showOrderData ? $data['delivery_country'] : '');
            $this->setCellValueSafe($sheet, 'X' . $row, $showOrderData ? $data['delivery_state'] : '');
            
            $lastOrderId = $data['id_order'];
            $row++;
        }

        // Add partial refund orders (possible_refund_date) to Sales refund total
        // Partial refund orders are excluded from getSalesData, so they are not in the loop above
        $totals['refund_amount'] += (float)$dataFetcher->getPartialRefundTotal();

        // Add totals row - using calculated sums from loop (not separate query)
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 3) {
            $row++;
            $this->setCellValueSafe($sheet, 'A' . $row, 'TOTALS');
            
            // Column N: Payment Breakdown - show total per payment method, one per line
            if (!empty($paymentMethodTotals)) {
                arsort($paymentMethodTotals);
                $breakdownParts = array();
                foreach ($paymentMethodTotals as $method => $amount) {
                    $breakdownParts[] = $method . ' ' . number_format($amount, 2, '.', '');
                }
                $breakdownText = implode("\n", $breakdownParts);
                $this->setCellValueSafe($sheet, 'N' . $row, $breakdownText);
                $sheet->getStyle('N' . $row)->getAlignment()->setWrapText(true);
                $lineCount = count($breakdownParts);
                $rowHeight = max(30, min(400, 18 * $lineCount));
                $sheet->getRowDimension($row)->setRowHeight($rowHeight);
            }
            
            $this->setNumericValue($sheet, 'E' . $row, $totals['gift_card']);
            $this->setNumericValue($sheet, 'F' . $row, $totals['credit_slip']);
            $this->setNumericValue($sheet, 'G' . $row, $totals['voucher']);
            $this->setNumericValue($sheet, 'H' . $row, $totals['shipping_incl']);
            $this->setNumericValue($sheet, 'I' . $row, $totals['shipping_gst']);
            $this->setNumericValue($sheet, 'J' . $row, $totals['shipping_qst']);
            $this->setNumericValue($sheet, 'K' . $row, $totals['refunded_products']);
            $this->setNumericValue($sheet, 'L' . $row, $totals['refund_amount']);
            $this->setNumericValue($sheet, 'M' . $row, $totals['refund_amount']);
            $this->setNumericValue($sheet, 'O' . $row, $totals['products_incl']);
            $this->setNumericValue($sheet, 'R' . $row, $totals['products_incl']);
            $this->setNumericValue($sheet, 'S' . $row, $totals['products_excl']);
            $this->setNumericValue($sheet, 'T' . $row, $totals['product_gst']);
            $this->setNumericValue($sheet, 'U' . $row, $totals['product_qst']);
            $this->setNumericValue($sheet, 'V' . $row, $totals['shipping_excl']);
            $sheet->getStyle('A' . $row . ':X' . $row)->getFont()->setBold(true);
            
            // Add column headers again after totals row for reference when scrolling
            $row++;
            foreach ($headers as $column => $header) {
                $this->setCellValueSafe($sheet, $column . $row, $header);
            }
            // Style header row (same as top header) - row height is set automatically by styleHeaderRow
            $this->styleHeaderRow($sheet, 'A' . $row . ':X' . $row);
        }
        
        // Style data rows and set column widths
        $this->styleDataRows($sheet, 'A3:X' . $row);
        $this->setColumnWidths($sheet, array(
            'A' => 12, 'B' => 10, 'C' => 14, 'D' => 22, 'E' => 12, 'F' => 10,
            'G' => 10, 'H' => 16, 'I' => 14, 'J' => 16, 'K' => 18, 'L' => 14,
            'M' => 18, 'N' => 35, 'O' => 16, 'P' => 14, 'Q' => 30, 'R' => 14,
            'S' => 14, 'T' => 14, 'U' => 16, 'V' => 16, 'W' => 14, 'X' => 14
        ));
        
        // Apply number formatting to numeric columns (2 decimal places)
        if ($row > 3) {
            $this->applyNumberFormat($sheet, 'E3:E' . $row); // Gift Card
            $this->applyNumberFormat($sheet, 'F3:F' . $row); // Credit Slip
            $this->applyNumberFormat($sheet, 'G3:G' . $row); // Voucher
            $this->applyNumberFormat($sheet, 'H3:J' . $row); // Shipping amounts
            $this->applyNumberFormat($sheet, 'K3:M' . $row); // Refund amounts
            $this->applyNumberFormat($sheet, 'O3:O' . $row); // Product price tax incl
            $this->applyNumberFormat($sheet, 'R3:U' . $row); // Product prices and taxes
            $this->applyNumberFormat($sheet, 'V3:V' . $row); // Shipping tax excl
        }
        
        // Set auto filter
        $sheet->setAutoFilter('A2:X' . $row);
        
        // Apply print settings - landscape for wide sheet with many columns
        $this->applyPrintSettings($sheet, 'landscape');
    }

    /**
     * Populate Refunds sheet with real data
     */
    protected function populateRefundsSheet($sheet, $dataFetcher, $date_from, $date_to)
    {
        // Add header row (width = top table columns A–R for print)
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Refunds Report (by Refund Date)', 'R');
        
        // Column headers (Row 2)
        $headers = array(
            'A' => 'Refund Date',
            'B' => 'Order Date',
            'C' => 'Order ID',
            'D' => 'Invoice Number',
            'E' => 'Credit Slip ID',
            'F' => 'Partial Refund',
            'G' => 'Payment Method',
            'H' => 'Product Name',
            'I' => 'Refunded Qty',
            'J' => 'Product Refund (Tax incl)',
            'K' => 'Product Refund (Tax excl)',
            'L' => 'Refund GST (5%)',
            'M' => 'Refund QST (9.975%)',
            'N' => 'Total Refund (Tax incl)',
            'O' => 'Total Refund (Tax excl)',
            'P' => 'Shipping Refund (Tax incl)',
            'Q' => 'Delivery Country',
            'R' => 'Delivery State'
        );
        
        foreach ($headers as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        
        // Style header row
        $this->styleHeaderRow($sheet, 'A2:R2');
        
        // Get refunds data
        $refundsData = $dataFetcher->getRefundsData();
        
        // Populate data rows (starting from row 3)
        // Initialize running totals - calculated from loop data
        $row = 3;
        $lastSlipId = null;
        $totals = array(
            'refund_count' => 0,
            'refunded_qty' => 0,
            'product_refund_incl' => 0,
            'product_refund_excl' => 0,
            'refund_gst' => 0,
            'refund_qst' => 0,
            'total_refund_incl' => 0,
            'total_refund_excl' => 0,
            'shipping_refund' => 0
        );
        
        foreach ($refundsData as $data) {
            // Show slip-level data only on first product row
            $showSlipData = ($lastSlipId != $data['id_order_slip']);
            
            $this->setCellValueSafe($sheet, 'A' . $row, $showSlipData ? $data['refund_date'] : '');
            $this->setCellValueSafe($sheet, 'B' . $row, $showSlipData ? $data['order_date'] : '');
            $this->setCellValueSafe($sheet, 'C' . $row, $showSlipData ? $data['id_order'] : '');
            $this->setCellValueSafe($sheet, 'D' . $row, $showSlipData ? '#ND' . $data['invoice_number'] : '');
            $this->setCellValueSafe($sheet, 'E' . $row, $showSlipData ? $data['id_order_slip'] : '');
            $this->setCellValueSafe($sheet, 'F' . $row, $showSlipData ? ($data['is_partial_refund'] ? 'Yes' : 'No') : '');
            $this->setCellValueSafe($sheet, 'G' . $row, $showSlipData ? $data['payment'] : '');
            $this->setCellValueSafe($sheet, 'H' . $row, $data['product_name']);
            
            // Column I: Refunded Qty - product level
            $this->setNumericValue($sheet, 'I' . $row, $data['refunded_quantity']);
            $totals['refunded_qty'] += (int)$data['refunded_quantity'];
            
            // Column J: Product Refund (Tax incl) - product level
            $this->setNumericValue($sheet, 'J' . $row, $data['product_refund_tax_incl']);
            $totals['product_refund_incl'] += (float)$data['product_refund_tax_incl'];
            
            // Column K: Product Refund (Tax excl) - product level
            $this->setNumericValue($sheet, 'K' . $row, $data['product_refund_tax_excl']);
            $totals['product_refund_excl'] += (float)$data['product_refund_tax_excl'];
            
            // Column L: Refund GST - product level
            $this->setNumericValue($sheet, 'L' . $row, $data['refund_gst_amount']);
            $totals['refund_gst'] += (float)$data['refund_gst_amount'];
            
            // Column M: Refund QST - product level
            $this->setNumericValue($sheet, 'M' . $row, $data['refund_qst_amount']);
            $totals['refund_qst'] += (float)$data['refund_qst_amount'];
            
            // Column N: Total Refund (Tax incl) - slip level
            if ($showSlipData) {
                $this->setNumericValue($sheet, 'N' . $row, $data['total_refund_tax_incl']);
                $totals['total_refund_incl'] += (float)$data['total_refund_tax_incl'];
                $totals['refund_count']++;
            } else {
                $this->setCellValueSafe($sheet, 'N' . $row, '');
            }
            
            // Column O: Total Refund (Tax excl) - slip level
            if ($showSlipData) {
                $this->setNumericValue($sheet, 'O' . $row, $data['total_refund_tax_excl']);
                $totals['total_refund_excl'] += (float)$data['total_refund_tax_excl'];
            } else {
                $this->setCellValueSafe($sheet, 'O' . $row, '');
            }
            
            // Column P: Shipping Refund - slip level
            if ($showSlipData) {
                $this->setNumericValue($sheet, 'P' . $row, $data['refund_shipping_tax_incl']);
                $totals['shipping_refund'] += (float)$data['refund_shipping_tax_incl'];
            } else {
                $this->setCellValueSafe($sheet, 'P' . $row, '');
            }
            
            $this->setCellValueSafe($sheet, 'Q' . $row, $showSlipData ? $data['delivery_country'] : '');
            $this->setCellValueSafe($sheet, 'R' . $row, $showSlipData ? $data['delivery_state'] : '');
            
            $lastSlipId = $data['id_order_slip'];
            $row++;
        }
        


        // Add totals row - using calculated sums from loop
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 3) {
            $row++;
            $this->setCellValueSafe($sheet, 'A' . $row, 'TOTALS');
            $this->setCellValueSafe($sheet, 'E' . $row, $totals['refund_count'] . ' refunds');
            $this->setNumericValue($sheet, 'I' . $row, $totals['refunded_qty']);
            $this->setNumericValue($sheet, 'J' . $row, $totals['product_refund_incl']);
            $this->setNumericValue($sheet, 'K' . $row, $totals['product_refund_excl']);
            $this->setNumericValue($sheet, 'L' . $row, $totals['refund_gst']);
            $this->setNumericValue($sheet, 'M' . $row, $totals['refund_qst']);
            $this->setNumericValue($sheet, 'N' . $row, $totals['total_refund_incl']);
            $this->setNumericValue($sheet, 'O' . $row, $totals['total_refund_excl']);
            $this->setNumericValue($sheet, 'P' . $row, $totals['shipping_refund']);
            $sheet->getStyle('A' . $row . ':R' . $row)->getFont()->setBold(true);
        }
        
        // Style data rows and set column widths
        $this->styleDataRows($sheet, 'A3:R' . $row);
        $this->setColumnWidths($sheet, array(
            'A' => 12, 'B' => 12, 'C' => 10, 'D' => 14, 'E' => 12, 'F' => 12, 'G' => 14,
            'H' => 30, 'I' => 12, 'J' => 16, 'K' => 16, 'L' => 12, 'M' => 14, 'N' => 16,
            'O' => 16, 'P' => 16, 'Q' => 14, 'R' => 14
        ));
        
        // Apply number formatting to numeric columns (2 decimal places)
        if ($row > 3) {
            $this->applyNumberFormat($sheet, 'I3:I' . $row); // Refunded quantity
            $this->applyNumberFormat($sheet, 'J3:P' . $row); // All refund amounts
        }
        
        // Set auto filter
        $sheet->setAutoFilter('A2:R' . $row);
        
        // Apply print settings - landscape for better fit
        $this->applyPrintSettings($sheet, 'landscape');
    }




    /**
     * Populate SBPM (Sales By Payment Method) sheet
     * Two parts: TOP summary table + BOTTOM Online/In-Store breakdown
     */
    protected function populateSBPMSheet($sheet, $dataFetcher, $date_from, $date_to)
    {
        // Get POS module name from configuration (use first one for display)
        $patterns = Khewareports::getPaymentMethodPatterns();
        $posModuleName = !empty($patterns['pos_module']) ? $patterns['pos_module'][0] : 'hspointofsalepro';

        // Get SBPM data
        $sbpmData = $dataFetcher->getSBPMData();

        // ==================== Build dynamic tax column map ====================
        // Fixed columns: A=Combined Payment, B=Confirmed Orders, C=Products Excl, D=Products Incl,
        //                E=Shipping, F=Total Sum, G=Total Bill (Tax Incl.)  → tax columns start at index 8 (H)
        $taxColumns = isset($sbpmData['tax_columns']) ? $sbpmData['tax_columns'] : array();
        $taxColMap = array(); // id_tax => column letter
        foreach ($taxColumns as $i => $tax) {
            $colIndex = 8 + $i; // H=8, I=9, ...
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $taxColMap[(int)$tax['id_tax']] = $colLetter;
        }
        $lastTaxColIndex = 7 + count($taxColumns);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(7, $lastTaxColIndex));

        // Add sheet header spanning full dynamic width
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Sales By Payment Method', $lastColLetter);

        // ==================== TOP PART: Summary Table ====================
        // Column headers (Row 2) — Module column removed; J/K Refund columns removed
        $topHeaders = array(
            'A' => 'Combined Payment',
            'B' => 'Confirmed Orders',
            'C' => 'Total Products (Tax Excl.)',
            'D' => 'Total Products (Tax Incl.)',
            'E' => 'Total Shipping (Tax Incl.)',
            'F' => 'Total Sum (Tax Incl)',
            'G' => 'Total Bill (Tax Incl.)',
        );
        // Add one column per active tax type
        foreach ($taxColumns as $tax) {
            $colLetter = $taxColMap[(int)$tax['id_tax']];
            $topHeaders[$colLetter] = $tax['tax_name'] . ' (' . $tax['rate'] . '%)';
        }
        // Commented out: J => 'Refund Online (Tax Incl.)', K => 'Refund Instore (Tax Incl.)'

        foreach ($topHeaders as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        $this->styleHeaderRow($sheet, 'A2:' . $lastColLetter . '2');
        $sheet->getRowDimension(2)->setRowHeight(30);

        // Populate top summary rows (combined by payment method)
        $row = 3;
        $totalOrders = 0;
        $totalProductsExcl = 0;
        $totalProductsIncl = 0;
        $totalShipping = 0;
        $totalSumTaxIncl = 0;
        $totalPaid = 0;
        $totalTaxes = array(); // dynamic: id_tax => total

        foreach ($sbpmData['combined'] as $data) {
            $this->setCellValueSafe($sheet, 'A' . $row, $data['payment_method']);
            // Column B: Confirmed Orders (Module column removed)
            $this->setCellValueSafe($sheet, 'B' . $row, $data['order_count']);
            $this->setNumericValue($sheet, 'C' . $row, (float)$data['total_products_tax_excl']);
            $this->setNumericValue($sheet, 'D' . $row, (float)$data['total_products_tax_incl']);
            $this->setNumericValue($sheet, 'E' . $row, (float)$data['total_shipping_tax_incl']);
            $totalSum = (float)$data['total_products_tax_incl'] + (float)$data['total_shipping_tax_incl'];
            $this->setNumericValue($sheet, 'F' . $row, $totalSum);
            $this->setNumericValue($sheet, 'G' . $row, (float)$data['total_paid_tax_incl']);
            // Dynamic tax columns
            foreach ($taxColMap as $taxId => $colLetter) {
                $taxAmount = isset($data['taxes'][$taxId]) ? (float)$data['taxes'][$taxId] : 0;
                $this->setNumericValue($sheet, $colLetter . $row, $taxAmount);
                if (!isset($totalTaxes[$taxId])) {
                    $totalTaxes[$taxId] = 0;
                }
                $totalTaxes[$taxId] += $taxAmount;
            }
            // Removed: J refund_online, K refund_instore

            $totalOrders += (int)$data['order_count'];
            $totalProductsExcl += (float)$data['total_products_tax_excl'];
            $totalProductsIncl += (float)$data['total_products_tax_incl'];
            $totalShipping += (float)$data['total_shipping_tax_incl'];
            $totalSumTaxIncl += $totalSum;
            $totalPaid += (float)$data['total_paid_tax_incl'];
            $row++;
        }
        // TOTALS row
        $this->setCellValueSafe($sheet, 'A' . $row, 'TOTALS');
        $this->setCellValueSafe($sheet, 'B' . $row, $totalOrders);
        $this->setNumericValue($sheet, 'C' . $row, $totalProductsExcl);
        $this->setNumericValue($sheet, 'D' . $row, $totalProductsIncl);
        $this->setNumericValue($sheet, 'E' . $row, $totalShipping);
        $this->setNumericValue($sheet, 'F' . $row, $totalSumTaxIncl);
        $this->setNumericValue($sheet, 'G' . $row, $totalPaid);
        foreach ($taxColMap as $taxId => $colLetter) {
            $this->setNumericValue($sheet, $colLetter . $row, isset($totalTaxes[$taxId]) ? $totalTaxes[$taxId] : 0);
        }
        // Removed: J totalRefundOnline, K totalRefundInstore
        $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFont()->setBold(true);
        $row++;

        // Shipping Online row (between TOTALS and Refund Online)
        $this->setCellValueSafe($sheet, 'A' . $row, 'Shipping Online');
        $this->setNumericValue($sheet, 'E' . $row, (float)$sbpmData['online']['shipping_incl']);
        foreach ($taxColMap as $taxId => $colLetter) {
            $shippingTax = isset($sbpmData['online']['shipping_taxes'][$taxId]) ? (float)$sbpmData['online']['shipping_taxes'][$taxId] : 0;
            $this->setNumericValue($sheet, $colLetter . $row, $shippingTax);
        }
        $row++;

        // Refund Online breakdown row
        $this->setCellValueSafe($sheet, 'A' . $row, 'Refund Online');
        $this->setNumericValue($sheet, 'C' . $row, (float)$sbpmData['online']['refund_products_excl']);
        $this->setNumericValue($sheet, 'D' . $row, (float)$sbpmData['online']['refund_products_incl']);
        $this->setNumericValue($sheet, 'E' . $row, (float)$sbpmData['online']['refund_shipping_incl']);
        $refundOnlineSum = round((float)$sbpmData['online']['refund_products_incl'] + (float)$sbpmData['online']['refund_shipping_incl'], 2);
        $this->setNumericValue($sheet, 'F' . $row, $refundOnlineSum);
        $this->setNumericValue($sheet, 'G' . $row, $refundOnlineSum);
        foreach ($taxColMap as $taxId => $colLetter) {
            $refundTax = isset($sbpmData['online']['refund_taxes'][$taxId]) ? (float)$sbpmData['online']['refund_taxes'][$taxId] : 0;
            $this->setNumericValue($sheet, $colLetter . $row, $refundTax);
        }
        $row++;

        // Refund Instore breakdown row
        $this->setCellValueSafe($sheet, 'A' . $row, 'Refund Instore');
        $this->setNumericValue($sheet, 'C' . $row, (float)$sbpmData['instore']['refund_products_excl']);
        $this->setNumericValue($sheet, 'D' . $row, (float)$sbpmData['instore']['refund_products_incl']);
        $this->setNumericValue($sheet, 'E' . $row, (float)$sbpmData['instore']['refund_shipping_incl']);
        $refundInstoreSum = round((float)$sbpmData['instore']['refund_products_incl'] + (float)$sbpmData['instore']['refund_shipping_incl'], 2);
        $this->setNumericValue($sheet, 'F' . $row, $refundInstoreSum);
        $this->setNumericValue($sheet, 'G' . $row, $refundInstoreSum);
        foreach ($taxColMap as $taxId => $colLetter) {
            $refundTax = isset($sbpmData['instore']['refund_taxes'][$taxId]) ? (float)$sbpmData['instore']['refund_taxes'][$taxId] : 0;
            $this->setNumericValue($sheet, $colLetter . $row, $refundTax);
        }
        $row++;

        $topSectionEndRow = $row - 1; // Track where top section ends (includes refund rows)
        
        // ==================== BOTTOM PART: Specific Payment Breakdown ====================
        // Column headers for bottom section
        $bottomHeaders = array(
            'A' => 'Specific Payment',
            'B' => 'Module',
            'C' => 'Payment Amount'
        );
        

        foreach ($bottomHeaders as $column => $header) {
            $this->setCellValueSafe($sheet, $column . $row, $header);
        }
        $this->styleHeaderRow($sheet, 'A' . $row . ':C' . $row);
        $row++;
        
        // ==================== ONLINE SECTION ====================
        // Fixed Online rows (always show even if $0) - show rows first, then total
        // Using dedicated payment amount fields for accuracy
        
        // Link via Stripe
        $this->setCellValueSafe($sheet, 'A' . $row, 'Link via Stripe');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['stripe_link']);
        $row++;
        
        // PayPal
        $this->setCellValueSafe($sheet, 'A' . $row, 'PayPal');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['paypal']);
        $row++;
        
        // Card via Stripe
        $this->setCellValueSafe($sheet, 'A' . $row, 'Card via Stripe');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['stripe_card']);
        $row++;
        
        // Paid with Gift Card
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Gift Card');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['gift_card']);
        $row++;
        
        // Paid with Voucher
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Voucher');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['voucher']);
        $row++;
        
        // Paid with Credit Slip
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Credit Slip');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['credit_slip']);
        $row++;
        
        // Discount Online
        $this->setCellValueSafe($sheet, 'A' . $row, 'Discount Online');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['discount']);
        $row++;
        
        // Refund Online
        $this->setCellValueSafe($sheet, 'A' . $row, 'Refund Online');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['refund']);
        $row++;
        
        // TOTAL ONLINE - moved to bottom of online section
        $this->setCellValueSafe($sheet, 'A' . $row, 'TOTAL ONLINE');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['online']['total']);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('B8CCE4');
        $row++;
        
        // Empty row separator
        $row++;
        

        // ==================== IN-STORE SECTION ====================
        // Fixed In-Store rows (always show even if $0) - show rows first, then total
        // Paid with Voucher
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Voucher');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['voucher']);
        $row++;
        
        // Paid with Credit Card - using dedicated field for accuracy
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Credit Card');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['credit_card']);
        $row++;
        
        // Paid with Cash = total received in cash that day (gross; no deduction — refunds appear in Refund Instore and Cash in hand)
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Cash');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['cash']);
        $row++;
        
        // Paid with Interac - using dedicated field for accuracy
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Interac');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['interac']);
        $row++;
        
        // Paid with InStore Gift Card
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with InStore Gift Card');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['gift_card']);
        $row++;
        
        // Paid with Credit Slip
        $this->setCellValueSafe($sheet, 'A' . $row, 'Paid with Credit Slip');
        $this->setCellValueSafe($sheet, 'B' . $row, $posModuleName);
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['credit_slip']);
        $row++;
        
        // Refund Instore
        $this->setCellValueSafe($sheet, 'A' . $row, 'Refund Instore');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['refund']);
        $row++;
        
        // Discount InStore
        $this->setCellValueSafe($sheet, 'A' . $row, 'Discount InStore');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['discount']);
        $row++;
        
        // TOTAL IN-STORE - moved to bottom of in-store section
        $this->setCellValueSafe($sheet, 'A' . $row, 'TOTAL IN-STORE');
        $this->setNumericValue($sheet, 'C' . $row, $sbpmData['instore']['total']);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('B8CCE4');
        $row++;
        
        // Cash in hand (Cash − refunded in cash) — after TOTAL IN-STORE
        $this->setCellValueSafe($sheet, 'A' . $row, 'Cash in hand');
        $this->setNumericValue($sheet, 'C' . $row, isset($sbpmData['instore']['cash_in_hand']) ? $sbpmData['instore']['cash_in_hand'] : 0);
        $row++;
        
        // Style data rows and column widths
        // Find the last data row (before we added styling)
        $lastRow = $row - 1;
        
        // Style all data rows (top and bottom sections)
        if ($lastRow > 2) {
            // Style top section (rows 3 to end of refund rows)
            $this->styleDataRows($sheet, 'A3:' . $lastColLetter . $topSectionEndRow);
            // Apply number formatting: B=order count, C onwards=monetary
            $this->applyNumberFormat($sheet, 'B3:B' . $topSectionEndRow);
            $this->applyNumberFormat($sheet, 'C3:' . $lastColLetter . $topSectionEndRow);

            // Style bottom section (after top section + bottom header row)
            $bottomStartRow = $topSectionEndRow + 2;
            if ($bottomStartRow <= $lastRow) {
                $this->styleDataRows($sheet, 'A' . $bottomStartRow . ':C' . $lastRow);
                $this->applyNumberFormat($sheet, 'C' . $bottomStartRow . ':C' . $lastRow);
            }
        }

        // Dynamic column widths
        $colWidths = array('A' => 22, 'B' => 12, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 16, 'G' => 16);
        foreach ($taxColMap as $taxId => $colLetter) {
            $colWidths[$colLetter] = 14;
        }
        $this->setColumnWidths($sheet, $colWidths);
        
        // Apply print settings - landscape for better fit
        $this->applyPrintSettings($sheet, 'landscape');
    }
    
    /**
     * Helper to find payment amount by method name
     * Searches for exact match first, then partial match
     * Sums all matching entries to handle edge cases
     */
    protected function findPaymentAmount($payments, $methodName)
    {
        if (empty($payments)) {
            return 0;
        }
        
        $total = 0;
        $methodLower = strtolower(trim($methodName));
        
        foreach ($payments as $payment) {
            $paymentMethodLower = strtolower(trim($payment['payment_method']));
            
            // Exact match first
            if ($paymentMethodLower === $methodLower) {
                $total += (float)$payment['payment_amount'];
            }
            // Partial match (payment method contains the search term)
            elseif (strpos($paymentMethodLower, $methodLower) !== false) {
                $total += (float)$payment['payment_amount'];
            }
        }
        
        return $total;
    }

    
    
    /**
     * Populate Taxes sheet - Simple tax name and amount
     */
    protected function populateTaxesSheet($sheet, $dataFetcher, $date_from, $date_to)
    {
        // Add header row (width = top table columns A–B for print)
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Tax Summary', 'B');
        
        // Column headers (Row 2)
        $headers = array(
            'A' => 'Tax Name',
            'B' => 'Tax Amount'
        );
        
        foreach ($headers as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        
        // Style header row
        $this->styleHeaderRow($sheet, 'A2:B2');
        
        // Get tax data
        $taxData = $dataFetcher->getTaxSummary();
        
        // Populate data rows
        $row = 3;
        $grandTotal = 0;
        
        foreach ($taxData as $data) {
            $this->setCellValueSafe($sheet, 'A' . $row, $data['tax_name']);
            $this->setNumericValue($sheet, 'B' . $row, (float)$data['tax_amount']);
            
            $grandTotal += (float)$data['tax_amount'];
            $row++;
        }
        
        // Add totals row
        $row++;
        $this->setCellValueSafe($sheet, 'A' . $row, 'GRAND TOTAL');
        $this->setNumericValue($sheet, 'B' . $row, $grandTotal);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        
        // Style and column widths
        $this->styleDataRows($sheet, 'A3:B' . $row);
        $this->setColumnWidths($sheet, array(
            'A' => 25, 'B' => 18
        ));
        
        // Apply print settings - portrait is fine for narrow sheet
        $this->applyPrintSettings($sheet, 'portrait');
    }

    /**
     * Add header row with date range and export info.
     * Header width is fixed to match the top table columns so printing scales correctly:
     * the top table (widest section) takes full page width and the header matches it.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param string $date_from
     * @param string $date_to
     * @param string $title
     * @param string $topTableLastColumn Last column letter of the top table (e.g. 'K' for A–K). Header merges A1 to this column.
     */
    protected function addSheetHeader($sheet, $date_from, $date_to, $title = '', $topTableLastColumn = 'A')
    {
        $exportDate = date('Y-m-d H:i:s');
        $headerText = $date_from . ' 00:00:00 - ' . $date_to . ' 23:59:59';
        if ($title) {
            $headerText = $title . ' | ' . $headerText;
        }
        $headerText .= ' | Exported: ' . $exportDate;
        
        // Merge header to match top table width (fixes print layout: header and table same width, scale to full page)
        $headerRange = 'A1:' . $topTableLastColumn . '1';
        $sheet->mergeCells($headerRange);
        $sheet->setCellValue('A1', $headerText);
        
        // Style header
        $sheet->getStyle('A1')->applyFromArray(array(
            'font' => array(
                'bold' => true,
                'size' => 12,
                'color' => array('rgb' => '1F4E79')
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('rgb' => 'D6DCE5')
            )
        ));
        
        $sheet->getRowDimension(1)->setRowHeight(35);
    }

    /**
     * Style the header row (column headers)
     */
    protected function styleHeaderRow($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray(array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
                'size' => 10
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('rgb' => '2F5496')
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
                'indent' => 1
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
        
        // Extract row number from range (e.g., "A2:W2" -> 2, "A5:W5" -> 5)
        if (preg_match('/^[A-Z]+(\d+):/', $range, $matches)) {
            $rowNumber = (int)$matches[1];
            $sheet->getRowDimension($rowNumber)->setRowHeight(30);
        }
    }

    /**
     * Style data rows with padding and number formatting
     */
    protected function styleDataRows($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray(array(
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent' => 1
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => 'D0D0D0')
                )
            )
        ));
    }
    
    /**
     * Apply number formatting (up to 2 decimal places, no trailing zeros) to a range
     */
    protected function applyNumberFormat($sheet, $range)
    {
        $sheet->getStyle($range)->getNumberFormat()
            ->setFormatCode('#,##0.##');
    }
    
    /**
     * Apply padding and number formatting to a specific cell range
     */
    protected function styleCellRange($sheet, $range, $isNumeric = false)
    {
        $sheet->getStyle($range)->applyFromArray(array(
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent' => 1
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => 'D0D0D0')
                )
            )
        ));
        
        if ($isNumeric) {
            $sheet->getStyle($range)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }
    }

    /**
     * Set column widths
     */
    protected function setColumnWidths($sheet, $widths)
    {
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }
    
    /**
     * Apply print settings to fit sheet to one page width
     * This ensures the Excel prints properly without manual scaling
     */
    protected function applyPrintSettings($sheet, $orientation = 'landscape')
    {
        // Set fit to width - this is the key setting that scales content to fit page width
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0); // Don't limit height - allow multiple pages vertically
        
        // Set orientation based on content
        if ($orientation === 'landscape') {
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        } else {
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        }
        
        // Set paper size to A4
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        
        // Set narrow margins for better fit
        $sheet->getPageMargins()->setLeft(0.4);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        
        // Set print area to include all used cells
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $sheet->getPageSetup()->setPrintArea('A1:' . $highestColumn . $highestRow);
    }
}
