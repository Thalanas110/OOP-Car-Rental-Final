<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Config\Config;
use App\Modules\Identity\Application\Security\NativePasswordHasher;
use App\Modules\Identity\Application\Security\OpaqueTokenIssuer;
use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;
use App\Shared\Infrastructure\Time\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    public function testHashesAndVerifiesPasswords(): void
    {
        $hasher = new NativePasswordHasher();
        $hash = $hasher->hash('secret');

        self::assertNotSame('secret', $hash);
        self::assertTrue($hasher->verify('secret', $hash));
        self::assertFalse($hasher->verify('wrong', $hash));
    }

    public function testIssuesRandomExpiringOpaqueToken(): void
    {
        $repository = new class implements AccountRepository {
            public ?string $token = null;
            public ?DateTimeImmutable $expiresAt = null;

            public function findByEmail(Email $email): ?Account { return null; }
            public function findByUserId(UserId $userId): ?Account { return null; }
            public function findUserIdByToken(string $token, DateTimeImmutable $now): ?UserId { return null; }
            public function save(Account $account): void {}
            public function replaceToken(UserId $userId, string $token, DateTimeImmutable $expiresAt): void
            {
                $this->token = $token;
                $this->expiresAt = $expiresAt;
            }
        };
        $issuer = new OpaqueTokenIssuer(
            $repository,
            new FrozenClock(new DateTimeImmutable('2026-09-29 12:00:00 UTC')),
            Config::fromEnvironment(['TOKEN_TTL' => '3600']),
        );

        $issued = $issuer->issue(UserId::fromInt(7), Email::fromString('customer@example.com'));

        self::assertSame($issued->value, $repository->token);
        self::assertSame('2026-09-29 13:00:00', $issued->expiresAt->format('Y-m-d H:i:s'));
        self::assertGreaterThan(40, strlen($issued->value));
    }
}
