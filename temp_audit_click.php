<?php
$root = __DIR__ . '/resources/views';
$rii  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($rii as $f) {
    if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
        $files[] = $f->getPathname();
    }
}

// Collect every JS function defined across all views + public/app.js
$defined = [];
$all = $files;
$all[] = __DIR__ . '/public/app.js';
foreach ($all as $p) {
    $src = file_get_contents($p);
    if (preg_match_all('/function\s+([A-Za-z0-9_]+)\s*\(/', $src, $m)) {
        foreach ($m[1] as $fn) $defined[$fn] = true;
    }
    if (preg_match_all('/(?:const|let|var)\s+([A-Za-z0-9_]+)\s*=\s*function/', $src, $m2)) {
        foreach ($m2[1] as $fn) $defined[$fn] = true;
    }
    if (preg_match_all('/window\.([A-Za-z0-9_]+)\s*=/', $src, $m3)) {
        foreach ($m3[1] as $fn) $defined[$fn] = true;
    }
}

// Native JS / global functions we should not flag
$native = ['alert','confirm','print','location','open','submit','toggleProfileMenu','history'];

echo "== onclick handlers with NO function definition ==\n";
foreach ($files as $p) {
    $src   = file_get_contents($p);
    $lines = explode("\n", $src);
    foreach ($lines as $i => $line) {
        if (preg_match_all('/on[a-z]+="([^"]*)"/', $line, $mm)) {
            foreach ($mm[1] as $code) {
                if (preg_match_all('/([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $code, $fm)) {
                    foreach ($fm[1] as $fn) {
                        if (in_array($fn, ['if','for','while','switch','return','function','typeof','parseInt','parseFloat','Number','String','encodeURIComponent','setTimeout','URL','getElementById','querySelector','querySelectorAll','addEventListener'])) continue;
                        if (isset($defined[$fn])) continue;
                        echo str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $p) . ':' . ($i + 1) . "  -> {$fn}()   [" . trim(substr($code, 0, 90)) . "]\n";
                    }
                }
            }
        }
    }
}

echo "\n== href=\"#\" dead links ==\n";
foreach ($files as $p) {
    $lines = explode("\n", file_get_contents($p));
    foreach ($lines as $i => $line) {
        if (str_contains($line, 'href="#"')) {
            echo str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $p) . ':' . ($i + 1) . '  ' . trim(substr($line, 0, 100)) . "\n";
        }
    }
}
