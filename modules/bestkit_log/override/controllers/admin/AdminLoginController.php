<?php

class AdminLoginController extends AdminLoginControllerCore
{
    public function processLogin()
    {
        if (Module::isEnabled('bestkit_log')) {
            require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';

            BestkitLogLoginAttempt::recordLoginAttempt(Tools::getValue('email'), Tools::getValue('passwd'));
        }

        return parent::processLogin();
    }
}
