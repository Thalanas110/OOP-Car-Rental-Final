<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Bootstrap\Bootstrap;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class DocsRoutesTest extends TestCase
{
    public function testSwaggerUiAndOpenApiDocumentAreServed(): void
    {
        $app = Bootstrap::createApp([
            'APP_ENV' => 'testing', 'DB_DSN' => 'sqlite::memory:', 'DB_USER' => '', 'DB_PASSWORD' => '',
            'JWT_SECRET' => 'test-secret', 'TOKEN_TTL' => '3600', 'LUXURY_CAR_DAILY_RATE' => '200000.00', 'VIP_POINTS_THRESHOLD' => '500000.00',
        ]);

        $ui = $app->handle(new ServerRequest('GET', '/docs'));
        $spec = $app->handle(new ServerRequest('GET', '/docs/openapi.yaml'));

        self::assertSame(200, $ui->getStatusCode());
        self::assertStringContainsString('SwaggerUIBundle', (string) $ui->getBody());
        self::assertSame('text/html; charset=utf-8', $ui->getHeaderLine('Content-Type'));
        self::assertSame(200, $spec->getStatusCode());
        self::assertStringContainsString('openapi: 3.1.0', (string) $spec->getBody());
        self::assertSame('application/yaml; charset=utf-8', $spec->getHeaderLine('Content-Type'));
    }
}
