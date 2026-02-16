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
        // Add header row with date range info
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Sales Report');
        
        // Column headers (Row 2)
        $headers = array(
            'A' => 'Ordered At',
            'B' => 'Order ID',
            'C' => 'Invoice Number',
            'D' => 'Payment Breakdown',
            'E' => 'Gift Card Payment',
            'F' => 'Credit Slip',
            'G' => 'Voucher',
            'H' => 'Total Shipping (Tax incl)',
            'I' => 'Shipping Tax (CA 5%)',
            'J' => 'Shipping Tax (CA-QC 9.975%)',
            'K' => 'Total Refunded Products (Tax incl)',
            'L' => 'Refunded Amount',
            'M' => 'Total Refunds ROCK (Tax incl)',
            'N' => 'Total Products With Tax',
            'O' => 'Payment Method',
            'P' => 'Product Name',
            'Q' => 'Total Price (Tax incl)',
            'R' => 'Total Price (Tax excl)',
            'S' => 'Total Amount (CA 5%)',
            'T' => 'Total Amount (CA-QC 9.975%)',
            'U' => 'Total Shipping Price (Tax excl)',
            'V' => 'Delivery Country',
            'W' => 'Delivery State'
        );
        
        foreach ($headers as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        
        // Style header row
        $this->styleHeaderRow($sheet, 'A2:W2');
        
        // Get sales data
        $salesData = $dataFetcher->getSalesData();
        
        // Populate data rows (starting from row 3)
        // Initialize running totals - calculated from loop data to ensure consistency
        $row = 3;
        $lastOrderId = null;
        $processedOrders = array(); // Track which orders we've counted for order-level totals
        $totals = array(
            'gift_card' => 0,
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
            // Column D: Payment Breakdown - shows how order was paid (e.g., "Cash($50.00) - Credit Card($25.00)")
            $this->setCellValueSafe($sheet, 'D' . $row, $showOrderData ? $data['payment_breakdown'] : '');
            
            // Column E: Gift Card - order level, add only once per order
            if ($showOrderData && $data['gift_card_amount']) {
                $this->setNumericValue($sheet, 'E' . $row, $data['gift_card_amount']);
                $totals['gift_card'] += (float)$data['gift_card_amount'];
            } else {
                $this->setCellValueSafe($sheet, 'E' . $row, '');
            }
            
            $this->setCellValueSafe($sheet, 'F' . $row, '0');
            
            // Column G: Voucher - order level
            if ($showOrderData && $data['voucher_value']) {
                $this->setNumericValue($sheet, 'G' . $row, $data['voucher_value']);
                $totals['voucher'] += (float)$data['voucher_value'];
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
            
            // Column N: Total Products With Tax - show product level detail
            $this->setNumericValue($sheet, 'N' . $row, $data['total_price_tax_incl']);
            
            // For TOTALS, use order-level values (orders.total_products_wt) - only count each order once
            if ($showOrderData) {
                $totals['products_incl'] += (float)$data['total_products_tax_incl'];  // order-level (from orders.total_products_wt)
                $totals['products_excl'] += (float)$data['total_products_tax_excl'];  // order-level (from orders.total_products)
            }
            
            $this->setCellValueSafe($sheet, 'O' . $row, $showOrderData ? $data['payment'] : '');
            $this->setCellValueSafe($sheet, 'P' . $row, $data['product_name']);
            
            // Column Q: Total Price (Tax incl) - show product level detail
            $this->setNumericValue($sheet, 'Q' . $row, $data['total_price_tax_incl']);
            
            // Column R: Total Price (Tax excl) - show product level detail
            $this->setNumericValue($sheet, 'R' . $row, $data['total_price_tax_excl']);
            
            // Column S: Product GST - product level (sum all)
            $this->setNumericValue($sheet, 'S' . $row, $data['gst_total_amount']);
            $totals['product_gst'] += (float)$data['gst_total_amount'];
            
            // Column T: Product QST - product level (sum all)
            $this->setNumericValue($sheet, 'T' . $row, $data['qst_total_amount']);
            $totals['product_qst'] += (float)$data['qst_total_amount'];
            
            // Column U: Shipping (Tax excl) - order level
            if ($showOrderData && $data['total_shipping_tax_excl']) {
                $this->setNumericValue($sheet, 'U' . $row, $data['total_shipping_tax_excl']);
                $totals['shipping_excl'] += (float)$data['total_shipping_tax_excl'];
            } else {
                $this->setCellValueSafe($sheet, 'U' . $row, '');
            }
            
            $this->setCellValueSafe($sheet, 'V' . $row, $showOrderData ? $data['delivery_country'] : '');
            $this->setCellValueSafe($sheet, 'W' . $row, $showOrderData ? $data['delivery_state'] : '');
            
            $lastOrderId = $data['id_order'];
            $row++;
        }
        
        // Add totals row - using calculated sums from loop (not separate query)
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 3) {
            $row++;
            $this->setCellValueSafe($sheet, 'A' . $row, 'TOTALS');
            $this->setNumericValue($sheet, 'E' . $row, $totals['gift_card']);
            $this->setNumericValue($sheet, 'G' . $row, $totals['voucher']);
            $this->setNumericValue($sheet, 'H' . $row, $totals['shipping_incl']);
            $this->setNumericValue($sheet, 'I' . $row, $totals['shipping_gst']);
            $this->setNumericValue($sheet, 'J' . $row, $totals['shipping_qst']);
            $this->setNumericValue($sheet, 'K' . $row, $totals['refunded_products']);
            $this->setNumericValue($sheet, 'L' . $row, $totals['refund_amount']);
            $this->setNumericValue($sheet, 'M' . $row, $totals['refund_amount']);
            $this->setNumericValue($sheet, 'N' . $row, $totals['products_incl']);
            $this->setNumericValue($sheet, 'Q' . $row, $totals['products_incl']);
            $this->setNumericValue($sheet, 'R' . $row, $totals['products_excl']);
            $this->setNumericValue($sheet, 'S' . $row, $totals['product_gst']);
            $this->setNumericValue($sheet, 'T' . $row, $totals['product_qst']);
            $this->setNumericValue($sheet, 'U' . $row, $totals['shipping_excl']);
            $sheet->getStyle('A' . $row . ':W' . $row)->getFont()->setBold(true);
            
            // Add column headers again after totals row for reference when scrolling
            $row++;
            foreach ($headers as $column => $header) {
                $this->setCellValueSafe($sheet, $column . $row, $header);
            }
            // Style header row (same as top header) - row height is set automatically by styleHeaderRow
            $this->styleHeaderRow($sheet, 'A' . $row . ':W' . $row);
        }
        
        // Style data rows and set column widths
        $this->styleDataRows($sheet, 'A3:W' . $row);
        $this->setColumnWidths($sheet, array(
            'A' => 12, 'B' => 10, 'C' => 14, 'D' => 35, 'E' => 12, 'F' => 10, 'G' => 10,
            'H' => 16, 'I' => 14, 'J' => 16, 'K' => 18, 'L' => 14, 'M' => 18, 'N' => 16,
            'O' => 14, 'P' => 30, 'Q' => 14, 'R' => 14, 'S' => 14, 'T' => 16, 'U' => 16,
            'V' => 14, 'W' => 14
        ));
        
        // Apply number formatting to numeric columns (2 decimal places)
        if ($row > 3) {
            $this->applyNumberFormat($sheet, 'E3:E' . $row); // Gift Card
            $this->applyNumberFormat($sheet, 'G3:G' . $row); // Voucher
            $this->applyNumberFormat($sheet, 'H3:J' . $row); // Shipping amounts
            $this->applyNumberFormat($sheet, 'K3:M' . $row); // Refund amounts
            $this->applyNumberFormat($sheet, 'N3:N' . $row); // Product price tax incl
            $this->applyNumberFormat($sheet, 'Q3:T' . $row); // Product prices and taxes
            $this->applyNumberFormat($sheet, 'U3:U' . $row); // Shipping tax excl
        }
        
        // Set auto filter
        $sheet->setAutoFilter('A2:W' . $row);
        
        // Apply print settings - landscape for wide sheet with many columns
        $this->applyPrintSettings($sheet, 'landscape');
    }

    /**
     * Populate Refunds sheet with real data
     */
    protected function populateRefundsSheet($sheet, $dataFetcher, $date_from, $date_to)
    {
        // Add header row with date range info
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Refunds Report (by Refund Date)');
        
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
        // Add header row with date range info
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Sales By Payment Method');
        
        // Get POS module name from configuration (use first one for display)
        $patterns = Khewareports::getPaymentMethodPatterns();
        $posModuleName = !empty($patterns['pos_module']) ? $patterns['pos_module'][0] : 'hspointofsalepro';
        
        // ==================== TOP PART: Summary Table ====================
        // Column headers (Row 2)
        $topHeaders = array(
            'A' => 'Combined Payment',
            'B' => 'Module',
            'C' => 'Confirmed Orders',
            'D' => 'Total Products (Tax Excl.)',
            'E' => 'Total Products (Tax Incl.)',
            'F' => 'Total Shipping (Tax Incl.)',
            'G' => 'Total Paid (Tax Incl.)',
            'H' => 'Total Tax (CA 5%)',
            'I' => 'Total Tax (CA-QC 9.975%)',
            'J' => 'Refund Online (Tax Incl.)',
            'K' => 'Refund Instore (Tax Incl.)'
        );
        
        foreach ($topHeaders as $column => $header) {
            $this->setCellValueSafe($sheet, $column . '2', $header);
        }
        $this->styleHeaderRow($sheet, 'A2:K2');
        $sheet->getRowDimension(2)->setRowHeight(30);
        
        // Get SBPM data
        $sbpmData = $dataFetcher->getSBPMData();
        
        // Populate top summary rows (combined by payment method)
        $row = 3;
        $totalOrders = 0;
        $totalProductsExcl = 0;
        $totalProductsIncl = 0;
        $totalShipping = 0;
        $totalPaid = 0;
        $totalGST = 0;
        $totalQST = 0;
        $totalRefundOnline = 0;
        $totalRefundInstore = 0;
        
        foreach ($sbpmData['combined'] as $data) {
            $this->setCellValueSafe($sheet, 'A' . $row, $data['payment_method']);
            $this->setCellValueSafe($sheet, 'B' . $row, $data['module']);
            $this->setCellValueSafe($sheet, 'C' . $row, $data['order_count']);
            $this->setNumericValue($sheet, 'D' . $row, (float)$data['total_products_tax_excl']);
            $this->setNumericValue($sheet, 'E' . $row, (float)$data['total_products_tax_incl']);
            $this->setNumericValue($sheet, 'F' . $row, (float)$data['total_shipping_tax_incl']);
            $this->setNumericValue($sheet, 'G' . $row, (float)$data['total_paid_tax_incl']);
            $this->setNumericValue($sheet, 'H' . $row, (float)$data['total_gst']);
            $this->setNumericValue($sheet, 'I' . $row, (float)$data['total_qst']);
            $this->setNumericValue($sheet, 'J' . $row, (float)$data['refund_online']);
            $this->setNumericValue($sheet, 'K' . $row, (float)$data['refund_instore']);
            
            $totalOrders += (int)$data['order_count'];
            $totalProductsExcl += (float)$data['total_products_tax_excl'];
            $totalProductsIncl += (float)$data['total_products_tax_incl'];
            $totalShipping += (float)$data['total_shipping_tax_incl'];
            $totalPaid += (float)$data['total_paid_tax_incl'];
            $totalGST += (float)$data['total_gst'];
            $totalQST += (float)$data['total_qst'];
            $totalRefundOnline += (float)$data['refund_online'];
            $totalRefundInstore += (float)$data['refund_instore'];
            $row++;
        }
        
        // TOTALS row
        $this->setCellValueSafe($sheet, 'A' . $row, 'TOTALS');
        $this->setCellValueSafe($sheet, 'C' . $row, $totalOrders);
        $this->setNumericValue($sheet, 'D' . $row, $totalProductsExcl);
        $this->setNumericValue($sheet, 'E' . $row, $totalProductsIncl);
        $this->setNumericValue($sheet, 'F' . $row, $totalShipping);
        $this->setNumericValue($sheet, 'G' . $row, $totalPaid);
        $this->setNumericValue($sheet, 'H' . $row, $totalGST);
        $this->setNumericValue($sheet, 'I' . $row, $totalQST);
        $this->setNumericValue($sheet, 'J' . $row, $totalRefundOnline);
        $this->setNumericValue($sheet, 'K' . $row, $totalRefundInstore);
        $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
        $topSectionEndRow = $row; // Track where top section ends
        $row++;
        
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
        
        // Paid with Cash - using dedicated field for accuracy
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
        
        // Style data rows and column widths
        // Find the last data row (before we added styling)
        $lastRow = $row - 1;
        
        // Style all data rows (top and bottom sections)
        if ($lastRow > 2) {
            // Style top section (rows 3 to TOTALS row)
            $this->styleDataRows($sheet, 'A3:K' . $topSectionEndRow);
            // Apply number formatting to numeric columns in top section
            $this->applyNumberFormat($sheet, 'C3:C' . $topSectionEndRow); // Order count
            $this->applyNumberFormat($sheet, 'D3:K' . $topSectionEndRow); // All monetary values
            
            // Style bottom section (after TOTALS row + bottom header row)
            $bottomStartRow = $topSectionEndRow + 2; // After TOTALS row + bottom header row
            if ($bottomStartRow <= $lastRow) {
                $this->styleDataRows($sheet, 'A' . $bottomStartRow . ':C' . $lastRow);
                // Apply number formatting to payment amount column
                $this->applyNumberFormat($sheet, 'C' . $bottomStartRow . ':C' . $lastRow);
            }
        }
        
        // Reduce column widths to fit on one page when printing
        $this->setColumnWidths($sheet, array(
            'A' => 20, 'B' => 15, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15,
            'G' => 15, 'H' => 14, 'I' => 15, 'J' => 15, 'K' => 15
        ));
        
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
        // Add header row with date range info
        $this->addSheetHeader($sheet, $date_from, $date_to, 'Tax Summary');
        
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
     * Add header row with date range and export info
     */
    protected function addSheetHeader($sheet, $date_from, $date_to, $title = '')
    {
        $exportDate = date('Y-m-d H:i:s');
        $headerText = $date_from . ' 00:00:00 - ' . $date_to . ' 23:59:59';
        if ($title) {
            $headerText = $title . ' | ' . $headerText;
        }
        $headerText .= ' | Exported: ' . $exportDate;
        
        // Merge cells for header (span across many columns)
        $sheet->mergeCells('A1:W1');
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
