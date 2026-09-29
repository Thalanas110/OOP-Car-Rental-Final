<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Bootstrap\Bootstrap;
use PHPUnit\Framework\TestCase;

final class RouteCompositionTest extends TestCase
{
    public function testComposesEveryPreservedRoute(): void
    {
        $app = Bootstrap::createApp([
            'APP_ENV' => 'testing', 'DB_DSN' => 'sqlite::memory:', 'DB_USER' => '', 'DB_PASSWORD' => '',
            'JWT_SECRET' => 'test-secret', 'TOKEN_TTL' => '3600', 'LUXURY_CAR_DAILY_RATE' => '200000.00', 'VIP_POINTS_THRESHOLD' => '500000.00',
        ]);
        $routes = $app->getRouteCollector()->getRoutes();
        $signatures = array_map(static fn ($route): string => implode(',', $route->getMethods()) . ' ' . $route->getPattern(), $routes);

        self::assertContains('POST /login', $signatures);
        self::assertContains('POST /users', $signatures);
        self::assertContains('GET /cars', $signatures);
        self::assertContains('POST /carbooking', $signatures);
        self::assertContains('POST /billing', $signatures);
        self::assertContains('DELETE /destroyusers/{id}', $signatures);
    }
}
