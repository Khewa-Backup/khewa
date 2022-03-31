<?php
class Product extends ProductCore
{
    /*
    * custom sorting for combinations
    * date: 2020-06-21
    */
    
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
        // echo('<pre>');print_r([$sorted_ids, $resume_ids]);echo('</pre>');
        // exit();
    }
}
