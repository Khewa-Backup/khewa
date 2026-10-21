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

/**
 * Custom Cart for Point of Sale
 * - assignCustomer: assign customer to object cart and object cookie at back end.
 */
class PosCart extends Cart
{
    /**
     * Cache payments.
     * @var array
     */
    protected static $cache_payments = array();

    /**
     * @param bool $null_values
     * @param bool $auto_date
     * @see ObjectModel::save()
     *
     */
    public function save($null_values = false, $auto_date = true)
    {
        if (!empty($this->id_address_delivery) && !empty($this->id_carrier)) {
            $delivery_option = array();
            $delivery_option[$this->id_address_delivery] = $this->id_carrier . ',';
            $this->delivery_option = json_encode($delivery_option);
            // Copied from Cart::setDeliveryOption()
        }
        $result = parent::save($null_values, $auto_date);
        PosContext::resetContext();

        return $result;
    }

    /**
     * @param int $id_lang
     * @param bool $refresh
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               name => string,
     *               devliery => string,
     *               id_carrier => int,
     *
     *     )
     * )
     */
    public function getCarriers($id_lang = null, $refresh = false)
    {
        static $cache = array();
        $cache_key = (int) $this->id . '_' . (int) $this->id_address_delivery;
        if (isset($cache[$cache_key]) && $cache[$cache_key] !== false && !$refresh) {
            return $cache[$cache_key];
        }

        if (empty($id_lang)) {
            $context = Context::getContext();
            $id_lang = $context->language->id;
        }
        $package_list = $this->getPackageList($refresh);
        $id_carriers = array();
        foreach ($package_list as $packages) {
            foreach ($packages as $package) {
                // No carriers a vailable
                if (count($packages) == 1 && count($package['carrier_list']) == 1 && current($package['carrier_list']) == 0) {
                    $cache[$cache_key] = array();

                    return $cache[$cache_key];
                }
                // Get all common carriers for each packages to the same address
                if (empty($id_carriers)) {
                    $id_carriers = $package['carrier_list'];
                } else {
                    $id_carriers = array_intersect($id_carriers, $package['carrier_list']);
                }
            }
        }
        $carriers = array();
        if (!empty($id_carriers)) {
            $carrier_collection = PosCarrier::getCarrierCollection($id_carriers);
            $id_pos_carrier = Configuration::get('POS_ID_CARRIER');
            $pos_carrier = array();
            foreach ($carrier_collection as $carrier) {
                if ($carrier->id == $id_pos_carrier) {
                    $pos_carrier = array(
                        'name' => $carrier->name,
                        'delivery' => $carrier->delay[$this->id_lang],
                        'id_carrier' => (int) $carrier->id,
                    );
                } else {
                    $carriers[] = array(
                        'name' => $carrier->name,
                        'delivery' => $carrier->delay[$this->id_lang],
                        'id_carrier' => (int) $carrier->id,
                    );
                }
            }
            if (!empty($pos_carrier)) {
                $carriers[] = $pos_carrier;
                $carriers = array_reverse($carriers);
            }
        }

        $cache[$cache_key] = $carriers;

        return $cache[$cache_key];
    }

    /**
     * @param int $id_lang
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               'id_cart_payment' => int,
     *               'id_payment' => int,
     *               'amount' => float,
     *               'given_money' => float,
     *               'change' => float,
     *               'payment_name' => string
     *               )
     *               ...
     *               )
     */
    public function getPayments($id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = Context::getContext()->language->id;
        }
        if (empty(self::$cache_payments[$this->id][$id_lang])) {
            $query = new DbQuery();
            $query->select('cp.`id_pos_cart_payment` AS `id_cart_payment`');
            $query->select('cp.`id_pos_payment` AS `id_payment`');
            $query->select('cp.`amount`');
            $query->select('cp.`given_money`');
            $query->select('cp.`change`');
            $query->select('pl.`payment_name`');
            $query->from('pos_cart_payment', 'cp');
            $query->leftJoin(
                'pos_payment_lang',
                'pl',
                'pl.`id_pos_payment` = cp.`id_pos_payment` AND pl.`id_lang` = ' . (int) $id_lang
            );
            $query->where('cp.`id_cart` = ' . (int) $this->id);
            self::$cache_payments[$this->id][$id_lang] = Db::getInstance()->executeS($query, true, false);
        }

        return self::$cache_payments[$this->id][$id_lang];
    }

    /**
     * Add required shop
     * Update product quantity.
     * @param int $quantity
     * @param int $id_product
     * @param Shop $shop
     * @param int $id_product_attribute
     * @param string $operator
     * @param int $id_customization
     * @param int $id_address_delivery
     * @param bool $auto_add_cart_rule
     * @return bool
     */
    public function updateQtyPos(
        $quantity,
        $id_product,
        Shop $shop,
        $id_product_attribute = null,
        $operator = 'up',
        $id_customization = false,
        $id_address_delivery = 0,
        $auto_add_cart_rule = true
    ) {
        if (!Validate::isLoadedObject($shop)) {
            return false;
        }

        if (Context::getContext()->customer->id) {
            if ($id_address_delivery == 0 && (int) $this->id_address_delivery) {
                // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = (int) $this->id_address_delivery;
            } elseif ($id_address_delivery == 0) {
                // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int) Address::getFirstCustomerAddressId((int) Context::getContext()->customer->id);
            } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) {
                // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
            }
        }
        $product = new PosProduct($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);

        if ($id_product_attribute) {
            $combination = new Combination((int) $id_product_attribute);
            if ($combination->id_product != $id_product) {
                return false;
            }
        }

        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        if (isset(self::$_nbProducts[(int) $this->id])) {
            unset(self::$_nbProducts[(int) $this->id]);
        }

        if (isset(self::$_totalWeight[(int) $this->id])) {
            unset(self::$_totalWeight[(int) $this->id]);
        }

        if ((int) $quantity <= 0) {
            return $this->deleteProduct($id_product, $id_product_attribute, (int) $id_customization) && PosSpecificPrice::deleteByIdCart((int) $this->id, $id_product, $id_product_attribute);
        } else {
            $minimal_quantity = $product->getMinimalQuantity($id_product_attribute);
            /* Check if the product is already in the cart */
            $result = $this->containsProduct($id_product, $id_product_attribute, (int) $id_customization, (int) $id_address_delivery);

            /* Update quantity if product already exist */
            if ($result) {
                if ($operator == 'up') {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity    FROM ' . _DB_PREFIX_ . 'product p
                            ' . Product::sqlStock('p', (int) $id_product_attribute, true, $shop) . '
                            WHERE p.id_product = ' . (int) $id_product;

                    $result2 = Db::getInstance()->getRow($sql);
                    $product_qty = (int) $result2['quantity'];
                    // Quantity for product pack
                    if (Pack::isPack((int) $id_product)) {
                        $product_qty = Pack::getQuantity((int) $id_product, (int) $id_product_attribute);
                    }
                    $new_qty = (int) $result['quantity'] + (int) $quantity;
                    $qty = '+ ' . (int) $quantity;

                    if (!PosProduct::isEnabledOrderOutOfStock((int) $result2['out_of_stock'])) {
                        if ($new_qty > $product_qty) {
                            return PosConstants::NOT_ENOUGH_PRODUCT;
                        }
                    }
                } elseif ($operator == 'down') {
                    $qty = '- ' . (int) $quantity;
                    $new_qty = (int) $result['quantity'] - (int) $quantity;
                    if ($new_qty < $minimal_quantity && $minimal_quantity > 1) {
                        return PosConstants::MIN_QUANTITY;
                    }
                } else {
                    return false;
                }

                /* Delete product from cart */
                if ($new_qty <= 0) {
                    return $this->deleteProduct((int) $id_product, (int) $id_product_attribute, (int) $id_customization) && PosSpecificPrice::deleteByIdCart((int) $this->id, (int) $id_product, (int) $id_product_attribute);
                } elseif ($new_qty < $minimal_quantity) {
                    return PosConstants::MIN_QUANTITY;
                } else {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                        SET `quantity` = `quantity` ' . pSQL($qty) . '
                        WHERE `id_product` = ' . (int) $id_product .
                        (!empty($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') . '
                        AND `id_cart` = ' . (int) $this->id . (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ? ' AND `id_address_delivery` = ' . (int) $id_address_delivery : '') . ' LIMIT 1';
                    Db::getInstance()->execute($sql);
                }
            } elseif ($operator == 'up') {
                /* Add product to the cart */
                $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                        FROM ' . _DB_PREFIX_ . 'product p
                        ' . Product::sqlStock('p', (int) $id_product_attribute, true, $shop) . '
                        WHERE p.id_product = ' . (int) $id_product;

                $result2 = Db::getInstance()->getRow($sql);

                // Quantity for product pack
                if (Pack::isPack((int) $id_product)) {
                    $result2['quantity'] = Pack::getQuantity((int) $id_product, (int) $id_product_attribute);
                }

                if (!PosProduct::isEnabledOrderOutOfStock((int) $result2['out_of_stock'])) {
                    if ((int) $quantity > $result2['quantity']) {
                        return PosConstants::NOT_ENOUGH_PRODUCT;
                    }
                }

                if ((int) $quantity < $minimal_quantity) {
                    return PosConstants::MIN_QUANTITY;
                }

                $result_add = Db::getInstance()->insert('cart_product', array(
                    'id_product' => (int) $id_product,
                    'id_product_attribute' => (int) $id_product_attribute,
                    'id_cart' => (int) $this->id,
                    'id_address_delivery' => (int) $id_address_delivery,
                    'id_shop' => (int) $shop->id,
                    'quantity' => (int) $quantity,
                    'date_add' => date('Y-m-d H:i:s'),
                ));

                if (!$result_add) {
                    return false;
                }
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        if ($auto_add_cart_rule) {
            CartRule::autoAddToCart($context);
        }

        if ($product->customizable) {
            return $this->_updateCustomizationQuantity(
                (int) $quantity,
                (int) $id_customization,
                (int) $id_product,
                (int) $id_product_attribute,
                (int) $id_address_delivery,
                $operator
            );
        } else {
            return true;
        }
    }

    /**
     * Customization management.
     * From PS version 9.0, this function has been removed from class Cart
     * @param int $quantity Quantity value to add or subtract
     * @param int $id_customization Customization ID
     * @param int $id_product Product ID
     * @param int $id_product_attribute ProductAttribute ID
     * @param int $id_address_delivery Delivery Address ID
     * @param string $operator Indicate if quantity must be increased (up) or decreased (down)
     *
     * @return bool
     */
    protected function _updateCustomizationQuantity($quantity, $id_customization, $id_product, $id_product_attribute, $id_address_delivery, $operator = 'up')
    {
        // Link customization to product combination when it is first added to cart
        if (empty($id_customization)) {
            $customization = $this->getProductCustomization($id_product, null, true);
            foreach ($customization as $field) {
                if ($field['quantity'] == 0) {
                    Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'customization`
                    SET `quantity` = ' . (int) $quantity . ',
                        `id_product_attribute` = ' . (int) $id_product_attribute . ',
                        `id_address_delivery` = ' . (int) $id_address_delivery . ',
                        `in_cart` = 1
                    WHERE `id_customization` = ' . (int) $field['id_customization']);
                }
            }
        }

        /* Deletion */
        if (!empty($id_customization) && (int) $quantity < 1) {
            return $this->_deleteCustomization((int) $id_customization, (int) $id_product, (int) $id_product_attribute);
        }

        /* Quantity update */
        if (!empty($id_customization)) {
            $result = Db::getInstance()->getRow('SELECT `quantity` FROM `' . _DB_PREFIX_ . 'customization` WHERE `id_customization` = ' . (int) $id_customization);
            if ($result && Db::getInstance()->numRows()) {
                if ($operator == 'down' && (int) $result['quantity'] - (int) $quantity < 1) {
                    return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'customization` WHERE `id_customization` = ' . (int) $id_customization);
                }

                return Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'customization`
                    SET
                        `quantity` = `quantity` ' . ($operator == 'up' ? '+ ' : '- ') . (int) $quantity . ',
                        `id_product_attribute` = ' . (int) $id_product_attribute . ',
                        `id_address_delivery` = ' . (int) $id_address_delivery . ',
                        `in_cart` = 1
                    WHERE `id_customization` = ' . (int) $id_customization);
            } else {
                Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'customization`
                    SET `id_address_delivery` = ' . (int) $id_address_delivery . ',
                    `id_product_attribute` = ' . (int) $id_product_attribute . ',
                    `in_cart` = 1
                    WHERE `id_customization` = ' . (int) $id_customization);
            }
        }
        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();

        return true;
    }

    /**
     * Get minimal quantity to be added to basked, based on current context.
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @return int
     */
    public function getMinimalQuantityToBeAdded($id_product, $id_product_attribute)
    {
        $current_quantity = $this->containsProduct($id_product, $id_product_attribute);
        $product = new PosProduct($id_product);
        $minimal_quantity = (int) $product->getMinimalQuantity($id_product_attribute);

        return (int) $minimal_quantity;
    }

    /**
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_customization
     * @param int $id_address_delivery
     * @return bool
     */
    public function deletePosProduct(
        $id_product,
        $id_product_attribute = null,
        $id_customization = null,
        $id_address_delivery = 0
    ) {
        $success = array();
        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }

        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }

        if ((int) $id_customization) {
            return $this->deleteCustomization(
                (int) $id_customization,
                (int) $id_product,
                (int) $id_product_attribute,
                (int) $id_address_delivery
            );
        }

        /* Get customization quantity */
        $customization_quantity = PosCustomization::getTotalQuantity(
            $this->id,
            (int) $id_product,
            (int) $id_product_attribute
        );
        if ($customization_quantity === false) {
            return false;
        }
        /* If the product still possesses customization it does not have to be deleted */
        if (Db::getInstance()->NumRows() && (int) $customization_quantity['quantity']) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                SET `quantity` = ' . (int) $customization_quantity['quantity'] . '
                WHERE `id_cart` = ' . (int) $this->id . '
                AND `id_product` = ' . (int) $id_product .
                    ($id_product_attribute != null ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '')
            );
        }

        /* Product deletion */
        $success[] = Db::getInstance()->execute('
        DELETE FROM `' . _DB_PREFIX_ . 'cart_product`
        WHERE `id_product` = ' . (int) $id_product . '
        ' . (!is_null($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') . '
        AND `id_cart` = ' . (int) $this->id . '
        ' . ((int) $id_address_delivery ? 'AND `id_address_delivery` = ' . (int) $id_address_delivery : ''));

        if (array_sum($success) >= count($success)) {
            $success[] = $this->update();
            // refresh cache of self::_products
            $this->_products = $this->getProducts(true);
            CartRule::autoRemoveFromCart();
            CartRule::autoAddToCart();
        }

        return array_sum($success) >= count($success);
    }

    /**
     * @param int $id_customization
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_address_delivery
     * @return bool
     */
    protected function deleteCustomization(
        $id_customization,
        $id_product,
        $id_product_attribute,
        $id_address_delivery = 0
    ) {
        $success = array();
        $customization = PosCustomization::getCustomizationById($id_customization);
        if ($customization) {
            $customization_datas = PosCustomizationData::getCustomizationDataById($id_customization);
            // Delete customization picture if necessary
            foreach ($customization_datas as $customization_data) {
                if (isset($customization_data['type']) && $customization_data['type'] == 0) {
                    $success[] = (@unlink(_PS_UPLOAD_DIR_ . $customization_data['value']) && @unlink(_PS_UPLOAD_DIR_ . $customization_data['value'] . '_small'));
                }
            }
            $success[] = PosCustomizationData::delete($id_customization);

            if (array_sum($success) >= count($success)) {
                $current_quantity = $this->containsProduct($id_product, $id_product_attribute);
                if ((int) $current_quantity['quantity'] === (int) $customization['quantity']) {
                    $success[] = Db::getInstance()->execute(
                        'DELETE FROM `' . _DB_PREFIX_ . 'cart_product`
                        WHERE `id_product` = ' . (int) $id_product .
                            (!is_null($id_product_attribute) ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') . '
                        AND `id_cart` = ' . (int) $this->id . '
                        ' . ((int) $id_address_delivery ? 'AND `id_address_delivery` = ' . (int) $id_address_delivery : '')
                    );
                } elseif ((int) $current_quantity['quantity'] > (int) $customization['quantity']) {
                    $success[] = Db::getInstance()->execute(
                        'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                        SET `quantity` = `quantity` - ' . (int) $customization['quantity'] . '
                        WHERE `id_cart` = ' . (int) $this->id . '
                        AND `id_product` = ' . (int) $id_product .
                            ((int) $id_product_attribute ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') .
                            ((int) $id_address_delivery ? ' AND `id_address_delivery` = ' . (int) $id_address_delivery : '')
                    );
                }
                $success[] = PosCustomization::delele($id_customization);
            }
        }

        return array_sum($success) >= count($success);
    }

    /**
     * Update new combination, quantity of product in cart.
     *
     * @param int $id_product
     * @param int $new_id_product_attribute
     * @param int $old_id_product_attribute
     * @param int $id_shop
     * @param int $quantity
     * @return bool
     */
    public function updateCartProduct(
        $id_product,
        $new_id_product_attribute,
        $old_id_product_attribute,
        $id_shop,
        $quantity
    ) {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                SET
                    `id_product_attribute` = ' . (int) $new_id_product_attribute . ',
                    `quantity` = ' . (int) $quantity . '
                WHERE  `id_cart` = ' . (int) $this->id . '
                    AND `id_product` = ' . (int) $id_product . '
                    AND `id_product_attribute` = ' . (int) $old_id_product_attribute . '
                    AND `id_shop` = ' . (int) $id_shop;

        $this->_products = null;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Check cart rule has already apllied for cart.
     * @param int $id_cart_rule
     * @return bool
     */
    public function doesCartRuleExist($id_cart_rule)
    {
        return (bool) Db::getInstance()->getValue('SELECT `id_cart_rule` FROM `' . _DB_PREFIX_ . 'cart_cart_rule` WHERE `id_cart_rule` = ' . (int) $id_cart_rule . ' AND `id_cart` = ' . (int) $this->id);
    }

    /**
     * Get current quantity in cart of a product (or combination).
     *
     * @param int $id_product
     * @param int $id_product_attribute
     *
     * @return int
     */
    public function getQuantity($id_product, $id_product_attribute = null)
    {
        if (empty($id_product_attribute)) {
            $product = $this->getProducts(false, $id_product);
        } else {
            $products = $this->getProducts(false);
            foreach ($products as $item) {
                if ($item['id_product'] == (int) $id_product && $item['id_product_attribute'] == (int) $id_product_attribute) {
                    $product = array($item);
                }
            }
        }

        return empty($product) ? 0 : (int) $product[0]['cart_quantity'];
    }

    /**
     * @param int $id_customer
     * @return bool
     */
    public function assignCustomer($id_customer)
    {
        $success = array();
        $customer = new PosCustomer((int) $id_customer);

        if (Validate::isLoadedObject($customer)) {
            $this->id_customer = $id_customer;

            if ((int) PosCustomer::getAddressesTotalById($id_customer) === 0) {
                PosAddress::createAddress($customer);
            }

            $this->id_address_delivery = Address::getFirstCustomerAddressId((int) $customer->id);
            $this->id_address_invoice = Address::getFirstCustomerAddressId((int) $customer->id);

            $this->id_customer = (int) $customer->id;
            $this->secure_key = $customer->secure_key;
            $success[] = $this->save();
            $success[] = $this->autosetProductAddress();
        }

        return array_sum($success) >= count($success);
    }

    /**
     * Override remove considion check id_shop "AND `id_shop` = '.(int)$this->id_shop"
     * Set an address to all products on the cart without address delivery.
     * @return bool
     */
    public function autosetProductAddress()
    {
        $success = array();
        $id_address_delivery = 0;
        // Get the main address of the customer
        if ((int) $this->id_address_delivery > 0) {
            $id_address_delivery = (int) $this->id_address_delivery;
        } else {
            $id_address_delivery = (int) Address::getFirstCustomerAddressId(Context::getContext()->customer->id);
        }

        if (!$id_address_delivery) {
            return false;
        }
        // Update
        $success[] = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'cart_product`
            SET `id_address_delivery` = ' . (int) $id_address_delivery . '
            WHERE `id_cart` = ' . (int) $this->id
        );

        $success[] = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'customization`
            SET `id_address_delivery` = ' . (int) $id_address_delivery . '
            WHERE `id_cart` = ' . (int) $this->id
        );

        return array_sum($success) >= count($success);
    }

    /**
     * wrong $this->context->cart->id_address_invoice = $id_address_new; line 208
     * Update the address id of the cart     *.
     * @param int $id_address Current address id to change
     * @param int $id_address_new New address id
     */
    public function updateAddressId($id_address, $id_address_new)
    {
        $to_update = false;
        if (!isset($this->id_address_invoice) || (int) $this->id_address_invoice !== (int) $id_address_new) {
            $to_update = true;
            $this->id_address_invoice = $id_address_new;
        }
        if (!isset($this->id_address_delivery) || (int) $this->id_address_delivery !== (int) $id_address_new) {
            $to_update = true;
            $this->id_address_delivery = (int) $id_address_new;
        }
        if ($to_update) {
            $this->update();
        }

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
        SET `id_address_delivery` = ' . (int) $id_address_new . '
        WHERE  `id_cart` = ' . (int) $this->id . '
        AND `id_address_delivery` = ' . (int) $id_address;
        Db::getInstance()->execute($sql);

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'customization`
                SET `id_address_delivery` = ' . (int) $id_address_new . '
                WHERE  `id_cart` = ' . (int) $this->id . '
                AND `id_address_delivery` = ' . (int) $id_address;
        Db::getInstance()->execute($sql);
    }

    /**
     * @param PosAddress $delivery_address
     * @param PosAddress $invoice_address
     * @return bool
     */
    public function updateAddresses(PosAddress $delivery_address = null, PosAddress $invoice_address = null)
    {
        $success = $need_update = false;
        if (Validate::isLoadedObject($delivery_address) && $delivery_address->id_customer == $this->id_customer) {
            $this->id_address_delivery = $delivery_address->id;
            $need_update = true;
        }
        if (Validate::isLoadedObject($invoice_address) && $invoice_address->id_customer == $this->id_customer) {
            $this->id_address_invoice = $invoice_address->id;
            $need_update = true;
        }
        if ($need_update) {
            $success = $this->save();
            if ($success && Validate::isLoadedObject($delivery_address)) {
                $success &= $this->autosetProductAddress();
            }
        }

        return $success;
    }

    /**
     * Add a new payment record to the current cart.
     * @param int $id_payment
     * @param float $given_money
     * @return bool
     */
    public function addPayment($id_payment, $given_money)
    {
        if (empty($this->id)) {
            return false;
        }
        $currency = new Currency($this->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            return false;
        }

        $payment = new PosPayment($id_payment);
        if (!Validate::isLoadedObject($payment)) {
            return false;
        }
        $amount_due = $this->getAmountDue($currency->decimals * PosTools::getPrecision());
        $change = ((float) $given_money - (float) $amount_due) > 0 ? ((float) $given_money - (float) $amount_due) : 0;

        return $this->resetPayments() && Db::getInstance()->insert('pos_cart_payment', array(
            'id_cart' => (int) $this->id,
            'id_pos_payment' => (int) $id_payment,
            'amount' => min($given_money, $amount_due),
            'given_money' => (float) $given_money,
            'change' => (float) $change,
            'reference' => pSQL($payment->reference),
        ));
    }

    /**
     * Add a new payment record of return to the current cart.
     * @param int $id_payment
     * @param float $given_money
     * @param float $amount_due
     * @param float $back_money
     * @return bool
     */
    public function addPaymentReturn($id_payment, $amount_due, $back_money, $given_money)
    {
        if (empty($this->id)) {
            return false;
        }
        $currency = new Currency($this->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            return false;
        }

        $payment = new PosPayment($id_payment);
        if (!Validate::isLoadedObject($payment)) {
            return false;
        }

        return $this->resetPayments() && Db::getInstance()->insert('pos_cart_payment', array(
            'id_cart' => (int) $this->id,
            'id_pos_payment' => (int) $id_payment,
            'amount' => (float) $amount_due,
            'given_money' => (float) $given_money,
            'change' => (float) $back_money,
            'reference' => pSQL($payment->reference),
        ));
    }

    /**
     * @param int $decimal decimal number
     * @return float
     */
    public function getAmountDue($decimal = 0)
    {
        $order_total = $this->getOrderTotal();
        $paid_amount = $this->getPaidAmount();
        return Tools::ps_round($order_total - $paid_amount, $decimal);
    }

    /**
     * reset cache payment.
     * @return bool
     */
    protected function resetPayments()
    {
        self::$cache_payments = null;

        return true;
    }

    /**
     * @param int $id_cart_payment
     * @return bool
     */
    public function removePayment($id_cart_payment)
    {
        $success = array();
        $success[] = $this->resetPayments();
        $success[] = Db::getInstance()->delete(
            'pos_cart_payment',
            'id_pos_cart_payment = ' . (int) $id_cart_payment . ' AND `id_cart` = ' . (int) $this->id
        );

        return array_sum($success) >= count($success);
    }

    /**
     * Get all paid amount for cart.
     * @return float
     */
    public function getPaidAmount()
    {
        $paid_amounts = $this->getPayments();
        $amount = 0;
        if (!empty($paid_amounts)) {
            foreach ($paid_amounts as $paid_amount) {
                $amount += (float) $paid_amount['amount'];
            }
        }

        return $amount;
    }

    /**
     * This is helpful when completing an order while POS_COLLECTING_PAYMENT is off.
     * @return bool
     */
    public function addDefaultPayment()
    {
        $pos_payment = PosPayment::getDefaultPayment();
        $success = false;
        if (Validate::isLoadedObject($pos_payment)) {
            $amount = $this->getOrderTotal(true, Cart::BOTH) - $this->getPaidAmount();
            $success = $amount <= 0.0 || ($amount > 0.0 && $this->addPayment($pos_payment->id, $amount, $pos_payment->reference, null, $amount));
        }

        return $success;
    }

    /**
     * combine all payments to string.
     * @return string
     */
    public function getPaymentMethods()
    {
        $payments = $this->getPayments();
        $payment_methods = array();
        $temp_payment = array();
        if (!empty($payments)) {
            foreach ($payments as $payment) {
                if (!in_array($payment['id_payment'], $temp_payment)) {
                    $temp_payment[] = $payment['id_payment'];
                    $payment_methods[] = $payment['payment_name'];
                }
            }
        }

        return implode('_', $payment_methods);
    }

    /**
     * Update ID customization for cart product (only PS 1.7.x).
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_address_delivery
     * @param int $id_customization
     * @return bool
     */
    public function updateCustomization($id_product, $id_product_attribute, $id_address_delivery, $id_customization)
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'cart_product`
            SET `id_customization` = ' . (int) $id_customization . '
            WHERE `id_cart` = ' . (int) $this->id . '
            AND `id_product` = ' . (int) $id_product .
                ((int) $id_product_attribute ? ' AND `id_product_attribute` = ' . (int) $id_product_attribute : '') .
                ((int) $id_address_delivery ? ' AND `id_address_delivery` = ' . (int) $id_address_delivery : '')
        );
    }

    /**
     * Override
     * 1/ remove AND cp.`id_customization` = '.(int)$id_customization.'
     * (don't need check id_customization, make sure that, RockPOS working on both PS 1.6.x and 1.7.x.
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_customization
     * @param int $id_address_delivery
     *
     * @return array|bool|null|object Whether the Cart contains the Product
     */
    public function containsProduct(
        $id_product,
        $id_product_attribute = 0,
        $id_customization = 0,
        $id_address_delivery = 0
    ) {
        $sql = new DbQuery();
        $sql->select('cp.`quantity`');
        $sql->from('cart_product', 'cp');
        if ($id_customization) {
            $sql->leftJoin(
                'customization',
                'c',
                'c.`id_product` = cp.`id_product` AND c.`id_product_attribute` = cp.`id_product_attribute`'
            );
        }

        $sql->where('cp.`id_product` = ' . (int) $id_product);
        $sql->where('cp.`id_product_attribute` = ' . (int) $id_product_attribute);
        $sql->where('cp.`id_cart` = ' . (int) $this->id);
        if (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery()) {
            $sql->where('cp.`id_address_delivery` = ' . (int) $id_address_delivery);
        }
        if ($id_customization) {
            $sql->where('c.`id_customization` = ' . (int) $id_customization);
        }

        return Db::getInstance()->getRow($sql);
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               total_product => float,
     *               total_product_discount => string,
     *               total_order_discount => string,
     *               total_items => int,
     *               amount_received => float,
     *               change_returned => float,
     *               )
     */
    public static function getSampleSummary()
    {
        $context = Context::getContext();
        $products = PosProduct::getSampleProducts();
        $total_product = 0;
        $total_product_discount = 0;
        $total_order_discount = 0;
        $total_items = 0;
        $amount_received = 200;
        $change_returned = 0;
        foreach ($products as $product) {
            $total_product += $product['unit_price'] * (int) $product['quantity'];
            $total_product_discount += $product['original_price'] - $product['unit_price'];
            $total_items += (int) $product['quantity'];
            $change_returned = $amount_received - $total_product;
        }
        $summary = array(
            'total_product' => $total_product,
            'total_product_discount' => PosTools::posDisplayPrice($total_product_discount, $context->currency, false),
            'total_order_discount' => PosTools::posDisplayPrice($total_order_discount, $context->currency, false),
            'you_saved' => $total_product_discount + $total_order_discount,
            'total_items' => $total_items,
            'amount_received' => $amount_received,
            'change_returned' => $change_returned,
        );

        return $summary;
    }

    /**
     * @return array
     *               <pre >
     *               array(
     *               int => int
     *               )
     */
    public function getIdProducts()
    {
        $id_products = array();
        $products = $this->getProducts();
        foreach ($products as $product) {
            $id_products[] = $product['id_product'];
        }

        return $id_products;
    }

    /**
     * @param int id_customer
     * @return array
     *               <pre>
     *               array(
     *               firstname => string,
     *               lastname => string,
     *               email => string,
     *               date_add => timestamp,
     *               id_cart => int
     *               ),
     */
    public static function getCartsByIdCustomer($id_customer)
    {
        $sql = new DbQuery();
        $sql->select('c.`firstname`,c.`lastname`,c.`email`,a.`date_add`,a.`id_cart`, SUM(cp.`id_product`) as item');
        $sql->from('customer', 'c');
        $sql->innerjoin('cart', 'a', 'c.`id_customer` = a.`id_customer`');
        $sql->innerjoin('cart_product', 'cp', 'cp.`id_cart` = a.`id_cart`');
        $sql->where('c.`id_customer` = ' . (int) $id_customer);
        $sql->where('a.`id_cart` NOT IN (SELECT o.`id_cart` FROM `' . _DB_PREFIX_ . 'orders` o )');
        $sql->where('a.`id_cart` NOT IN (SELECT pc.`id_cart` FROM `' . _DB_PREFIX_ . 'pos_cart` pc)');
        // temporary fixing due to the system there are a lot of abandoned cart like 50k,...
        $sql->limit(500);
        $sql->groupBy('cp.`id_product`');
        $sql->having('item > 0');

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Override : re-calculate the total_shipping_tax_exc.
     *
     * @param int $id_lang
     * @param bool $refresh
     * @param array $deliver_option
     *
     * @return array
     *               <pre>
     *               array(
     *               ...
     *               total_shipping_tax_exc => int
     *               )
     *),
     */
    public function getSummaryDetails($id_lang = null, $refresh = false, $deliver_option = null)
    {
        $summary_details = parent::getSummaryDetails($id_lang, $refresh, $deliver_option);

        if (!empty($deliver_option) && !empty($summary_details)) {
            $summary_details['total_shipping_tax_exc'] = $this->getTotalShippingCost($deliver_option, false);
        }

        return $summary_details;
    }

    /**
     * Override : get the id_warehouse for each product.
     * @param bool $refresh
     * @param int $id_product
     * @param int $id_country
     * @return array
     *               <pre>
     *               array(
     *               ...
     *               id_warehouse => int
     *               )
     *),
     */
    public function getProducts(
        $refresh = false,
        $id_product = false,
        $id_country = null,
        $fullInfos = true,
        bool $keepOrderPrices = false,
        bool $shouldSplitGiftProductsQuantity = false
    ) {
        $products = parent::getProducts($refresh, $id_product, $id_country);
        if (!empty($products)) {
            foreach ($products as &$product) {
                $product['id_warehouse'] = PosProduct::getProductWarehouseIds(
                    $product['id_product'],
                    $product['id_product_attribute']
                );
            }
        }

        return $products;
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               key => int
     *               )
     *),
     */
    public function getDeliverOptionFormat()
    {
        $delivery_option_list = $this->getDeliveryOptionList();
        if (!empty($delivery_option_list)) {
            $first_key = key(reset($delivery_option_list));

            return array_filter(explode(',', $first_key));
        }
    }

    /**
     * This function returns the total cart amount
     * Possible values for $type:
     * Cart::ONLY_PRODUCTS
     * Cart::ONLY_DISCOUNTS
     * Cart::BOTH
     * Cart::BOTH_WITHOUT_SHIPPING
     * Cart::ONLY_SHIPPING
     * Cart::ONLY_WRAPPING
     * Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING
     * Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
     * @param bool $withTaxes With or without taxes
     * @param int $type Total type
     * @param bool $use_cache Allow using cache of the method CartRule::getContextualValue
     * @return float Order total
     */
    public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true, $keepOrderPrices = false)
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
                // Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
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

            /*if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
                $type = Cart::ONLY_PRODUCTS;
            }*/

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
                        // $product_price = $price * $product['cart_quantity'];
                        $product_price = Tools::ps_round($price * $product['cart_quantity'], PosConstants::POS_PRICE_COMPUTE_PRECISION);
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision);
                        break;

                    case Order::ROUND_ITEM:
                    default:
                        $product_price = /* $with_taxes ? $tax_calculator->addTaxes($price) : */
                            $price;
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price * (int) $product['cart_quantity'], $compute_precision);
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
                foreach ($cart_rules as $cart_rule) {
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
                $order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float) $order_total_products) + (float) $order_shipping_discount;
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
                // Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
                Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
            );
            if (version_compare(_PS_VERSION_, '9.0.2', '>')) {
                $array_type[] = Cart::ONLY_PRODUCTS_WITHOUT_GIFTS;
            }
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

            /*if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
                $type = Cart::ONLY_PRODUCTS;
            }*/

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

                $price = Tools::ps_round($price_with_reduction, 6);

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
                        // $product_price = $price * $product['cart_quantity'];
                        $products_total[$id_tax_rules_group] += Tools::ps_round($price * (int) $product['cart_quantity'], $compute_precision);
                        break;

                    case Order::ROUND_ITEM:
                    default:
                        $product_price = /*$with_taxes ? $tax_calculator->addTaxes($price) : */
                            $price;
                        $products_total[$id_tax_rules_group] += Tools::ps_round($product_price * (int) $product['cart_quantity'], $compute_precision);
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
                foreach ($cart_rules as $cart_rule) {
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

                $order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float) $order_total_products) + (float) $order_shipping_discount;
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
    protected function getPackageShippingCostFromModule(Carrier $carrier, $shipping_cost, $products)
    {
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
    }
}
