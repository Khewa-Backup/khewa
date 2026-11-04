<?php
// Rebuild the amazzingfilter product index (ps_af_index) after the data move.
// Same as the module's settings page: erase the index, then index all products, per shop.
// Usage: php reindex-filter.php /path/to/live
$root = rtrim($argv[1] ?? '', '/');
if (!is_file($root . '/config/config.inc.php')) {
    fwrite(STDERR, "give the shop folder\n");
    exit(1);
}
$_SERVER['REQUEST_METHOD'] = 'GET';
require $root . '/config/config.inc.php';
error_reporting(E_ALL & ~E_DEPRECATED); // the module's PHP 8.2+ notices flood the log otherwise

$af = Module::getInstanceByName('amazzingfilter');
if (!$af || !$af->active) {
    fwrite(STDERR, "amazzingfilter not active\n");
    exit(1);
}
foreach (Shop::getShops(true, null, true) as $id_shop) {
    Shop::setContext(Shop::CONTEXT_SHOP, (int) $id_shop);
    Context::getContext()->shop = new Shop((int) $id_shop);
    $af->indexationData('erase', ['id_shop' => (int) $id_shop]);
    $total = 0;
    $rounds = 0;
    do {
        $n = $af->indexMissingProducts(500, [(int) $id_shop]);
        $total += $n;
    } while ($n > 0 && ++$rounds < 200);
    $info = $af->indexationInfo('count', [(int) $id_shop]);
    echo "shop $id_shop: indexed $total, missing " . ($info[$id_shop]['missing'] ?? '?') . "\n";
}
