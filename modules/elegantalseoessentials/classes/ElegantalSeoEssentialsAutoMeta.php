<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage rules for auto meta filling
 */
class ElegantalSeoEssentialsAutoMeta extends ElegantalSeoEssentialsObjectModel
{

    public $tableName = 'elegantalseoessentials_auto_meta';
    public static $definition = array(
        'table' => 'elegantalseoessentials_auto_meta',
        'primary' => 'id_elegantalseoessentials_auto_meta',
        'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255, 'required' => true),
            'category_ids' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'url_pattern' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true),
            'title_pattern' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true),
            'description_pattern' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true),
            'keywords_pattern' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true),
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

    public function applyRuleOnProduct($id_product, $lang_id = null, $commit_save = true)
    {
        $settings = $this->getModuleSettings();
        $settings['excluded_product_ids'] = preg_replace("/[^0-9,]/", "", $settings['excluded_product_ids']);
        $excluded_product_ids = explode(',', $settings['excluded_product_ids']);
        if (is_array($excluded_product_ids) && in_array($id_product, $excluded_product_ids)) {
            return false;
        }

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
                'url' => $this->url_pattern[$id_lang],
                'title' => $this->title_pattern[$id_lang],
                'description' => $this->description_pattern[$id_lang],
                'keywords' => $this->keywords_pattern[$id_lang],
            );

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

            // Validate the same way as Product.php class
            $lang_label = (count($languages) > 1) ? ' [' . Tools::strtoupper($language['iso_code']) . ']' : "";

            $sql = "";

            $patterns['url'] = trim($patterns['url']);
            if ($patterns['url'] == "" && empty($product->link_rewrite[$id_lang])) {
                $patterns['url'] = Tools::substr($product->name[$id_lang], 0, 128);
            }
            if ($patterns['url'] != "") {
                $patterns['url'] = Tools::link_rewrite($patterns['url']);
                if (!Validate::isLinkRewrite($patterns['url'])) {
                    throw new Exception('Pattern for friendly URL is invalid' . $lang_label . ': ' . $patterns['url']);
                }
                $sql .= "`link_rewrite` = '" . pSQL($patterns['url']) . "'";
            }

            if ($patterns['title'] != "") {
                if ($patterns['title'] == " ") {
                    $patterns['title'] = "";
                } else {
                    $meta_title_length = $settings['meta_title_length'];
                    if ($meta_title_length < 1) {
                        $meta_title_length = 70;
                    } elseif ($meta_title_length > 128) {
                        $meta_title_length = 128;
                    }
                    $patterns['title'] = Tools::substr($patterns['title'], 0, $meta_title_length);
                    if (!Validate::isGenericName($patterns['title'])) {
                        throw new Exception('Pattern for title of product page is invalid' . $lang_label . ': ' . $patterns['title']);
                    }
                }
                $sql .= $sql ? "," : "";
                $sql .= "`meta_title` = '" . pSQL($patterns['title']) . "'";
            }

            if ($patterns['description'] != "") {
                if ($patterns['description'] == " ") {
                    $patterns['description'] = "";
                } else {
                    $meta_description_length = $settings['meta_description_length'];
                    if ($meta_description_length < 1) {
                        $meta_description_length = 160;
                    } elseif ($meta_description_length > 255) {
                        $meta_description_length = 255;
                    }
                    $patterns['description'] = Tools::substr($patterns['description'], 0, $meta_description_length);
                    if (!Validate::isGenericName($patterns['description'])) {
                        throw new Exception('Pattern for meta description is invalid' . $lang_label . ': ' . $patterns['description']);
                    }
                }
                $sql .= $sql ? "," : "";
                $sql .= "`meta_description` = '" . pSQL($patterns['description']) . "'";
            }

            if ($patterns['keywords'] != "") {
                if ($patterns['keywords'] == " ") {
                    $patterns['keywords'] = "";
                } else {
                    $patterns['keywords'] = Tools::substr($patterns['keywords'], 0, 255);
                    if (!Validate::isGenericName($patterns['keywords'])) {
                        throw new Exception('Pattern for meta keywords is invalid' . $lang_label . ': ' . $patterns['keywords']);
                    }
                }
                $sql .= $sql ? "," : "";
                $sql .= "`meta_keywords` = '" . pSQL($patterns['keywords']) . "'";
            }

            if ($commit_save && $sql) {
                $sql = "UPDATE " . _DB_PREFIX_ . "product_lang SET " . $sql . " 
                    WHERE id_product = " . (int) $product->id . " AND id_lang = " . (int) $id_lang . " AND id_shop = " . (int) $id_shop;
                if (Db::getInstance()->execute($sql) == false) {
                    throw new Exception(Db::getInstance()->getMsgError());
                }
            }
        }

        return true;
    }

    protected function replaceShortcode($shortcode, $replace, &$pattern)
    {
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
