<?php

/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

class BT_XmlCombination extends BT_BaseProductXml
{
    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        parent::__construct($aParams);
    }

    /**
     * load products combination
     *
     * @param int $iProductId
     * @param bool $bExcludedProduct
     * @return array
     */
    public function hasCombination($iProductId, $bExcludedProduct = false)
    {
        return BT_GmcProModuleDao::getProductCombination($this->aParams['iShopId'], $iProductId, $bExcludedProduct);
    }

    /**
     * build product XML tags
     *
     * @return mixed
     */
    public function buildDetailProductXml()
    {
        // set the product ID
        $this->data->step->id = $this->data->p->id . 'v' . $this->data->c['id_product_attribute'];
        $this->data->step->id_no_combo = $this->data->p->id;

        // format the product URL  with attribute combination
        if (!empty($this->data->step->url)) {
            $this->data->step->url = BT_GmcProModuleDao::getProductComboLink($this->data->step->url, $this->data->c['id_product_attribute'], $this->aParams['iLangId'], $this->aParams['iShopId'], $this->data->p->id, (int) $this->data->currencyId);
        }

        // get weight
        $this->data->step->weight = (float) $this->data->p->weight + (float) $this->data->c['weight'];

        // handle different prices and shipping fees
        $this->data->step->price_default_currency_no_tax = Tools::convertPrice(Product::getPriceStatic((int) $this->data->p->id, false, (int) $this->data->c['id_product_attribute']), $this->data->currency, false);

        // Exclude based on min price
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_MIN_PRICE'])
            && ((float) $this->data->step->price_default_currency_no_tax < (float) GMerchantCenterPro::$conf['GMCP_MIN_PRICE'])
        ) {
            BT_GmcProReporting::create()->set('_no_export_min_price', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // Exclude based on max weight
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_MAX_WEIGHT'])
            && ((float) $this->data->step->weight > (float) GMerchantCenterPro::$conf['GMCP_MAX_WEIGHT'])
        ) {
            BT_GmcProReporting::create()->set('_no_export_max_weight', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // handle both price and discounted price
        if (isset($this->aParams['bUseTax'])) {
            $bUseTax = !empty($this->aParams['bUseTax']) ? true : false;
        } else {
            $bUseTax = true;
        }

        $this->data->step->price_raw = Product::getPriceStatic((int) $this->data->p->id, $bUseTax, (int) $this->data->c['id_product_attribute']);
        $this->data->step->price_raw_no_discount = Product::getPriceStatic((int) $this->data->p->id, $bUseTax, (int) $this->data->c['id_product_attribute'], 6, null, false, false);
        $this->data->step->price = number_format(BT_GmcProModuleTools::round($this->data->step->price_raw), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        $this->data->step->price_no_discount = number_format(BT_GmcProModuleTools::round($this->data->step->price_raw_no_discount), 2, '.', '') . ' ' . $this->data->currency->iso_code;

        // Cost price
        if (!empty((int) $this->data->c['wholesale_price'])) {
            $this->data->step->cost_price = number_format(BT_GmcProModuleTools::round($this->data->c['wholesale_price']), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        } elseif (!empty((int) $this->data->p->wholesale_price)) {
            $this->data->step->cost_price = number_format(BT_GmcProModuleTools::round($this->data->p->wholesale_price), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        }

        // shipping fees
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_SHIPPING_USE'])
            && !isset($this->aParams['sFreeShipping'][$this->data->p->id])
        ) {
            $fPrice = number_format((float) $this->getProductShippingFees((float) BT_GmcProModuleTools::round($this->data->step->price_raw)), 2, '.', '');
        } else {
            if (in_array($this->data->c['id_product_attribute'], $this->aParams['sFreeShipping'][$this->data->p->id])) {
                $fPrice = number_format((float) 0, 2, '.', '');
            } else {
                $fPrice = number_format((float) $this->getProductShippingFees((float) BT_GmcProModuleTools::round($this->data->step->price_raw)), 2, '.', '');
            }
        }

        $this->data->step->shipping_fees = $fPrice . ' ' . $this->data->currency->iso_code;

        // get images
        $this->data->step->images = $this->getImages($this->data->p, $this->data->c['id_product_attribute']);

        // quantity
        // Do not export if the quantity is 0 for the combination and export out of stock setting is not On
        if (
            (int) $this->data->c['combo_quantity'] <= 0
            && (int) GMerchantCenterPro::$conf['GMCP_EXPORT_OOS'] == 0
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
            return false;
        }
        $this->data->step->quantity = (int) $this->data->c['combo_quantity'];

        //Manage GTIN code
        $this->data->step->gtin = BT_GmcProModuleTools::getGtin(GMerchantCenterPro::$conf['GMCP_GTIN_PREF'], $this->data->c);

        // Exclude without EAN
        if (
            GMerchantCenterPro::$conf['GMCP_EXC_NO_EAN']
            && empty($this->data->step->gtin)
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_ean_upc', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // supplier reference
        $this->data->step->mpn = $this->getSupplierReference(
            $this->data->p->id,
            $this->data->p->id_supplier,
            $this->data->p->supplier_reference,
            $this->data->p->reference,
            (int) $this->data->c['id_product_attribute'],
            $this->data->c['supplier_reference'],
            $this->data->c['reference']
        );

        // exclude if mpn is empty
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_EXC_NO_MREF'])
            && !GMerchantCenterPro::$conf['GMCP_INC_ID_EXISTS']
            && empty($this->data->step->mpn)
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_supplier_ref', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // Use case for the specific price
        if (!empty($this->data->p->specificPrice)) {

            // Use case for specific price on all combination on the from
            if (!empty($this->data->p->specificPrice['from'])) {
                $sFrom = $this->data->p->specificPrice['from'];
            } else {
                if (!empty($this->data->c['from'])) {
                    $sFrom = $this->data->c['from'];
                }
            }

            // Use case for specific price on all combination on the from
            if (!empty($this->data->p->specificPrice['to'])) {
                $sTo = $this->data->p->specificPrice['to'];
            } else {
                if (!empty($this->data->c['to'])) {
                    $sTo = $this->data->c['to'];
                }
            }
        } else {
            if (!empty($this->data->c['from'])) {
                $sFrom = $this->data->c['from'];
            }
            if (!empty($this->data->c['to'])) {
                $sTo = $this->data->c['to'];
            }
        }

        //handle the specific price feature
        $this->data->step->specificPriceFrom = !empty($sFrom) ? $sFrom : '0000-00-00 00:00:00';

        $this->data->step->specificPriceTo = !empty($sTo) ? $sTo : '0000-00-00 00:00:00';

        $this->data->step->visibility = $this->data->p->visibility;

        return true;
    }

    /**
     * format the product name
     *
     * @param int $iAdvancedProdName
     * @param int $iAdvancedProdTitle
     * @param string $sProdName
     * @param string $sCatName
     * @param string $sManufacturerName
     * @param int $iLength
     * @param int $iProdAttrId
     * @return string
     */
    public function formatProductName($iAdvancedProdName, $iAdvancedProdTitle, $sProdName, $sCatName, $sManufacturerName, $iLength, $iProdAttrId = null, $iLangId = null, $sPrefix = null, $sSuffix = null)
    {
        // get the combination attributes to format the product name
        $aCombinationAttr = BT_GmcProModuleDao::getProductComboAttributes(
            $iProdAttrId,
            $this->aParams['iLangId'],
            $this->aParams['iShopId']
        );

        if (!empty($aCombinationAttr)) {
            $sExtraName = '';
            foreach ($aCombinationAttr as $c) {
                $sExtraName .= ' ' . Tools::stripslashes($c['name']);
            }
            $sProdName .= $sExtraName;
        }
        // encode
        $sProdName = BT_GmcProModuleTools::truncateProductTitle($iAdvancedProdName, $sProdName, $sCatName, $sManufacturerName, $iLength, $this->aParams['iLangId'], $sPrefix, $sSuffix);

        $sProdName = BT_GmcProModuleTools::formatProductTitle($sProdName, $iAdvancedProdTitle);

        return $sProdName;
    }


    /**
     * get images of one product or one combination
     *
     * @param obj $oProduct
     * @param int $iProdAttributeId
     * @return array
     */
    public function getImages(Product $oProduct, $iProdAttributeId = null)
    {
        // set vars
        $aResultImages = array();
        $iCounter = 1;

        // get images of combination
        $aAttributeImages = $oProduct->getCombinationImages(GMerchantCenterPro::$iCurrentLang);

        if (
            !empty($aAttributeImages)
            && is_array($aAttributeImages)
            && isset($aAttributeImages[$iProdAttributeId])
        ) {
            $aImage = array('id_image' => $aAttributeImages[$iProdAttributeId][0]['id_image']);
            unset($aAttributeImages[$iProdAttributeId][0]);
        } else {
            $aImage = Product::getCover($oProduct->id);
        }

        // Additional images
        if (!empty($aAttributeImages) && is_array($aAttributeImages) && isset($aAttributeImages[$iProdAttributeId])) {
            foreach ($aAttributeImages[$iProdAttributeId] as $aImg) {
                if ($iCounter <= _GMCP_IMG_LIMIT) {
                    $aResultImages[] = array('id_image' => $aImg['id_image']);
                    $iCounter++;
                }
            }
        }

        return array('image' => $aImage, 'others' => $aResultImages);
    }

    /**
     * get supplier reference
     *
     * @param int $iProdId
     * @param int $iSupplierId
     * @param string $sSupplierRef
     * @param string $sProductRef
     * @param int $iProdAttributeId
     * @param string $sCombiSupplierRef
     * @param string $sCombiRef
     * @return string
     */
    public function getSupplierReference($iProdId, $iSupplierId, $sSupplierRef = null, $sProductRef = null, $iProdAttributeId = 0, $sCombiSupplierRef = null, $sCombiRef = null)
    {
        // set  vars
        $sReturnRef = '';

        // detect the MPN type
        $sReturnRef = BT_GmcProModuleDao::getProductSupplierReference($iProdId, $iSupplierId, $iProdAttributeId);

        if (
            empty($sReturnRef)
            && !empty($sCombiRef)
        ) {
            $sReturnRef = $sCombiRef;
        }

        return $sReturnRef;
    }
}
