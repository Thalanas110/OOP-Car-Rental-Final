<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Presentation\Http;

use Slim\App;

final class BookingRoutes
{
    public static function register(App $app, BookingController $controller): void
    {
        $app->post('/carbooking', [$controller, 'create']);
    }
}
