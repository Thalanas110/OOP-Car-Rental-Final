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
use App\Shared\Presentation\Http\DocsController;
use Psr\Container\ContainerInterface;
use Slim\App;

final class RouteRegistrar
{
    /** @param App<ContainerInterface> $app */
    public static function register(App $app, ContainerInterface $container): void
    {
        /** @var DocsController $docs */
        $docs = $container->get(DocsController::class);
        $app->get('/docs', [$docs, 'ui']);
        $app->get('/docs/openapi.yaml', [$docs, 'document']);
        /** @var JsonResponder $responder */
        $responder = $container->get(JsonResponder::class);
        /** @var \App\Modules\Identity\Application\Security\TokenVerifier $tokenVerifier */
        $tokenVerifier = $container->get(\App\Modules\Identity\Application\Security\TokenVerifier::class);
        /** @var AuthorizationPolicy $authorizationPolicy */
        $authorizationPolicy = $container->get(AuthorizationPolicy::class);
        $authentication = new AuthenticationMiddleware($tokenVerifier, $responder);
        $authorization = new \App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware($authorizationPolicy, $responder);
        $adminAuthorization = new \App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware($authorizationPolicy, $responder, true);
        /** @var IdentityController $identityController */
        $identityController = $container->get(IdentityController::class);
        /** @var UserController $userController */
        $userController = $container->get(UserController::class);
        /** @var FleetController $fleetController */
        $fleetController = $container->get(FleetController::class);
        /** @var BookingController $bookingController */
        $bookingController = $container->get(BookingController::class);
        /** @var BillingController $billingController */
        $billingController = $container->get(BillingController::class);
        IdentityRoutes::register($app, $identityController, $authentication, $authorization);
        UserRoutes::register($app, $userController, $authentication, $authorization, $adminAuthorization);
        FleetRoutes::register($app, $fleetController, $authentication, $authorization, $adminAuthorization);
        BookingRoutes::register($app, $bookingController, $authentication, $authorization);
        BillingRoutes::register($app, $billingController, $authentication, $authorization);
        $app->add(new JsonBodyMiddleware());
        $app->add(new LegacyRouteAdapter());
        $app->add(new RequestIdMiddleware());
        $app->add(new ExceptionMiddleware($responder));
    }
}
