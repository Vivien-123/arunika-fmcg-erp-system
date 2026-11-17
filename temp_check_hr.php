<?php
$base = 'http://127.0.0.1:8000';
$jar  = sys_get_temp_dir() . '/arunika_hr.txt';

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
    return ['code' => $code, 'body' => substr($raw, $hdr)];
}

function check(string $label, bool $pass, string $detail = ''): void
{
    echo ($pass ? 'PASS  ' : 'FAIL  ') . $label . ($detail ? '   [' . $detail . ']' : '') . "\n";
}

@unlink($jar);
$page = http($base . '/login', null, $jar);
preg_match('/name="_token"\s+value="([^"]+)"/', $page['body'], $m);
http($base . '/login', ['_token' => $m[1] ?? '', 'username' => 'hr_user', 'password' => 'Demo2026!'], $jar);

$dir = http($base . '/employees', null, $jar);
check('hr: employee directory allowed', $dir['code'] === 200 && str_contains($dir['body'], 'Dini Pratiwi'), 'code=' . $dir['code']);

$private = http($base . '/employees/E01', null, $jar);
check('hr: can see private record block', str_contains($private['body'], 'Data Privat Pegawai'), 'code=' . $private['code']);

$defense = http($base . '/employees/defense', null, $jar, false);
check('hr: defense prep blocked', $defense['code'] === 403, 'code=' . $defense['code']);

$me = http($base . '/employees/me', null, $jar);
check('hr: /employees/me resolves to own record', str_contains($me['body'], 'Melati Yusuf'), 'code=' . $me['code']);

$menu = http($base . '/employees', null, $jar);
check('hr: profile menu still shows directory label', str_contains($menu['body'], 'Profil & Pegawai') || str_contains($menu['body'], 'Profil & Pegawai'));
