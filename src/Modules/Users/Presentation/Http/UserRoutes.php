<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentation\Http;

use App\Modules\Identity\Presentation\Http\Middleware\AuthenticationMiddleware;
use App\Shared\Presentation\Http\Middleware\AuthorizationMiddleware;
use Slim\App;

final class UserRoutes
{
    public static function register(App $app, UserController $controller, AuthenticationMiddleware $authentication, AuthorizationMiddleware $authorization, AuthorizationMiddleware $adminAuthorization): void
    {
        $app->get('/users', [$controller, 'list'])->add($authorization)->add($authentication);
        $app->get('/users/{id}', [$controller, 'get'])->add($authorization)->add($authentication);
        $app->post('/users', [$controller, 'create'])->add($authorization)->add($authentication);
        $app->patch('/users/{id}', [$controller, 'update'])->add($authorization)->add($authentication);
        $app->delete('/users/{id}', [$controller, 'archive'])->add($authorization)->add($authentication);
        $app->delete('/destroyusers/{id}', [$controller, 'destroy'])->add($adminAuthorization)->add($authentication);
    }
}
