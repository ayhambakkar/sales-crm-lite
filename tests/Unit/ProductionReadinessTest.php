<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\HealthController;
use App\Core\Session;
use PHPUnit\Framework\TestCase;

class ProductionReadinessTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testProductionConfigKeysExistInEnvExample(): void
    {
        $env = (string) file_get_contents($this->root . '/.env.example');

        foreach ([
            'APP_ENV=',
            'APP_DEBUG=',
            'APP_URL=',
            'DB_DATABASE=',
            'DB_USERNAME=',
            'DB_PASSWORD=',
            'SESSION_NAME=',
            'SESSION_LIFETIME=',
        ] as $key) {
            $this->assertStringContainsString($key, $env, $key);
        }

        $this->assertStringContainsString('APP_ENV=production', $env);
        $this->assertStringContainsString('APP_DEBUG=false', $env);
        $this->assertStringContainsString('storage/logs/app.log', $env);
    }

    public function testHealthRouteAndControllerExist(): void
    {
        $routes = (string) file_get_contents($this->root . '/config/routes.php');

        $this->assertStringContainsString('/health', $routes);
        $this->assertTrue(class_exists(HealthController::class));
        $this->assertTrue(method_exists(HealthController::class, 'show'));
    }

    public function testSecurityHeadersAreConfiguredInFrontController(): void
    {
        $frontController = (string) file_get_contents($this->root . '/public/index.php');

        foreach ([
            'X-Frame-Options: DENY',
            'X-Content-Type-Options: nosniff',
            'Referrer-Policy: strict-origin-when-cross-origin',
            'Content-Security-Policy',
            "default-src 'self'",
            'Strict-Transport-Security',
        ] as $header) {
            $this->assertStringContainsString($header, $frontController, $header);
        }
    }

    public function testSessionCookieSecurityPolicyIsCentralized(): void
    {
        $this->assertTrue(method_exists(Session::class, 'cookieOptions'));

        $session = (string) file_get_contents($this->root . '/src/Core/Session.php');

        $this->assertStringContainsString("'httponly' => true", $session);
        $this->assertStringContainsString("'samesite' => 'Strict'", $session);
        $this->assertStringContainsString("Config::get('APP_ENV') === 'production'", $session);
        $this->assertStringContainsString('session_regenerate_id(true)', $session);
        $this->assertStringContainsString('setcookie(session_name()', $session);
    }
}
