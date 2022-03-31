<?php
/**
 * DiscountGenerator Prestashop Module
 *
 * @author    iRessources <support-prestashop@iressources.com>
 * @copyright Copyright &copy; 2015-2019 iRessources
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @link http://www.iressources.com/
 * @version 1.4.1
 */
class AdminCartRulesController extends AdminCartRulesControllerCore
{
    /*
    * module: discountgenerator
    * date: 2021-04-06 05:15:51
    * version: 1.4.1
    */
    public function __construct()
    {
        parent::__construct();
    }
    /*
    * module: discountgenerator
    * date: 2021-04-06 05:15:51
    * version: 1.4.1
    */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddcart_rule') || Tools::isSubmit('submitAddcart_ruleAndStay')) {
            if (Tools::getValue('show_group_discount') == 1) {
                $letters = str_split(Tools::strtolower(Tools::getValue('code_mask')));
                $combinations = 1;
                $combinationsRegexp = '';
                foreach ($letters as $v) {
                    if ($v == 'x') {
                        $combinations = $combinations * 10;
                        $combinationsRegexp .= '[0-9]';
                    }
                    if ($v == 'y') {
                        $combinations = $combinations * 26;
                        $combinationsRegexp .= '[a-z]';
                    }
                }
                $moduleInstance = Module::getInstanceByName('discountgenerator');
                if (Tools::getValue('coupon_quantity') > $combinations) {
                    $this->errors[] = sprintf($moduleInstance->l('Your combination of numbers and letters : %s is only sufficient to create %s vouchers. To create %s vouchers you have to increase your combination power. Please add an X or a Y to the Code mask field.'), Tools::getValue('code_mask'), $combinations, Tools::getValue('coupon_quantity'));
                }
                if (!is_numeric(Tools::getValue('coupon_quantity')) || Tools::getValue('coupon_quantity') < 1) {
                    $this->errors[] = $moduleInstance->l('Invalid coupons quantity count');
                }
                if (Tools::getValue('code_prefix') == ''
                    || (strpos(Tools::getValue('code_prefix'), '_') !== false)
                    || Tools::getValue('code_prefix') == $moduleInstance->l('Prefix')
                    || !Validate::isCleanHtml(Tools::getValue('code_prefix'))
                ) {
                    $this->errors[] = $moduleInstance->l('Invalid coupons prefix');
                }
                if (Tools::getValue('code_mask') == ''
                    || Tools::getValue('code_mask') == $moduleInstance->l('Code mask')
                    || !preg_match("~[xy]+~i", Tools::getValue('code_mask'))
                ) {
                    $this->errors[] = $moduleInstance->l('Invalid coupons mask');
                }
                if (!count($this->errors)) {
                    $combinationsExists = Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE `code` REGEXP \'^' . pSQL(Tools::getValue('code_prefix')) . $combinationsRegexp . '$\'');
                    if ($combinations - $combinationsExists < Tools::getValue('coupon_quantity')) {
                        $this->errors[] = sprintf($moduleInstance->l('Can\'t generate this count of vouchers using current mask. You can generate %s vouchers with current mask (possible mask combinations - %s, already exists mask vouchers - %s).'), $combinations - $combinationsExists, $combinations, $combinationsExists);
                    }
                }
            }
        }
        return parent::postProcess();
    }
    /*
    * module: discountgenerator
    * date: 2021-04-06 05:15:51
    * version: 1.4.1
    */
    public function processAdd()
    {
        if (Tools::getValue('show_group_discount') == 1 && count($this->errors) <= 0) {
            Db::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "discountgenerator_group` (`date`) VALUES(NOW())");
            $group_id = Db::getInstance()->Insert_ID();
            foreach (Language::getLanguages() as $language) {
                $name = Tools::getValue('name_' . $language['id_lang']);
                if (Tools::getIsset('name_' . $language['id_lang']) && !empty($name)) {
                    $name = pSQL(Tools::getValue('name_' . $language['id_lang']));
                } else {
                    $name = pSQL(Tools::getValue('name_' . (int)(Configuration::get('PS_LANG_DEFAULT'))));
                }
                Db::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "discountgenerator_group_lang` (`id_group`,`id_lang`,`name`) VALUES({$group_id},{$language['id_lang']},'{$name}')");
            }
            for ($i = 1; $i <= Tools::getValue('coupon_quantity'); $i++) {
                do {
                    $code = Tools::strtoupper(Tools::getValue('code_prefix'));
                    $letters = str_split(Tools::strtolower(Tools::getValue('code_mask')));
                    foreach ($letters as $v) {
                        if ($v == 'x') {
                            $code .= chr(mt_rand(48, 57));
                        }
                        if ($v == 'y') {
                            $code .= chr(mt_rand(65, 90));
                        }
                    }
                } while (CartRule::cartRuleExists($code));
                $_POST['code'] = $code;
                $cart_rule = parent::processAdd();
                if (!$cart_rule || !$cart_rule->id) {
                    $this->errors[] = Tools::displayError('An error occurred while creating object.') . ' <b>' . $this->table . '</b>';
                    break;
                }
                Db::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "discountgenerator_list` (`id_cart_rule`,`id_group`) VALUES({$cart_rule->id},{$group_id})");
            }
            return isset($cart_rule) ? $cart_rule : null;
        } else {
            if ($cart_rule = parent::processAdd()) {
                $this->context->smarty->assign('new_cart_rule', $cart_rule);
            }
            if (Tools::getValue('submitFormAjax')) {
                $this->redirect_after = false;
            }
            return $cart_rule;
        }
    }
    /*
    * module: discountgenerator
    * date: 2021-04-06 05:15:51
    * version: 1.4.1
    */
    public function createTemplate($tpl_name)
    {
        if ($tpl_name != 'form.tpl') {
            return parent::createTemplate($tpl_name);
        }
        if (_PS_VERSION_ >= 1.5 && _PS_VERSION_ < 1.6) {
            $tpl_name = 'form_1_5.tpl';
        }
        if (file_exists(_PS_MODULE_DIR_ . 'discountgenerator' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $this->override_folder . $tpl_name)) {
            return $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'discountgenerator' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $this->override_folder . $tpl_name, $this->context->smarty);
        }
        return parent::createTemplate($tpl_name);
    }
    /*
    * module: discountgenerator
    * date: 2021-04-06 05:15:51
    * version: 1.4.1
    */
    public function renderForm()
    {
        $this->context->smarty->assign(
            array(
                'show_group_discount' => Tools::getValue('show_group_discount'),
                'coupon_quantity' => Tools::getValue('coupon_quantity'),
                'code_prefix' => Tools::getValue('code_prefix'),
                'code_mask' => Tools::getValue('code_mask')
            )
        );
        return parent::renderForm();
    }
}
