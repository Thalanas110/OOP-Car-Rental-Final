<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testReadsDatabaseAndTokenSettings(): void
    {
        $config = Config::fromEnvironment([
            'APP_ENV' => 'testing',
            'DB_DSN' => 'sqlite::memory:',
            'DB_USER' => '',
            'DB_PASSWORD' => '',
            'JWT_SECRET' => 'test-secret',
            'TOKEN_TTL' => '3600',
        ]);

        self::assertSame('sqlite::memory:', $config->databaseDsn());
        self::assertSame(3600, $config->tokenTtlSeconds());
    }
}
