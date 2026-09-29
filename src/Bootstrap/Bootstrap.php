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
use App\Modules\Identity\Application\Port\PasswordUpdater;
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
use App\Shared\Presentation\Http\DocsController;
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
            JsonResponder::class => static fn(): JsonResponder => new JsonResponder(),
            DocsController::class => static fn(): DocsController => new DocsController(dirname(__DIR__, 2) . '/docs/openapi/openapi.yaml', dirname(__DIR__, 2) . '/public/docs/index.html'),
            Clock::class => static fn(): Clock => new SystemClock(),
            TransactionManager::class => static function (ContainerInterface $container): TransactionManager {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new PdoTransactionManager($pdo);
            },
            AccountRepository::class => static function (ContainerInterface $container): AccountRepository {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Identity\Infrastructure\Persistence\PdoAccountRepository($pdo);
            },
            PasswordUpdater::class => static function (ContainerInterface $container): PasswordUpdater {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Identity\Infrastructure\Persistence\PdoPasswordUpdater($pdo);
            },
            PasswordHasher::class => static fn(): PasswordHasher => new NativePasswordHasher(),
            TokenIssuer::class => static function (ContainerInterface $container): TokenIssuer {
                /** @var OpaqueTokenIssuer $issuer */
                $issuer = $container->get(OpaqueTokenIssuer::class);
                return $issuer;
            },
            TokenVerifier::class => static function (ContainerInterface $container): TokenVerifier {
                /** @var OpaqueTokenIssuer $verifier */
                $verifier = $container->get(OpaqueTokenIssuer::class);
                return $verifier;
            },
            OpaqueTokenIssuer::class => static function (ContainerInterface $container): OpaqueTokenIssuer {
                /** @var AccountRepository $accounts */
                $accounts = $container->get(AccountRepository::class);
                /** @var Clock $clock */
                $clock = $container->get(Clock::class);
                /** @var Config $config */
                $config = $container->get(Config::class);
                return new OpaqueTokenIssuer($accounts, $clock, $config);
            },
            UserRepository::class => static function (ContainerInterface $container): UserRepository {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Users\Infrastructure\Persistence\PdoUserRepository($pdo);
            },
            CarRepository::class => static function (ContainerInterface $container): CarRepository {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                /** @var Config $config */
                $config = $container->get(Config::class);
                return new \App\Modules\Fleet\Infrastructure\Persistence\PdoCarRepository($pdo, Money::fromDecimal($config->luxuryCarDailyRate()));
            },
            UserExistenceReader::class => static function (ContainerInterface $container): UserExistenceReader {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Users\Infrastructure\Adapter\PdoUserExistenceReader($pdo);
            },
            CarRateReader::class => static function (ContainerInterface $container): CarRateReader {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Fleet\Infrastructure\Adapter\PdoCarRateReader($pdo);
            },
            VipStatusReader::class => static function (ContainerInterface $container): VipStatusReader {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                /** @var Config $config */
                $config = $container->get(Config::class);
                return new \App\Modules\Identity\Infrastructure\Adapter\PdoVipStatusReader($pdo, Money::fromDecimal($config->vipPointsThreshold()));
            },
            BookingRepository::class => static function (ContainerInterface $container): BookingRepository {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Bookings\Infrastructure\Persistence\PdoBookingRepository($pdo);
            },
            BookingPolicy::class => static fn(): BookingPolicy => new BookingPolicy(),
            CreateBookingHandler::class => static function (ContainerInterface $container): CreateBookingHandler {
                /** @var BookingRepository $bookings */
                $bookings = $container->get(BookingRepository::class);
                /** @var UserExistenceReader $users */
                $users = $container->get(UserExistenceReader::class);
                /** @var CarRateReader $cars */
                $cars = $container->get(CarRateReader::class);
                /** @var VipStatusReader $vipStatus */
                $vipStatus = $container->get(VipStatusReader::class);
                /** @var BookingPolicy $policy */
                $policy = $container->get(BookingPolicy::class);
                /** @var TransactionManager $transactions */
                $transactions = $container->get(TransactionManager::class);
                /** @var Config $config */
                $config = $container->get(Config::class);
                return new CreateBookingHandler($bookings, $users, $cars, $vipStatus, $policy, $transactions, Money::fromDecimal($config->luxuryCarDailyRate()));
            },
            PaymentRepository::class => static function (ContainerInterface $container): PaymentRepository {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Billing\Infrastructure\Persistence\PdoPaymentRepository($pdo);
            },
            VipAccountPort::class => static function (ContainerInterface $container): VipAccountPort {
                /** @var PDO $pdo */
                $pdo = $container->get(PDO::class);
                return new \App\Modules\Billing\Infrastructure\Persistence\PdoVipAccountAdapter($pdo);
            },
            VipPolicy::class => static function (ContainerInterface $container): VipPolicy {
                /** @var Config $config */
                $config = $container->get(Config::class);
                return new VipPolicy(Money::fromDecimal($config->vipPointsThreshold()));
            },
        ]);

        return $builder->build();
    }

    /**
     * @param array<string, string> $environment
     * @return App<ContainerInterface>
     */
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
