<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class PosModuleFrontController extends ModuleFrontController
{
    /**
     * Actions allowed without authenticated employee session.
     *
     * @var array
     */
    protected $public_actions = array(
        'initApp',
        'login',
        'forgotPasswd',
    );

    /**
     * Legacy static actions kept for backward compatibility.
     *
     * @var array
     */
    protected $static_actions = array(
        'getAllInOne',
        'login',
        'forgotPasswd',
    );

    /**
     * Check if the current request has a valid, active authenticated employee session.
     *
     * @return bool
     */
    public function isEmployeeAuthenticated()
    {
        $pos_id_employee = (int) Context::getContext()->cookie->pos_id_employee;
        if ($pos_id_employee <= 0) {
            return false;
        }

        if (!Validate::isLoadedObject($this->context->employee) || (int) $this->context->employee->id !== $pos_id_employee || !$this->context->employee->active) {
            $employee = new PosEmployee($pos_id_employee);
            if (Validate::isLoadedObject($employee) && $employee->active) {
                $this->context->employee = $employee;
            } else {
                return false;
            }
        }

        return $this->module ? $this->module->isLoggedSell($this->context->employee) : true;
    }

    /**
     * Enforce employee authentication. Terminate if not authenticated.
     */
    protected function _requirePosEmployeeAuth()
    {
        if (!$this->isEmployeeAuthenticated()) {
            $this->ajax_json['success'] = false;
            $this->ajax_json['message'] = $this->module->i18n['oops_your_session_just_expired'];
            $this->ajax_json['data']['timeout'] = true;
            die(json_encode($this->ajax_json));
        }
    }

    /**
     *
     * @see parent::postProcess();
     */
    public function postProcess()
    {
        $action = Tools::getValue('action');
        if ((int) Tools::getValue('ajax') === 1) {
            if (!in_array($action, $this->public_actions)) {
                $this->_requirePosEmployeeAuth();
            }
        } elseif (!empty($action) && !in_array($action, $this->public_actions)) {
            $this->_requirePosEmployeeAuth();
        }

        parent::postProcess();
        $action = Tools::toCamelCase(Tools::getValue('action'), true);
        if ($this->ajax) {
            if (!empty($action) && method_exists($this, 'ajaxProcess' . $action)) {
                $this->{'ajaxProcess' . $action}();
            } elseif (method_exists($this, 'ajaxProcess')) {
                return $this->ajaxProcess();
            }
        }
        if (!empty($action) && method_exists($this, 'process' . Tools::ucfirst(Tools::toCamelCase($action)))) {
            // Call process
            return $this->{'process' . Tools::toCamelCase($action)}();
        }
    }
    
    /**
     * Copy datas from $_POST to object
     *
     * @param object &$object Object
     * @param string $table Object table
     * @param bool $not_allow_empty_value
     */
    protected function copyFromPost(&$object, $table, $not_allow_empty_value = false)
    {
        /* Classical fields */
        foreach ($_POST as $key => $value) {
            if ($not_allow_empty_value) {
                if (!empty($value)) {
                    if (property_exists($object, $key) && $key != 'id_' . $table) {
                        /* Do not take care of password field if empty */
                        if ($key == 'passwd' && Tools::getValue('id_' . $table) && empty($value)) {
                            continue;
                        }
                        /* Automatically encrypt password in MD5 */
                        if ($key == 'passwd' && !empty($value)) {
                            $value = PosTools::encrypt($value);
                        }
                        $object->{$key} = $value;
                    }
                }
            } else {
                if (property_exists($object, $key) && $key != 'id_' . $table) {
                    /* Do not take care of password field if empty */
                    if ($key == 'passwd' && Tools::getValue('id_' . $table) && empty($value)) {
                        continue;
                    }
                    /* Automatically encrypt password in MD5 */
                    if ($key == 'passwd' && !empty($value)) {
                        $value = PosTools::encrypt($value);
                    }
                    $object->{$key} = $value;
                }
            }
        }

        /* Multilingual fields */
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            $class_vars = get_class_vars(get_class($object));
            $fields = [];
            if (isset($class_vars['definition']['fields'])) {
                $fields = $class_vars['definition']['fields'];
            }

            foreach ($fields as $field => $params) {
                if (array_key_exists('lang', $params) && $params['lang']) {
                    foreach (Language::getIDs(false) as $id_lang) {
                        if (Tools::isSubmit($field . '_' . (int) $id_lang)) {
                            $object->{$field}[(int) $id_lang] = Tools::getValue($field . '_' . (int) $id_lang);
                        }
                    }
                }
            }
        } else {
            $rules = call_user_func(array(get_class($object), 'getValidationRules'), get_class($object));
            if (count($rules['validateLang'])) {
                $language_ids = Language::getIDs(false);
                foreach ($language_ids as $id_lang) {
                    foreach (array_keys($rules['validateLang']) as $field) {
                        if (Tools::isSubmit($field . '_' . (int)$id_lang)) {
                            $object->{$field}[(int)$id_lang] = Tools::getValue($field . '_' . (int)$id_lang);
                        }
                    }
                }
            }
        }
    }
}
