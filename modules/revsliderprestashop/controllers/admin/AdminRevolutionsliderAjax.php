<?php


if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'revsliderprestashop/rev-loader.php';
class AdminRevolutionsliderAjaxController extends ModuleAdminController
{

    protected $_ajax_results;

    protected $_ajax_stripslash;

    protected $_filter_whitespace;

    protected $lushslider_model;

    public function __construct()
    {
        $this->display_header = false;
        $this->display_footer = false;
        $this->content_only   = true;
        parent::__construct();
        $this->_ajax_results['error_on'] = 1; 
    }
    /**
     * Khewa PS 9.2: SECURITY. init() used to call initProcess(), which runs the whole slider ajax /
     * file manager. On 9.2 init() runs while the route is matched, before the login check (and this
     * init() never called the parent login check on 1.7 either), so anyone could run it. The work now
     * runs from postProcess(), which 9.2 calls only after the login and permission checks.
     */
    public function init()
    {
        parent::init(); // Khewa PS 9.2: sets up the controller (initProcess() below does nothing now)
    }

    public function initProcess()
    {
        // Khewa PS 9.2: nothing here, see postProcess()
    }

    public function postProcess()
    {
        $this->khewaProcess(); // Khewa PS 9.2
    }
    /**
     * 
     * @throws Exception
     */
    protected function khewaProcess() // Khewa PS 9.2: was initProcess()
    {
        ob_start();
        $RevSliderAdmin = new RevSliderAdmin();
        $RevSliderAdmin->do_ajax_action();
        $output = ob_get_contents();
        ob_end_clean();
        //die($output);
        die($output); 
    } 
}
