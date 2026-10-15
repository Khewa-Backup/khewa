<?php
class Product extends ProductCore
{
    
    
    public function getAttributesResume($id_lang, $attribute_value_separator = ' - ', $attribute_separator = ', ')
    {
        $resume = parent::getAttributesResume($id_lang, $attribute_value_separator, $attribute_separator);
        $sorted_ids = array_column(Db::getInstance()->executeS('
            SELECT * FROM '._DB_PREFIX_.'product_attribute pa
            '.Shop::addSqlAssociation('product_attribute', 'pa').'
            LEFT JOIN '._DB_PREFIX_.'product_attribute_combination pac
                ON pac.id_product_attribute = pa.id_product_attribute
            LEFT JOIN '._DB_PREFIX_.'attribute a
                ON a.id_attribute = pac.id_attribute
            WHERE pa.id_product = '.(int)$this->id.'
            GROUP BY pa.id_product_attribute
            ORDER BY a.position ASC, pac.id_attribute ASC, pa.id_product_attribute ASC
        '), 'id_product_attribute');

        $resume_ids = array_column($resume, 'id_product_attribute');
        if ($resume_ids != $sorted_ids && count($resume_ids) == count($sorted_ids)
            && !array_diff($resume_ids, $sorted_ids)) {
            $combination_positions = array_flip($sorted_ids);
            $sorted_resume = array();
            foreach ($resume as $key => $r) {
                if (isset($combination_positions[$r['id_product_attribute']])) {
                    $position = $combination_positions[$r['id_product_attribute']];
                    $sorted_resume[$position] = $r;
                    unset($resume[$key]);
                }
            }
            ksort($sorted_resume);
            $sorted_resume = array_merge($sorted_resume, $resume); // just in case if something remained
            $resume = $sorted_resume;
        }
        return $resume;
    }
    /*
    * module: amazzingfilter
    * date: 2023-01-14 11:22:26
    * version: 3.2.2
    */
    public static function getProductsProperties($id_lang, $query_result)
    {
        if (!empty(Context::getContext()->properties_not_required)) {
            return $query_result;
        } else {
            return parent::getProductsProperties($id_lang, $query_result);
        }
    }
}
