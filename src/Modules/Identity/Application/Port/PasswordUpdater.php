<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Port;

use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;

interface PasswordUpdater
{
    public function update(UserId $userId, PasswordHash $password): void;
}
