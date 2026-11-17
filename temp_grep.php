<?php
$root = __DIR__;
$needles = ['reporting.testing', 'Integration Testing', 'Test Results', 'reporting.audit'];
$dirs = ['routes', 'resources/views', 'app'];
$hits = [];
$rii = function ($dir) use (&$rii, $root) {
    $out = [];
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $p = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_dir($p)) $out = array_merge($out, $rii($p));
        elseif (preg_match('/\.(php|blade\.php)$/', $f)) $out[] = $p;
    }
    return $out;
};
foreach ($dirs as $d) {
    $path = $root . DIRECTORY_SEPARATOR . $d;
    if (!is_dir($path)) continue;
    foreach ($rii($path) as $file) {
        $lines = file($file);
        foreach ($lines as $i => $line) {
            foreach ($needles as $n) {
                if (stripos($line, $n) !== false) {
                    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
                    echo $rel . ':' . ($i + 1) . ': ' . trim(substr($line, 0, 160)) . PHP_EOL;
                    break;
                }
            }
        }
    }
}
