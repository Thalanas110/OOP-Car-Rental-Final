<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Modules\Identity\Domain\ValueObject\PasswordHash;
use App\Modules\Identity\Infrastructure\Persistence\PdoPasswordUpdater;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class PdoPasswordUpdaterTest extends TestCase
{
    public function testUpdatesPasswordForAccount(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE accountstable (userID INTEGER PRIMARY KEY, user_password TEXT NOT NULL)');
        $pdo->exec("INSERT INTO accountstable (userID, user_password) VALUES (7, 'old')");

        (new PdoPasswordUpdater($pdo))->update(UserId::fromInt(7), PasswordHash::fromString('new-hash'));

        self::assertSame('new-hash', $pdo->query('SELECT user_password FROM accountstable WHERE userID = 7')->fetchColumn());
    }
}
