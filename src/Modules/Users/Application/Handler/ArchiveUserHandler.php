<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\Command\ArchiveUser;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Shared\Domain\ValueObject\UserId;

final readonly class ArchiveUserHandler
{
    public function __construct(private UserRepository $users) {}
    public function __invoke(ArchiveUser $command): void
    {
        $this->users->archive(UserId::fromInt($command->userId));
    }
}
