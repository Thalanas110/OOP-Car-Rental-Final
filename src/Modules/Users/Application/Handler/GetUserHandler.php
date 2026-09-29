<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\Query\GetUser;
use App\Modules\Users\Application\View\UserView;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\ValueObject\UserId;

final readonly class GetUserHandler
{
    public function __construct(private UserRepository $users) {}

    public function __invoke(GetUser $query): UserView
    {
        $user = $this->users->find(UserId::fromInt($query->userId));
        if ($user === null) {
            throw new NotFoundException('User was not found.');
        }

        return UserView::fromEntity($user);
    }
}
