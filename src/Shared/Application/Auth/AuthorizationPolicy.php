<?php

declare(strict_types=1);

namespace App\Shared\Application\Auth;

use App\Shared\Domain\Exception\ForbiddenException;
use App\Shared\Domain\Exception\UnauthorizedException;
use App\Shared\Domain\ValueObject\UserId;

final readonly class AuthorizationPolicy
{
    public function __construct(private int $adminUserId = 1) {}

    public function assertAllowed(?UserId $userId, bool $adminOnly = false): void
    {
        if ($userId === null) { throw new UnauthorizedException('Authentication is required.'); }
        if ($adminOnly && $userId->toInt() !== $this->adminUserId) { throw new ForbiddenException('Administrator access is required.'); }
    }
}
