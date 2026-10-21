<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   One license is granted for only one website.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class Cart extends CartCore
{
    public function getProducts(
        $refresh = false,
        $id_product = false,
        $id_country = null,
        $fullInfos = true,
        bool $keepOrderPrices = false,
        bool $shouldSplitGiftProductsQuantity = false
    ) {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            if (!$this->id) {
                return array();
            }
            // Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
            if ($this->_products !== null && !$refresh) {
                // Return product row with specified ID if it exists
                if (is_int($id_product)) {
                    foreach ($this->_products as $product) {
                        if ($product['id_product'] == $id_product) {
                            return array($product);
                        }
                    }
                    return array();
                }
                return $this->_products;
            }

            // Build query
            $sql = new DbQuery();

            // Build SELECT
            $sql->select('cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, pl.`name`, p.`is_virtual`,
                             pl.`description_short`, pl.`available_now`, pl.`available_later`, product_shop.`id_category_default`, p.`id_supplier`,
                             p.`id_manufacturer`, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`,
                             product_shop.`available_for_order`, product_shop.`price`, product_shop.`active`, product_shop.`unity`, product_shop.`unit_price_ratio`,
                             stock.`quantity` AS quantity_available, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, p.`weight`,
                             p.`date_add`, p.`date_upd`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`link_rewrite` AS category,
                             CONCAT(LPAD(cp.`id_product`, 10, 0), LPAD(IFNULL(cp.`id_product_attribute`, 0), 10, 0), IFNULL(cp.`id_address_delivery`, 0)) AS unique_id, cp.id_address_delivery,
                             product_shop.advanced_stock_management, ps.product_supplier_reference supplier_reference');

            // Build FROM
            $sql->from('cart_product', 'cp');

            // Build JOIN
            $sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
            $sql->innerJoin('product_shop', 'product_shop', '(product_shop.`id_shop` = cp.`id_shop` AND product_shop.`id_product` = p.`id_product`)');
            $sql->leftJoin('product_lang', 'pl', 'p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $this->id_lang . Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop'));
            $sql->leftJoin('category_lang', 'cl', ' product_shop.`id_category_default` = cl.`id_category` AND cl.`id_lang` = ' . (int) $this->id_lang . Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop'));
            $sql->leftJoin('product_supplier', 'ps', 'ps.`id_product` = cp.`id_product` AND ps.`id_product_attribute` = cp.`id_product_attribute` AND ps.`id_supplier` = p.`id_supplier`');

            // @todo test if everything is ok, then refactorise call of this method
            $sql->join(Product::sqlStock('cp', 'cp'));

            // Build WHERE clauses
            $sql->where('cp.`id_cart` = ' . (int) $this->id);
            if ($id_product) {
                $sql->where('cp.`id_product` = ' . (int) $id_product);
            }
            $sql->where('p.`id_product` IS NOT NULL');

            // Build ORDER BY
            $sql->orderBy('cp.`date_add`, cp.`id_product`, cp.`id_product_attribute` ASC');

            if (Customization::isFeatureActive()) {
                $sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
                $sql->leftJoin('customization', 'cu', 'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.`id_product_attribute` AND cu.`id_cart` = ' . (int) $this->id);
                $sql->groupBy('cp.`id_product_attribute`, cp.`id_product`, cp.`id_shop`');
            } else {
                $sql->select('NULL AS customization_quantity, NULL AS id_customization');
            }

            if (Combination::isFeatureActive()) {
                $sql->select(' product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
                IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
                (p.`weight`+ pa.`weight`) weight_attribute,
                 IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
                  IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
                  IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity,
                  IF(product_attribute_shop.wholesale_price > 0,  product_attribute_shop.wholesale_price, product_shop.`wholesale_price`) wholesale_price
                  ');

                $sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
                $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.`id_shop` = cp.`id_shop` AND product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute`)');
            } else {
                $sql->select(
                    'p.`reference` AS reference, p.`ean13`, p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity, product_shop.`wholesale_price` wholesale_price'
                );
            }

            $sql->select('image_shop.`id_image` id_image, il.`legend`');
            $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $this->id_shop);
            $sql->leftJoin('image_lang', 'il', 'il.`id_image` = image_shop.`id_image` AND il.`id_lang` = ' . (int) $this->id_lang);

            $result = Db::getInstance()->executeS($sql);

            // Reset the cache before the following return, or else an empty cart will add dozens of queries
            $products_ids = array();
            $pa_ids = array();
            if ($result) {
                foreach ($result as $key => $row) {
                    $products_ids[] = $row['id_product'];
                    $pa_ids[] = $row['id_product_attribute'];
                    $specific_price = SpecificPrice::getSpecificPrice($row['id_product'], $this->id_shop, $this->id_currency, $id_country, $this->id_shop_group, $row['cart_quantity'], $row['id_product_attribute'], $this->id_customer, $this->id);
                    if ($specific_price) {
                        $reduction_type_row = array('reduction_type' => $specific_price['reduction_type']);
                    } else {
                        $reduction_type_row = array('reduction_type' => 0);
                    }

                    $result[$key] = array_merge($row, $reduction_type_row);
                }
            }
            // Thus you can avoid one query per product, because there will be only one query for all the products of the cart
            Product::cacheProductsFeatures($products_ids);
            Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);

            $this->_products = array();
            if (empty($result)) {
                return array();
            }
            $cart_shop_context = Context::getContext()->cloneContext();

            foreach ($result as &$row) {
                if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0) {
                    $row['ecotax'] = (float) $row['ecotax_attr'];
                }

                $row['stock_quantity'] = (int) $row['quantity'];
                // for compatibility with 1.2 themes
                $row['quantity'] = (int) $row['cart_quantity'];

                if (isset($row['id_product_attribute']) && (int) $row['id_product_attribute'] && isset($row['weight_attribute'])) {
                    $row['weight'] = (float) $row['weight_attribute'];
                }

                if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
                    $address_id = (int) $this->id_address_invoice;
                } else {
                    $address_id = (int) $row['id_address_delivery'];
                }
                if (!Address::addressExists($address_id)) {
                    $address_id = null;
                }

                if ($cart_shop_context->shop->id != $row['id_shop']) {
                    $cart_shop_context->shop = new Shop((int) $row['id_shop']);
                }

                $address = Address::initialize($address_id, true);
                $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int) $row['id_product'], $cart_shop_context);
                $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();

                $specific_price_output = null;

                $row['price_without_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    true,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    false,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price_without_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    false,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    false,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price_with_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    true,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    true,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price'] = $row['price_with_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    false,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    true,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price_with_only_reduction_without_customer_discount_tax_incl'] = $row['price_with_only_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    true,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    true,
                    true,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price_with_only_reduction_without_customer_discount_tax_excl'] = $row['price_with_only_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    false,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    true,
                    true,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    true,
                    $cart_shop_context,
                    true,
                    $row['id_customization']
                ), 4);

                $row['price_without_customer_discount_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    true,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    false,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    false,
                    $cart_shop_context,
                    false,
                    $row['id_customization']
                ), 4);

                $row['price_without_customer_discount_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
                    (int) $row['id_product'],
                    false,
                    isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    false,
                    $row['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $address_id,
                    $specific_price_output,
                    true,
                    false,
                    $cart_shop_context,
                    false,
                    $row['id_customization']
                ), 4);

                if ($row['price_without_reduction_tax_incl'] < $row['price_without_customer_discount_tax_incl']) {
                    $percent_customer_discount = ($row['price_with_only_reduction_without_customer_discount_tax_incl'] * 100) / $row['price_without_customer_discount_tax_incl'];
                    $row['price_with_only_reduction_tax_incl'] = ($row['price_without_reduction_tax_incl'] * $percent_customer_discount) / 100;
                    $row['price_with_only_reduction_tax_excl'] = ($row['price_without_reduction_tax_excl'] * $percent_customer_discount) / 100;
                }

                switch (Configuration::get('PS_ROUND_TYPE')) {
                    case Order::ROUND_TOTAL:
                        $row['total'] = $row['price_with_reduction_tax_excl'] * (int) $row['cart_quantity'];
                        $row['total_wt'] = $row['price_with_reduction_tax_incl'] * (int) $row['cart_quantity'];
                        break;
                    case Order::ROUND_LINE:
                        // fixing wrong round mode
                        //$row['total'] = Tools::ps_round($row['price_with_reduction_without_tax'] * (int)$row['cart_quantity'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
                        $row['total'] = Tools::ps_round($row['price_with_reduction_tax_excl'], 4) * (int) $row['cart_quantity'];
                        //$row['total_wt'] = Tools::ps_round($row['price_with_reduction'] * (int)$row['cart_quantity'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
                        $row['total_wt'] = Tools::ps_round($row['price_with_reduction_tax_incl'], 4) * (int) $row['cart_quantity'];
                        break;

                    case Order::ROUND_ITEM:
                    default:
                        $row['total'] = Tools::ps_round($row['price_with_reduction_tax_excl'], 4) * (int) $row['cart_quantity'];
                        $row['total_wt'] = Tools::ps_round($row['price_with_reduction_tax_incl'], 4) * (int) $row['cart_quantity'];
                        break;
                }

                $row['price_wt'] = $row['price_with_reduction_tax_incl'];
                $row['description_short'] = Tools::nl2br($row['description_short']);

                // check if a image associated with the attribute exists
                if ($row['id_product_attribute']) {
                    $row2 = Image::getBestImageAttribute($row['id_shop'], $this->id_lang, $row['id_product'], $row['id_product_attribute']);
                    if ($row2) {
                        $row = array_merge($row, $row2);
                    }
                }

                $row['reduction_applies'] = ($specific_price_output && (float) $specific_price_output['reduction']);
                $row['quantity_discount_applies'] = ($specific_price_output && $row['cart_quantity'] >= (int) $specific_price_output['from_quantity']);
                $row['id_image'] = Product::defineProductImage($row, $this->id_lang);
                $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
                $row['features'] = Product::getFeaturesStatic((int) $row['id_product']);

                if (array_key_exists($row['id_product_attribute'] . '-' . $this->id_lang, self::$_attributesLists)) {
                    $row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'] . '-' . $this->id_lang]);
                }

                $row = Product::getTaxesInformations($row, $cart_shop_context);

                $this->_products[] = $row;
            }

            return $this->_products;
        } else {
            return parent::getProducts($refresh, $id_product, $id_country, $fullInfos, $keepOrderPrices);
        }
    }



    protected function applyProductCalculations($row, $shopContext, $productQuantity = null, bool $keepOrderPrices = false)
    {
        if (is_null($productQuantity)) {
            $productQuantity = (int) $row['cart_quantity'];
        }

        if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0) {
            $row['ecotax'] = (float) $row['ecotax_attr'];
        }
        $row['stock_quantity'] = (int) $row['quantity'];
        // for compatibility with 1.2 themes
        $row['quantity'] = $productQuantity;

        // get the customization weight impact
        $customization_weight = Customization::getCustomizationWeight($row['id_customization']);

        if (isset($row['id_product_attribute']) && (int) $row['id_product_attribute'] && isset($row['weight_attribute'])) {
            $row['weight_attribute'] += $customization_weight;
            $row['weight'] = (float) $row['weight_attribute'];
        } else {
            $row['weight'] += $customization_weight;
        }
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
            $address_id = (int) $this->id_address_invoice;
        } else {
            $address_id = isset($row['id_address_delivery']) ? (int) $row['id_address_delivery'] : (int) $this->id_address_delivery;
        }
        if (!Address::addressExists($address_id)) {
            $address_id = null;
        }

        if ($shopContext->shop->id != $row['id_shop']) {
            $shopContext->shop = new Shop((int) $row['id_shop']);
        }

        $address = Address::initialize($address_id, true);
        $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int) $row['id_product'], $shopContext);
        $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();

        $specific_price_output = null;
        $orderId = null;
        if ($keepOrderPrices) {
            $orderId = Order::getIdByCartId($this->id);
            $orderId = (int) $orderId ?: null;
        }

        if (!empty($orderId)) {
            $orderPrices = $this->getOrderPrices($row, $orderId, $productQuantity, $address_id, $shopContext, $specific_price_output);
            $row = array_merge($row, $orderPrices);
        } else {
            $cartPrices = $this->getCartPrices($row, $productQuantity, $address_id, $shopContext, $specific_price_output);
            $row = array_merge($row, $cartPrices);
        }

        $row['price_without_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            false,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price_without_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            false,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            false,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price_with_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            true,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price'] = $row['price_with_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            false,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            true,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price_with_only_reduction_without_customer_discount_tax_incl'] = $row['price_with_only_reduction_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            true,
            true,
            $productQuantity,
            true,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price_with_only_reduction_without_customer_discount_tax_excl'] = $row['price_with_only_reduction_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            false,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            true,
            true,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        ), 4);

        $row['price_without_customer_discount_tax_incl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            false,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            false,
            $shopContext,
            false,
            $row['id_customization']
        ), 4);

        $row['price_without_customer_discount_tax_excl'] = Tools::ps_round(Product::getPriceStatic(
            (int) $row['id_product'],
            false,
            isset($row['id_product_attribute']) ? (int) $row['id_product_attribute'] : null,
            6,
            null,
            false,
            false,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $address_id,
            $specific_price_output,
            true,
            false,
            $shopContext,
            false,
            $row['id_customization']
        ), 4);

        if ($row['price_without_reduction_tax_incl'] < $row['price_without_customer_discount_tax_incl']) {
            $percent_customer_discount = ($row['price_with_only_reduction_without_customer_discount_tax_incl'] * 100) / $row['price_without_customer_discount_tax_incl'];
            $row['price_with_only_reduction_tax_incl'] = ($row['price_without_reduction_tax_incl'] * $percent_customer_discount) / 100;
            $row['price_with_only_reduction_tax_excl'] = ($row['price_without_reduction_tax_excl'] * $percent_customer_discount) / 100;
        }
        switch (Configuration::get('PS_ROUND_TYPE')) {
            case Order::ROUND_TOTAL:
                $row['total'] = $row['price_with_reduction_tax_excl'] * $productQuantity;
                $row['total_wt'] = $row['price_with_reduction_tax_incl'] * $productQuantity;
                break;
            case Order::ROUND_LINE:
                $row['total'] = Tools::ps_round($row['price_with_reduction_tax_excl'], 4) * (int) $productQuantity;
                $row['total_wt'] = Tools::ps_round($row['price_with_reduction_tax_incl'], 4) * (int) $productQuantity;
                break;

            case Order::ROUND_ITEM:
            default:
                $row['total'] = Tools::ps_round($row['price_with_reduction_tax_excl'], 4) * $productQuantity;
                $row['total_wt'] = Tools::ps_round($row['price_with_reduction_tax_incl'], 4) * $productQuantity;
                break;
        }

        $row['price_wt'] = $row['price_with_reduction_tax_incl'];
        $row['description_short'] = Tools::nl2br($row['description_short']);

        // check if a image associated with the attribute exists
        if ($row['id_product_attribute']) {
            $row2 = Image::getBestImageAttribute($row['id_shop'], $this->id_lang, $row['id_product'], $row['id_product_attribute']);
            if ($row2) {
                $row = array_merge($row, $row2);
            }
        }

        $row['reduction_applies'] = ($specific_price_output && (float) $specific_price_output['reduction']);
        $row['quantity_discount_applies'] = ($specific_price_output && $productQuantity >= (int) $specific_price_output['from_quantity']);
        $row['id_image'] = Product::defineProductImage($row, $this->id_lang);
        $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
        $row['features'] = Product::getFeaturesStatic((int) $row['id_product']);

        if (array_key_exists($row['id_product_attribute'] . '-' . $this->id_lang, self::$_attributesLists)) {
            $row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'] . '-' . $this->id_lang]);
        }
        return Product::getTaxesInformations($row, $shopContext);
    }

    /**
     * @param array $productRow
     * @param int $orderId
     * @param int $productQuantity
     * @param int|null $addressId Customer's address id (for tax calculation)
     * @param Context $shopContext
     * @param array|false|null $specificPriceOutput
     *
     * @return array
     */
    private function getOrderPrices(
        array $productRow,
        int $orderId,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): array {
        $orderPrices = [];
        $orderPrices['price_without_reduction'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            true,
            false,
            true
        );

        $orderPrices['price_without_reduction_without_tax'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            false,
            false,
            true
        );

        $orderPrices['price_with_reduction'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            true,
            true,
            true
        );

        $orderPrices['price'] = $orderPrices['price_with_reduction_without_tax'] = Product::getPriceFromOrder(
            $orderId,
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : 0,
            false,
            true,
            true
        );

        // If the product price was not found in the order, use cart prices as fallback
        if (false !== array_search(null, $orderPrices)) {
            $cartPrices = $this->getCartPrices(
                $productRow,
                $productQuantity,
                $addressId,
                $shopContext,
                $specificPriceOutput
            );
            foreach ($orderPrices as $orderPrice => $value) {
                if (null === $value) {
                    $orderPrices[$orderPrice] = $cartPrices[$orderPrice];
                }
            }
        }

        return $orderPrices;
    }

    /**
     * @param array $productRow
     * @param int $productQuantity
     * @param int|null $addressId
     * @param Context $shopContext
     * @param array|false|null $specificPriceOutput
     *
     * @return array
     */
    private function getCartPrices(
        array $productRow,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): array {
        $cartPrices = [];
        $cartPrices['price_without_reduction'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            true,
            false,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price_without_reduction_without_tax'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            false,
            false,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price_with_reduction'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            true,
            true,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        $cartPrices['price'] = $cartPrices['price_with_reduction_without_tax'] = $this->getCartPriceFromCatalog(
            (int) $productRow['id_product'],
            isset($productRow['id_product_attribute']) ? (int) $productRow['id_product_attribute'] : null,
            (int) $productRow['id_customization'],
            false,
            true,
            true,
            $productQuantity,
            $addressId,
            $shopContext,
            $specificPriceOutput
        );

        return $cartPrices;
    }

    /**
     * @param int $productId
     * @param int $combinationId
     * @param int $customizationId
     * @param bool $withTaxes
     * @param bool $useReduction
     * @param bool $withEcoTax
     * @param int $productQuantity
     * @param int|null $addressId
     * @param Context $shopContext
     * @param array|false|null $specificPriceOutput
     *
     * @return float|null
     */
    private function getCartPriceFromCatalog(
        int $productId,
        int $combinationId,
        int $customizationId,
        bool $withTaxes,
        bool $useReduction,
        bool $withEcoTax,
        int $productQuantity,
        ?int $addressId,
        Context $shopContext,
        &$specificPriceOutput
    ): ?float {
        return Product::getPriceStatic(
            $productId,
            $withTaxes,
            $combinationId,
            6,
            null,
            false,
            $useReduction,
            $productQuantity,
            false,
            (int) $this->id_customer ? (int) $this->id_customer : null,
            (int) $this->id,
            $addressId,
            $specificPriceOutput,
            $withEcoTax,
            true,
            $shopContext,
            true,
            $customizationId
        );
    }

    protected function getPackageShippingCostFromModule(Carrier $carrier, $shipping_cost, $products)
    {
        if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
            if (!$carrier->shipping_external) {
                return $shipping_cost;
            }
            /** @var CarrierModule $module */
            $module = Module::getInstanceByName($carrier->external_module_name);

            if (!Validate::isLoadedObject($module)) {
                return false;
            }
            if (property_exists($module, 'id_carrier')) {
                $module->id_carrier = $carrier->id;
            }
            if (!$carrier->need_range) {
                return $module->getOrderShippingCostExternal($this);
            }
            if (method_exists($module, 'getPackageShippingCost')) {
                $shipping_cost = $module->getPackageShippingCost($this, $shipping_cost, $products);
            } else {
                $shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
            }
            return $shipping_cost;
        } else {
            return parent::getPackageShippingCostFromModule($carrier, $shipping_cost, $products);
        }
    }
}
