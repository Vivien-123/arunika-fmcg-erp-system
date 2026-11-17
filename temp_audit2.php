<?php
$root = __DIR__ . '/resources/views';
$rii  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($rii as $f) {
    if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) $files[] = $f->getPathname();
}
sort($files);

$problems = [];

foreach ($files as $p) {
    $src = file_get_contents($p);
    $rel = str_replace([__DIR__ . DIRECTORY_SEPARATOR, __DIR__ . '/'], '', $p);

    // full <button ...> tags (multi-line aware)
    if (preg_match_all('/<button\b[^>]*>/is', $src, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[0] as [$tag, $offset]) {
            $line    = substr_count(substr($src, 0, $offset), "\n") + 1;
            $hasOn   = preg_match('/on(click|submit|change|input)\s*=/i', $tag);
            $isSubmit= preg_match('/type\s*=\s*["\']submit["\']/i', $tag);
            $hasForm = preg_match('/\bform\s*=\s*["\']/i', $tag);
            $hasId   = preg_match('/\bid\s*=\s*["\']/i', $tag);
            if (!$hasOn && !$isSubmit && !$hasForm) {
                $problems[] = [$rel, $line, 'BUTTON tanpa handler/submit' . ($hasId ? ' (punya id, mungkin di-wire via JS)' : '') , preg_replace('/\s+/', ' ', substr($tag, 0, 120))];
            }
        }
    }

    // <a ...> without href or href="#"
    if (preg_match_all('/<a\b[^>]*>/is', $src, $m2, PREG_OFFSET_CAPTURE)) {
        foreach ($m2[0] as [$tag, $offset]) {
            $line = substr_count(substr($src, 0, $offset), "\n") + 1;
            if (!preg_match('/href\s*=/i', $tag)) {
                $problems[] = [$rel, $line, 'LINK tanpa href', preg_replace('/\s+/', ' ', substr($tag, 0, 120))];
            } elseif (preg_match('/href\s*=\s*["\']#["\']/i', $tag)) {
                $problems[] = [$rel, $line, 'LINK href="#" (tidak navigasi)', preg_replace('/\s+/', ' ', substr($tag, 0, 120))];
            }
        }
    }
}

foreach ($problems as $row) {
    echo str_pad($row[2], 34) . ' | ' . $row[0] . ':' . $row[1] . ' | ' . $row[3] . "\n";
}
echo "\nTotal: " . count($problems) . "\n";
