<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Users;

use App\Modules\Users\Domain\Entity\User;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreatesUpdatesAndArchivesUser(): void
    {
        $user = User::register('Ada Lovelace', '09123456789', DriverLicense::fromString(' DL-123 '));

        $user->updateProfile('Ada Byron', '09111111111', DriverLicense::fromString('DL-456'));
        $user->archive();

        self::assertSame('Ada Byron', $user->name());
        self::assertSame('DL-456', $user->driversLicense()->toString());
        self::assertTrue($user->isArchived());
    }

    public function testRejectsEmptyLicense(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DriverLicense::fromString(' ');
    }
}
