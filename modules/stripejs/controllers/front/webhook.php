<?php
/**
 * 2007-2018 PrestaShop
 *
 * DISCLAIMER
 ** Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

class stripejsWebhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
	   
       include dirname(__FILE__).'/../../lib/Stripe.php';
        \Stripe\Stripe::setApiKey(Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PRIVATE_KEY_LIVE') : Configuration::get('STRIPE_PRIVATE_KEY_TEST'));
        // Retrieve the request's body and parse it as JSON
        $input = Tools::file_get_contents("php://input");
        $event_json = json_decode($input);
        try {
            \Stripe\Event::retrieve($event_json->id);
        } catch (Exception $e) {
            print_r($e->getMessage());die;
        }

        http_response_code(200);
		
		if (Tools::getValue('token')!=Configuration::get('STRIPE_WEBHOOK_TOKEN')) {
			die('Invalid token!!');
		}

        if ($event_json) {
			
			$source = $event_json->data->object->source;
            
            if ($event_json->type == "source.chargeable" && $source->type == "multibanco") {
                                
                $stripe_payment = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'stripejs_transaction WHERE type = "payment" && `status` = "unpaid" && `source` = "' . pSQL($source->id) . '"');
                if (isset($stripe_payment['status'])) {
                    
                    $result_json = \Stripe\Charge::create(array('source' => $source->id, 'amount' => $source->amount, 'currency' => $source->currency, 'description' => $this->l('Order ID:').' '.(int)$stripe_payment['id_order'],"receipt_email" => $source->owner->email,"expand" =>array("balance_transaction")));
                    
                    if($result_json->status == 'succeeded') {
                        
                      $order = new Order($stripe_payment['id_order']);
                      if (Validate::isLoadedObject($order)) {
                          if ($order->getCurrentState() != Configuration::get('STRIPE_PAYMENT_ORDER_STATUS')) {
                             $order->setCurrentState(Configuration::get('STRIPE_PAYMENT_ORDER_STATUS'));
                          }
                      }
                      
                      Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stripejs_transaction` SET `status` = "paid", `id_transaction` = "'.pSQL($result_json->id).'" WHERE `id_stripe_transaction` = '.pSQL($stripe_payment['id_stripe_transaction']));
                    }
                }
            }
            
            if (($event_json->type == "source.canceled" || $event_json->type == "source.failed") && $source->type == "multibanco") {
                                
                $stripe_payment = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'stripejs_transaction WHERE type = "payment" && `status` = "unpaid" && `source` = "' . pSQL($source->id) . '"');
                if (isset($stripe_payment['status'])) {
                    
                      $order = new Order($stripe_payment['id_order']);
                      if (Validate::isLoadedObject($order)) {
                          if ($order->getCurrentState() != Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS')) {
                             $order->setCurrentState(Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS'));
                          }
                      }
                      if($event_json->type == "source.canceled")
                        $set = 'canceled';
                        else
                        $set = 'failed';
                      Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stripejs_transaction` SET `status` = "'.$set.'" WHERE `id_stripe_transaction` = '.pSQL($stripe_payment['id_stripe_transaction']));
                    }
            }
           
            if ($event_json->type == "charge.canceled" || $event_json->type == "charge.failed") {
                $source_type = $event_json->data->object->source->type;
                $id_transaction = $event_json->data->object->id;
                if (in_array($source_type,array('sofort','sepa_debit'))) {
                    $stripe_payment = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'stripejs_transaction WHERE `id_transaction` = "' . pSQL($id_transaction) . '"');
                    if ($stripe_payment) {
                        $order = new Order($stripe_payment['id_order']);
                        if (Validate::isLoadedObject($order)) {
							if ($order->getCurrentState() != Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS')) {
                               $order->setCurrentState(Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS'));
							}
                        }
						if($event_json->type == "charge.canceled")
						$set = 'canceled';
						else
						$set = 'failed';
                        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stripejs_transaction` SET `status` = "'.$set.'" WHERE `id_transaction` = "'.pSQL($id_transaction).'"');
                    }
                }
            }
            if ($event_json->type == "charge.succeeded") {
                $source_type = $event_json->data->object->source->type;
                $id_transaction = $event_json->data->object->id;
                if (in_array($source_type,array('sofort','sepa_debit'))) {
                    $stripe_payment = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'stripejs_transaction WHERE `id_transaction` = "' . pSQL($id_transaction) . '"');
					$order = new Order($stripe_payment['id_order']);
                    if (Validate::isLoadedObject($order)) {
						
                       if ($order->getCurrentState() != Configuration::get('STRIPE_PAYMENT_ORDER_STATUS')) {
                            $order->setCurrentState(Configuration::get('STRIPE_PAYMENT_ORDER_STATUS'));
                        }else {
                        die('Order is not in the awaiting state of payment.');
                        }
                        Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stripejs_transaction` SET `status` = "paid" WHERE `id_transaction` = "'.pSQL($id_transaction).'"');
                    }
                }
            }
            die('ok');
        }
    }
}
