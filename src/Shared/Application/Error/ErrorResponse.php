<?php

declare(strict_types=1);

namespace App\Shared\Application\Error;

use App\Shared\Domain\Exception\DomainException;
use Throwable;

final class ErrorResponse
{
    /** @return array{status: int, error: array{code: string, message: string}, meta: array{request_id: string}} */
    public static function fromException(Throwable $exception, string $requestId): array
    {
        if ($exception instanceof DomainException) {
            return [
                'status' => $exception->statusCode(),
                'error' => ['code' => $exception->errorCode(), 'message' => $exception->getMessage()],
                'meta' => ['request_id' => $requestId],
            ];
        }

        return [
            'status' => 500,
            'error' => ['code' => 'internal_error', 'message' => 'An unexpected error occurred.'],
            'meta' => ['request_id' => $requestId],
        ];
    }
}
