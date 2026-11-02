<?php
/**
 * Khewa PS 9.2: class loader for the module's src/ folder (namespace KhewaCoreChanges\).
 *
 * PrestaShop loads modules/<name>/vendor/autoload.php for every active module, early,
 * before the Symfony container is built (AppKernel::enableComposerAutoloaderOnModules).
 * PrestaShop 9.2 needs this: its Messenger compiler pass checks that every command handler
 * class exists while the container is built, and the `file:` key in config/services.yml is
 * only used later, when the service is created. Without this file the whole back office
 * fails with "class KhewaCoreChanges\...\AddOrderCustomerMessageHandler does not exist".
 *
 * Hand-written (no Composer needed): KhewaCoreChanges\Foo\Bar => src/Foo/Bar.php
 */
spl_autoload_register(static function ($class) {
    $prefix = 'KhewaCoreChanges\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
