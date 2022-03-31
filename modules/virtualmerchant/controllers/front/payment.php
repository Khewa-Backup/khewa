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

class VirtualMerchantPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $auth = true;
	public $guestAllowed = true;
	public $display_column_left = false;
	public $display_column_right = false;

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryPlugin('fancybox');

        $this->registerStylesheet('vmcardcss', '/modules/virtualmerchant/views/css/card-js.min.css', ['media' => 'all', 'priority' => 0]);

		//register cardjs first, which appears to control the order of loading the javascript.
        $this->registerJavascript('vmcardjs', '/modules/virtualmerchant/views/js/card-js.min.js', ['position' => 'bottom', 'priority' => 0]);
        $this->registerJavascript('vmfrontjs', '/modules/virtualmerchant/views/js/front.js', ['position' => 'bottom', 'priority' => 10]);
    }

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		//class variables are being ignored for some reason.  so moved here so it would work properly
		$this->display_column_left = false;
		$this->display_column_right = false;

		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		if (Tools::isSubmit('paymentSubmit')) {
			if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
				strcmp(Tools::getToken('merchant'), Tools::getValue('token')))
				Tools::redirect('order.php');
		}

		if (Tools::isSubmit('paymentSubmit') || Tools::isSubmit('dccSubmit')) {
			$return = $this->module->execPayment($cart);

			if ($return['result'] == 'errorCode' or $return['result'] == 'declined' or $return['result'] == 'failed')
			{
				$errors = array();
				$errors[] = $this->module->l('There was a problem processing your credit card, please double check your data and try again.');
		
				if (isset($return['code']))
					$errors[] = $this->module->l('Error code '.$return['code'].': '.$return['message']);

				if (isset($return['ssl_result_message']))
					$errors[] = $this->module->l('Result Message: '.$return['ssl_result_message']);

				if (isset($return['txn_id']))
					$errors[] = $this->module->l('Transaction ID: '.$return['txn_id']);

				$this->context->smarty->assign('perrors', $errors);
			}
			else if ($return['result'] == 'validation')
				$this->context->smarty->assign('perrors', $return['perrors']);

			elseif ($return['result'] == 'dccoption')
			{
				$this->context->smarty->assign(array(
					'mytoken'					=> Tools::getToken('merchant'),
					'status'					=> 'dccoption',
					'original_txn_currency'		=> $this->context->currency->iso_code,
					'response'					=> $return['response'],
				));

				$this->setTemplate('dccoption.tpl');
				return;
			}
		}

		//setup the payment form fields
		$this->context->smarty->assign(array(
			'vm_total'			=> Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
			'mytoken'			=> Tools::getToken('merchant'),
			'this_path_vm'		=> $this->module->getPathUri(),
		));

		$this->setTemplate('module:virtualmerchant/views/templates/front/payment_execution.tpl');
	}
}