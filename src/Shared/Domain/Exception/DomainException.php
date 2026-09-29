<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $status,
        private readonly string $codeName,
    ) {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->codeName;
    }
}
