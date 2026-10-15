<?php
/**
 * This file is part of module Virtual Merchant
 *
 *  @author    Bellini Services <bellini@bellini-services.com>
 *  @copyright 2007-2017 bellini-services.com
 *  @license   readme
 *
 * Your purchase grants you usage rights subject to the terms outlined by this license.
 *
 * You CAN use this module with a single, non-multi store configuration, production installation and unlimited test installations of PrestaShop.
 * You CAN make any modifications necessary to the module to make it fit your needs. However, the modified module will still remain subject to this license.
 *
 * You CANNOT redistribute the module as part of a content management system (CMS) or similar system.
 * You CANNOT resell or redistribute the module, modified, unmodified, standalone or combined with another product in any way without prior written (email) consent from bellini-services.com.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VirtualMerchant extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();
    private $devMode = false;

    public function __construct()
    {
        $this->name = 'virtualmerchant';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.1';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' <= '1.7.99');
        $this->author = 'Bellini Services';
        $this->controllers = array('payment');

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        $this->need_instance = 1;
        $this->module_key = "f57e285059fe634afe41696f99e6e51c";

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Payments via Credit Card');
        $this->description = $this->l('Process credit card transactions through Virtual Merchant.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (!$this->checkSettings()) {
            $this->warning = $this->l('You must complete the configuration of the Virtual Merchant module before it will function properly.');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        if (!parent::install() or !$this->registerHook('paymentOptions') or !$this->registerHook('displayPaymentReturn')) {
            return false;
        }
        Configuration::updateValue('VM_DEMO', 1);
        Configuration::updateValue('VM_LOG_TRANSACTIONS', 0);
        Configuration::updateValue('VM_VERSION', $this->version);

        return true;
    }
    
    public function uninstall()
    {
        Configuration::deleteByName('VM_ACCT_ID');
        Configuration::deleteByName('VM_USER_ID');
        Configuration::deleteByName('VM_PIN_NUM');
        Configuration::deleteByName('VM_DEMO');
        Configuration::deleteByName('VM_LOG_TRANSACTIONS');
        Configuration::deleteByName('VM_VERSION');

        return parent::uninstall();
    }

    /**
     * Display the Back-office interface of the Braintree's module
     *
     * @return string HTML/JS Content
     */
    public function getContent()
    {
        $this->_html .= '<link href="'.$this->_path.'views/css/braintree-prestashop-admin.css" rel="stylesheet" type="text/css" media="all" />';

        if (Tools::isSubmit('SubmitVM')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayInfos();
        $this->_html .= $this->_displayTechChecks();
        $this->_html .= $this->_displayDocumentation();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    protected function _displayInfos()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    protected function _displayTechChecks()
    {
        $requirements = $this->checkRequirements();
        $check_result = $requirements['result'];
        unset($requirements['result']);

        $this->smarty->assign(array(
            'requirements' => $requirements,
            'check_result' => $check_result,
        ));
        return $this->display(__FILE__, 'techchecks.tpl');
    }

    protected function _displayDocumentation()
    {
        return $this->display(__FILE__, 'documentation.tpl');
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('SubmitVM')) {
            $vm_acct_id = Tools::getValue('VM_ACCT_ID') !== false ? (string)Tools::getValue('VM_ACCT_ID') : false;
            $vm_user_id = Tools::getValue('VM_USER_ID') !== false ? (string)Tools::getValue('VM_USER_ID') : false;
            $vm_pin_num = Tools::getValue('VM_PIN_NUM') !== false ? (string)Tools::getValue('VM_PIN_NUM') : false;
            $demo = Tools::getValue('VM_DEMO') !== false ? (int)Tools::getValue('VM_DEMO') : false;
            $vm_log_transactions = Tools::getValue('VM_LOG_TRANSACTIONS') !== false ? (int)Tools::getValue('VM_LOG_TRANSACTIONS') : false;

            if (!$vm_acct_id || $vm_acct_id===false) {
                $this->_postErrors[] = $this->l('Virtual Merchant Account ID is required.');
            }
            if (!$vm_user_id || $vm_user_id===false) {
                $this->_postErrors[] = $this->l('Virtual Merchant User ID is required.');
            }
            if (!$vm_pin_num || $vm_pin_num===false) {
                $this->_postErrors[] = $this->l('Virtual Merchant Pin Number is required.');
            }
            if ($demo===false) {
                $this->_postErrors[] = $this->l('Demo Mode is required.');
            }
            if ($vm_log_transactions===false) {
                $this->_postErrors[] = $this->l('Enable Transaction Logs is required.');
            }
        }
        return !count($this->_postErrors);
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('SubmitVM')) {
            Configuration::updateValue('VM_ACCT_ID', (string)Tools::getValue('VM_ACCT_ID'));
            Configuration::updateValue('VM_USER_ID', (string)Tools::getValue('VM_USER_ID'));
            Configuration::updateValue('VM_PIN_NUM', (string)Tools::getValue('VM_PIN_NUM'));
            Configuration::updateValue('VM_DEMO', (int)(Tools::getValue('VM_DEMO')));
            Configuration::updateValue('VM_LOG_TRANSACTIONS', (int)(Tools::getValue('VM_LOG_TRANSACTIONS')));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cog'
                ),
                'description' => $this->l('Configure your API settings in this block.  Be sure to read the modules documentation for assistance'),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Account ID'),
                        'name' => 'VM_ACCT_ID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('User ID'),
                        'name' => 'VM_USER_ID',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pin Number'),
                        'name' => 'VM_PIN_NUM',
                        'required' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Demo Mode'),
                        'name' => 'VM_DEMO',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'VM_DEMO_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'VM_DEMO_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc' => $this->l('When you first signup with Elavon, your account will be in a demo mode (test mode). Once integration testing has been completed and you are ready to begin processing Production transactions, switch demo mode to No')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Transaction Logs (debug)?'),
                        'name' => 'VM_LOG_TRANSACTIONS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'VM_LOG_TRANSACTIONS_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'VM_LOG_TRANSACTIONS_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                        'desc' => $this->l('Only enable when requested by our support team.  If this option is enabled, all transactions to and from the VM gateway will be recorded in the �/modules/virtualmerchant/logs� directory.  This is useful for testing in the sandbox and for troubleshooting issues. It is recommended to disable this option when you move to �Production� mode, and only enable if you are encountering issues or have been requested to enable by our support team. Sensitive customer and payment information will be recorded in this logs, so be sure to delete these logs when you are finished with them.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'SubmitVM';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $array = array(
            'VM_ACCT_ID' => Tools::getValue('VM_ACCT_ID', Configuration::get('VM_ACCT_ID')),
            'VM_USER_ID' => Tools::getValue('VM_USER_ID', Configuration::get('VM_USER_ID')),
            'VM_PIN_NUM' => Tools::getValue('VM_PIN_NUM', Configuration::get('VM_PIN_NUM')),

            'VM_DEMO' => Tools::getValue('VM_DEMO', Configuration::get('VM_DEMO')),
            'VM_LOG_TRANSACTIONS' => Tools::getValue('VM_LOG_TRANSACTIONS', Configuration::get('VM_LOG_TRANSACTIONS')),
        );
        return $array;
    }


    public function getVMUrl()
    {
        return Configuration::get('VM_DEMO') ? 'https://api.demo.convergepay.com/VirtualMerchantDemo/process.do' : 'https://api.convergepay.com/VirtualMerchant/process.do';
    }

    public function execPayment($cart)
    {
        if (!$this->active) {
            return false;
        }

        $result = array();
        $errors = array();

        $address = new Address((int)($cart->id_address_invoice));
        $delivery = new Address((int)($cart->id_address_delivery));
        $customer = new Customer((int)($cart->id_customer));

        //need to revisit this. since the module only supports a single currency, and I assume it has to be USD, then why do we need the currency to calculate the order total, wouldn't we just use the cart currency?
        $id_currency = (int)(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->currency = new Currency((int)($id_currency));

        if (Tools::isSubmit('paymentSubmit')) {
            $card_number = Tools::getValue('card-number-nospaces');
            $cvc = Tools::getValue('cvc');
            $CCExpiresMonth = (int)Tools::getValue('expiry-month');
            $CCExpiresYear = (int)Tools::getValue('expiry-year');

            //validate all input fields
            if ($this->is_blank($card_number)) {
                $errors[] = $this->l('You must enter a credit card number');
            } elseif (strpos($card_number, " ") !== false) {
                $errors[] = $this->l('The credit card number you entered is invalid. Remove spaces and formating and try again.');
            } elseif (!$this->validateCard($card_number)) {
                $errors[] = $this->l('The credit card number you entered is invalid');
            }

            if ($this->is_blank($cvc)) {
                $errors[] = $this->l('You must enter a CVV/CVC code');
            } elseif (!is_numeric($cvc)) {
                $errors[] = $this->l('The CVV/CVC code you entered is invalid');
            }

            if ($this->is_blank($CCExpiresMonth)) {
                $errors[] = $this->l('You must select the expiration date\'s month');
            } elseif (!is_numeric($CCExpiresMonth)) {
                $errors[] = $this->l('The expiration date you entered is invalid');
            }

            if ($this->is_blank($CCExpiresYear)) {
                $errors[] = $this->l('You must select the expiration date\'s year');
            } elseif (!is_numeric($CCExpiresYear)) {
                $errors[] = $this->l('The expiration date you entered is invalid');
            }

            if (!$this->validateCCExpDate($CCExpiresMonth.'/'.$CCExpiresYear)) {
                $errors[] = $this->l('The expiration date you entered is invalid');
            }

			//add leading zero to the month when the number is less than 10
			$CCExpiresMonth = sprintf("%02d", $CCExpiresMonth);

            //only continue if there are no validation errors
            if (!sizeof($errors)) {
                $conf = Configuration::getMultiple(array('VM_ACCT_ID', 'VM_USER_ID', 'VM_PIN_NUM', 'VM_DEMO'));

                $total = number_format(Tools::convertPrice($cart->getOrderTotal(true, Cart::BOTH), $this->currency), 2, '.', '');
                $totalNoTax = number_format(Tools::convertPrice($cart->getOrderTotal(false, Cart::BOTH), $this->currency), 2, '.', '');
                $tax = $total - $totalNoTax;

                $demo = $conf['VM_DEMO'] ? $demo = 'true' : $demo = 'false';
                                
                $products = $cart->getProducts();
                $prod="";
                foreach ($products as $key => $product) {
                    $products[$key]['name'] = str_replace('"', '\'', $product['name']);
                    $prod .= $products[$key]['name'].' ';
                    if (isset($product['attributes'])) {
                        $products[$key]['attributes'] = str_replace('"', '\'', $product['attributes']);
                        $prod .= $products[$key]['attributes'].' ';
                    }
                    $products[$key]['name'] = htmlentities(utf8_decode($product['name']));
                    $products[$key]['amount'] = number_format(Tools::convertPrice($product['price_wt'], $this->currency), 2, '.', '');
                    $prod .= $products[$key]['amount'].', ';
                }

                $fields = array(
                    'ssl_testmode'        => $demo,
                    'ssl_transaction_type' => 'ccsale',
                    'ssl_entry_mode'    => '01',
                    'ssl_card_present'    => 'N',
                    'ssl_merchant_id'    => (string)$conf['VM_ACCT_ID'],
                    'ssl_pin'            => (string)$conf['VM_PIN_NUM'],
                    'ssl_user_id'        => (string)$conf['VM_USER_ID'],
                    'ssl_amount'        => $total,
//					'ssl_transaction_currency'		=> $this->context->currency->iso_code,	//this is only supported if the merchant has Multi Currency enabled in their terminal, otherwise there will be an error

                    'ssl_salestax'        => $tax,
                    'ssl_description'    => VirtualMerchant::truncateString($prod, 255),
                    'ssl_show_form'        => 'false',
                    'ssl_result_format' => 'ASCII',

                    'ssl_card_number'    => $card_number,
                    'ssl_exp_date'        => $CCExpiresMonth . $CCExpiresYear,
                    'ssl_cvv2cvc2_indicator' => '1',
                    'ssl_cvv2cvc2'        => $cvc,

                    'ssl_first_name'    => VirtualMerchant::truncateString((string)($address->firstname), 20),
                    'ssl_last_name'        => VirtualMerchant::truncateString((string)($address->lastname), 30),
                    'ssl_avs_address'    => VirtualMerchant::truncateString((string)($address->address1), 30),
                    'ssl_address2'        => VirtualMerchant::truncateString((string)($address->address2), 30),
                    'ssl_city'            => VirtualMerchant::truncateString((string)($address->city), 30),
                    'ssl_avs_zip'        => VirtualMerchant::truncateString((string)($address->postcode), 9),
                    'ssl_country'       => (string)(VirtualMerchant::countryISOLookup(Country::getIsoById($address->id_country))),
                    'ssl_phone'            => VirtualMerchant::truncateString((string)($address->phone), 20),
                    'ssl_email'            => VirtualMerchant::truncateString((string)($customer->email), 100),
                    'ssl_company'        => VirtualMerchant::truncateString((string)($address->company), 50),

                    'ssl_ship_to_first_name' => VirtualMerchant::truncateString((string)($delivery->firstname), 20),
                    'ssl_ship_to_last_name'     => VirtualMerchant::truncateString((string)($delivery->lastname), 30),
                    'ssl_ship_to_address1'     => VirtualMerchant::truncateString((string)($delivery->address1), 30),
                    'ssl_ship_to_address2'     => VirtualMerchant::truncateString((string)($delivery->address2), 30),
                    'ssl_ship_to_city'        => VirtualMerchant::truncateString((string)($delivery->city), 30),
                    'ssl_ship_to_zip'        => VirtualMerchant::truncateString((string)($delivery->postcode), 10),
                    'ssl_ship_to_country'   => (string)(VirtualMerchant::countryISOLookup(Country::getIsoById($delivery->id_country))),
                    'ssl_ship_to_phone'        => VirtualMerchant::truncateString((string)($delivery->phone), 20),

                    'ssl_invoice_number'    => (int)($cart->id),
                );
                if (Country::containsStates($address->id_country)) {
                    $invoice_state=new State((int)($address->id_state));
                    $fields['ssl_state'] = (string)($invoice_state->name);
                }
                if (Country::containsStates($delivery->id_country)) {
                    $delivery_state=new State((int)($delivery->id_state));
                    $fields['ssl_ship_to_state'] = (string)($delivery_state->name);
                }

                $ip_address = $this->get_client_ip_server();
                if ($ip_address) {
                    $fields['ssl_cardholder_ip'] = (string)($ip_address);
                }

                // perform request
                $response = $this->makeConnection($fields, $cart->id, 'ccsale');

                //Error code returned only if an error occurred. Typically, when the transaction failed validation or the request is incorrect.
                //This is different then the ccsale failing for AVS, Fraud or decline reasons, in which case we would check ssl_result
                if (isset($response['errorCode'])) {
                    $result['result'] = 'errorCode';
                    $result['code'] = $response['errorCode'];
                    $result['message'] = $response['errorMessage'];
                    $result['name'] = $response['errorName'];
                    return $result;
                }

                //if dccoption is present, this means it is a Dynamic Currency Conversion transaction (dcc)
                //this requires redirecting the customer to a new page, showing them the currency conversion and allowing the customer to decide if the transaction should be processed in their cards currency, or in the transactions currency.
                if (isset($response['dccoption'])) {
                    $result['result'] = 'dccoption';
                    $result['response'] = $response;
                    return $result;
                }

                if (isset($response['ssl_result'])) {
                    $ssl_result = $response['ssl_result'];
                    $ssl_txn_id = $response['ssl_txn_id'];
                    $ssl_approval_code = $response['ssl_approval_code'];
                    $ssl_result_message = $response['ssl_result_message'];

                    if ($ssl_result == 0) { //approved
                        //up until PS v1.6.0.7, the payment method had to be equal to the modules displayName or the order confirmation page would not work
                        $payment_method = $this->displayName;
                        if (version_compare(_PS_VERSION_, '1.6.0.7', '>=')) {
                            $payment_method = $this->l('Credit Card');
                        }


                        $this->validateOrder((int)$this->context->cart->id, (int)Configuration::get('PS_OS_PAYMENT'), (float)$total, $payment_method, "Authorization Transaction Number: $ssl_txn_id, Approval Code: $ssl_approval_code, Result Message: $ssl_result_message", array(), null, false, $this->context->customer->secure_key);

                        $new_order = new Order((int)$this->currentOrder);
                        if (Validate::isLoadedObject($new_order)) {
                            $payment = $new_order->getOrderPaymentCollection();
                            if (isset($payment[0])) {
                                $payment[0]->transaction_id = pSQL($ssl_txn_id);
                                $payment[0]->save();
                            }
                        }

                        Tools::redirectLink(__PS_BASE_URI__."order-confirmation.php?id_module=".$this->id."&id_cart=".$cart->id."&id_order=".$this->currentOrder."&key=".$customer->secure_key);
                    } else { //declined
                        //TODO: need to test declines on live system to determine what is returned and what we can display
                        $result['result'] = 'declined';
                        $result['ssl_result_message'] = $ssl_result_message;
                        $result['txn_id'] = $ssl_txn_id;
                        return $result;
                    }
                } else { //invalid response, todo: refactor this to create the order with a failed status
                    $result['result'] = 'failed';
                    return $result;
                }
            } else {
                $result['result'] = 'validation';
                $result['perrors'] = $errors;
                return $result;
            }
        } elseif (Tools::isSubmit('dccSubmit')) {
            $id = Tools::getValue('transactionid');
            $dcc = (int)Tools::getValue('dcc');

            if ($dcc) {
                $dcc = 'Y';
            } else {
                $dcc = 'N';
            }

            $fields = array(
                'id'                    => $id,
                'dccoption'                => $dcc,
            );

            //we provide this just to bypass the warnings received in makeConnection when using devMode
            //however we can use this to setup use cases in devMode responses (TODO)
            if ($this->devMode) {
                $fields['ssl_cvv2cvc2'] = '987';
            }

            // perform request
            $response = $this->makeConnection($fields, $cart->id, 'dccoption');

            //Error code returned only if an error occurred. Typically, when the transaction failed validation or the request is incorrect.
            //This is different then the ccsale failing for AVS, Fraud or decline reasons, in which case we would check ssl_result
            if (isset($response['errorCode'])) {
                $result['result'] = 'errorCode';
                $result['code'] = $response['errorCode'];
                $result['message'] = $response['errorMessage'];
                $result['name'] = $response['errorName'];
                return $result;
            }

            if (isset($response['ssl_result'])) {
                $ssl_result = $response['ssl_result'];
                $ssl_txn_id = $response['ssl_txn_id'];
                $ssl_approval_code = $response['ssl_approval_code'];
                $ssl_result_message = $response['ssl_result_message'];
                $total = number_format(Tools::convertPrice($cart->getOrderTotal(true, Cart::BOTH), $this->currency), 2, '.', '');

                if ($ssl_result == 0) { //approved
                    //up until PS v1.6.0.7, the payment method had to be equal to the modules displayName or the order confirmation page would not work
                    $payment_method = $this->displayName;
                    if (version_compare(_PS_VERSION_, '1.6.0.7', '>=')) {
                        $payment_method = $this->l('Credit Card');
                    }

                    $this->validateOrder((int)$this->context->cart->id, (int)Configuration::get('PS_OS_PAYMENT'), (float)$total, $payment_method, "Authorization Transaction Number: $ssl_txn_id, Approval Code: $ssl_approval_code, Result Message: $ssl_result_message", array(), null, false, $this->context->customer->secure_key);

                    $new_order = new Order((int)$this->currentOrder);
                    if (Validate::isLoadedObject($new_order)) {
                        $payment = $new_order->getOrderPaymentCollection();
                        if (isset($payment[0])) {
                            $payment[0]->transaction_id = pSQL($ssl_txn_id);
                            $payment[0]->save();
                        }
                    }

                    Tools::redirectLink(__PS_BASE_URI__."order-confirmation.php?id_module=".$this->id."&id_cart=".$cart->id."&id_order=".$this->currentOrder."&key=".$customer->secure_key);
                } else { //declined
                    //TODO: need to test declines on live system to determine what is returned and what we can display
                    $result['result'] = 'declined';
                    $result['ssl_result_message'] = $ssl_result_message;
                    $result['txn_id'] = $ssl_txn_id;
                    return $result;
                }
            } else { //invalid response, todo: refactor this to create the order with a failed status
                $result['result'] = 'failed';
                $result['response'] = $response;
                return $result;
            }
        }
    }

	public function hookPaymentOptions($params)
	{
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }

		if (!$this->checkSettings()) {
			return;
		}

		$buttonText = $this->l('Pay by Credit Card');

		$embeddedOption = new PaymentOption();
		$embeddedOption->setCallToActionText($buttonText)
			->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
			->setAdditionalInformation($this->context->smarty->fetch('module:virtualmerchant/views/templates/front/intro.tpl'));

		$payment_options[] = $embeddedOption;

		return $payment_options;
	}

    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        if ($params['order']->module != $this->name) {
            return;
        }

        $state = $params['order']->getCurrentState();
        
        if ($state == Configuration::get('PS_OS_OUTOFSTOCK') or $state == Configuration::get('PS_OS_PAYMENT')) {
            $this->context->smarty->assign(array(
                'reference' => isset($params['order']->reference) ? $params['order']->reference : '#'.sprintf('%06d', $params['order']->id),
				'shop_name' => $this->context->shop->name,
            ));
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_module = $this->getCurrency((int)$cart->id_currency);

        if ((int)$cart->id_currency == (int)$currency_module->id) {
            return true;
        }
        return false;
    }

    private function makeConnection($fields, $id_cart, $transaction)
    {
        $build = http_build_query($fields);

        if ($this->devMode) {
            $response = array();
            $response['ssl_result']=0;                                                //0=Approved anything else is Declined
            $response['ssl_result_message']='APPROVED';
            $response['ssl_txn_id']='138FA6E57-3FBE-BBE5-8EE2-FBAE43C782D9';
            $response['ssl_invoice_number']=$id_cart;                                //returns the cart id
            $response['ssl_approval_code']='N20032';

            if ($fields['ssl_cvv2cvc2']=='456') { //decline
                //todo: need to confirm if errorCode is returned on a decline. unable to determine from API doc
                $response['ssl_result']=1;
                $response['ssl_result_message']='DECLINED';
                $response['ssl_txn_id']='';
                $response['ssl_approval_code']='';
            } elseif ($fields['ssl_cvv2cvc2']=='789') { //error
                $response['errorCode']='3000';    //'gateway not responding'
                $response['errorMessage']='gateway not responding';    //'gateway not responding'
                $response['errorName']='gateway not responding';    //'gateway not responding'
                $response['ssl_result']=1;
                $response['ssl_result_message']='';
                $response['ssl_txn_id']='';
                $response['ssl_approval_code']='';
            } elseif ($fields['ssl_cvv2cvc2']=='1234') { //dccoption, initial response
                unset($response['ssl_result']);
                unset($response['ssl_result_message']);
                unset($response['ssl_txn_id']);
                unset($response['ssl_invoice_number']);
                unset($response['ssl_approval_code']);
                $response['id']='123123';
                $response['ssl_txn_currency_code']='CAD';
                $response['ssl_markup']='3.25';
                $response['ssl_conversion_rate']='1.28958';
                $response['ssl_amount']='36.23';
                $response['ssl_cardholder_amount']='46.72';
                $response['dccoption']="{(option label='Please charge my purchase in my home currency')=Y;(option label='Do not charge me in my home currency; charge my purchase in US dollars')=N}";
            }

            return $response;
        }

        $ch = curl_init($this->getVMUrl());
//		$ch = curl_init('https://www.myvirtualmerchant.com/VirtualMerchant/test_tran.do');	//used to debug post variables
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $build);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        $this->recordTransaction($build, $id_cart, $transaction.'_request');

        $authorize = curl_exec($ch);
        if (!$authorize) {
            return "cURL error: " . curl_error($ch);
        }
        curl_close($ch);

        $this->recordTransaction($authorize, $id_cart, $transaction.'_response');

        $authorize = trim($authorize);
        $authorize = str_replace("\r", "", $authorize);
        $authorize = str_replace("\n", "&", $authorize);
        $this->recordTransaction($authorize, $id_cart, $transaction.'_response_cleansed');

        parse_str($authorize, $response);

        return $response;
    }

    /*
     *	validateCard($cardnumber)
     * 	Checks mod10 check digit of card, returns true if valid
     */
    public function validateCard($cardnumber)
    {
        $cardnumber = preg_replace("/\D|\s/", "", $cardnumber);  # strip any non-digits
        $cardlength = Tools::strlen($cardnumber);
        if ($cardlength != 0) {
            $parity = $cardlength % 2;
            $sum = 0;
            for ($i = 0; $i < $cardlength; $i++) {
                $digit = $cardnumber[$i];
                if ($i % 2 == $parity) {
                    $digit = $digit * 2;
                }
                if ($digit > 9) {
                    $digit = $digit-9;
                }
                $sum = $sum + $digit;
            }
            $valid = ($sum % 10 == 0);
            return $valid;
        }
        return false;
    }

    /* $value: should be formatted as mm/yy or mm/yyyy
    */
    public function validateCCExpDate($value)
    {
        //split the value into month and year.
        if (!preg_match('!^(\d+)\D+(\d+)$!', $value, $_match)) {
            return false;
        }

        $_month = $_match[1];
        $_year = $_match[2];
        
        //converts a 2 year date into a 4 year date.
        if (Tools::strlen($_year) == 2) {
            $_year = Tools::substr(date('Y', time()), 0, 2) . $_year;
        }

        $_month = (int) $_month;
        $_year = (int) $_year;

        //validates the month is a value between 1 and 12
        if ($_month < 1 || $_month > 12) {
            return false;
        }

        //validates the year is equal or greater than the current year
        if (date('Y', time()) > $_year) {
            return false;
        }

        //validates the year exp date is equal to or greater than then the current month/year
        if (date('Y', time()) == $_year && date('m', time()) > $_month) {
            return false;
        }

        return true;
    }

    /**
     * Check settings requirements to make sure the module will work properly
     *
     * @return boolean Check result
     */
    public function checkSettings()
    {
        $conf = Configuration::getMultiple(array('VM_ACCT_ID', 'VM_USER_ID', 'VM_PIN_NUM'));
        return $conf['VM_ACCT_ID'] != '' && $conf['VM_USER_ID'] != '' && $conf['VM_PIN_NUM'] != '';
    }

    /**
     * Check technical requirements to make sure the Braintree's module will work properly
     *
     * @return array Requirements tests results
     */
    public function checkRequirements()
    {
        $tests = array('result' => true);
        $tests['curl'] = array('name' => $this->l('PHP cURL extension must be enabled on your server'), 'result' => function_exists('curl_init'));
        $tests['configuration'] = array('name' => $this->l('Your must configure the module with your merchant account information'), 'result' => $this->checkSettings());

        foreach ($tests as $k => $test) {
            if ($k != 'result' && !$test['result']) {
                $tests['result'] = false;
            }
        }

        return $tests;
    }

    public function recordTransaction($data, $id_cart, $transaction_type)
    {
        $record = Configuration::get('VM_LOG_TRANSACTIONS');
        if (!$record) {
            return;
        }

        $time = time();
        $location = dirname(__FILE__).'/logs/'.$id_cart.'_'.$transaction_type.'_'.$time.'.log';
        error_log($time.': '.print_r($data, true), 3, $location);
    }

    /**
     * Function to get the client ip address
     * returns true if the IP address is a valid IPv4 address, otherwise false
     */
    public function get_client_ip_server()
    {
        $ipaddress = false;

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            $ipaddress = false;
        }
     
        return $ipaddress;
    }
    /**
     * is_blank( $var )
     * returns true if the var is blank (its like isset() but it works!)
     */
    public function is_blank($var)
    {
        return isset($var) || $var == '0' ? ($var == "" ? true : false) : false;
    }

    private static function truncateString($text, $limit)
    {
        if (Tools::strlen($text)>$limit) {
            return Tools::substr($text, 0, $limit);
        }

        return $text;
    }

    public static function countryISOLookup($isoCode)
    {
        if (Tools::strlen($isoCode)==3) {
            return $isoCode;
        } else {
            return VirtualMerchant::$countriesISO[$isoCode];
        }
    }

    private static $countriesISO = array(
          "AF" => "AFG",
          "AX" => "ALA",
          "AL" => "ALB",
          "DZ" => "DZA",
          "AS" => "ASM",
          "AD" => "AND",
          "AO" => "AGO",
          "AI" => "AIA",
          "AQ" => "ATA",
          "AG" => "ATG",
          "AR" => "ARG",
          "AM" => "ARM",
          "AW" => "ABW",
          "AU" => "AUS",
          "AT" => "AUT",
          "AZ" => "AZE",
          "BS" => "BHS",
          "BH" => "BHR",
          "BD" => "BGD",
          "BB" => "BRB",
          "BY" => "BLR",
          "BE" => "BEL",
          "BZ" => "BLZ",
          "BJ" => "BEN",
          "BM" => "BMU",
          "BT" => "BTN",
          "BO" => "BOL",
          "BA" => "BIH",
          "BW" => "BWA",
          "BV" => "BVT",
          "BR" => "BRA",
          "VG" => "VGB",
          "IO" => "IOT",
          "BN" => "BRN",
          "BG" => "BGR",
          "BF" => "BFA",
          "BI" => "BDI",
          "KH" => "KHM",
          "CM" => "CMR",
          "CA" => "CAN",
          "CV" => "CPV",
          "KY" => "CYM",
          "CF" => "CAF",
          "TD" => "TCD",
          "CL" => "CHL",
          "CN" => "CHN",
          "HK" => "HKG",
          "MO" => "MAC",
          "CX" => "CXR",
          "CC" => "CCK",
          "CO" => "COL",
          "KM" => "COM",
          "CG" => "COG",
          "CD" => "COD",
          "CK" => "COK",
          "CR" => "CRI",
          "CI" => "CIV",
          "HR" => "HRV",
          "CU" => "CUB",
          "CY" => "CYP",
          "CZ" => "CZE",
          "DK" => "DNK",
          "DJ" => "DJI",
          "DM" => "DMA",
          "DO" => "DOM",
          "EC" => "ECU",
          "EG" => "EGY",
          "SV" => "SLV",
          "GQ" => "GNQ",
          "ER" => "ERI",
          "EE" => "EST",
          "ET" => "ETH",
          "FK" => "FLK",
          "FO" => "FRO",
          "FJ" => "FJI",
          "FI" => "FIN",
          "FR" => "FRA",
          "GF" => "GUF",
          "PF" => "PYF",
          "TF" => "ATF",
          "GA" => "GAB",
          "GM" => "GMB",
          "GE" => "GEO",
          "DE" => "DEU",
          "GH" => "GHA",
          "GI" => "GIB",
          "GR" => "GRC",
          "GL" => "GRL",
          "GD" => "GRD",
          "GP" => "GLP",
          "GU" => "GUM",
          "GT" => "GTM",
          "GG" => "GGY",
          "GN" => "GIN",
          "GW" => "GNB",
          "GY" => "GUY",
          "HT" => "HTI",
          "HM" => "HMD",
          "VA" => "VAT",
          "HN" => "HND",
          "HU" => "HUN",
          "IS" => "ISL",
          "IN" => "IND",
          "ID" => "IDN",
          "IR" => "IRN",
          "IQ" => "IRQ",
          "IE" => "IRL",
          "IM" => "IMN",
          "IL" => "ISR",
          "IT" => "ITA",
          "JM" => "JAM",
          "JP" => "JPN",
          "JE" => "JEY",
          "JO" => "JOR",
          "KZ" => "KAZ",
          "KE" => "KEN",
          "KI" => "KIR",
          "KP" => "PRK",
          "KR" => "KOR",
          "KW" => "KWT",
          "KG" => "KGZ",
          "LA" => "LAO",
          "LV" => "LVA",
          "LB" => "LBN",
          "LS" => "LSO",
          "LR" => "LBR",
          "LY" => "LBY",
          "LI" => "LIE",
          "LT" => "LTU",
          "LU" => "LUX",
          "MK" => "MKD",
          "MG" => "MDG",
          "MW" => "MWI",
          "MY" => "MYS",
          "MV" => "MDV",
          "ML" => "MLI",
          "MT" => "MLT",
          "MH" => "MHL",
          "MQ" => "MTQ",
          "MR" => "MRT",
          "MU" => "MUS",
          "YT" => "MYT",
          "MX" => "MEX",
          "FM" => "FSM",
          "MD" => "MDA",
          "MC" => "MCO",
          "MN" => "MNG",
          "ME" => "MNE",
          "MS" => "MSR",
          "MA" => "MAR",
          "MZ" => "MOZ",
          "MM" => "MMR",
          "NA" => "NAM",
          "NR" => "NRU",
          "NP" => "NPL",
          "NL" => "NLD",
          "AN" => "ANT",
          "NC" => "NCL",
          "NZ" => "NZL",
          "NI" => "NIC",
          "NE" => "NER",
          "NG" => "NGA",
          "NU" => "NIU",
          "NF" => "NFK",
          "MP" => "MNP",
          "NO" => "NOR",
          "OM" => "OMN",
          "PK" => "PAK",
          "PW" => "PLW",
          "PS" => "PSE",
          "PA" => "PAN",
          "PG" => "PNG",
          "PY" => "PRY",
          "PE" => "PER",
          "PH" => "PHL",
          "PN" => "PCN",
          "PL" => "POL",
          "PT" => "PRT",
          "PR" => "PRI",
          "QA" => "QAT",
          "RE" => "REU",
          "RO" => "ROU",
          "RU" => "RUS",
          "RW" => "RWA",
          "BL" => "BLM",
          "SH" => "SHN",
          "KN" => "KNA",
          "LC" => "LCA",
          "MF" => "MAF",
          "PM" => "SPM",
          "VC" => "VCT",
          "WS" => "WSM",
          "SM" => "SMR",
          "ST" => "STP",
          "SA" => "SAU",
          "SN" => "SEN",
          "RS" => "SRB",
          "SC" => "SYC",
          "SL" => "SLE",
          "SG" => "SGP",
          "SK" => "SVK",
          "SI" => "SVN",
          "SB" => "SLB",
          "SO" => "SOM",
          "ZA" => "ZAF",
          "GS" => "SGS",
          "SS" => "SSD",
          "ES" => "ESP",
          "LK" => "LKA",
          "SD" => "SDN",
          "SR" => "SUR",
          "SJ" => "SJM",
          "SZ" => "SWZ",
          "SE" => "SWE",
          "CH" => "CHE",
          "SY" => "SYR",
          "TW" => "TWN",
          "TJ" => "TJK",
          "TZ" => "TZA",
          "TH" => "THA",
          "TL" => "TLS",
          "TG" => "TGO",
          "TK" => "TKL",
          "TO" => "TON",
          "TT" => "TTO",
          "TN" => "TUN",
          "TR" => "TUR",
          "TM" => "TKM",
          "TC" => "TCA",
          "TV" => "TUV",
          "UG" => "UGA",
          "UA" => "UKR",
          "AE" => "ARE",
          "GB" => "GBR",
          "US" => "USA",
          "UM" => "UMI",
          "UY" => "URY",
          "UZ" => "UZB",
          "VU" => "VUT",
          "VE" => "VEN",
          "VN" => "VNM",
          "VI" => "VIR",
          "WF" => "WLF",
          "EH" => "ESH",
          "YE" => "YEM",
          "ZM" => "ZMB",
          "ZW " => "ZWE ");
}
