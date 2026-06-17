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

class GiftCardModel extends ObjectModel
{
    public static $customizations = array(
        0 => array('name' => 'name', 'value' => array('en' => 'Friend Name', 'fr' => 'Nom du destinataire')),
        1 => array('name' => 'email', 'value' => array('en' => 'Email', 'fr' => 'Email')),
        2 => array('name' => 'content', 'value' => array('en' => 'Email content', 'fr' => 'Contenu de l\'email')),
        3 => array('name' => 'date', 'value' => array('en' => 'Date of send', 'fr' => 'Date d\'envoi'))
    );

    public static $attributes_goup = array(
        0 => array('name' => 'template', 'value' => array('en' => 'Gift card template', 'fr' => 'Modèle de la carte cadeau')),
        1 => array('name' => 'amount', 'value' => array('en' => 'Gift card amount', 'fr' => 'Montant de la carte cadeau'))
    );

    const PRINT_AT_HOME = 1;
    const SEND_TO_FRIEND = 2;

    public static function getGiftcards($sent = null)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT gc.*
		FROM `'._DB_PREFIX_.'giftcard` gc
		'.(isset($sent) ? 'WHERE gc.`sent` = '.$sent : ''));
    }

    public static function getActiveGiftCards()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT gc.*
		FROM `'._DB_PREFIX_.'giftcard` gc
		LEFT JOIN `'._DB_PREFIX_.'cart_rule` cr ON gc.`id_cart_rule` = cr.`id_cart_rule`
		WHERE cr.`active` = 1');
    }

    public static function giftCardExists($id_cart_rule)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT gc.`id_giftcard`
		FROM `'._DB_PREFIX_.'giftcard` gc
		WHERE gc.`id_cart_rule` = '.(int)$id_cart_rule);
    }

    public static function getStatistics($module)
    {
        $result = array();
        $mail_error = 0;

        $sql = 'SELECT gc.*, o.reference as reference, o.id_order as id_order, cr.date_add as date_purschased,
        cr.date_from as date_from, cr.date_to, cr.code, cr.reduction_amount, cr.reduction_currency as id_currency,
        ocr.id_order as id_order_cart_rule, ocr.value, c.email as beneficiary
		FROM `'._DB_PREFIX_.'giftcard` gc
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od on gc.id_order_detail = od.id_order_detail
		LEFT JOIN `'._DB_PREFIX_.'orders` o on od.id_order = o.id_order
        LEFT JOIN `'._DB_PREFIX_.'customer` c on o.id_customer = c.id_customer
		LEFT JOIN `'._DB_PREFIX_.'cart_rule` cr on gc.id_cart_rule = cr.id_cart_rule
		LEFT JOIN `'._DB_PREFIX_.'order_cart_rule` ocr on cr.id_cart_rule = ocr.id_cart_rule
		ORDER BY gc.id_giftcard DESC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($result as &$row) {
            if ($row['id_customization']) {
                $customization = GiftCardModel::getCustomizedData($row['id_customization']);
                if (count($customization)
                    && (isset($customization['email']) && !empty($customization['email']))
                    && (isset($customization['name']) && !empty($customization['name']))
                    && (isset($customization['content']) && !empty($customization['content']))
                ) {
                    $row['beneficiary'] = $customization['email'];
                }
            }

            $row['img_url'] = Context::getContext()->link->getImageLink('cartes-cadeaux', $row['id_image']);
            $row['cart_rule_url'] = $module->getAdminLink('AdminCartRules', array('id_cart_rule' => $row['id_cart_rule'], 'updatecart_rule' => 1));
            $row['order_url'] = $module->getAdminLink('AdminOrders', array('id_order' => $row['id_order'], 'vieworder' => 1));
            if (!empty($row['id_order_cart_rule'])) {
                $order = new Order((int)$row['id_order_cart_rule']);
                $row['date_used'] = $order->date_add;
                $row['value'] = Tools::convertPriceFull($row['value'], Currency::getCurrencyInstance((int)$order->id_currency), Currency::getCurrencyInstance((int)$row['id_currency']));
            } else {
                $row['date_used'] = '';
            }

            $row['badge_value'] = 'none';
            if (!empty($row['value'])) {
                if ($row['value'] < $row['reduction_amount'] && $row['value'] > 0) {
                    $row['badge_value'] = 'partial';
                } elseif ($row['value'] == $row['reduction_amount']) {
                    $row['badge_value'] = 'total';
                }
            } else {
                $row['value'] = 0;
            }

            $row['should_be_sent'] = false;
            if (date('Y-m-d', strtotime($row['date_from'])) <= date('Y-m-d') && !$row['sent']) {
                $row['should_be_sent'] = true;
                $mail_error++;
            }
        }

        $result = array(
            'giftcards' => $result,
            'mail_error' => $mail_error,
            'currency_missing' => count(self::getCurrenciesNotIndexed())
        );

        return $result;
    }

    public static function getCurrenciesNotIndexed()
    {
        $currencies = Currency::getCurrencies(false, true, true);
        $not_idexed = array();

        foreach ($currencies as $currency) {
            $giftcard = new Product((int)Configuration::get('GIFTCARD_PROD_'.(int)$currency['id_currency']));
            if (Validate::isLoadedObject($giftcard) && ($giftcard->isAssociatedToShop() || Shop::getContextShopID(true) === null)) {
                continue;
            }

            $not_idexed[] = $currency['id_currency'];
        }

        return $not_idexed;
    }

    public static function isAttribute($id_attribute_group, $name, $id_lang)
    {
        if (is_callable(array('Attribute', 'isAttribute'))) {
            return Attribute::isAttribute($id_attribute_group, $name, $id_lang);
        }

        if (!Combination::isFeatureActive()) {
            return array();
        }

        $result = Db::getInstance()->getValue('
			SELECT COUNT(*)
			FROM `'._DB_PREFIX_.'attribute_group` ag
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl
				ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'attribute` a
				ON a.`id_attribute_group` = ag.`id_attribute_group`
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al
				ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
			'.Shop::addSqlAssociation('attribute_group', 'ag').'
			'.Shop::addSqlAssociation('attribute', 'a').'
			WHERE al.`name` = \''.pSQL($name).'\' AND ag.`id_attribute_group` = '.(int)$id_attribute_group.'
			ORDER BY agl.`name` ASC, a.`position` ASC
		');

        return ((int)$result > 0);
    }

    public static function getAttributes($id_lang, $id_attribute_group, $id_shop = null)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }
        return Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'attribute` a
			'.($id_shop !== null ? ' INNER JOIN `'._DB_PREFIX_.'attribute_shop` ash
				ON (a.`id_attribute` = ash.`id_attribute` AND ash.`id_shop` = '.(int)$id_shop.')' : '').'
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al
				ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
			WHERE a.`id_attribute_group` = '.(int)$id_attribute_group.'
			ORDER BY `position` ASC
		');
    }

    public static function getDefaultAttributes($id_product, $id_lang = false, $id_shop = null)
    {
        $ids = array();

        if (!$id_lang) {
            $id_lang = (int)Context::getContext()->language->id;
        }

        $cover = false;
        $product_images = Image::getImages((int)Context::getContext()->language->id, (int)$id_product);
        foreach ($product_images as $image) {
            if ($image['cover']) {
                $cover = $image['id_image'];
            }
        }

        $ids_attribute_group = array((int)Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE'), (int)Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT'));
        foreach ($ids_attribute_group as $key => $id_attribute_group) {
            $attributes = GiftCardModel::getAttributes($id_lang, (int)$id_attribute_group, $id_shop);
            foreach ($attributes as $attribute) {
                if ($id_attribute_group == (int)Configuration::get('GIFTCARD_ATTRGROUP_TEMPLATE')) {
                    $image = new Image((int)$attribute['name']);
                    if (!Validate::isLoadedObject($image) || $image->id != $cover) {
                        continue;
                    }

                    $ids[$key] = array((int)$attribute['id_attribute']);
                } elseif ($id_attribute_group == (int)Configuration::get('GIFTCARD_ATTRGROUP_AMOUNT')) {
                    $default_amount = GiftCardModel::getAmount($cover);
                    if (!$default_amount || $default_amount['amount'] != $attribute['name']) {
                        continue;
                    }

                    $ids[$key] = array((int)$attribute['id_attribute']);
                }
            }
        }

        return $ids;
    }

    public static function deleteDefaultAttributes($id_product, $shops = array())
    {
        $id_shop_list = $shops;
        if (!count($shops)) {
            $id_shop_list = Shop::getContextListShopID();
        }

        $result = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_attribute`
            SET default_on = NULL
            WHERE id_product = '.(int)$id_product
        );

        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_attribute_shop` pas
			INNER JOIN `'._DB_PREFIX_.'product_attribute` pa ON pas.`id_product_attribute` = pa.`id_product_attribute`
            SET pas.`default_on` = NULL
            WHERE pa.`id_product` = '.(int)$id_product.'
            AND pas.`id_shop` IN ('.implode(',', array_map('intval', $id_shop_list)).')'
        );

        return $result;
    }

    public static function setDefaultAttribute($id_product, $id_product_attribute, $shops = array())
    {
        $id_shop_list = $shops;
        if (!count($shops)) {
            $id_shop_list = Shop::getContextListShopID();
        }

        $result = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_attribute`
            SET default_on = 1
            WHERE id_product_attribute = '.(int)$id_product_attribute
        );

        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_attribute_shop`
            SET default_on = 1
            WHERE id_product_attribute = '.(int)$id_product_attribute.'
            AND id_shop IN ('.implode(',', array_map('intval', $id_shop_list)).')'
        );

        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product`
            SET cache_default_attribute = '.(int)$id_product_attribute.'
            WHERE id_product = '.(int)$id_product
        );

        $result &= Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_shop`
            SET cache_default_attribute = '.(int)$id_product_attribute.'
            WHERE id_product = '.(int)$id_product.'
            AND id_shop IN ('.implode(',', array_map('intval', $id_shop_list)).')'
        );

        return $result;
    }

    public static function getTags($id_lang, $images_id = array())
    {
        if (!$id_lang) {
            return array();
        }

        $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
			SELECT gt.`tags` as tags
			FROM `'._DB_PREFIX_.'giftcard_tags` gt
            LEFT JOIN `'._DB_PREFIX_.'image_shop` ims ON ims.`id_image` = gt.`id_image`
			WHERE gt.`id_lang` = '.(int)$id_lang.'
            AND gt.`id_image` IN ('.implode(',', array_map('intval', $images_id)).')
            AND ims.`id_shop` = '.(int)Context::getContext()->shop->id
        );

        return GiftCardModel::getTagsList($tags, true);
    }

    public static function getTagsByIdImage($id_image, $id_lang = null)
    {
        $tags = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
			SELECT tags as tags
			FROM `'._DB_PREFIX_.'giftcard_tags`
			WHERE `id_image` = '.(int)$id_image.
            ($id_lang !== null ? ' AND `id_lang` = '.(int)$id_lang.'' : '')
        );

        return GiftCardModel::getTagsList($tags, false);
    }

    public static function getTagsList($tags, $count)
    {
        $tags_list = array();
        foreach ($tags as $tag) {
            $result = explode(',', $tag['tags']);
            foreach ($result as $res) {
                if ($count) {
                    if (isset($tags_list[$res]) && !empty($tags_list[$res])) {
                        $tags_list[$res] = $tags_list[$res] + 1;
                    } elseif (!in_array($res, $tags_list) && !empty($res)) {
                        $tags_list[$res] = 1;
                    }
                } else {
                    $tags_list[] = $res;
                }
            }
        }
        return $tags_list;
    }

    public static function existsInCart($id_product_attribute)
    {
        return (bool)Db::getInstance()->getValue('
		SELECT gc.`id_giftcard`
		FROM `'._DB_PREFIX_.'giftcard` gc
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order_detail` = gc.`id_order_detail`
		WHERE od.`product_attribute_id` = '.(int)$id_product_attribute.'
		AND gc.`sent` = 0');
    }

    public static function addMeta($id_meta, $themes)
    {
        $theme_meta_value = array();
        foreach ($themes as $theme) {
            $theme_meta_value[] = array(
                'id_theme' => $theme->id,
                'id_meta' => (int)$id_meta,
                'left_column' => (int)$theme->default_left_column,
                'right_column' => (int)$theme->default_right_column
            );
        }

        return Db::getInstance()->insert('theme_meta', $theme_meta_value, false, true, DB::INSERT_IGNORE);
    }

    public static function deleteMetaById($id_meta)
    {
        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'theme_meta` WHERE id_meta='.(int)$id_meta);
    }

    public static function addCustomizationField($id_product)
    {
        $customization_field_data = array(
            'id_product' => (int)$id_product,
            'type' => (int)Product::CUSTOMIZE_TEXTFIELD,
            'required' => 0
        );

        Db::getInstance()->insert('customization_field', $customization_field_data);
        return Db::getInstance()->Insert_ID();
    }

    public static function addCustomizationFieldLang($id_customization_field, $customizationFieldLangData)
    {
        $data = array();
        foreach ($customizationFieldLangData as $id_shop => $row) {
            foreach ($row as $id_lang => $value) {
                if (version_compare(_PS_VERSION_, '1.6.0.12', '<') && ($id_shop != Configuration::get('PS_SHOP_DEFAULT'))) {
                    continue;
                }

                $fields = array(
                    'id_customization_field' => (int)$id_customization_field,
                    'id_lang' => (int)$id_lang,
                    'name' => pSQL($value)
                );

                if (version_compare(_PS_VERSION_, '1.6.0.12', '>=')) {
                    $fields = array_merge($fields, array(
                        'id_shop' => (int)$id_shop,
                    ));
                }

                $data[] = $fields;
            }
        }

        return Db::getInstance()->insert('customization_field_lang', $data);
    }

    public static function getCustomization($id_cart, $id_product_attribute, $quantity, $exclude_ids = array())
    {
        if (!$id_cart || !$id_product_attribute || !$quantity) {
            return 0;
        }

        $sql = count($exclude_ids) ? ' AND c.`id_customization` NOT IN ('.implode(',', array_map('intval', $exclude_ids)).')' : '';

        return (int)Db::getInstance()->getValue(
            '
			SELECT c.`id_customization`
			FROM `'._DB_PREFIX_.'customization` c
			WHERE c.`id_cart` = '.(int)$id_cart .'
			AND c.`id_product_attribute` = '.(int)$id_product_attribute .'
			AND c.`in_cart` = 1
            AND c.`quantity` = '.(int)$quantity .
            $sql
        );
    }

    public static function getCustomizedData($id_customization)
    {
        $customization = array();

        if (!(int)$id_customization || $id_customization == 0) {
            return $customization;
        }

        if (!$result = Db::getInstance()->executeS('
			SELECT c.`id_product`, cd.`index`, cd.`value`
			FROM `'._DB_PREFIX_.'customization` c
			LEFT JOIN `'._DB_PREFIX_.'customized_data` cd ON cd.`id_customization` = c.`id_customization`
			WHERE cd.`id_customization` = '.(int)$id_customization)) {
            return $customization;
        }

        foreach ($result as $row) {
            if ($row['index'] == Configuration::get('GIFTCARD_CUST_NAME_'.(int)$row['id_product'])) {
                $customization['name'] = $row['value'];
            } elseif ($row['index'] == Configuration::get('GIFTCARD_CUST_EMAIL_'.(int)$row['id_product'])) {
                $customization['email'] = $row['value'];
            } elseif ($row['index'] == Configuration::get('GIFTCARD_CUST_CONTENT_'.(int)$row['id_product'])) {
                $customization['content'] = $row['value'];
            } elseif ($row['index'] == Configuration::get('GIFTCARD_CUST_DATE_'.(int)$row['id_product'])) {
                $customization['date'] = $row['value'];
            }
        }

        return $customization;
    }

    public static function getCustomizedDataByIndex($id_customization, $index)
    {
        if (!(int)$id_customization || $id_customization == 0) {
            return false;
        }

        return Db::getInstance()->getValue('
		SELECT cd.`value`
		FROM `'._DB_PREFIX_.'customized_data` cd
		WHERE cd.`id_customization` = '.(int)$id_customization.'
		AND cd.`index` = '.(int)$index);
    }

    public static function getCustomizationFieldsNLabels($product_id, $id_shop = null)
    {
        if (!Customization::isFeatureActive()) {
            return false;
        }

        if (Shop::isFeatureActive() && !$id_shop) {
            $id_shop = 1;
        }

        $customizations = array();
        if (($customizations['fields'] = Db::getInstance()->executeS('
			SELECT `id_customization_field`, `type`, `required`
			FROM `'._DB_PREFIX_.'customization_field`
			WHERE `id_product` = '.(int)$product_id.'
			ORDER BY `id_customization_field`')) === false) {
            return false;
        }

        if (empty($customizations['fields'])) {
            return array();
        }

        $customization_field_ids = array();
        foreach ($customizations['fields'] as $customization_field) {
            $customization_field_ids[] = $customization_field['id_customization_field'];
        }

        if (($customization_labels = Db::getInstance()->executeS('
			SELECT `id_customization_field`, `id_lang`, `id_shop`, `name`
			FROM `'._DB_PREFIX_.'customization_field_lang`
			WHERE `id_customization_field` IN ('.implode(',', array_map('intval', $customization_field_ids)).')'.($id_shop ? ' AND `id_shop` = '.(int)$id_shop : '').'
			ORDER BY `id_customization_field`')) === false) {
            return false;
        }

        foreach ($customization_labels as $customization_label) {
            $customizations['labels'][$customization_label['id_customization_field']][] = $customization_label;
        }

        return $customizations;
    }

    public static function getImage($idLang, $idProduct, $idProductAttribute = null)
    {
        $attributeFilter = ($idProductAttribute ? ' AND ai.`id_product_attribute` = '.(int) $idProductAttribute : '');
        $sql = 'SELECT i.`id_image`
			FROM `'._DB_PREFIX_.'image` i
			LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image`)';

        if ($idProductAttribute) {
            $sql .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_image` ai ON (i.`id_image` = ai.`id_image`)';
        }

        $sql .= ' WHERE i.`id_product` = '.(int)$idProduct.' AND il.`id_lang` = '.(int) $idLang.$attributeFilter.'
			ORDER BY i.`position` ASC';

        return Db::getInstance()->getValue($sql);
    }

    public static function addGiftCardImageLang($id_image, $id_lang = 0)
    {
        if (!(int)$id_image || $id_image == 0) {
            return false;
        }

        return Db::getInstance()->execute('
        INSERT INTO `'._DB_PREFIX_.'giftcard_image_lang`
        (`id_image`, `id_lang`) VALUES ('.(int)$id_image.', '.(int)$id_lang.')
        ON DUPLICATE KEY UPDATE `id_lang` = '.(int)$id_lang);
    }

    public static function getGiftCardImageLang($id_image)
    {
        if (!(int)$id_image || $id_image == 0) {
            return false;
        }

        return (int)Db::getInstance()->getValue('
		SELECT gcil.`id_lang`
		FROM `'._DB_PREFIX_.'giftcard_image_lang` gcil
		WHERE gcil.`id_image` = '.(int)$id_image);
    }

    public static function getShopsByIdCurrency($id_currency)
    {
        $results = array();

        $rows = Db::getInstance()->executeS('
		SELECT cs.id_shop
		FROM  `'._DB_PREFIX_.'currency_shop` cs
		WHERE cs.`id_currency` = '.(int)$id_currency);

        foreach ($rows as $row) {
            $results[] = $row['id_shop'];
        }

        return $results;
    }

    public static function getGiftCardProducts($shops, $list = false)
    {
        $products = array();

        $currencies = Currency::getCurrencies(false, true, true);
        foreach ($currencies as $currency) {
            $product = new Product((int)Configuration::get('GIFTCARD_PROD_'.(int)$currency['id_currency']));
            if (!Validate::isLoadedObject($product)) {
                continue;
            }


            foreach ($shops as $shop) {
                if ($product->isAssociatedToShop($shop)) {
                    if ($list) {
                        $products[] = $product->id;
                    } else {
                        $products[] = $product;
                    }
                }
            }
        }

        return $products;
    }

    public static function updateGiftcardProductStatus($shops, $products, $active)
    {
        if (!count($shops) || !count($products)) {
            return false;
        }

        return Db::getInstance()->update(
            'product_shop',
            array('active' => (int)$active),
            'id_shop IN ('.implode(',', array_map('intval', $shops)).') AND id_product IN ('.implode(',', array_map('intval', $products)).')'
        );
    }

    public static function getNumberPurchased($id_order_detail)
    {
        return (int)Db::getInstance()->getValue('
		SELECT COUNT(*)
		FROM `'._DB_PREFIX_.'giftcard` gc
		WHERE gc.`id_order_detail` = '.(int)$id_order_detail);
    }

    public static function addAmount($id_image, $amount, $auto = false, $id_shop_group = null, $id_shop = null)
    {
        if (!$id_image || $id_image == 0
            || !$amount || $amount == 0
        ) {
            return false;
        }

        if ($id_shop_group === null) {
            $id_shop_group = Shop::getContextShopGroupID(true);
        }

        if ($id_shop === null) {
            $id_shop = Shop::getContextShopID(true);
        }

        return Db::getInstance()->execute('
        INSERT INTO `'._DB_PREFIX_.'giftcard_amounts`
        (`id_image`, `id_shop_group`, `id_shop`, `amount`, `auto`)
        VALUES ('.(int)$id_image.', '.(int)$id_shop_group.', '.(int)$id_shop.', '.(int)$amount.', '.(int)$auto.')
        ON DUPLICATE KEY UPDATE `amount` = '.(int)$amount.', `auto` = '.(int)$auto);
    }

    public static function getAmount($id_image, $id_shop_group = null, $id_shop = null)
    {
        if (!$id_image || $id_image == 0) {
            return false;
        }

        if ($id_shop_group === null) {
            $id_shop_group = Shop::getContextShopGroupID(true);
        }

        if ($id_shop === null) {
            $id_shop = Shop::getContextShopID(true);
        }

        $result = Db::getInstance()->getRow('
        SELECT gc.`amount`, gc.`auto`
        FROM `'._DB_PREFIX_.'giftcard_amounts` gc
        WHERE gc.`id_image` = '.(int)$id_image.'
        '.GiftCardModel::sqlRestriction($id_shop_group, $id_shop));

        if (!$result) {
            $result = Db::getInstance()->getRow('
            SELECT gc.`amount`, gc.`auto`
            FROM `'._DB_PREFIX_.'giftcard_amounts` gc
            WHERE gc.`id_image` = '.(int)$id_image.'
            '.GiftCardModel::sqlRestriction(0, 0));
        }

        return $result;
    }

    public static function deleteAmount($id_image, $id_shop_group = null, $id_shop = null)
    {
        if (!$id_image || $id_image == 0) {
            return false;
        }

        if ($id_shop_group === null) {
            $id_shop_group = Shop::getContextShopGroupID(true);
        }

        if ($id_shop === null) {
            $id_shop = Shop::getContextShopID(true);
        }

        return Db::getInstance()->execute('
        DELETE FROM `'._DB_PREFIX_.'giftcard_amounts`
        WHERE id_image='.(int)$id_image.'
        '.GiftCardModel::sqlRestriction($id_shop_group, $id_shop));
    }

    protected static function sqlRestriction($id_shop_group, $id_shop)
    {
        if ($id_shop) {
            return ' AND id_shop = '.(int)$id_shop;
        } elseif ($id_shop_group) {
            return ' AND id_shop_group = '.(int)$id_shop_group.' AND (id_shop IS NULL OR id_shop = 0)';
        } else {
            return ' AND (id_shop_group IS NULL OR id_shop_group = 0) AND (id_shop IS NULL OR id_shop = 0)';
        }
    }
}
