<?php
$targets = [
    'resources/views/reporting/dashboard.blade.php'                => [[1, 40], [270, 310]],
    'resources/views/reporting/testing.blade.php'                  => [[1, 45]],
    'resources/views/inventory/stock.blade.php'                    => [[1, 40], [175, 215]],
    'resources/views/accounting/invoices/index.blade.php'          => [[25, 55]],
    'resources/views/accounting/ledger/index.blade.php'            => [[15, 45]],
    'resources/views/admin/business-rules.blade.php'               => [[1, 25]],
];

foreach ($targets as $file => $ranges) {
    $lines = explode("\n", file_get_contents($file));
    echo "===== {$file} =====\n";
    foreach ($ranges as [$from, $to]) {
        for ($i = $from; $i <= min($to, count($lines)); $i++) {
            echo $i . ': ' . rtrim($lines[$i - 1]) . "\n";
        }
        echo "---\n";
    }
}
