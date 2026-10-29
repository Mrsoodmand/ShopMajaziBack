<?php

/*
 * Local development router for `php -S 127.0.0.1:8000 dev-server.php`.
 * public/index.php loads Laravel from ../Update-1402-07-01 (the production folder layout),
 * so locally we boot it from this folder instead. Not used in production.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Local overrides (.env.dev): set before Laravel reads .env, which never overwrites existing variables.
foreach (@file(__DIR__ . '/.env.dev', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if ($line[0] === '#' || !str_contains($line, '=')) {
        continue;
    }
    [$key, $value] = array_map('trim', explode('=', $line, 2));
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture())->send();
$kernel->terminate($request, $response);
