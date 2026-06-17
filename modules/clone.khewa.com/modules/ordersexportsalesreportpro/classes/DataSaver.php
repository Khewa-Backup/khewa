<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class DataSaver
{

    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function saveConfig()
    {
        $config = pSQL(Tools::getValue('config'));
        $datatables = Tools::getValue('datatables');
//        print_r($datatables);
//        die;
        $name = pSQL(Tools::getValue('name'));
        if (!$name) {
            die(Tools::jsonEncode(array('type' => 'danger', 'message' => $this->module->l('Enter a name.', 'DataSaver'))));
        }
//        $config = pSQL(urldecode($config));
//        $config = pSQL($config);
        $sql = 'SELECT
                        `id_orders_export_srpro` 
                    FROM `' . _DB_PREFIX_ . "orders_export_srpro` 
                    WHERE `name` = '" . $name . "';";
        $result = DB::getInstance()->executeS($sql);
        if (!empty($result)) {
            die(Tools::jsonEncode(array('type' => 'danger', 'message' => $this->module->l('This setting already exists.', 'DataSaver'))));
        } else {
            if (DB::getInstance()->insert('orders_export_srpro', array('name' => $name, 'configuration' => $config, 'datatables' => str_replace('&quuot;', '\\\"', $datatables)))) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('Configuration was saved.', 'DataSaver'),
                        'configs' => $this->module->getConfig(),
                )));
            } else {
                die(Tools::jsonEncode(array(
                        'type' => 'danger',
                        'message' => $this->module->l('Configuration couldn\'t be saved.', 'DataSaver')
                )));
            }
        }
    }

    public function deleteConfig()
    {
        $id = (int) Tools::getValue('id');
        DB::getInstance()->update('oxsrp_aexp_email', array('email_setting' => 1), 'email_setting = ' . $id);
        DB::getInstance()->update('oxsrp_aexp_ftp', array('ftp_setting' => 1), 'ftp_setting = ' . $id);
        DB::getInstance()->update('oxsrp_schdl_email', array('email_setting' => 1), 'email_setting = ' . $id);
        DB::getInstance()->update('oxsrp_schdl_ftp', array('ftp_setting' => 1), 'ftp_setting = ' . $id);
        if (DB::getInstance()->delete('orders_export_srpro', 'id_orders_export_srpro = ' . $id)) {
            die(Tools::jsonEncode(array(
                    'type' => 'success',
                    'message' => $this->module->l('Configuration was deleted.', 'DataSaver'),
                    'configs' => $this->module->getConfig(),
            )));
        } else {
            die(Tools::jsonEncode(array(
                    'type' => 'danger',
                    'message' => $this->module->l('Configuration couldn\'t be deleted.', 'DataSaver'),
            )));
        }
    }

    public function saveAutoexport()
    {
        $enable_autoexport = (int) Tools::getValue('enable_autoexport');
        $on_what = Tools::getValue('on_what');
        $enable_autoexport_email = (int) Tools::getValue('enable_autoexport_email');
        $enable_autoexport_ftp = (int) Tools::getValue('enable_autoexport_ftp');

        Configuration::updateValue('OXSRP_AEXP_ENABLE', $enable_autoexport);
        Configuration::updateValue('OXSRP_AEXP_ON_WHAT', implode(',', $on_what));
        Configuration::updateValue('OXSRP_AEXP_USE_EMAIL', $enable_autoexport_email);
        Configuration::updateValue('OXSRP_AEXP_USE_FTP', $enable_autoexport_ftp);

        die(Tools::jsonEncode(array(
                'type' => 'success',
                'message' => $this->module->l('AutoExport settings were successfully saved.', 'DataSaver'),
        )));
    }

    public function saveTakeLong()
    {
        $seconds = (float) Tools::getValue('seconds');
        $to_emails = pSQL(Tools::getValue('to_emails'));
        $ftp_url = pSQL(Tools::getValue('ftp_url'));
        $ftp_username = pSQL(Tools::getValue('ftp_username'));
        $ftp_password = pSQL(Tools::getValue('ftp_password'));
        $ftp_folder = pSQL(Tools::getValue('ftp_folder'));

        if (!Validate::isFloat($seconds)) {
            die(Tools::jsonEncode(array(
                    'type' => 'danger',
                    'message' => $this->module->l('Invalid numerical value.', 'DataSaver'),
            )));
        }

        Configuration::updateValue('OXSRP_TL_SECONDS', $seconds);
        Configuration::updateValue('OXSRP_TL_EMAILS', $to_emails);
        Configuration::updateValue('OXSRP_TL_FTP_URL', $ftp_url);
        Configuration::updateValue('OXSRP_TL_FTP_USRNM', $ftp_username);
        Configuration::updateValue('OXSRP_TL_FTP_PSWD', $ftp_password);
        Configuration::updateValue('OXSRP_TL_FTP_FLDR', $ftp_folder);

        die(Tools::jsonEncode(array(
                'type' => 'success',
                'message' => $this->module->l('The settings were successfully saved.', 'DataSaver'),
        )));
    }

    private function getCurrencySymbolByIso($iso_code)
    {
        $curs = json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../assets/currencies.json'));
        if (isset($curs->{$iso_code})) {
            return $curs->{$iso_code};
        } else {
            return $iso_code;
        }
    }

    public function updateCurrencySymbols()
    {
        $currency_def_iso = DB::getInstance()->getValue('SELECT iso_code FROM `'
            . _DB_PREFIX_ . 'currency` WHERE id_currency = ' . Configuration::get('PS_CURRENCY_DEFAULT'));

        $currency_symbol = $this->getCurrencySymbolByIso($currency_def_iso);
        Configuration::updateValue('OXSRP_DEF_CURR_SMBL', $currency_symbol);

        die(Tools::jsonEncode(array('status' => 'ok')));
    }

    public function saveAutoexportFTP()
    {
        $ftp_id = (int) Tools::getValue('ftp_id');
        $data = Tools::getValue('data');
        if (!is_numeric($data['ftp_port'])) {
            $data['ftp_port'] = '';
        }
        if ($ftp_id) {
            if (DB::getInstance()->update('oxsrp_aexp_ftp', $data, 'id_oxsrp_aexp_ftp = ' . $ftp_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'DataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('oxsrp_aexp_ftp', $data)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'DataSaver')
                )));
            }
        }
    }

    public function deleteAutoexportFTP()
    {
        $ftp_id = (int) Tools::getValue('ftp_id');
        if ($ftp_id) {
            if (DB::getInstance()->delete('oxsrp_aexp_ftp', 'id_oxsrp_aexp_ftp = ' . $ftp_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'DataSaver')
                )));
            }
        } else {
            die(Tools::jsonEncode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'DataSaver')
            )));
        }
    }

    public function saveAutoexportEmail()
    {
        $email_id = (int) Tools::getValue('email_id');
        $data = Tools::getValue('data');
        if ($email_id) {
            if (DB::getInstance()->update('oxsrp_aexp_email', $data, 'id_oxsrp_aexp_email = ' . $email_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'DataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('oxsrp_aexp_email', $data)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'DataSaver')
                )));
            }
        }
    }

    public function deleteAutoexportEmail()
    {
        $email_id = (int) Tools::getValue('email_id');
        if ($email_id) {
            if (DB::getInstance()->delete('oxsrp_aexp_email', 'id_oxsrp_aexp_email = ' . $email_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'DataSaver')
                )));
            }
        } else {
            die(Tools::jsonEncode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'DataSaver')
            )));
        }
    }

    public function saveScheduleFTP()
    {
        $ftp_id = (int) Tools::getValue('ftp_id');
        $data = Tools::getValue('data');
        if (!is_numeric($data['ftp_port'])) {
            $data['ftp_port'] = '';
        }
        if ($ftp_id) {
            if (DB::getInstance()->update('oxsrp_schdl_ftp', $data, 'id_oxsrp_schdl_ftp = ' . $ftp_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'DataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('oxsrp_schdl_ftp', $data)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'DataSaver')
                )));
            }
        }
    }

    public function deleteScheduleFTP()
    {
        $ftp_id = (int) Tools::getValue('ftp_id');
        if ($ftp_id) {
            if (DB::getInstance()->delete('oxsrp_schdl_ftp', 'id_oxsrp_schdl_ftp = ' . $ftp_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'DataSaver')
                )));
            }
        } else {
            die(Tools::jsonEncode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'DataSaver')
            )));
        }
    }

    public function saveScheduleEmail()
    {
        $email_id = (int) Tools::getValue('email_id');
        $data = Tools::getValue('data');
        if ($email_id) {
            if (DB::getInstance()->update('oxsrp_schdl_email', $data, 'id_oxsrp_schdl_email = ' . $email_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully updated.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be updated.', 'DataSaver')
                )));
            }
        } else {
            if (DB::getInstance()->insert('oxsrp_schdl_email', $data)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully added.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be added.', 'DataSaver')
                )));
            }
        }
    }

    public function deleteScheduleEmail()
    {
        $email_id = (int) Tools::getValue('email_id');
        if ($email_id) {
            if (DB::getInstance()->delete('oxsrp_schdl_email', 'id_oxsrp_schdl_email = ' . $email_id)) {
                die(Tools::jsonEncode(array(
                        'type' => 'success',
                        'message' => $this->module->l('The data was successfully deleted.', 'DataSaver')
                )));
            } else {
                die(Tools::jsonEncode(array('type' => 'error',
                        'message' => $this->module->l('The data couldn\'t be deleted.', 'DataSaver')
                )));
            }
        } else {
            die(Tools::jsonEncode(array('type' => 'error',
                    'message' => $this->module->l('Invalid arguments.', 'DataSaver')
            )));
        }
    }

    public function updateAutoexportSchedule()
    {
        $key = pSQL(Tools::getValue('key'));
        $value = pSQL(Tools::getValue('value'));

        if (Configuration::updateValue($key, $value)) {
            die(Tools::jsonEncode(array(
                    'type' => 'success',
                    'message' => $this->module->l('Successfully updated.', 'DataSaver'),
            )));
        } else {
            die(Tools::jsonEncode(array(
                    'type' => 'error',
                    'message' => $this->module->l('Couldn\'t update.', 'DataSaver'),
            )));
        }
    }
}
