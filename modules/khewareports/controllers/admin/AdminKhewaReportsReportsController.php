<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    Khewa
 *  @copyright 2024 Khewa
 *  @license   Commercial License
 */

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

    protected function generateExcelExport($date_from, $date_to)
    {
        // Use PhpSpreadsheet from the ordersexportsalesreportpro module
        $phpspreadsheet_path = _PS_MODULE_DIR_ . 'ordersexportsalesreportpro/vendor/autoload.php';
        
        if (!file_exists($phpspreadsheet_path)) {
            $this->errors[] = $this->module->l('PhpSpreadsheet library not found.');
            return;
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
            $this->populateSheetWithDummyData($sheet);
        }
        
        // Set first sheet (Sales) as active
        $spreadsheet->setActiveSheetIndex(0);
        
        // Output file
        $filename = 'khewa_reports_' . date('Y-m-d_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Populate a sheet with dummy data
     */
    protected function populateSheetWithDummyData($sheet)
    {
        // Create dummy data - Set headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Date');
        $sheet->setCellValue('D1', 'Amount');
        $sheet->setCellValue('E1', 'Status');
        
        // Add dummy rows
        $row = 2;
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
        
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        
        // Style data rows
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
        
        $sheet->getStyle('A2:E' . $lastRow)->applyFromArray($dataStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        
        // Set row heights
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        // Set auto filter
        $sheet->setAutoFilter('A1:E' . $lastRow);
    }
}

