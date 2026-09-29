<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Application\Command\RegisterAccount;
use App\Modules\Identity\Application\Command\RegisterAccountHandler;
use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class RegisterAccountHandlerTest extends TestCase
{
    public function testHashesAndStoresNewAccount(): void
    {
        $repository = new class implements AccountRepository {
            public ?Account $saved = null;
            public function findByEmail(Email $email): ?Account
            {
                return null;
            }
            public function findByUserId(UserId $userId): ?Account
            {
                return null;
            }
            public function findUserIdByToken(string $token, \DateTimeImmutable $now): ?UserId
            {
                return null;
            }
            public function save(Account $account): void
            {
                $this->saved = $account;
            }
            public function replaceToken(UserId $userId, string $token, \DateTimeImmutable $expiresAt): void {}
        };
        $hasher = new class implements PasswordHasher {
            public function hash(string $plainText): string
            {
                return 'hashed:' . $plainText;
            }
            public function verify(string $plainText, string $hash): bool
            {
                return false;
            }
        };
        $handler = new RegisterAccountHandler($repository, $hasher);

        $view = $handler(new RegisterAccount(7, ' CUSTOMER@example.com ', 'secret'));

        self::assertSame(7, $view->userId);
        self::assertSame('customer@example.com', $view->email);
        self::assertNotNull($repository->saved);
        self::assertSame('hashed:secret', $repository->saved->passwordHash()->toString());
    }

    public function testRejectsDuplicateEmail(): void
    {
        $existing = Account::register(UserId::fromInt(7), Email::fromString('customer@example.com'), \App\Modules\Identity\Domain\ValueObject\PasswordHash::fromString('hash'));
        $repository = new class ($existing) implements AccountRepository {
            public function __construct(private Account $existing) {}
            public function findByEmail(Email $email): ?Account
            {
                return $this->existing;
            }
            public function findByUserId(UserId $userId): ?Account
            {
                return null;
            }
            public function findUserIdByToken(string $token, \DateTimeImmutable $now): ?UserId
            {
                return null;
            }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, \DateTimeImmutable $expiresAt): void {}
        };
        $hasher = new class implements PasswordHasher {
            public function hash(string $plainText): string
            {
                return $plainText;
            }
            public function verify(string $plainText, string $hash): bool
            {
                return false;
            }
        };

        $this->expectException(ConflictException::class);
        (new RegisterAccountHandler($repository, $hasher))(new RegisterAccount(7, 'customer@example.com', 'secret'));
    }
}
