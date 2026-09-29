<?php

declare(strict_types=1);

namespace App\Modules\Identity\Application\Command;

use App\Modules\Identity\Application\Port\PasswordUpdater;
use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Application\View\PasswordChangedView;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;

final readonly class ChangePasswordHandler
{
    public function __construct(private PasswordUpdater $passwords, private PasswordHasher $hasher) {}

    public function __invoke(ChangePassword $command): PasswordChangedView
    {
        $this->passwords->update($command->userId, PasswordHash::fromString($this->hasher->hash($command->password)));

        return new PasswordChangedView('Password updated successfully.');
    }
}
