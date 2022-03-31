<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2022, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is an object model class used to manage html blocks
 */
class ElegantalSeoEssentialsHtml extends ElegantalSeoEssentialsObjectModel
{

    public $tableName = 'elegantalseoessentials_html';
    public static $definition = array(
        'table' => 'elegantalseoessentials_html',
        'primary' => 'id_elegantalseoessentials_html',
        'multilang' => true,
        'multishop' => true,
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isString'),
            'html' => array('type' => self::TYPE_HTML, 'required' => true, 'validate' => 'isString', 'lang' => true),
            'pages' => array('type' => self::TYPE_STRING, 'required' => true),
            'hooks' => array('type' => self::TYPE_STRING, 'required' => true),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'is_active' => array('type' => self::TYPE_BOOL, 'validate' => 'isUnsignedInt'),
        ),
    );

    /**
     * List of short-codes
     * @var array
     */
    public static $shortcodes = array(
        'general' => array(
            '{shop_name}',
            '{shop_link}',
        ),
        'product' => array(
            '{product_id}',
            '{product_attributes}',
            '{product_name}',
            '{supplier_name}',
            '{product_reference}',
            '{manufacturer_name}',
            '{short_description}',
            '{cover_image_link}',
            '{long_description}',
            '{price_with_reduction_inc_tax}',
            '{default_category}',
            '{price_without_reduction_inc_tax}',
            '{product_categories}',
            '{price_with_reduction_exc_tax}',
            '{product_link}',
            '{price_without_reduction_exc_tax}',
            '{product_features}',
            '{discount_percent}',
            '{product_tags}',
            '{product_condition}',
        ),
        'category' => array(
            '{category_id}',
            '{category_parent_categories}',
            '{category_name}',
            '{category_meta_title}',
            '{category_description}',
            '{category_meta_description}',
            '{category_link}',
            '{category_meta_keywords}',
            '{category_cover_image}',
        ),
        'manufacturer' => array(
            '{manufacturer_id}',
            '{manufacturer_meta_title}',
            '{manufacturer_name}',
            '{manufacturer_meta_description}',
            '{manufacturer_link}',
            '{manufacturer_meta_keywords}',
            '{manufacturer_short_description}',
            '{manufacturer_logo_link}',
            '{manufacturer_long_description}',
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if (method_exists('Shop', 'addTableAssociation')) {
            Shop::addTableAssociation($this->tableName, array('type' => 'shop'));
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function renderHtml()
    {
        if (empty($this->html)) {
            return "";
        }

        $html = $this->html;
        $context = Context::getContext();
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;

        // GENERAL
        $shop_url = trim(Context::getContext()->shop->getBaseURL(true), '/');
        if (preg_match("/{shop_link[a-z\|]*}/", $html)) {
            $html = $this->replaceShortcode("/{shop_link[a-z\|]*}/", $shop_url, $html);
        }
        if (preg_match("/{shop_name[a-z\|]*}/", $html)) {
            $html = $this->replaceShortcode("/{shop_name[a-z\|]*}/", $context->shop->name, $html);
        }

        // PRODUCT
        $product = new Product(Tools::getValue('id_product'), true, $id_lang);
        $id_product_attribute = ((int) Tools::getValue('id_product_attribute')) ? (int) Tools::getValue('id_product_attribute') : null;
        if (Tools::getValue('id_product') && Validate::isLoadedObject($product)) {
            if (preg_match("/{product_id[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{product_id[a-z\|]*}/", $product->id, $html);
            }
            if (preg_match("/{product_name[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{product_name[a-z\|]*}/", $product->name, $html);
            }
            if (preg_match("/{product_reference[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{product_reference[a-z\|]*}/", $product->reference, $html);
            }
            if (preg_match("/{short_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{short_description[a-z\|]*}/", $product->description_short, $html);
            }
            if (preg_match("/{long_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{long_description[a-z\|]*}/", $product->description, $html);
            }
            if (preg_match("/{product_condition[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{product_condition[a-z\|]*}/", $product->condition, $html);
            }
            if (preg_match("/{manufacturer_name[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_name[a-z\|]*}/", $product->manufacturer_name, $html);
            }
            if (preg_match("/{supplier_name[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{supplier_name[a-z\|]*}/", $product->supplier_name, $html);
            }
            if (preg_match("/{default_category[a-z\|]*}/", $html)) {
                $defaultCategory = new Category($product->id_category_default, $id_lang);
                $html = $this->replaceShortcode("/{default_category[a-z\|]*}/", $defaultCategory->name, $html);
            }
            if (preg_match("/{product_features[a-z\|]*}/", $html)) {
                $product_features = "";
                $features = $product->getFeatures();
                if ($features) {
                    foreach ($features as $feature) {
                        $featureObj = new Feature($feature['id_feature'], $id_lang);
                        if (Validate::isLoadedObject($featureObj)) {
                            $featureValue = new FeatureValue($feature['id_feature_value'], $id_lang);
                            if (Validate::isLoadedObject($featureValue)) {
                                if (!empty($product_features)) {
                                    $product_features .= ', ';
                                }
                                $product_features .= $featureObj->name . ': ' . $featureValue->value;
                            }
                        }
                    }
                }
                $html = $this->replaceShortcode("/{product_features[a-z\|]*}/", $product_features, $html);
            }
            if (preg_match("/{product_tags[a-z\|]*}/", $html)) {
                $product_tags = $product->getTags($id_lang);
                $html = $this->replaceShortcode("/{product_tags[a-z\|]*}/", $product_tags, $html);
            }
            if (preg_match("/{product_attributes[a-z\|]*}/", $html)) {
                $product_attributes = "";
                $attribute_combinations = $product->getAttributeCombinations($id_lang);
                foreach ($attribute_combinations as $attribute_combination) {
                    if (!empty($product_attributes)) {
                        $product_attributes .= ', ';
                    }
                    $product_attributes .= $attribute_combination['group_name'] . ': ' . $attribute_combination['attribute_name'];
                }
                $html = $this->replaceShortcode("/{product_attributes[a-z\|]*}/", $product_attributes, $html);
            }
            $price_without_reduction_inc_tax = (float) Product::getPriceStatic($product->id, true, null, 2, null, false, false);
            $price_without_reduction_exc_tax = (float) Product::getPriceStatic($product->id, false, null, 2, null, false, false);
            $price_with_reduction_inc_tax = (float) Product::getPriceStatic($product->id, true, null, 2, null, false, true);
            $price_with_reduction_exc_tax = (float) Product::getPriceStatic($product->id, false, null, 2, null, false, true);
            if (preg_match("/{price_with_reduction_exc_tax[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{price_with_reduction_exc_tax[a-z\|]*}/", Tools::displayPrice($price_with_reduction_exc_tax, $context->currency), $html);
            }
            if (preg_match("/{price_without_reduction_exc_tax[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{price_without_reduction_exc_tax[a-z\|]*}/", Tools::displayPrice($price_without_reduction_exc_tax, $context->currency), $html);
            }
            if (preg_match("/{price_with_reduction_inc_tax[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{price_with_reduction_inc_tax[a-z\|]*}/", Tools::displayPrice($price_with_reduction_inc_tax, $context->currency), $html);
            }
            if (preg_match("/{price_without_reduction_inc_tax[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{price_without_reduction_inc_tax[a-z\|]*}/", Tools::displayPrice($price_without_reduction_inc_tax, $context->currency), $html);
            }
            if (preg_match("/{discount_percent[a-z\|]*}/", $html)) {
                $discount = (float) Product::getPriceStatic($product->id, false, null, 2, null, true, true, 1, true, null, null, null);
                $discount_percent = round(($discount * 100) / $price_without_reduction_exc_tax) . '%';
                $html = $this->replaceShortcode("/{discount_percent[a-z\|]*}/", $discount_percent, $html);
            }
            if (preg_match("/{product_categories[a-z\|]*}/", $html, $product_categories_match)) {
                $categories_names = "";
                $categoriesIds = $product->getCategories();
                foreach ($categoriesIds as $id_category) {
                    $categoryObj = new Category($id_category, $id_lang);
                    if (Validate::isLoadedObject($categoryObj) && !$categoryObj->is_root_category) {
                        $categories_names .= $categories_names ? ", " : "";
                        $categories_names .= (strpos($product_categories_match[0], '|nospace') !== false) ? str_replace(' ', '', $categoryObj->name) : $categoryObj->name;
                    }
                }
                $html = $this->replaceShortcode("/{product_categories[a-z\|]*}/", $categories_names, $html);
            }
            if (preg_match("/{product_link[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{product_link[a-z\|]*}/", $context->link->getProductLink($product, null, null, null, $id_lang, $id_shop, $id_product_attribute), $html);
            }
            if (preg_match("/{cover_image_link[a-z\|]*}/", $html)) {
                $cover_image = Image::getCover($product->id);
                $cover_image_link = (isset($cover_image['id_image']) && $cover_image['id_image']) ? $context->link->getImageLink($product->link_rewrite, $product->id . '-' . $cover_image['id_image']) : "";
                $html = $this->replaceShortcode("/{cover_image_link[a-z\|]*}/", $cover_image_link, $html);
            }
        }

        // CATEGORY
        $category = new Category(Tools::getValue('id_category'), $id_lang);
        if (Tools::getValue('id_category') && Validate::isLoadedObject($category)) {
            if (preg_match("/{category_id[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_id[a-z\|]*}/", $category->id, $html);
            }
            if (preg_match("/{category_name[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_name[a-z\|]*}/", $category->name, $html);
            }
            if (preg_match("/{category_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_description[a-z\|]*}/", $category->description, $html);
            }
            if (preg_match("/{category_link[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_link[a-z\|]*}/", $context->link->getCategoryLink($category, $category->link_rewrite, $id_lang, null, $id_shop), $html);
            }
            if (preg_match("/{category_meta_title[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_meta_title[a-z\|]*}/", $category->meta_title, $html);
            }
            if (preg_match("/{category_meta_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_meta_description[a-z\|]*}/", $category->meta_description, $html);
            }
            if (preg_match("/{category_meta_keywords[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{category_meta_keywords[a-z\|]*}/", $category->meta_keywords, $html);
            }
            if (preg_match("/{category_cover_image[a-z\|]*}/", $html)) {
                $category_cover_image = $category->id_image ? $context->link->getCatImageLink($category->link_rewrite, $category->id_image) : "";
                $html = $this->replaceShortcode("/{category_cover_image[a-z\|]*}/", $category_cover_image, $html);
            }
            if (preg_match("/{category_parent_categories[a-z\|]*}/", $html)) {
                $category_parent_categories = "";
                $category_parents = $category->getParentsCategories();
                if (is_array($category_parents) && $category_parents) {
                    $category_parents = array_reverse($category_parents);
                    foreach ($category_parents as $category_parent) {
                        if (!$category_parent['is_root_category']) {
                            $category_parent_categories .= $category_parent_categories ? ", " : "";
                            $category_parent_categories .= $category_parent['name'];
                        }
                    }
                }
                $html = $this->replaceShortcode("/{category_parent_categories[a-z\|]*}/", $category_parent_categories, $html);
            }
        }

        // MANUFACTURER
        $manufacturer = new Manufacturer(Tools::getValue('id_manufacturer'), $id_lang);
        if (Tools::getValue('id_manufacturer') && Validate::isLoadedObject($manufacturer)) {
            if (preg_match("/{manufacturer_id[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_id[a-z\|]*}/", $manufacturer->id, $html);
            }
            if (preg_match("/{manufacturer_name[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_name[a-z\|]*}/", $manufacturer->name, $html);
            }
            if (preg_match("/{manufacturer_short_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_short_description[a-z\|]*}/", $manufacturer->short_description, $html);
            }
            if (preg_match("/{manufacturer_long_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_long_description[a-z\|]*}/", $manufacturer->description, $html);
            }
            if (preg_match("/{manufacturer_link[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_link[a-z\|]*}/", $context->link->getManufacturerLink($manufacturer, $manufacturer->link_rewrite, $id_lang, $id_shop), $html);
            }
            if (preg_match("/{manufacturer_logo_link[a-z\|]*}/", $html)) {
                $manufacturer_logo = ($manufacturer->id && file_exists(_PS_MANU_IMG_DIR_ . (int) $manufacturer->id . '.jpg')) ? $shop_url . '/img/m/' . (int) $manufacturer->id . '.jpg' : "";
                $html = $this->replaceShortcode("/{manufacturer_logo_link[a-z\|]*}/", $manufacturer_logo, $html);
            }
            if (preg_match("/{manufacturer_meta_title[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_meta_title[a-z\|]*}/", $manufacturer->meta_title, $html);
            }
            if (preg_match("/{manufacturer_meta_description[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_meta_description[a-z\|]*}/", $manufacturer->meta_description, $html);
            }
            if (preg_match("/{manufacturer_meta_keywords[a-z\|]*}/", $html)) {
                $html = $this->replaceShortcode("/{manufacturer_meta_keywords[a-z\|]*}/", $manufacturer->meta_keywords, $html);
            }
        }

        return $html;
    }

    protected function replaceShortcode($pattern, $replace, $html)
    {
        $matches = array();
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[0] as $match) {
                $params = explode('|', str_replace(array('{', '}'), '', $match));
                foreach ($params as $key => $value) {
                    if ($key > 0) {
                        switch ($value) {
                            case 'lowercase':
                                $replace = Tools::strtolower($replace);
                                break;
                            case 'uppercase':
                                $replace = Tools::strtoupper($replace);
                                break;
                            case 'nospace':
                                $replace = str_replace(' ', '', $replace);
                                break;
                            case 'spacetohashtag':
                                $replace = str_replace(' ', ' #', $replace);
                                break;
                            case 'removedash':
                                $replace = str_replace('-', '', $replace);
                                break;
                            case 'removecomma':
                                $replace = str_replace(',', '', $replace);
                                break;
                            case 'commatospace':
                                $replace = str_replace(',', ' ', $replace);
                                break;
                            case 'nohtml':
                                $replace = str_replace(array('<br>', '<br/>', '<br />'), ' ', $replace);
                                $replace = strip_tags($replace);
                                break;
                            case 'nocurrency':
                                $replace = preg_replace("/[^0-9,.]/", "", $replace);
                                break;
                            default:
                                break;
                        }
                    }
                }
                $html = str_replace($match, $replace, $html);
            }
        }
        return $html;
    }
}
