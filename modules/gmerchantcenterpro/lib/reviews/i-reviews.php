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

interface BT_IReviews
{
    /**
     * get the reviews
     * @params int id of the lang
     * @return array of reviews
     */
    public function getReviews($iLangId);


    /**
     * build a generic review tabs to be compatible with all reviews system
     *
     * @params array of reviews
     * @param int $iLangId
     * @return generic array of reviews
     */
    public function buildGenericReviewsArray(array $aReviews, $iLangId);
}
