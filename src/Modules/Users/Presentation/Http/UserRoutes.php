<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentation\Http;

use Slim\App;

final class UserRoutes
{
    public static function register(App $app, UserController $controller): void
    {
        $app->get('/users', [$controller, 'list']);
        $app->get('/users/{id}', [$controller, 'get']);
        $app->post('/users', [$controller, 'create']);
        $app->patch('/users/{id}', [$controller, 'update']);
        $app->delete('/users/{id}', [$controller, 'archive']);
        $app->delete('/destroyusers/{id}', [$controller, 'destroy']);
    }
}
