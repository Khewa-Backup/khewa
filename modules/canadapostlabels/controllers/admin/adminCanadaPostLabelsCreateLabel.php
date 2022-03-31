<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium, is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

class AdminCanadaPostLabelsCreateLabelController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initHeader()
    {
        parent::initHeader();

        // Style material icons
        if (version_compare(_PS_VERSION_, '1.7.7') >= 0) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/material-styles.css');
        }
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->module->isConnected() || !$this->module->isVerified()) {
            $this->context->controller->errors[] = $this->module->l(\CanadaPostPs\Tools::$error_messages['CONNECT_ACCOUNT']);
            return false;
        }

        $Forms = new \CanadaPostPs\Forms();

        // Process form submits
        $Forms->postProcessShipments($this->context->link->getAdminLink($this->controller_name));

        // modal.tpl template vars
        $this->modals[] = array(
            'modal_id' => 'labelModal',
            'modal_title' => \CanadaPostPs\Tools::renderHtmlTag('h4', $this->module->l('Print Label')),
            'modal_content' => \CanadaPostPs\Tools::renderHtmlTag(
                    'div', null, array('class' => 'modal-body')
                ) . $this->module->logo,
            'modal_actions' => true,
            'modal_class' => 'zhmedia-modal'
        );

        $tabs = array(
            'createLabel'     => array(
                'title'   => $this->module->l('Create Label'),
                'id'      => 'createLabel',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('print'),
                'badge'   => false,
                'enabled' => true,
            ),
            'createReturnLabel'     => array(
                'title'   => $this->module->l('Create Return Label'),
                'id'      => 'createReturnLabel',
                'icon'    => \CanadaPostPs\Icon::getIconHtml('arrow_back'),
                'badge'   => false,
                'enabled' => true,
            ),
        );

        $this->context->smarty->assign(array(
            'forms' => array(
                'createLabel'     => $Forms->renderCreateLabelForm(
                    false,
                    $this->context->link->getAdminLink($this->controller_name)
                ),
                'createReturnLabel'     => $Forms->renderCreateReturnLabelForm(
                    false,
                    $this->context->link->getAdminLink($this->controller_name)
                ),
            ),
            'modal' => $this->renderModal(),
            'logo' => $this->module->logo,
            'form_tabs' => $tabs,
            'icon' => \CanadaPostPs\Icon::getIconHtml('local_shipping'),
        ));

        $this->context->smarty->assign(array(
            'content' => $this->context->smarty->fetch(sprintf(_PS_MODULE_DIR_.'%s/views/templates/hook/forms.tpl', $this->module->name)),
        ));
    }

    public function postProcess()
    {
        if (Tools::getIsset('addCustomsProduct')) {
            $this->ajaxAddProduct(Tools::getValue('id_customs_product'));
        }
        if (Tools::getIsset('getParcelBox')) {
            $this->ajaxGetParcelBox(Tools::getValue('id_box'));
        }
        if (Tools::getIsset('updateRate') || Tools::getIsset('updateBulkOrderRate')) {
            $this->ajaxUpdateRate();
        }
        if (Tools::getIsset('ajaxSaveChanges')) {
            $this->ajaxSaveChanges();
        }

        if (Tools::getIsset('ajaxCreateLabel') && Tools::getIsset('submitCreateLabel')) {
            $this->ajaxCreateLabel();
        }
        if (Tools::getIsset('ajaxCreateLabel') && Tools::getIsset('submitCreateReturnLabel')) {
            $this->ajaxCreateReturnLabel();
        }
        parent::postProcess();
    }

    public function ajaxGetParcelBox($id_box)
    {
        $boxArr = \CanadaPostPs\Box::getBox($id_box);
        $Box = new \CanadaPostPs\Box($boxArr['id_box']);
        $Box->convertDimensionsToUnit('cm');
        $Box->weight = \CanadaPostPs\Tools::tokg($Box->weight);
        die(json_encode($Box));
    }

    public function ajaxAddProduct($id_product)
    {
        if (!is_numeric($id_product)) {
            $id_product = 1;
        }
        $product      = array(
            array(
                'product_name' => sprintf('Product %s', $id_product),
                'id_order_detail'   => $id_product,
                'is_virtual' => 0,
            )
        );

        $Forms = new \CanadaPostPs\Forms();

        $input_fields = $Forms->getCustomsProductFields('customs', $product[0], false);

        $fields = array(
            'form' => array(
                'input' => $input_fields,
            ),
        );

        die($Forms->renderForm(
            $fields,
            'addProduct',
            false,
            false,
            true,
            $Forms->getCreateLabelFormFieldValues(false, $product)
        ));
    }

    public function ajaxUpdateRate()
    {
        $API = new \CanadaPostPs\API();

        if (Tools::getIsset('id_order') && Tools::getIsset('updateBulkOrderRate')) {
            $Forms = new \CanadaPostPs\Forms();
            $Order = new Order(Tools::getValue('id_order'));
            $values = $Forms->getCreateLabelFormFieldValues($Order->id, $Order->getProducts());
        } else {
            $values = Tools::getAllValues();
        }

        // Service Code
        $serviceCode = array($values['service-code']);

        // Sender
        $senderAddress = new \CanadaPostPs\Address($values['sender']);

        // Destination
        $destinationAddress = new \Address();
        $destinationAddress->postcode = $values['postal-zip-code'];
        $destinationAddress->id_country = Country::getByIso($values['country-code']);

        // Box
        $Box = new \CanadaPostPs\Box();
        $Box->length = $values['length'];
        $Box->width = $values['width'];
        $Box->height = $values['height'];

        // Weight
        $weight = \CanadaPostPs\Tools::toKg($values['weight']);

        $optionArr = array();
        foreach (\CanadaPostPs\Method::$options as $option_code => $name) {
            if (isset($values['options_'.$option_code]) &&
                !Tools::isEmpty($values['options_'.$option_code]) &&
                $values['options_'.$option_code] !== false) {
                $option = array();
                if ($option_code == 'COV') {
                    $option['COV-option-amount'] = $values['COV-option-amount'];
                }

                if ($option_code == 'COD') {
                    $option['COD-option-amount'] = $values['COD-option-amount'];
                    $option['COD-option-qualifier-1'] = $values['COD-option-qualifier-1'];
                }

                if ($option_code == 'D2PO') {
                    $option['D2PO-option-qualifier-2'] = $values['D2PO-option-qualifier-2'];
                }
                $optionArr[$option_code] = $option;
            }
        }

        // Get rate
        try {
            $Rating = $API->getRates($serviceCode, $senderAddress, $destinationAddress, $Box, $weight, $optionArr);

            $response = array();
            if ($Rating->isSuccess()) {
                /* @var $Quote \CanadaPostWs\Type\Rating\QuoteType */
                foreach ($Rating->getResponse()->getQuotes() as $Quote) {
                    $serviceMethod    = \CanadaPostPs\Method::getMethodByCode($Quote->getServiceCode());
                    if (Tools::getIsset('updateBulkOrderRate')) {
                        $price = Configuration::get($this->module->_prefix . 'TAX') ? $Quote->getPriceTaxIncl() : $Quote->getPriceTaxExcl();
                        $response['rate'] = \CanadaPostPs\Icon::getIconHtml('refresh') .\Tools::displayPrice($price);
                    } else {
                        $response['rate'] = sprintf(
                            '%s: $%s (tax incl) | $%s (tax excl) | %s Business Day(s) | Arrives %s',
                            $serviceMethod['name'],
                            \CanadaPostPs\Tools::renderHtmlTag('b', number_format($Quote->getPriceTaxIncl(), '2', '.', '')),
                            \CanadaPostPs\Tools::renderHtmlTag('b', number_format($Quote->getPriceTaxExcl(), '2', '.', '')),
                            \CanadaPostPs\Tools::renderHtmlTag('b', $Quote->getTransitTime()),
                                \CanadaPostPs\Tools::renderHtmlTag('b', $Quote->getDeliveryDate())
                        );
                    }
                    // Cache rate if this is an order
                    if (isset($values['id_order'])) {
                        $Order = new Order($values['id_order']);
                        $Cache = \CanadaPostPs\Cache::getByCartId($Order->id_cart);
                        if (!Validate::isLoadedObject($Cache)) {
                            $Cache = $API->cacheCart($Order->id_cart);
                        }
                        $Cache->addRate($Quote, Configuration::get($this->module->_prefix . 'TAX'));
                        $orderErrorArr = \CanadaPostPs\OrderError::getOrderErrors(array('id_order' => $values['id_order'], 'id_batch' => 0));
                        if (!empty($orderErrorArr)) {
                            $OrderError = new \CanadaPostPs\OrderError($orderErrorArr[0]['id_order_error']);
                            $OrderError->delete();
                        }
                    }
                    break;
                }
            } else {
                $response['error'] = !empty($Rating->getErrorMessage()) ? \CanadaPostPs\Tools::formatErrorMessage($Rating->getErrorMessage()) : \CanadaPostPs\Tools::$error_messages['RATES_NOT_RETURNED'];
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        if (isset($response['error']) && Tools::getIsset('updateBulkOrderRate')) {
            if ($orderErrorArr = \CanadaPostPs\OrderError::getOrderErrorByOrderId($values['id_order'])) {
                $OrderError = new \CanadaPostPs\OrderError($orderErrorArr['id_order_error']);
            } else {
                $OrderError = new \CanadaPostPs\OrderError();
                $OrderError->id_order = $values['id_order'];
            }
            $OrderError->errorMessage = $response['error'];
            $OrderError->save();
            $response['error'] = \CanadaPostPs\Icon::getIconHtml('exclamation-circle');
        }

        die(json_encode($response));
    }


    public function ajaxCreateLabel()
    {
        $API = new \CanadaPostPs\API();
        $Forms = new \CanadaPostPs\Forms();
        if (Tools::getIsset('id_order')) {
            $Order = new \Order(Tools::getValue('id_order'));
            $products = $Order->getProducts();
        } else {
            $products = array();
        }
        $values = $Forms->getCreateLabelFormFieldValues(Tools::getValue('id_order', false), $products);
        $API->processSubmitCreateLabel($values);
        die();
    }

    public function ajaxCreateReturnLabel()
    {
        $API = new \CanadaPostPs\API();
        $API->processSubmitCreateReturnLabel(false, Tools::getValue('id_order', false));
        die();
    }

    public function ajaxSaveChanges()
    {
        $API = new \CanadaPostPs\API();
        $API->processSubmitSaveChanges();
        die();
    }
}
