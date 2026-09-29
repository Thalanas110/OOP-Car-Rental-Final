<?php

declare(strict_types=1);

namespace App\Modules\Users\Domain\Repository;

use App\Modules\Users\Domain\Entity\User;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Shared\Domain\ValueObject\UserId;

interface UserRepository
{
    public function find(UserId $id): ?User;

    /** @return list<User> */
    public function listActive(): array;

    public function existsByDriverLicense(DriverLicense $license): bool;

    public function save(User $user): User;

    public function archive(UserId $id): void;

    public function delete(UserId $id): void;
}
