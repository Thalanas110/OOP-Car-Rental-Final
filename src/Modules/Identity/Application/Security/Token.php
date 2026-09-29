<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Security;

use DateTimeImmutable;

final readonly class Token
{
    public function __construct(
        public string $value,
        public DateTimeImmutable $expiresAt,
    ) {}
}
