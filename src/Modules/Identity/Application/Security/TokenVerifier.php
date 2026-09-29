<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Security;

use App\Shared\Domain\ValueObject\UserId;

interface TokenVerifier
{
    public function verify(string $token): ?UserId;
}
