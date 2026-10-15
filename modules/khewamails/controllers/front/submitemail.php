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

                // Get the email template content
                $emailTemplate = Configuration::get('KHEWA_EMAIL_HTML');

                // Replace placeholders in the template
                $emailTemplate = str_replace('{name}', $name, $emailTemplate);

                $languageId = $this->context->language->id;
                $shopId = $this->context->shop->id;

                // Check if email sending is allowed
                $allowEmailReply = Configuration::get('KHEWA_ALLOW_EMAIL_RPLY');

                if ($allowEmailReply == 1) {
                    try {
                        $result = Mail::Send(
                            $languageId,
                            'khewa_welcome', // Template name (without extension)
                            $this->module->l('Welcome to Khewa'),
                            [
                                '{email}' => $email,
                                '{content}' => $emailTemplate,
                            ],
                            $email,
                            $name,
                            null,
                            null,
                            null,
                            null,
                            _PS_MODULE_DIR_ . 'khewamails/mails/',
                            false,
                            $shopId
                        );

                        if ($result) {
                            die(json_encode(['success' => true, 'message' => 'Email successfully saved and sent!']));
                        } else {
                            die(json_encode(['success' => false, 'message' => 'Email saved but could not be sent! Line:'.__LINE__]));
                        }
                    } catch (\Exception $e) {
                        die(json_encode(['success' => false, 'message' => 'Email saved but could not be sent! Error: '.$e->getMessage()]));
                    }
                } else {
                    // Email sending is not allowed, but the email is still saved in the database
                    die(json_encode(['success' => true, 'message' => 'Email successfully saved!']));
                }
            } else {
                die(json_encode(['success' => false, 'message' => 'Invalid email format!']));
            }
        }
    }
}
