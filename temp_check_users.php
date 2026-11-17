<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
echo "=== USERS ===\n";
foreach ($db->query('SELECT id, username, role, name, email FROM users') as $r) {
    echo $r['id'] . '  ' . $r['username'] . '  role=[' . $r['role'] . ']  ' . $r['name'] . "\n";
}
echo "\n=== EMPLOYEE -> USER MAP ===\n";
foreach ($db->query('SELECT id, name, department_id, status, mapped_user_id FROM employees') as $r) {
    echo $r['id'] . '  ' . $r['name'] . '  dept=' . $r['department_id'] . '  ' . $r['status'] . '  user=' . ($r['mapped_user_id'] ?? '-') . "\n";
}
echo "\n=== DELIVERIES ===\n";
foreach ($db->query('SELECT id, sales_order_id, status, is_posted FROM deliveries') as $r) {
    echo implode('  ', $r) . "\n";
}
