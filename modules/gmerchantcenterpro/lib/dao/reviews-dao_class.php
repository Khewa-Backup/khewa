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

class BT_GmcProReviewsDao
{
    /**
     * get product comment reviews
     *
     * @return array
     */
    public static function getProductCommentReviews()
    {
        return Db::getInstance()->ExecuteS('SELECT * from `' . _DB_PREFIX_ . 'product_comment`');
    }

    /**
     * get product reviews from SPR4
     *
     * @param int $iLangId
     * 
     * @return array
     */
    public static function getGsrReviews($iLangId)
    {
        $sQuery = 'SELECT * from `' . _DB_PREFIX_ . 'gsr_rating` rt' . ' INNER JOIN `' . _DB_PREFIX_ . 'gsr_review` rw ON (rt.`RTG_ID` = rw.`RTG_ID`) WHERE RTG_SHOP_ID = ' . (int)GMerchantCenterPro::$iShopId . ' AND RVW_LANG_ID=' . $iLangId;

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * get reviews from spr5
     *
     * @param int $id_lang
     * 
     * @return array
     */
    public static function getSprReviews($id_lang)
    {
        $query = new \DbQuery();

        $query->select('*');
        $query->from('bt_spr_products_reviews', 'spr');
        $query->where('spr.`id_lang` = ' . (int)$id_lang);
        $query->where('spr.`id_shop` = ' . (int)\Context::getContext()->shop->id);

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}
