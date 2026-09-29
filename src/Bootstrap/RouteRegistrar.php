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
use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Application\Auth\AuthorizationPolicy;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Container\ContainerInterface;
use Slim\App;

final class RouteRegistrar
{
    public static function register(App $app, ContainerInterface $container): void
    {
        $responder = $container->get(JsonResponder::class);
        $authentication = new AuthenticationMiddleware($container->get(\App\Modules\Identity\Application\Security\TokenVerifier::class), $responder);
        $authorization = new \App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware($container->get(AuthorizationPolicy::class), $responder);
        $adminAuthorization = new \App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware($container->get(AuthorizationPolicy::class), $responder, true);
        IdentityRoutes::register($app, $container->get(IdentityController::class), $authentication, $authorization);
        UserRoutes::register($app, $container->get(UserController::class), $authentication, $authorization, $adminAuthorization);
        FleetRoutes::register($app, $container->get(FleetController::class), $authentication, $authorization, $adminAuthorization);
        BookingRoutes::register($app, $container->get(BookingController::class), $authentication, $authorization);
        BillingRoutes::register($app, $container->get(BillingController::class), $authentication, $authorization);
        $app->add(new JsonBodyMiddleware());
        $app->add(new LegacyRouteAdapter());
        $app->add(new RequestIdMiddleware());
        $app->add(new ExceptionMiddleware($responder));
    }
}
