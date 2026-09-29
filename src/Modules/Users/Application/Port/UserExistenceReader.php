<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Port;

use App\Shared\Domain\ValueObject\UserId;

interface UserExistenceReader
{
    public function exists(UserId $userId): bool;
}
