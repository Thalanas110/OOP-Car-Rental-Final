<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\Command\CreateUser;
use App\Modules\Users\Application\View\UserView;
use App\Modules\Users\Domain\Entity\User;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Shared\Domain\Exception\ConflictException;

final readonly class CreateUserHandler
{
    public function __construct(private UserRepository $users) {}

    public function __invoke(CreateUser $command): UserView
    {
        $license = DriverLicense::fromString($command->driversLicense);
        if ($this->users->existsByDriverLicense($license)) {
            throw new ConflictException('Driver license is already associated with another user.');
        }

        return UserView::fromEntity($this->users->save(User::register($command->name, $command->contactNumber, $license)));
    }
}
