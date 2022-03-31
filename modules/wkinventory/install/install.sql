CREATE TABLE IF NOT EXISTS `PREFIX_wkinventory` (
  `id_inventory` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `id_employee` int(11) NOT NULL,
  `category_ids` varchar(255) DEFAULT NULL,
  `id_supplier` int(11) NOT NULL,
  `manufacturer_ids` varchar(255) DEFAULT NULL,
  `id_warehouse` int(11) DEFAULT NULL,
  `id_shop` int(11) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime DEFAULT NULL,
  `done` tinyint(1) NOT NULL,
  `stock_updated` tinyint(1) NOT NULL,
  `is_empty` tinyint(1) unsigned DEFAULT '0',
  `stock_zero` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id_inventory`)
) ENGINE=_SQLENGINE_ DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_wkinventory_product` (
  `id_inventory_product` int(11) NOT NULL AUTO_INCREMENT,
  `id_inventory` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_product_attribute` int(11) NOT NULL,
  `id_warehouse` int(11) DEFAULT NULL,
  `id_employee` int(11) NOT NULL,
  `date_upd` datetime DEFAULT NULL,
  `shop_quantity` int(11) NOT NULL,
  `real_quantity` int(11) NOT NULL,
  `sold_quantity` int(11) DEFAULT NULL,
  `unit_price` float NOT NULL,
  `id_currency` int(11) DEFAULT NULL,
  `stock_updated` tinyint(1) NOT NULL,
  `has_error` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_inventory_product`),
  KEY `id_inventory` (`id_inventory`),
  KEY `product_attribute_product` (`id_product`),
  KEY `id_product_id_product_attribute` (`id_product`,`id_product_attribute`)
) ENGINE=_SQLENGINE_ DEFAULT CHARSET=utf8;

