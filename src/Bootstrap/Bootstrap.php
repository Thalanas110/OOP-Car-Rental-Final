<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\Config;
use App\Modules\Billing\Application\Port\VipAccountPort;
use App\Modules\Billing\Domain\Repository\PaymentRepository;
use App\Modules\Billing\Domain\Service\VipPolicy;
use App\Modules\Bookings\Application\Command\CreateBookingHandler;
use App\Modules\Bookings\Domain\Repository\BookingRepository;
use App\Modules\Bookings\Domain\Service\BookingPolicy;
use App\Modules\Fleet\Application\Port\CarRateReader;
use App\Modules\Fleet\Domain\Repository\CarRepository;
use App\Modules\Identity\Application\Port\VipStatusReader;
use App\Modules\Identity\Application\Security\NativePasswordHasher;
use App\Modules\Identity\Application\Security\OpaqueTokenIssuer;
use App\Modules\Identity\Application\Security\PasswordHasher;
use App\Modules\Identity\Application\Security\TokenIssuer;
use App\Modules\Identity\Application\Security\TokenVerifier;
use App\Modules\Identity\Domain\Repository\AccountRepository;
use App\Modules\Users\Application\Port\UserExistenceReader;
use App\Modules\Users\Domain\Repository\UserRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Service\Clock;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Infrastructure\Persistence\PdoConnectionFactory;
use App\Shared\Infrastructure\Persistence\PdoTransactionManager;
use App\Shared\Infrastructure\Time\SystemClock;
use App\Shared\Presentation\Http\Response\JsonResponder;
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
                return new PdoConnectionFactory()->create($config);
            },
            JsonResponder::class => static fn (): JsonResponder => new JsonResponder(),
            Clock::class => static fn (): Clock => new SystemClock(),
            TransactionManager::class => static fn (ContainerInterface $container): TransactionManager => new PdoTransactionManager($container->get(PDO::class)),
            AccountRepository::class => static fn (ContainerInterface $container): AccountRepository => new \App\Modules\Identity\Infrastructure\Persistence\PdoAccountRepository($container->get(PDO::class)),
            PasswordHasher::class => static fn (): PasswordHasher => new NativePasswordHasher(),
            TokenIssuer::class => static fn (ContainerInterface $container): TokenIssuer => $container->get(OpaqueTokenIssuer::class),
            TokenVerifier::class => static fn (ContainerInterface $container): TokenVerifier => $container->get(OpaqueTokenIssuer::class),
            OpaqueTokenIssuer::class => static fn (ContainerInterface $container): OpaqueTokenIssuer => new OpaqueTokenIssuer($container->get(AccountRepository::class), $container->get(Clock::class), $container->get(Config::class)),
            UserRepository::class => static fn (ContainerInterface $container): UserRepository => new \App\Modules\Users\Infrastructure\Persistence\PdoUserRepository($container->get(PDO::class)),
            CarRepository::class => static fn (ContainerInterface $container): CarRepository => new \App\Modules\Fleet\Infrastructure\Persistence\PdoCarRepository($container->get(PDO::class), Money::fromDecimal($container->get(Config::class)->luxuryCarDailyRate())),
            UserExistenceReader::class => static fn (ContainerInterface $container): UserExistenceReader => new \App\Modules\Users\Infrastructure\Adapter\PdoUserExistenceReader($container->get(PDO::class)),
            CarRateReader::class => static fn (ContainerInterface $container): CarRateReader => new \App\Modules\Fleet\Infrastructure\Adapter\PdoCarRateReader($container->get(PDO::class)),
            VipStatusReader::class => static fn (ContainerInterface $container): VipStatusReader => new \App\Modules\Identity\Infrastructure\Adapter\PdoVipStatusReader($container->get(PDO::class), Money::fromDecimal($container->get(Config::class)->vipPointsThreshold())),
            BookingRepository::class => static fn (ContainerInterface $container): BookingRepository => new \App\Modules\Bookings\Infrastructure\Persistence\PdoBookingRepository($container->get(PDO::class)),
            BookingPolicy::class => static fn (): BookingPolicy => new BookingPolicy(),
            CreateBookingHandler::class => static fn (ContainerInterface $container): CreateBookingHandler => new CreateBookingHandler($container->get(BookingRepository::class), $container->get(UserExistenceReader::class), $container->get(CarRateReader::class), $container->get(VipStatusReader::class), $container->get(BookingPolicy::class), $container->get(TransactionManager::class), Money::fromDecimal($container->get(Config::class)->luxuryCarDailyRate())),
            PaymentRepository::class => static fn (ContainerInterface $container): PaymentRepository => new \App\Modules\Billing\Infrastructure\Persistence\PdoPaymentRepository($container->get(PDO::class)),
            VipAccountPort::class => static fn (ContainerInterface $container): VipAccountPort => new \App\Modules\Billing\Infrastructure\Persistence\PdoVipAccountAdapter($container->get(PDO::class)),
            VipPolicy::class => static fn (ContainerInterface $container): VipPolicy => new VipPolicy(Money::fromDecimal($container->get(Config::class)->vipPointsThreshold())),
        ]);

        return $builder->build();
    }

    /** @param array<string, string> $environment */
    public static function createApp(array $environment = []): App
    {
        $container = self::createContainer($environment);
        $app = AppFactory::createFromContainer($container);
        RouteRegistrar::register($app, $container);

        return $app;
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
