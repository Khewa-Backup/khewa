<?php
//require_once dirname( __FILE__ ) . '/../../classes/CrazyContent.php';



class AdminReportExportController extends ModuleAdminController{


	public function __construct()
    {
        $this->lang = true;
        $this->deleted = false;
        $this->bootstrap = true;
        $this->module = 'ordersexportsalesreportpro';


        parent::__construct();
    }


	public function display() {
		parent::display();
	}


	public function setMedia( $isNewTheme = false ) {
		parent::setMedia();
	}


    public function initContent(){

//        $AdminOrdersExportSalesReportProController = Context::getContext()->link->getAdminLink('AdminOrdersExportSalesReportPro', true).'&auto_export=true';
//        Tools::redirectAdmin($AdminOrdersExportSalesReportProController);

        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure=ordersexportsalesreportpro'.'&auto_export=true');
        parent::initContent();
    }





	public function initToolbar() {
		parent::initToolbar();
	}
}