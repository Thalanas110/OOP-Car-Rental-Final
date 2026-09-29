<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\Transaction\TransactionManager;
use PDO;
use Throwable;

final readonly class PdoTransactionManager implements TransactionManager
{
    public function __construct(private PDO $pdo)
    {
    }

    public function run(callable $operation): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $result = $operation();
            $this->pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
