<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class UnauthorizedException extends DomainException
{
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct($message, 401, 'unauthorized');
    }
}
