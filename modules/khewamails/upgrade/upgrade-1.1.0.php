<?php


function upgrade_module_1_1_0($module)
{
    return Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'khewamails` ADD `name` VARCHAR(255) NULL AFTER `id`'
    );
}
