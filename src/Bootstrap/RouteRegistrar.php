<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Modules\Billing\Presentation\Http\BillingController;
use App\Modules\Billing\Presentation\Http\BillingRoutes;
use App\Modules\Bookings\Presentation\Http\BookingController;
use App\Modules\Bookings\Presentation\Http\BookingRoutes;
use App\Modules\Fleet\Presentation\Http\FleetController;
use App\Modules\Fleet\Presentation\Http\FleetRoutes;
use App\Modules\Identity\Presentation\Http\IdentityController;
use App\Modules\Identity\Presentation\Http\IdentityRoutes;
use App\Modules\Users\Presentation\Http\UserController;
use App\Modules\Users\Presentation\Http\UserRoutes;
use App\Shared\Presentation\Http\Middleware\ExceptionMiddleware;
use App\Shared\Presentation\Http\Middleware\JsonBodyMiddleware;
use App\Shared\Presentation\Http\Middleware\RequestIdMiddleware;
use App\Shared\Presentation\Http\LegacyRouteAdapter;
use Psr\Container\ContainerInterface;
use Slim\App;

final class RouteRegistrar
{
    public static function register(App $app, ContainerInterface $container): void
    {
        IdentityRoutes::register($app, $container->get(IdentityController::class));
        UserRoutes::register($app, $container->get(UserController::class));
        FleetRoutes::register($app, $container->get(FleetController::class));
        BookingRoutes::register($app, $container->get(BookingController::class));
        BillingRoutes::register($app, $container->get(BillingController::class));
        $app->add(new JsonBodyMiddleware());
        $app->add(new LegacyRouteAdapter());
        $app->add(new RequestIdMiddleware());
        $app->add(new ExceptionMiddleware($container->get(\App\Shared\Presentation\Http\Response\JsonResponder::class)));
    }
}
