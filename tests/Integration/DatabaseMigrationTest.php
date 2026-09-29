<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

final class DatabaseMigrationTest extends TestCase
{
    public function testMigrationsDeclareTheModularMonolithSchema(): void
    {
        $directory = dirname(__DIR__, 2) . '/database/migrations';
        $files = glob($directory . '/*.sql');

        self::assertNotFalse($files);
        sort($files);
        self::assertSame(['001_initial_schema.sql', '002_performance_indexes.sql'], array_map('basename', $files));

        $schema = file_get_contents($files[0]);
        self::assertIsString($schema);
        foreach (['accountstable', 'userstable', 'carstable', 'bookingtable', 'billingtable', 'token_expires_at'] as $tableOrColumn) {
            self::assertStringContainsString($tableOrColumn, $schema);
        }
    }
}
