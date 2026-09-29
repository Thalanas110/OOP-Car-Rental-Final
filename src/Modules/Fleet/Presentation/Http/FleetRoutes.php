<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Presentation\Http;

use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;

final class FleetRoutes
{
    /** @param App<ContainerInterface> $app */
    public static function register(App $app, FleetController $controller, AuthenticationMiddleware $authentication, AuthorizationMiddleware $authorization, AuthorizationMiddleware $adminAuthorization): void
    {
        $app->get('/cars', [$controller, 'list'])->add($authorization)->add($authentication);
        $app->get('/cars/{id}', [$controller, 'get'])->add($authorization)->add($authentication);
        $app->get('/carchecking', [$controller, 'checking'])->add($authorization)->add($authentication);
        $app->post('/cars', [$controller, 'create'])->add($authorization)->add($authentication);
        $app->patch('/cars/{id}', [$controller, 'update'])->add($authorization)->add($authentication);
        $app->delete('/cars/{id}', [$controller, 'archive'])->add($authorization)->add($authentication);
        $app->delete('/destroycars/{id}', [$controller, 'destroy'])->add($adminAuthorization)->add($authentication);
    }
}
