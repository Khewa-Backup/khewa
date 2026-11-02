<?php
/**
 * Khewa Reports - Quick Export Controller
 * Just calculates date range from settings and calls Reports controller's export
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminKhewaReportsQuickActionController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * Khewa PS 9.2: SECURITY. This used to run in init(), which on 9.2 runs before the login check,
     * so anyone could download the sales report. postProcess() runs only after the login and
     * permission checks.
     */
    public function postProcess()
    {
        // When this tab is accessed, immediately trigger export
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->processQuickExport();
        die();
    }

    public function initContent()
    {
        // This should never be reached since init() will trigger export and die()
        parent::initContent();
    }

    /**
     * Get period from settings, calculate date range, and call Reports controller export
     */
    protected function processQuickExport()
    {
        // Get the saved period setting
        $period = Configuration::get('KHEWA_QUICK_EXPORT_PERIOD', 'daily');
        
        // Calculate date range based on period
        $dateRange = $this->calculateDateRange($period);
        
        if (!$dateRange) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminKhewaReportsReports') . '&error=invalid_period');
            die();
        }
        
        // Use Reports controller's export method - it has all the logic
        require_once _PS_MODULE_DIR_ . 'khewareports/controllers/admin/AdminKhewaReportsReportsController.php';
        $reportsController = new AdminKhewaReportsReportsController();
        $reportsController->generateExcelExport($dateRange['from'], $dateRange['to']);
        
        // Should never reach here, but just in case
        die();
    }

    /**
     * Calculate date range from period setting
     */
    protected function calculateDateRange($period)
    {
        $today = date('Y-m-d');
        
        switch ($period) {
            case 'daily':
                return array('from' => $today, 'to' => $today);
            case 'weekly':
                return array('from' => date('Y-m-d', strtotime('-7 days')), 'to' => $today);
            case 'monthly':
                return array('from' => date('Y-m-d', strtotime('-30 days')), 'to' => $today);
            default:
                return false;
        }
    }
}
