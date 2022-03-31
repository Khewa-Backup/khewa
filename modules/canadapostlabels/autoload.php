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

/**
 * autoload
 *
 * @param  string $class
 * @param  string $dir
 * @return bool
 */
function __CplAutoloader($class, $dir = null)
{
    if (is_null($dir)) {
        $dir = dirname(__FILE__).'/classes/';
    }

    foreach (scandir($dir) as $file) {

        // directory?
        if (is_dir($dir.$file) && \Tools::substr($file, 0, 1) !== '.') {
            __CplAutoloader($class, $dir.$file.'/');
        }

        // php file?
        if (\Tools::substr($file, 0, 2) !== '._' && preg_match("/.php$/i", $file)) {

//            $className = str_replace('\\', '/', $class);
            // filename matches class?
            if (str_replace('.php', '', $file) == $class || str_replace('.class.php', '', $file) == $class || strpos($class, str_replace('.php', '', $file))) {
                require_once $dir . $file;
            }
        }
    }
}

spl_autoload_register('__CplAutoloader');
