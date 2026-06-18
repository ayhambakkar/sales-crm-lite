<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use App\Models\UserModel;
use PHPUnit\Framework\TestCase;

class FoundationSmokeTest extends TestCase
{
    public function testCoreClassesAutoload(): void
    {
        $this->assertTrue(class_exists(Router::class));
        $this->assertTrue(class_exists(UserModel::class));
    }

    public function testEscapeHelperIsLoaded(): void
    {
        $this->assertTrue(function_exists('e'));
        $this->assertSame('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', e("<script>alert('x')</script>"));
    }
}
