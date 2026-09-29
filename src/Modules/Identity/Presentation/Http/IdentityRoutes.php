<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http;

use Slim\App;

final class IdentityRoutes
{
    public static function register(App $app, IdentityController $controller): void
    {
        $app->post('/login', [$controller, 'login']);
        $app->post('/useraccount', [$controller, 'register']);
        $app->patch('/useraccount', [$controller, 'changePassword']);
    }
}
