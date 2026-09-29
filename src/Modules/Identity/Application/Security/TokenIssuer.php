<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Security;

use App\Modules\Identity\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\UserId;

interface TokenIssuer
{
    public function issue(UserId $userId, Email $email): Token;
}
