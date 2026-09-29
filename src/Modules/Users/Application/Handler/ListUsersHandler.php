<?php

declare(strict_types=1);

namespace App\Modules\Users\Application\Handler;

use App\Modules\Users\Application\View\UserView;
use App\Modules\Users\Domain\Repository\UserRepository;

final readonly class ListUsersHandler
{
    public function __construct(private UserRepository $users) {}

    /** @return list<UserView> */
    public function __invoke(): array
    {
        return array_map(static fn ($user): UserView => UserView::fromEntity($user), $this->users->listActive());
    }
}
