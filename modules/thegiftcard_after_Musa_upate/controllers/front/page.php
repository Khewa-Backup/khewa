<?php
/**
* 2022 - Keyrnel SARL
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
* @copyright 2022 - Keyrnel SARL
* @license   commercial
* International Registered Trademark & Property of Keyrnel SARL
*/
class ThegiftcardPageModuleFrontController extends ModuleFrontController
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var Category
     */
    public $category;

    /**
     *  @var Thegiftcard
     */
    public $module;

    public function display()
    {
        $scope = $this->context->smarty->createData($this->context->smarty);
        $scope->assign([
            'errors' => $this->errors,
            'request_uri' => Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])),
        ]);
        $tpl_errors = version_compare(_PS_VERSION_, '1.7', '<') ? _PS_THEME_DIR_ . '/errors.tpl' : '_partials/form-errors.tpl';
        $errors_rendered = $this->context->smarty->createTemplate($tpl_errors, $scope)->fetch();

        $this->context->smarty->assign([
            'errors_rendered' => $errors_rendered,
            'errors_nb' => (int) count($this->errors),
            'token' => Tools::getToken(false),
            'attribute_anchor_separator' => Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'),
            'currentUrl' => $this->context->link->getModuleLink('thegiftcard', 'page'),
            'currencySign' => $this->context->currency->sign,
            'ajax_allowed' => 1 == (int) (Configuration::get('PS_BLOCK_CART_AJAX')) ? true : false,
        ]);

        $template = version_compare(_PS_VERSION_, '1.7', '>=') ? 'module:thegiftcard/views/templates/front/layout.tpl' : 'giftcard.tpl';
        $this->setTemplate($template);

        return parent::display();
    }

    public function initContent()
    {
        $id_currency = (int) $this->context->currency->id;
        $this->product = new Product((int) Configuration::get('GIFTCARD_PROD_' . $id_currency), false, $this->context->language->id, $this->context->shop->id);

        if (Validate::isLoadedObject($this->product) && $this->product->isAssociatedToShop()) {
            if (Tools::getValue('ajax')) {
                if ('getCombination' == Tools::getValue('action')) {
                    $this->displayAjaxGetCombination();
                }

                if ('generatePdf' == Tools::getValue('action')) {
                    $this->displayAjaxGeneratePdf();
                }

                if ('refresh' == Tools::getValue('action')) {
                    $this->displayAjaxRefresh();
                }
            }

            parent::initContent();

            // Assign template vars related to the customization
            $this->assignCustomization();

            // Assign template vars related to the category + execute hooks related to the category
            $this->assignCategory();

            // Assign attribute groups to the template
            $this->assignAttributesGroups();

            $this->context->smarty->assign([
                'giftcard' => $this->product,
                'active' => Configuration::get('GIFTCARD_ACTIVE_' . (int) $this->product->id),
                'pitch' => (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $this->product->id),
                'custom_amount_from' => (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $this->product->id),
                'custom_amount_to' => (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $this->product->id),
                'isCustomAmountFeatureActive' => (int) GiftCardModel::isCustomAmountFeatureActive($this->product->id),
                'isPDFFeatureActive' => (int) GiftCardModel::isPDFFeatureActive(),
            ]);
        } else {
            Tools::redirect('index.php?controller=404');
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/front/design.css', 'all');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/front/product.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tools/bootstrap.js');
        $this->addJqueryUI('ui.datepicker');

        if (version_compare(_PS_VERSION_, '1.7', '>=')
            && file_exists(_PS_ROOT_DIR_ . '/js/jquery/ui/i18n/jquery.ui.datepicker-' . Context::getContext()->language->iso_code . '.js')
        ) {
            $this->registerJavascript('jquery-ui-datepicker-i18n', '/js/jquery/ui/i18n/jquery.ui.datepicker-' . Context::getContext()->language->iso_code . '.js', ['position' => 'bottom', 'priority' => 100]);
        }

        $this->addjQueryPlugin('fancybox');
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->addjQueryPlugin('growl', null, false);
        } else {
            $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/tools/jquery.growl.js');
        }

        return true;
    }

    /**
     * Assign customization fields
     */
    protected function assignCustomization()
    {
        $cronjobs = (bool) Configuration::get('GIFTCARD_CRON_ACTIVE');
        $customization_fields = $this->product->customizable ? $this->product->getCustomizationFields($this->context->language->id, $this->context->shop->id) : false;
        if (is_array($customization_fields)) {
            foreach ($customization_fields as $key => $customization_field) {
                if ($customization_field['id_customization_field'] == Configuration::get('GIFTCARD_CUST_DATE_' . (int) $this->product->id) && !$cronjobs) {
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
        $id_category = (int) Configuration::get('GIFTCARD_CAT');
        $this->category = new Category((int) $id_category, (int) $this->context->language->id);

        $imageType = version_compare(_PS_VERSION_, '1.7', '>=')
            ? ImageType::getFormattedName('category')
            : ImageType::getFormatedName('category')
        ;

        if (Validate::isLoadedObject($this->category)) {
            $this->context->smarty->assign([
                'category' => $this->category,
                'categorySize' => Image::getSize($imageType),
            ]);
        }
    }

    /**
     * Assign template vars related to attribute groups
     */
    protected function assignAttributesGroups()
    {
        $selected_amount = null;
        $template_vars = [];
        $template_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'));
        if (Validate::isLoadedObject($template_group_object)) {
            $images_id = [];
            $product_images = Image::getImages($this->context->language->id, $this->product->id);
            foreach ($product_images as $image) {
                $images_id[] = (int) $image['id_image'];
            }

            $images = [];
            $template_attributes = AttributeGroup::getAttributes((int) $this->context->language->id, (int) $template_group_object->id);
            foreach ($template_attributes as $template_attribute) {
                $image_obj = new Image((int) $template_attribute['name']);
                if (!Validate::isLoadedObject($image_obj) || !in_array($image_obj->id, $images_id)) {
                    continue;
                }

                $image_lang = GiftCardModel::getGiftCardImageLang((int) $image_obj->id);
                if ($image_lang != $this->context->language->id && 0 != $image_lang) {
                    continue;
                }

                $default_amount = GiftCardModel::getAmount($image_obj->id);
                if ($image_obj->cover) {
                    $selected_amount = $default_amount['amount'];
                }

                $image = [];
                $image['thumbnail'] = $this->getThumbnail($image_obj);
                $image['attribute_value'] = (int) $image_obj->id;
                $image['position'] = (int) $image_obj->position;
                $image['legend'] = $image_obj->legend[$this->context->language->id];
                $image['tags'] = GiftCardModel::getTagsByIdImage((int) $image_obj->id, $this->context->language->id);
                $image['cover'] = $image_obj->cover;
                $image['auto'] = $default_amount && $default_amount['auto'] ? $default_amount['amount'] : false;
                $images[] = $image;
            }

            $tags = [$this->module->l('All', 'page') => count($images)];
            $tag_list = GiftCardModel::getTags((int) $this->context->language->id, $images_id);
            if (is_array($tag_list) && count($tag_list)) {
                $tags = $tags + $tag_list;
            }

            if (count($images)) {
                $positions = array_column($images, 'position');
                array_multisort($positions, SORT_ASC, $images);
                $template_vars = [
                    'id_attribute_group' => (int) $template_group_object->id,
                    'public_group_name' => $template_group_object->public_name[(int) $this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace([',', '.'], '-', $template_group_object->public_name[(int) $this->context->language->id]))),
                    'attributes' => $images,
                    'tags' => $tags,
                ];
            }
        }

        $amount_vars = [];
        $amount_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
        if (Validate::isLoadedObject($amount_group_object)) {
            $amounts = [];
            $fixed_amount_list = explode(',', Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $this->product->id));
            $amount_attributes = AttributeGroup::getAttributes((int) $this->context->language->id, (int) $amount_group_object->id);
            foreach ($amount_attributes as $amount_attribute) {
                if (!in_array($amount_attribute['name'], $fixed_amount_list)) {
                    continue;
                }

                $amount = [];
                $amount['attribute_value'] = (int) $amount_attribute['name'];
                $amount['position'] = (int) $amount_attribute['position'];
                $amounts[] = $amount;
            }

            if (count($amounts)) {
                $attribute_values = array_column($amounts, 'attribute_value');
                array_multisort($attribute_values, SORT_ASC, $amounts);
                $amount_vars = [
                    'id_attribute_group' => (int) $amount_group_object->id,
                    'public_group_name' => $amount_group_object->public_name[(int) $this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace([',', '.'], '-', $amount_group_object->public_name[(int) $this->context->language->id]))),
                    'attributes' => $amounts,
                ];
            }
        }

        $this->context->smarty->assign([
            'template_vars' => (count($template_vars)) ? $template_vars : false,
            'amount_vars' => (count($amount_vars)) ? $amount_vars : false,
            'default_amount' => $selected_amount,
        ]);
    }

    public function getThumbnail($image)
    {
        $key = (int) $this->product->id . '-' . (int) $image->id;

        if (file_exists(_PS_PROD_IMG_DIR_ . $image->getImgPath() . '-thumbnail.jpg')) {
            return Context::getContext()->link->getImageLink($this->product->link_rewrite, $key, 'thumbnail');
        }

        return Context::getContext()->link->getImageLink($this->product->link_rewrite, $key);
    }

    public function getImagesList()
    {
        $images_list = [];

        $product_images = Image::getImages($this->context->language->id, $this->product->id);
        foreach ($product_images as $image) {
            $images_list[] = (int) $image['id_image'];
        }

        return $images_list;
    }

    public function getAmountsList()
    {
        $amounts_list = [];

        if (GiftCardModel::isCustomAmountFeatureActive($this->product->id)) {
            $custom_amount_from = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $this->product->id);
            $custom_amount_to = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $this->product->id);
            $pitch = Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $this->product->id);
            for ($i = $custom_amount_from; $i <= $custom_amount_to; $i = $i + $pitch) {
                $amounts_list[] = $i;
            }
        } else {
            $fixed_amounts = Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $this->product->id);
            $amounts_list = array_map('intval', explode(',', $fixed_amounts));
        }

        return $amounts_list;
    }

    /**
     * @param array $attributes
     * @param int $id_attribute_group
     * @param array $list
     * @param bool $get_value
     *
     * @return string|int|bool
     */
    public function filterAttribute($attributes, $id_attribute_group, $list, $get_value = false)
    {
        $result = false;
        $existing_attributes = AttributeGroup::getAttributes($this->context->language->id, $id_attribute_group);

        foreach ($attributes as $attribute) {
            if ($attribute['id_attribute_group'] == $id_attribute_group
                && in_array($attribute['value'], $list)
            ) {
                foreach ($existing_attributes as $existing_attribute) {
                    if (isset($existing_attribute['name'])
                        && $existing_attribute['name'] == $attribute['value']
                    ) {
                        $result = $get_value ? $existing_attribute['name'] : $existing_attribute['id_attribute'];
                        break 2;
                    }
                }
            }
        }

        return $result;
    }

    public function displayAjaxGetCombination()
    {
        if (!Context::getContext()->cart->id) {
            if (Context::getContext()->cookie->id_guest) {
                $guest = new Guest((int) Context::getContext()->cookie->id_guest);
                Context::getContext()->cart->mobile_theme = $guest->mobile_theme;
            }
            Context::getContext()->cart->add();
            if (Validate::isLoadedObject($this->context->cart)) {
                Context::getContext()->cookie->id_cart = (int) Context::getContext()->cart->id;
            }
        }

        if (!Configuration::get('GIFTCARD_ACTIVE_' . (int) $this->product->id) || !$this->product->active) {
            exit(json_encode(['error' => $this->module->l('The gift card is not active for the moment.', 'page')]));
        }

        $sending_method = (int) Tools::getValue('sendingMethod');
        if (GiftCardModel::PRINT_AT_HOME != $sending_method && GiftCardModel::SEND_TO_FRIEND != $sending_method) {
            exit(json_encode(['error' => $this->module->l('Please select a sending method', 'page')]));
        }

        // get or create combination with attributes
        $attributes = Tools::getValue('attributes');
        if (!isset($attributes)
            || !is_array($attributes)
            || empty($attributes)
        ) {
            exit(json_encode(['error' => $this->module->l('Please select a template and an amount', 'page')]));
        }

        if (!($id_template = $this->filterAttribute($attributes, (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'), $this->getImagesList()))) {
            exit(json_encode(['error' => $this->module->l('Please select a template', 'page')]));
        }

        if (!($id_amount = $this->filterAttribute($attributes, (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'), $this->getAmountsList()))) {
            exit(json_encode(['error' => $this->module->l('Please select an amount', 'page')]));
        }

        if (!($id_combination = $this->module->generateCombination($this->product->id, [$id_template, $id_amount]))) {
            exit(json_encode(['error' => $this->module->l('Error while creating the gift card with selected template and amount', 'page')]));
        }

        $combination = new Combination($id_combination);
        if (!Validate::isLoadedObject($combination)) {
            exit(json_encode(['error' => $this->module->l('Unable to load the combination object', 'page')]));
        }

        $giftcard_vars = [
            'id_combination' => (int) $combination->id,
        ];

        // get customization fields if user chose to send the gift card to a friend
        if (GiftCardModel::SEND_TO_FRIEND == $sending_method) {
            $field_ids = $this->product->getCustomizationFieldIds();

            $authorized_text_fields = [];
            foreach ($field_ids as $field_id) {
                if (Product::CUSTOMIZE_TEXTFIELD == $field_id['type']) {
                    $authorized_text_fields[(int) $field_id['id_customization_field']] = 'textField' . (int) $field_id['id_customization_field'];
                }
            }

            $indexes = array_flip($authorized_text_fields);
            foreach (Tools::getValue('customizationData') as $field_name => $value) {
                $value = trim($value);
                if (in_array($field_name, $authorized_text_fields) && '' != $value) {
                    if ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_DATE_' . (int) $this->product->id)
                        && (!Validate::isDate($value = date('Y-m-d', strtotime($value))) || date('Y-m-d') > $value)
                    ) {
                        exit(json_encode(['error' => $this->module->l('Please select a valid date', 'page')]));
                    } elseif ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $this->product->id) && !Validate::isEmail($value)) {
                        exit(json_encode(['error' => $this->module->l('Please fill a valid email', 'page')]));
                    } elseif (!Validate::isMessage($value)) {
                        exit(json_encode(['error' => $this->module->l('An error occurred while attempting to save this data.', 'page')]));
                    } else {
                        Context::getContext()->cart->_addCustomization($this->product->id, $giftcard_vars['id_combination'], $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value, 0);
                    }
                } else {
                    exit(json_encode(['error' => $this->module->l('Please fill all the fields.', 'page')]));
                }
            }

            $customization_datas = Context::getContext()->cart->getProductCustomization($this->product->id, null, true);
            if (empty($customization_datas)) {
                $combination->delete();
                exit(json_encode(['error' => $this->module->l('An error occurred while attempting to get the customized data.', 'page')]));
            }

            $giftcard_vars = array_merge($giftcard_vars, [
                'id_customization' => (int) $customization_datas[0]['id_customization'],
            ]);
        }

        exit(json_encode([
            'error' => false,
            'giftcard_vars' => $giftcard_vars,
        ]));
    }

    public function displayAjaxGeneratePdf()
    {
        if (!GiftCardModel::isPDFFeatureActive()) {
            exit(json_encode(['error' => $this->module->l('The preview feature is not active for the moment.', 'page')]));
        }

        if (!Configuration::get('GIFTCARD_ACTIVE_' . (int) $this->product->id) || !$this->product->active) {
            exit(json_encode(['error' => $this->module->l('The gift card is not active for the moment.', 'page')]));
        }

        $sending_method = (int) Tools::getValue('sendingMethod');
        if (GiftCardModel::PRINT_AT_HOME != $sending_method && GiftCardModel::SEND_TO_FRIEND != $sending_method) {
            exit(json_encode(['error' => $this->module->l('Please select a sending method', 'page')]));
        }

        // get or create combination with attributes
        $attributes = Tools::getValue('attributes');
        if (!isset($attributes)
            || !is_array($attributes)
            || empty($attributes)
        ) {
            exit(json_encode(['error' => $this->module->l('Please select a template and an amount', 'page')]));
        }

        if (!($id_image = $this->filterAttribute($attributes, (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'), $this->getImagesList(), true))) {
            exit(json_encode(['error' => $this->module->l('Please select a template', 'page')]));
        }

        if (!($amount = $this->filterAttribute($attributes, (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'), $this->getAmountsList(), true))) {
            exit(json_encode(['error' => $this->module->l('Please select an amount', 'page')]));
        }

        $customization = [];
        if (GiftCardModel::SEND_TO_FRIEND == $sending_method) {
            $field_ids = $this->product->getCustomizationFieldIds();

            $authorized_text_fields = [];
            foreach ($field_ids as $field_id) {
                if (Product::CUSTOMIZE_TEXTFIELD == $field_id['type']) {
                    $authorized_text_fields[(int) $field_id['id_customization_field']] = 'textField' . (int) $field_id['id_customization_field'];
                }
            }

            $indexes = array_flip($authorized_text_fields);
            foreach (Tools::getValue('customizationData') as $field_name => $value) {
                $value = trim($value);
                if (in_array($field_name, $authorized_text_fields) && '' != $value) {
                    if ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_DATE_' . (int) $this->product->id)
                        && (!Validate::isDate($value = date('Y-m-d', strtotime($value))) || date('Y-m-d') > $value)
                    ) {
                        exit(json_encode(['error' => $this->module->l('Please select a valid date', 'page')]));
                    } elseif ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $this->product->id) && !Validate::isEmail($value)) {
                        exit(json_encode(['error' => $this->module->l('Please fill a valid email', 'page')]));
                    } elseif (!Validate::isMessage($value)) {
                        exit(json_encode(['error' => $this->module->l('An error occurred while attempting to save this data.', 'page')]));
                    } else {
                        switch ($indexes[$field_name]) {
                            case Configuration::get('GIFTCARD_CUST_NAME_' . (int) $this->product->id):
                                $key = 'name';
                                break;
                            case Configuration::get('GIFTCARD_CUST_CONTENT_' . (int) $this->product->id):
                                $key = 'content';
                                break;
                            case Configuration::get('GIFTCARD_CUST_DATE_' . (int) $this->product->id):
                                $key = 'date';
                                break;
                            default:
                                $key = null;
                                break;
                        }

                        if ($key) {
                            $customization[$key] = $value;
                        }
                    }
                } else {
                    exit(json_encode(['error' => $this->module->l('Please fill all the fields.', 'page')]));
                }
            }
        }

        $image = new Image($id_image);
        $customer = Validate::isLoadedObject(Context::getContext()->customer)
            ? Context::getContext()->customer->firstname . ' ' . Context::getContext()->customer->lastname
            : $this->module->l('Firstname', 'page') . ' ' . $this->module->l('Name', 'page');

        $date_from = count($customization) && isset($customization['date']) ? $customization['date'] : date('Y-m-d');
        $expiration = date('Y-m-d', strtotime('+' . Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' .
            Configuration::get('GIFTCARD_EXPIRATION_DATE'), strtotime($date_from)));

        $pdf_vars = [
            'module' => $this->module,
            'lang_id' => Context::getContext()->language->id,
            'shop_id' => Context::getContext()->shop->id,
            'shop_name' => Configuration::get('PS_SHOP_NAME', null, null, Context::getContext()->shop->id),
            'shop_url' => Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, Context::getContext()->shop->id),
            'customer' => $customer,
            'image_url' => version_compare(_PS_VERSION_, '1.7', '>=')
                ? Tools::getShopProtocol() . Tools::getMediaServer(_PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg') . _PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg'
                : _PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.jpg',
            'image_width' => Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH'),
            'image_height' => Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT'),
            'giftcard_amount' => Tools::displayPrice((float) $amount, (int) Context::getContext()->currency->id),
            'giftcard_code' => 'xxxx-xxxx-xxxx',
            'giftcard_expiration' => Tools::displayDate($expiration),
            'giftcard_expiration_date' => Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' . $this->module->getExpirationDate(),
        ];

        if (count($customization)
            && (isset($customization['name']) && !empty($customization['name']))
            && (isset($customization['content']) && !empty($customization['content']))) {
            $pdf_vars = array_merge($pdf_vars, [
                'beneficiary' => $customization['name'],
                'giftcard_message' => $customization['content'],
            ]);
        }

        $pdf = new PDF([$pdf_vars], 'GiftCardCore', $this->context->smarty);
        $output = $pdf->render(false);
        $pdfBase64 = base64_encode($output);

        exit(json_encode([
            'error' => false,
            'url' => 'data:application/pdf;base64,' . $pdfBase64,
        ]));
    }

    public function displayAjaxRefresh()
    {
        exit(1);
    }
}
