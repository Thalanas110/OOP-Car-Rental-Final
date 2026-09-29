<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence;

use App\Modules\Identity\Application\Port\PasswordUpdater;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoPasswordUpdater implements PasswordUpdater
{
    public function __construct(private PDO $pdo) {}

    public function update(UserId $userId, PasswordHash $password): void
    {
        $statement = $this->pdo->prepare('UPDATE accountstable SET user_password = :password WHERE userID = :user_id');
        $statement->execute(['password' => $password->toString(), 'user_id' => $userId->toInt()]);
    }
}
