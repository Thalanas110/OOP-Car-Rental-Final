<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Security;

interface PasswordHasher
{
    public function hash(string $plainText): string;

    public function verify(string $plainText, string $hash): bool;
}
