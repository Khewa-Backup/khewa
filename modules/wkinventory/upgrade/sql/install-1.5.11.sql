ALTER TABLE `PREFIX_wkinventory` ADD `id_warehouse` INT(11) NULL DEFAULT NULL AFTER `manufacturer_ids`;
ALTER TABLE `PREFIX_wkinventory_product` 
ADD `id_warehouse` INT(11) NULL DEFAULT NULL AFTER `id_product_attribute`,
ADD `id_currency` INT(11) NULL DEFAULT NULL AFTER `unit_price`,
ADD `has_error` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL AFTER `id_employee`;
