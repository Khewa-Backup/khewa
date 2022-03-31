ALTER TABLE `PREFIX_wkinventory` ADD `category_ids` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;
ALTER TABLE `PREFIX_wkinventory_product` ADD `sold_quantity` INT(11) NULL DEFAULT NULL AFTER `real_quantity`;
