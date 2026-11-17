<?php
$base = 'http://127.0.0.1:8000';

function http(string $url, ?array $post = null, ?string $jar = null, bool $follow = true): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 60,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hdr  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    return ['code' => $code, 'headers' => substr($raw, 0, $hdr), 'body' => substr($raw, $hdr)];
}

function csrf(string $html): ?string
{
    return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function login(string $base, string $user, string $jar): array
{
    @unlink($jar);
    $page  = http($base . '/login', null, $jar);
    $token = csrf($page['body']);
    if (!$token) return ['ok' => false, 'why' => 'no csrf'];

    $res = http($base . '/login', ['_token' => $token, 'username' => $user, 'password' => 'Demo2026!'], $jar);
    return ['ok' => $res['code'] === 200, 'code' => $res['code']];
}

function check(string $label, bool $pass, string $detail = ''): void
{
    echo ($pass ? 'PASS  ' : 'FAIL  ') . $label . ($detail ? '   [' . $detail . ']' : '') . "\n";
}

$jarSales = sys_get_temp_dir() . '/arunika_sales.txt';
$jarOwner = sys_get_temp_dir() . '/arunika_owner.txt';

// ── Sales user ────────────────────────────────────────────────────────
$r = login($base, 'sales_user', $jarSales);
check('login sales_user', $r['ok'] ?? false, json_encode($r));

$me = http($base . '/employees/me', null, $jarSales);
check('sales: /employees/me shows own profile', str_contains($me['body'], 'Dini Pratiwi') && !str_contains($me['body'], 'Melati Yusuf'), 'code=' . $me['code']);

$meSide = http($base . '/employees/me', null, $jarSales);
check('sales: sidebar only Profil Saya', str_contains($meSide['body'], 'Profil Saya') && !str_contains($meSide['body'], 'Employee Directory'));

$dir = http($base . '/employees', null, $jarSales, false);
check('sales: /employees blocked (403)', $dir['code'] === 403 || $dir['code'] === 302, 'code=' . $dir['code']);

$other = http($base . '/employees/E08', null, $jarSales);
check('sales: other employee profile redirected to own', str_contains($other['body'], 'Dini Pratiwi') && str_contains($other['body'], 'hanya dapat melihat profil Anda sendiri'), 'code=' . $other['code']);

$dashExport = http($base . '/reporting/dashboard/export', null, $jarSales, false);
check('dashboard export CSV', $dashExport['code'] === 200 && str_contains($dashExport['body'], 'BEV-COF-B20-ORI'), 'code=' . $dashExport['code']);

$testRun = http($base . '/reporting/testing?run=1', null, $jarSales);
check('testing run scenarios executes', str_contains($testRun['body'], 'Skenario dijalankan'), 'code=' . $testRun['code']);

$testPage = http($base . '/reporting/testing', null, $jarSales);
check('testing filters present', str_contains($testPage['body'], 'testSearch') && str_contains($testPage['body'], 'exportTestingCsv'));

$stockPage = http($base . '/inventory/stock', null, $jarSales);
check('stock page filters present', str_contains($stockPage['body'], 'applyStockFilters') && str_contains($stockPage['body'], 'stockRows'));
check('stock page transfer read-only for Sales', str_contains($stockPage['body'], 'tidak memiliki izin Internal Transfer'));

// ── Owner user ────────────────────────────────────────────────────────
$r = login($base, 'owner_user', $jarOwner);
check('login owner_user', $r['ok'] ?? false, json_encode($r));

$del = http($base . '/inventory/deliveries', null, $jarOwner);
check('owner: deliveries list, no create button', $del['code'] === 200 && !str_contains($del['body'], 'Buat Delivery Order'), 'code=' . $del['code']);

$delCreate = http($base . '/inventory/deliveries/create', null, $jarOwner);
check('owner: create DO form blocked (redirect + error)', str_contains($delCreate['body'], 'view-only'), 'code=' . $delCreate['code']);

$post = http($base . '/inventory/deliveries', ['_token' => csrf($del['body']) ?? '', 'sales_order_id' => 'S00007'], $jarOwner, false);
check('owner: POST /inventory/deliveries blocked', $post['code'] === 403 || $post['code'] === 302, 'code=' . $post['code']);

$ownerEmp = http($base . '/employees', null, $jarOwner);
check('owner: employee directory allowed', $ownerEmp['code'] === 200 && str_contains($ownerEmp['body'], 'Melati Yusuf'), 'code=' . $ownerEmp['code']);

$ownerRules = http($base . '/admin/business-rules', null, $jarOwner);
check('owner: business rules page loads (export/confirm wired)', str_contains($ownerRules['body'], 'exportRulesCsv') && str_contains($ownerRules['body'], 'ruleForm'), 'code=' . $ownerRules['code']);
