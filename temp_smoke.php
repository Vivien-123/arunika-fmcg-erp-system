<?php
/**
 * In-process smoke crawl: dispatch every parameterless GET route as every
 * seeded user through the HTTP kernel and record exceptions / 5xx.
 * Temp harness — delete after use.
 */
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$out = __DIR__ . '/temp_smoke_result.txt';
file_put_contents($out, "starting\n");

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$routes = collect($app['router']->getRoutes()->getRoutes())
    ->filter(fn($r) => in_array('GET', $r->methods(), true))
    ->map(fn($r) => trim($r->uri(), '/'))
    ->reject(fn($u) => $u === '' || str_contains($u, '{'))
    ->unique()->values()->all();

$users    = \App\Models\User::orderBy('role')->get();
$failures = [];
$checked  = 0;
$lines    = [];
$lines[]  = 'Users: ' . $users->count() . '  Routes: ' . count($routes);

foreach ($users as $user) {
    \Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
    $app['auth']->shouldUse('web');

    foreach ($routes as $uri) {
        if ($uri === 'login' || $uri === 'logout') {
            continue;
        }

        $request = Illuminate\Http\Request::create('/' . $uri, 'GET');
        $checked++;

        try {
            $response = $httpKernel->handle($request);
            $status   = $response->getStatusCode();
            $body     = (string) $response->getContent();
            $isError  = $status >= 500
                || str_contains($body, 'Server Error')
                || str_contains($body, 'Whoops, looks like something went wrong');
            $detail   = '';
        } catch (Throwable $e) {
            $isError = true;
            $status  = 'EXC';
            $detail  = get_class($e) . ': ' . $e->getMessage()
                . ' @ ' . basename($e->getFile()) . ':' . $e->getLine();
        }

        if ($isError) {
            $failures[] = sprintf('%-14s %-38s %-4s %s', $user->username, $uri, $status, substr($detail, 0, 200));
            file_put_contents($out, end($failures) . "\n", FILE_APPEND);
        }
    }
}

$lines[] = 'Requests: ' . $checked;
$lines[] = $failures
    ? count($failures) . " PROBLEM(S):\n  - " . implode("\n  - ", $failures)
    : 'ALL GREEN — no 5xx / exceptions.';

file_put_contents($out, implode("\n", $lines) . "\n", FILE_APPEND);
echo implode("\n", $lines) . "\n";
