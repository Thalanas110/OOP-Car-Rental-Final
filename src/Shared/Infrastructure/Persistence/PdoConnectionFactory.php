<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Config\Config;
use PDO;

final class PdoConnectionFactory
{
    public function create(Config $config): PDO
    {
        $pdo = new PDO($config->databaseDsn(), $config->databaseUser, $config->databasePassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }
}
