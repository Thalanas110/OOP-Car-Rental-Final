<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Presentation\Http;

use Slim\App;

final class FleetRoutes
{
    public static function register(App $app, FleetController $controller): void
    {
        $app->get('/cars', [$controller, 'list']);
        $app->get('/cars/{id}', [$controller, 'get']);
        $app->get('/carchecking', [$controller, 'checking']);
        $app->post('/cars', [$controller, 'create']);
        $app->patch('/cars/{id}', [$controller, 'update']);
        $app->delete('/cars/{id}', [$controller, 'archive']);
        $app->delete('/destroycars/{id}', [$controller, 'destroy']);
    }
}
