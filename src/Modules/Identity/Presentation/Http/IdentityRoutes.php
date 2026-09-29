<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http;

use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;

final class IdentityRoutes
{
    /** @param App<ContainerInterface> $app */
    public static function register(App $app, IdentityController $controller, AuthenticationMiddleware $authentication, AuthorizationMiddleware $authorization): void
    {
        $app->post('/login', [$controller, 'login']);
        $app->post('/useraccount', [$controller, 'register']);
        $app->patch('/useraccount', [$controller, 'changePassword'])->add($authorization)->add($authentication);
    }
}
