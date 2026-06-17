<?php
/**
 * 2007-2017 ETSHybridauth
 *
 *  @author Hybridauth <https://hybridauth.github.io>
 *  @copyright  2009-2017 Hybridauth
 *  @license    https://hybridauth.github.io/license.html
 *  International Registered Trademark & Property of ETSHybridauth
 */

if (!defined('_PS_VERSION_')) { exit; }


if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('ETSHybridauth 3 requires PHP version 5.4 or higher.');
}
/**
 * Register the autoloader for ETSHybridauth classes.
 *
 * Based off the official PSR-4 autoloader example found at
 * http://www.php-fig.org/psr/psr-4/examples/
 *
 * @param string $class The fully-qualified class name.
 *
 * @return void
 */
spl_autoload_register(
    function ($class) {
        // project-specific namespace prefix. Will only kicks in for ETSHybridauth's namespace.
        $prefix = 'ETSHybridauth\\';

        // base directory for the namespace prefix.
        $base_dir = dirname(__FILE__);// By default, it points to this same folder.
                               // You may change this path if having trouble detecting the path to
                               // the source files.

        // does the class use the namespace prefix?
        $len = \Tools::strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader.
            return;
        }

        // get the relative class name.
        $relative_class = \Tools::substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $relative_class).'.php';

        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
);
