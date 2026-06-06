<?php

declare(strict_types=1);

/**
 * Sales CRM Lite — Front Controller
 *
 * Every HTTP request is routed here by the web server.
 * This file bootstraps the application and hands control to the Router.
 *
 * Web server setup:
 *   Apache — see public/.htaccess
 *   Nginx  — try_files $uri $uri/ /index.php?$query_string;
 */

// ---------------------------------------------------------------------------
// 1. Constants
// ---------------------------------------------------------------------------

define('APP_ROOT', dirname(__DIR__));

// ---------------------------------------------------------------------------
// 2. Autoloader
// ---------------------------------------------------------------------------

$autoloader = APP_ROOT . '/vendor/autoload.php';

if (! file_exists($autoloader)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Dependencies not installed. Run: composer install';
    exit(1);
}

require_once $autoloader;

// ---------------------------------------------------------------------------
// 3. Load environment configuration
// ---------------------------------------------------------------------------

\App\Core\Config::load(APP_ROOT . '/.env');

// ---------------------------------------------------------------------------
// 4. Security headers (applied to every response)
// ---------------------------------------------------------------------------

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

if (\App\Core\Config::get('APP_ENV') === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// ---------------------------------------------------------------------------
// 5. Session
// ---------------------------------------------------------------------------

\App\Core\Session::start();

// ---------------------------------------------------------------------------
// 6. Router — load route definitions and dispatch the request
// ---------------------------------------------------------------------------

$router = new \App\Core\Router();

require_once APP_ROOT . '/config/routes.php';

$router->dispatch();
