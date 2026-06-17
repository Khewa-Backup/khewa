<?php

/**
 * Google Merchant Center Pro
 *
 * @author    businesstech.fr <modules@businesstech.fr> - https://www.businesstech.fr/
 * @copyright Business Tech - https://www.businesstech.fr/
 * @license   see file: LICENSE.txt
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

class BT_GsnippetsreviewsReviews implements BT_IReviews
{
    /**
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
    }


    /**
     * get the reviews from the ctrl object
     *
     * @param int $iLangId
     * @return array of reviews
     */
    public function getReviews($iLangId)
    {
        $review_spr4 = BT_GmcProReviewsDao::getGsrReviews($iLangId);
        $review_spr5 = BT_GmcProReviewsDao::getSprReviews($iLangId);

        if (!empty($review_spr4)) {
            return $this->buildGenericReviewsArray($review_spr4, $iLangId);
        }

        if (!empty($review_spr5)) {
            return $this->buildGenericReviewsArraySpr5($review_spr5, $iLangId);
        }
    }


    /**
     * get the generic array to manipulate it for the data feed
     *
     * @param int $iLangId
     * @param array $aReviews
     * @return array
     */
    public function buildGenericReviewsArray(array $aReviews, $iLangId)
    {
        $aGenericArray = array();

        $aDataForbidden = unserialize(GMerchantCenterPro::$conf['GMCP_FORBIDDEN_WORDS']);

        foreach ($aReviews as $sKey => $aReview) {
            // use case - check if there is a comment related to this rating and
            if (
                !empty($aReview['RVW_DATA']
                    && is_string($aReview['RVW_DATA']))
                && !empty($aReview['RTG_PROD_ID'])
                && !empty($aReview['RTG_CUST_ID'])
                && !empty($aReview['RVW_STATUS'])
            ) {
                // Init the product object
                $oProduct = new Product($aReview['RTG_PROD_ID'], $iLangId);

                // check if the product is still valid
                if (!empty($oProduct->active) && !empty($oProduct->id)) {
                    // use case - some merchants had triple double quotes into their serialized content, and we had to to this replace below
                    $aReview['RVW_DATA'] = str_replace('"""', '"', $aReview['RVW_DATA']);
                    $aComment = @unserialize($aReview['RVW_DATA']);

                    // Handle the customer information
                    $oCustomer = new Customer((int)$aReview['RTG_CUST_ID']);

                    // Build the end of the array with simple data
                    $aGenericArray[$sKey]['sCustomerName'] = $oCustomer->firstname . ' ' . ucfirst(substr($oCustomer->lastname, 0, 1)) . '.';
                    $aGenericArray[$sKey]['sDate'] = $aReview['RTG_DATE_ADD'];

                    foreach ($aDataForbidden as $sForbidden) {
                        $aComment['sComment'] = str_replace($sForbidden, '', $aComment['sComment']);
                    }

                    $aGenericArray[$sKey]['sReview'] = $aComment['sComment'];
                    $aGenericArray[$sKey]['sTitle'] = $aComment['sTitle'];
                    $aGenericArray[$sKey]['sReviewUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['RTG_PROD_ID'], (int)$aReview['RTG_LANG_ID']);
                    $aGenericArray[$sKey]['sRating'] = $aReview['RTG_NOTE'];
                    $aGenericArray[$sKey]['sProductUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['RTG_PROD_ID'], (int)$aReview['RTG_LANG_ID']);
                    $aGenericArray[$sKey]['iProductId'] = $oProduct->id;
                    $aGenericArray[$sKey]['sProductName'] = $oProduct->name[$iLangId];

                    // USE case for the GTIN code // Same logic as the product data feed.
                    $sGtin = BT_GmcProModuleTools::getGtin(GMerchantCenterPro::$conf['GMCP_GTIN_PREF'], (array)$oProduct);

                    if (!empty($sGtin)) {
                        $aGenericArray[$sKey]['sGtin'] = $sGtin;
                    }

                    // USE case for the MPN
                    if (!empty($oProduct->reference)) {
                        $aGenericArray[$sKey]['sMpn'] = $oProduct->reference;
                    }

                    // USE case for the SKU
                    if (!empty($oProduct->supplier_reference)) {
                        $aGenericArray[$sKey]['sSku'] = $oProduct->supplier_reference;
                    }

                    // USE case for the brand
                    if (!empty($oProduct->manufacturer_name)) {
                        $aGenericArray[$sKey]['sManufacturer'] = $oProduct->manufacturer_name;
                    }
                }
            }
        }

        return $aGenericArray;
    }

    /**
     * get the generic array to manipulate it for the data feed
     *
     * @param int $iLangId
     * @param array $aReviews
     * @return array
     */
    public function buildGenericReviewsArraySpr5(array $aReviews, $iLangId)
    {
        $aGenericArray = array();

        $aDataForbidden = unserialize(GMerchantCenterPro::$conf['GMCP_FORBIDDEN_WORDS']);

        foreach ($aReviews as $sKey => $aReview) {
            // use case - check if there is a comment related to this rating and
            if (
                !empty($aReview['title_review']) && !empty($aReview['text_review']) && !empty($aReview['rating_value']) && !empty($aReview['id_customer']) && !empty($aReview['id_product'])
            ) {

                try {
                    // Init the product object
                    $oProduct = new Product($aReview['id_product'], $iLangId);

                    // check if the product is still valid
                    if (!empty($oProduct->active) && !empty($oProduct->id)) {

                        // Handle the customer information
                        $oCustomer = new Customer((int)$aReview['id_customer']);

                        // Build the end of the array with simple data
                        $aGenericArray[$sKey]['sCustomerName'] = $oCustomer->firstname . ' ' . ucfirst(substr($oCustomer->lastname, 0, 1)) . '.';
                        $aGenericArray[$sKey]['sDate'] = $aReview['date_add'];

                        foreach ($aDataForbidden as $sForbidden) {
                            $aReview['text_review'] = str_replace($sForbidden, '', $aReview['text_review']);
                        }

                        $aGenericArray[$sKey]['sReview'] = $aReview['text_review'];
                        $aGenericArray[$sKey]['sTitle'] = $aReview['title_review'];
                        $aGenericArray[$sKey]['sReviewUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['id_product'], (int)$aReview['id_lang']);
                        $aGenericArray[$sKey]['sRating'] = $aReview['rating_value'];
                        $aGenericArray[$sKey]['sProductUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['id_product'], (int)$aReview['id_lang']);
                        $aGenericArray[$sKey]['iProductId'] = $oProduct->id;
                        $aGenericArray[$sKey]['sProductName'] = $oProduct->name[$iLangId];

                        // USE case for the GTIN code // Same logic as the product data feed.
                        $sGtin = BT_GmcProModuleTools::getGtin(GMerchantCenterPro::$conf['GMCP_GTIN_PREF'], (array)$oProduct);

                        if (!empty($sGtin)) {
                            $aGenericArray[$sKey]['sGtin'] = $sGtin;
                        }

                        // USE case for the MPN
                        if (!empty($oProduct->reference)) {
                            $aGenericArray[$sKey]['sMpn'] = $oProduct->reference;
                        }

                        // USE case for the SKU
                        if (!empty($oProduct->supplier_reference)) {
                            $aGenericArray[$sKey]['sSku'] = $oProduct->supplier_reference;
                        }

                        // USE case for the brand
                        if (!empty($oProduct->manufacturer_name)) {
                            $aGenericArray[$sKey]['sManufacturer'] = $oProduct->manufacturer_name;
                        }
                    }
                } catch (Exception $e) {
                    echo ($e->getMessage());
                }
            }
        }

        return $aGenericArray;
    }
}
