<?php
header('Content-Type: text/plain');
$start = '/home/kbklzsah';
$dirCounts = [];

function scan($dir, &$dirCounts) {
    $files = @scandir($dir);
    if ($files === false) return;
    $count = 0;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            if ($file !== '.cagefs' && $file !== '.trash') {
                scan($path, $dirCounts);
            }
        } else {
            $count++;
        }
    }
    if ($count > 0) {
        $dirCounts[$dir] = $count;
    }
}

scan($start, $dirCounts);
arsort($dirCounts);
$top = array_slice($dirCounts, 0, 50, true);
foreach ($top as $dir => $count) {
    echo "$count - $dir\n";
}
