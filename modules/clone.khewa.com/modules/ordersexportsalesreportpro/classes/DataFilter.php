<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    IntelliPresta <tehran.alishov@gmail.com>
 *  @copyright 2020 IntelliPresta
 *  @license   Commercial License
 */

class DataFilter
{

    public static function getGroups()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    g.id_group id, 
                    gl.`name`, 
                    g.reduction,
                    IFNULL(gc.members, 0) members,
                    g.show_prices, 
                    DATE(g.`date_add`) `date_add`
                    FROM ';

        $table = '`' . _DB_PREFIX_ . 'group` g
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl
                            ON g.id_group = gl.id_group AND gl.id_lang = ' . Context::getContext()->language->id . '
                        LEFT JOIN (
                            SELECT id_group, COUNT(id_customer) members FROM `' . _DB_PREFIX_ . 'customer_group`
                            GROUP BY id_group
                            ) gc ON g.id_group = gc.id_group
                        WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND g.id_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND g.id_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND g.id_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND g.id_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (gl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "DATE(g.`date_add`) LIKE '%" . $search . "%' OR ";
            $cond .= "g.id_group LIKE '%" . $search . "%' OR ";
            $cond .= "g.`reduction` LIKE '%" . $search . "%' OR ";
            $cond .= "gc.members LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));
//                d($sql);
        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_group) count FROM `'
            . _DB_PREFIX_ . 'group`');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getCustomers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    c.id_customer id, 
                    c.firstname, 
                    c.lastname, 
                    c.email, 
                    gel.`name` gender, 
                    active enabled,
                    gl.`name` `group`,
                    c.newsletter,
                    c.deleted
                    FROM ';

        $table = '`' . _DB_PREFIX_ . 'customer` c 
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl
                            ON c.id_default_group = gl.id_group AND gl.id_lang = ' . Context::getContext()->language->id . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'gender_lang` gel
                            ON c.id_gender = gel.id_gender AND gel.id_lang= ' . Context::getContext()->language->id . '
                        WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_customer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_customer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_customer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_customer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (firstname LIKE '%" . $search . "%' OR ";
            $cond .= "lastname LIKE '%" . $search . "%' OR ";
            $cond .= "id_customer LIKE '%" . $search . "%' OR ";
            $cond .= "gel.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "gl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_customer) count FROM '
            . _DB_PREFIX_ . 'customer');


        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getOrders($module)
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    o.id_order id, 
                    CONCAT("' . Configuration::get('PS_INVOICE_PREFIX', $lang) . '", LPAD(o.invoice_number, 6, "0")) invoice_number,
                    o.reference, 
                    IF((SELECT so.id_order FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.id_customer = o.id_customer AND so.id_order < o.id_order LIMIT 1) > 0, "' . $module->l('No', 'ExportSales') . '", "' . $module->l('Yes', 'ExportSales') . '") new_client,
                    cl.`name` delivery_country,
                    CONCAT(LEFT(c.firstname, 1), ". ", c.lastname) customer,
                    o.total_paid,
                    o.payment,
                    o.`date_add`
                FROM `'
                . _DB_PREFIX_ . 'orders` o 
                LEFT JOIN `' . _DB_PREFIX_ . 'address` ad ON o.id_address_delivery = ad.id_address
                LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON ad.id_country = cl.id_country AND cl.id_lang = ' . $lang . '
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON o.id_customer = c.id_customer
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND o.id_order IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND o.id_order NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND o.id_order NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND o.id_order IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (o.id_order LIKE '%" . $search . "%' OR ";
            $cond .= "CONCAT('" . Configuration::get('PS_INVOICE_PREFIX', $lang) . "', LPAD(o.invoice_number, 6, '0')) LIKE '%" . $search . "%' OR ";
            $cond .= "o.reference LIKE '%" . $search . "%' OR ";
            $cond .= "cl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "CONCAT(LEFT(c.firstname, 1), '. ', c.lastname) LIKE '%" . $search . "%' OR ";
            $cond .= "o.total_paid LIKE '%" . $search . "%' OR ";
            $cond .= "o.payment LIKE '%" . $search . "%' OR ";
            $cond .= "o.`date_add` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_order) count FROM `'
            . _DB_PREFIX_ . 'orders`');


        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getOrderStates()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    a.id_order_state id, 
                    `name`, 
                    color,
                    send_email,
                    invoice,
                    delivery,
                    template,
                    deleted
                FROM `'
            . _DB_PREFIX_ . 'order_state` a LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` b 
                        ON a.id_order_state = b.id_order_state AND b.id_lang = '
            . Context::getContext()->language->id . ' WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_order_state IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_order_state NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_order_state NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_order_state IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`name` LIKE '%" . $search . "%' OR ";
            $cond .= "template LIKE '%" . $search . "%' OR ";
            $cond .= "a.id_order_state LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_order_state) count FROM `'
            . _DB_PREFIX_ . 'order_state`');

        foreach ($data as &$val) {
            $val['font_color'] = $val['color'] && Tools::getBrightness($val['color']) < 128 ? 'white' : '#383838';
            $val['icon'] = ImageManager::thumbnail(_PS_ORDER_STATE_IMG_DIR_ . $val['id'] . '.gif', 'order_state_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.gif', 45, 'gif');
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getCarriers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    *  
                FROM (SELECT 
                    c.id_carrier id,
                    c.id_reference reference,
                    delay,
                    active enabled,
                    is_free,
                    IF(`name` = "0", (SELECT value FROM '
            . _DB_PREFIX_ . 'configuration WHERE `name` = "PS_SHOP_NAME" LIMIT 1), `name`) `name` FROM `'
            . _DB_PREFIX_ . 'carrier` c
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON  c.id_carrier = cl.id_carrier
                    AND cl.id_lang = ' . Context::getContext()->language->id . '
                    AND cl.id_shop = ' . Context::getContext()->shop->id . '
                WHERE c.deleted = 0 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_reference IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_reference NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_reference NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_reference IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $cond .= ') tmp WHERE 1';

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (id LIKE '%" . $search . "%' OR ";
            $cond .= "reference LIKE '%" . $search . "%' OR ";
            $cond .= "delay LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_SHIP_IMG_DIR_ . $val['id'] . '.jpg', 'carrier_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_carrier) count FROM '
            . _DB_PREFIX_ . 'carrier WHERE deleted = 0');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getShops()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_shop id, s.`name` `name`, sg.`name` sg_name
                FROM ' . _DB_PREFIX_ . 'shop s
                LEFT JOIN ' . _DB_PREFIX_ . 'shop_group sg ON s.id_shop_group = sg.id_shop_group 
                WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_shop IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_shop NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_shop NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_shop IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        $search = Tools::getValue('search')['value'];

        if ($search || $search === '0') {
            $cond .= " AND (s.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "sg.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "s.id_shop LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_shop) count FROM `'
            . _DB_PREFIX_ . 'shop`');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getManufacturers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    m.id_manufacturer id, 
                    `name`, 
                    active enabled, 
                    mp.prod_count, 
                    ma.address_count
                FROM 
                ' . _DB_PREFIX_ . 'manufacturer m ';

        $join = 'LEFT JOIN (
                SELECT id_manufacturer, COUNT(id_product) prod_count
                FROM ' . _DB_PREFIX_ . 'product
                GROUP BY id_manufacturer) mp ON m.id_manufacturer = mp.id_manufacturer
                LEFT JOIN (SELECT id_manufacturer, COUNT(id_address) address_count
                FROM ' . _DB_PREFIX_ . 'address
                GROUP BY id_manufacturer) ma ON m.id_manufacturer = ma.id_manufacturer WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.id_manufacturer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND m.id_manufacturer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.id_manufacturer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND m.id_manufacturer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (m.id_manufacturer LIKE '%" . $search . "%' OR ";
            $cond .= "prod_count LIKE '%" . $search . "%' OR ";
            $cond .= "address_count LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $join . $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_MANU_IMG_DIR_ . $val['id'] . '.jpg', 'manufacturer_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_manufacturer) `count` FROM '
            . _DB_PREFIX_ . 'manufacturer');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getSuppliers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    s.id_supplier id, 
                    `name`, 
                    active enabled, 
                    prod_count
                FROM ' . _DB_PREFIX_ . 'supplier s ';
        $join = ' LEFT JOIN (
                SELECT id_supplier, COUNT(id_product) prod_count
                FROM ' . _DB_PREFIX_ . 'product
                GROUP BY id_supplier) sp ON s.id_supplier = sp.id_supplier
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_supplier IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_supplier NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_supplier NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_supplier IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (s.id_supplier LIKE '%" . $search . "%' OR ";
            $cond .= "prod_count LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $join . $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_SUPP_IMG_DIR_ . $val['id'] . '.jpg', 'supplier_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_supplier) count FROM '
            . _DB_PREFIX_ . 'supplier');


        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getCartRules()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    cr.id_cart_rule id, 
                    `name`, 
                    priority, 
                    `code`, 
                    quantity, 
                    date_to, 
                    active
                FROM ' . _DB_PREFIX_ . 'cart_rule cr
                LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule_lang crl 
                    ON cr.id_cart_rule = crl.id_cart_rule AND crl.id_lang = '.$lang.'
                WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND cr.id_cart_rule IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND cr.id_cart_rule NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND cr.id_cart_rule NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND cr.id_cart_rule IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        $search = Tools::getValue('search')['value'];

        if ($search || $search === '0') {
            $cond .= " AND (cr.`code` LIKE '%" . $search . "%' OR ";
            $cond .= "crl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "cr.`quantity` LIKE '%" . $search . "%' OR ";
            $cond .= "cr.`date_to` LIKE '%" . $search . "%' OR ";
            $cond .= "cr.id_cart_rule LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_cart_rule) count FROM `'
            . _DB_PREFIX_ . 'cart_rule`');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }
    
    public static function getCountries()
    {
        $id_lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    c.id_country id, 
                    cl.`name`, 
                    c.iso_code,
                    c.call_prefix,
                    z.`name` zone,
                    c.active enabled
                FROM ' . _DB_PREFIX_ . 'country c
                LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $id_lang . ')
		LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`id_zone` = c.`id_zone`)
                WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_country IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_country NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_country NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_country IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (cl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "c.iso_code LIKE '%" . $search . "%' OR ";
            $cond .= "c.call_prefix LIKE '%" . $search . "%' OR ";
            $cond .= "z.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "c.id_country LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_country) count FROM `'
            . _DB_PREFIX_ . 'country`');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }
    
    public static function getCurrencies()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    id_currency id, 
                    `name`, 
                    iso_code,
                    conversion_rate,
                    active enabled,
                    deleted
                FROM ' . _DB_PREFIX_ . 'currency
                WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_currency IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_currency NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_currency NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_currency IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`name` LIKE '%" . $search . "%' OR ";
            $cond .= "iso_code LIKE '%" . $search . "%' OR ";
            $cond .= "conversion_rate LIKE '%" . $search . "%' OR ";
            $cond .= "id_currency LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $curs = json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../assets/currencies.json'));
        foreach ($data as &$val) {
            $val['symbol'] = isset($curs->{$val['iso_code']}) ? $curs->{$val['iso_code']} : $val['iso_code'];
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_currency) count FROM `'
            . _DB_PREFIX_ . 'currency`');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    private static function sortPaymentMethods($a, $b)
    {
        $order = Tools::getValue('order');
        $columns = Tools::getValue('columns');

        if (Tools::strtolower($a[$columns[$order[0]['column']]['data']]) <
            Tools::strtolower($b[$columns[$order[0]['column']]['data']])) {
            if ($order[0]['dir'] === 'asc') {
                return -1;
            } else {
                return 1;
            }
        } elseif (Tools::strtolower($a[$columns[$order[0]['column']]['data']]) >
            Tools::strtolower($b[$columns[$order[0]['column']]['data']])) {
            if ($order[0]['dir'] === 'asc') {
                return 1;
            } else {
                return -1;
            }
        } else {
            if (isset($order[1])) {
                if (Tools::strtolower($a[$columns[$order[1]['column']]['data']]) <
                    Tools::strtolower($b[$columns[$order[1]['column']]['data']])) {
                    if ($order[1]['dir'] === 'asc') {
                        return -1;
                    } else {
                        return 1;
                    }
                } elseif (Tools::strtolower($a[$columns[$order[1]['column']]['data']]) >
                    Tools::strtolower($b[$columns[$order[1]['column']]['data']])) {
                    if ($order[1]['dir'] === 'asc') {
                        return 1;
                    } else {
                        return -1;
                    }
                } else {
                    if (isset($order[2])) {
                        if (Tools::strtolower($a[$columns[$order[2]['column']]['data']]) <
                            Tools::strtolower($b[$columns[$order[2]['column']]['data']])) {
                            if ($order[2]['dir'] === 'asc') {
                                return -1;
                            } else {
                                return 1;
                            }
                        } elseif (Tools::strtolower($a[$columns[$order[2]['column']]['data']]) >
                            Tools::strtolower($b[$columns[$order[2]['column']]['data']])) {
                            if ($order[2]['dir'] === 'asc') {
                                return 1;
                            } else {
                                return -1;
                            }
                        } else {
                            return 0;
                        }
                    } else {
                        return 0;
                    }
                }
            } else {
                return 0;
            }
        }
    }

    public static function getPaymentMethods()
    {
        $hook_payment = 'Payment';
        if (Db::getInstance()->getValue('SELECT `id_hook` FROM `'
                . _DB_PREFIX_ . 'hook` WHERE `name` = \'displayPayment\'')) {
            $hook_payment = 'displayPayment';
        }

        $ps17_hook_payment = 'paymentOptions';

        $sql = 'SELECT DISTINCT m.`id_module` id, m.`name`
            FROM `' . _DB_PREFIX_ . 'module` m
            LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON hm.`id_module` = m.`id_module`
            LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON hm.`id_hook` = h.`id_hook`
            WHERE h.`name` IN ( \'' . pSQL($hook_payment) . '\', \'' . $ps17_hook_payment . '\')';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.`name` IN ('" . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                } else {
                    $cond .= " AND m.`name` NOT IN ('" . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0 ";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.`name` NOT IN ('" . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                } else {
                    $cond .= " AND m.`name` IN ('" . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0 ";
            }
        }

        $sql .= $cond;

        $payment_methods = DB::getInstance()->executeS($sql);

        $data = array();
        $search = Tools::getValue('search')['value'];

        foreach ($payment_methods as &$val) {
            $val['module_name'] = Module::getModuleName($val['name']);
            $val['logo'] = '<img style="max-width:40px;" src="' . __PS_BASE_URI__ . 'modules/' . $val['name'] . '/logo.png' . '" alt="" title="' . $val['module_name'] . '" />';
            if ($search || $search === '0') {
                if (stripos($val['name'], $search) !== false || stripos($val['id'], $search) !== false || stripos($val['module_name'], $search) !== false) {
                    $data[] = $val;
                }
            }
        }

        if (!$search && $search !== '0') {
            $data = $payment_methods;
        }
//        var_dump($search);
//        exit;
        $filtered = count($data);

        usort($data, array('DataFilter', 'sortPaymentMethods'));

        $data = array_slice($data, Tools::getValue('start'), Tools::getValue('length'));

        $total = DB::getInstance()->getValue('SELECT COUNT(DISTINCT m.`id_module`)
            FROM `' . _DB_PREFIX_ . 'module` m
            LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON hm.`id_module` = m.`id_module`
            LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON hm.`id_hook` = h.`id_hook`
            WHERE h.`name` IN ( \'' . pSQL($hook_payment) . '\', \'' . $ps17_hook_payment . '\')');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }
    
    public static function getPaymentMethods2()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT
                CONCAT(IFNULL(module, ""), "_#&_", IFNULL(payment, "")) id, 
                module `name`, 
                payment `module_name`
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND CONCAT(IFNULL(module, ''), '_#&_', IFNULL(payment, '')) IN ('"
                        . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                } else {
                    $cond .= " AND CONCAT(IFNULL(module, ''), '_#&_', IFNULL(payment, '')) NOT IN ('"
                        . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND CONCAT(IFNULL(module, ''), '_#&_', IFNULL(payment, '')) NOT IN ('"
                        . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                } else {
                    $cond .= " AND CONCAT(IFNULL(module, ''), '_#&_', IFNULL(payment, '')) IN ('"
                        . implode("', '", Tools::getValue('extra_search_params')['data']) . "')";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        
        if (Tools::getValue('search')['value']) {
            $cond .= " AND (module LIKE '%" . Tools::getValue('search')['value'] . "%' OR ";
            $cond .= "`payment` LIKE '%" . Tools::getValue('search')['value'] . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = '<img style="max-width:40px;" src="' . __PS_BASE_URI__ . 'modules/'
                . $val['name'] . '/logo.png' . '" alt="" title="' . $val['module_name'] . '" />';
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(DISTINCT module, payment) count FROM '
            . _DB_PREFIX_ . 'orders');


        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getProducts()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                        p.id_product id,
                        pl.`name`,
                        p.reference,
                        cl.`name` category,
                        ROUND(p.price, 2) base_price,
                        ROUND(p.price * (1 + rate / 100) ,2) final_price,
                        sa.quantity,
                        active enabled ';
        $lang = Context::getContext()->language->id;
        $table = '
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON p.id_category_default = cl.id_category AND cl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product AND sa.id_product_attribute = 0
                LEFT JOIN (
                        SELECT rate, tr.id_tax_rules_group
                        FROM ' . _DB_PREFIX_ . 'tax t
                        LEFT JOIN ' . _DB_PREFIX_ . 'tax_rule tr ON t.id_tax = tr.id_tax GROUP BY tr.id_tax_rules_group) tmp
                    ON p.id_tax_rules_group = tmp.id_tax_rules_group
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        $table .= $cond . ' GROUP BY p.id_product) tmp WHERE 1 ';
        $search = Tools::getValue('search')['value'];
        $cond2 = '';
        if ($search || $search === '0') {
            $cond2 .= " AND (`name` LIKE '%" . $search . "%' OR ";
            $cond2 .= "category LIKE '%" . $search . "%' OR ";
            $cond2 .= "reference LIKE '%" . $search . "%' OR ";
            $cond2 .= "base_price LIKE '%" . $search . "%' OR ";
            $cond2 .= "final_price LIKE '%" . $search . "%' OR ";
            $cond2 .= "quantity LIKE '%" . $search . "%' OR ";
            $cond2 .= "id LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond2 . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_product) `count` FROM `'
            . _DB_PREFIX_ . 'product`');

        $currency_symbol = Configuration::get('OXSRP_DEF_CURR_SMBL');

        foreach ($data as &$val) {
            $cover = Product::getCover($val['id']);
            $join = Image::getImgFolderStatic($cover['id_image']) . (int) $cover['id_image'];
            $val['image'] = ImageManager::thumbnail(_PS_PROD_IMG_DIR_ . $join . '.jpg', 'product_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');

            $val['base_price'] = $val['base_price'] ? $currency_symbol . $val['base_price'] : '';
            $val['final_price'] = $val['final_price'] ? $currency_symbol . $val['final_price'] : '';
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getCategories()
    {
        $catIds = array();
        $categories = Category::getCategories(Context::getContext()->language->id, false, false);
        foreach ($categories as $cat) {
            $catIds[] = $cat['id_category'];
        }

        $tree = new HelperTreeCategories('data_export_orders_categories_tree', 'Filter by Category');
        $tree->setRootCategory((int) Category::getRootCategory()->id)
            ->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('products_categories')
            ->setSelectedCategories($catIds);

        die($tree->render());
    }
    
    public static function getFeatures()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                    CONCAT(IFNULL(fl.name, ""), "_#&_", IFNULL(fvl.value, ""), "_#&_", fv.custom) id,
                    fl.name feature_name,
                    fvl.value feature_value,
                    fv.custom
                FROM ' . _DB_PREFIX_ . 'feature_value fv
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = '.$lang.'
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON f.id_feature = fv.id_feature
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = '.$lang.'
                GROUP BY fl.name, fvl.value, fv.custom) features
                WHERE 1 ';
        
        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                } else {
                    $cond .= " AND id NOT IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id NOT IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                } else {
                    $cond .= " AND id IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (feature_name LIKE '%" . $search . "%' OR ";
            $cond .= "feature_value LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(feature_value) count FROM (
                SELECT
                    fvl.value feature_value
                FROM ' . _DB_PREFIX_ . 'feature_value fv
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = '.$lang.'
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON f.id_feature = fv.id_feature
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = '.$lang.'
                GROUP BY fl.name, fvl.value, fv.custom) features');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(Tools::jsonEncode($response));
    }
    
    public static function getAttributes()
    {
        $lang = Context::getContext()->language->id;
        $sql = '
            SELECT SQL_CALC_FOUND_ROWS
                    a.id_attribute id,
                    ag.id_attribute_group group_id,
                    ag.group_type,
                    agl.name group_name,
                    al.name attribute_name
            FROM ' . _DB_PREFIX_ . 'attribute a
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $lang . '
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $lang . ' 
            WHERE 1 ';
        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (ag.group_type LIKE '%" . $search . "%' OR ";
            $cond .= "agl.name LIKE '%" . $search . "%' OR ";
            $cond .= "a.id_attribute LIKE '%" . $search . "%' OR ";
            $cond .= "ag.id_attribute_group LIKE '%" . $search . "%' OR ";
            $cond .= "al.name LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');
        
        $total = DB::getInstance()->getValue('SELECT COUNT(id_attribute) count FROM `'
            . _DB_PREFIX_ . 'attribute`');

        $response = array(
            'draw'            => pSQL(Tools::getValue('draw')),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data
        );
        die(Tools::jsonEncode($response));
    }

    public static function getAutoexportFTPs($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_oxsrp_aexp_ftp ftp_id,
                ftp_type,
                ftp_mode,
                IF(ftp_port = '' OR ftp_port IS NULL, ftp_url, CONCAT(ftp_url, ':', ftp_port)) ftp_url,
                ftp_username,
                ftp_password,
                ftp_folder,
                ftp_timestamp,
                ftp_setting,
                ftp_active,
                IF(b.`name` = 'orders_default', '--" . $module->l('Default', DataFilter::class) . " --', b.`name`) ftp_setting_name
            FROM " . _DB_PREFIX_ . "oxsrp_aexp_ftp a
            LEFT JOIN " . _DB_PREFIX_ . "orders_export_srpro b ON a.ftp_setting = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`ftp_url` LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_username LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_password LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_folder LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_setting LIKE '%" . $search . "%' OR ";
            $cond .= "id_oxsrp_aexp_ftp LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_oxsrp_aexp_ftp) cc
            FROM ' . _DB_PREFIX_ . 'oxsrp_aexp_ftp');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(Tools::jsonEncode($response));
    }

    public static function getAutoexportEmails($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_oxsrp_aexp_email email_id,
                email_address,
                email_setting,
                email_active,
                IF(b.`name` = 'orders_default', '-- " . $module->l('Default', DataFilter::class) . " --', b.`name`) email_setting_name
            FROM " . _DB_PREFIX_ . "oxsrp_aexp_email a
            LEFT JOIN " . _DB_PREFIX_ . "orders_export_srpro b ON a.email_setting = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`email_address` LIKE '%" . $search . "%' OR ";
            $cond .= "email_setting LIKE '%" . $search . "%' OR ";
            $cond .= "id_oxsrp_aexp_email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_oxsrp_aexp_email) cc
            FROM ' . _DB_PREFIX_ . 'oxsrp_aexp_email');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(Tools::jsonEncode($response));
    }

    public static function getScheduleFTPs($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_oxsrp_schdl_ftp ftp_id,
                ftp_type,
                ftp_mode,
                IF(ftp_port = '' OR ftp_port IS NULL, ftp_url, CONCAT(ftp_url, ':', ftp_port)) ftp_url,
                ftp_username,
                ftp_password,
                ftp_folder,
                ftp_timestamp,
                ftp_setting,
                ftp_active,
                IF(b.`name` = 'orders_default', '-- " . $module->l('Default', DataFilter::class) . " --', b.`name`) ftp_setting_name
            FROM " . _DB_PREFIX_ . "oxsrp_schdl_ftp a
            LEFT JOIN " . _DB_PREFIX_ . "orders_export_srpro b ON a.ftp_setting = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`ftp_url` LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_username LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_password LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_folder LIKE '%" . $search . "%' OR ";
            $cond .= "ftp_setting LIKE '%" . $search . "%' OR ";
            $cond .= "id_oxsrp_schdl_ftp LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_oxsrp_schdl_ftp) cc
            FROM ' . _DB_PREFIX_ . 'oxsrp_schdl_ftp');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(Tools::jsonEncode($response));
    }

    public static function getScheduleEmails($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_oxsrp_schdl_email email_id,
                email_address,
                email_setting,
                email_active,
                IF(b.`name` = 'orders_default', '-- " . $module->l('Default', DataFilter::class) . " --', b.`name`) email_setting_name
            FROM " . _DB_PREFIX_ . "oxsrp_schdl_email a
            LEFT JOIN " . _DB_PREFIX_ . "orders_export_srpro b ON a.email_setting = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`email_address` LIKE '%" . $search . "%' OR ";
            $cond .= "email_setting LIKE '%" . $search . "%' OR ";
            $cond .= "id_oxsrp_schdl_email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        foreach (Tools::getValue('order') as $order) {
            $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . pSQL(Tools::getValue('start')) . ', ' . pSQL(Tools::getValue('length'));

        $data = DB::getInstance()->executeS($sql);
        
        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_oxsrp_schdl_email) cc
            FROM ' . _DB_PREFIX_ . 'oxsrp_schdl_email');

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(Tools::jsonEncode($response));
    }
}
