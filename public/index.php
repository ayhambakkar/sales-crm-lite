<?php

declare(strict_types=1);

/**
 * Sales CRM Lite — Front Controller
 *
 * Single entry point for all HTTP requests.
 * The web server (Apache/Nginx) routes every request here.
 *
 * Request lifecycle:
 *   Browser → index.php → Config → Session → Router → Controller → View
 */

// ---------------------------------------------------------------------------
// 1. Application Root
// ---------------------------------------------------------------------------
define('APP_ROOT', dirname(__DIR__));

// ---------------------------------------------------------------------------
// 2. Autoloader
// ---------------------------------------------------------------------------
$autoloader = APP_ROOT . '/vendor/autoload.php';

if (! file_exists($autoloader)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Dependencies not installed. Run: composer install' . PHP_EOL;
    exit(1);
}

require_once $autoloader;

// ---------------------------------------------------------------------------
// 3. Bootstrap placeholder
//    The following steps will be implemented in Milestone 1.1:
//
//    - Load .env configuration
//    - Set security HTTP headers
//    - Initialize session with hardened settings
//    - Instantiate Router and dispatch the request
// ---------------------------------------------------------------------------

// TODO: \App\Core\Config::load(APP_ROOT . '/.env');
// TODO: \App\Core\Session::start();
// TODO: (new \App\Core\Router())->dispatch();

// ---------------------------------------------------------------------------
// Temporary: confirm bootstrap is reachable
// ---------------------------------------------------------------------------
http_response_code(200);
header('Content-Type: text/plain; charset=UTF-8');

echo 'Sales CRM Lite — Bootstrap OK' . PHP_EOL;
echo 'PHP ' . PHP_VERSION . PHP_EOL;
echo 'Environment: ' . (getenv('APP_ENV') ?: 'not set') . PHP_EOL;
