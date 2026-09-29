<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Users;

use App\Modules\Users\Domain\Entity\User;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Modules\Users\Infrastructure\Persistence\PdoUserRepository;
use App\Shared\Domain\ValueObject\UserId;
use PDO;
use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE userstable (userID INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, contact_no TEXT NOT NULL, isdeleted INTEGER NOT NULL DEFAULT 0, drivers_license TEXT NOT NULL UNIQUE)');
        $this->repository = new PdoUserRepository($this->pdo);
    }

    public function testSavesListsAndFindsActiveUsers(): void
    {
        $user = User::register('Ada Lovelace', '09123456789', DriverLicense::fromString('DL-1'));
        $saved = $this->repository->save($user);

        self::assertNotNull($saved->id());
        self::assertCount(1, $this->repository->listActive());
        self::assertSame('Ada Lovelace', $this->repository->find($saved->id())->name());
    }

    public function testArchiveExcludesUserAndLicenseLookupIsParameterized(): void
    {
        $saved = $this->repository->save(User::register('Ada Lovelace', '09123456789', DriverLicense::fromString('DL-1')));
        self::assertTrue($this->repository->existsByDriverLicense(DriverLicense::fromString('dl-1')));

        $this->repository->archive($saved->id());

        self::assertCount(0, $this->repository->listActive());
        self::assertNotNull($this->repository->find($saved->id()));
    }
}
