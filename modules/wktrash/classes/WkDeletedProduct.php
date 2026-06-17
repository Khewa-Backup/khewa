<?php
/**
 * 2010-2021 Webkul.
 *
 * NOTICE OF LICENSE
 *
 * All right is reserved,
 * Please go through LICENSE.txt file inside our module
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright 2010-2021 Webkul IN
 * @license LICENSE.txt
 */
class WkDeletedProduct extends ObjectModel
{
    public $id_product;
    public $id_supplier;
    public $id_manufacturer;
    public $id_category_default;
    public $id_shop_default;
    public $id_tax_rules_group;
    public $product_name;
    public $ean13;
    public $isbn;
    public $upc;
    public $quantity;
    public $minimal_quantity;
    public $price;
    public $wholesale_price;
    public $unity;
    public $unit_price_ratio;
    public $reference;
    public $supplier_reference;
    public $location;
    public $width;
    public $height;
    public $depth;
    public $weight;
    public $out_of_stock;
    public $additional_delivery_times;
    public $quantity_discount;
    public $cache_is_pack;
    public $cache_has_attachments;
    public $is_virtual;
    public $state;
    public $shop;
    public $lang;
    public $combination;
    public $feature;
    public $attachment;
    public $download;
    public $category;
    public $tag;
    public $supplier;
    public $image;
    public $customization_field;
    public $customized_data;
    public $carrier;
    public $stock;
    public $specific_price;
    public $specific_price_priority;
    public $product_sale;
    public $product_group_reduction;
    public $related_product;
    public $pack_product;
    public $old_date_add;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'wk_deleted_product',
        'primary' => 'id_wk_deleted_product',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_manufacturer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_category_default' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_shop_default' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_tax_rules_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'product_name' => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 128],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13],
            'isbn' => ['type' => self::TYPE_STRING, 'validate' => 'isIsbn', 'size' => 32],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'minimal_quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'wholesale_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'unity' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'unit_price_ratio' => ['type' => self::TYPE_FLOAT],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64],
            'supplier_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64],
            'location' => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64],
            'width' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'height' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'depth' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'out_of_stock' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'additional_delivery_times' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'quantity_discount' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'cache_is_pack' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'cache_has_attachments' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_virtual' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'state' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'shop' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'lang' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'combination' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'feature' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'attachment' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'download' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'category' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'tag' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'supplier' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'customization_field' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'customized_data' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'carrier' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'stock' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'specific_price' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'specific_price_priority' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_sale' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'product_group_reduction' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'related_product' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'pack_product' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'old_date_add' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
        ],
    ];

    public static function productExistsAfterRestore($idProduct)
    {
        if ($idProduct) {
            $row = Db::getInstance()->getRow(
                'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if (isset($row['id_product'])) {
                return $idProduct;
            } else {
                $productNewId = Db::getInstance()->getValue(
                    'SELECT `id_new_entity` FROM `' . _DB_PREFIX_ . 'wk_entity_restore_history`
                    WHERE `id_old_entity` = ' . (int) $idProduct . ' AND `type` = 1'
                );
                if ($productNewId) {
                    return self::productExistsAfterRestore($productNewId);
                }
            }
        }

        return false;
    }

    public function getProductDetailBeforeDelete($idProduct)
    {
        if ($idProduct) {
            $productInfo = $this->getProduct($idProduct);
            if ($productInfo) {
                $productInfo['product_name'] = Product::getProductName($idProduct);
                $shop = $this->getProduct($idProduct, true);
                if ($shop) {
                    $productInfo['shop'] = json_encode($shop);
                }
                $lang = $this->getProduct($idProduct, false, true);
                if ($lang) {
                    $productInfo['lang'] = json_encode($lang);
                }
                $combinations = $this->getCombinationByProductId($idProduct);
                if ($combinations) {
                    $productInfo['combination'] = json_encode($combinations);
                } else {
                    $productInfo['combination'] = null;
                }
                $features = $this->getProductFeatureByProductId($idProduct);
                if ($features) {
                    $productInfo['feature'] = json_encode($features);
                } else {
                    $productInfo['feature'] = null;
                }
                $attachment = $this->getAttachmentByProductId($idProduct);
                if ($attachment) {
                    $productInfo['attachment'] = json_encode($attachment);
                } else {
                    $productInfo['attachment'] = null;
                }
                $download = $this->getDownloadByProductId($idProduct);
                if ($download) {
                    $source = _PS_DOWNLOAD_DIR_ . $download['filename'];
                    $destination = _PS_MODULE_DIR_ . 'wktrash/views/img/product/download/' . $download['filename'];
                    if (file_exists($source)) {
                        copy($source, $destination);
                    }
                    $productInfo['download'] = json_encode($download);
                } else {
                    $productInfo['download'] = null;
                }
                $category = $this->getCategoryByProductId($idProduct);
                if ($category) {
                    $productInfo['category'] = json_encode($category);
                } else {
                    $productInfo['category'] = null;
                }
                $tag = $this->getTagByProductId($idProduct);
                if ($tag) {
                    $productInfo['tag'] = json_encode($tag);
                } else {
                    $productInfo['tag'] = null;
                }
                $supplier = $this->getSupplierByProductId($idProduct);
                if ($supplier) {
                    $productInfo['supplier'] = json_encode($supplier);
                } else {
                    $productInfo['supplier'] = null;
                }
                $images = $this->getImageByProductId($idProduct);
                if ($images) {
                    foreach ($images as $image) {
                        $source = _PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($image['id_image']) .
                            $image['id_image'] . '.jpg';
                        $destination = _PS_MODULE_DIR_ . 'wktrash/views/img/product/image/' . $image['id_image'] . '.jpg';
                        if (file_exists($source)) {
                            ImageManager::resize($source, $destination);
                        }
                    }
                    $productInfo['image'] = json_encode($images);
                } else {
                    $productInfo['image'] = null;
                }
                $customizationField = $this->getCustomizationFieldByProductId($idProduct);
                if ($customizationField) {
                    $productInfo['customization_field'] = json_encode($customizationField);
                } else {
                    $productInfo['customization_field'] = null;
                }
                $customizedData = $this->getCustomizedDataByProductId($idProduct);
                if ($customizedData) {
                    $productInfo['customized_data'] = json_encode($customizedData);
                } else {
                    $productInfo['customized_data'] = null;
                }
                $carrier = $this->getCarrierByProductId($idProduct);
                if ($carrier) {
                    $productInfo['carrier'] = json_encode($carrier);
                } else {
                    $productInfo['carrier'] = null;
                }
                $stock = $this->getStockAvailableByProductId($idProduct);
                if ($stock) {
                    $productInfo['stock'] = $stock;
                } else {
                    $productInfo['stock'] = 0;
                }
                $specificPrice = $this->getSpecificPriceByProductId($idProduct);
                if ($specificPrice) {
                    $productInfo['specific_price'] = json_encode($specificPrice);
                } else {
                    $productInfo['specific_price'] = null;
                }
                $specificPricePriority = $this->getPriorityByProductId($idProduct);
                if ($specificPricePriority) {
                    $productInfo['specific_price_priority'] = json_encode($specificPricePriority);
                } else {
                    $productInfo['specific_price_priority'] = null;
                }
                $productSale = $this->getProductSaleByProductId($idProduct);
                if ($productSale) {
                    $productInfo['product_sale'] = json_encode($productSale);
                } else {
                    $productInfo['product_sale'] = null;
                }
                $productGroupReduction = $this->getGroupReductionByProductId($idProduct);
                if ($productGroupReduction) {
                    $productInfo['product_group_reduction'] = json_encode($productGroupReduction);
                } else {
                    $productInfo['product_group_reduction'] = null;
                }
                $relatedProduct = $this->getRelatedProductsByProductId($idProduct);
                if ($relatedProduct) {
                    $productInfo['related_product'] = json_encode($relatedProduct);
                } else {
                    $productInfo['related_product'] = null;
                }
                $packProductList = $this->getPackProductsByProductId($idProduct);
                if ($packProductList) {
                    $productInfo['pack_product'] = json_encode($packProductList);
                } else {
                    $productInfo['pack_product'] = null;
                }
                $this->saveDeletedProduct($productInfo);
            }
            $objEntityRestoreHistory = new WkEntityRestoreHistory();
            $objEntityRestoreHistory->addEntityHistory(1, $idProduct);
        }
    }

    public function getPackProductsByProductId($idProduct)
    {
        $packProductList = [];
        if (Pack::isPack($idProduct)) {
            $packProducts = Pack::getItemTable($idProduct, Context::getContext()->language->id);
            if (!empty($packProducts)) {
                $packProductData = [];
                foreach ($packProducts as $packProduct) {
                    $packProductData['packProductId'] = $packProduct['id_product'];
                    $packProductData['packProductAttributeId'] = $packProduct['id_product_attribute_item'];
                    $packProductData['packProductQuantity'] = $packProduct['pack_quantity'];
                    $packProductList[] = $packProductData;
                }
            }
        }

        return $packProductList;
    }

    public function getProduct($idProduct, $isShop = false, $isLang = false)
    {
        if ($idProduct) {
            if ($isShop) {
                return Db::getInstance()->executeS(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'product_shop`
                    WHERE `id_product` = ' . (int) $idProduct
                );
            } elseif ($isLang) {
                $allLang = [];
                foreach (Language::getLanguages() as $lang) {
                    $allLang[$lang['id_lang']] = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'product_lang`
                        WHERE `id_product` = ' . (int) $idProduct .
                            ' AND `id_lang` = ' . (int) $lang['id_lang']
                    );
                }

                return $allLang;
            } else {
                return Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'product`
                    WHERE `id_product` = ' . (int) $idProduct
                );
            }
        }

        return false;
    }

    public function getCombinationByProductId($idProduct)
    {
        if ($idProduct) {
            if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                $combinations = Db::getInstance()->executeS(
                    'SELECT `id_product_attribute`, `id_product`, `reference`, `supplier_reference`,
                            `ean13`, `isbn`, `upc` FROM `' . _DB_PREFIX_ . 'product_attribute`
                            WHERE `id_product` = ' . (int) $idProduct
                );
            } else {
                $combinations = Db::getInstance()->executeS(
                    'SELECT `id_product_attribute`, `id_product`, `reference`, `supplier_reference`, `location`,
                            `ean13`, `isbn`, `upc`, `quantity` FROM `' . _DB_PREFIX_ . 'product_attribute`
                            WHERE `id_product` = ' . (int) $idProduct
                );
            }
            if ($combinations) {
                $allCombination = [];
                foreach ($combinations as $combination) {
                    if ($combination['id_product_attribute']) {
                        $combination['shop'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_shop`
                            WHERE `id_product_attribute` = ' . (int) $combination['id_product_attribute']
                        );
                        $combination['attribute_combination'] = $this->getAttributeId(
                            $combination['id_product_attribute']
                        );
                        $combination['stock'] = $this->getStockAvailableByProductId(
                            $idProduct,
                            $combination['id_product_attribute']
                        );
                        $combination['image'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_image`
                            WHERE `id_product_attribute` = ' . (int) $combination['id_product_attribute']
                        );
                    }
                    array_push($allCombination, $combination);
                }

                return $allCombination;
            }
        }

        return false;
    }

    public function getAttributeId($idProductAttribute)
    {
        if ($idProductAttribute) {
            return Db::getInstance()->executeS(
                'SELECT `id_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute_combination`
                WHERE `id_product_attribute` = ' . (int) $idProductAttribute
            );
        }

        return false;
    }

    public function getProductFeatureByProductId($idProduct)
    {
        if ($idProduct) {
            $features = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'feature_product`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if ($features) {
                $allFeature = [];
                foreach ($features as $feature) {
                    if ($feature['id_feature'] && $feature['id_feature_value']) {
                        $feature['custom'] = Db::getInstance()->getValue(
                            'SELECT `custom` FROM `' . _DB_PREFIX_ . 'feature_value`
                            WHERE `id_feature` = ' . (int) $feature['id_feature'] .
                                ' AND id_feature_value = ' . (int) $feature['id_feature_value']
                        );
                        if ($feature['custom']) {
                            $feature['feature_value_lang'] = Db::getInstance()->executeS(
                                'SELECT * FROM `' . _DB_PREFIX_ . 'feature_value_lang`
                                WHERE `id_feature_value` = ' . (int) $feature['id_feature_value']
                            );
                        }
                    }
                    array_push($allFeature, $feature);
                }

                return $allFeature;
            }
        }

        return false;
    }

    public function getAttachmentByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_attachment`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getDownloadByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_download`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getCategoryByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'category_product`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getTagByProductId($idProduct)
    {
        if ($idProduct) {
            $tags = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_tag`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if ($tags) {
                $allTag = [];
                foreach ($tags as $tag) {
                    if ($tag['id_tag'] && $tag['id_lang']) {
                        $tag['name'] = Db::getInstance()->getValue(
                            'SELECT `name` FROM `' . _DB_PREFIX_ . 'tag`
                            WHERE `id_tag` = ' . (int) $tag['id_tag'] .
                                ' AND `id_lang` = ' . (int) $tag['id_lang']
                        );
                    }
                    array_push($allTag, $tag);
                }

                return $allTag;
            }
        }

        return false;
    }

    public function getSupplierByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_supplier`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getImageByProductId($idProduct)
    {
        if ($idProduct) {
            $images = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'image`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if ($images) {
                $allImage = [];
                foreach ($images as $image) {
                    if ($image['id_image']) {
                        $image['lang'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'image_lang`
                            WHERE `id_image` = ' . (int) $image['id_image']
                        );
                        $image['shop'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'image_shop`
                            WHERE `id_image` = ' . (int) $image['id_image']
                        );
                        $image['combination'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'product_attribute_image`
                            WHERE `id_image` = ' . (int) $image['id_image']
                        );
                    }
                    array_push($allImage, $image);
                }

                return $allImage;
            }
        }

        return false;
    }

    public function getCustomizationFieldByProductId($idProduct)
    {
        if ($idProduct) {
            $fields = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'customization_field`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if ($fields) {
                $allField = [];
                foreach ($fields as $field) {
                    if ($field['id_customization_field']) {
                        $field['lang'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'customization_field_lang`
                            WHERE `id_customization_field` = ' . (int) $field['id_customization_field']
                        );
                    }
                    array_push($allField, $field);
                }

                return $allField;
            }
        }

        return false;
    }

    public function getCustomizedDataByProductId($idProduct)
    {
        if ($idProduct) {
            $customizations = Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'customization`
                WHERE `id_product` = ' . (int) $idProduct
            );
            if ($customizations) {
                $allCustomization = [];
                foreach ($customizations as $customization) {
                    if ($customization['id_customization']) {
                        $customization['customized_data'] = Db::getInstance()->executeS(
                            'SELECT * FROM `' . _DB_PREFIX_ . 'customized_data`
                            WHERE `id_customization` = ' . (int) $customization['id_customization']
                        );
                    }
                    array_push($allCustomization, $customization);
                }

                return $allCustomization;
            }
        }

        return false;
    }

    public function getCarrierByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_carrier`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getStockAvailableByProductId($idProduct, $idProductAttribute = false)
    {
        if ($idProduct) {
            return Db::getInstance()->getValue(
                'SELECT `quantity` FROM `' . _DB_PREFIX_ . 'stock_available` WHERE `id_product` = ' . (int) $idProduct .
                    ' AND `id_product_attribute` = ' . (int) $idProductAttribute
            );
        }

        return false;
    }

    public function getSpecificPriceByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'specific_price`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getPriorityByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'specific_price_priority`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getProductSaleByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_sale`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getGroupReductionByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'product_group_reduction_cache`
                WHERE `id_product` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getRelatedProductsByProductId($idProduct)
    {
        if ($idProduct) {
            return Db::getInstance()->executeS(
                'SELECT * FROM `' . _DB_PREFIX_ . 'accessory`
                WHERE `id_product_1` = ' . (int) $idProduct
            );
        }

        return false;
    }

    public function getCustomizationIdByProductId($idCustomization)
    {
        if ($idCustomization) {
            return Db::getInstance()->getValue(
                'SELECT `id_customization` FROM `' . _DB_PREFIX_ . 'customization`
                WHERE `id_customization` = ' . (int) $idCustomization
            );
        }

        return false;
    }

    public function saveDeletedProduct($productInfo)
    {
        if ($productInfo) {
            $objDeletedProduct = new WkDeletedProduct();
            $objDeletedProduct->id_product = $productInfo['id_product'];
            $objDeletedProduct->id_supplier = $productInfo['id_supplier'];
            $objDeletedProduct->id_manufacturer = $productInfo['id_manufacturer'];
            $objDeletedProduct->id_category_default = $productInfo['id_category_default'];
            $objDeletedProduct->id_shop_default = $productInfo['id_shop_default'];
            $objDeletedProduct->id_tax_rules_group = $productInfo['id_tax_rules_group'];
            $objDeletedProduct->product_name = $productInfo['product_name'];
            $objDeletedProduct->ean13 = $productInfo['ean13'];
            $objDeletedProduct->isbn = $productInfo['isbn'];
            $objDeletedProduct->upc = $productInfo['upc'];
            $objDeletedProduct->quantity = $productInfo['quantity'];
            $objDeletedProduct->minimal_quantity = $productInfo['minimal_quantity'];
            $objDeletedProduct->price = $productInfo['price'];
            $objDeletedProduct->wholesale_price = $productInfo['wholesale_price'];
            $objDeletedProduct->unity = $productInfo['unity'];
            $objDeletedProduct->unit_price_ratio = $productInfo['unit_price_ratio'];
            $objDeletedProduct->reference = $productInfo['reference'];
            $objDeletedProduct->supplier_reference = $productInfo['supplier_reference'];
            $objDeletedProduct->location = isset($productInfo['location']) ? $productInfo['location'] : '';
            $objDeletedProduct->width = $productInfo['width'];
            $objDeletedProduct->height = $productInfo['height'];
            $objDeletedProduct->depth = $productInfo['depth'];
            $objDeletedProduct->weight = $productInfo['weight'];
            $objDeletedProduct->out_of_stock = $productInfo['out_of_stock'];
            $objDeletedProduct->additional_delivery_times = $productInfo['additional_delivery_times'];
            $objDeletedProduct->quantity_discount = $productInfo['quantity_discount'];
            $objDeletedProduct->cache_is_pack = $productInfo['cache_is_pack'];
            $objDeletedProduct->cache_has_attachments = $productInfo['cache_has_attachments'];
            $objDeletedProduct->is_virtual = $productInfo['is_virtual'];
            $objDeletedProduct->state = $productInfo['state'];
            $objDeletedProduct->shop = $productInfo['shop'];
            $objDeletedProduct->lang = $productInfo['lang'];
            $objDeletedProduct->combination = $productInfo['combination'];
            $objDeletedProduct->feature = $productInfo['feature'];
            $objDeletedProduct->attachment = $productInfo['attachment'];
            $objDeletedProduct->download = $productInfo['download'];
            $objDeletedProduct->category = $productInfo['category'];
            $objDeletedProduct->tag = $productInfo['tag'];
            $objDeletedProduct->supplier = $productInfo['supplier'];
            $objDeletedProduct->image = $productInfo['image'];
            $objDeletedProduct->customization_field = $productInfo['customization_field'];
            $objDeletedProduct->customized_data = $productInfo['customized_data'];
            $objDeletedProduct->carrier = $productInfo['carrier'];
            $objDeletedProduct->stock = $productInfo['stock'];
            $objDeletedProduct->specific_price = $productInfo['specific_price'];
            $objDeletedProduct->specific_price_priority = $productInfo['specific_price_priority'];
            $objDeletedProduct->product_sale = $productInfo['product_sale'];
            $objDeletedProduct->product_group_reduction = $productInfo['product_group_reduction'];
            $objDeletedProduct->related_product = $productInfo['related_product'];
            $objDeletedProduct->pack_product = $productInfo['pack_product'];
            $objDeletedProduct->old_date_add = $productInfo['date_add'];
            $objDeletedProduct->save();
        }
    }

    public function getDeletedProductDetail($idDeletedProduct)
    {
        if ($idDeletedProduct) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'wk_deleted_product`
                WHERE `id_wk_deleted_product` = ' . (int) $idDeletedProduct
            );
        }

        return false;
    }

    public function updateCustomerThreadProductId($oldIdProduct, $newIdProduct)
    {
        if ($oldIdProduct && $newIdProduct) {
            return Db::getInstance()->update(
                'customer_thread',
                [
                    'id_product' => (int) $newIdProduct,
                ],
                'id_product = ' . (int) $oldIdProduct
            );
        }

        return false;
    }

    public function setImagesForCombination($combinationId, $imageId)
    {
        if ($combinationId && $imageId) {
            return Db::getInstance()->insert('product_attribute_image', [
                'id_product_attribute' => (int) $combinationId,
                'id_image' => (int) $imageId,
            ]);
        }

        return false;
    }

    public function insertProductDownload($download, $idProduct)
    {
        if ($download && $idProduct) {
            return Db::getInstance()->insert('product_download', [
                'id_product' => (int) $idProduct,
                'display_filename' => pSQL($download['display_filename']),
                'filename' => pSQL($download['filename']),
                'date_add' => pSQL($download['date_add']),
                'date_expiration' => pSQL($download['date_expiration']),
                'nb_days_accessible' => (int) $download['nb_days_accessible'],
                'nb_downloadable' => (int) $download['nb_downloadable'],
                'active' => (int) $download['active'],
                'is_shareable' => (int) $download['is_shareable'],
            ]);
        }

        return false;
    }

    public function setCustomizedData($idProduct, $customData)
    {
        if ($idProduct && $customData) {
            Db::getInstance()->insert('customization', [
                'id_product_attribute' => (int) $customData['id_product_attribute'],
                'id_address_delivery' => (int) $customData['id_address_delivery'],
                'id_cart' => (int) $customData['id_cart'],
                'id_product' => (int) $idProduct,
                'quantity' => (int) $customData['quantity'],
                'quantity_refunded' => (int) $customData['quantity_refunded'],
                'quantity_returned' => (int) $customData['quantity_returned'],
                'in_cart' => (int) $customData['in_cart'],
            ]);
            $idCustomization = Db::getInstance()->Insert_ID();
            if ($idCustomization) {
                foreach ($customData['customized_data'] as $customizedData) {
                    Db::getInstance()->insert('customized_data', [
                        'id_customization' => (int) $idCustomization,
                        'type' => (int) $customizedData['type'],
                        'index' => (int) $customizedData['index'],
                        'value' => pSQL($customizedData['value']),
                        'id_module' => (int) $customizedData['id_module'],
                        'price' => (float) $customizedData['price'],
                        'weight' => (float) $customizedData['weight'],
                    ]);
                }
            }

            return true;
        }

        return false;
    }

    public function setPriority($idProduct, $priority, $isUpdate)
    {
        if ($idProduct) {
            if ($isUpdate) {
                return Db::getInstance()->update(
                    'specific_price_priority',
                    [
                        'id_product' => (int) $idProduct,
                        'priority' => pSQL($priority['priority']),
                    ],
                    'id_specific_price_priority = ' . (int) $priority['id_specific_price_priority']
                );
            } else {
                return Db::getInstance()->insert(
                    'specific_price_priority',
                    [
                        'id_product' => (int) $idProduct,
                        'priority' => pSQL($priority['priority']),
                    ]
                );
            }
        }

        return false;
    }

    public function setProductSale($productSale, $idProduct)
    {
        if ($productSale && $idProduct) {
            return Db::getinstance()->insert('product_sale', [
                'id_product' => (int) $idProduct,
                'quantity' => (int) $productSale['quantity'],
                'sale_nbr' => (int) $productSale['sale_nbr'],
                'date_upd' => pSQL($productSale['date_upd']),
            ]);
        }

        return false;
    }

    public static function insertDataInPrimaryTable($productInfo)
    {
        if ($productInfo) {
            return Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'product` (`id_product`, `id_supplier`,
                `id_manufacturer`, `id_category_default`, `id_shop_default`, `id_tax_rules_group`, `on_sale`,
                `online_only`,  `ean13`, `isbn`, `upc`, `ecotax`, `quantity`, `minimal_quantity`, `low_stock_threshold`,
                `low_stock_alert`, `price`, `wholesale_price`, `unity`, `unit_price_ratio`, `additional_shipping_cost`,
                `reference`, `supplier_reference`, `location`, `width`, `height`, `depth`, `weight`, `out_of_stock`,
                `additional_delivery_times`, `quantity_discount`, `customizable`, `uploadable_files`, `text_fields`,
                `active`, `redirect_type`, `id_type_redirected`, `available_for_order`, `available_date`,
                `show_condition`, `condition`, `show_price`, `indexed`, `visibility`, `cache_is_pack`,
                `cache_has_attachments`, `is_virtual`, `cache_default_attribute`, `date_add`, `date_upd`,
                `advanced_stock_management`, `pack_stock_type`, `state`)
                VALUES (' . (int) $productInfo['id_product'] . ", NULL, NULL, NULL, '1', '', '0', '0', NULL, NULL, NULL,
                '0.000000', '0', '1', NULL, '0', '0.000000', '0.000000', NULL, '0.000000', '0.00', NULL, NULL, '',
                '0.000000', '0.000000', '0.000000', '0.000000', '2', '1', '0', '0', '0', '0', '0', '', '0', '1', NULL,
                '0', 'new', '1', '0', 'both', '0', '0', '0', NULL, '" . pSQL($productInfo['date_add']) . "',
                '" . pSQL($productInfo['date_upd']) . "', '0', '3', '1')"
            );
        }

        return false;
    }
}
