<?php
//require_once dirname( __FILE__ ) . '/../../classes/CrazyContent.php';



class AdminReportExportConfigController extends ModuleAdminController{


	public function __construct()
    {
        
        $this->bootstrap = true;
        $this->module = 'ordersexportsalesreportpro';


        parent::__construct();
    }


    public function initContent()
    {


        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules', true, [], [
            'configure' => 'ordersexportsalesreportpro',
        ]));
        parent::initContent();
    }





	public function initToolbar() {
		parent::initToolbar();
	}
}