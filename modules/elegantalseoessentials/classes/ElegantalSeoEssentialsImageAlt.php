<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage rules for image legends
 */
class ElegantalSeoEssentialsImageAlt extends ElegantalSeoEssentialsObjectModel
{

    public $tableName = 'elegantalseoessentials_image_alt';
    public static $definition = array(
        'table' => 'elegantalseoessentials_image_alt',
        'primary' => 'id_elegantalseoessentials_image_alt',
        'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255, 'required' => true),
            'category_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pattern' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true, 'required' => true),
            'is_active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
            'created_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'applied_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * List of short-codes
     * @var array
     */
    public static $shortcodes = array(
        'product' => array(
            '{product_name}',
            '{price_with_reduction_exc_tax}',
            '{default_category}',
            '{price_without_reduction_exc_tax}',
            '{product_categories}',
            '{price_with_reduction_inc_tax}',
            '{short_description}',
            '{price_without_reduction_inc_tax}',
            '{long_description}',
            '{product_attributes}',
            '{product_reference}',
            '{product_features}',
            '{manufacturer_name}',
            '{product_tags}',
            '{supplier_name}',
            '{product_condition}',
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (!$id || empty($this->created_at) || $this->created_at == '0000-00-00 00:00:00') {
            $this->created_at = date('Y-m-d H:i:s');
        }
        if ($this->applied_at == '0000-00-00 00:00:00') {
            $this->applied_at = null;
        }
        if (method_exists('Shop', 'addTableAssociation')) {
            Shop::addTableAssociation($this->tableName, array('type' => 'shop'));
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function applyRuleOnProduct($id_product, $lang_id = null)
    {
        $product = new Product($id_product, true);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }
        $id_shop = (int) Context::getContext()->shop->id;
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];

            // If language is given, apply only that language
            if ($lang_id && $lang_id != $id_lang) {
                continue;
            }

            $patterns = array(
                'legend' => trim($this->pattern[$id_lang]),
            );

            if (empty($patterns['legend'])) {
                continue;
            }

            // Replace each shortcode with associated value
            foreach (self::$shortcodes as $shortcode_group) {
                foreach ($shortcode_group as $shortcode) {
                    foreach ($patterns as &$pattern) {
                        // Check if pattern has shortcode. Compare only part of shortcode till length.
                        if (strpos($pattern, rtrim($shortcode, '}')) === false) {
                            continue;
                        }
                        switch ($shortcode) {
                            case '{product_name}':
                                $this->replaceShortcode($shortcode, $product->name[$id_lang], $pattern);
                                break;
                            case '{default_category}':
                                $category = new Category($product->id_category_default, $id_lang);
                                $this->replaceShortcode($shortcode, $category->name, $pattern);
                                break;
                            case '{product_categories}':
                                $categories = '';
                                $category_ids = $product->getCategories();
                                foreach ($category_ids as $id_cat) {
                                    $category = new Category($id_cat, $id_lang);
                                    $categories .= $categories ? ', ' : '';
                                    $categories .= $category->name;
                                }
                                $this->replaceShortcode($shortcode, $categories, $pattern);
                                break;
                            case '{short_description}':
                                $this->replaceShortcode($shortcode, strip_tags($product->description_short[$id_lang]), $pattern);
                                break;
                            case '{long_description}':
                                $this->replaceShortcode($shortcode, strip_tags($product->description[$id_lang]), $pattern);
                                break;
                            case '{product_reference}':
                                $this->replaceShortcode($shortcode, $product->reference, $pattern);
                                break;
                            case '{manufacturer_name}':
                                $this->replaceShortcode($shortcode, $product->manufacturer_name, $pattern);
                                break;
                            case '{supplier_name}':
                                $this->replaceShortcode($shortcode, $product->supplier_name, $pattern);
                                break;
                            case '{product_condition}':
                                $this->replaceShortcode($shortcode, $product->condition, $pattern);
                                break;
                            case '{product_features}':
                                $product_features = '';
                                $features = $product->getFeatures();
                                if ($features && is_array($features)) {
                                    foreach ($features as $feature) {
                                        $featureObj = new Feature($feature['id_feature'], $id_lang);
                                        if (Validate::isLoadedObject($featureObj)) {
                                            $featureValue = new FeatureValue($feature['id_feature_value'], $id_lang);
                                            if (Validate::isLoadedObject($featureValue)) {
                                                $product_features .= $product_features ? ', ' : '';
                                                $product_features .= $featureObj->name . ': ' . $featureValue->value;
                                            }
                                        }
                                    }
                                }
                                $this->replaceShortcode($shortcode, $product_features, $pattern);
                                break;
                            case '{product_attributes}':
                                $product_attributes = '';
                                $attributes = $product->getAttributeCombinations($id_lang);
                                foreach ($attributes as $attribute) {
                                    $product_attributes .= $product_attributes ? ', ' : '';
                                    $product_attributes .= $attribute['group_name'] . ': ' . $attribute['attribute_name'];
                                }
                                $this->replaceShortcode($shortcode, $product_attributes, $pattern);
                                break;
                            case '{product_tags}':
                                $product_tags = explode(', ', $product->getTags($id_lang));
                                // Remove space within each tag
                                $product_tags = str_replace(array(' ', ',', '.', '!', ':', ';', '`', '?', '&', '*', '#', '+', '"', "'", '-', '_', '(', ')', '{', '}', '[', ']', '\\', '/', '|', '=', '…'), '', $product_tags);
                                $product_tags = implode(', ', $product_tags);
                                $this->replaceShortcode($shortcode, $product_tags, $pattern);
                                break;
                            case '{price_with_reduction_exc_tax}':
                                $price_with_reduction_exc_tax = (float) Product::getPriceStatic($product->id, false, null, 2, null, false, true);
                                $price_with_reduction_exc_tax = Tools::displayPrice($price_with_reduction_exc_tax);
                                $this->replaceShortcode($shortcode, $price_with_reduction_exc_tax, $pattern);
                                break;
                            case '{price_without_reduction_exc_tax}':
                                $price_without_reduction_exc_tax = (float) Product::getPriceStatic($product->id, false, null, 2, null, false, false);
                                $price_without_reduction_exc_tax = Tools::displayPrice($price_without_reduction_exc_tax);
                                $this->replaceShortcode($shortcode, $price_without_reduction_exc_tax, $pattern);
                                break;
                            case '{price_with_reduction_inc_tax}':
                                $price_with_reduction_inc_tax = (float) Product::getPriceStatic($product->id, true, null, 2, null, false, true);
                                $price_with_reduction_inc_tax = Tools::displayPrice($price_with_reduction_inc_tax);
                                $this->replaceShortcode($shortcode, $price_with_reduction_inc_tax, $pattern);
                                break;
                            case '{price_without_reduction_inc_tax}':
                                $price_without_reduction_inc_tax = (float) Product::getPriceStatic($product->id, true, null, 2, null, false, false);
                                $price_without_reduction_inc_tax = Tools::displayPrice($price_without_reduction_inc_tax);
                                $this->replaceShortcode($shortcode, $price_without_reduction_inc_tax, $pattern);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }

            if (empty($patterns['legend'])) {
                continue;
            }

            $patterns['legend'] = Tools::substr($patterns['legend'], 0, 255);

            $sql = "UPDATE `" . _DB_PREFIX_ . "image_lang` AS il 
                INNER JOIN `" . _DB_PREFIX_ . "image` AS i ON il.`id_image` = i.`id_image` 
                INNER JOIN `" . _DB_PREFIX_ . "image_shop` AS sh ON il.`id_image` = sh.`id_image` AND i.`id_product` = sh.`id_product`  
                SET il.`legend` = '" . pSQL($patterns['legend']) . "' 
                WHERE i.`id_product` = " . (int) $product->id . " AND il.`id_lang` = " . (int) $id_lang . " AND sh.`id_shop` = " . (int) $id_shop;
            if (Db::getInstance()->execute($sql) == false) {
                throw new Exception(Db::getInstance()->getMsgError());
            }

            /*
              $images = Image::getImages($id_lang, $product->id);
              foreach ($images as $image) {
              $imageObj = new Image($image['id_image'], $id_lang);
              if (Validate::isLoadedObject($imageObj)) {
              $imageObj->legend = $patterns['legend'];
              $imageObj->save();
              }
              }
             */
        }

        return true;
    }

    protected function replaceShortcode($shortcode, $replace, &$pattern)
    {
        $replace = str_replace('"', '', $replace);
        $matches = null;
        $regex = "/{" . str_replace(array('{', '}'), '', $shortcode) . "(\|(\d+))*}/";
        preg_match_all($regex, $pattern, $matches);
        if ($matches && isset($matches[0])) {
            foreach ($matches[0] as $key => $match) {
                $length = (int) $matches[2][$key];
                if ($length > 0 && Tools::strlen($replace) > $length) {
                    $pattern = str_replace($match, Tools::substr($replace, 0, $length), $pattern);
                } else {
                    $pattern = str_replace($match, $replace, $pattern);
                }
            }
        }
    }

    public function getProductIds($offset = null, $limit = null)
    {
        $product_ids = array();
        $id_shop = (int) Context::getContext()->shop->id;
        $category_ids = ElegantalSeoEssentialsTools::unserialize($this->category_ids);

        $sql = "SELECT p.`id_product` 
            FROM `" . _DB_PREFIX_ . "product` p 
            INNER JOIN `" . _DB_PREFIX_ . "product_shop` sh ON (sh.`id_product` = p.`id_product`) 
            INNER JOIN `" . _DB_PREFIX_ . "category_product` c ON (c.`id_product` = p.`id_product`) 
            WHERE sh.`id_shop` = " . (int) $id_shop . " ";
        if ($category_ids && is_array($category_ids)) {
            $sql .= "AND c.`id_category` IN (" . implode(', ', array_map('intval', $category_ids)) . ") ";
        }
        $sql .= "GROUP BY p.`id_product` ORDER BY p.`id_product` ";
        if (!is_null($limit) && !is_null($offset)) {
            $sql .= "LIMIT " . (int) $offset . ", " . (int) $limit;
        }

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $product_ids[] = $row['id_product'];
        }

        return $product_ids;
    }
}
