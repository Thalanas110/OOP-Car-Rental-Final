<?php

declare(strict_types=1);

namespace Tests\Docs;

use PHPUnit\Framework\TestCase;

final class ReadmeAttributionTest extends TestCase
{
    public function testReadmeCreditsOriginalAuthorAndDocumentsSwaggerUi(): void
    {
        $readme = file_get_contents(dirname(__DIR__, 2) . '/README.md');

        self::assertIsString($readme);
        self::assertStringContainsString('@jeraldpangan', $readme);
        self::assertStringContainsString('/docs', $readme);
        self::assertStringContainsString('Swagger UI', $readme);
    }
}
