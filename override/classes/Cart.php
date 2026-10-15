<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class Cart extends CartCore
{
    public function getProducts($refresh = false, $id_product = false, $id_country = null, $fullInfo = true, bool $keepOrderPrices = false)
    {
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
            return parent::getProducts($refresh, $id_product, $id_country);
        }
    }

    /**
     * This function returns the total cart amount
     *
     * Possible values for $type:
     * Cart::ONLY_PRODUCTS
     * Cart::ONLY_DISCOUNTS
     * Cart::BOTH
     * Cart::BOTH_WITHOUT_SHIPPING
     * Cart::ONLY_SHIPPING
     * Cart::ONLY_WRAPPING
     * Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING
     * Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
     *
     * @param bool $withTaxes With or without taxes
     * @param int $type Total type
     * @param bool $use_cache Allow using cache of the method CartRule::getContextualValue
     * @return float Order total
     */
    public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true, bool $keepOrderPrices = false)
    {
        // Dependencies
        if (Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            // Dependencies
            $address_factory = Adapter_ServiceLocator::get('Adapter_AddressFactory');
            $price_calculator = Adapter_ServiceLocator::get('Adapter_ProductPriceCalculator');
            $configuration = Adapter_ServiceLocator::get('Core_Business_ConfigurationInterface');

            $ps_tax_address_type = $configuration->get('PS_TAX_ADDRESS_TYPE');
            $ps_use_ecotax = $configuration->get('PS_USE_ECOTAX');
            $ps_round_type = $configuration->get('PS_ROUND_TYPE');
            $ps_ecotax_tax_rules_group_id = $configuration->get('PS_ECOTAX_TAX_RULES_GROUP_ID');
            $compute_precision = $configuration->get('PosConstants::POS_PRICE_COMPUTE_PRECISION');

            if (!$this->id) {
                return 0;
            }

            $type = (int) $type;
            $array_type = array(
                Cart::ONLY_PRODUCTS,
                Cart::ONLY_DISCOUNTS,
                Cart::BOTH,
                Cart::BOTH_WITHOUT_SHIPPING,
                Cart::ONLY_SHIPPING,
                Cart::ONLY_WRAPPING,
                Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
                Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
            );

            // Define virtual context to prevent case where the cart is not the in the global context
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;

            if (!in_array($type, $array_type)) {
                die(Tools::displayError());
            }

            $with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));

            // if cart rules are not used
            if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive()) {
                return 0;
            }

            // no shipping cost if is a cart with only virtuals products
            $virtual = $this->isVirtualCart();
            if ($virtual && $type == Cart::ONLY_SHIPPING) {
                return 0;
            }

            if ($virtual && $type == Cart::BOTH) {
                $type = Cart::BOTH_WITHOUT_SHIPPING;
            }

            if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
                if (is_null($products) && is_null($id_carrier)) {
                    $shipping_fees = $this->getTotalShippingCost(null, (bool) $with_taxes);
                } else {
                    $shipping_fees = $this->getPackageShippingCost((int) $id_carrier, (bool) $with_taxes, null, $products);
                }
            } else {
                $shipping_fees = 0;
            }

            if ($type == Cart::ONLY_SHIPPING) {
                return $shipping_fees;
            }

            if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
                $type = Cart::ONLY_PRODUCTS;
            }

            $param_product = true;
            if (is_null($products)) {
                $param_product = false;
                $products = $this->getProducts(true);
            }

            if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING) {
                foreach ($products as $key => $product) {
                    if ($product['is_virtual']) {
                        unset($products[$key]);
                    }
                }
                $type = Cart::ONLY_PRODUCTS;
            }

            $order_total = 0;
            if (Tax::excludeTaxeOption()) {
                $with_taxes = false;
            }

            $products_total = array();
            $ecotax_total = 0;

            foreach ($products as $product) {
                // products refer to the cart details

                if ($virtual_context->shop->id != $product['id_shop']) {
                    $virtual_context->shop = new Shop((int) $product['id_shop']);
                }

                if ($ps_tax_address_type == 'id_address_invoice') {
                    $id_address = (int) $this->id_address_invoice;
                } else {
                    $id_address = (int) $product['id_address_delivery'];
                } // Get delivery address of the product from the cart
                if (!$address_factory->addressExists($id_address)) {
                    $id_address = null;
                }

                // The $null variable below is not used,
                // but it is necessary to pass it to getProductPrice because
                // it expects a reference.
                $null = null;
                $price = $price_calculator->getProductPrice(
                    (int) $product['id_product'],
                    $with_taxes,
                    (int) $product['id_product_attribute'],
                    6,
                    null,
                    false,
                    true,
                    $product['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $id_address,
                    $null,
                    $ps_use_ecotax,
                    true,
                    $virtual_context
                );

                $address = $address_factory->findOrCreate($id_address, true);

                if ($with_taxes) {
                    $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int) $product['id_product'], $virtual_context);
                    $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();
                } else {
                    $id_tax_rules_group = 0;
                }

                if (in_array($ps_round_type, array(Order::ROUND_ITEM, Order::ROUND_LINE))) {
                    if (!isset($products_total[$id_tax_rules_group])) {
                        $products_total[$id_tax_rules_group] = 0;
                    }
                } elseif (!isset($products_total[$id_tax_rules_group . '_' . $id_address])) {
                    $products_total[$id_tax_rules_group . '_' . $id_address] = 0;
                }

                switch ($ps_round_type) {
                    case Order::ROUND_TOTAL:
                        $products_total[$id_tax_rules_group . '_' . $id_address] += $price * (int) $product['cart_quantity'];
                        break;

                    case Order::ROUND_LINE:
                        // fixing wrong round mode
                        //$product_price = $price * $product['cart_quantity'];
                        $product_price = Tools::ps_round($price, PosConstants::POS_PRICE_COMPUTE_PRECISION) * $product['cart_quantity'];
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision);
                        break;

                    case Order::ROUND_ITEM:
                    default:
                        $product_price = /*$with_taxes ? $tax_calculator->addTaxes($price) : */
                        $price;
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision) * (int) $product['cart_quantity'];
                        break;
                }
            }

            foreach ($products_total as $key => $price) {
                $order_total += $price;
            }

            $order_total_products = $order_total;

            if ($type == Cart::ONLY_DISCOUNTS) {
                $order_total = 0;
            }

            // Wrapping Fees
            $wrapping_fees = 0;

            // With PS_ATCP_SHIPWRAP on the gift wrapping cost computation calls getOrderTotal with $type === Cart::ONLY_PRODUCTS, so the flag below prevents an infinite recursion.
            $include_gift_wrapping = (!$configuration->get('PS_ATCP_SHIPWRAP') || $type !== Cart::ONLY_PRODUCTS);

            if ($this->gift && $include_gift_wrapping) {
                $wrapping_fees = Tools::convertPrice(Tools::ps_round($this->getGiftWrappingPrice($with_taxes), $compute_precision), Currency::getCurrencyInstance((int) $this->id_currency));
            }
            if ($type == Cart::ONLY_WRAPPING) {
                return $wrapping_fees;
            }

            $order_total_discount = 0;
            $order_shipping_discount = 0;
            if (!in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive()) {
                // First, retrieve the cart rules associated to this "getOrderTotal"
                if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
                    $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
                } else {
                    $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
                    // Cart Rules array are merged manually in order to avoid doubles
                    foreach ($this->getCartRules(CartRule::FILTER_ACTION_GIFT) as $tmp_cart_rule) {
                        $flag = false;
                        foreach ($cart_rules as $cart_rule) {
                            if ($tmp_cart_rule['id_cart_rule'] == $cart_rule['id_cart_rule']) {
                                $flag = true;
                            }
                        }
                        if (!$flag) {
                            $cart_rules[] = $tmp_cart_rule;
                        }
                    }
                }

                $id_address_delivery = 0;
                if (isset($products[0])) {
                    $id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
                }
                $package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);

                // Then, calculate the contextual value for each one
                $flag = false;
                $has_fixed_amount_discount = false;
                foreach ($cart_rules as $cart_rule) {
                    // Check if this is a fixed amount discount (gift card) - applied after tax, so can exceed product total
                    if ((float) $cart_rule['obj']->reduction_amount > 0 && (float) $cart_rule['obj']->reduction_percent == 0) {
                        $has_fixed_amount_discount = true;
                    }
                    
                    // If the cart rule offers free shipping, add the shipping cost
                    if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule['obj']->free_shipping && !$flag) {
                        $order_shipping_discount = (float) Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), $compute_precision);
                        $flag = true;
                    }

                    // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                    if ((int) $cart_rule['obj']->gift_product) {
                        $in_order = false;
                        if (is_null($products)) {
                            $in_order = true;
                        } else {
                            foreach ($products as $product) {
                                if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute']) {
                                    $in_order = true;
                                }
                            }
                        }

                        if ($in_order) {
                            $order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
                        }
                    }

                    // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                    if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0) {
                        $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), $compute_precision);
                    }
                }
                // For fixed amount discounts: allow discount to exceed product total (can cover tax)
                // For percentage discounts: cap at product total (applied before tax)
                if ($has_fixed_amount_discount) {
                    $order_total_discount = Tools::ps_round($order_total_discount, 2) + (float) $order_shipping_discount;
                } else {
                    $order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float) $order_total_products) + (float) $order_shipping_discount;
                }
                $order_total -= $order_total_discount;
            }

            if ($type == Cart::BOTH) {
                $order_total += $shipping_fees + $wrapping_fees;
            }

            if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS) {
                return 0;
            }

            if ($type == Cart::ONLY_DISCOUNTS) {
                return $order_total_discount;
            }

            return Tools::ps_round((float) $order_total, $compute_precision);
        } else {
            // Dependencies
            /** @var \PrestaShop\PrestaShop\Adapter\Product\PriceCalculator $price_calculator */
            $price_calculator = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\Product\\PriceCalculator');
            $ps_use_ecotax = $this->configuration->get('PS_USE_ECOTAX');
            $ps_round_type = $this->configuration->get('PS_ROUND_TYPE');
            $ps_ecotax_tax_rules_group_id = $this->configuration->get('PS_ECOTAX_TAX_RULES_GROUP_ID');
            $compute_precision = $this->configuration->get('PosConstants::POS_PRICE_COMPUTE_PRECISION');

            if (!$this->id) {
                return 0;
            }

            $type = (int) $type;
            $array_type = array(
                Cart::ONLY_PRODUCTS,
                Cart::ONLY_DISCOUNTS,
                Cart::BOTH,
                Cart::BOTH_WITHOUT_SHIPPING,
                Cart::ONLY_SHIPPING,
                Cart::ONLY_WRAPPING,
                Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
                Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
            );

            // Define virtual context to prevent case where the cart is not the in the global context
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;

            if (!in_array($type, $array_type)) {
                die(Tools::displayError());
            }

            $with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));

            // if cart rules are not used
            if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive()) {
                return 0;
            }

            // no shipping cost if is a cart with only virtuals products
            $virtual = $this->isVirtualCart();
            if ($virtual && $type == Cart::ONLY_SHIPPING) {
                return 0;
            }

            if ($virtual && $type == Cart::BOTH) {
                $type = Cart::BOTH_WITHOUT_SHIPPING;
            }

            if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
                if (is_null($products) && is_null($id_carrier)) {
                    $shipping_fees = $this->getTotalShippingCost(null, (bool) $with_taxes);
                } else {
                    $shipping_fees = $this->getPackageShippingCost((int) $id_carrier, (bool) $with_taxes, null, $products);
                }
            } else {
                $shipping_fees = 0;
            }

            if ($type == Cart::ONLY_SHIPPING) {
                return $shipping_fees;
            }

            if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
                $type = Cart::ONLY_PRODUCTS;
            }

            $param_product = true;
            if (is_null($products)) {
                $param_product = false;
                $products = $this->getProducts();
            }

            if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING) {
                foreach ($products as $key => $product) {
                    if ($product['is_virtual']) {
                        unset($products[$key]);
                    }
                }
                $type = Cart::ONLY_PRODUCTS;
            }

            $order_total = 0;
            if (Tax::excludeTaxeOption()) {
                $with_taxes = false;
            }

            $products_total = array();
            $ecotax_total = 0;
            $productLines = $this->countProductLines($products);

            foreach ($products as $product) {
                // products refer to the cart details

                if (array_key_exists('is_gift', $product) && $product['is_gift']) {
                    // products given away may appear twice if added manually
                    // so we prevent adding their subtotal twice if another line is found
                    $productIndex = $product['id_product'] . '-' . $product['id_product_attribute'];
                    if ($productLines[$productIndex] > 1) {
                        continue;
                    }
                }

                if ($virtual_context->shop->id != $product['id_shop']) {
                    $virtual_context->shop = new Shop((int) $product['id_shop']);
                }

                $id_address = $this->getProductAddressId($product);

                // The $null variable below is not used,
                // but it is necessary to pass it to getProductPrice because
                // it expects a reference.
                $null = null;
                $price_with_reduction = Tools::ps_round(Product::getPriceStatic(
                    (int) $product['id_product'],
                    $with_taxes,
                    isset($product['id_product_attribute']) ? (int) $product['id_product_attribute'] : null,
                    6,
                    null,
                    false,
                    true,
                    $product['cart_quantity'],
                    false,
                    (int) $this->id_customer ? (int) $this->id_customer : null,
                    (int) $this->id,
                    $id_address,
                    $null,
                    $ps_use_ecotax,
                    true,
                    $virtual_context,
                    true,
                    $product['id_customization']
                ), 4);

                $price = Tools::ps_round($price_with_reduction, 2);

                $id_tax_rules_group = $this->findTaxRulesGroupId($with_taxes, $product, $virtual_context);

                if (in_array($ps_round_type, array(Order::ROUND_ITEM, Order::ROUND_LINE))) {
                    if (!isset($products_total[$id_tax_rules_group])) {
                        $products_total[$id_tax_rules_group] = 0;
                    }
                } elseif (!isset($products_total[$id_tax_rules_group . '_' . $id_address])) {
                    $products_total[$id_tax_rules_group . '_' . $id_address] = 0;
                }

                switch ($ps_round_type) {
                    case Order::ROUND_TOTAL:
                        $products_total[$id_tax_rules_group . '_' . $id_address] += $price * (int) $product['cart_quantity'];
                        break;

                    case Order::ROUND_LINE:
                        //$product_price = $price * $product['cart_quantity'];
                        $products_total[$id_tax_rules_group] += Tools::ps_round($price, $compute_precision) * (int) $product['cart_quantity'];
                        break;

                    case Order::ROUND_ITEM:
                    default:
                        $product_price = /*$with_taxes ? $tax_calculator->addTaxes($price) : */
                        $price;
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision) * (int) $product['cart_quantity'];
                        break;
                }
            }

            foreach ($products_total as $key => $price) {
                $order_total += $price;
            }

            $order_total_products = $order_total;

            if ($type == Cart::ONLY_DISCOUNTS) {
                $order_total = 0;
            }

            $wrappingFees = $this->calculateWrappingFees($with_taxes, $type);
            if ($type == Cart::ONLY_WRAPPING) {
                return $wrappingFees;
            }

            $order_total_discount = 0;
            $order_shipping_discount = 0;
            if (!in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive()) {
                $cart_rules = $this->getTotalCalculationCartRules($type, $with_shipping);

                $package = array(
                    'id_carrier' => $id_carrier,
                    'id_address' => $this->getDeliveryAddressId($products),
                    'products' => $products,
                );

                // Then, calculate the contextual value for each one
                $flag = false;
                $has_fixed_amount_discount = false;
                foreach ($cart_rules as $cart_rule) {
                    // Check if this is a fixed amount discount (gift card) - applied after tax, so can exceed product total
                    if ((float) $cart_rule['obj']->reduction_amount > 0 && (float) $cart_rule['obj']->reduction_percent == 0) {
                        $has_fixed_amount_discount = true;
                    }
                    
                    // If the cart rule offers free shipping, add the shipping cost
                    if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule['obj']->free_shipping && !$flag) {
                        $order_shipping_discount = (float) Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), $compute_precision);
                        $flag = true;
                    }

                    // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                    if (!$this->shouldExcludeGiftsDiscount && (int) $cart_rule['obj']->gift_product) {
                        $in_order = false;
                        if (is_null($products)) {
                            $in_order = true;
                        } else {
                            foreach ($products as $product) {
                                if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute']) {
                                    $in_order = true;
                                }
                            }
                        }

                        if ($in_order) {
                            $order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
                        }
                    }

                    // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                    if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0) {
                        $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), $compute_precision);
                    }
                }

                // For fixed amount discounts: allow discount to exceed product total (can cover tax)
                // For percentage discounts: cap at product total (applied before tax)
                if ($has_fixed_amount_discount) {
                    $order_total_discount = Tools::ps_round($order_total_discount, 2) + (float) $order_shipping_discount;
                } else {
                    $order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float) $order_total_products) + (float) $order_shipping_discount;
                }
                $order_total -= $order_total_discount;
            }

            if ($type == Cart::BOTH) {
                $order_total += $shipping_fees + $wrappingFees;
            }

            if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS) {
                return 0;
            }

            if ($type == Cart::ONLY_DISCOUNTS) {
                return $order_total_discount;
            }

            return Tools::ps_round((float) $order_total, $compute_precision);
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
            $address_id = (int) $row['id_address_delivery'];
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
}
