<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\Config;
use DI\ContainerBuilder;
use PDO;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

final class Bootstrap
{
    /** @param array<string, string> $environment */
    public static function createContainer(array $environment = []): ContainerInterface
    {
        $resolvedEnvironment = $environment !== [] ? $environment : self::runtimeEnvironment();
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            Config::class => Config::fromEnvironment($resolvedEnvironment),
            PDO::class => static function (ContainerInterface $container): PDO {
                /** @var Config $config */
                $config = $container->get(Config::class);
                $pdo = new PDO($config->databaseDsn(), $config->databaseUser, $config->databasePassword);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                return $pdo;
            },
        ]);

        return $builder->build();
    }

    /** @param array<string, string> $environment */
    public static function createApp(array $environment = []): App
    {
        return AppFactory::createFromContainer(self::createContainer($environment));
    }

    /** @return array<string, string> */
    private static function runtimeEnvironment(): array
    {
        $environment = [];
        foreach (['APP_ENV', 'DB_DSN', 'DB_USER', 'DB_PASSWORD', 'JWT_SECRET', 'TOKEN_TTL', 'LUXURY_CAR_DAILY_RATE', 'VIP_POINTS_THRESHOLD'] as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $environment[$key] = $value;
            }
        }

        return $environment;
    }
}
