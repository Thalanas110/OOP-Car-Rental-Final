<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Http;

use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;

final class BookingRoutes
{
    /** @param App<ContainerInterface> $app */
    public static function register(App $app, BookingController $controller, AuthenticationMiddleware $authentication, AuthorizationMiddleware $authorization): void
    {
        $app->post('/carbooking', [$controller, 'create'])->add($authorization)->add($authentication);
    }
}
