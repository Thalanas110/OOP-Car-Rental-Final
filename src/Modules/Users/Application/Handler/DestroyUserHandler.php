<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\Command\DestroyUser;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Shared\Domain\ValueObject\UserId;

final readonly class DestroyUserHandler
{
    public function __construct(private UserRepository $users) {}
    public function __invoke(DestroyUser $command): void
    {
        $this->users->delete(UserId::fromInt($command->userId));
    }
}
