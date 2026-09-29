<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\Command\UpdateUser;
use App\Modules\Users\Application\View\UserView;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\UserId;

final readonly class UpdateUserHandler
{
    public function __construct(private UserRepository $users) {}

    public function __invoke(UpdateUser $command): UserView
    {
        $user = $this->users->find(UserId::fromInt($command->userId));
        if ($user === null) {
            throw new NotFoundException('User was not found.');
        }

        $user->updateProfile($command->name, $command->contactNumber, DriverLicense::fromString($command->driversLicense));

        return UserView::fromEntity($this->users->save($user));
    }
}
