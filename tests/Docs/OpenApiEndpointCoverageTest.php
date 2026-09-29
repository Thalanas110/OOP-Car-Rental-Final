<?php

declare(strict_types=1);

namespace Tests\Docs;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiEndpointCoverageTest extends TestCase
{
    public function testDocumentListsEveryPreservedEndpoint(): void
    {
        /** @var array<string, mixed> $document */
        $document = Yaml::parseFile(dirname(__DIR__, 2) . '/docs/openapi/openapi.yaml');
        $paths = $document['paths'];
        $expected = [
            'POST /login', 'POST /useraccount', 'PATCH /useraccount',
            'GET /users', 'POST /users', 'GET /users/{id}', 'PATCH /users/{id}', 'DELETE /users/{id}', 'DELETE /destroyusers/{id}',
            'GET /cars', 'POST /cars', 'GET /cars/{id}', 'PATCH /cars/{id}', 'DELETE /cars/{id}', 'GET /carchecking', 'DELETE /destroycars/{id}',
            'POST /carbooking', 'POST /billing',
        ];

        $actual = [];
        foreach ($paths as $path => $operations) {
            foreach (array_keys($operations) as $method) {
                $actual[] = strtoupper($method) . ' ' . $path;
            }
        }

        foreach ($expected as $signature) {
            self::assertContains($signature, $actual);
        }
    }
}
