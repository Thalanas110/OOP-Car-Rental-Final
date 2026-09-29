<?php

declare(strict_types=1);

namespace App\Modules\Users\Infrastructure\Adapter;

use App\Modules\Users\Application\Port\UserExistenceReader;
use App\Shared\Domain\ValueObject\UserId;
use PDO;

final readonly class PdoUserExistenceReader implements UserExistenceReader
{
    public function __construct(private PDO $pdo) {}
    public function exists(UserId $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM userstable WHERE userID = :id AND isdeleted = 0 LIMIT 1');
        $statement->execute(['id' => $userId->toInt()]);
        return $statement->fetchColumn() !== false;
    }
}
