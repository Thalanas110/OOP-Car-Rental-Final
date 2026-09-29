<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Shared\Presentation\Http\LegacyRequestPath;
use App\Shared\Presentation\Http\LegacyRouteAdapter;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LegacyRouteAdapterTest extends TestCase
{
    public function testParsesLegacyRequestQueryPath(): void
    {
        $request = (new ServerRequest('GET', '/routes.php'))->withQueryParams(['request' => 'cars/7']);

        self::assertSame(['cars', '7'], LegacyRequestPath::fromRequest($request));
    }

    public function testRewritesLegacyRequestForSlimRouting(): void
    {
        $captured = null;
        $request = (new ServerRequest('GET', '/routes.php'))->withQueryParams(['request' => 'users/8']);
        $handler = new class ($captured) implements RequestHandlerInterface {
            public function __construct(private mixed &$captured) {}
            public function handle(ServerRequestInterface $request): ResponseInterface { $this->captured = $request->getUri()->getPath(); return new Response(200); }
        };

        $response = (new LegacyRouteAdapter())->process($request, $handler);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('/users/8', $captured);
    }
}
