<?php

$sourceDatabase = __DIR__ . '/../database/database.sqlite';
$serverlessDatabase = '/tmp/database.sqlite';

if (!file_exists($serverlessDatabase) && file_exists($sourceDatabase)) {
    copy($sourceDatabase, $serverlessDatabase);
}

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . $serverlessDatabase);
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $serverlessDatabase;

putenv('APP_STORAGE=/tmp');
putenv('VIEW_COMPILED_PATH=/tmp');
putenv('APP_URL=https://arunika-fmcg-erp-system-seven.vercel.app');
$_ENV['APP_STORAGE'] = '/tmp';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp';
$_ENV['APP_URL'] = 'https://arunika-fmcg-erp-system-seven.vercel.app';
$_SERVER['APP_URL'] = 'https://arunika-fmcg-erp-system-seven.vercel.app';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_SCHEME'] = 'https';
$_SERVER['HTTP_HOST'] = 'arunika-fmcg-erp-system-seven.vercel.app';

foreach ([
    __DIR__ . '/../bootstrap/cache/packages.php',
    __DIR__ . '/../bootstrap/cache/services.php',
] as $cacheFile) {
    @unlink($cacheFile);
}

$storagePath = '/tmp/storage';
$bootstrapPath = '/tmp/bootstrap';
foreach ([
    $storagePath,
    $storagePath . '/framework',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/sessions',
    $storagePath . '/framework/views',
    $storagePath . '/logs',
    $storagePath . '/app',
    $bootstrapPath,
    $bootstrapPath . '/cache',
] as $directory) {
    if (!is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

try {
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';

    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($bootstrapPath);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'error' => get_class($exception),
        'message' => $exception->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
}
