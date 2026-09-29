<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrap;

use App\Bootstrap\Bootstrap;
use App\Config\Config;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\App;

final class ContainerTest extends TestCase
{
    /** @return array<string, string> */
    private function environment(): array
    {
        return [
            'APP_ENV' => 'testing',
            'DB_DSN' => 'sqlite::memory:',
            'DB_USER' => '',
            'DB_PASSWORD' => '',
            'JWT_SECRET' => 'test-secret',
            'TOKEN_TTL' => '3600',
        ];
    }

    public function testCreatesContainerWithTypedConfigurationAndPdo(): void
    {
        $container = Bootstrap::createContainer($this->environment());

        self::assertInstanceOf(Config::class, $container->get(Config::class));
        self::assertInstanceOf(PDO::class, $container->get(PDO::class));
    }

    public function testCreatesSlimApplicationFromContainer(): void
    {
        self::assertInstanceOf(App::class, Bootstrap::createApp($this->environment()));
    }
}
