<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Application\Error\ErrorResponse;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ErrorResponseTest extends TestCase
{
    public function testMapsKnownDomainErrors(): void
    {
        $response = ErrorResponse::fromException(new ConflictException('Car is already booked.'), 'req-1');

        self::assertSame(409, $response['status']);
        self::assertSame('conflict', $response['error']['code']);
        self::assertSame('req-1', $response['meta']['request_id']);
    }

    public function testMapsUnexpectedErrorsWithoutLeakingInternalMessages(): void
    {
        $response = ErrorResponse::fromException(new RuntimeException('SQLSTATE password=secret'), 'req-2');

        self::assertSame(500, $response['status']);
        self::assertSame('internal_error', $response['error']['code']);
        self::assertSame('An unexpected error occurred.', $response['error']['message']);
        self::assertStringNotContainsString('SQLSTATE', $response['error']['message']);
    }

    public function testExposesValidationAndNotFoundStatuses(): void
    {
        self::assertSame(422, ErrorResponse::fromException(new ValidationException('Invalid input.'), 'a')['status']);
        self::assertSame(404, ErrorResponse::fromException(new NotFoundException('Missing resource.'), 'b')['status']);
    }
}
