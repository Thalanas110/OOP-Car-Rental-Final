<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Domain\Entity\Account;
use App\Modules\Identity\Domain\ValueObject\Email;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AccountTest extends TestCase
{
    public function testRegistersAccountWithNormalizedEmail(): void
    {
        $account = Account::register(
            UserId::fromInt(7),
            Email::fromString('  CUSTOMER@Example.COM '),
            PasswordHash::fromString('$2y$10$hash'),
        );

        self::assertSame(7, $account->userId()->toInt());
        self::assertSame('customer@example.com', $account->email()->toString());
        self::assertSame('$2y$10$hash', $account->passwordHash()->toString());
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::fromString('not-an-email');
    }
}
