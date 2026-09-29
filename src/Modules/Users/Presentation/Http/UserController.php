<?php

declare(strict_types=1);

namespace App\Modules\Users\Presentation\Http;

use App\Modules\Users\Application\Command\ArchiveUser;
use App\Modules\Users\Application\Command\CreateUser;
use App\Modules\Users\Application\Command\DestroyUser;
use App\Modules\Users\Application\Command\UpdateUser;
use App\Modules\Users\Application\Handler\ArchiveUserHandler;
use App\Modules\Users\Application\Handler\CreateUserHandler;
use App\Modules\Users\Application\Handler\DestroyUserHandler;
use App\Modules\Users\Application\Handler\GetUserHandler;
use App\Modules\Users\Application\Handler\ListUsersHandler;
use App\Modules\Users\Application\Handler\UpdateUserHandler;
use App\Modules\Users\Application\Query\GetUser;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserController
{
    public function __construct(
        private CreateUserHandler $createUser,
        private ListUsersHandler $listUsers,
        private GetUserHandler $getUser,
        private UpdateUserHandler $updateUser,
        private ArchiveUserHandler $archiveUser,
        private DestroyUserHandler $destroyUser,
        private JsonResponder $responder,
    ) {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = array_map(static fn ($view): array => $view->toArray(), ($this->listUsers)());

        return $this->responder->success($data, 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responder->success(($this->getUser)(new GetUser($this->id($args)))->toArray(), 200, $this->meta($request));
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->createUser)(new CreateUser($this->string($body, 'name'), $this->string($body, 'contact_no'), $this->string($body, 'drivers_license')));

        return $this->responder->success($view->toArray(), 201, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->updateUser)(new UpdateUser($this->id($args), $this->string($body, 'name'), $this->string($body, 'contact_no'), $this->string($body, 'drivers_license')));

        return $this->responder->success($view->toArray(), 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function archive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        ($this->archiveUser)(new ArchiveUser($this->id($args)));

        return $this->responder->success(null, 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        ($this->destroyUser)(new DestroyUser($this->id($args)));

        return $this->responder->success(null, 200, $this->meta($request));
    }

    /** @return array<string, mixed> */
    private function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new ValidationException('Request body must be a JSON object.');
        }

        return $body;
    }

    /** @param array<string, mixed> $body */
    private function string(array $body, string $key): string
    {
        $value = $body[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new ValidationException(sprintf('%s is required.', $key));
        }

        return $value;
    }

    /** @param array<string, string> $args */
    private function id(array $args): int
    {
        $value = $args['id'] ?? null;
        if ($value === null || !ctype_digit($value) || (int) $value < 1) {
            throw new ValidationException('id must be a positive integer.');
        }

        return (int) $value;
    }

    /** @return array{request_id: string} */
    private function meta(ServerRequestInterface $request): array
    {
        return ['request_id' => (string) ($request->getAttribute('request_id') ?? 'unknown')];
    }
}
