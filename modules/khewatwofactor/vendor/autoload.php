<?php
/**
 * Khewa staff login code: class loader for the module's src/ folder (namespace KhewaTwoFactor\).
 *
 * PrestaShop 9.2 includes modules/<name>/vendor/autoload.php for every installed module,
 * early, and only when the file is there. So when the module folder is renamed or deleted,
 * nothing here runs and nothing breaks.
 *
 * Hand-written (no Composer needed): KhewaTwoFactor\Foo\Bar => src/Foo/Bar.php
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'KhewaTwoFactor\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
