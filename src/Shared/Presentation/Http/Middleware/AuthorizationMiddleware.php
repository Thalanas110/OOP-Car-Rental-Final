<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Middleware;

use App\Shared\Application\Auth\AuthorizationPolicy;
use App\Shared\Application\Error\ErrorResponse;
use App\Shared\Domain\ValueObject\UserId;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final readonly class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthorizationPolicy $policy, private JsonResponder $responder, private bool $adminOnly = false) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $attribute = $request->getAttribute('authenticated_user_id');
            $userId = $attribute instanceof UserId ? $attribute : null;
            $this->policy->assertAllowed($userId, $this->adminOnly);
            return $handler->handle($request);
        } catch (Throwable $exception) {
            $requestId = $request->getAttribute('request_id');
            return $this->responder->error(ErrorResponse::fromException($exception, is_string($requestId) ? $requestId : 'unknown'));
        }
    }
}
