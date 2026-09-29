<?php

declare(strict_types=1);

namespace App\Modules\Users\Infrastructure\Persistence;

use App\Modules\Users\Domain\Entity\User;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Modules\Users\Domain\ValueObject\DriverLicense;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoUserRepository implements UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(UserId $id): ?User
    {
        $statement = $this->pdo->prepare('SELECT userID, name, contact_no, drivers_license, isdeleted FROM userstable WHERE userID = :id LIMIT 1');
        $statement->execute(['id' => $id->toInt()]);

        return $this->hydrate($statement->fetch());
    }

    public function listActive(): array
    {
        $rows = $this->pdo->query('SELECT userID, name, contact_no, drivers_license, isdeleted FROM userstable WHERE isdeleted = 0 ORDER BY userID')->fetchAll();

        return array_values(array_filter(array_map(fn (array $row): ?User => $this->hydrate($row), $rows)));
    }

    public function existsByDriverLicense(DriverLicense $license): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM userstable WHERE drivers_license = :license LIMIT 1');
        $statement->execute(['license' => $license->toString()]);

        return $statement->fetchColumn() !== false;
    }

    public function save(User $user): User
    {
        if ($user->id() === null) {
            $statement = $this->pdo->prepare('INSERT INTO userstable (name, contact_no, drivers_license, isdeleted) VALUES (:name, :contact_no, :license, :isdeleted)');
            $statement->execute([
                'name' => $user->name(),
                'contact_no' => $user->contactNumber(),
                'license' => $user->driversLicense()->toString(),
                'isdeleted' => $user->isArchived() ? 1 : 0,
            ]);
            $user->assignId(UserId::fromInt((int) $this->pdo->lastInsertId()));

            return $user;
        }

        $statement = $this->pdo->prepare('UPDATE userstable SET name = :name, contact_no = :contact_no, drivers_license = :license, isdeleted = :isdeleted WHERE userID = :id');
        $statement->execute([
            'name' => $user->name(),
            'contact_no' => $user->contactNumber(),
            'license' => $user->driversLicense()->toString(),
            'isdeleted' => $user->isArchived() ? 1 : 0,
            'id' => $user->id()->toInt(),
        ]);

        return $user;
    }

    public function archive(UserId $id): void
    {
        $statement = $this->pdo->prepare('UPDATE userstable SET isdeleted = 1 WHERE userID = :id');
        $statement->execute(['id' => $id->toInt()]);
    }

    public function delete(UserId $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM userstable WHERE userID = :id');
        $statement->execute(['id' => $id->toInt()]);
    }

    /** @param array<string, mixed>|false $row */
    private function hydrate(array|false $row): ?User
    {
        if ($row === false) {
            return null;
        }

        return User::reconstitute(
            UserId::fromInt((int) $row['userID']),
            (string) $row['name'],
            (string) $row['contact_no'],
            DriverLicense::fromString((string) $row['drivers_license']),
            (bool) $row['isdeleted'],
        );
    }
}
