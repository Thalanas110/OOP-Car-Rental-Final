<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

use App\Shared\Domain\ValueObject\UserId;

final readonly class ChangePassword
{
    public function __construct(public UserId $userId, public string $password) {}
}
