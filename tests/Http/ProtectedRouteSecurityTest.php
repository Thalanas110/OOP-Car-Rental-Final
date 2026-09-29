<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Bootstrap\Bootstrap;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class ProtectedRouteSecurityTest extends TestCase
{
    public function testUsersEndpointRequiresAuthentication(): void
    {
        $app = Bootstrap::createApp([
            'APP_ENV' => 'testing', 'DB_DSN' => 'sqlite::memory:', 'DB_USER' => '', 'DB_PASSWORD' => '',
            'JWT_SECRET' => 'test-secret', 'TOKEN_TTL' => '3600', 'LUXURY_CAR_DAILY_RATE' => '200000.00', 'VIP_POINTS_THRESHOLD' => '500000.00',
        ]);

        $response = $app->handle(new ServerRequest('GET', '/users'));

        self::assertSame(401, $response->getStatusCode());
    }
}
