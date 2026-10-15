<?php
/**
 *  2019 Zack Hussain
 *
 *  @author 		Zack Hussain <me@zackhussain.ca>
 *  @copyright  	2019 Zack Hussain
 *
 *  DISCLAIMER
 *
 *  Do not redistribute without my permission. Feel free to modify the code as needed.
 *  Modifying the code may break future PrestaShop updates.
 *  Do not remove this comment containing author information and copyright.
 *
 */

class CanadapostlabelsCarrierModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        // Check if request is from module
        if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $this->module->getSecureKey()) {
            exit;
        }
    }

    public function postProcess()
    {
        parent::postProcess();

        // Update cart carrier if selected
        if (Tools::isSubmit('submitChangeCarrier') && Tools::isSubmit('carrier')) {
            $this->module->processChangeCarrier();
            die();
        }

        // Show/hide postcode field
        if (Tools::isSubmit('submitChangeCountry') && Tools::isSubmit('id_country')) {
            $Country = new Country(Tools::getValue('id_country'));
            $response = array();
            if (Validate::isLoadedObject($Country)) {
                $response['hasPostcode'] = ($Country->iso_code != 'CA' && $Country->iso_code != 'US') ? false : true;
            } else {
                $response['error'] = true;
            }
            die(json_encode($response));
        }

        // get new carriers on address change
        if (Tools::isSubmit('submitChangeAddress') &&
            Tools::isSubmit('ajax')
        ) {
            $error = false;
            if (!Tools::isSubmit('id_address')) {
                $this->context->cookie->id_country = (int)Tools::getValue('id_country');

                $Country = new Country((int)Tools::getValue('id_country'));

                if (($Country->iso_code == 'CA' || $Country->iso_code == 'US') && Tools::isEmpty(Tools::getValue('postcode'))) {
                    $error = $this->module->l('Please enter a postal/zip code.');
                } elseif (($Country->iso_code == 'CA' && !\CanadaPostPs\Tools::isCanadianPostalCode(Tools::getValue('postcode'))) ||
                          ($Country->iso_code == 'US' && !Validate::isZipCodeFormat(Tools::getValue('postcode')))) {
                    $error = $this->module->l('Invalid postal/zip code.');
                } else {

                    // clear unused cookies for intl countries
                    if ($Country->iso_code != 'CA' && $Country->iso_code != 'US') {
                        $this->context->cookie->postcode = null;
                    } else {
                        $this->context->cookie->postcode = (string)Tools::getValue('postcode');
                    }
                }
            }

            if (!$error) {

                // Add cart if no cart found
                if (!$this->context->cart->id) {
                    if (Context::getContext()->cookie->id_guest) {
                        $guest                             = new Guest(Context::getContext()->cookie->id_guest);
                        $this->context->cart->mobile_theme = $guest->mobile_theme;
                    }
                    $this->context->cart->add();
                    if ($this->context->cart->id) {
                        $this->context->cookie->id_cart = (int)$this->context->cart->id;
                    }
                }

                // Unset selected delivery option
                $this->context->cart->id_carrier = 0;
                $this->context->cart->delivery_option = null;
                $this->context->cart->update();

                // If cart has an address, update the address to the new country/postcode
                if (Tools::isSubmit('id_address')) {

                    // unset guest address
                    $this->context->cookie->id_country = null;
                    $this->context->cookie->postcode = null;

                    // update cart address
                    $Address = new Address(Tools::getValue('id_address'));
                    if (Validate::isLoadedObject($Address) && $Address->id_customer == $this->context->customer->id) {
                        $this->context->cart->updateAddressId($this->context->cart->id_address_delivery, $Address->id);
                    }
                }

                // Clear Cache
                $Cache = CanadaPostPs\Cache::getByCartId($this->context->cart->id);
                if (Validate::isLoadedObject($Cache)) {
                    $Cache->clearCacheRates();
                }
            }

            $params = array(
                'cart'    => $this->context->cart
            );

            // If this is a product page, set the product param
            if (Tools::getIsset('id_product')) {
                $params['product'] = new Product(Tools::getValue('id_product'));
                if (Tools::getIsset('id_product_attribute')) {
                    $params['product']->id_product_attribute = Tools::getValue('id_product_attribute');
                }
            }

            die($this->module->displayShippingEstimator($params, $error));
        }
    }
}
