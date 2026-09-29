<?php

declare(strict_types=1);

namespace App\Modules\Fleet\Presentation\Http;

use App\Modules\Fleet\Application\Command\ArchiveCar;
use App\Modules\Fleet\Application\Command\CreateCar;
use App\Modules\Fleet\Application\Command\DestroyCar;
use App\Modules\Fleet\Application\Command\UpdateCar;
use App\Modules\Fleet\Application\Handler\ArchiveCarHandler;
use App\Modules\Fleet\Application\Handler\CreateCarHandler;
use App\Modules\Fleet\Application\Handler\DestroyCarHandler;
use App\Modules\Fleet\Application\Handler\GetCarHandler;
use App\Modules\Fleet\Application\Handler\ListCarsHandler;
use App\Modules\Fleet\Application\Handler\UpdateCarHandler;
use App\Modules\Fleet\Application\Query\GetCar;
use App\Modules\Fleet\Application\Query\ListCars;
use App\Shared\Domain\Exception\ValidationException;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class FleetController
{
    public function __construct(private CreateCarHandler $createCar, private ListCarsHandler $listCars, private GetCarHandler $getCar, private UpdateCarHandler $updateCar, private ArchiveCarHandler $archiveCar, private DestroyCarHandler $destroyCar, private JsonResponder $responder) {}

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $includeLuxury = strtolower($request->getQueryParams()['include_luxury'] ?? 'false') === 'true';
        $data = array_map(static fn ($view): array => $view->toArray(), ($this->listCars)(new ListCars($includeLuxury)));
        return $this->responder->success($data, 200, $this->meta($request));
    }

    public function checking(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = array_map(static fn ($view): array => $view->toArray(), ($this->listCars)(new ListCars(false)));
        return $this->responder->success($data, 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->responder->success(($this->getCar)(new GetCar($this->id($args)))->toArray(), 200, $this->meta($request));
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->createCar)(new CreateCar($this->string($body, 'car_brand'), $this->string($body, 'car_model'), isset($body['manu_year']) ? (string) $body['manu_year'] : null, $this->string($body, 'daily_rate'), (bool) ($body['AC'] ?? false), $this->positiveInt($body, 'seating_capacity'), isset($body['plate_no']) ? (string) $body['plate_no'] : null));
        return $this->responder->success($view->toArray(), 201, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $this->body($request);
        $view = ($this->updateCar)(new UpdateCar($this->id($args), $this->string($body, 'daily_rate'), isset($body['plate_no']) ? (string) $body['plate_no'] : null));
        return $this->responder->success($view->toArray(), 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function archive(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        ($this->archiveCar)(new ArchiveCar($this->id($args)));
        return $this->responder->success(null, 200, $this->meta($request));
    }

    /** @param array<string, string> $args */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        ($this->destroyCar)(new DestroyCar($this->id($args)));
        return $this->responder->success(null, 200, $this->meta($request));
    }

    /** @return array<string, mixed> */
    private function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) { throw new ValidationException('Request body must be a JSON object.'); }
        return $body;
    }

    /** @param array<string, mixed> $body */
    private function string(array $body, string $key): string
    {
        $value = $body[$key] ?? null;
        if (!is_string($value) || trim($value) === '') { throw new ValidationException(sprintf('%s is required.', $key)); }
        return $value;
    }

    /** @param array<string, mixed> $body */
    private function positiveInt(array $body, string $key): int
    {
        $value = $body[$key] ?? null;
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) { throw new ValidationException(sprintf('%s must be a positive integer.', $key)); }
        $integer = (int) $value;
        if ($integer < 1) { throw new ValidationException(sprintf('%s must be a positive integer.', $key)); }
        return $integer;
    }

    /** @param array<string, string> $args */
    private function id(array $args): int { return $this->positiveInt($args, 'id'); }
    /** @return array{request_id: string} */
    private function meta(ServerRequestInterface $request): array { return ['request_id' => (string) ($request->getAttribute('request_id') ?? 'unknown')]; }
}
