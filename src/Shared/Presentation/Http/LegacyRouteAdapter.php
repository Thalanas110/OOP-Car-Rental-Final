<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LegacyRouteAdapter implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() !== '/routes.php') {
            return $handler->handle($request);
        }

        $segments = LegacyRequestPath::fromRequest($request);
        if ($segments === []) {
            return $handler->handle($request);
        }

        return $handler->handle($request->withUri($request->getUri()->withPath('/' . implode('/', $segments))));
    }
}
