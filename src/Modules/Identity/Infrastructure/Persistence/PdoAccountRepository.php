<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Persistence;

use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PDO;

final readonly class PdoAccountRepository implements AccountRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(Email $email): ?Account
    {
        $statement = $this->pdo->prepare('SELECT userID, user_email, user_password FROM accountstable WHERE user_email = :email LIMIT 1');
        $statement->execute(['email' => $email->toString()]);

        return $this->hydrate($statement->fetch());
    }

    public function findByUserId(UserId $userId): ?Account
    {
        $statement = $this->pdo->prepare('SELECT userID, user_email, user_password FROM accountstable WHERE userID = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId->toInt()]);

        return $this->hydrate($statement->fetch());
    }

    public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId
    {
        $statement = $this->pdo->prepare('SELECT userID FROM accountstable WHERE token = :token AND (token_expires_at IS NULL OR token_expires_at > :now) LIMIT 1');
        $statement->execute(['token' => $token, 'now' => $now->format('Y-m-d H:i:s')]);
        $userId = $statement->fetchColumn();

        return $userId === false ? null : UserId::fromInt((int) $userId);
    }

    public function save(Account $account): void
    {
        $statement = $this->pdo->prepare('INSERT INTO accountstable (userID, user_email, user_password) VALUES (:user_id, :email, :password)');
        $statement->execute([
            'user_id' => $account->userId()->toInt(),
            'email' => $account->email()->toString(),
            'password' => $account->passwordHash()->toString(),
        ]);
    }

    public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void
    {
        $statement = $this->pdo->prepare('UPDATE accountstable SET token = :token, token_expires_at = :expires_at WHERE userID = :user_id');
        $statement->execute([
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'user_id' => $userId->toInt(),
        ]);
    }

    /** @param array<string, mixed>|false $row */
    private function hydrate(array|false $row): ?Account
    {
        if ($row === false) {
            return null;
        }

        return Account::register(
            UserId::fromInt((int) $row['userID']),
            Email::fromString((string) $row['user_email']),
            PasswordHash::fromString((string) $row['user_password']),
        );
    }
}
