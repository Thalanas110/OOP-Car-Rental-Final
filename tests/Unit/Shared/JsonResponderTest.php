<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Application\Error\ErrorResponse;
use App\Shared\Presentation\Http\Response\JsonResponder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonResponderTest extends TestCase
{
    public function testSerializesStableSuccessEnvelope(): void
    {
        $response = (new JsonResponder())->success(['id' => 3], 201, ['request_id' => 'req-1']);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame(['id' => 3], $payload['data']);
        self::assertNull($payload['error']);
        self::assertSame('req-1', $payload['meta']['request_id']);
    }

    public function testSerializesSafeErrorEnvelope(): void
    {
        $error = ErrorResponse::fromException(new RuntimeException('database secret'), 'req-2');
        $response = (new JsonResponder())->error($error);
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(500, $response->getStatusCode());
        self::assertNull($payload['data']);
        self::assertSame('internal_error', $payload['error']['code']);
    }
}
