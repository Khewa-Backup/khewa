<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    Khewa
 *  @copyright 2024 Khewa
 *  @license   Commercial License
 */

class AdminKhewaReportsQuickActionController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    
    
    public function init()
    {
        parent::init();
        
        // When this tab is accessed, immediately trigger export
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        $this->processQuickExport();
        // This should never be reached if export is successful
        die();
    }

    public function initContent()
    {
        // This should never be reached since init() will trigger export and die()
        parent::initContent();
    }

    protected function processQuickExport()
    {
        // Get the saved period setting
        $period = Configuration::get('KHEWA_QUICK_EXPORT_PERIOD', 'daily');
        
        // Calculate date range based on period
        $dateRange = $this->calculateDateRange($period);
        
        if (!$dateRange) {
            // If error, redirect back with error message
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminKhewaReportsQuickAction') . '&error=invalid_period');
            die();
        }
        
        // Generate Excel file - this will die on success
        $this->generateExcelExport($dateRange['from'], $dateRange['to']);
        
        // Should never reach here, but just in case
        die();
    }

    protected function calculateDateRange($period)
    {
        $today = date('Y-m-d');
        
        switch ($period) {
            case 'daily':
                // Today only (or last 1 day)
                return array(
                    'from' => $today,
                    'to' => $today
                );
                
            case 'weekly':
                // Last 7 days
                return array(
                    'from' => date('Y-m-d', strtotime('-7 days')),
                    'to' => $today
                );
                
            case 'monthly':
                // Last 30 days
                return array(
                    'from' => date('Y-m-d', strtotime('-30 days')),
                    'to' => $today
                );
                
            default:
                return false;
        }
    }

    protected function generateExcelExport($date_from, $date_to)
    {
        // Use PhpSpreadsheet from the ordersexportsalesreportpro module
        $phpspreadsheet_path = _PS_MODULE_DIR_ . 'ordersexportsalesreportpro/vendor/autoload.php';
        
        if (!file_exists($phpspreadsheet_path)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminKhewaReportsQuickAction') . '&error=library_not_found');
            die();
        }
        
        require_once $phpspreadsheet_path;
        
        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Khewa Reports')
            ->setLastModifiedBy('Khewa Reports')
            ->setTitle('Khewa Reports Export')
            ->setSubject('Reports Export')
            ->setDescription('Generated report from Khewa Reports module')
            ->setKeywords('khewa reports export');
        
        // Define tab names
        $tabs = array('Sales', 'Refunds', 'SBPM', 'Taxes');
        
        // Create each tab with dummy data
        foreach ($tabs as $index => $tabName) {
            if ($index == 0) {
                // Use the first sheet (already exists)
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($tabName);
            } else {
                // Create new sheet
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($tabName);
            }
            
            // Populate sheet with dummy data
            $this->populateSheetWithDummyData($sheet, $date_from, $date_to);
        }
        
        // Set first sheet (Sales) as active
        $spreadsheet->setActiveSheetIndex(0);
        
        // Output file
        $filename = 'khewa_reports_' . date('Y-m-d_His') . '.xlsx';
        
        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        die();
    }

    /**
     * Populate a sheet with dummy data
     */
    protected function populateSheetWithDummyData($sheet, $date_from = '', $date_to = '')
    {
        // Add header row with date range and export date
        $exportDate = date('Y-m-d H:i:s');
        $dateRangeText = '';
        if ($date_from && $date_to) {
            $dateRangeText = 'Date Range: ' . $date_from . ' to ' . $date_to;
        }
        $exportDateText = 'Exported on: ' . $exportDate;
        
        // Merge cells for header row - use more columns for better spacing
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', '    ' . $dateRangeText . ' | ' . $exportDateText . '    ');
        $headerFont = $sheet->getStyle('A1')->getFont();
        $headerFont->setBold(true);
        $headerFont->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setIndent(2);
        $sheet->getRowDimension(1)->setRowHeight(40);
        
        // Set column widths for the merged columns to ensure proper spacing
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        
        // Create dummy data - Set column headers (now in row 2)
        $sheet->setCellValue('A2', 'ID');
        $sheet->setCellValue('B2', 'Name');
        $sheet->setCellValue('C2', 'Date');
        $sheet->setCellValue('D2', 'Amount');
        $sheet->setCellValue('E2', 'Status');
        
        // Add dummy rows (starting from row 3)
        $row = 3;
        for ($i = 1; $i <= 10; $i++) {
            $sheet->setCellValue('A' . $row, (string)$i);
            $sheet->setCellValue('B' . $row, 'Item ' . $i);
            $sheet->setCellValue('C' . $row, date('Y-m-d', strtotime('-' . (10 - $i) . ' days')));
            $sheet->setCellValue('D' . $row, number_format(rand(100, 10000) / 100, 2));
            $sheet->setCellValue('E' . $row, ($i % 2 == 0) ? 'Active' : 'Inactive');
            $row++;
        }
        
        $lastRow = $row - 1;
        
        // Style the header row
        $headerStyle = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('rgb' => '4472C4'),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        
        // Style the column header row (row 2)
        $sheet->getStyle('A2:E2')->applyFromArray($headerStyle);
        
        // Style data rows (starting from row 3)
        $dataStyle = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            ),
        );
        
        $sheet->getStyle('A3:E' . $lastRow)->applyFromArray($dataStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        
        // Set row heights
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(25);
        
        // Set auto filter (starting from row 2 where headers are)
        $sheet->setAutoFilter('A2:E' . $lastRow);
    }
}

