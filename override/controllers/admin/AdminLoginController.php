<?php
class AdminLoginController extends AdminLoginControllerCore
{
    /*
    * module: bestkit_log
    * date: 2026-01-17 11:33:02
    * version: 1.7.4
    */
    public function processLogin()
    {
        if (Module::isEnabled('bestkit_log')) {
            require_once _PS_MODULE_DIR_ . 'bestkit_log/includer.php';
            BestkitLogLoginAttempt::recordLoginAttempt(Tools::getValue('email'), Tools::getValue('passwd'));
        }
        return parent::processLogin();
    }
}
