<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Middleware;

use App\Shared\Application\Error\ErrorResponse;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final readonly class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private JsonResponder $responder) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return $this->responder->error(ErrorResponse::fromException($exception, (string) ($request->getAttribute('request_id') ?? 'unknown')));
        }
    }
}
