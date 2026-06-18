<?php

/**
 * 2023 - Keyrnel
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
 * @copyright 2023 - Keyrnel
 * @license   commercial
 * International Registered Trademark & Property of Keyrnel
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'thegiftcard/models/GiftCardModel.php';
require_once _PS_MODULE_DIR_ . 'thegiftcard/models/GiftCardUploader.php';
require_once _PS_MODULE_DIR_ . 'thegiftcard/models/HTMLTemplateGiftCard.php';

class Thegiftcard extends Module
{
    private $_html = '';
    private $_post_errors = [];
    private $attribute_class;

    public function __construct()
    {
        $this->name = 'thegiftcard';
        $this->tab = 'front_office_features';
        $this->version = '1.18.1';
        $this->author = 'Keyrnel';
        $this->module_key = '09a30c9e46963b4cea1943096439e8ea';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('The gift card');
        $this->description = $this->l('A customer stuck for inspiration ? The module The Gift Card gives the possibility of offering the perfect gift for all occasions. Attract new customers and boost your sales by suggesting customizable gift cards. Very intuitive, the module is already preconfigured for immediate use !');
        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/cron_thegiftcard.php')) {
            $this->warning = $this->l('The script for sending scheduled emails has been moved to a front controller. Please update your cron task with the url provide within the tab "Gift card generator".');
        }

        $this->attribute_class = $this->getAttributeClass();
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('displayBackOfficeTop')
            || !$this->registerHook('actionDeleteProductInCartAfter')
            || !$this->registerHook('actionAfterDeleteProductInCart')
            || !$this->registerHook('actionObjectCartRuleDeleteAfter')
            || !$this->registerHook('actionObjectCartRuleAddAfter')
            || !$this->registerHook('actionObjectCartRuleUpdateAfter')
            || !$this->registerHook('actionCronJob')
            || !$this->registerHook('actionShopDataDuplication')
            || !$this->registerHook('filterProductSearch')
            || !$this->registerHook('actionGetProductPropertiesBefore')
            || !$this->registerHook('actionOrderStatusUpdate')) {
            return false;
        }

        // Install SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/install.php';
        if (!$this->executeSql($sql)) {
            return false;
        }

        $languages = Language::getLanguages();
        $shops = Shop::getShops(true, null, true);

        // Install Conf
        Configuration::updateGlobalValue('GIFTCARD_EXPIRATION_TIME', 1);
        Configuration::updateGlobalValue('GIFTCARD_EXPIRATION_DATE', 'year');
        Configuration::updateGlobalValue('GIFTCARD_DISPLAY_TOPMENU', 0);
        Configuration::updateGlobalValue('GIFTCARD_EMAIL_IMG_WIDTH', '400');
        Configuration::updateGlobalValue('GIFTCARD_EMAIL_IMG_HEIGHT', '300');
        Configuration::updateGlobalValue('GIFTCARD_CART_RULE', 1);
        Configuration::updateGlobalValue('GIFTCARD_CART_RULE_BUY', 1);
        Configuration::updateGlobalValue('GIFTCARD_USE_CART_RULE', 0);
        Configuration::updateGlobalValue('GIFTCARD_USE_CACHE', 0);
        Configuration::updateGlobalValue('GIFTCARD_PDF_ATTACHMENT', 1);
        Configuration::updateGlobalValue('GIFTCARD_CRON_ACTIVE', 0);
        Configuration::updateGlobalValue('GIFTCARD_USE_CUSTOMIZATION', 1);

        $hashMethod = version_compare(_PS_VERSION_, '1.7', '>') ? 'hash' : 'encrypt';
        Configuration::updateGlobalValue('GIFTCARD_CRON_TOKEN', Tools::$hashMethod('thegiftcard/cron'));

        foreach ($shops as $shop_id) {
            $shop_group_id = Shop::getGroupFromShop($shop_id);
            $core_confs = [
                ['name' => 'PS_VIRTUAL_PROD_FEATURE_ACTIVE', 'value' => '1'],
                ['name' => 'PS_COMBINATION_FEATURE_ACTIVE', 'value' => '1'],
            ];

            foreach ($core_confs as $conf) {
                Configuration::updateValue(
                    $conf['name'],
                    $conf['value'],
                    false,
                    $shop_group_id,
                    $shop_id
                );
            }
        }

        $values = [];
        foreach ($languages as $language) {
            $values['GIFTCARD_EMAIL_SUBJECT_PRINT'] = [$language['id_lang'] => 'fr' == $language['iso_code'] ?
                'Votre carte cadeau' : 'Your gift card', ];
            $values['GIFTCARD_EMAIL_SUBJECT_FRIEND'] = [$language['id_lang'] => 'fr' == $language['iso_code'] ?
                '%s vous offre une carte cadeau' : '%s offers you a gift card', ];

            foreach ($values as $key => $value) {
                Configuration::updateGlobalValue($key, $value);
            }
        }

        // Install Category
        $category = new Category();
        foreach ($languages as $language) {
            if ('fr' == $language['iso_code']) {
                $category->name[(int) $language['id_lang']] = 'Cartes cadeaux';
                $category->link_rewrite[(int) $language['id_lang']] = 'cartes-cadeaux';
            } else {
                $category->name[(int) $language['id_lang']] = 'Gift cards';
                $category->link_rewrite[(int) $language['id_lang']] = 'gift-cards';
            }
        }
        $category->id_parent = (int) Configuration::get('PS_HOME_CATEGORY');
        $category->is_root_category = false;
        $category->level_depth = (int) $category->id_parent + 1;
        $category->active = false;
        $category->id_shop_list = $shops;
        if ($category->add()) {
            Configuration::updateGlobalValue('GIFTCARD_CAT', $category->id);
            Configuration::updateGlobalValue('GIFTCARD_CAT_ASSOCIATION', $category->id);
        }

        // Install Attribute Group
        foreach (GiftCardModel::$attributes_group as $attribute_group) {
            $attribute_group_obj = new AttributeGroup();
            foreach ($languages as $language) {
                if (isset($attribute_group['value'][$language['iso_code']]) && !empty($attribute_group['value'][$language['iso_code']])) {
                    $attribute_group_obj->name[(int) $language['id_lang']] = $attribute_group['value'][$language['iso_code']];
                    $attribute_group_obj->public_name[(int) $language['id_lang']] = $attribute_group['value'][$language['iso_code']];
                } else {
                    $attribute_group_obj->name[(int) $language['id_lang']] = $attribute_group['value']['en'];
                    $attribute_group_obj->public_name[(int) $language['id_lang']] = $attribute_group['value']['en'];
                }
            }
            $attribute_group_obj->group_type = 'radio';
            $attribute_group_obj->id_shop_list = $shops;
            if ($attribute_group_obj->add()) {
                Configuration::updateGlobalValue('GIFTCARD_ATTRGROUP_' . Tools::strtoupper($attribute_group['name']), $attribute_group_obj->id);
            }
        }

        // Install Product
        $currency_installed = [];
        foreach ($shops as $id_shop) {
            $currencies = Currency::getCurrenciesByIdShop((int) $id_shop);
            foreach ($currencies as $currency) {
                if (in_array($currency['id_currency'], $currency_installed) || $currency['deleted'] || !$currency['active']) {
                    continue;
                }

                $product = new Product();
                foreach ($languages as $language) {
                    if ('fr' == $language['iso_code']) {
                        $product->name[(int) $language['id_lang']] = 'La carte cadeau';
                        $product->link_rewrite[(int) $language['id_lang']] = 'la-carte-cadeau-' . $currency['iso_code'];
                    } else {
                        $product->name[(int) $language['id_lang']] = 'The gift card';
                        $product->link_rewrite[(int) $language['id_lang']] = 'the-gift-card-' . $currency['iso_code'];
                    }
                }
                $product->id_category_default = (int) Configuration::get('GIFTCARD_CAT');
                $product->active = true;
                $product->customizable = 1;
                $product->is_virtual = true;
                $product->reference = 'gift_card_' . $currency['iso_code'];
                $product->id_tax_rules_group = 0;
                $product->available_date = date('Y-m-d');
                $product->text_fields = count(GiftCardModel::$customizations);
                $product->id_shop_list = GiftCardModel::getShopsByIdCurrency($currency['id_currency']);
                $product->add();

                $product->updateCategories([(int) Configuration::get('GIFTCARD_CAT')]);

                Configuration::updateGlobalValue('GIFTCARD_PROD_' . (int) $currency['id_currency'], (int) $product->id);
                Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_FEATURE_' . (int) $product->id, 1);
                Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id, '10');
                Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id, '50');
                Configuration::updateGlobalValue('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id, '10,20,30,40,50');
                Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id, '1');
                Configuration::updateGlobalValue('GIFTCARD_ACTIVE_' . (int) $product->id, '1');

                // Install product customization
                foreach (GiftCardModel::$customizations as $customization) {
                    if (!($id_customization_field = GiftCardModel::addCustomizationField($product->id))) {
                        continue;
                    }

                    $customizationFieldLangData = [];
                    foreach ($shops as $id_shop) {
                        $customizationFieldLangData[(int) $id_shop] = [];
                        foreach ($languages as $language) {
                            $value = isset($customization['value'][$language['iso_code']]) && !empty($customization['value'][$language['iso_code']]) ? $customization['value'][$language['iso_code']] : $customization['value']['en'];
                            $customizationFieldLangData[(int) $id_shop][(int) $language['id_lang']] = $value;
                        }
                    }

                    if (!GiftCardModel::addCustomizationFieldLang($id_customization_field, $customizationFieldLangData)) {
                        continue;
                    }

                    Configuration::updateGlobalValue('GIFTCARD_CUST_' . Tools::strtoupper($customization['name']) . '_' . $product->id, $id_customization_field);
                }

                $currency_installed[] = (int) $currency['id_currency'];
            }
        }

        Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');

        // Install Meta
        $meta = new Meta();
        $meta->page = 'module-' . $this->name . '-page';
        $meta->configurable = 1;
        foreach ($languages as $language) {
            $meta->title[(int) $language['id_lang']] = $category->name[(int) $language['id_lang']];
            $meta->url_rewrite[(int) $language['id_lang']] = $category->link_rewrite[(int) $language['id_lang']];
        }
        $meta->add();

        if ($meta->id && version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
            $themes = Theme::getThemes();
            GiftCardModel::addMeta($meta->id, $themes);
        }

        // load init
        if (!$this->init()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $shops = Shop::getShops(true, null, true);

        // Uninstall Product
        $currency_uninstalled = [];
        foreach ($shops as $id_shop) {
            $currencies = Currency::getCurrenciesByIdShop((int) $id_shop);
            foreach ($currencies as $currency) {
                if (in_array($currency['id_currency'], $currency_uninstalled)) {
                    continue;
                }

                $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
                if (Validate::isLoadedObject($product)) {
                    $product->id_shop_list = GiftCardModel::getShopsByIdCurrency((int) $currency['id_currency']);
                    $product->delete();

                    Configuration::deleteByName('GIFTCARD_PROD_' . (int) $currency['id_currency']);
                    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id);
                    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id);
                    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id);
                    Configuration::deleteByName('GIFTCARD_AMOUNT_CUSTOM_FEATURE_' . (int) $product->id);
                    Configuration::deleteByName('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id);

                    foreach (GiftCardModel::$customizations as $customization) {
                        Configuration::deleteByName('GIFTCARD_CUST_' . Tools::strtoupper($customization['name']) . '_' . (int) $product->id);
                    }
                }

                $currency_uninstalled[] = (int) $currency['id_currency'];
            }
        }

        // Uninstall Attribute Group
        $attribute_group = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
        if (Validate::isLoadedObject($attribute_group)) {
            $attribute_group->id_shop_list = $shops;
            $attribute_group->delete();
        }

        $attribute_group = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'));
        if (Validate::isLoadedObject($attribute_group)) {
            $attribute_group->id_shop_list = $shops;
            $attribute_group->delete();
        }

        // Uninstall Category
        $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
        if (Validate::isLoadedObject($category)) {
            $category->id_shop_list = $shops;
            $category->delete();
        }

        // Uninstall Conf
        Configuration::deleteByName('GIFTCARD_ATTRGROUP_AMOUNT');
        Configuration::deleteByName('GIFTCARD_ATTRGROUP_TEMPLATE');
        Configuration::deleteByName('GIFTCARD_CAT');
        Configuration::deleteByName('GIFTCARD_DISPLAY_TOPMENU');
        Configuration::deleteByName('GIFTCARD_EXPIRATION_TIME');
        Configuration::deleteByName('GIFTCARD_EXPIRATION_DATE');
        Configuration::deleteByName('GIFTCARD_EMAIL_IMG_WIDTH');
        Configuration::deleteByName('GIFTCARD_EMAIL_IMG_HEIGHT');
        Configuration::deleteByName('GIFTCARD_EMAIL_SUBJECT_PRINT');
        Configuration::deleteByName('GIFTCARD_EMAIL_SUBJECT_FRIEND');
        Configuration::deleteByName('GIFTCARD_CART_RULE');
        Configuration::deleteByName('GIFTCARD_CART_RULE_BUY');
        Configuration::deleteByName('GIFTCARD_USE_CART_RULE');
        Configuration::deleteByName('GIFTCARD_USE_CACHE');
        Configuration::deleteByName('GIFTCARD_CRON_ACTIVE');
        Configuration::deleteByName('GIFTCARD_CRON_TOKEN');
        Configuration::deleteByName('GIFTCARD_USE_CUSTOMIZATION');

        // Uninstall Meta
        if ($metas = Meta::getMetaByPage('module-' . $this->name . '-page', (int) $this->context->language->id)) {
            $meta = new Meta((int) $metas['id_meta']);
            if ($meta->delete() && version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
                GiftCardModel::deleteMetaById($meta->id);
            }
        }

        // Disable cart rules generated by module
        $giftcards = GiftCardModel::getGiftcards();
        foreach ($giftcards as $giftcard) {
            $cart_rule = new CartRule((int) $giftcard['id_cart_rule']);
            if (!Validate::isLoadedObject($cart_rule)) {
                continue;
            }

            $cart_rule->active = false;
            $cart_rule->update();
        }

        // Uninstall SQL
        $sql = [];
        include dirname(__FILE__) . '/sql/uninstall.php';
        if (!$this->executeSql($sql)) {
            return false;
        }

        // Uninstall Module
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    protected function executeSql($sql = [])
    {
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        return true;
    }

    public function enable($force_all = false)
    {
        if (Module::isInstalled($this->name)) {
            $giftCardProducts = GiftCardModel::getGiftCardProducts(Shop::getContextListShopID(), true);
            GiftCardModel::updateGiftcardProductStatus(Shop::getContextListShopID(), $giftCardProducts, 1);
        }

        if (!parent::enable($force_all)) {
            return false;
        }

        return true;
    }

    public function disable($force_all = false)
    {
        if (Module::isInstalled($this->name)) {
            $giftCardProducts = GiftCardModel::getGiftCardProducts(Shop::getContextListShopID(), true);
            GiftCardModel::updateGiftcardProductStatus(Shop::getContextListShopID(), $giftCardProducts, 0);

            $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
            if (Validate::isLoadedObject($category)) {
                $category->active = false;
                $category->setFieldsToUpdate([
                    'active' => true,
                ]);
                $category->update();
            }

            $topmenu_module = version_compare(_PS_VERSION_, '1.7', '>=') ? 'ps_mainmenu' : 'blocktopmenu';
            $topmenu = Module::getInstanceByName($topmenu_module);
            if ($topmenu && $topmenu->active) {
                $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
                $shop_ids = Shop::getContextListShopID();
                foreach ($shop_ids as $shop_id) {
                    $shop_group_id = Shop::getGroupFromShop($shop_id);
                    $conf = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
                    $id_linksmenutop = $this->addTopMenuLink($shop_id);

                    if ($id_linksmenutop) {
                        $topmenu_class::remove($id_linksmenutop, $shop_id);
                        Configuration::updateValue(
                            'MOD_BLOCKTOPMENU_ITEMS',
                            (string) str_replace(['LNK' . $id_linksmenutop . ',', 'LNK' . $id_linksmenutop], '', $conf),
                            false,
                            $shop_group_id,
                            $shop_id
                        );
                    }
                }
                Configuration::updateValue('GIFTCARD_DISPLAY_TOPMENU', 0);
            }
        }

        if (!parent::disable($force_all)) {
            return false;
        }

        return true;
    }

    protected function init()
    {
        $languages = Language::getLanguages();
        $currencies = Currency::getCurrencies(false, true, true);
        $shops = Shop::getShops(true, null, true);

        // init category image banner
        $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
        if (!Validate::isLoadedObject($category)) {
            return false;
        }

        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/img/cat_banner.jpg')) {
            ImageManager::resize(_PS_MODULE_DIR_ . $this->name . '/views/img/cat_banner.jpg', _PS_CAT_IMG_DIR_ . $category->id . '.jpg', null, null, 'jpg');
            $images_types = ImageType::getImagesTypes('categories');
            $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

            foreach ($images_types as $image_type) {
                ImageManager::resize(
                    _PS_CAT_IMG_DIR_ . $category->id . '.jpg',
                    _PS_CAT_IMG_DIR_ . $category->id . '-' . stripslashes($image_type['name']) . '.jpg',
                    (int) $image_type['width'],
                    (int) $image_type['height']
                );

                if ($generate_hight_dpi_images) {
                    ImageManager::resize(
                        _PS_CAT_IMG_DIR_ . $category->id . '.jpg',
                        _PS_CAT_IMG_DIR_ . $category->id . '-' . stripslashes($image_type['name']) . '2x.jpg',
                        (int) $image_type['width'] * 2,
                        (int) $image_type['height'] * 2
                    );
                }
            }
        }

        foreach ($currencies as $currency) {
            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            // init gift card templates
            $templates = ['carte_cadeau', 'carte_cadeau_1'];
            foreach ($templates as $template) {
                if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $template . '.jpg')) {
                    $image = new Image();
                    $image->id_product = (int) $product->id;
                    $image->position = Image::getHighestPosition($product->id) + 1;
                    foreach ($languages as $language) {
                        $image->legend[(int) $language['id_lang']] = 'giftcard';
                    }

                    if (!Image::getCover($image->id_product)) {
                        $image->cover = true;
                    } else {
                        $image->cover = false;
                    }

                    $image->id_shop_list = $shops;
                    $image->add();
                    GiftCardModel::addGiftCardImageLang((int) $image->id, 0);

                    $new_path = $image->getPathForCreation();

                    ImageManager::resize(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $template . '.jpg', $new_path . '.jpg', null, null, 'jpg');
                    $images_types = ImageType::getImagesTypes('products');
                    $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

                    foreach ($images_types as $imageType) {
                        ImageManager::resize(
                            $new_path . '.jpg',
                            $new_path . '-' . stripslashes($imageType['name']) . '.jpg',
                            $imageType['width'],
                            $imageType['height']
                        );

                        if ($generate_hight_dpi_images) {
                            ImageManager::resize(
                                $new_path . '.jpg',
                                $new_path . '-' . stripslashes($imageType['name']) . '2x.jpg',
                                (int) $imageType['width'] * 2,
                                (int) $imageType['height'] * 2
                            );
                        }
                    }

                    GiftCardModel::createThumbnail(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $template . '.jpg', $new_path . '-thumbnail.jpg', 300);

                    if ($generate_hight_dpi_images) {
                        GiftCardModel::createThumbnail(_PS_MODULE_DIR_ . $this->name . '/views/img/' . $template . '.jpg', $new_path . '-thumbnail2x.jpg', 600);
                    }

                    $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE');
                    $obj = new $this->attribute_class();
                    $obj->id_attribute_group = (int) $id_attribute_group;
                    foreach ($languages as $language) {
                        $obj->name[(int) $language['id_lang']] = $image->id;
                    }
                    $obj->position = $this->attribute_class::getHigherPosition((int) $id_attribute_group) + 1;
                    $obj->id_shop_list = $shops;
                    $obj->add();
                    $obj->cleanPositions((int) $id_attribute_group, false);

                    GiftCardModel::addAmount($image->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id), false, 0, 0);
                }
            }

            // init gift card amounts
            $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT');
            $custom_amount_from = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id);
            $custom_amount_to = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id);
            $fixed_amounts = Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id);
            $pitch = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id);
            $custom_list = [];

            $fixed_list = array_map('intval', explode(',', $fixed_amounts));
            for ($i = $custom_amount_from; $i <= $custom_amount_to; $i = $i + $pitch) {
                $custom_list[] = $i;
            }

            $list_add = array_merge($custom_list, $fixed_list);
            foreach ($list_add as $amount) {
                if (GiftCardModel::isAttribute((int) $id_attribute_group, $amount, $this->context->language->id) || 0 == $amount) {
                    continue;
                }

                $obj = new $this->attribute_class();
                $obj->id_attribute_group = (int) $id_attribute_group;
                foreach ($languages as $language) {
                    $obj->name[(int) $language['id_lang']] = (int) $amount;
                }
                $obj->position = $this->attribute_class::getHigherPosition((int) $id_attribute_group) + 1;
                $obj->id_shop_list = $shops;
                $obj->add();
                $obj->cleanPositions((int) $id_attribute_group, false);
            }

            // init combinations
            $attributes = GiftCardModel::getDefaultAttributes($product->id);
            $id_combination = $this->generateCombination($product->id, $attributes, $currency['id_currency'], $shops);
            GiftCardModel::setDefaultAttribute($product->id, $id_combination, $shops);
        }

        // init mails
        $path = _PS_MODULE_DIR_ . $this->name . '/mails/en';
        foreach ($languages as $language) {
            $dest = _PS_MODULE_DIR_ . $this->name . '/mails/' . $language['iso_code'];
            if (file_exists($dest)) {
                continue;
            }

            if (!mkdir($dest, 0777, true)) {
                continue;
            }

            $files = scandir($path);
            foreach ($files as $file) {
                if ('.' == $file || '..' == $file) {
                    continue;
                }

                copy($path . '/' . $file, $dest . '/' . $file);
            }
        }

        return true;
    }

    public function getContent()
    {
        if (Tools::getValue('ajax') && !empty($action = Tools::getValue('action')) && method_exists($this, 'ajaxProcess' . $action)) {
            return $this->{'ajaxProcess' . $action}();
        }

        if (Tools::getValue('deleteCatImage')) {
            $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
            if (Validate::isLoadedObject($category)) {
                $category->deleteImage(true);
            }

            Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'conf' => 7]));
        } elseif (Tools::isSubmit('submitGiftCard')) {
            $topmenu_module = version_compare(_PS_VERSION_, '1.7', '>=') ? 'ps_mainmenu' : 'blocktopmenu';
            $topmenu = Module::getInstanceByName($topmenu_module);
            if ($topmenu && $topmenu->active) {
                $this->updateTopMenuDisplay();
            }

            if ($this->updateCategory() && $this->updateMailForm() && $this->updateTranslations() && $this->updateGiftcard() && !count($this->_post_errors)) {
                Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'conf' => 4]));
            }
        } elseif (Tools::getValue('sendEmails')) {
            if ($this->hookActionCronJob()) {
                Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'conf' => 10]));
            } else {
                $this->_post_errors[] = $this->l('The emails cannot be sent');
            }
        } elseif (Tools::getValue('indexCurrencies')) {
            $error = false;
            $currencies_not_indexed = GiftCardModel::getCurrenciesNotIndexed();
            foreach ($currencies_not_indexed as $id_currency) {
                if (!$this->duplicateGiftCard((int) $id_currency)) {
                    $error = true;
                    break;
                }
            }

            if (!$error) {
                Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'conf' => 4]));
            } else {
                $this->_post_errors[] = $this->l('The currencies cannot be index');
            }
        } elseif (Tools::getValue('resendEmail')) {
            $id_giftcard = Tools::getValue('resendEmail');
            $giftcard = Db::getInstance()->getRow('SELECT gc.* FROM `' . _DB_PREFIX_ . 'giftcard` gc WHERE gc.`id_giftcard` = ' . (int) $id_giftcard);
            if (!$giftcard) {
                $this->_post_errors[] = $this->l('The gift card object cannot be loaded.');
            } else {
                if (!$giftcard['sent']) {
                    $this->_post_errors[] = $this->l('The gift card has not been sent yet.');
                }

                $cart_rule = new CartRule((int) $giftcard['id_cart_rule']);
                if (!Validate::isLoadedObject($cart_rule)) {
                    $this->_post_errors[] = $this->l('The cart rule object cannot be loaded.');
                }

                $order_detail = new OrderDetail((int) $giftcard['id_order_detail']);
                $order = new Order((int) $order_detail->id_order);
                $customer = new Customer((int) $order->id_customer);

                $langId = Validate::isLoadedObject($order) ? (int) $order->id_lang : (int) Context::getContext()->language->id;
                $shopId = Validate::isLoadedObject($order) ? (int) $order->id_shop : (int) Context::getContext()->shop->id;

                if (!count($this->_post_errors)) {
                    if ($this->sendEmail($langId, $customer, $cart_rule, (int) $giftcard['id_image'], (int) $giftcard['id_customization'], $shopId)) {
                        Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'conf' => 10]));
                    } else {
                        $this->_post_errors[] = $this->l('The email has not been correctly sent.');
                    }
                }
            }
        }

        foreach ($this->_post_errors as $err) {
            $this->_html .= $this->displayError($err);
        }

        $this->context->controller->addJs(_MODULE_DIR_ . 'thegiftcard/views/js/tools/bootstrap.js');

        $this->_html .= $this->renderGiftCardForm();

        return $this->_html;
    }

    protected function assignGiftCardCreatorData()
    {
        $giftCardProduct = new Product(
            (int) Configuration::get('GIFTCARD_PROD_' . (int) $this->context->currency->id),
            false,
            $this->context->language->id,
            $this->context->shop->id
        );

        if (!Validate::isLoadedObject($giftCardProduct)) {
            $this->context->smarty->assign([
                'showGiftCardForm' => false,
            ]);

            return;
        }

        $selected_amount = null;
        $template_vars = [];
        $amount_vars = [];

        $templateGroup = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'));
        if (Validate::isLoadedObject($templateGroup)) {
            $images_id = [];
            $product_images = Image::getImages($this->context->language->id, $giftCardProduct->id);
            foreach ($product_images as $image) {
                $images_id[] = (int) $image['id_image'];
            }

            $images = [];
            $template_attributes = AttributeGroup::getAttributes((int) $this->context->language->id, (int) $templateGroup->id);
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
                $image['thumbnail'] = $this->getThumbnail($giftCardProduct, $image_obj);
                $image['attribute_value'] = (int) $image_obj->id;
                $image['position'] = (int) $image_obj->position;
                $image['legend'] = $image_obj->legend[$this->context->language->id];
                $image['tags'] = GiftCardModel::getTagsByIdImage((int) $image_obj->id, $this->context->language->id);
                $image['cover'] = $image_obj->cover;
                $image['auto'] = $default_amount && $default_amount['auto'] ? $default_amount['amount'] : false;
                $images[] = $image;
            }

            if (count($images)) {
                $positions = array_column($images, 'position');
                array_multisort($positions, SORT_ASC, $images);
                $template_vars = [
                    'id_attribute_group' => (int) $templateGroup->id,
                    'public_group_name' => $templateGroup->public_name[(int) $this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::str2url(str_replace([',', '.'], '-', $templateGroup->public_name[(int) $this->context->language->id]))),
                    'attributes' => $images,
                ];
            }
        }

        $amount_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
        if (Validate::isLoadedObject($amount_group_object)) {
            $amounts = [];
            $fixed_amount_list = explode(',', Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $giftCardProduct->id));
            $amount_attributes = AttributeGroup::getAttributes((int) $this->context->language->id, (int) $amount_group_object->id);
            foreach ($amount_attributes as $amount_attribute) {
                if (!in_array($amount_attribute['name'], $fixed_amount_list)) {
                    continue;
                }

                $amount = [];
                $amount['attribute_value'] = (int) $amount_attribute['name'];
                $amount['attribute_value_formatted'] = $this->displayPrice((float) $amount_attribute['name'], $this->context->currency->id);
                $amount['position'] = (int) $amount_attribute['position'];
                $amounts[] = $amount;
            }

            if (count($amounts)) {
                $attribute_values = array_column($amounts, 'attribute_value');
                array_multisort($attribute_values, SORT_ASC, $amounts);
                $amount_vars = [
                    'id_attribute_group' => (int) $amount_group_object->id,
                    'public_group_name' => $amount_group_object->public_name[(int) $this->context->language->id],
                    'rewrite_group_name' => str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::str2url(str_replace([',', '.'], '-', $amount_group_object->public_name[(int) $this->context->language->id]))),
                    'attributes' => $amounts,
                ];
            }
        }

        $pitch = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $giftCardProduct->id);
        $customAmountFrom = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $giftCardProduct->id);
        $customAmountTo = (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $giftCardProduct->id);

        $customization_fields = $giftCardProduct->customizable ? $giftCardProduct->getCustomizationFields($this->context->language->id, $this->context->shop->id) : false;
        if (is_array($customization_fields)) {
            foreach ($customization_fields as $key => $customization_field) {
                if ($customization_field['id_customization_field'] == Configuration::get('GIFTCARD_CUST_DATE_' . (int) $giftCardProduct->id)) {
                    unset($customization_fields[$key]);
                }
            }
        }

        $this->context->smarty->assign([
            'showGiftCardForm' => true,
            'template_vars' => (count($template_vars)) ? $template_vars : false,
            'amount_vars' => (count($amount_vars)) ? $amount_vars : false,
            'default_amount' => $selected_amount,
            'giftcard' => $giftCardProduct,
            'currencySign' => $this->context->currency->sign,
            'isCustomAmountFeatureActive' => (int) GiftCardModel::isCustomAmountFeatureActive($giftCardProduct->id),
            'pitch' => $pitch,
            'pitch_formatted' => $this->displayPrice((float) $pitch, (int) $this->context->currency->id),
            'custom_amount_from' => $customAmountFrom,
            'custom_amount_from_formatted' => $this->displayPrice((float) $customAmountFrom, (int) $this->context->currency->id),
            'custom_amount_to' => $customAmountTo,
            'custom_amount_to_formatted' => $this->displayPrice((float) $customAmountTo, (int) $this->context->currency->id),
            'customizationFields' => $customization_fields,
        ]);
    }

    public function getThumbnail($product, $image)
    {
        $key = (int) $product->id . '-' . (int) $image->id;

        if (file_exists($this->getProductImageDir() . $image->getImgPath() . '-thumbnail.jpg')) {
            return Context::getContext()->link->getImageLink($product->link_rewrite, $key, 'thumbnail');
        }

        return Context::getContext()->link->getImageLink($product->link_rewrite, $key);
    }

    protected function updateCategory()
    {
        $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
        if (!Validate::isLoadedObject($category)) {
            return false;
        }

        $category_fields = [];
        $languages = Language::getLanguages();

        foreach ($languages as $language) {
            $category->name[(int) $language['id_lang']] = Tools::getValue('category_name_' . (int) $language['id_lang']);
            $category->description[(int) $language['id_lang']] = Tools::getValue('category_description_' . (int) $language['id_lang']);
            $category->link_rewrite[(int) $language['id_lang']] = Tools::str2url($category->name[(int) $language['id_lang']]);

            $category_fields['name'][(int) $language['id_lang']] = true;
            $category_fields['description'][(int) $language['id_lang']] = true;
            $category_fields['link_rewrite'][(int) $language['id_lang']] = true;
        }

        $category->active = Tools::getValue('display_cat');
        $category->groupBox = $category->getGroups();
        $category->setFieldsToUpdate([
            'active' => true,
            'name' => $category_fields['name'],
            'description' => $category_fields['description'],
            'link_rewrite' => $category_fields['link_rewrite'],
            'groupBox' => true,
        ]);
        $category->update(true);

        if (isset($_FILES['image_cat']['tmp_name']) && !empty($_FILES['image_cat']['tmp_name'])) {
            $category->deleteImage();

            if ($error = ImageManager::validateUpload($_FILES['image_cat'], Tools::getMaxUploadSize())) {
                $this->_post_errors[] = $error;
            }

            $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
            if (!$tmp_name) {
                return false;
            }

            if (!move_uploaded_file($_FILES['image_cat']['tmp_name'], $tmp_name)) {
                return false;
            }

            if (!ImageManager::checkImageMemoryLimit($tmp_name)) {
                $this->_post_errors[] = $this->l('Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value on your server configuration.');
            }

            if (empty($this->_post_errors) && !ImageManager::resize($tmp_name, _PS_CAT_IMG_DIR_ . $category->id . '.jpg', null, null, 'jpg')) {
                $this->_post_errors[] = $this->l('An error occurred while uploading the image.');
            }

            if (count($this->_post_errors)) {
                return false;
            }

            unlink($tmp_name);

            $images_types = ImageType::getImagesTypes('categories');
            $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

            foreach ($images_types as $image_type) {
                ImageManager::resize(
                    _PS_CAT_IMG_DIR_ . $category->id . '.jpg',
                    _PS_CAT_IMG_DIR_ . $category->id . '-' . stripslashes($image_type['name']) . '.jpg',
                    (int) $image_type['width'],
                    (int) $image_type['height']
                );

                if ($generate_hight_dpi_images) {
                    ImageManager::resize(
                        _PS_CAT_IMG_DIR_ . $category->id . '.jpg',
                        _PS_CAT_IMG_DIR_ . $category->id . '-' . stripslashes($image_type['name']) . '2x.jpg',
                        (int) $image_type['width'] * 2,
                        (int) $image_type['height'] * 2
                    );
                }
            }
        }

        return true;
    }

    public function initCategoriesAssociation()
    {
        $categories = [];

        $currencies = Currency::getCurrencies(false, true, true);
        foreach ($currencies as $currency) {
            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $categories = $product->getCategories();
            break;
        }

        $rootCategory = Category::getRootCategory();
        $idRootCategory = Validate::isLoadedObject($rootCategory) ? $rootCategory->id : Configuration::get('PS_ROOT_CATEGORY');

        $tree_categories_helper = new HelperTreeCategories('categories-treeview');
        $tree_categories_helper
            ->setRootCategory((int) $idRootCategory)
            ->setUseCheckBox(true)
            ->setSelectedCategories($categories)
            ->setDisabledCategories([Configuration::get('GIFTCARD_CAT')]);

        return $tree_categories_helper->render();
    }

    protected function updateTopMenuDisplay()
    {
        $display = Tools::getValue('display_topmenu');
        Configuration::updateValue('GIFTCARD_DISPLAY_TOPMENU', (int) $display);

        $shop_ids = Shop::getContextListShopID();
        foreach ($shop_ids as $shop_id) {
            $shop_group_id = Shop::getGroupFromShop($shop_id);
            $conf = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
            $id_linksmenutop = $this->addTopMenuLink($shop_id);

            if (!$display) {
                $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
                $topmenu_class::remove($id_linksmenutop, $shop_id);

                Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string) str_replace(['LNK' . $id_linksmenutop . ',', 'LNK' . $id_linksmenutop], '', $conf), false, $shop_group_id, $shop_id);
            } else {
                $menu_items = Tools::strlen($conf) ? explode(',', $conf) : [];
                if (!in_array('LNK' . $id_linksmenutop, $menu_items)) {
                    $menu_items[] = 'LNK' . $id_linksmenutop;
                }

                Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string) implode(',', $menu_items), false, (int) $shop_group_id, (int) $shop_id);
            }
        }

        return true;
    }

    protected function addTopMenuLink($shop_id)
    {
        $id_linksmenutop = 0;
        $topmenu_class = version_compare(_PS_VERSION_, '1.7', '>=') ? 'Ps_MenuTopLinks' : 'MenuTopLinks';
        $labels = [];
        $page_link = [];
        $languages = Language::getLanguages();
        $category = new Category((int) Configuration::get('GIFTCARD_CAT'));

        if (!Validate::isLoadedObject($category)) {
            return 0;
        }

        foreach ($languages as $language) {
            $labels[(int) $language['id_lang']] = $category->name[$language['id_lang']];
            $page_link[(int) $language['id_lang']] = $this->context->link->getModuleLink($this->name, 'page', [], null, (int) $language['id_lang'], (int) $shop_id);
            $links = $topmenu_class::gets((int) $language['id_lang'], null, (int) $shop_id);
            foreach ($links as $link) {
                if ($link['link'] == $page_link[(int) $language['id_lang']]) {
                    $id_linksmenutop = (int) $link['id_linksmenutop'];
                    break 2;
                }
            }
        }

        if (0 == $id_linksmenutop) {
            $topmenu_class::add($page_link, $labels, 0, (int) $shop_id);
            $id_linksmenutop = $this->addTopMenuLink($shop_id);
        }

        return $id_linksmenutop;
    }

    protected function updateGiftcard()
    {
        Configuration::updateValue('GIFTCARD_USE_CART_RULE', (int) Tools::getValue('use_cart_rule'));
        Configuration::updateValue('GIFTCARD_USE_CACHE', (int) Tools::getValue('use_cache'));
        Configuration::updateValue('GIFTCARD_CRON_ACTIVE', (int) Tools::getValue('cron_active'));
        Configuration::updateValue('GIFTCARD_USE_CUSTOMIZATION', (int) Tools::getValue('use_customization'));

        $expiration_time = Tools::getValue('expiration_time');
        $expiration_date = Tools::getValue('expiration_date');
        if (!Validate::isInt($expiration_time) || !Validate::isCleanHtml($expiration_date)) {
            $this->_post_errors[] = $this->l('Invalid expiration datetime');

            return false;
        }
        Configuration::updateValue('GIFTCARD_EXPIRATION_TIME', $expiration_time);
        Configuration::updateValue('GIFTCARD_EXPIRATION_DATE', $expiration_date);

        // update stacking method with other cart rules
        if ((int) Configuration::get('GIFTCARD_USE_CART_RULE')
            && !($this->addCartRuleRestriction() && $this->addProductRestriction())
        ) {
            return false;
        }

        if (!$this->addAmountAttributes()) {
            return false;
        }

        // update categories association
        $categories = Tools::getValue('categoryBox', []);
        $categories = array_unique(array_merge($categories, [(int) Configuration::get('GIFTCARD_CAT')]));

        $product_fields = [];
        $currencies = Currency::getCurrencies(false, true, true);
        $languages = Language::getLanguages();

        foreach ($currencies as $currency) {
            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            foreach ($languages as $language) {
                $product->name[(int) $language['id_lang']] = Tools::getValue('product_name_' . (int) $language['id_lang']);
                $product->link_rewrite[(int) $language['id_lang']] = Tools::str2url($product->name[(int) $language['id_lang']] . '-' . $currency['iso_code']);
                $product->description_short[(int) $language['id_lang']] = Tools::getValue('product_description_short_' . (int) $language['id_lang']);
                $product->description[(int) $language['id_lang']] = Tools::getValue('product_description_' . (int) $language['id_lang']);

                $product_fields['name'][(int) $language['id_lang']] = true;
                $product_fields['link_rewrite'][(int) $language['id_lang']] = true;
                $product_fields['description_short'][(int) $language['id_lang']] = true;
                $product_fields['description'][(int) $language['id_lang']] = true;
            }

            $product->is_virtual = true;
            $product->id_tax_rules_group = Tools::getValue('id_tax_rules_group_' . (int) $product->id);
            $product->visibility = Tools::getValue('visibility');
            $product->active = Tools::getValue('active_' . (int) $product->id);
            $product->updateCategories($categories);
            $product->setFieldsToUpdate([
                'is_virtual' => true,
                'id_tax_rules_group' => true,
                'visibility' => true,
                'active' => true,
                'name' => $product_fields['name'],
                'link_rewrite' => $product_fields['link_rewrite'],
                'description_short' => $product_fields['description_short'],
                'description' => $product_fields['description'],
            ]);
            $product->update(true);

            Configuration::updateValue('GIFTCARD_ACTIVE_' . (int) $product->id, (int) $product->active);

            // update default combination
            $attributes = GiftCardModel::getDefaultAttributes($product->id);
            $id_combination = $this->generateCombination($product->id, $attributes, $currency['id_currency']);
            $id_default_combination = (int) Product::getDefaultAttribute($product->id);
            if ($id_default_combination != $id_combination) {
                if (!GiftCardModel::existsInCart($id_default_combination)
                    && (int) Configuration::get('GIFTCARD_USE_CACHE')) {
                    $combination = new Combination($id_default_combination);
                    $combination->delete();
                }
            }

            GiftCardModel::deleteDefaultAttributes($product->id);
            GiftCardModel::setDefaultAttribute($product->id, $id_combination);
        }

        return true;
    }

    protected function addCartRuleRestriction()
    {
        $cart_rule_combination = (int) Tools::getValue('cart_rule');
        $gift_card_cart_rule_ids = GiftCardModel::getGiftcardCartRuleIds();

        foreach ($gift_card_cart_rule_ids as $id_cart_rule) {
            Db::getInstance()->delete('cart_rule_combination', '`id_cart_rule_1` = ' . (int) $id_cart_rule . ' OR `id_cart_rule_2` = ' . (int) $id_cart_rule);

            if ($cart_rule_combination) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 0 WHERE id_cart_rule = ' . (int) $id_cart_rule . ' LIMIT 1');
                Db::getInstance()->execute('
        				INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
        					SELECT id_cart_rule, ' . (int) $id_cart_rule . ' FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE cart_rule_restriction = 1
        				)');
            } else {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = ' . (int) $id_cart_rule . ' LIMIT 1');

                $ruleCombinations = Db::getInstance()->executeS('
            				SELECT cr.id_cart_rule
            				FROM ' . _DB_PREFIX_ . 'cart_rule cr
            				WHERE cr.id_cart_rule != ' . (int) $id_cart_rule . '
            				AND cr.cart_rule_restriction = 0
            				AND NOT EXISTS (
            					SELECT 1
            					FROM ' . _DB_PREFIX_ . 'cart_rule_combination
            					WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_2 AND ' . (int) $id_cart_rule . ' = id_cart_rule_1
            				)
            				AND NOT EXISTS (
            					SELECT 1
            					FROM ' . _DB_PREFIX_ . 'cart_rule_combination
            					WHERE cr.id_cart_rule = ' . _DB_PREFIX_ . 'cart_rule_combination.id_cart_rule_1 AND ' . (int) $id_cart_rule . ' = id_cart_rule_2
            				)');

                foreach ($ruleCombinations as $incompatibleRule) {
                    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET cart_rule_restriction = 1 WHERE id_cart_rule = ' . (int) $incompatibleRule['id_cart_rule'] . ' LIMIT 1');
                    Db::getInstance()->execute('
          					INSERT IGNORE INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
          						SELECT id_cart_rule, ' . (int) $incompatibleRule['id_cart_rule'] . ' FROM `' . _DB_PREFIX_ . 'cart_rule`
          						WHERE active = 1
          						AND id_cart_rule != ' . (int) $id_cart_rule . '
          						AND id_cart_rule != ' . (int) $incompatibleRule['id_cart_rule'] . '
          					)');
                }
            }
        }

        Configuration::updateValue('GIFTCARD_CART_RULE', $cart_rule_combination);

        return true;
    }

    protected function updateCartRuleRestriction($cart_rule)
    {
        $cart_rule_combination = (int) Configuration::get('GIFTCARD_CART_RULE');
        if ($cart_rule_combination && !$cart_rule->cart_rule_restriction) {
            return false;
        }

        if ($cart_rule->cart_rule_restriction) {
            $selected_ids = [];
            $giftcard_cart_rule_ids = GiftCardModel::getGiftcardCartRuleIds();

            if (in_array($cart_rule->id, $giftcard_cart_rule_ids) && !$cart_rule_combination) {
                $selected_ids = [];
            } else {
                if (Tools::getValue('cart_rule_restriction') && is_array($array = Tools::getValue('cart_rule_select')) && count($array)) {
                    foreach ($array as $id) {
                        $selected_ids[] = (int) $id;
                    }
                } elseif (!Tools::getValue('cart_rule_restriction')) {
                    $cart_rules = Db::getInstance()->executeS('
                      SELECT cr.`id_cart_rule`
                      FROM `' . _DB_PREFIX_ . 'cart_rule` cr
                      WHERE cr.`active` = 1
                    ');

                    foreach ($cart_rules as $cart_rule) {
                        $selected_ids[] = $cart_rule['id_cart_rule'];
                    }
                }
            }

            $ids = $cart_rule_combination
              ? array_unique(array_merge($selected_ids, $giftcard_cart_rule_ids))
              : array_diff($selected_ids, $giftcard_cart_rule_ids);

            $_POST['cart_rule_restriction'] = 1;
            $_POST['cart_rule_select'] = $ids;
        } else {
            $cart_rule->cart_rule_restriction = 1;

            return true;
        }

        return false;
    }

    protected function isArrayValuesIdentical($a, $b)
    {
        sort($a);
        sort($b);

        return $a == $b;
    }

    protected function addProductRestriction()
    {
        $allow_stack = (int) Tools::getValue('cart_rule_buy');
        $active_cart_rules_ids = GiftCardModel::getActivePercentCartRuleIds();
        $giftcard_category_id = (int) Configuration::get('GIFTCARD_CAT');
        $category_ids = GiftCardModel::getCategoryIds([$giftcard_category_id]);

        foreach ($active_cart_rules_ids as $id_cart_rule) {
            $product_rule = GiftCardModel::getGiftcardProductRestriction($id_cart_rule, $giftcard_category_id);

            if ($product_rule) {
                if (!$allow_stack) {
                    continue;
                }

                $item_ids = GiftCardModel::getProductRestrictionItemIds($product_rule['id_product_rule']);

                if ($this->isArrayValuesIdentical($item_ids, $category_ids)) {
                    Db::getInstance()->delete(
                        'cart_rule_product_rule_value',
                        '`id_product_rule` = ' . (int) $product_rule['id_product_rule']
                    );

                    Db::getInstance()->delete(
                        'cart_rule_product_rule',
                        '`id_product_rule` = ' . (int) $product_rule['id_product_rule']
                    );

                    Db::getInstance()->delete(
                        'cart_rule_product_rule_group',
                        '`id_product_rule_group` = ' . (int) $product_rule['id_product_rule_group']
                    );

                    Db::getInstance()->execute(
                        '
                        UPDATE `' . _DB_PREFIX_ . 'cart_rule`
                        SET product_restriction = 0, reduction_product = 0
                        WHERE id_cart_rule = ' . (int) $id_cart_rule . '
                        LIMIT 1'
                    );
                }
            } else {
                if ($allow_stack) {
                    continue;
                }

                $existing_product_rule = GiftCardModel::getGiftcardProductRestriction($id_cart_rule, $giftcard_category_id, true);

                if (!$existing_product_rule) {
                    $cartRuleHasNoProductRestriction = (bool) Db::getInstance()->getValue(
                        '
                        SELECT COUNT(*)
                        FROM `' . _DB_PREFIX_ . 'cart_rule`
                        WHERE product_restriction = 0
                        AND reduction_product = 0
                        AND id_cart_rule = ' . (int) $id_cart_rule
                    );

                    if ($cartRuleHasNoProductRestriction) {
                        $values = [];

                        Db::getInstance()->execute('
                            INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
                            VALUES (' . (int) $id_cart_rule . ', 1)
                        ');
                        $id_product_rule_group = Db::getInstance()->Insert_ID();

                        Db::getInstance()->execute('
                            INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule` (`id_product_rule_group`, `type`)
                            VALUES (' . (int) $id_product_rule_group . ', "categories")
                        ');
                        $id_product_rule = Db::getInstance()->Insert_ID();

                        foreach ($category_ids as $category_id) {
                            $values[] = '(' . (int) $id_product_rule . ',' . (int) $category_id . ')';
                        }

                        Db::getInstance()->execute(
                            '
                            INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` (`id_product_rule`, `id_item`)
                            VALUES ' . implode(',', $values)
                        );

                        Db::getInstance()->execute(
                            '
                            UPDATE `' . _DB_PREFIX_ . 'cart_rule`
                            SET product_restriction = 1, reduction_product = -2
                            WHERE id_cart_rule = ' . (int) $id_cart_rule . '
                            LIMIT 1'
                        );
                    }
                }
            }
        }

        Configuration::updateValue('GIFTCARD_CART_RULE_BUY', $allow_stack);

        return true;
    }

    protected function updateProductRestriction($cart_rule)
    {
        if (((int) Configuration::get('GIFTCARD_CART_RULE_BUY'))
            || $cart_rule->reduction_product != 0
            || $cart_rule->reduction_percent <= 0
        ) {
            return false;
        }

        if ($cart_rule->product_restriction) {
            Db::getInstance()->execute(
                '
                UPDATE `' . _DB_PREFIX_ . 'cart_rule`
                SET reduction_product = -2
                WHERE id_cart_rule = ' . (int) $cart_rule->id . '
                LIMIT 1'
            );

            $_POST['product_restriction'] = 1;
            $_POST['product_rule_group'] = [1];
            $_POST['product_rule_group_1_quantity'] = 1;
            $_POST['product_rule_1'] = [1];
            $_POST['product_rule_1_1_type'] = 'categories';
            $_POST['product_rule_select_1_1'] = GiftCardModel::getCategoryIds([(int) Configuration::get('GIFTCARD_CAT')]);
        } else {
            $cart_rule->product_restriction = 1;

            return true;
        }

        return false;
    }

    protected function addAmountAttributes()
    {
        $languages = Language::getLanguages();
        $currencies = Currency::getCurrencies(false, true, true);
        $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT');

        $attributes_list = [];
        $amounts_list = [];

        foreach ($currencies as $currency) {
            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $custom_amount_from = (int) Tools::getValue('custom_amount_from_' . (int) $product->id);
            $custom_amount_to = (int) Tools::getValue('custom_amount_to_' . (int) $product->id);
            $pitch = (int) Tools::getValue('custom_amount_pitch_' . (int) $product->id, 1);
            $fixed_amounts = Tools::getValue('fixed_amounts_' . (int) $product->id);
            $fixed_list = array_map('intval', explode(',', $fixed_amounts));
            $default_amount = (int) Tools::getValue('default_amount_' . (int) $product->id, $custom_amount_from);

            if ($custom_amount_from > $custom_amount_to || 0 == $custom_amount_from || !(Validate::isUnsignedInt($custom_amount_from) && Validate::isUnsignedInt($custom_amount_to))) {
                $this->_post_errors[] = $this->l('Invalid custom amounts');
            }

            if (!Validate::isUnsignedInt($pitch) || 0 == $pitch) {
                $this->_post_errors[] = $this->l('Invalid pitch');
            }

            if (count($this->_post_errors)) {
                return false;
            }

            foreach ($fixed_list as $key => $amount) {
                if (!Validate::isUnsignedInt($amount)
                    || 0 == $amount
                    || $amount < $custom_amount_from
                    || $amount > $custom_amount_to
                    || 0 != $amount % $pitch
                ) {
                    unset($fixed_list[$key]);
                }
            }

            $product_images = Image::getImages($this->context->language->id, $product->id);
            foreach ($product_images as $image) {
                $default_amount_auto = (int) Tools::getValue('auto_select_amount_' . (int) $image['id_image']);
                $default_amount = Tools::getValue('default_amount_' . (int) $image['id_image']);
                if (!$default_amount) {
                    $default_amount = $custom_amount_from;
                }

                if (!in_array($default_amount, $fixed_list) || !Validate::isUnsignedInt($default_amount)) {
                    $this->_post_errors[] = $this->l('Please select a default amount which belongs to the fixed amount list');
                    continue;
                }

                if (!GiftCardModel::addAmount((int) $image['id_image'], $default_amount, $default_amount_auto)) {
                    $this->_post_errors[] = $this->l('Invalid default amounts');
                }
            }

            if (count($this->_post_errors)) {
                return false;
            }

            // Update configuration
            Configuration::updateValue('GIFTCARD_AMOUNT_CUSTOM_FEATURE_' . (int) $product->id, (int) !Tools::getValue('custom_amount_feature_' . (int) $product->id));
            Configuration::updateValue('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id, (int) $custom_amount_from);
            Configuration::updateValue('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id, (int) $custom_amount_to);
            Configuration::updateValue('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id, (string) implode(',', $fixed_list));
            Configuration::updateValue('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id, (int) $pitch);

            $attributes = AttributeGroup::getAttributes($this->context->language->id, (int) $id_attribute_group);
            foreach ($attributes as $attribute) {
                $attributes_list[$attribute['id_attribute']] = (int) $attribute['name'];
            }

            $amounts = [];
            for ($i = $custom_amount_from; $i <= $custom_amount_to; $i = $i + $pitch) {
                $amounts[] = $i;
            }
            $amounts = array_merge($amounts, $fixed_list);
            $amounts_list = array_merge($amounts_list, $amounts);
        }

        $list_add = array_diff($amounts_list, $attributes_list);
        $list_del = array_diff($attributes_list, $amounts_list);

        // Remove all deprecated amounts in attributes
        foreach ($list_del as $id_attribute => $amount) {
            if (!GiftCardModel::isAttribute((int) $id_attribute_group, (int) $amount, $this->context->language->id)) {
                continue;
            }

            $obj = new $this->attribute_class((int) $id_attribute);
            $obj->delete();
        }

        // Add all new amounts in attributes
        foreach ($list_add as $amount) {
            if (GiftCardModel::isAttribute((int) $id_attribute_group, (int) $amount, $this->context->language->id) || 0 == $amount) {
                continue;
            }

            $obj = new $this->attribute_class();
            $obj->id_attribute_group = (int) $id_attribute_group;
            foreach ($languages as $language) {
                $obj->name[(int) $language['id_lang']] = (int) $amount;
            }
            $obj->position = $this->attribute_class::getHigherPosition((int) $id_attribute_group) + 1;
            $obj->add();
            $obj->cleanPositions((int) $id_attribute_group, false);
        }

        return true;
    }

    public function getCombinationProperties($id_product, $attributes, $id_currency = null)
    {
        $product = new Product((int) $id_product);
        if (!Validate::isLoadedObject($product)) {
            return [];
        }

        if (!$id_currency) {
            $id_currency = Context::getContext()->currency->id;
        }

        $price = 0;
        $currency_from = new Currency((int) $id_currency);
        $currency_to = new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT'));

        foreach ($attributes as $attribute) {
            $obj = new $this->attribute_class((int) $attribute);
            if (!Validate::isLoadedObject($obj) || $obj->id_attribute_group == (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE')) {
                continue;
            }

            $price_ti = Tools::convertPriceFull((int) $obj->name[$this->context->language->id], $currency_from, $currency_to);
            $tax = new Tax();
            $tax->rate = $product->getTaxesRate();
            $tax_calculator = new TaxCalculator([$tax]);
            $price = Tools::ps_round($tax_calculator->removeTaxes($price_ti), 6);
        }

        return [
            'id_product' => (int) $product->id,
            'price' => (float) $price,
            'weight' => 0,
            'ecotax' => 0,
            'reference' => 'gift_card_' . implode('_', $attributes),
            'default_on' => 0,
            'minimal_quantity' => 1,
            'available_date' => date('Y-m-d'),
        ];
    }

    public function generateCombination($id_product, $attributes, $id_currency = null, $shops = [])
    {
        $product = new Product((int) $id_product);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        $id_shop_list = $shops;
        if (!count($shops)) {
            $id_shop_list = Shop::getContextListShopID();
        }

        $properties = $this->getCombinationProperties($id_product, $attributes, $id_currency);
        $id_combination = (int) $product->productAttributeExists($attributes, false, null, true, true);
        $combination = new Combination((int) $id_combination);

        if (!Validate::isLoadedObject($combination) || !$combination->id_product) {
            foreach ($properties as $field => $value) {
                $combination->$field = $value;
            }

            if (version_compare(_PS_VERSION_, '1.7.3', '>=')) {
                $combination->low_stock_threshold = null;
                $combination->low_stock_alert = false;
            }
        } else {
            $combination->price = $properties['price'];
            $combination->setFieldsToUpdate($combination->getFieldsShop());
        }

        if (version_compare(_PS_VERSION_, '8', '<')) {
            $combination->quantity = 1000;
        }

        $combination->id_shop_list = $id_shop_list;

        if (!$combination->save()) {
            return false;
        }

        foreach ($id_shop_list as $shop) {
            version_compare(_PS_VERSION_, '1.7.2', '>=')
                ? StockAvailable::setQuantity($product->id, (int) $combination->id, 1000, (int) $shop, false)
                : StockAvailable::setQuantity($product->id, (int) $combination->id, 1000, (int) $shop);
        }

        $result = true;
        if (!$id_combination) {
            $attribute_list = [];

            foreach ($attributes as $id_attribute) {
                $attribute = new $this->attribute_class((int) $id_attribute);
                if ($attribute->id_attribute_group == Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE')) {
                    $combination->setImages([$attribute->name[$this->context->language->id]]);
                }

                $attribute_list[] = [
                    'id_product_attribute' => (int) $combination->id,
                    'id_attribute' => (int) $id_attribute,
                ];
            }

            $result &= Db::getInstance()->insert('product_attribute_combination', (array) $attribute_list);
        }

        return $result ? (int) $combination->id : false;
    }

    protected function updateMailForm()
    {
        Configuration::updateValue('GIFTCARD_EMAIL_IMG_WIDTH', (int) Tools::getValue('email_img_width'));
        Configuration::updateValue('GIFTCARD_EMAIL_IMG_HEIGHT', (int) Tools::getValue('email_img_height'));
        Configuration::updateValue('GIFTCARD_PDF_ATTACHMENT', (int) Tools::getValue('generate_pdf'));

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            foreach (Tools::getValue('giftcard_email_subject') as $key => $val) {
                Configuration::updateValue('GIFTCARD_EMAIL_SUBJECT_' . Tools::strtoupper($key), [
                    (int) $language['id_lang'] => $val[(int) $language['id_lang']], ], true);
            }
        }

        return true;
    }

    public function getTranslations($product)
    {
        $label = $conf = null;
        $languages = Language::getLanguages();
        $customization_fields = $product->getCustomizationFields(false, Context::getContext()->shop->id);

        foreach ($customization_fields[Product::CUSTOMIZE_TEXTFIELD] as $key => $customization_field) {
            if ($key == Configuration::get('GIFTCARD_CUST_NAME_' . (int) $product->id)) {
                $label = $this->l('Name');
                $conf = 'GIFTCARD_CUST_NAME';
            } elseif ($key == Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $product->id)) {
                $label = $this->l('Email');
                $conf = 'GIFTCARD_CUST_EMAIL';
            } elseif ($key == Configuration::get('GIFTCARD_CUST_CONTENT_' . (int) $product->id)) {
                $label = $this->l('Content');
                $conf = 'GIFTCARD_CUST_CONTENT';
            } elseif ($key == Configuration::get('GIFTCARD_CUST_DATE_' . (int) $product->id)) {
                $label = $this->l('Date of send');
                $conf = 'GIFTCARD_CUST_DATE';
            }

            $value = [];
            foreach ($languages as $language) {
                $value[$language['id_lang']] = $customization_field[$language['id_lang']]['name'];
            }

            $translations[] = [
                'label' => $this->l('Customization field') . ' "' . $label . '"',
                'name' => 'customization_' . $this->getConfIds($conf),
                'value' => $value,
                'required' => true,
            ];
        }

        $attribute_group_ids = [(int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'), (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT')];
        foreach ($attribute_group_ids as $attribute_group_id) {
            $attribute_group_obj = new AttributeGroup($attribute_group_id);
            if ($attribute_group_obj->id == (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE')) {
                $label = $this->l('Template');
            } elseif ($attribute_group_obj->id == (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT')) {
                $label = $this->l('Amount');
            }
            $translations[] = [
                'label' => $this->l('Attribute group') . ' "' . $label . '"',
                'name' => 'group_' . (int) $attribute_group_obj->id,
                'value' => $attribute_group_obj->public_name,
                'required' => true,
            ];
        }

        return $translations;
    }

    public function updateTranslations()
    {
        $translations = Tools::getValue('translations');
        if (!is_array($translations) || !count($translations)) {
            $this->_post_errors[] = $this->l('Missing translation fields');

            return false;
        }

        foreach ($translations as $key => $translation) {
            $tmp = explode('_', $key, 2);
            if ('customization' == $tmp[0]) {
                $ids = explode('_', $tmp[1]);
                foreach ($ids as $id) {
                    foreach ($translation as $id_lang => $value) {
                        if (!isset($value) || empty($value) || !Validate::isLabel($value)) {
                            $this->_post_errors[] = $this->l('Translation field not valid : ') . $key;
                            continue;
                        }

                        foreach (Shop::getContextListShopID() as $id_shop) {
                            if (!Db::getInstance()->execute('
                                INSERT INTO `' . _DB_PREFIX_ . 'customization_field_lang`
                                (`id_customization_field`, `id_lang`, `id_shop`, `name`) VALUES (' . (int) $id . ', ' . (int) $id_lang . ', ' . (int) $id_shop . ', \'' . pSQL($value) . '\')
                                ON DUPLICATE KEY UPDATE `name` = \'' . pSQL($value) . '\'')
                            ) {
                                $this->_post_errors[] = $this->l('Error while updating field : ') . $key;
                            }
                        }
                    }
                }
            } elseif ('group' == $tmp[0]) {
                $attribute_group = new AttributeGroup((int) $tmp[1]);
                if (!Validate::isLoadedObject($attribute_group)
                    || !in_array((int) $attribute_group->id, [(int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'), (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT')])) {
                    $this->_post_errors[] = $this->l('Unable to load object : ') . $key;
                    continue;
                }

                $attribute_group_translations = [];
                foreach ($translation as $id_lang => $value) {
                    if (!isset($value) || empty($value) || !Validate::isGenericName($value)) {
                        $this->_post_errors[] = $this->l('Translation field not valid : ') . $key;
                        continue;
                    }
                    $attribute_group->name[(int) $id_lang] = $value;
                    $attribute_group_translations['name'][(int) $id_lang] = true;

                    $attribute_group->public_name[(int) $id_lang] = $value;
                    $attribute_group_translations['public_name'][(int) $id_lang] = true;
                }

                $attribute_group->setFieldsToUpdate([
                    'name' => $attribute_group_translations['name'],
                    'public_name' => $attribute_group_translations['public_name'],
                ]);
                if (!$attribute_group->update()) {
                    $this->_post_errors[] = $this->l('Error while updating field : ') . $key;
                }
            }
        }

        return true;
    }

    public function renderGiftCardForm()
    {
        $this->context->controller->addCss($this->_path . 'views/css//tools/jquery.tagify.css');
        $this->context->controller->addCss($this->_path . 'views/css/admin/admin.css');
        $this->context->controller->addJqueryUi([
            'ui.widget',
            'ui.progressbar',
        ]);

        $this->context->controller->addJs([
            _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_ . 'tinymce.inc.js',
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            _PS_JS_DIR_ . 'admin-dnd.js',
            $this->_path . 'views/js/tools/jquery.tagify.min.js',
        ]);

        $this->context->controller->addJqueryPlugin([
            'colorpicker',
            'tablednd',
        ]);

        $languages = Language::getLanguages();
        $this->context->smarty->assign('default_language', (int) Configuration::get('PS_LANG_DEFAULT'));

        $currencies = Currency::getCurrencies(false, true, true);
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $this->context->smarty->assign('default_currency', $default_currency);

        $shops = false;
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
        }

        $this->context->smarty->assign('shops', $shops);

        $products_data = [];
        foreach ($currencies as $key => $currency) {
            $custom_amounts = [];
            $images = [];
            $images_amount = [];
            $images_id = [];
            $images_lang = [];
            $tags = [];

            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                unset($currencies[$key]);
                continue;
            }

            $custom_amounts = [
                'feature' => Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FEATURE_' . (int) $product->id),
                'from' => Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id),
                'to' => Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id),
            ];

            $product_images = Image::getImages($this->context->language->id, $product->id);
            foreach ($product_images as $image) {
                $images[] = new Image((int) $image['id_image']);

                if ($default_amount = GiftCardModel::getAmount($image['id_image'])) {
                    $images_amount[$image['id_image']]['default_amount'] = $default_amount['amount'];
                    $images_amount[$image['id_image']]['auto'] = $default_amount['auto'];
                }

                if ($currency['id_currency'] == $default_currency) {
                    $images_id[(int) $image['position']] = $this->getImageIds((int) $image['position']);
                    $images_lang[$image['id_image']] = GiftCardModel::getGiftCardImageLang((int) $image['id_image']);
                    foreach ($languages as $language) {
                        $tags[$image['id_image']][$language['id_lang']] = implode(',', GiftCardModel::getTagsByIdImage((int) $image['id_image'], (int) $language['id_lang']));
                    }
                }
            }

            $products_data[(int) $currency['id_currency']] = [
                'product' => $product,
                'active' => (int) Configuration::get('GIFTCARD_ACTIVE_' . (int) $product->id),
                'custom_amounts' => $custom_amounts,
                'id_tax_rules_group' => $product->getIdTaxRulesGroup(),
                'fixed_amounts' => Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id),
                'pitch' => (int) Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id),
                'currency' => $currency,
                'images' => $images,
                'images_amount' => $images_amount,
            ];

            if ($currency['id_currency'] == $default_currency) {
                $products_data[(int) $currency['id_currency']] = array_merge($products_data[(int) $currency['id_currency']], [
                    'images_id' => $images_id,
                    'images_lang' => $images_lang,
                    'tags' => $tags,
                    'translations' => $this->getTranslations($product),
                ]);
            }
        }

        $category = new Category((int) Configuration::get('GIFTCARD_CAT'));
        if (file_exists(_PS_TMP_IMG_DIR_ . 'category_' . (int) $category->id . 'categories')) {
            unlink(_PS_TMP_IMG_DIR_ . 'category_' . (int) $category->id . 'categories');
        }

        $image_cat = _PS_CAT_IMG_DIR_ . $category->id . '.jpg';
        $image_cat_url = ImageManager::thumbnail($image_cat, 'category_' . (int) $category->id . 'categories.jpg', 350, 'jpg', true, true);
        $image_cat_size = file_exists($image_cat) ? filesize($image_cat) / 1000 : false;
        $image_cat_delete_url = $this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name, 'id_category' => $category->id, 'deleteCatImage' => 1]);
        $image_cat_uploader = new GiftCardUploader('file');
        $image_cat_uploader->setId('image_cat')
            ->setName('image_cat')
            ->setFiles([$image_cat_url ? [
                'type' => GiftCardUploader::TYPE_IMAGE,
                'image' => $image_cat_url,
                'size' => $image_cat_size,
                'delete_url' => $image_cat_delete_url,
            ] : [],
            ]);

        $image_uploader = new GiftCardUploader('file');
        $image_uploader->setMultiple(true)
            ->setUseAjax(true)
            ->setUrl($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]) . '&ajax=1&action=addProductImage');

        $type = ImageType::getByNameNType('%', 'products', 'height');
        $imageType = isset($type['name']) ? $type['name'] : (version_compare(_PS_VERSION_, '1.7', '>=') ? ImageType::getFormattedName('small') : ImageType::getFormatedName('small'));
        $this->context->smarty->assign('imageType', $imageType);

        $iso = $this->context->language->iso_code;
        $topmenu_module = version_compare(_PS_VERSION_, '1.7', '>=') ? 'ps_mainmenu' : 'blocktopmenu';
        $topmenu = Module::getInstanceByName($topmenu_module);

        $this->context->smarty->assign([
            'module' => $this,
            'products_data' => $products_data,
            'category' => $category,
            'categories_association' => $this->initCategoriesAssociation(),
            'id_category' => (int) $category->id,
            'iso_lang' => $languages[0]['iso_code'],
            'max_image_size' => (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE') / 1024 / 1024,
            'up_filename' => (string) Tools::getValue('virtual_product_filename_attribute'),
            'current_shop_id' => Shop::CONTEXT_SHOP == $this->context->shop->getContext() ? (int) $this->context->shop->id : 0,
            'uploader' => $image_uploader->render(),
            'cat_uploader' => $image_cat_uploader->render(),
            'languages' => $languages,
            'currencies' => $currencies,
            'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroupsForOptions(),
            'currentIndex' => $this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]),
            'currentToken' => Tools::getAdminTokenLite('AdminModules'),
            'defaultLanguage' => (int) $this->context->language->id,
            'topmenu' => (bool) ($topmenu && $topmenu->active),
            'tinymce' => true,
            'iso' => file_exists(__PS_BASE_URI__ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
            'path_css' => _THEME_CSS_DIR_,
            'ad' => __PS_BASE_URI__,
            'statistics' => GiftCardModel::getstatistics($this),
            'cron_url' => $this->context->link->getBaseLink((int) Configuration::get('PS_SHOP_DEFAULT')) . 'index.php?fc=module&module=thegiftcard&controller=cron&secure_key=' . Configuration::get('GIFTCARD_CRON_TOKEN'),
            'cron_active' => Configuration::get('GIFTCARD_CRON_ACTIVE'),
            'use_customization' => Configuration::get('GIFTCARD_USE_CUSTOMIZATION'),
        ]);

        $this->assignGiftCardCreatorData();

        return $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration.tpl', $this->context->smarty)->fetch();
    }

    public function getConfIds($conf)
    {
        $ids = [];
        $currencies = Currency::getCurrencies(false, true, true);
        foreach ($currencies as $currency) {
            if ($id_product = Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency'])) {
                $ids[] = 'GIFTCARD_PROD' == $conf ? (int) $id_product : (int) Configuration::get($conf . '_' . (int) $id_product);
            }
        }
        $ids = implode('_', $ids);

        return $ids;
    }

    public function getImageIds($position)
    {
        $images_id = [];
        $currencies = Currency::getCurrencies(false, true, true);

        foreach ($currencies as $currency) {
            $product_images = Image::getImages($this->context->language->id, (int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            foreach ($product_images as $image) {
                if ($image['position'] != $position) {
                    continue;
                }

                $images_id[] = $image['id_image'];
            }
        }

        $images_id = implode('_', $images_id);

        return $images_id;
    }

    public function ajaxProcessAddGiftCard()
    {
        $result = true;

        try {
            $giftCardProduct = new Product(
                (int) Configuration::get('GIFTCARD_PROD_' . (int) $this->context->currency->id),
                false,
                $this->context->language->id,
                $this->context->shop->id
            );

            if (!Validate::isLoadedObject($giftCardProduct)) {
                throw new PrestaShopException($this->l('Gift card product cannot be loaded'));
            }

            $attributes = Tools::getValue('attributes');
            if (!isset($attributes)
                || !is_array($attributes)
                || !count($attributes)
            ) {
                throw new PrestaShopException($this->l('Please select a template and an amount'));
            }

            if (!($template = GiftCardModel::filterAttribute(
                $attributes,
                (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'),
                (int) $this->context->language->id,
                GiftCardModel::getImagesList($giftCardProduct->id, $this->context->language->id)
            ))) {
                throw new PrestaShopException($this->l('Please select a template'));
            }

            if (!($amount = GiftCardModel::filterAttribute(
                $attributes,
                (int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'),
                (int) $this->context->language->id,
                GiftCardModel::getAmountsList($giftCardProduct->id)
            ))) {
                throw new PrestaShopException($this->l('Please select an amount'));
            }

            if (!($id_combination = $this->generateCombination($giftCardProduct->id, [$template['id_attribute'], $amount['id_attribute']]))) {
                throw new PrestaShopException($this->l('Error while creating the gift card with selected template and amount'));
            }

            $combination = new Combination($id_combination);
            if (!Validate::isLoadedObject($combination)) {
                throw new PrestaShopException($this->l('Unable to load the combination object'));
            }

            $id_customization = 0;

            if (($customizationData = Tools::getValue('customizationData')) && is_array($customizationData)) {
                $field_ids = $giftCardProduct->getCustomizationFieldIds();
                $authorized_text_fields = [];
                foreach ($field_ids as $field_id) {
                    if (Product::CUSTOMIZE_TEXTFIELD == $field_id['type']) {
                        $authorized_text_fields[(int) $field_id['id_customization_field']] = 'textField' . (int) $field_id['id_customization_field'];
                    }
                }

                $indexes = array_flip($authorized_text_fields);
                foreach ($customizationData as $field_name => $value) {
                    $value = trim($value);
                    if (in_array($field_name, $authorized_text_fields) && '' != $value) {
                        if ($indexes[$field_name] == Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $giftCardProduct->id) && !Validate::isEmail($value)) {
                            throw new PrestaShopException($this->l('Please fill a valid email'));
                        } elseif (!Validate::isMessage($value)) {
                            throw new PrestaShopException($this->l('An error occurred while attempting to save this data.'));
                        } else {
                            if ($id_customization == 0) {
                                Db::getInstance()->execute(
                                    'INSERT INTO `' . _DB_PREFIX_ . 'customization` (`id_cart`, `id_product`, `id_product_attribute`)
                                VALUES (0, ' . (int) $giftCardProduct->id . ', ' . (int) $id_combination . ')'
                                );

                                $id_customization = (int) Db::getInstance()->Insert_ID();
                            }

                            $query = 'INSERT INTO `' . _DB_PREFIX_ . 'customized_data` (`id_customization`, `type`, `index`, `value`)
                            VALUES (' . (int) $id_customization . ', ' . Product::CUSTOMIZE_TEXTFIELD . ', ' . (int) $indexes[$field_name] . ', \'' . pSQL($value) . '\')';

                            if (!Db::getInstance()->execute($query)) {
                                throw new PrestaShopException($this->l('Cannot save customization data'));
                            }
                        }
                    } else {
                        throw new PrestaShopException($this->l('Please fill all the fields.'));
                    }
                }
            }

            $cart_rule = new CartRule();
            foreach (Language::getLanguages(false) as $language) {
                $cart_rule->name[(int) $language['id_lang']] = $this->displayName;
            }
            $cart_rule->description = 'Gift card created from back office';
            $cart_rule->code = Tools::strtoupper(Tools::passwdGen(12));
            $cart_rule->date_from = date('Y-m-d');
            $cart_rule->date_to = date('Y-m-d', strtotime('+' . Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' .
                Configuration::get('GIFTCARD_EXPIRATION_DATE'), strtotime($cart_rule->date_from)));
            $cart_rule->reduction_amount = $amount['name'];
            $cart_rule->reduction_tax = true;
            $cart_rule->reduction_currency = (int) $this->context->currency->id;
            $cart_rule->cart_rule_restriction = (int) Configuration::get('GIFTCARD_CART_RULE') ? false : true;
            $cart_rule->shop_restriction = Shop::isFeatureActive() ? true : false;
            $cart_rule->add();

            if (Shop::isFeatureActive()) {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`)
                    VALUES(' . (int) $cart_rule->id . ', ' . (int) $this->context->shop->id . ')');
            }

            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'giftcard (`id_order_detail`, `id_cart_rule`, `id_image`, `id_customization`, `sent`)
				VALUES(0, ' . (int) $cart_rule->id . ', ' . (int) $template['name'] . ', ' . (int) $id_customization . ', 0)');

            if (isset(Tools::getValue('customizationData')['textField' . (int) Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $giftCardProduct->id)])
                && Validate::isEmail(Tools::getValue('customizationData')['textField' . (int) Configuration::get('GIFTCARD_CUST_EMAIL_' . (int) $giftCardProduct->id)])
            ) {
                $id_giftcard = Db::getInstance()->Insert_ID();

                if ($this->sendEmail(
                    (int) $this->context->language->id,
                    null,
                    $cart_rule,
                    (int) $template['name'],
                    (int) $id_customization,
                    (int) $this->context->shop->id)
                ) {
                    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'giftcard` SET `sent` = 1 WHERE `id_giftcard` = ' . (int) $id_giftcard);
                }
            }
        } catch (PrestaShopException $e) {
            $this->_post_errors[] = $e->getMessage();
            $result = false;
        }

        if ($result) {
            exit(json_encode([
                'error' => false,
            ]));
        } else {
            exit(json_encode([
                'error' => true,
                'errors' => $this->_post_errors,
            ]));
        }
    }

    public function ajaxProcessaddProductImage()
    {
        $languages = Language::getLanguages();
        $currencies = Currency::getCurrencies(false, true, true);
        $shops = Shop::getContextListShopID();

        $image_uploader = new GiftCardUploader('file');
        $image_uploader
            ->setAcceptTypes(['jpeg', 'gif', 'png', 'jpg'])
            ->setMaxSize((int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))
        ;
        $files = $image_uploader->process();
        $default_amounts = [];

        foreach ($files as &$file) {
            foreach ($currencies as $currency) {
                $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
                if (!Validate::isLoadedObject($product)) {
                    continue;
                }

                $image = new Image();
                $image->id_product = (int) $product->id;
                $image->position = Image::getHighestPosition($product->id) + 1;
                $image->id_shop_list = $shops;

                if (!Image::getCover($image->id_product)) {
                    $image->cover = true;
                } else {
                    $image->cover = false;
                }

                if (($validate = $image->validateFieldsLang(false, true)) !== true) {
                    $file['error'] = Tools::displayError($validate);
                }

                if (isset($file['error']) && (!is_numeric($file['error']) || 0 != $file['error'])) {
                    continue;
                }

                if (!$image->add()) {
                    $file['error'] = Tools::displayError('Error while creating additional image');
                } else {
                    GiftCardModel::addGiftCardImageLang((int) $image->id, 0);

                    if (!$new_path = $image->getPathForCreation()) {
                        $file['error'] = Tools::displayError('An error occurred during new folder creation');
                        continue;
                    }

                    $error = 0;
                    if (!ImageManager::resize($file['save_path'], $new_path . '.' . $image->image_format, null, null, 'jpg', false, $error)) {
                        switch ($error) {
                            case ImageManager::ERROR_FILE_NOT_EXIST:
                                $file['error'] = Tools::displayError('An error occurred while copying image, the file does not exist anymore.');
                                break;

                            case ImageManager::ERROR_FILE_WIDTH:
                                $file['error'] = Tools::displayError('An error occurred while copying image, the file width is 0px.');
                                break;

                            case ImageManager::ERROR_MEMORY_LIMIT:
                                $file['error'] = Tools::displayError('An error occurred while copying image, check your memory limit.');
                                break;

                            default:
                                $file['error'] = Tools::displayError('An error occurred while copying image.');
                                break;
                        }
                        continue;
                    }

                    $images_types = ImageType::getImagesTypes('products');
                    $generate_hight_dpi_images = (bool) Configuration::get('PS_HIGHT_DPI');

                    foreach ($images_types as $imageType) {
                        if (!ImageManager::resize($file['save_path'], $new_path . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                            $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                            continue 2;
                        }

                        if ($generate_hight_dpi_images) {
                            if (!ImageManager::resize($file['save_path'], $new_path . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                                $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                                continue 2;
                            }
                        }
                    }

                    if (!GiftCardModel::createThumbnail($file['save_path'], $new_path . '-thumbnail.' . $image->image_format, 300)) {
                        $file['error'] = Tools::displayError('An error occurred while creating thumbnail:');
                        continue;
                    }

                    if ($generate_hight_dpi_images) {
                        if (!GiftCardModel::createThumbnail($file['save_path'], $new_path . '-thumbnail2x.' . $image->image_format, 600)) {
                            $file['error'] = Tools::displayError('An error occurred while creating thumbnail:');
                            continue;
                        }
                    }

                    $json_shops = [];
                    foreach ($shops as $id_shop) {
                        $json_shops[$id_shop] = true;
                    }

                    $file['status'] = 'ok';
                    $file['ids'] = $this->getImageIds($image->position);
                    $file['id'] = $image->id;
                    $file['position'] = $image->position;
                    $file['cover'] = $image->cover;
                    $file['path'] = $image->getExistingImgPath();
                    $file['shops'] = $json_shops;

                    $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE');
                    $obj = new $this->attribute_class();
                    $obj->id_attribute_group = (int) $id_attribute_group;
                    foreach ($languages as $language) {
                        $obj->name[(int) $language['id_lang']] = $image->id;
                    }
                    $obj->position = $this->attribute_class::getHigherPosition((int) $id_attribute_group) + 1;
                    $obj->add();
                    $obj->cleanPositions((int) $id_attribute_group, false);

                    if (GiftCardModel::addAmount($image->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $image->id_product), false, 0, 0)) {
                        $default_amounts[] = [
                            'currency' => $currency,
                            'image' => $file,
                            'amount' => Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $image->id_product),
                            'auto' => 0,
                        ];
                    }
                }
            }
        }

        $return = [
            $image_uploader->getName() => $files,
            'default_amounts' => $default_amounts,
        ];

        exit(json_encode($return));
    }

    public function ajaxProcessUpdateImagePosition()
    {
        $result = true;

        try {
            if (!($json = Tools::getValue('json'))) {
                throw new PrestaShopException('errors');
            }

            $json = stripslashes($json);
            $images = json_decode($json, true);
            foreach ($images as $ids => $position) {
                $ids = explode('_', $ids);
                foreach ($ids as $id) {
                    Db::getInstance()->execute(
                        'UPDATE `' . _DB_PREFIX_ . 'image`
            			SET `position` = ' . (int) $position . '
            			WHERE `id_image` = ' . (int) $id
                    );
                }
            }
        } catch (PrestaShopException $e) {
            $this->_post_errors[] = $e->getMessage();
            $result = false;
        }

        if ($result) {
            exit(json_encode([
                'confirmations' => $this->l('Image position updated.'),
            ]));
        } else {
            exit(json_encode([
                'confirmations' => '',
                'errors' => $this->_post_errors,
            ]));
        }
    }

    public function ajaxProcessUpdateCover()
    {
        $result = true;
        $ids = explode('_', Tools::getValue('ids_image'));
        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);

        try {
            foreach ($ids as $id) {
                $image = new Image((int) $id);

                if (!Validate::isLoadedObject($image)) {
                    throw new PrestaShopException($this->l('Image cannot be loaded.'));
                }

                if (!$image->isAssociatedToShop()) {
                    throw new PrestaShopException(sprintf($this->l('You have to enable the image for the shop %s before updating cover '), Context::getContext()->shop->name));
                }

                if (!in_array($image->id_product, $product_ids)) {
                    throw new PrestaShopException($this->l('Image is not associated to the gift card product.'));
                }

                Image::deleteCover((int) $image->id_product);
                $image->cover = true;
                $image->id_shop_list = Shop::getContextListShopID();
                $image->setFieldsToUpdate([
                    'cover' => true,
                ]);
                $image->update();

                if (file_exists(_PS_TMP_IMG_DIR_ . 'product_' . (int) $image->id_product . '.jpg')) {
                    unlink(_PS_TMP_IMG_DIR_ . 'product_' . (int) $image->id_product . '.jpg');
                }
                if (file_exists(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $image->id_product . '_' . $this->context->shop->id . '.jpg')) {
                    unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $image->id_product . '_' . $this->context->shop->id . '.jpg');
                }
            }
        } catch (PrestaShopException $e) {
            $this->_post_errors[] = $e->getMessage();
            $result = false;
        }

        if ($result) {
            exit(json_encode([
                'confirmations' => $this->l('Cover updated.'),
            ]));
        } else {
            exit(json_encode([
                'confirmations' => '',
                'errors' => $this->_post_errors,
            ]));
        }
    }

    public function ajaxProcessUpdateCaptionTags()
    {
        $result = true;
        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);
        $image_ids = explode('_', Tools::getValue('ids_image'));
        $image_lang = (int) Tools::getValue('image_lang');
        $image_legends = Tools::getValue('legends');
        $image_tags = Tools::getValue('tags');
        $image_shops = Tools::getValue('shops');

        try {
            foreach ($product_ids as $product_id) {
                $product_images = Image::getImages($this->context->language->id, (int) $product_id);
                foreach ($product_images as $product_image) {
                    $image_id = (int) $product_image['id_image'];
                    if (!in_array($image_id, $image_ids)) {
                        continue;
                    }

                    foreach ($image_legends as $legend) {
                        if (!Validate::isGenericName($legend['value'])) {
                            throw new PrestaShopException(sprintf($this->l('Legend is not valid for image %s'), $image_id));
                        }

                        if (!Db::getInstance()->execute(
                            '
            				UPDATE ' . _DB_PREFIX_ . 'image_lang SET legend = "' . pSQL($legend['value']) . '"
            				WHERE id_image = ' . (int) $image_id . '
            				AND id_lang = ' . (int) $legend['id_lang']
                        )) {
                            throw new PrestaShopException(sprintf($this->l('Unable to update legend for image %s'), $image_id));
                        }
                    }

                    foreach ($image_tags as $tag) {
                        if (!Validate::isTagsList($tag['value'])) {
                            throw new PrestaShopException(sprintf($this->l('Tag list is not valid for image %s'), $image_id));
                        }

                        if (!Db::getInstance()->execute('
                            INSERT INTO ' . _DB_PREFIX_ . 'giftcard_tags (id_image, id_lang, tags) VALUES (' . (int) $image_id . ', ' . (int) $tag['id_lang'] . ', "' . pSQL($tag['value']) . '")
                            ON DUPLICATE KEY UPDATE tags="' . pSQL($tag['value']) . '"
                        ')) {
                            throw new PrestaShopException(sprintf($this->l('Unable to update tag list for image %s'), $image_id));
                        }
                    }

                    if (!GiftCardModel::addGiftCardImageLang((int) $image_id, (int) $image_lang)) {
                        throw new PrestaShopException(sprintf($this->l('Unable to update language visibility for image %s'), $image_id));
                    }

                    if (Shop::isFeatureActive()) {
                        $template_attribute = Db::getInstance()->getRow('
                        SELECT a.id_attribute FROM ' . _DB_PREFIX_ . 'attribute a
                        INNER JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute
                        WHERE a.id_attribute_group = ' . (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE') . '
                        AND al.id_lang = ' . (int) $this->context->language->id . '
                        AND al.name = ' . (int) $image_id);

                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image_shop WHERE `id_image` = ' . (int) $image_id);
                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'attribute_shop WHERE `id_attribute` = ' . (int) $template_attribute['id_attribute']);
                        $cover = (int) $product_image['cover'] ? '1' : 'NULL';

                        foreach ($image_shops as $id_shop) {
                            Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'image_shop (`id_product`, `id_image`, `id_shop`, `cover`) VALUES(' . (int) $product_id . ', ' . (int) $image_id . ', ' . (int) $id_shop . ', ' . $cover . ')');
                            Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'attribute_shop (`id_attribute`, `id_shop`) VALUES(' . (int) $template_attribute['id_attribute'] . ', ' . (int) $id_shop . ')');
                        }
                    }
                }
            }
        } catch (PrestaShopException $e) {
            $this->_post_errors[] = $e->getMessage();
            $result = false;
        }

        if (!$result) {
            exit(json_encode([
                'confirmations' => '',
                'errors' => $this->_post_errors,
            ]));
        } else {
            exit(json_encode([
                'confirmations' => $this->l('Image data updated.'),
            ]));
        }
    }

    public function getProductImageDir()
    {
        if (defined('_PS_PRODUCT_IMG_DIR_')) {
            return _PS_PRODUCT_IMG_DIR_;
        }

        if (defined('_PS_PROD_IMG_DIR_')) {
            return _PS_PROD_IMG_DIR_;
        }

        return null;
    }

    public function ajaxProcessDeleteProductImage()
    {
        $result = true;
        $ids = explode('_', Tools::getValue('ids_image'));
        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);
        $deleted_ids = [];
        $productImageDir = $this->getProductImageDir();

        try {
            foreach ($ids as $id) {
                $image = new Image((int) $id);

                if (!Validate::isLoadedObject($image)) {
                    continue;
                }

                $image->delete();
                $imageFolder = $image->getImgFolder();

                if (in_array($image->id_product, $product_ids) && !$image->hasMultishopEntries() && $productImageDir && $imageFolder) {
                    Image::deleteAllImages($productImageDir . $imageFolder);
                }

                // if deleted image was the cover, change it to the first one
                if (!Image::getCover($image->id_product)) {
                    Db::getInstance()->execute('
                  			UPDATE `' . _DB_PREFIX_ . 'image_shop` image_shop, ' . _DB_PREFIX_ . 'image i
                  			SET image_shop.`cover` = 1,
                  			i.cover = 1
                  			WHERE image_shop.`id_image` = (SELECT id_image FROM
                  				(SELECT image_shop.id_image
                  					FROM ' . _DB_PREFIX_ . 'image i' .
                                      Shop::addSqlAssociation('image', 'i') . '
                  					WHERE i.id_product =' . (int) $image->id_product . ' LIMIT 1
                  				) tmpImage)
                  			AND id_shop=' . (int) $this->context->shop->id . '
                  			AND i.id_image = image_shop.id_image
              			');
                }

                if (!$image->hasMultishopEntries()) {
                    Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'giftcard_tags` WHERE `id_image` = ' . (int) $image->id . '');
                    Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'giftcard_image_lang` WHERE `id_image` = ' . (int) $image->id . '');

                    if (file_exists(_PS_TMP_IMG_DIR_ . 'product_' . $image->id_product . '.jpg')) {
                        unlink(_PS_TMP_IMG_DIR_ . 'product_' . $image->id_product . '.jpg');
                    }
                }

                if (file_exists(_PS_TMP_IMG_DIR_ . 'product_mini_' . $image->id_product . '_' . $this->context->shop->id . '.jpg')) {
                    unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . $image->id_product . '_' . $this->context->shop->id . '.jpg');
                }

                $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE');
                $attributes = AttributeGroup::getAttributes($this->context->language->id, $id_attribute_group);
                foreach ($attributes as $attribute) {
                    if ($attribute['name'] == $id) {
                        $obj = new $this->attribute_class((int) $attribute['id_attribute']);
                        $obj->delete();
                        $obj->cleanPositions((int) $id_attribute_group, false);
                        break;
                    }
                }

                if (GiftCardModel::deleteAmount((int) $image->id)) {
                    $deleted_ids[] = $image->id;
                }
            }
        } catch (PrestaShopException $e) {
            $this->_post_errors[] = $e->getMessage();
            $result = false;
        }

        if ($result) {
            exit(json_encode([
                'confirmations' => $this->l('Template deleted.'),
                'deleted_ids' => $deleted_ids,
            ]));
        } else {
            exit(json_encode([
                'confirmations' => '',
                'errors' => $this->_post_errors,
            ]));
        }
    }

    public function ajaxProcessGeneratePdf()
    {
        $cart_rule = $order_detail = $order = $customer = null;
        $giftcard = Db::getInstance()->getRow(
            'SELECT gc.*
            FROM `' . _DB_PREFIX_ . 'giftcard` gc
            WHERE gc.`id_giftcard` = ' . (int) Tools::getValue('id_giftcard')
        );

        if (!$giftcard) {
            $this->_post_errors[] = $this->l('The gift card object cannot be loaded.');
        } else {
            $cart_rule = new CartRule((int) $giftcard['id_cart_rule']);
            if (!Validate::isLoadedObject($cart_rule)) {
                $this->_post_errors[] = $this->l('The cart rule object cannot be loaded.');
            }
        }

        if (!count($this->_post_errors)) {
            $image = new Image((int) $giftcard['id_image']);
            $order_detail = new OrderDetail((int) $giftcard['id_order_detail']);
            $order = new Order((int) $order_detail->id_order);
            $customer = new Customer((int) $order->id_customer);
            $shopId = Validate::isLoadedObject($order) ? (int) $order->id_shop : (int) Context::getContext()->shop->id;
            $langId = Validate::isLoadedObject($order) ? (int) $order->id_lang : (int) Context::getContext()->language->id;
            $customerName = Validate::isLoadedObject($customer) ? $customer->firstname . ' ' . $customer->lastname : null;

            $pdf_vars = [
                'module' => $this,
                'lang_id' => $langId,
                'shop_id' => $shopId,
                'shop_name' => Configuration::get('PS_SHOP_NAME', null, null, $shopId),
                'shop_url' => Context::getContext()->link->getPageLink('index', true, $langId, null, false, $shopId),
                'customer' => $customerName,
                'image_url' => version_compare(_PS_VERSION_, '1.7', '>=')
                    ? Tools::getShopProtocol() . Tools::getMediaServer(_PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg') . _PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg'
                    : _PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.jpg',
                'image_width' => Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH'),
                'image_height' => Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT'),
                'giftcard_amount' => $this->displayPrice((float) $cart_rule->reduction_amount, (int) $cart_rule->reduction_currency),
                'giftcard_code' => $cart_rule->code,
                'giftcard_expiration' => Tools::displayDate($cart_rule->date_to),
                'giftcard_expiration_date' => Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' . $this->getExpirationDate(),
            ];

            $customization = GiftCardModel::getCustomizedData((int) $giftcard['id_customization']);
            if (count($customization)
                && (isset($customization['name']) && !empty($customization['name']))
                && (isset($customization['content']) && !empty($customization['content']))) {
                $pdf_vars = array_merge($pdf_vars, [
                    'beneficiary' => $customization['name'],
                    'giftcard_message' => $customization['content'],
                ]);
            }

            try {
                $pdf = new PDF([$pdf_vars], 'GiftCardCore', $this->context->smarty);
                $output = $pdf->render(false);
                $pdfBase64 = base64_encode($output);

                exit(json_encode([
                    'error' => false,
                    'url' => 'data:application/pdf;base64,' . $pdfBase64,
                ]));
            } catch (PrestaShopException $e) {
                $this->_post_errors[] = $e->getMessage();
            }
        }

        exit(json_encode([
            'error' => true,
            'errors' => $this->_post_errors,
        ]));
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');

        if ('AdminOrders' == $controller) {
            $id_order = null;
            $path = null;

            if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                if ($request = $this->getRequest()) {
                    $id_order = (int) $request->attributes->get('orderId');
                    $path = $this->_path . 'views/js/admin/bo-order.js';
                }
            } else {
                $id_order = Tools::getValue('id_order');
                $path = $this->_path . 'views/js/admin/bo-order-legacy.js';
            }

            if ($id_order && ($gift_cards = GiftCardModel::getGiftCardsConsumptionByOrderId($id_order))) {
                foreach ($gift_cards as &$row) {
                    $row['cart_rule_url'] = $this->getAdminLink('AdminCartRules', ['id_cart_rule' => $row['id_cart_rule'], 'updatecart_rule' => 1]);
                    $row['order_url'] = $this->getAdminLink('AdminOrders', ['id_order' => $row['id_order'], 'vieworder' => 1]);

                    if (is_array($row['remaining_amount'])) {
                        $row['remaining_amount'] = $this->displayPrice((float) $row['remaining_amount']['amount'], (int) $row['remaining_amount']['id_currency']);
                    }
                }

                Media::addJsDefL('ocr_gift_cards', json_encode($gift_cards));
                $this->context->controller->addJS($path);
            }
        }

        if ('AdminTranslations' == $controller && (Tools::getValue('type') == 'mails')) {
            $this->context->controller->addJS($this->_path . 'views/js/admin/bo-mails.js');
        }
    }

    public function hookDisplayBackOfficeTop($params)
    {
        $controller = Tools::getValue('controller');
        if ('AdminProducts' != $controller) {
            return;
        }

        if (Tools::getValue('ajax') && 'updatePositions' === Tools::getValue('action')) {
            return;
        }

        $id_product = null;
        if ($request = $this->getRequest()) {
            $id_product = (int) $request->attributes->get('id') ? (int) $request->attributes->get('id') : (int) $request->attributes->get('productId');
        } else {
            $id_product = (int) Tools::getValue('id_product');
        }

        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);

        if ($id_product && in_array($id_product, $product_ids)) {
            Tools::redirectAdmin($this->getAdminLink('AdminModules', ['configure' => $this->name, 'tab_module' => $this->tab, 'module_name' => $this->name]));
        }

        return;
    }

    public function hookDisplayHeader($params)
    {
        if (method_exists($this->context->controller, 'getProduct') && ($product = $this->context->controller->getProduct())) {
            $product_ids = $this->getConfIds('GIFTCARD_PROD');
            $product_ids = explode('_', $product_ids);
            if (in_array($product->id, $product_ids)) {
                Tools::redirect($this->context->link->getModuleLink($this->name, 'page'));
            }
        } elseif (method_exists($this->context->controller, 'getCategory') && ($category = $this->context->controller->getCategory())) {
            if ($category->id == Configuration::get('GIFTCARD_CAT')) {
                Tools::redirect($this->context->link->getModuleLink($this->name, 'page'));
            }
        }

        return;
    }

    public function hookActionDeleteProductInCartAfter($params)
    {
        return $this->hookActionAfterDeleteProductInCart($params);
    }

    public function hookActionAfterDeleteProductInCart($params)
    {
        $id_product = (int) $params['id_product'];
        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);
        if (!in_array($id_product, $product_ids)) {
            return false;
        }

        $id_product_attribute = (int) $params['id_product_attribute'];

        $combination = new Combination($id_product_attribute);
        if (!Validate::isLoadedObject($combination)
            || $combination->default_on
            || (int) GiftCardModel::existsInCart($combination->id)
            || !Configuration::get('GIFTCARD_USE_CACHE')
            || !$combination->delete()) {
            return false;
        }

        return true;
    }

    public function hookActionObjectCartRuleDeleteAfter($params)
    {
        $object = $params['object'];

        if ($object instanceof CartRule) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'giftcard` WHERE `id_cart_rule` = ' . (int) $object->id);
        }

        return true;
    }

    public function hookActionObjectCartRuleAddAfter($params)
    {
        $object = $params['object'];

        if ($object instanceof CartRule) {
            if (!Configuration::get('GIFTCARD_USE_CART_RULE')
                || !isset($this->context->controller->className)
                || 'CartRule' != $this->context->controller->className
            ) {
                return true;
            }

            $update_cart_rule_restriction = $this->updateCartRuleRestriction($object);
            $update_product_restriction = $this->updateProductRestriction($object);

            if ($update_cart_rule_restriction || $update_product_restriction) {
                $object->update();
            }
        }

        return true;
    }

    public function hookActionObjectCartRuleUpdateAfter($params)
    {
        $object = $params['object'];
        if ($object instanceof CartRule) {
            return $this->hookActionObjectCartRuleAddAfter($params);
        }

        return true;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        if (!Validate::isLoadedObject($order = new Order($params['id_order']))
            || !($params['newOrderStatus'] instanceof OrderState)
            || 1 != $params['newOrderStatus']->paid
            || !Validate::isLoadedObject($customer = new Customer((int) $order->id_customer))
            || !($giftcards_data = $this->getGiftCardsData($order))
        ) {
            return false;
        }

        foreach ($giftcards_data as $data) {
            $cart_rule = new CartRule();
            foreach (Language::getLanguages(false) as $language) {
                $cart_rule->name[(int) $language['id_lang']] = $this->displayName;
            }
            $cart_rule->description = 'Order ' . (int) $order->id;
            $cart_rule->code = Tools::strtoupper(Tools::passwdGen(12));
            $cart_rule->date_from = $data['date_from'];
            $cart_rule->date_to = date('Y-m-d', strtotime('+' . Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' .
                Configuration::get('GIFTCARD_EXPIRATION_DATE'), strtotime($cart_rule->date_from)));
            $cart_rule->reduction_amount = $data['amount'];
            $cart_rule->reduction_tax = false;
            $cart_rule->minimum_amount_tax = true;
            $cart_rule->reduction_currency = (int) $order->id_currency;
            $cart_rule->cart_rule_restriction = (int) Configuration::get('GIFTCARD_CART_RULE') ? false : true;
            $cart_rule->shop_restriction = Shop::isFeatureActive() ? true : false;
            $cart_rule->add();

            // Force reduction_tax to 0 (tax excluded) and minimum_amount_tax to 1 (tax included)
            // directly in database to prevent hooks or defaults from changing them
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET `reduction_tax` = 0, `minimum_amount_tax` = 1 WHERE `id_cart_rule` = ' . (int) $cart_rule->id);

            if (Shop::isFeatureActive()) {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`)
                      VALUES(' . (int) $cart_rule->id . ', ' . (int) $order->id_shop . ')');
            }

            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'giftcard (`id_order_detail`, `id_cart_rule`, `id_image`, `id_customization`, `sent`)
				        VALUES(' . (int) $data['id_order_detail'] . ', ' . (int) $cart_rule->id . ', ' . (int) $data['id_image'] . ', ' . (int) $data['id_customization'] . ', 0)');

            if ($cart_rule->date_from == date('Y-m-d')) {
                $id_giftcard = Db::getInstance()->Insert_ID();

                if ($this->sendEmail((int) $order->id_lang, $customer, $cart_rule, (int) $data['id_image'], (int) $data['id_customization'], (int) $order->id_shop)) {
                    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'giftcard` SET `sent` = 1 WHERE `id_giftcard` = ' . (int) $id_giftcard);

                    $combination = new Combination((int) $data['id_product_attribute']);
                    if (!GiftCardModel::existsInCart((int) $combination->id)
                        && !$combination->default_on
                        && (int) Configuration::get('GIFTCARD_USE_CACHE')) {
                        $combination->delete();
                    }
                }
            }

            // Ensure reduction_tax stays false (tax excluded) and minimum_amount_tax stays true
            // (tax included) - set before update
            $cart_rule->reduction_tax = false;
            $cart_rule->minimum_amount_tax = true;
            $cart_rule->update();

            // Force reduction_tax to 0 (tax excluded) and minimum_amount_tax to 1 (tax included)
            // directly in database after update as well
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET `reduction_tax` = 0, `minimum_amount_tax` = 1 WHERE `id_cart_rule` = ' . (int) $cart_rule->id);
        }

        return true;
    }

    public function hookActionShopDataDuplication($params)
    {
        $customization_ids = [];
        $currencies = Currency::getCurrencies(false, true, true);
        foreach ($currencies as $currency) {
            $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            foreach (GiftCardModel::$customizations as $customization) {
                $customization_ids[] = Configuration::get('GIFTCARD_CUST_' . Tools::strtoupper($customization['name']) . '_' . (int) $product->id);
            }
        }

        $res = Db::getInstance()->getRow(
            'SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`id_shop`, cfl.`name`
            FROM `' . _DB_PREFIX_ . 'customization_field_lang` cfl
            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
            WHERE cfl.`id_shop` = ' . (int) $params['old_id_shop'] . '
            AND cfl.`id_customization_field` IN (' . implode(',', array_map('intval', $customization_ids)) . ')'
        );

        if ($res) {
            $sql = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'customization_field_lang` (`id_customization_field`, `id_lang`, `name`, `id_shop`)
                    (SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`name`, ' . (int) $params['new_id_shop'] . '
                    FROM ' . _DB_PREFIX_ . 'customization_field_lang cfl
                    LEFT JOIN `' . _DB_PREFIX_ . 'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
                    WHERE cfl.`id_shop` = ' . (int) $params['old_id_shop'] . '
                    AND cfl.`id_customization_field` IN (' . implode(',', array_map('intval', $customization_ids)) . '))';

            Db::getInstance()->execute($sql);
        }

        return true;
    }

    public function hookActionGetProductPropertiesBefore($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7', '>=')
            && ($giftCardProductId = (int) Configuration::get('GIFTCARD_PROD_' . (int) Context::getContext()->currency->id))
            && isset($params['product'])
            && isset($params['product']['id_product'])
            && $params['product']['id_product'] == $giftCardProductId
            && !isset($params['product']['cover_image_id'])
        ) {
            $cover = Product::getCover($giftCardProductId);

            if (!isset($cover['id_image'])
                || (($coverLang = GiftCardModel::getGiftCardImageLang($cover['id_image']))
                    && $coverLang != Context::getContext()->language->id
                    && 0 != $coverLang
                )
            ) {
                $params['product']['cover_image_id'] = GiftCardModel::getCoverByLangId(
                    $giftCardProductId,
                    Context::getContext()->language->id,
                    Context::getContext()->shop->id
                );
            }
        }
    }

    public function hookFilterProductSearch($params)
    {
        if (($currencies = Currency::getCurrencies(false, true, true))
            && count($currencies) > 1
            && isset($params['searchVariables']['products'])
        ) {
            $excludedProductIds = [];

            foreach ($currencies as $currency) {
                if ($currency['id_currency'] != Context::getContext()->currency->id
                    && ($id_product = Configuration::get('GIFTCARD_PROD_' . (int) $currency['id_currency']))
                ) {
                    $excludedProductIds[] = (int) $id_product;
                }
            }

            foreach ($params['searchVariables']['products'] as $key => $product) {
                if (in_array($product->getId(), $excludedProductIds)) {
                    unset($params['searchVariables']['products'][$key]);
                }
            }
        }
    }

    /**
     * @deprecated, to be removed in the next minor
     */
    public function hookActionCronJob()
    {
        try {
            $this->runCronTask();
        } catch (PrestaShopException $e) {
            return false;
        }

        return true;
    }

    /**
     * @deprecated, to be removed in the next minor
     */
    public function getCronFrequency()
    {
        return [
            'hour' => -1,
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1,
        ];
    }

    public function getGiftCardsData($order)
    {
        $giftcard_data = [];
        $customization_ids = [];
        $i = 0;
        $products = $order->getProductsDetail();

        $product_ids = $this->getConfIds('GIFTCARD_PROD');
        $product_ids = explode('_', $product_ids);

        foreach ($products as $product) {
            if (!in_array($product['product_id'], $product_ids)) {
                continue;
            }

            $purchased_quantity = GiftCardModel::getNumberPurchased($product['id_order_detail']);
            $quantity = $product['product_quantity'] - $purchased_quantity;

            if (0 == $quantity) {
                continue;
            }

            if (!($id_image = (int) GiftCardModel::getImage((int) $order->id_lang, $product['product_id'], $product['product_attribute_id']))) {
                $cover = Product::getCover($product['product_id']);
                $id_image = (int) $cover['id_image'];
            }

            $id_customization = GiftCardModel::getCustomization((int) $order->id_cart, (int) $product['product_attribute_id'], (int) $product['product_quantity'], $customization_ids);
            if (!in_array($id_customization, $customization_ids)) {
                $customization_ids[] = $id_customization;
            }

            $customized_date_from = GiftCardModel::getCustomizedDataByIndex((int) $id_customization, (int) Configuration::get('GIFTCARD_CUST_DATE_' . (int) $product['product_id']));
            $date_from = $customized_date_from ? $customized_date_from : date('Y-m-d');

            for ($k = 1; $k <= $quantity; ++$k) {
                $giftcard_data[$i]['id_image'] = $id_image;
                $giftcard_data[$i]['id_order_detail'] = $product['id_order_detail'];
                $giftcard_data[$i]['amount'] = $product['total_price_tax_incl'] / $product['product_quantity'];
                $giftcard_data[$i]['id_product_attribute'] = $product['product_attribute_id'];
                $giftcard_data[$i]['id_customization'] = $id_customization;
                $giftcard_data[$i]['date_from'] = $date_from;
                ++$i;
            }
        }

        if (!count($giftcard_data)) {
            return false;
        }

        return $giftcard_data;
    }

    public function sendEmail($id_lang, $customer, $cart_rule, $id_image, $id_customization = 0, $id_shop = null)
    {
        $file_attachement = null;
        $image = new Image((int) $id_image);
        $email_template = 'giftcard_print';
        $subject = Configuration::get('GIFTCARD_EMAIL_SUBJECT_PRINT', $id_lang);
        $email = Validate::isLoadedObject($customer) ? $customer->email : null;
        $name = Validate::isLoadedObject($customer) ? $customer->firstname . ' ' . $customer->lastname : null;
        $pdf_vars = [];

        $template_vars = [
            '{name}' => $name,
            '{image_url}' => $this->context->link->getImageLink($this->l('gift_card'), (string) $image->id),
            '{image_width}' => Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH'),
            '{image_height}' => Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT'),
            '{giftcard_amount}' => $this->displayPrice((float) $cart_rule->reduction_amount, (int) $cart_rule->reduction_currency),
            '{giftcard_message}' => '',
            '{giftcard_code}' => $cart_rule->code,
            '{giftcard_expiration}' => Tools::displayDate($cart_rule->date_to),
            '{giftcard_expiration_date}' => Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' . $this->getExpirationDate(),
        ];

        if (GiftCardModel::isPDFFeatureActive()) {
            $pdf_vars = [
                'module' => $this,
                'lang_id' => $id_lang,
                'shop_id' => $id_shop,
                'shop_name' => Configuration::get('PS_SHOP_NAME', null, null, $id_shop),
                'shop_url' => Context::getContext()->link->getPageLink('index', true, $id_lang, null, false, $id_shop),
                'customer' => $name,
                'image_url' => version_compare(_PS_VERSION_, '1.7', '>=')
                    ? Tools::getShopProtocol() . Tools::getMediaServer(_PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg') . _PS_PROD_IMG_ . $image->getExistingImgPath() . '.jpg'
                    : _PS_PROD_IMG_DIR_ . $image->getExistingImgPath() . '.jpg',
                'image_width' => Configuration::get('GIFTCARD_EMAIL_IMG_WIDTH'),
                'image_height' => Configuration::get('GIFTCARD_EMAIL_IMG_HEIGHT'),
                'giftcard_amount' => $this->displayPrice((float) $cart_rule->reduction_amount, (int) $cart_rule->reduction_currency),
                'giftcard_code' => $cart_rule->code,
                'giftcard_expiration' => Tools::displayDate($cart_rule->date_to),
                'giftcard_expiration_date' => Configuration::get('GIFTCARD_EXPIRATION_TIME') . ' ' . $this->getExpirationDate(),
            ];
        }

        $customization = GiftCardModel::getCustomizedData((int) $id_customization);

        if (count($customization)) {
            if (isset($customization['name']) && !empty($customization['name'])) {
                $template_vars = array_merge($template_vars, [
                    '{name}' => $customization['name'],
                ]);

                if (GiftCardModel::isPDFFeatureActive()) {
                    $pdf_vars = array_merge($pdf_vars, [
                        'beneficiary' => $customization['name'],
                    ]);
                }
            }

            if (isset($customization['content']) && !empty($customization['content'])) {
                $template_vars = array_merge($template_vars, [
                    '{giftcard_message}' => $customization['content'],
                ]);

                if (GiftCardModel::isPDFFeatureActive()) {
                    $pdf_vars = array_merge($pdf_vars, [
                        'giftcard_message' => $customization['content'],
                    ]);
                }
            }

            if (isset($customization['email']) && !empty($customization['email'])) {
                $email = $customization['email'];

                if (Validate::isLoadedObject($customer)) {
                    $email_template = 'giftcard_friend';
                    $subject = sprintf(Configuration::get('GIFTCARD_EMAIL_SUBJECT_FRIEND', $id_lang), $customer->firstname . ' ' . $customer->lastname);
                    $email = $customization['email'];
                    $name = $customization['name'];
                    $template_vars = array_merge($template_vars, [
                        '{customer}' => $customer->firstname . ' ' . $customer->lastname,
                    ]);
                }
            }
        }

        if (GiftCardModel::isPDFFeatureActive()) {
            $pdf = new PDF([$pdf_vars], 'GiftCardCore', $this->context->smarty);
            $file_attachement = [
                'content' => $pdf->render(false),
                'name' => $this->l('gift_card') . '_' . $cart_rule->id . '.pdf',
                'mime' => 'application/pdf',
            ];
        }

        if (!Validate::isEmail($email)) {
            return false;
        }

        if (Mail::Send(
            (int) $id_lang,
            $email_template,
            $subject,
            $template_vars,
            $email,
            $name,
            null,
            null,
            $file_attachement,
            null,
            dirname(__FILE__) . '/mails/',
            false,
            $id_shop
        )) {
            return true;
        }

        return false;
    }

    public function getExpirationDate()
    {
        switch (Configuration::get('GIFTCARD_EXPIRATION_DATE')) {
            case 'day':
                $expiration_date = Configuration::get('GIFTCARD_EXPIRATION_TIME') > 1 ? $this->l('days') : $this->l('day');
                break;
            case 'month':
                $expiration_date = Configuration::get('GIFTCARD_EXPIRATION_TIME') > 1 ? $this->l('months') : $this->l('month');
                break;
            case 'year':
                $expiration_date = Configuration::get('GIFTCARD_EXPIRATION_TIME') > 1 ? $this->l('years') : $this->l('year');
                break;
            default:
                $expiration_date = null;
                break;
        }

        return $expiration_date;
    }

    public function addComponentToShops($component, $id_shop_default, $shops)
    {
        if ('Product' == $component['class_name']) {
            $object = new $component['class_name']((int) $component['id'], false, null, $id_shop_default);
        } else {
            $object = new $component['class_name']((int) $component['id'], null, $id_shop_default);
        }

        $object_shop_list = $shops;
        foreach ($object_shop_list as $key => $id_shop) {
            if ($object->isAssociatedToShop($id_shop)) {
                unset($object_shop_list[$key]);
            }
        }

        if (count($object_shop_list)) {
            $object->id_shop_list = $object_shop_list;
            $object->setFieldsToUpdate($object->getFieldsShop());
            $object->update();
        }

        unset($object);

        return true;
    }

    public function duplicateGiftCard($id_currency, $shops = [])
    {
        $id_shop_list = Shop::getContextListShopID();
        if (count($shops)) {
            $id_shop_list = $shops;
        }

        if (($id_product_exists = Configuration::get('GIFTCARD_PROD_' . (int) $id_currency))
            && Validate::isLoadedObject($product = new Product((int) $id_product_exists))
        ) {
            $this->addComponentToShops(
                [
                    'class_name' => 'Product',
                    'id' => (int) $product->id,
                ],
                $product->id_shop_default,
                $id_shop_list
            );

            $this->addComponentToShops(
                [
                    'class_name' => 'Category',
                    'id' => (int) Configuration::get('GIFTCARD_CAT'),
                ],
                $product->id_shop_default,
                $id_shop_list
            );

            foreach (GiftCardModel::$attributes_group as $attribute_group) {
                $this->addComponentToShops(
                    [
                        'class_name' => 'AttributeGroup',
                        'id' => (int) Configuration::get('GIFTCARD_ATTRGROUP_' . Tools::strtoupper($attribute_group['name'])),
                    ],
                    $product->id_shop_default,
                    $id_shop_list
                );
            }

            $customization_ids = [];
            foreach (GiftCardModel::$customizations as $customization) {
                $customization_ids[] = Configuration::get('GIFTCARD_CUST_' . Tools::strtoupper($customization['name']) . '_' . (int) $product->id);
            }

            foreach ($id_shop_list as $shop) {
                $res = Db::getInstance()->getRow(
                    'SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`id_shop`, cfl.`name`
                    FROM `' . _DB_PREFIX_ . 'customization_field_lang` cfl
                    LEFT JOIN `' . _DB_PREFIX_ . 'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
                    WHERE cfl.`id_shop` = ' . (int) $shop . '
                    AND cfl.`id_customization_field` IN (' . implode(',', array_map('intval', $customization_ids)) . ')'
                );

                if (!$res) {
                    $sql = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'customization_field_lang` (`id_customization_field`, `id_lang`, `name`, `id_shop`)
                            (SELECT cfl.`id_customization_field`, cfl.`id_lang`, cfl.`name`, ' . (int) $shop . '
                            FROM ' . _DB_PREFIX_ . 'customization_field_lang cfl
                            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field` cf ON cf.`id_customization_field` = cfl.`id_customization_field`
                            WHERE cfl.`id_shop` = ' . (int) $product->id_shop_default . '
                            AND cfl.`id_customization_field` IN (' . implode(',', array_map('intval', $customization_ids)) . '))';

                    Db::getInstance()->execute($sql);
                }
            }

            $product_images = Image::getImages($this->context->language->id, $product->id);
            $template_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'));
            if (!Validate::isLoadedObject($template_group_object)) {
                return false;
            }

            $template_attributes = GiftCardModel::getAttributes((int) $this->context->language->id, (int) $template_group_object->id, (int) $product->id_shop_default);
            foreach ($template_attributes as $template_attribute) {
                $this->addComponentToShops(
                    [
                        'class_name' => $this->attribute_class,
                        'id' => (int) $template_attribute['id_attribute'],
                    ],
                    $product->id_shop_default,
                    $id_shop_list
                );

                $image_obj = new Image((int) $template_attribute['name']);
                foreach ($product_images as $image) {
                    if ($image['id_image'] != $image_obj->id) {
                        continue;
                    }

                    $cover = (int) $image['cover'] ? '1' : 'NULL';
                    foreach ($id_shop_list as $shop) {
                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image_shop WHERE `id_image` = ' . (int) $image['id_image'] . ' AND `id_shop` = ' . (int) $shop);
                        Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'image_shop (`id_product`, `id_image`, `id_shop`, `cover`) VALUES(' . (int) $product->id . ', ' . (int) $image['id_image'] . ', ' . (int) $shop . ', ' . $cover . ')');
                    }

                    GiftCardModel::addAmount($image['id_image'], Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id), false, 0, 0);
                }
            }

            $amount_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
            if (!Validate::isLoadedObject($amount_group_object)) {
                return false;
            }

            $amount_attributes = GiftCardModel::getAttributes((int) $this->context->language->id, (int) $amount_group_object->id, (int) $product->id_shop_default);
            foreach ($amount_attributes as $amount_attribute) {
                $this->addComponentToShops(
                    [
                        'class_name' => $this->attribute_class,
                        'id' => (int) $amount_attribute['id_attribute'],
                    ],
                    $product->id_shop_default,
                    $id_shop_list
                );
            }

            $attributes = GiftCardModel::getDefaultAttributes($product->id, false, (int) $product->id_shop_default);
            $id_combination = $this->generateCombination($product->id, $attributes, $id_currency, $id_shop_list);
            GiftCardModel::deleteDefaultAttributes($product->id, $id_shop_list);
            GiftCardModel::setDefaultAttribute($product->id, $id_combination, $id_shop_list);

            return true;
        }

        $id_default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $product = new Product((int) Configuration::get('GIFTCARD_PROD_' . (int) $id_default_currency));
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        $product = new Product((int) $product->id, false, null, $product->id_shop_default);

        $this->addComponentToShops(
            [
                'class_name' => 'Category',
                'id' => (int) Configuration::get('GIFTCARD_CAT'),
            ],
            $product->id_shop_default,
            $id_shop_list
        );

        foreach (GiftCardModel::$attributes_group as $attribute_group) {
            $this->addComponentToShops(
                [
                    'class_name' => 'AttributeGroup',
                    'id' => (int) Configuration::get('GIFTCARD_ATTRGROUP_' . Tools::strtoupper($attribute_group['name'])),
                ],
                $product->id_shop_default,
                $id_shop_list
            );
        }

        $id_product_old = $product->id;
        $id_shop_default = $product->id_shop_default;

        unset($product->id);

        $product->id_shop_list = $id_shop_list;
        if ($product->add()
            && Category::duplicateProductCategories($id_product_old, $product->id)
            && Image::duplicateProductImages($id_product_old, $product->id, false)
        ) {
            Configuration::updateGlobalValue('GIFTCARD_PROD_' . (int) $id_currency, (int) $product->id);
            Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $id_product_old));
            Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_TO_' . (int) $id_product_old));
            Configuration::updateGlobalValue('GIFTCARD_AMOUNT_FIXED_' . (int) $product->id, Configuration::get('GIFTCARD_AMOUNT_FIXED_' . (int) $id_product_old));
            Configuration::updateGlobalValue('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $product->id, Configuration::get('GIFTCARD_AMOUNT_CUSTOM_PITCH_' . (int) $id_product_old));
            Configuration::updateGlobalValue('GIFTCARD_ACTIVE_' . (int) $product->id, '1');

            $customizations = GiftCardModel::getCustomizationFieldsNLabels($id_product_old, $id_shop_default);
            foreach ($customizations['fields'] as $customization_field) {
                /* The new datas concern the new product */
                $customization_field['id_product'] = (int) $product->id;
                $old_customization_field_id = (int) $customization_field['id_customization_field'];
                $name = false;

                foreach (GiftCardModel::$customizations as $customization) {
                    if (Configuration::get('GIFTCARD_CUST_' . Tools::strtoupper($customization['name']) . '_' . (int) $id_product_old) == $old_customization_field_id) {
                        $name = $customization['name'];
                        break;
                    }
                }

                unset($customization_field['id_customization_field']);

                if (!$name
                    || !Db::getInstance()->insert('customization_field', $customization_field)
                    || !$customization_field_id = Db::getInstance()->Insert_ID()) {
                    return false;
                }

                if (isset($customizations['labels'])) {
                    foreach ($customizations['labels'][$old_customization_field_id] as $customization_label) {
                        foreach ($id_shop_list as $id_shop) {
                            $data = [
                                'id_customization_field' => (int) $customization_field_id,
                                'id_lang' => (int) $customization_label['id_lang'],
                                'id_shop' => (int) $id_shop,
                                'name' => pSQL($customization_label['name']),
                            ];

                            if (!Db::getInstance()->insert('customization_field_lang', $data)) {
                                continue;
                            }
                        }
                    }
                }

                Configuration::updateGlobalValue('GIFTCARD_CUST_' . Tools::strtoupper($name) . '_' . (int) $product->id, $customization_field_id);
            }

            $languages = Language::getLanguages();

            // retrieve product images
            $images = [];
            $default_product_images = Image::getImages($this->context->language->id, $id_product_old);
            foreach ($default_product_images as $image) {
                $image['ids'] = $this->getImageIds((int) $image['position']);
                $image['image_lang'] = GiftCardModel::getGiftCardImageLang((int) $image['id_image']);
                $image['tags'] = [];
                foreach ($languages as $language) {
                    $image['tags'][$language['id_lang']] = implode(',', GiftCardModel::getTagsByIdImage((int) $image['id_image'], (int) $language['id_lang']));
                }

                $images[] = $image;
            }

            $product_images = Image::getImages($this->context->language->id, (int) $product->id);
            foreach ($product_images as $image) {
                $id_attribute_group = (int) Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE');
                $obj = new $this->attribute_class();
                $obj->id_attribute_group = (int) $id_attribute_group;
                foreach ($languages as $language) {
                    $obj->name[(int) $language['id_lang']] = (int) $image['id_image'];
                }
                $obj->position = $this->attribute_class::getHigherPosition((int) $id_attribute_group) + 1;
                $obj->id_shop_list = $id_shop_list;
                $obj->add();
                $obj->cleanPositions((int) $id_attribute_group, false);

                foreach ($images as $image_data) {
                    if (in_array($image['id_image'], explode('_', $image_data['ids']))) {
                        if (!Image::getGlobalCover((int) $product->id)) {
                            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'image` SET cover = 1 WHERE id_product = ' . (int) $product->id . ' AND id_image = ' . (int) $image['id_image']);
                        }

                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image_shop WHERE `id_image` = ' . (int) $image['id_image']);
                        $cover = (int) $image['cover'] ? '1' : 'NULL';
                        foreach ($id_shop_list as $shop) {
                            Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'image_shop (`id_product`, `id_image`, `id_shop`, `cover`) VALUES(' . (int) $product->id . ', ' . (int) $image['id_image'] . ', ' . (int) $shop . ', ' . $cover . ')');
                        }

                        GiftCardModel::addGiftCardImageLang((int) $image['id_image'], $image_data['image_lang']);

                        foreach ($image_data['tags'] as $id_lang => $tags) {
                            Db::getInstance()->execute('
                                INSERT INTO ' . _DB_PREFIX_ . 'giftcard_tags (id_image, id_lang, tags) VALUES (' . (int) $image['id_image'] . ', ' . (int) $id_lang . ', "' . pSQL($tags) . '")
                                ON DUPLICATE KEY UPDATE tags="' . pSQL($tags) . '"
                            ');
                        }

                        GiftCardModel::addAmount($image['id_image'], Configuration::get('GIFTCARD_AMOUNT_CUSTOM_FROM_' . (int) $product->id), false, 0, 0);
                    }
                }
            }

            $amount_group_object = new AttributeGroup((int) Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
            $amount_attributes = GiftCardModel::getAttributes((int) $this->context->language->id, (int) $amount_group_object->id, (int) $id_shop_default);
            foreach ($amount_attributes as $amount_attribute) {
                $this->addComponentToShops(
                    [
                        'class_name' => $this->attribute_class,
                        'id' => (int) $amount_attribute['id_attribute'],
                    ],
                    $id_shop_default,
                    $id_shop_list
                );
            }

            $attributes = GiftCardModel::getDefaultAttributes($product->id, false, (int) $id_shop_default);
            $id_combination = $this->generateCombination($product->id, $attributes, $id_currency, $id_shop_list);
            GiftCardModel::deleteDefaultAttributes($product->id, $id_shop_list);
            GiftCardModel::setDefaultAttribute($product->id, $id_combination, $id_shop_list);

            return true;
        }

        return false;
    }

    public function getAdminLink($controller, $params = [])
    {
        if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
            return Context::getContext()->link->getAdminLink($controller, true, [], $params);
        }

        return Context::getContext()->link->getAdminLink($controller) . '&' . http_build_query($params);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        if (!$id_lang || $id_lang == Context::getContext()->language->id) {
            return parent::l($string, $specific);
        }

        global $_MODULE;

        $language = new Language((int) $id_lang);
        if (!Validate::isLoadedObject($language)) {
            $language = Context::getContext()->language;
        }

        if ($language instanceof LanguageCore) {
            $files_by_priority = [
                _PS_THEME_DIR_ . 'modules/' . $this->name . '/translations/' . $language->iso_code . '.php',
                _PS_THEME_DIR_ . 'modules/' . $this->name . '/' . $language->iso_code . '.php',
                _PS_MODULE_DIR_ . $this->name . '/translations/' . $language->iso_code . '.php',
                _PS_MODULE_DIR_ . $this->name . '/' . $language->iso_code . '.php',
            ];

            foreach ($files_by_priority as $file) {
                if (file_exists($file)) {
                    include_once $file;
                }
            }
        }

        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);
        $source = $specific ? $specific : $this->name;
        $current_key = strtolower('<{' . $this->name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
        $default_key = strtolower('<{' . $this->name . '}prestashop>' . $source) . '_' . $key;

        if (!empty($_MODULE[$current_key])) {
            $return = stripslashes($_MODULE[$current_key]);
        } elseif (!empty($_MODULE[$default_key])) {
            $return = stripslashes($_MODULE[$default_key]);
        } else {
            $return = stripslashes($string);
        }

        return htmlspecialchars($return, ENT_COMPAT, 'UTF-8');
    }

    public function getRequest()
    {
        $request = null;

        global $kernel;

        if (null !== $kernel && $kernel instanceof Symfony\Component\HttpKernel\KernelInterface) {
            $request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();
        }

        return $request;
    }

    public function getAttributeClass()
    {
        return version_compare(_PS_VERSION_, '8', '>=') ? 'ProductAttribute' : 'Attribute';
    }

    public function displayPrice(float $price, int $currencyId)
    {
        $currency = Currency::getCurrencyInstance((int) $currencyId);

        return version_compare(_PS_VERSION_, '1.7.6', '<')
            ? Tools::displayPrice((float) $price, (int) $currency->id)
            : Tools::getContextLocale(Context::getContext())->formatPrice((float) $price, $currency->iso_code);
    }

    public function runCronTask()
    {
        if (!$this->active) {
            throw new PrestaShopException('Module is not active');
        }

        $giftcards = GiftCardModel::getGiftcards(0);

        foreach ($giftcards as $giftcard) {
            if (Validate::isLoadedObject($cart_rule = new CartRule((int) $giftcard['id_cart_rule']))
                && (date('Y-m-d', strtotime($cart_rule->date_from)) <= date('Y-m-d'))
            ) {
                $order_detail = new OrderDetail((int) $giftcard['id_order_detail']);
                $order = new Order((int) $order_detail->id_order);
                $customer = new Customer((int) $order->id_customer);

                $langId = Validate::isLoadedObject($order) ? (int) $order->id_lang : (int) Context::getContext()->language->id;
                $shopId = Validate::isLoadedObject($order) ? (int) $order->id_shop : (int) Context::getContext()->shop->id;

                if ($this->sendEmail($langId, $customer, $cart_rule, (int) $giftcard['id_image'], (int) $giftcard['id_customization'], $shopId)) {
                    Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'giftcard` SET `sent` = 1 WHERE `id_giftcard` = ' . (int) $giftcard['id_giftcard']);

                    $combination = new Combination((int) $order_detail->product_attribute_id);
                    if (!GiftCardModel::existsInCart((int) $order_detail->product_attribute_id)
                        && !$combination->default_on
                        && (int) Configuration::get('GIFTCARD_USE_CACHE')) {
                        $combination->delete();
                    }
                }
            }
        }

        return;
    }
}
