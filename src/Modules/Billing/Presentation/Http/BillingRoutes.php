<?php

declare(strict_types=1);

namespace App\Modules\Billing\Presentation\Http;

use Slim\App;

final class BillingRoutes
{
    public static function register(App $app, BillingController $controller): void
    {
        $app->post('/billing', [$controller, 'create']);
    }
}
