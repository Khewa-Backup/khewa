<?php
/**
* 2017 - Keyrnel SARL
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one shop.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
* If you have not received this module from us, thank you for contacting us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    Keyrnel
* @copyright 2017 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/

class ThegiftcardPageModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $scope = $this->context->smarty->createData($this->context->smarty);
        $scope->assign(array(
            'errors' => $this->errors,
            'request_uri' => Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']))
        ));
        $tpl_errors = version_compare(_PS_VERSION_, '1.7', '<') ? _PS_THEME_DIR_.'/errors.tpl' : '_partials/form-errors.tpl';
        $errors_rendered = $this->context->smarty->createTemplate($tpl_errors, $scope)->fetch();

        $this->context->smarty->assign(array(
            'errors_rendered' => $errors_rendered,
            'errors_nb' => (int)count($this->errors),
            'token' => Tools::getToken(false),
            'attribute_anchor_separator' => Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'),
            'currentUrl' => $this->context->link->getModuleLink('thegiftcard', 'page'),
            'currencySign' => $this->context->currency->sign,
            'ajax_allowed' => (int)(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false
        ));

        $template = version_compare(_PS_VERSION_, '1.7', '>=') ? 'module:thegiftcard/views/templates/front/layout.tpl' : 'giftcard.tpl';
        $this->setTemplate($template);

        return parent::display();
    }

    public function initContent()
    {
        $id_currency = (int)$this->context->currency->id;
        $this->product = new Product((int)Configuration::get('GIFTCARD_PROD_'.$id_currency), false, $this->context->language->id, $this->context->shop->id);

        if (Validate::isLoadedObject($this->product) && $this->product->isAssociatedToShop()) {
            if (Tools::getValue('ajax')) {
                if (Tools::getValue('action') == 'getCombination') {
                    $this->displayAjax();
                }
                if (Tools::getValue('action') == 'refresh') {
                    $this->displayAjaxRefresh();
                }
            }

            parent::initContent();

            $id_category = (int)$this->product->id_category_default;
            $this->category = new Category((int)$id_category, (int)$this->context->cookie->id_lang);
            if (isset($this->context->cookie) && isset($this->category->id_category)
            && !(Module::isInstalled('blockcategories') && Module::isEnabled('blockcategories'))) {
                $this->context->cookie->last_visited_category = (int)$this->category->id_category;
            }

            //Assign template vars related to the customization
            $this->assignCustomization();

            // Assign template vars related to the category + execute hooks related to the category
            $this->assignCategory();

            // Assign attribute groups to the template
            $this->assignAttributesGroups();

            $this->context->smarty->assign(array(
                'product' => $this->product,
                'active' => Configuration::get('GIFTCARD_ACTIVE_'.(int)$this->product->id),
                'pitch' => (int)Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_'.(int)$this->product->id),
                'custom_amount_from' => (int)Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_'.(int)$this->product->id),
                'custom_amount_to' => (int)Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_'.(int)$this->product->id),
            ));
        } else {
            Tools::redirect('index.php?controller=404');
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/front/design.css', 'all');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/front/product.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/tools/bootstrap.js');
        $this->addJqueryUI('ui.datepicker');
        // if (version_compare(_PS_VERSION_, '1.7', '>=')) {
        //     $this->registerJavascript('jquery-ui-datepicker-i18n', '/js/jquery/ui/i18n/jquery.ui.datepicker-'.Context::getContext()->language->iso_code.'.js', ['position' => 'bottom', 'priority' => 100]);
        // }

        $this->addjQueryPlugin('fancybox');
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->addjQueryPlugin('growl', null, false);
        } else {
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/tools/jquery.growl.js');
        }
    }

    /**
    * Assign customization fields
    */
    protected function assignCustomization()
    {
        $cronjobs = Module::getInstanceByName('cronjobs');
        $cronjobs = (bool)($cronjobs && $cronjobs->active);
        $this->product->customization_required = false;
        $customization_fields = $this->product->customizable ? $this->product->getCustomizationFields($this->context->language->id) : false;
        if (is_array($customization_fields)) {
            foreach ($customization_fields as $key => $customization_field) {
                if ($customization_field['id_customization_field'] == Configuration::get('GIFTCARD_CUST_DATE_'.(int)$this->product->id) && !$cronjobs) {
                    unset($customization_fields[$key]);
                }
            }
        }

        $this->context->smarty->assign('customizationFields', $customization_fields);
    }

    /**
     * Assign template vars related to category
     */
    protected function assignCategory()
    {
        // Assign category to the template
        if ($this->category !== false && Validate::isLoadedObject($this->category) && $this->category->inShop() && $this->category->isAssociatedToShop()) {
            $path = Tools::getPath($this->category->id, $this->product->name, true);
        } elseif (Category::inShopStatic($this->product->id_category_default, $this->context->shop)) {
            $this->category = new Category((int)$this->product->id_category_default, (int)$this->context->language->id);
            if (Validate::isLoadedObject($this->category) && $this->category->active && $this->category->isAssociatedToShop()) {
                $path = Tools::getPath((int)$this->product->id_category_default, $this->product->name);
            }
        }
        if (!isset($path) || !$path) {
            $path = Tools::getPath((int)$this->context->shop->id_category, $this->product->name);
        }

        $imageType = version_compare(_PS_VERSION_, '1.7', '>=') ? ImageType::getFormattedName('category') : ImageType::getFormatedName('category');

        if (Validate::isLoadedObject($this->category)) {
            $this->context->smarty->assign(array(
                'path' => $path,
                'category' => $this->category,
                'categorySize' => Image::getSize($imageType)
            ));
        }
    }

    /**
    * Assign template vars related to attribute groups
    */
    protected function assignAttributesGroups()
    {
        $selected_amount = null;
        $template_vars = array();
        $template_group_object = new AttributeGroup((int)Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'));
        if (Validate::isLoadedObject($template_group_object)) {
            $images_id = array();
            $product_images = Image::getImages($this->context->language->id, $this->product->id);
            foreach ($product_images as $image) {
                $images_id[] = (int)$image['id_image'];
            }

            $images = array();
            $template_attributes = AttributeGroup::getAttributes((int)$this->context->language->id, (int)$template_group_object->id);
            foreach ($template_attributes as $template_attribute) {
                $image_obj = new Image((int)$template_attribute['name']);
                if (!Validate::isLoadedObject($image_obj) || !in_array($image_obj->id, $images_id)) {
                    continue;
                }

                $image_lang = GiftCardModel::getGiftCardImageLang((int)$image_obj->id);
                if ($image_lang != $this->context->language->id && $image_lang != 0) {
                    continue;
                }

                $default_amount = GiftCardModel::getAmount($image_obj->id);
                if ($image_obj->cover) {
                    $selected_amount = $default_amount['amount'];
                }

                $image = array();
                $name = 'product_mini_'.(int)$image_obj->id_product.'_'.(int)$image_obj->id.'.jpg';
                $image['thumbnail'] = ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$image_obj->getExistingImgPath().'.jpg', $name, 300, 'jpg', false, true);
                $image['attribute_value'] = (int)$image_obj->id;
                $image['position'] = (int)$image_obj->position;
                $image['legend'] = $image_obj->legend[$this->context->language->id];
                $image['tags'] = GiftCardModel::getTagsByIdImage((int)$image_obj->id, $this->context->language->id);
                $image['cover'] = $image_obj->cover;
                $image['auto'] = $default_amount && $default_amount['auto'] ? $default_amount['amount'] : false;
                $images[] = $image;
            }

            $tags = array($this->module->l('All', 'page') => count($images));
            $tag_list = GiftCardModel::getTags((int)$this->context->language->id, $images_id);
            if (is_array($tag_list) && count($tag_list)) {
                $tags = $tags + $tag_list;
            }

            if (count($images)) {
                array_multisort(array_column($images, 'position'), SORT_ASC, $images);
                $template_vars = array(
                    'id_attribute_group' => (int)$template_group_object->id,
                    'public_group_name' => $template_group_object->public_name[(int)$this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace(array(',', '.'), '-', $template_group_object->public_name[(int)$this->context->language->id]))),
                    'attributes' => $images,
                    'tags' => $tags
                );
            }
        }
        $amount_vars = array();
        $amount_group_object = new AttributeGroup((int)Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
        if (Validate::isLoadedObject($amount_group_object)) {
            $amounts = array();
            $fixed_amount_list = explode(',', Configuration::get('GIFTCARD_AMOUNT_FIXED_'.(int)$this->product->id));
            $amount_attributes = AttributeGroup::getAttributes((int)$this->context->language->id, (int)$amount_group_object->id);
            foreach ($amount_attributes as $amount_attribute) {
                if (!in_array($amount_attribute['name'], $fixed_amount_list)) {
                    continue;
                }

                $amount = array();
                $amount['attribute_value'] = (int)$amount_attribute['name'];
                $amount['position'] = (int)$amount_attribute['position'];
                $amounts[] = $amount;
            }

            if (count($amounts)) {
                array_multisort(array_column($amounts, 'attribute_value'), SORT_ASC, $amounts);
                $amount_vars = array(
                    'id_attribute_group' => (int)$amount_group_object->id,
                    'public_group_name' => $amount_group_object->public_name[(int)$this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace(array(',', '.'), '-', $amount_group_object->public_name[(int)$this->context->language->id]))),
                    'attributes' => $amounts
                );
            }
        }

        $this->context->smarty->assign(array(
            'template_vars' => (count($template_vars)) ? $template_vars : false,
            'amount_vars' => (count($amount_vars)) ? $amount_vars : false,
            'default_amount' => $selected_amount,
        ));
    }

    public function displayAjax()
    {
        if (!Configuration::get('GIFTCARD_ACTIVE_'.(int)$this->product->id) || !$this->product->active) {
            die(Tools::jsonEncode(array('error' => $this->module->l('The gift card is not active for the moment.', 'page'))));
        }

        $giftcard_vars = array();
        $id_template_attribute_group = (int)Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE');
        $id_amount_attribute_group = (int)Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT');

        if (!Context::getContext()->cart->id && isset($_COOKIE[Context::getContext()->cookie->getName()])) {
            Context::getContext()->cart->add();
            Context::getContext()->cookie->id_cart = (int)Context::getContext()->cart->id;
        }

        $sending_method = (int)Tools::getValue('sendingMethod');
        if ($sending_method != GiftCardModel::PRINT_AT_HOME && $sending_method != GiftCardModel::SEND_TO_FRIEND) {
            die(Tools::jsonEncode(array('error' => $this->module->l('Please select a sending method', 'page'))));
        }

        // get or create combination with attributes
        $attributes = Tools::getValue('attributes');
        if (!isset($attributes)
            || empty($attributes)
            || !is_array($attributes)
            || !count($attributes)) {
            die(Tools::jsonEncode(array('error' => $this->module->l('Please select a template and an amount', 'page'))));
        }

        $images_list = array();
        $product_images = Image::getImages($this->context->language->id, $this->product->id);
        foreach ($product_images as $image) {
            $images_list[] = (int)$image['id_image'];
        }

        $amounts_list = array();
        $custom_amount_from = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_'.(int)$this->product->id);
        $custom_amount_to = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_'.(int)$this->product->id);
        $pitch = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_'.(int)$this->product->id);
        for ($i = $custom_amount_from; $i <= $custom_amount_to; $i = $i + $pitch) {
            $amounts_list[] = $i;
        }

        $ids_attribute = array();
        $attribute_values = array();
        foreach ($attributes as $attribute) {
            $id_attribute = false;
            $existing_attributes = AttributeGroup::getAttributes($this->context->language->id, (int)$attribute['id_attribute_group']);
            $list = $attribute['id_attribute_group'] == $id_template_attribute_group ? $images_list : $amounts_list;

            if (in_array($attribute['value'], $list)) {
                foreach ($existing_attributes as $existing_attribute) {
                    if (isset($existing_attribute['name']) && $existing_attribute['name'] == $attribute['value']) {
                        $id_attribute = $existing_attribute['id_attribute'];
                    }
                }
            }

            if (!$id_attribute) {
                if ($attribute['id_attribute_group'] == $id_template_attribute_group) {
                    die(Tools::jsonEncode(array('error' => $this->module->l('Please select a template', 'page'))));
                } elseif ($attribute['id_attribute_group'] == $id_amount_attribute_group) {
                    die(Tools::jsonEncode(array('error' => $this->module->l('Please select an amount', 'page'))));
                }
            }

            $ids_attribute[] = array($id_attribute);
            $attribute_values[(int)$attribute['id_attribute_group']] = (int)$attribute['value'];
        }

        $combinations = array_values(Thegiftcard::createCombinations($ids_attribute));
        $values = array_values(array_map(array($this->module, 'getCombinationProperties'), array($this->product->id), array((int)Context::getContext()->currency->id), $combinations));

        if (!$this->module->generateMultipleCombinations((int)$this->product->id, $values, $combinations)) {
            die(Tools::jsonEncode(array('error' => $this->module->l('Error while creating the gift card with selected template and amount', 'page'))));
        }

        $giftcard_vars['id_combination'] = (int)$this->product->productAttributeExists($combinations[0], false, null, true, true);
        $combination = new Combination((int)$giftcard_vars['id_combination']);
        if (!Validate::isLoadedObject($combination)) {
            die(Tools::jsonEncode(array('error' => $this->module->l('Unable to load the combination object', 'page'))));
        }

        // get customization fields if user chose to send the gift card to a friend
        if ($sending_method == GiftCardModel::SEND_TO_FRIEND) {
            $field_ids = $this->product->getCustomizationFieldIds();

            $authorized_text_fields = array();
            foreach ($field_ids as $field_id) {
                if ($field_id['type'] == Product::CUSTOMIZE_TEXTFIELD) {
                    $authorized_text_fields[(int)$field_id['id_customization_field']] = 'textField'.(int)$field_id['id_customization_field'];
                }
            }

            $indexes = array_flip($authorized_text_fields);
            foreach (Tools::getValue('customizationData') as $field_name => $value) {
                if (in_array($field_name, $authorized_text_fields) && $value != '') {
                    if ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_DATE_'.(int)$this->product->id) && (!($value = date('Y-m-d', strtotime($value))) || !Validate::isDate($value) || date('Y-m-d') > $value)) {
                        die(Tools::jsonEncode(array('error' => $this->module->l('Please select a valid date', 'page'))));
                    } elseif ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_EMAIL_'.(int)$this->product->id) && !Validate::isEmail($value)) {
                        die(Tools::jsonEncode(array('error' => $this->module->l('Please fill a valid email', 'page'))));
                    } elseif (!Validate::isMessage($value)) {
                        die(Tools::jsonEncode(array('error' => $this->module->l('An error occurred while attempting to save this data.', 'page'))));
                    } else {
                        Context::getContext()->cart->_addCustomization($this->product->id, $giftcard_vars['id_combination'], $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value, 0);
                    }
                } else {
                    die(Tools::jsonEncode(array('error' => $this->module->l('Please fill all the fields.', 'page'))));
                }
            }

            $customization_datas = Context::getContext()->cart->getProductCustomization($this->product->id, null, true);
            if (empty($customization_datas)) {
                $combination->delete();
                die(Tools::jsonEncode(array('error' => $this->module->l('An error occurred while attempting to get the customized data.', 'page'))));
            }
            $giftcard_vars['id_customization'] = (int)$customization_datas[0]['id_customization'];
        }

        die(Tools::jsonEncode(array(
            'error' => false,
            'giftcard_vars' => $giftcard_vars
        )));
    }

    public function displayAjaxRefresh()
    {
        die(1);
    }
}
