<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Application\Command\ChangePassword;
use App\Modules\Identity\Application\Command\ChangePasswordHandler;
use App\Modules\Identity\Application\Port\PasswordUpdater;
use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Shared\Domain\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class ChangePasswordHandlerTest extends TestCase
{
    public function testHashesAndPersistsTheAuthenticatedUsersNewPassword(): void
    {
        $updater = new class implements PasswordUpdater {
            public ?UserId $userId = null;
            public ?PasswordHash $password = null;

            public function update(UserId $userId, PasswordHash $password): void
            {
                $this->userId = $userId;
                $this->password = $password;
            }
        };
        $hasher = new class implements PasswordHasher {
            public function hash(string $plainText): string { return 'hashed:' . $plainText; }
            public function verify(string $plainText, string $hash): bool { return false; }
        };

        $view = (new ChangePasswordHandler($updater, $hasher))(new ChangePassword(UserId::fromInt(7), 'new-secret'));

        self::assertSame(7, $updater->userId?->toInt());
        self::assertSame('hashed:new-secret', $updater->password?->toString());
        self::assertSame('Password updated successfully.', $view->message);
    }
}
