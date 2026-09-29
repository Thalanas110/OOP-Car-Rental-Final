<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Users\Application\Handler\ArchiveUserHandler;
use App\Modules\Users\Application\Handler\CreateUserHandler;
use App\Modules\Users\Application\Handler\DestroyUserHandler;
use App\Modules\Users\Application\Handler\GetUserHandler;
use App\Modules\Users\Application\Handler\ListUsersHandler;
use App\Modules\Users\Application\Handler\UpdateUserHandler;
use App\Modules\Users\Infrastructure\Persistence\PdoUserRepository;
use App\Modules\Users\Presentation\Http\UserController;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PDO;
use PHPUnit\Framework\TestCase;

final class UsersRoutesTest extends TestCase
{
    public function testCreateAndListUsersController(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE userstable (userID INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, contact_no TEXT NOT NULL, isdeleted INTEGER NOT NULL DEFAULT 0, drivers_license TEXT NOT NULL UNIQUE)');
        $repository = new PdoUserRepository($pdo);
        $controller = new UserController(
            new CreateUserHandler($repository),
            new ListUsersHandler($repository),
            new GetUserHandler($repository),
            new UpdateUserHandler($repository),
            new ArchiveUserHandler($repository),
            new DestroyUserHandler($repository),
            new JsonResponder(),
        );

        $createdResponse = $controller->create(
            (new ServerRequest('POST', '/users'))->withParsedBody(['name' => 'Ada', 'contact_no' => '09123456789', 'drivers_license' => 'DL-1']),
            new Response(),
        );
        $created = json_decode((string) $createdResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $listResponse = $controller->list(new ServerRequest('GET', '/users'), new Response());
        $list = json_decode((string) $listResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $createdResponse->getStatusCode());
        self::assertSame(1, $created['data']['userID']);
        self::assertCount(1, $list['data']);
    }
}
