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
class AdminWkDeletedProductsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'wk_deleted_product';
        $this->className = 'WkDeletedProduct';
        $this->identifier = 'id_wk_deleted_product';
        $this->list_no_link = true;
        parent::__construct();

        $this->_select = '`id_wk_deleted_product` as temp_deleted_product_id';
        $this->fields_list = [
            'id_wk_deleted_product' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
            ],
            'product_name' => [
                'title' => $this->l('Product name'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'center',
                'class' => 'fixed-width-xxl',
            ],
            'temp_deleted_product_id' => [
                'title' => $this->l('Restore'),
                'align' => 'center',
                'class' => 'fixed-width-xl',
                'search' => false,
                'callback' => 'getRestoreButton',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items permanently?'),
            ],
            'restore' => [
                'text' => $this->l('Restore selected'),
                'icon' => 'icon-undo',
                'confirm' => $this->l('Restore selected items?'),
            ],
        ];
        $index = count($this->_conf);
        $this->_conf[$index] = $this->l('Successful restore.');
    }

    /**
     * To display restore button on deleted product list
     *
     * @param [int] $idDeletedProduct
     *
     * @return html
     */
    public function getRestoreButton($idDeletedProduct)
    {
        if ($idDeletedProduct) {
            $this->context->smarty->assign([
                'idDeletedEntity' => $idDeletedProduct,
                'entityTable' => $this->table,
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/restore-button.tpl'
            );
        }

        return false;
    }

    /**
     * To render list for deleted product
     *
     * @return void
     */
    public function renderList()
    {
        $this->addRowAction('delete');

        return parent::renderList();
    }

    /**
     * To hide add new button from deleted product list
     *
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    /**
     * To restore the data of products.
     *
     * @return void
     */
    public function postProcess()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        if (Tools::issubmit('restoreButton' . $this->table)) {
            if (Tools::getValue('restoreButton' . $this->table)) {
                $idDeletedProduct = Tools::getValue('restoreButton' . $this->table);
                if ($idDeletedProduct) {
                    $this->restoreProductAfterDeletion($idDeletedProduct);
                }
                if (empty($this->context->controller->errors)) {
                    $index = count($this->_conf);
                    Tools::redirectAdmin(
                        AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                    );
                }
            }
        }
        parent::postProcess();
    }

    public function processBulkRestore()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $idDeletedProduct) {
                if (!empty($idDeletedProduct) && $idDeletedProduct) {
                    $this->restoreProductAfterDeletion($idDeletedProduct);
                }
            }
            if (empty($this->context->controller->errors)) {
                $index = count($this->_conf);
                Tools::redirectAdmin(
                    AdminController::$currentIndex . '&token=' . $this->context->controller->token . '&conf=' . $index
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You must have select at least one product to restore.');
        }
    }

    public function restoreProductAfterDeletion($idDeletedProduct)
    {
        if (!empty($idDeletedProduct) && $idDeletedProduct) {
            $objDeletdProduct = new WkDeletedProduct($idDeletedProduct);
            if (Validate::isLoadedObject($objDeletdProduct)) {
                $productInfo = $objDeletdProduct->getDeletedProductDetail($idDeletedProduct);
                if (!empty($productInfo) && $productInfo) {
                    $idNewProduct = $this->restoreDeletedProduct($productInfo);
                    if (!empty($idNewProduct) && $idNewProduct) {
                        $objEntityHistory = new WkEntityRestoreHistory();
                        $historyId = $objEntityHistory->getIdByOldEntityId($productInfo['id_product'], 1);
                        if ($historyId) {
                            $objEntityHistory->updateEntityHistory($historyId, $idNewProduct);
                        }
                        $objDeletdProduct->delete();
                    }
                }
            }
        }
    }

    public function restoreDeletedProduct($productInfo)
    {
        if (!empty($productInfo) && $productInfo) {
            $product = new Product(); // Restore product with new ID
            if (!Configuration::get('WK_RESTORE_ENTITY_NEW_ID')) {
                // Restore product with Old ID
                $wkResult = WkDeletedProduct::insertDataInPrimaryTable($productInfo);
                if ($wkResult) {
                    $product = new Product($productInfo['id_product']);
                }
            }
            $product->name = [];
            $product->description = [];
            $product->description_short = [];
            $product->meta_title = [];
            $product->meta_description = [];
            $product->meta_keywords = [];
            $product->link_rewrite = [];
            $product->available_now = [];
            $product->available_later = [];
            $product->delivery_in_stock = [];
            $product->delivery_out_stock = [];

            // Decode all product info
            $productInfo['shop'] = json_decode($productInfo['shop'], true);
            $productInfo['lang'] = json_decode($productInfo['lang'], true);
            $productInfo['combination'] = json_decode($productInfo['combination'], true);
            $productInfo['feature'] = json_decode($productInfo['feature'], true);
            $productInfo['attachment'] = json_decode($productInfo['attachment'], true);
            $productInfo['download'] = json_decode($productInfo['download'], true);
            $productInfo['category'] = json_decode($productInfo['category'], true);
            $productInfo['tag'] = json_decode($productInfo['tag'], true);
            $productInfo['supplier'] = json_decode($productInfo['supplier'], true);
            $productInfo['image'] = json_decode($productInfo['image'], true);
            $productInfo['customization_field'] = json_decode($productInfo['customization_field'], true);
            $productInfo['customized_data'] = json_decode($productInfo['customized_data'], true);
            $productInfo['carrier'] = json_decode($productInfo['carrier'], true);
            $productInfo['stock'] = json_decode($productInfo['stock'], true);
            $productInfo['specific_price'] = json_decode($productInfo['specific_price'], true);
            $productInfo['specific_price_priority'] = json_decode($productInfo['specific_price_priority'], true);
            $productInfo['product_sale'] = json_decode($productInfo['product_sale'], true);
            $productInfo['product_group_reduction'] = json_decode($productInfo['product_group_reduction'], true);
            $productInfo['related_product'] = json_decode($productInfo['related_product'], true);
            $productInfo['pack_product'] = json_decode($productInfo['pack_product'], true);

            foreach (Language::getLanguages() as $lang) {
                $product->name[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['name'];
                $product->description[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['description'];
                $product->description_short[$lang['id_lang']] =
                $productInfo['lang'][$lang['id_lang']]['description_short'];

                $product->meta_title[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['meta_title'];
                $product->meta_description[$lang['id_lang']] =
                $productInfo['lang'][$lang['id_lang']]['meta_description'];
                $product->meta_keywords[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['meta_keywords'];
                $product->link_rewrite[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['link_rewrite'];

                $product->available_now[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['available_now'];
                $product->available_later[$lang['id_lang']] = $productInfo['lang'][$lang['id_lang']]['available_later'];

                $product->delivery_in_stock[$lang['id_lang']] =
                $productInfo['lang'][$lang['id_lang']]['delivery_in_stock'];
                $product->delivery_out_stock[$lang['id_lang']] =
                $productInfo['lang'][$lang['id_lang']]['delivery_out_stock'];
            }

            $idSupplier = $productInfo['id_supplier'];
            $product->id_supplier = Supplier::supplierExists($idSupplier) ? $idSupplier : 0;

            $idManufacturer = $productInfo['id_manufacturer'];
            if ($idManufacturer) {
                $wkEntityRestoreHistory = new WkEntityRestoreHistory();
                $dataByOldId = $wkEntityRestoreHistory->getIdByOldEntityAndType($idManufacturer, 3);
                if ($dataByOldId) {
                    if ($dataByOldId['id_new_entity']) {
                        $idManufacturer = $dataByOldId['id_new_entity'];
                    }
                }
            }
            $product->id_manufacturer = Manufacturer::manufacturerExists($idManufacturer) ? $idManufacturer : 0;

            if ($categoryId = WkDeletedCategory::categoryExistsAfterRestore($productInfo['id_category_default'])) {
                $product->id_category_default = $categoryId;
                $reduction = true;
            } else {
                $product->id_category_default = Category::getRootCategory()->id;
                $reduction = false;
            }
            $product->id_shop_default = Context::getContext()->shop->id;

            $idTaxRulesGroup = $productInfo['id_tax_rules_group'];
            $objTaxRule = new TaxRulesGroup($idTaxRulesGroup);
            if ($objTaxRule->active) {
                $product->id_tax_rules_group = $idTaxRulesGroup;
            } else {
                $product->id_tax_rules_group = 0;
            }

            $product->ean13 = $productInfo['ean13'];
            $product->isbn = $productInfo['isbn'];
            $product->upc = $productInfo['upc'];

            $product->quantity = $productInfo['quantity'];
            $product->minimal_quantity = $productInfo['minimal_quantity'];
            $product->price = $productInfo['price'];
            $product->wholesale_price = $productInfo['wholesale_price'];

            $unitPriceRatio = $productInfo['unit_price_ratio'];
            $product->unity = $productInfo['unity'];
            $product->unit_price = ($unitPriceRatio != 0 ? $product->price / $unitPriceRatio : 0);
            $product->unit_price_ratio = (float) $product->unit_price > 0 ? $product->price / $product->unit_price : 0;

            $product->reference = $productInfo['reference'];
            $product->supplier_reference = $productInfo['supplier_reference'];
            $product->location = isset($productInfo['location']) ? $productInfo['location'] : '';

            $product->width = $productInfo['width'];
            $product->height = $productInfo['height'];
            $product->depth = $productInfo['depth'];
            $product->weight = $productInfo['weight'];

            $product->out_of_stock = $productInfo['out_of_stock'];
            $product->additional_delivery_times = $productInfo['additional_delivery_times'];

            $product->quantity_discount = $productInfo['quantity_discount'];
            $product->cache_is_pack = $productInfo['cache_is_pack'];
            $product->cache_has_attachments = $productInfo['cache_has_attachments'];
            $product->is_virtual = $productInfo['is_virtual'];
            $product->state = $productInfo['state'];

            if ($productInfo['shop']) {
                foreach ($productInfo['shop'] as $shop) {
                    if (!empty($shop) && $shop) {
                        if ((int) $shop['id_shop'] === Context::getContext()->shop->id) {
                            $product->on_sale = $shop['on_sale'];
                            $product->online_only = $shop['online_only'];
                            $product->ecotax = $shop['ecotax'];

                            $product->low_stock_threshold = $shop['low_stock_threshold'];
                            $product->low_stock_alert = $shop['low_stock_alert'];
                            $product->additional_shipping_cost = $shop['additional_shipping_cost'];

                            $product->customizable = $shop['customizable'];
                            $product->uploadable_files = $shop['uploadable_files'];
                            $product->text_fields = $shop['text_fields'];
                            $product->redirect_type = $shop['redirect_type'];
                            $product->id_type_redirected = $shop['id_type_redirected'];

                            $product->available_for_order = $shop['available_for_order'];
                            $product->available_date = $shop['available_date'];

                            $product->show_condition = $shop['show_condition'];
                            $product->condition = $shop['condition'];
                            $product->show_price = $shop['show_price'];
                            $product->indexed = $shop['indexed'];
                            $product->visibility = $shop['visibility'];

                            $product->cache_default_attribute = $shop['cache_default_attribute'];
                            $product->advanced_stock_management = $shop['advanced_stock_management'];
                            $product->pack_stock_type = $shop['pack_stock_type'];
                            $product->active = $shop['active'];
                        }
                    }
                }
            }
            $product->save();

            if ($product->id) {
                $product->date_add = $productInfo['old_date_add'];
                $product->save();
                $objDeletdProduct = new WkDeletedProduct();
                $objDeletdProduct->updateCustomerThreadProductId($productInfo['id_product'], $product->id);

                if ($productInfo['stock'] >= 0) {
                    $idShop = Context::getContext()->shop->id;
                    StockAvailable::removeProductFromStockAvailable($product->id, null, $idShop);
                    StockAvailable::updateQuantity($product->id, null, $productInfo['stock'], $idShop);
                }

                if (!empty($productInfo['image']) && $productInfo['image']) {
                    $imageIds = [];
                    foreach ($productInfo['image'] as $image) {
                        $objImage = new Image();
                        $objImage->id_product = $product->id;
                        $objImage->position = $image['position'];
                        $objImage->cover = $image['cover'];
                        if ($image['lang'] && !empty($image['lang'])) {
                            foreach ($image['lang'] as $lang) {
                                $objImage->legend[$lang['id_lang']] = $lang['legend'];
                            }
                        }
                        $objImage->save();
                        if (isset($image['combination']) && !empty($image['combination'])) {
                            $imageId = [];
                            foreach ($image['combination'] as $comboImage) {
                                $imageId['old_image_id'] = $comboImage['id_image'];
                                $imageId['new_image_id'] = $objImage->id;
                                $imageIds[] = $imageId;
                                break;
                            }
                        }
                        $source = _PS_MODULE_DIR_ . $this->module->name . '/views/img/product/image/' .
                            $image['id_image'] . '.jpg';
                        $destination = $objImage->getPathForCreation();
                        if (file_exists($source)) {
                            if ($imageTypes = ImageType::getImagesTypes('products')) {
                                foreach ($imageTypes as $imageType) {
                                    ImageManager::resize(
                                        $source,
                                        $destination . '-' . Tools::stripslashes($imageType['name']) . '.' .
                                            $objImage->image_format,
                                        $imageType['width'],
                                        $imageType['height'],
                                        $objImage->image_format
                                    );
                                }
                                ImageManager::resize($source, $destination . '.' . $objImage->image_format);
                            }
                        }
                    }
                }

                $wkCombinationPair = [];
                if (!empty($productInfo['combination']) && $productInfo['combination']) {
                    foreach ($productInfo['combination'] as $combination) {
                        $objCombination = new Combination();
                        $objCombination->id_product = (int) $product->id;
                        $objCombination->reference = $combination['reference'];
                        $objCombination->supplier_reference = $combination['supplier_reference'];
                        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
                        } else {
                            $objCombination->location = $combination['location'];
                            $objCombination->quantity = (int) $combination['quantity'];
                        }
                        $objCombination->ean13 = $combination['ean13'];
                        $objCombination->isbn = $combination['isbn'];
                        $objCombination->upc = $combination['upc'];
                        if ($combination['shop'] && !empty($combination['shop'])) {
                            foreach ($combination['shop'] as $comboShop) {
                                if (!empty($comboShop) && $comboShop) {
                                    if ((int) $comboShop['id_shop'] === Context::getContext()->shop->id) {
                                        $objCombination->wholesale_price = $comboShop['wholesale_price'];
                                        $objCombination->price = $comboShop['price'];
                                        $objCombination->ecotax = $comboShop['ecotax'];
                                        $objCombination->weight = $comboShop['weight'];
                                        $objCombination->unit_price_impact = $comboShop['unit_price_impact'];
                                        $objCombination->default_on = $comboShop['default_on'];
                                        $objCombination->minimal_quantity = $comboShop['minimal_quantity'];
                                        $objCombination->low_stock_threshold = $comboShop['low_stock_threshold'];
                                        $objCombination->low_stock_alert = $comboShop['low_stock_alert'];
                                        $objCombination->available_date = $comboShop['available_date'];
                                    }
                                }
                            }
                        }
                        $objCombination->save();
                        $wkCombinationPair[$combination['id_product_attribute']] = $objCombination->id;
                        if ($combination['attribute_combination'] && !empty($combination['attribute_combination'])) {
                            $attrCombos = [];
                            foreach ($combination['attribute_combination'] as $attributeCombination) {
                                $idAttribute = $attributeCombination['id_attribute'];
                                if ($idAttribute) {
                                    $wkEntityRestoreHistory = new WkEntityRestoreHistory();
                                    $dataByOldId = $wkEntityRestoreHistory->getIdByOldEntityAndType($idAttribute, 6);
                                    if ($dataByOldId) {
                                        if ($dataByOldId['id_new_entity']) {
                                            $idAttribute = $dataByOldId['id_new_entity'];
                                        }
                                    }
                                }
                                $attrCombos[] = $idAttribute;
                            }
                            $objCombination->setAttributes($attrCombos);
                        }
                        if ($combination['stock'] >= 0) {
                            StockAvailable::updateQuantity($product->id, $objCombination->id, $combination['stock']);
                        }
                        if ($combination['image'] && !empty($combination['image'])) {
                            foreach ($combination['image'] as $combinationImage) {
                                if (isset($imageIds)) {
                                    foreach ($imageIds as $imageId) {
                                        if ($combinationImage['id_image'] == $imageId['old_image_id']) {
                                            $objDeletdProduct->setImagesForCombination(
                                                $objCombination->id,
                                                $imageId['new_image_id']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($productInfo['feature']) && $productInfo['feature']) {
                    foreach ($productInfo['feature'] as $feature) {
                        $wkEntityRestoreHistory = new WkEntityRestoreHistory();
                        $dataByOldId = $wkEntityRestoreHistory->getIdByOldEntityAndType($feature['id_feature'], 8);
                        if ($dataByOldId) {
                            if ($dataByOldId['id_new_entity']) {
                                $feature['id_feature'] = $dataByOldId['id_new_entity'];
                                if (isset($feature['custom']) && (bool) $feature['custom']) {
                                    $idValue = $product->addFeaturesToDB(
                                        $feature['id_feature'],
                                        null,
                                        true
                                    );
                                    if ($idValue && !empty($idValue)) {
                                        foreach ($feature['feature_value_lang'] as $lang) {
                                            $product->addFeaturesCustomToDB(
                                                (int) $idValue,
                                                (int) $lang['id_lang'],
                                                $lang['value']
                                            );
                                        }
                                    }
                                } else {
                                    $product->addFeaturesToDB(
                                        $feature['id_feature'],
                                        $feature['id_feature_value'],
                                        false
                                    );
                                }
                            }
                        } else {
                            if (isset($feature['custom']) && (bool) $feature['custom']) {
                                $idValue = $product->addFeaturesToDB(
                                    $feature['id_feature'],
                                    null,
                                    true
                                );
                                if ($idValue && !empty($idValue)) {
                                    foreach ($feature['feature_value_lang'] as $lang) {
                                        $product->addFeaturesCustomToDB(
                                            (int) $idValue,
                                            (int) $lang['id_lang'],
                                            $lang['value']
                                        );
                                    }
                                }
                            } else {
                                $product->addFeaturesToDB(
                                    $feature['id_feature'],
                                    $feature['id_feature_value'],
                                    false
                                );
                            }
                        }
                    }
                }

                if (!empty($productInfo['attachment']) && $productInfo['attachment']) {
                    $attachmentIds = [];
                    foreach ($productInfo['attachment'] as $attachment) {
                        array_push($attachmentIds, $attachment['id_attachment']);
                    }
                    Attachment::attachToProduct((int) $product->id, $attachmentIds);
                }

                if (!empty($productInfo['download']) && $productInfo['download']) {
                    $filename = $productInfo['download']['filename'];
                    $source = _PS_MODULE_DIR_ . $this->module->name . '/views/img/product/download/' . $filename;
                    $destination = _PS_DOWNLOAD_DIR_ . $filename;
                    if (file_exists($source)) {
                        rename($source, $destination);
                    }
                    $objDeletdProduct->insertProductDownload($productInfo['download'], (int) $product->id);
                }

                if ($productInfo['category']) {
                    $categories = [];
                    foreach ($productInfo['category'] as $category) {
                        if ($category) {
                            if ($categoryId = WkDeletedCategory::categoryExistsAfterRestore($category['id_category'])) {
                                array_push($categories, $categoryId);
                            }
                        }
                    }
                    if (empty($categories)) {
                        $categories[] = Category::getRootCategory()->id;
                    }
                    $product->addToCategories($categories);
                }

                if ($productInfo['tag']) {
                    foreach ($productInfo['tag'] as $tag) {
                        if ($tag) {
                            Tag::addTags((int) $tag['id_lang'], (int) $product->id, $tag['name']);
                        }
                    }
                }

                if ($productInfo['supplier']) {
                    foreach ($productInfo['supplier'] as $supplier) {
                        if ($supplier) {
                            $product->addSupplierReference(
                                (int) $supplier['id_supplier'],
                                (int) $supplier['id_product_attribute'],
                                (int) $supplier['product_supplier_reference'],
                                $supplier['product_supplier_price_te'],
                                $supplier['id_currency']
                            );
                        }
                    }
                }

                if ($productInfo['customization_field']) {
                    foreach ($productInfo['customization_field'] as $customField) {
                        $objCustomField = new CustomizationField();
                        $objCustomField->id_product = $product->id;
                        $objCustomField->type = $customField['type'];
                        $objCustomField->required = $customField['required'];
                        $objCustomField->is_module = $customField['is_module'];
                        $objCustomField->is_deleted = $customField['is_deleted'];
                        // $objCustomField->name = array();
                        foreach ($customField['lang'] as $lang) {
                            $objCustomField->name[$lang['id_lang']] = $lang['name'];
                        }
                        $objCustomField->save();
                    }
                }

                if ($productInfo['customized_data']) {
                    foreach ($productInfo['customized_data'] as $customData) {
                        // If already exist then update product id else insert entry in customization & customized_data.
                        $isCustomizationExist = $objDeletdProduct->getCustomizationIdByProductId(
                            $customData['id_customization']
                        );
                        if ($isCustomizationExist) {
                            $objCustomization = new Customization((int) $isCustomizationExist);
                            $objCustomization->id_product = $product->id;
                            $objCustomization->save();
                        } else {
                            $objDeletdProduct->setCustomizedData($product->id, $customData);
                        }
                    }
                }

                if ($productInfo['carrier']) {
                    $carriers = [];
                    foreach ($productInfo['carrier'] as $carrier) {
                        if ($carrier) {
                            array_push($carriers, $carrier['id_carrier_reference']);
                        }
                    }
                    $product->setCarriers($carriers);
                }

                if ($productInfo['specific_price']) {
                    foreach ($productInfo['specific_price'] as $specificPrice) {
                        if ($specificPrice) {
                            $objSpecifiedPrice = new SpecificPrice();
                            $objSpecifiedPrice->id_specific_price_rule = (int) $specificPrice['id_specific_price_rule'];
                            $objSpecifiedPrice->id_cart = (int) $specificPrice['id_cart'];
                            $objSpecifiedPrice->id_product = (int) $product->id;
                            $objSpecifiedPrice->id_shop = (int) $specificPrice['id_shop'];
                            $objSpecifiedPrice->id_shop_group = (int) $specificPrice['id_shop_group'];
                            $objSpecifiedPrice->id_currency = (int) $specificPrice['id_currency'];
                            $objSpecifiedPrice->id_country = (int) $specificPrice['id_country'];
                            $objSpecifiedPrice->id_group = (int) $specificPrice['id_group'];
                            $objSpecifiedPrice->id_customer = (int) $specificPrice['id_customer'];
                            if (isset($wkCombinationPair) && $wkCombinationPair) {
                                if ($specificPrice['id_product_attribute']) {
                                    $idProAtt = (int) $wkCombinationPair[$specificPrice['id_product_attribute']];
                                    $objSpecifiedPrice->id_product_attribute = $idProAtt;
                                } else {
                                    $objSpecifiedPrice->id_product_attribute = 0;
                                }
                            } else {
                                $objSpecifiedPrice->id_product_attribute = 0;
                            }
                            $objSpecifiedPrice->price = $specificPrice['price'];
                            $objSpecifiedPrice->from_quantity = $specificPrice['from_quantity'];
                            $objSpecifiedPrice->reduction = $specificPrice['reduction'];
                            $objSpecifiedPrice->reduction_tax = $specificPrice['reduction_tax'];
                            $objSpecifiedPrice->reduction_type = $specificPrice['reduction_type'];
                            $objSpecifiedPrice->from = $specificPrice['from'];
                            $objSpecifiedPrice->to = $specificPrice['to'];
                            $objSpecifiedPrice->save();
                        }
                    }
                }

                if ($productInfo['specific_price_priority']) {
                    // If already exist then update product id else insert new entry.
                    $isPriorityExist = $objDeletdProduct->getPriorityByProductId($productInfo['id_product']);
                    if ($isPriorityExist) {
                        $objDeletdProduct->setPriority(
                            $product->id,
                            $productInfo['specific_price_priority'],
                            true
                        );
                    } else {
                        $objDeletdProduct->setPriority(
                            $product->id,
                            $productInfo['specific_price_priority'],
                            false
                        );
                    }
                }

                foreach (Language::getLanguages(false) as $lang) {
                    Search::indexation($productInfo['lang'][$lang['id_lang']]['link_rewrite'], $product->id);
                }

                if ($productInfo['product_sale']) {
                    $objDeletdProduct->setProductSale($productInfo['product_sale'], (int) $product->id);
                }

                if ($productInfo['product_group_reduction']) {
                    if (isset($reduction) && $reduction) {
                        GroupReduction::setProductReduction((int) $product->id);
                    }
                }

                if ($productInfo['related_product']) {
                    $accessoryIds = [];
                    foreach ($productInfo['related_product'] as $relatedProduct) {
                        array_push($accessoryIds, $relatedProduct['id_product_2']);
                    }
                    Product::changeAccessoriesForProduct($accessoryIds, (int) $product->id);
                }

                if ($productInfo['pack_product']) {
                    foreach ($productInfo['pack_product'] as $packProduct) {
                        Pack::addItem(
                            $product->id,
                            $packProduct['packProductId'],
                            $packProduct['packProductQuantity'],
                            $packProduct['packProductAttributeId']
                        );
                    }
                }

                return $product->id;
            }
        }
    }

    /**
     * To set JS & CSS for controller
     *
     * @return void
     */
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef([
            'restore_selected_item' => $this->l('Restore selected item?'),
        ]);
        $this->context->controller->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/wk-restore-entity.js');
    }
}
