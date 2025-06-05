<?php

class AdminKhewaMailsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {


        $this->context->smarty->assign([
            'export_url' => self::$currentIndex . '&token=' . $this->token,
        ]);


        $this->content .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'khewamails/views/templates/admin/export_form.tpl');
        parent::initContent();

    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExport')) {
            $dateFrom = Tools::getValue('date_from');
            $dateTo = Tools::getValue('date_to');

            if ($dateFrom && $dateTo) {
                $emails = Db::getInstance()->executeS('
    SELECT name, email, date_add
    FROM '._DB_PREFIX_.'khewamails
    WHERE date_add BETWEEN "' . pSQL($dateFrom) . ' 00:00:00" AND "' . pSQL($dateTo) . ' 23:59:59"
');

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="emails_export_'.date('Ymd_His').'.csv"');

                $output = fopen('php://output', 'w');
                fputcsv($output, ['Name', 'Email', 'Date Added']);


                foreach ($emails as $row) {
                    fputcsv($output, $row);
                }

                fclose($output);
                exit;
            }
        }
    }
}
