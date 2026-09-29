<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

final readonly class Login
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
