<?php
class KhewamailsSubmitEmailModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $logoUrl = _PS_BASE_URL_ . '/img/' . Configuration::get('PS_LOGO');

        $this->context->smarty->assign([
            'welcome_message' => Configuration::get('KHEWA_WELCOME'),
            'description_text' => Configuration::get('KHEWA_DESCRIPTION'),
            'logo_url' => $logoUrl,
        ]);

        $this->setTemplate('module:khewamails/views/templates/front/submit_email.tpl');
    }


    public function postProcess()
    {
        if (Tools::isSubmit('ajax') && Tools::getValue('action') == 'submitEmail') {
            $name = Tools::getValue('name');
            $email = Tools::getValue('email');

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Db::getInstance()->insert('khewamails', [
                    'name' => pSQL($name),
                    'email' => pSQL($email),
                    'date_add' => date('Y-m-d H:i:s'),
                ]);
                die(json_encode(['success' => true, 'message' => 'Email successfully saved!']));
            } else {
                die(json_encode(['success' => false, 'message' => 'Invalid email format!']));
            }
        }
    }
}
