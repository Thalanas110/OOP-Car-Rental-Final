<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

final readonly class RegisterAccount
{
    public function __construct(
        public int $userId,
        public string $email,
        public string $password,
    ) {}
}
