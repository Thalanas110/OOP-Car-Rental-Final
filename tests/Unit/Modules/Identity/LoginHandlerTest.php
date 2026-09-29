<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Application\Command\Login;
use App\Modules\Identity\Application\Command\LoginHandler;
use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Application\Security\Token;
use App\Modules\Identity\Application\Security\TokenIssuer;
use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoginHandlerTest extends TestCase
{
    public function testAuthenticatesValidCredentialsAndIssuesToken(): void
    {
        $account = Account::register(UserId::fromInt(7), Email::fromString('customer@example.com'), PasswordHash::fromString('stored'));
        $repository = new class ($account) implements AccountRepository {
            public function __construct(private Account $account) {}
            public function findByEmail(Email $email): ?Account
            {
                return $this->account;
            }
            public function findByUserId(UserId $userId): ?Account
            {
                return $this->account;
            }
            public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId
            {
                return null;
            }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void {}
        };
        $hasher = new class implements PasswordHasher {
            public function hash(string $plainText): string
            {
                return 'stored';
            }
            public function verify(string $plainText, string $hash): bool
            {
                return $plainText === 'secret' && $hash === 'stored';
            }
        };
        $issuer = new class implements TokenIssuer {
            public function issue(UserId $userId, Email $email): Token
            {
                return new Token('token-123', new DateTimeImmutable('2026-10-01 UTC'));
            }
        };

        $view = (new LoginHandler($repository, $hasher, $issuer))(new Login('customer@example.com', 'secret'));

        self::assertSame(7, $view->userId);
        self::assertSame('token-123', $view->token);
    }

    public function testRejectsUnknownOrIncorrectCredentialsWithSameException(): void
    {
        $repository = new class implements AccountRepository {
            public function findByEmail(Email $email): ?Account
            {
                return null;
            }
            public function findByUserId(UserId $userId): ?Account
            {
                return null;
            }
            public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId
            {
                return null;
            }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void {}
        };
        $hasher = new class implements PasswordHasher {
            public function hash(string $plainText): string
            {
                return 'hash';
            }
            public function verify(string $plainText, string $hash): bool
            {
                return false;
            }
        };
        $issuer = new class implements TokenIssuer {
            public function issue(UserId $userId, Email $email): Token
            {
                return new Token('token', new DateTimeImmutable());
            }
        };

        try {
            (new LoginHandler($repository, $hasher, $issuer))(new Login('missing@example.com', 'wrong'));
            self::fail('Expected invalid credentials exception.');
        } catch (DomainException $exception) {
            self::assertSame(401, $exception->statusCode());
        }
    }
}
