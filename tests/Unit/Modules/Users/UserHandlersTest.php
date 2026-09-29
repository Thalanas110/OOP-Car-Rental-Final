<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Users;

use App\Modules\Users\Application\Command\ArchiveUser;
use App\Modules\Users\Application\Handler\ArchiveUserHandler;
use App\Modules\Users\Application\Command\CreateUser;
use App\Modules\Users\Application\Handler\CreateUserHandler;
use App\Modules\Users\Application\Command\UpdateUser;
use App\Modules\Users\Application\Handler\UpdateUserHandler;
use App\Modules\Users\Application\Query\GetUser;
use App\Modules\Users\Application\Handler\GetUserHandler;
use App\Modules\Users\Application\Handler\ListUsersHandler;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Modules\Users\Infrastructure\Persistence\PdoUserRepository;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class UserHandlersTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE userstable (userID INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, contact_no TEXT NOT NULL, isdeleted INTEGER NOT NULL DEFAULT 0, drivers_license TEXT NOT NULL UNIQUE)');
        $this->repository = new PdoUserRepository($pdo);
    }

    public function testCreatesRejectsDuplicateListsUpdatesAndArchives(): void
    {
        $create = new CreateUserHandler($this->repository);
        $view = $create(new CreateUser('Ada Lovelace', '09123456789', 'DL-1'));
        self::assertSame('Ada Lovelace', $view->name);

        $this->expectException(ConflictException::class);
        $create(new CreateUser('Another', '09111111111', 'dl-1'));
    }

    public function testListsUpdatesAndArchivesExistingUser(): void
    {
        $created = (new CreateUserHandler($this->repository))(new CreateUser('Ada Lovelace', '09123456789', 'DL-1'));
        $id = UserId::fromInt($created->userId);

        self::assertCount(1, (new ListUsersHandler($this->repository))());
        $updated = (new UpdateUserHandler($this->repository))(new UpdateUser($id->toInt(), 'Ada Byron', '09111111111', 'DL-2'));
        self::assertSame('Ada Byron', $updated->name);
        self::assertSame('Ada Byron', (new GetUserHandler($this->repository))(new GetUser($id->toInt()))->name);

        (new ArchiveUserHandler($this->repository))(new ArchiveUser($id->toInt()));
        self::assertCount(0, (new ListUsersHandler($this->repository))());
    }
}
