<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

//Class and its methods to handle some common features of Etsy Module
class KbMarketplaceIntegration extends ObjectModel
{

    /**
     * Returns all of the categories of store
     * @return string
     */
    public static function getAllCategories()
    {
        $categories = Category::getNestedCategories();
        $result_array = array();
        $result_array['success'] = $categories;
        $result_array['error'] = '';

        return $result_array;
    }

    /**
     * Returns the products inside a category
     * @param type $id_category
     * @param type $start
     * @param type $limit
     * @param type $order_by
     * @param type $sort_order
     * @return string
     */
    public static function getProductsByCategoryId($id_category = '', $start = 0, $limit = PHP_INT_MAX, $order_by = 'id_product', $sort_order = "ASC")
    {
        $result_array = array();
        if (!empty($id_category)) {
            $id_language = Context::getContext()->language->id;
            $product_list = Product::getProducts($id_language, $start, $limit, $order_by, $sort_order, $id_category);
            $result_array['success'] = $product_list;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }
        return $result_array;
    }

    /**
     * Returns an array of details of a product using id_product
     * @param type $id_product
     * @return string
     */
    public static function getProductByProductId($id_product, $language)
    {
        return new ProductCore($id_product, false, $language);
    }

    /**
     * Returns array of details about a category using id_category
     * @param type $id_category
     * @return string
     */
    public static function getCategoryByCategoryId($id_category = '')
    {
        $result_array = array();
        if (!empty($id_category)) {
            $category = new Category($id_category);
            $result_array['success'] = $category;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }

        return $result_array;
    }

    public static function getProductInventory($id_product = '', $id_product_attribute = '')
    {
        $inventory = 0;
        if (!empty($id_product)) {
            $id_shop = Context::getContext()->shop->id;
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && Product::usesAdvancedStockManagement($id_product) && StockAvailable::dependsOnStock($id_product, $id_shop)) {
                $warehouses = Warehouse::getWarehousesByProductId($id_product, $id_product_attribute);
                if (!empty($warehouses)) {
                    foreach ($warehouses as $warehouse) {
                        $inventory += Product::getRealQuantity($id_product, $id_product_attribute, $warehouse['id_warehouse']);
                    }
                } else {
                    $inventory = Product::getRealQuantity($id_product, $id_product_attribute);
                }
            } else {
                $inventory = Product::getRealQuantity($id_product, $id_product_attribute);
            }
        }
        return $inventory;
    }

    /**
     *
     * @param type $id_product
     * @param type $id_product_attribute
     * @return string
     */
    public static function getInventoryByProductAttributeId($id_product = '', $id_product_attribute = '')
    {
        $result_array = array();
        if (!empty($id_product) && !empty($id_product_attribute)) {
            // SELECT * FROM stock_available WHERE id_product_attribute = '3' AND id_product = '1'
            $query_get_inventory = "SELECT * FROM " . _DB_PREFIX_ . "stock_available WHERE id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "'";
            $inventory_details = Db::getInstance()->executeS($query_get_inventory);
            $result_array['success'] = $inventory_details;
            $result_array['error'] = "";
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }

        return $result_array;
    }

    /**
     * Returns an multi-dimensional array of Inventory details by id_product.
     * @param type $id_product
     * @return string
     */
    public static function getAttributesByProductId($id_product = '')
    {
        $result_array = array();
        if (!empty($id_product)) {
            $id_language = Context::getContext()->language->id;
            /**
             * SELECT p.id_product, pa.reference, pa.upc, pa.price, pai.id_image, pl.name, GROUP_CONCAT(DISTINCT(al.name) SEPARATOR ", ") as combination, pa.id_product_attribute, sa.quantity FROM product p LEFT JOIN product_attribute pa ON (p.id_product = pa.id_product) LEFT JOIN stock_available sa ON (p.id_product = sa.id_product AND pa.id_product_attribute = sa.id_product_attribute) LEFT JOIN product_lang pl ON (p.id_product = pl.id_product) LEFT JOIN product_attribute_combination pac ON (pa.id_product_attribute = pac.id_product_attribute) LEFT JOIN attribute_lang al ON (pac.id_attribute = al.id_attribute) LEFT JOIN product_attribute_image pai ON (pa.id_product_attribute = pai.id_product_attribute) WHERE pl.id_lang = 1 AND al.id_lang = 1 AND p.id_product = 1 GROUP BY pac.id_product_attribute
             */
            $query_get_inventory = 'SELECT
                                    p.id_product,
                                    pa.reference,
                                    pa.upc,
                                    pa.price,
                                    pai.id_image,
                                    pl.name,
                                    GROUP_CONCAT(DISTINCT(al.name) SEPARATOR ", ") as combination,
                                    pa.id_product_attribute,
                                    sa.quantity
                                    FROM ' . _DB_PREFIX_ . 'product p
                                    LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
                                    ON (p.id_product = pa.id_product)
                                    LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa
                                    ON (p.id_product = sa.id_product AND pa.id_product_attribute = sa.id_product_attribute)
                                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl
                                    ON (p.id_product = pl.id_product)
                                    LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                    ON (pa.id_product_attribute = pac.id_product_attribute)
                                    LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al
                                    ON (pac.id_attribute = al.id_attribute)
                                    LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_image pai
                                    ON (pa.id_product_attribute = pai.id_product_attribute)
                                    WHERE pl.id_lang = ' . (int) $id_language . ' AND al.id_lang = ' . (int) $id_language . ' AND p.id_product = ' . (int) $id_product .
                    ' GROUP BY pac.id_product_attribute';
            $inventory_details = Db::getInstance()->executeS($query_get_inventory, true, false);
            $result_array['success'] = $inventory_details;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Replaces the quantity of given attributes of a product by passed quantity
     * @param type $id_product
     * @param type $id_product_attribute
     * @param type $quantity
     * @return string
     */
    public static function updateQuantity($id_product = '', $id_product_attribute = '', $quantity = '')
    {
        $result_array = array();
        // If quantity is given and is number and is not negative
        if (is_numeric($quantity) && $quantity >= 0 && $quantity != "" && is_numeric($id_product_attribute) && is_numeric($id_product)) {
            $data = array();
            $data['quantity'] = $quantity;
            $where = "id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "'";
            Db::getInstance()->update('stock_available', $data, $where);
            $result_array['success'] = 'Quantity of product attribute id ' . $id_product_attribute . ' updated successfully.';
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Invalid parameters passed. Could not update quantity.';
        }
        return $result_array;
    }

    /**
     * Updates the quantity by given amount. Given amount is added into the current quantity
     * New quantity = Current quantity + Quantity to update
     * @param type $id_product
     * @param type $id_product_attribute
     * @param type $quantity_offset
     * @return array
     */
    public static function updateQuantityByOffset($id_product = '', $id_product_attribute = '', $quantity_offset = '')
    {
        $result_array = array();
        // If quantity is given and is number and is not negative
        if (is_numeric($quantity_offset) && $quantity_offset != "") {
            // Getting the current quantity of the product\
            // SELECT quantity FROM stock_available WHERE id_product_attribute = '3' AND id_product = '1'
            $query_get_quantity = "SELECT quantity FROM " . _DB_PREFIX_ . "stock_available WHERE id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "'";
            $quantity_details = Db::getInstance()->executeS($query_get_quantity);
            $current_quantity = $quantity_details[0]['quantity'];

            // Adding the given Quantity with the current quantity
            $new_quantity = $current_quantity + $quantity_offset;

            $data = array();
            $data['quantity'] = $new_quantity;
            $where = "id_product_attribute = '" . (int) $id_product_attribute . "' AND id_product = '" . (int) $id_product . "'";
            Db::getInstance()->update('stock_available', $data, $where);
            $result_array['success'] = 'Quantity of product attribute id ' . $id_product_attribute . ' updated successfully.';
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Invalid parameter(s) passed. Could not update quantity.';
        }
        return $result_array;
    }

    /**
     * Get count for no of products in a category
     * @param type $id_category
     * @return string
     */
    public static function getCountProductByCategoryId($id_category = '')
    {
        $result_array = array();
        if (is_numeric($id_category) && $id_category > 1) {
            $result_product_count = Db::getInstance()->ExecuteS('SELECT COUNT(ac.`id_product`) as totalProducts

                                                                FROM `' . _DB_PREFIX_ . 'category_product` ac

                                                                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = ac.`id_product`

                                                                WHERE ac.`id_category` = ' . (int) $id_category . ' AND p.`active` = 1');


            $result_array['success'] = $result_product_count[0]['totalProducts'];
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Get count for no of products by id_default_category
     * @param type $id_category
     * @return string
     */
    public static function getCountProductByDefaultCategoryId($id_category = '')
    {
        $result_array = array();
        if (is_numeric($id_category) && $id_category > 1) {
            $sql = 'SELECT 
                    count(p.id_product) as totalProducts
                    FROM  ' . _DB_PREFIX_ . 'product  p
                    ' . Shop::addSqlAssociation('product', 'p') . '
                    LEFT JOIN  ' . _DB_PREFIX_ . 'product_lang  pl ON (p. id_product  = pl. id_product  ' . Shop::addSqlRestrictionOnLang('pl') . ')
                    LEFT JOIN  ' . _DB_PREFIX_ . 'manufacturer  m ON (m. id_manufacturer  = p. id_manufacturer )
                    LEFT JOIN  ' . _DB_PREFIX_ . 'supplier  s ON (s. id_supplier  = p. id_supplier )
                    WHERE pl. id_lang  = ' . (int) Context::getContext()->language->id . ' AND product_shop. id_category_default  = ' . (int) $id_category . ' AND p.active = 1';
            $result_product_count = Db::getInstance()->ExecuteS($sql, true, false);

            $result_array['success'] = $result_product_count[0]['totalProducts'];
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameter is missing!';
        }
        return $result_array;
    }

    /**
     * Returns the products of a default category
     * @param type $id_category
     * @param type $start
     * @param type $limit
     * @param type $order_by
     * @param type $sort_order
     * @return string
     */
    public static function getProductsByDefaultCategoryId($id_category = '', $start = 0, $limit = PHP_INT_MAX, $order_by = 'id_product', $sort_order = "ASC")
    {
        $result_array = array();
        if (!empty($id_category) && $id_category > 1) {
            $sql = 'SELECT 
                    p.*, 
                    product_shop.*, 
                    pl.* , 
                    m.name AS manufacturer_name, 
                    s.name  AS supplier_name
                    FROM  ' . _DB_PREFIX_ . 'product  p
                    ' . Shop::addSqlAssociation('product', 'p') . '
                    LEFT JOIN  ' . _DB_PREFIX_ . 'product_lang  pl ON (p. id_product  = pl. id_product  ' . Shop::addSqlRestrictionOnLang('pl') . ')
                    LEFT JOIN  ' . _DB_PREFIX_ . 'manufacturer  m ON (m. id_manufacturer  = p. id_manufacturer )
                    LEFT JOIN  ' . _DB_PREFIX_ . 'supplier  s ON (s. id_supplier  = p. id_supplier )
                    WHERE pl. id_lang  = ' . (int) Context::getContext()->language->id . ' AND product_shop. id_category_default  = ' . (int) $id_category . ' AND p.active = 1
                    GROUP BY p.id_product
                ORDER BY p.' . pSQL($order_by) . ' ' . pSQL($sort_order) . ' LIMIT ' . (int) $start . ',' . (int) $limit;
            $result_product = Db::getInstance()->executeS($sql, true, false);
            $result_array['success'] = $result_product;
            $result_array['error'] = '';
        } else {
            $result_array['success'] = '';
            $result_array['error'] = 'Parameters are missing!';
        }
        return $result_array;
    }

    /**
     * This function writes order into prestashop
     * @param type $array_order_details
     * @throws PrestaShopException
     */
    public static function writeOrderIntoDb($module, $array_order_details)
    {
        $success = array();
        try {
            $email = $array_order_details['customer']['email'];
            $firstname = $array_order_details['customer']['firstname'];
            $lastname = $array_order_details['customer']['lastname'];
            $address1 = $array_order_details['customer']['address1'];

            $postcode = $array_order_details['customer']['postcode'];
            $city = $array_order_details['customer']['city'];
            $phone_mobile = $array_order_details['customer']['phone_mobile'];

            if (isset($array_order_details['customer']['address2'])) {
                $address2 = $array_order_details['customer']['address2'];
            } else {
                $address2 = "";
            }

            if (isset($array_order_details['customer']['id_state'])) {
                $id_state = $array_order_details['customer']['id_state'];
            } else {
                $id_state = 0;
            }

            if (isset($array_order_details['customer']['id_country'])) {
                $id_country = $array_order_details['customer']['id_country'];
            } else {
                $id_country = 0;
            }


            //$id_customer from addCustomer function
            $id_customer = KbMarketplaceIntegration::addCustomer($firstname, $lastname, $email);
            //$id_address from writeOrderAddress function
            $id_address = KbMarketplaceIntegration::writeOrderAddress($firstname, $lastname, $address1, $address2, $postcode, $city, $id_state, $id_country, $phone_mobile, $id_customer);
            // Getting the customer id and address id according to the given email id
            if ($id_customer == 0) {
                // SELECT id_customer from customer WHERE email = 'zombie@zom5.zom'"
                $query = "SELECT id_customer from " . _DB_PREFIX_ . "customer WHERE email = '" . pSQL($email) . "'";

                $result = Db::getInstance()->executeS($query);
                $id_customer = $result[0]['id_customer'];
            }
            if ($id_address == 0) {
                // SELECT id_address from address WHERE id_customer = '24'
                $query = "SELECT id_address from " . _DB_PREFIX_ . "address WHERE id_customer = '" . (int) $id_customer . "'";
                $result = Db::getInstance()->executeS($query);
                $id_address = $result[0]['id_address'];
            }

            $cart_id_address_delivery = $id_address;

            $cart_id_lang = $array_order_details['order']['id_language'];
            $cart_currency_iso_code = $array_order_details['order']['currency_iso_code'];

            $cart_id_currency = KbMarketplaceIntegration::getCurrencyId($cart_currency_iso_code);
            $id_currency = KbMarketplaceIntegration::getCurrencyId($cart_currency_iso_code);

            $cart_id_currency = (int) $cart_id_currency['success'];
            $id_currency = (int) $id_currency['success'];

            $name_carrier = $array_order_details['order']['name_carrier'];
            $id_carrier = KbMarketplaceIntegration::createCarrier($name_carrier);
            $cart_id_carrier = $id_carrier;
            $package = array('id_carrier' => $id_carrier);

            $payment_method = $array_order_details['order']['payment_method'];
            // Warehouse id
            $id_warehouse = $array_order_details['order']['id_warehouse'];

            $cart_recyclable = $array_order_details['order']['cart_recyclable'];
            $cart_gift = $array_order_details['order']['cart_gift'];
            $id_shop = $array_order_details['order']['id_shop'];
            $id_shop_group = $array_order_details['order']['id_shop_group'];
            $current_state = $array_order_details['order']['current_state'];

            // Creating random security key
            $secure_key = md5(uniqid(rand(), true));
            $order_reference = $array_order_details['order']['order_reference'];

            $total_paid_real = $array_order_details['order']['total_paid_real'];
            $total_products = $array_order_details['order']['total_products'];
            $total_products_wt = $array_order_details['order']['total_products_wt'];
            $total_discounts_tax_excl = $array_order_details['order']['total_discounts_tax_excl'];
            $total_discounts_tax_incl = $array_order_details['order']['total_discounts_tax_incl'];
            $total_shipping_tax_excl = $array_order_details['order']['total_shipping_tax_excl'];
            $total_shipping_tax_incl = $array_order_details['order']['total_shipping_tax_incl'];

            $total_wrapping_tax_excl = $array_order_details['order']['total_wrapping_tax_excl'];
            $total_wrapping_tax_incl = $array_order_details['order']['total_wrapping_tax_incl'];
            $total_paid_tax_excl = $array_order_details['order']['total_paid_tax_excl'];
            $total_paid_tax_incl = $array_order_details['order']['total_paid_tax_incl'];
            $invoice_date = $array_order_details['order']['invoice_date'];
            $delivery_date = $array_order_details['order']['delivery_date'];

            $result_array = KbMarketplaceIntegration::validateDetails($array_order_details);
            $is_okay = $result_array['is_okay'];
            $validation_errors = array();
            if (isset($result_array['validation_errors'])) {
                $validation_errors = $result_array['validation_errors'];
            }

            // If we get some errors at the time of validation then return them in an array
            if (!empty($validation_errors)) {
                return array('error' => $validation_errors);
            }

            // If values are valid
            if ($is_okay == 1) {
                $error = '';
                $package = array();
                $package['product_list'] = array();
                $package['product_list'] = $array_order_details['products'];
                $product_list = array();
                $ps_not_exist_product_list = array();
                /*
                * changes by rishabh jain for tax rule
                */
                $total_tax = 0;
                /*
                 * changes over
                 */
                $is_enabled_tax_breakup = Configuration::get('etsy_order_tax');
                if (!empty($array_order_details['products'])) {
                    foreach ($array_order_details['products'] as $products) {
                        $total_tax_product = 0;
                        if ($products['id_product'] != 0) {
                            $data  = self::getProductByProductId($products['id_product'], $array_order_details['order']['id_language']);
                            if (!empty($data->id)) {
                                /*
                                 * changes by rishabh jain for tax rule
                                 */
                                if ($is_enabled_tax_breakup) {
                                    $tax = new Tax();
                                    $tax_rate = $tax->getProductTaxRate($products['id_product'], $id_address);
                                    $products['id_tax_rules_group'] = (int) Product::getIdTaxRulesGroupByIdProduct($products['id_product']);
                                    if (isset($tax_rate) && $tax_rate > 0) {
                                        $products['price'] = Tools::ps_round($products['price_wt']/(0.01*$tax_rate + 1), 2);
                                        $products['total'] = $products['price'] * $products['cart_quantity'];
                                        $total_tax += ($products['total_wt'] - $products['total']);
                                    }
                                }
                                /*
                                 * changes over
                                 */
                                $product_list[] = $products;
                            } else {
                                $ps_not_exist_product_list[] = $products;
                            }
                        } else {
                            $ps_not_exist_product_list[] = $products;
                        }
                    }
                }
                /*
                * changes by rishabh jain for tax rule
                */
                $total_paid_tax_excl -= $total_tax;
                $total_products -= $total_tax;
                /*
                 * changes over
                 */
                $package['product_list'] = $product_list;
                $cart = new Cart();
                $cart->id_customer = $id_customer;
                $cart->id_address_delivery = $cart_id_address_delivery;
                $cart->id_address_invoice = $cart->id_address_delivery;
                $cart->id_lang = $cart_id_lang;
                $cart->id_currency = $cart_id_currency;
                $cart->id_carrier = $cart_id_carrier;
                $cart->recyclable = $cart_recyclable;
                $cart->gift = $cart_gift;
                $customer = new Customer($id_customer);
                $cart->secure_key = $customer->secure_key;
                $cart->add();
                Context::getContext()->cookie->id_cart = (int) ($cart->id);
                $cart->update();

                $order = new Order();
                $order->product_list = $package['product_list'];

                $order->id_carrier = $id_carrier;

                $order->id_customer = $id_customer;
                $order->id_address_invoice = $id_address;
                $order->id_address_delivery = $id_address;
                $order->id_currency = $id_currency;
                $order->id_lang = $cart_id_lang;
                $order->id_cart = Context::getContext()->cookie->id_cart;
                $order->reference = $order_reference;
                $order->id_shop = $id_shop;
                $order->id_shop_group = $id_shop_group;
                $order->current_state = $current_state;

                $order->secure_key = $secure_key;

                $order->payment = $payment_method;
                if (isset($module)) {
                    $order->module = $module;
                }
                $order->recyclable = $cart_recyclable;
                $order->gift = (int) $cart_gift;
                $order->gift_message = '';
                $order->mobile_theme = '';
                $order->conversion_rate = Context::getContext()->currency->conversion_rate;
                //$amount_paid = !$dont_touch_amount ? Tools::ps_round((float)$amount_paid, 2) : $amount_paid;
                $order->total_paid_real = $total_paid_real;

                $order->total_products = $total_products;
                $order->total_products_wt = $total_products_wt;
                $order->total_discounts_tax_excl = $total_discounts_tax_excl;
                $order->total_discounts_tax_incl = $total_discounts_tax_incl;
                $order->total_discounts = $order->total_discounts_tax_incl;

                $order->total_shipping_tax_excl = $total_shipping_tax_excl;
                $order->total_shipping_tax_incl = $total_shipping_tax_incl;
                $order->total_shipping = $order->total_shipping_tax_incl;

                $order->total_wrapping_tax_excl = $total_wrapping_tax_excl;
                $order->total_wrapping_tax_incl = $total_wrapping_tax_incl;
                $order->total_wrapping = $order->total_wrapping_tax_incl;

                $order->total_paid_tax_excl = $total_paid_tax_excl;
                $order->total_paid_tax_incl = $total_paid_tax_incl;
                $order->total_paid = $order->total_paid_tax_incl;
                $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                $order->round_type = Configuration::get('PS_ROUND_TYPE');

                $order->invoice_date = $invoice_date;
                $order->delivery_date = $delivery_date;

                //Creating order
                $order->add();

                if (!empty($ps_not_exist_product_list)) {
                    foreach ($ps_not_exist_product_list as $prod_list) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'order_detail SET '
                                . 'id_order_detail = null,'
                                . 'id_order = ' . (int) $order->id . ','
                                . 'id_order_invoice = 0,'
                                . 'id_warehouse = 0,'
                                . 'id_shop = ' . (int) Context::getContext()->shop->id . ','
                                . 'product_id = ' . (int) $prod_list['id_product'] . ','
                                . 'product_attribute_id = ' . (int) $prod_list['id_product_attribute'] . ','
                                . 'product_name = "' . pSQL($prod_list['name']) . '",'
                                . 'product_quantity_in_stock = ' . (int) $prod_list['stock_quantity'] . ','
                                . 'product_quantity = ' . (int) $prod_list['cart_quantity'] . ','
                                . 'product_price = "' . pSQL($prod_list['price']) . '",'
                                . 'product_ean13 = "' . pSQL($prod_list['ean13']) . '",'
                                . 'product_upc = "' . pSQL($prod_list['upc']) . '",'
                                . 'product_reference = "' . pSQL($prod_list['reference']) . '",'
                                . 'product_supplier_reference = "' . pSQL($prod_list['supplier_reference']) . '",'
                                . 'product_weight = ' . (int) $prod_list['weight'] . ','
                                . 'tax_name ="",'
                                . 'total_price_tax_incl = "' . pSQL($prod_list['price']) . '",'
                                . 'total_price_tax_excl = "' . pSQL($prod_list['total_wt']) . '",'
                                . 'unit_price_tax_incl = "' . pSQL($prod_list['price']) . '",'
                                . 'unit_price_tax_excl = "' . pSQL($prod_list['price']) . '",'
                                . 'original_product_price = "' . pSQL($prod_list['price']) . '",'
                                . 'original_wholesale_price = "' . pSQL($prod_list['wholesale_price']) . '"');
                    }
                }

                // Insert order entry in Order history table
                $order_history = new OrderHistory($order->id);
                $order_history->id_order = $order->id;
                $order_history->id_order_state = $current_state;
                $order_history->id_employee = 0;
                $order_history->add();

                // Insert new Order detail list using cart for the current order
                $cart->setTaxCalculationMethod();
                $order_detail = new OrderDetail(null, null, Context::getContext());
                $order_detail->createList($order, $cart, $current_state, $order->product_list, 0, true, $id_warehouse);

                //Adding an entry in order_carrier table
                $order_carrier = new OrderCarrier();
                $order_carrier->id_order = (int) $order->id;
                $order_carrier->id_carrier = $id_carrier;
                $order_carrier->weight = (float) $order->getTotalWeight();
                $order_carrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
                $order_carrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
                $order_carrier->add();

                $new_os = $order->getCurrentOrderState();

                if ($new_os->invoice && !$order->invoice_number) {
                    $order->setInvoice(true);
                } elseif ($new_os->delivery && !$order->delivery_number) {
                    $order->setDeliverySlip();
                }

                // set orders as paid
                if ($new_os->paid == 1) {
                    $invoices = $order->getInvoicesCollection();
                    if ($order->total_paid != 0) {
                        $payment_method = Module::getInstanceByName($order->module);
                    }

                    foreach ($invoices as $invoice) {
                        /** @var OrderInvoice $invoice */
                        $rest_paid = $invoice->getRestPaid();
                        if ($rest_paid > 0) {
                            $payment = new OrderPayment();
                            $payment->order_reference = Tools::substr($order->reference, 0, 9);
                            $payment->id_currency = $order->id_currency;
                            $payment->amount = $rest_paid;

                            if ($order->total_paid != 0) {
                                $payment->payment_method = $payment_method->displayName;
                            } else {
                                $payment->payment_method = null;
                            }

                            // Update total_paid_real value for backward compatibility reasons
                            if ($payment->id_currency == $order->id_currency) {
                                $order->total_paid_real += $payment->amount;
                            } else {
                                $order->total_paid_real += Tools::ps_round(Tools::convertPrice($payment->amount, $payment->id_currency, false), 2);
                            }
                            $order->save();

                            $payment->conversion_rate = ($order ? $order->conversion_rate : 1);
                            $payment->save();
                            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment` 
                                (`id_order_invoice`, `id_order_payment`, `id_order`) 
                                VALUES(' . (int) $invoice->id . ', ' . (int) $payment->id . ', ' . (int) $order->id . ')');
                        }
                    }
                }

                // changes by rishabh jain for order message functionality
                if ($array_order_details['order']['order_msg'] != '') {
                    $customer_obj = new Customer($order->id_customer);
                    $id_customer_thread = CustomerThread::getIdCustomerThreadByEmailAndIdOrder(
                        $customer_obj->email,
                        $order->id
                    );
                    if (!$id_customer_thread) {
                        $customer_thread = new CustomerThread();
                        $customer_thread->id_contact = 0;
                        $customer_thread->id_customer = (int) $order->id_customer;
                        $customer_thread->id_shop = (int) $order->id_shop;
                        $customer_thread->id_order = (int) $order->id;
                        $customer_thread->id_lang = (int) $order->id_lang;
                        $customer_thread->email = $customer_obj->email;
                        $customer_thread->status = 'open';
                        $customer_thread->token = Tools::passwdGen(12);
                        $customer_thread->add();
                    } else {
                        $customer_thread = new CustomerThread((int) $id_customer_thread);
                    }

                    $customer_message = new CustomerMessage();
                    $customer_message->id_customer_thread = $customer_thread->id;
                    $customer_message->id_employee        = 0;
                    $customer_message->message            = $array_order_details['order']['order_msg'];
                    $customer_message->private            = 1;
                    $customer_message->add();
                }
                // changes over
                
                $success = array(
                    'message' => "Order created successfully.",
                    'order_id' => $order->id,
                    'email' => $email,
                    'id_customer' => $id_customer,
                    'id_address' => $id_address,
                    'id_carrier' => $id_carrier,
                    'name_carrier' => $name_carrier
                );
            } else {
                $error = "Something went wrong. Please check the format of the array given in parameters.";
            }
        } catch (Exception $ex) {
            $error[0] = "Some error occured. Please find the traces below.";
            $error[1] = "Exception ~ " . $ex->getMessage();
            $error[2] = $ex;
        }
        if (count($success)) {
            $result_array['success'] = $success;
        }
        $result_array['error'] = $error;
        return $result_array;
    }

    /**
     * Order address is added into the database and id_address for the last inserted address is returned
     * @param type $firstname
     * @param type $lastname
     * @param type $address1
     * @param type $postcode
     * @param type $city
     * @param type $phone_mobile
     * @return type
     */
    private static function writeOrderAddress($firstname, $lastname, $address1, $address2, $postcode, $city, $id_state, $id_country, $phone_mobile, $id_customer)
    {
        $id_address = '';
        try {
            $query = "SELECT id_address FROM " . _DB_PREFIX_ . "address WHERE firstname = '" . pSQL($firstname) . "' AND lastname = '" . pSQL($lastname) . "' AND address1 = '" . pSQL($address1) . "' AND postcode = '" . pSQL($postcode) . "' AND city = '" . pSQL($city) . "' AND phone_mobile = '" . pSQL($phone_mobile) . "'";
            $result = Db::getInstance()->getRow($query);
            if (isset($result['id_address'])) {
                $id_address = (int) $result['id_address'];
            } else {
                Db::getInstance()->insert('address', array(
                    'firstname' => pSQL($firstname),
                    'lastname' => pSQL($lastname),
                    'id_customer' => (int) $id_customer,
                    'address1' => pSQL($address1),
                    'address2' => pSQL($address2),
                    'postcode' => pSQL($postcode),
                    'city' => pSQL($city),
                    'id_state' => (int) $id_state,
                    'id_country' => (int) $id_country,
                    'phone_mobile' => pSQL($phone_mobile)
                ));

                $id_address = Db::getInstance()->Insert_ID();
            }
        } catch (Exception $ex) {
        }

        // Returning the last inserted id
        return $id_address;
    }

    /**
     * Customer is added into the database and set as guest by default. This function returns customer id (id_customer).
     * @param type $firstname
     * @param type $lastname
     * @param type $email
     * @param type $is_guest
     * @param type $id_default_group
     * @return type
     */
    private static function addCustomer($firstname, $lastname, $email, $is_guest = 1, $id_default_group = 2)
    {
        $is_available = 0;
        $id_customer = '';
        try {
            // SELECT id_customer from customer EHERE email = 'zombie@zom3.zom';
            $query = "SELECT id_customer from " . _DB_PREFIX_ . "customer WHERE email = '" . pSQL($email) . "'";
            $result = Db::getInstance()->executeS($query);
            if (!empty($result)) {
                $id_customer = $result[0]['id_customer'];
                $is_available = 1;
            }
            // Add new customer if the provided email does not exist
            if ($is_available == 0) {
                Db::getInstance()->insert('customer', array(
                    'firstname' => pSQL($firstname),
                    'lastname' => pSQL($lastname),
                    'email' => pSQL($email),
                    'is_guest' => (int) $is_guest,
                    'id_default_group' => (int) $id_default_group
                ));

                $id_customer = Db::getInstance()->Insert_ID();
            }
        } catch (Exception $ex) {
        }

        // Returning the last inserted id
        return $id_customer;
    }

    /**
     * Returns the carrier ID. New carrier is added if not already exists
     * @param type $name_carrier
     * @return type
     */
    public static function createCarrier($name_carrier)
    {
        $map_ps_carrier = (int) Configuration::get('map_etsy_order_store_carrier');
        $id_carrier_reference = Configuration::get('KB_SHIPPING_METHOD_ETSY_ORDER');
        $carrier_object = Carrier::getCarrierByReference($id_carrier_reference);
        if (isset($carrier_object->id) && $carrier_object->id && $map_ps_carrier) {
            return (int) $carrier_object->id;
        } else {
            $id_carrier = '';
            try {
                $query = "SELECT c.id_carrier FROM " . _DB_PREFIX_ . "carrier c INNER JOIN " . _DB_PREFIX_ . "carrier_lang cl ON(c.id_carrier = cl.id_carrier && cl.id_shop = " . (int) Context::getContext()->shop->id . " && cl.id_lang = " . (int) Context::getContext()->language->id . ") WHERE c.name = '" . pSQL($name_carrier) . "'";

                $result = Db::getInstance()->executeS($query, true, false);
                // If carrier already exists then find id only
                if (!empty($result)) {
                    $id_carrier = $result[0]['id_carrier'];
                } else {
                    // If the carrier is not available then add a new carrier and find out it's id
                    Db::getInstance()->insert('carrier', array(
                        'name' => pSQL($name_carrier),
                        'deleted' => 1,
                        'active' => 1,
                    ));
                    $id_carrier = Db::getInstance()->Insert_ID();

                    // Insert that too in carrier_lang table
                    Db::getInstance()->insert('carrier_lang', array(
                        'id_carrier' => (int) $id_carrier,
                        'id_shop' => (int) Context::getContext()->shop->id,
                        'id_lang' => (int) Context::getContext()->language->id,
                        'delay' => pSQL($name_carrier)
                    ));
                }
            } catch (Exception $ex) {
                //            $response_data = $ex->getMessage();
            }


            return $id_carrier;
        }
    }

    /**
     * Returns an array containing the currency id for given currency iso code
     * @param type $currency_iso_code
     * @return string
     */
    public static function getCurrencyId($currency_iso_code)
    {
        $result_array = array();
        // SELECT id_currency from currency WHERE iso_code = 'INR'
        $query = "SELECT id_currency from " . _DB_PREFIX_ . "currency WHERE iso_code = '" . pSQL($currency_iso_code) . "'";
        $result = Db::getInstance()->getRow($query);
        $result_array['success'] = $result['id_currency'];
        $result_array['error'] = "";
        return $result_array;
    }

    /**
     * Returns the array containing all the messages (success or error messages)
     * @param type $array_order_details
     * @return int
     */
    public static function validateDetails($array_order_details)
    {
        $return_array = array();
        $validation_flag = 1; // By default set as valid
        // We can add validation code here if needed

        if (!isset($array_order_details['customer']) || !isset($array_order_details['order'])) {
            $return_array['validation_errors'][] = "Customer or Order details are not present. Please make sure that the array you passed does have 'customer' and 'order' indexes.";
            $validation_flag = 0;
        }

        // Required indexes arrays
        $array_customer_required_indexes = array(
            'email' => "",
            'firstname' => "",
            'lastname' => "",
            'address1' => "",
            'postcode' => "",
            'city' => ""
        );
        $array_order_required_indexes = array(
            'id_language' => "",
            'currency_iso_code' => "",
            'name_carrier' => "",
            'payment_method' => "",
            'id_warehouse' => "",
            'cart_recyclable' => "",
            'cart_gift' => "",
            'id_shop' => "",
            'id_shop_group' => "",
            'current_state' => "",
            'order_reference' => "",
            'total_paid_real' => "",
            'total_products' => "",
            'total_products_wt' => "",
            'total_discounts_tax_excl' => "",
            'total_discounts_tax_incl' => "",
            'total_shipping_tax_excl' => "",
            'total_shipping_tax_incl' => "",
            'total_wrapping_tax_excl' => "",
            'total_wrapping_tax_incl' => "",
            'total_paid_tax_excl' => "",
            'total_paid_tax_incl' => "",
            'invoice_date' => "",
            'delivery_date' => ""
        );

        // Checking if values for necessary fields are available
        if (!isset($array_order_details['customer']['email']) || !isset($array_order_details['customer']['firstname']) || !isset($array_order_details['customer']['lastname']) || !isset($array_order_details['customer']['address1']) || !isset($array_order_details['customer']['postcode']) || !isset($array_order_details['customer']['city'])) {
            $return_array['validation_errors'][] = "Value(s) from the 'customer' index are missing or not set. These array indexes must be present and must contain some value. Compare required and given arrays to find out the mistake(s).";
            $return_array['validation_errors']['customer_required_fields'] = $array_customer_required_indexes;
            $return_array['validation_errors']['cuatomer_given_fields'] = $array_order_details['customer'];
            $validation_flag = 0;
        }
        if (!isset($array_order_details['order']['id_language']) || !isset($array_order_details['order']['currency_iso_code']) || !isset($array_order_details['order']['name_carrier']) || !isset($array_order_details['order']['payment_method']) || !isset($array_order_details['order']['id_warehouse']) || !isset($array_order_details['order']['cart_recyclable']) || !isset($array_order_details['order']['cart_gift']) || !isset($array_order_details['order']['id_shop']) || !isset($array_order_details['order']['id_shop_group']) || !isset($array_order_details['order']['current_state']) || !isset($array_order_details['order']['order_reference']) || !isset($array_order_details['order']['total_paid_real']) || !isset($array_order_details['order']['total_products']) || !isset($array_order_details['order']['total_products_wt']) || !isset($array_order_details['order']['total_discounts_tax_excl']) || !isset($array_order_details['order']['total_discounts_tax_incl']) || !isset($array_order_details['order']['total_shipping_tax_excl']) || !isset($array_order_details['order']['total_shipping_tax_incl']) || !isset($array_order_details['order']['total_wrapping_tax_excl']) || !isset($array_order_details['order']['total_wrapping_tax_incl']) || !isset($array_order_details['order']['total_paid_tax_excl']) || !isset($array_order_details['order']['total_paid_tax_incl']) || !isset($array_order_details['order']['invoice_date']) || !isset($array_order_details['order']['delivery_date'])) {
            $return_array['validation_errors'][] = "Value(s) from the 'order' index are missing.  These array indexes must be present and must contain value. Compare required and given arrays to find out the mistake(s).";
            $return_array['validation_errors']['order_required_fields'] = $array_order_required_indexes;
            $return_array['validation_errors']['order_given_fields'] = $array_order_details['order'];
            $validation_flag = 0;
        }

        // Email format
        if (!filter_var($array_order_details['customer']['email'], FILTER_VALIDATE_EMAIL)) {
            $return_array['validation_errors'][] = "Customer email id is not valid. Please check that. The format should be like example@example.com";
            $validation_flag = 0;
        }

        $return_array['is_okay'] = $validation_flag;

        return $return_array;
    }

    /**
     * Returns an array of details of a product using id_product
     * @param type $id_product
     * @return string
     */
    public static function getTranslations($update)
    {
        $lang_data = new Language(Configuration::get('etsy_default_lang'));
        $language = Tools::strtolower($lang_data->iso_code);
        $sync_arr = explode(',', Configuration::get('etsy_sync_lang'));
        $sync_lang_cond = '';
        if (count($sync_arr) > 0) {
            $sync_lang_cond .= ' AND (';
        }
        $flag = 0;
        foreach ($sync_arr as $key => $sync) {
            $sync_lang_cond .= 'lang_code = "' . pSQL(Language::getIsoById((int) $sync)) . '"';
            if (count($sync_arr) > 1) {
                if ($flag == 0) {
                    end($sync_arr);
                    $end_key = key($sync_arr);
                    if ($key != $end_key) {
                        $sync_lang_cond .= ' or ';
                    }
                }
            }
            $flag++;
        }
        $sync_lang_cond .= ')';
        if ($update) {
            $query_get_translations = 'SELECT * FROM ' . _DB_PREFIX_ . 'etsy_translation WHERE status = "Update" AND lang_code != "' . pSQL($language) . '"' . $sync_lang_cond;
        } else {
            $query_get_translations = 'SELECT * FROM ' . _DB_PREFIX_ . 'etsy_translation WHERE status = "Pending" AND lang_code != "' . pSQL($language) . '"' . $sync_lang_cond;
        }
        return Db::getInstance()->executeS($query_get_translations);
    }
}
