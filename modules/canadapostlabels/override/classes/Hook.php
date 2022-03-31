<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

class Hook extends HookCore
{
    /**
     * Remove backslashes in hook names to fix namespace bug
     * Commit: 8eaf5a3677bc40c44959bfd2ac06b89039c1c1af
     * https://github.com/PrestaShop/PrestaShop/commit/8eaf5a3677bc40c44959bfd2ac06b89039c1c1af
     * @module canadapostlabels
     * */
    public static function exec(
        $hook_name,
        $hook_args = array(),
        $id_module = null,
        $array_return = false,
        $check_exceptions = true,
        $use_push = false,
        $id_shop = null,
        $chain = false
    ) {

        $hook_name = str_replace('\\', '', $hook_name);

        return parent::exec(
            $hook_name,
            $hook_args,
            $id_module,
            $array_return,
            $check_exceptions,
            $use_push,
            $id_shop,
            $chain
        );
    }
}