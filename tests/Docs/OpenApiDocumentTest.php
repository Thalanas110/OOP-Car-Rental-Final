<?php

declare(strict_types=1);

namespace Tests\Docs;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiDocumentTest extends TestCase
{
    public function testOpenApiDocumentContainsRequiredComponents(): void
    {
        /** @var array{document: string} $config */
        $config = require dirname(__DIR__, 2) . '/config/openapi.php';
        $path = $config['document'];
        self::assertFileExists($path);
        $document = Yaml::parseFile($path);

        self::assertSame('3.1.0', $document['openapi']);
        self::assertArrayHasKey('bearerAuth', $document['components']['securitySchemes']);
        self::assertArrayHasKey('ErrorResponse', $document['components']['schemas']);
        self::assertArrayHasKey('/login', $document['paths']);
    }
}
