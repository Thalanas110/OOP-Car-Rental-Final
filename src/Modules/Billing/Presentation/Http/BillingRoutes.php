<?php

declare(strict_types=1);

namespace App\Modules\Billing\Presentation\Http;

use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;

final class BillingRoutes
{
    /** @param App<ContainerInterface> $app */
    public static function register(App $app, BillingController $controller, AuthenticationMiddleware $authentication, AuthorizationMiddleware $authorization): void
    {
        $app->post('/billing', [$controller, 'create'])->add($authorization)->add($authentication);
    }
}
