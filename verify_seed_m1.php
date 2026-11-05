<?php
// Quick seed verification script for M1 Week 2
$dsn = 'sqlite:' . __DIR__ . '/database/database.sqlite';
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$checks = [
    ['query' => "SELECT COUNT(*) as c FROM leads", 'col' => 'c', 'expect' => 3, 'label' => 'Leads count = 3'],
    ['query' => "SELECT status FROM sales_orders WHERE id='S00007'", 'col' => 'status', 'expect' => 'confirmed', 'label' => 'S00007 status = confirmed'],
    ['query' => "SELECT gross_amount FROM sales_orders WHERE id='S00007'", 'col' => 'gross_amount', 'expect' => 222000, 'label' => 'S00007 gross = 222.000'],
    ['query' => "SELECT COUNT(*) as c FROM sales_order_lines WHERE sales_order_id='S00007'", 'col' => 'c', 'expect' => 1, 'label' => 'S00007 line count = 1'],
    ['query' => "SELECT reserved FROM opening_stock WHERE variant_sku='CLN-DTR-800-LMN'", 'col' => 'reserved', 'expect' => 8, 'label' => 'P01 reserved = 8 (SR-22)'],
    ['query' => "SELECT stage FROM leads WHERE id='L00003'", 'col' => 'stage', 'expect' => 'new', 'label' => 'L00003 stage = new'],
    ['query' => "SELECT partner_name FROM leads WHERE id='L00003'", 'col' => 'partner_name', 'expect' => 'UD Berkah Niaga', 'label' => 'L00003 partner = UD Berkah Niaga (C03)'],
    ['query' => "SELECT SUM(company_total) as t FROM opening_stock", 'col' => 't', 'expect' => 880, 'label' => 'Total on hand = 880'],
    ['query' => "SELECT SUM(standard_value) as v FROM opening_stock", 'col' => 'v', 'expect' => 23095000, 'label' => 'Total stock value = Rp23.095.000'],
];

$passed = 0; $failed = 0;
foreach ($checks as $c) {
    $stmt = $pdo->query($c['query']);
    $row  = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    $col  = $c['col'] ?? 'c';
    $actual = $row[$col] ?? null;
    $ok = (string)$actual === (string)$c['expect'];
    if ($ok) { $passed++; echo "✅ PASS  {$c['label']} (got: {$actual})\n"; }
    else      { $failed++; echo "❌ FAIL  {$c['label']} (expected: {$c['expect']}, got: {$actual})\n"; }
}

echo "\n--- RESULT: {$passed} passed, {$failed} failed ---\n";
