<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Modules\Identity\Infrastructure\Persistence\PdoAccountRepository;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class PdoAccountRepositoryTest extends TestCase
{
    private PDO $pdo;
    private AccountRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE accountstable (id INTEGER PRIMARY KEY AUTOINCREMENT, userID INTEGER NOT NULL, user_email TEXT NOT NULL, user_password TEXT NOT NULL, token TEXT NULL, token_expires_at TEXT NULL, vip_points NUMERIC NOT NULL DEFAULT 0, vip_access INTEGER NOT NULL DEFAULT 0)');
        $this->repository = new PdoAccountRepository($this->pdo);
    }

    public function testSavesAndFindsAccountByEmail(): void
    {
        $account = Account::register(UserId::fromInt(7), Email::fromString('customer@example.com'), PasswordHash::fromString('hash'));

        $this->repository->save($account);

        $found = $this->repository->findByEmail(Email::fromString('CUSTOMER@example.com'));
        self::assertNotNull($found);
        self::assertSame(7, $found->userId()->toInt());
        self::assertSame('hash', $found->passwordHash()->toString());
    }

    public function testReplacesTokenAndExpiry(): void
    {
        $account = Account::register(UserId::fromInt(7), Email::fromString('customer@example.com'), PasswordHash::fromString('hash'));
        $this->repository->save($account);

        $expiresAt = new \DateTimeImmutable('2026-10-01 00:00:00 UTC');
        $this->repository->replaceToken(UserId::fromInt(7), 'opaque-token', $expiresAt);

        $row = $this->pdo->query('SELECT token, token_expires_at FROM accountstable WHERE userID = 7')->fetch();
        self::assertSame('opaque-token', $row['token']);
        self::assertSame($expiresAt->format('Y-m-d H:i:s'), $row['token_expires_at']);
    }
}
