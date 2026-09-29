<?php

declare(strict_types=1);

namespace App\Modules\Identity\Domain\Repository;

use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;

interface AccountRepository
{
    public function findByEmail(Email $email): ?Account;

    public function findByUserId(UserId $userId): ?Account;

    public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId;

    public function save(Account $account): void;

    public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void;
}
