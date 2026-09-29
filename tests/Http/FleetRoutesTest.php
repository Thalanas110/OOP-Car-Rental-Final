<?php

declare(strict_types=1);

namespace Tests\Http;

use App\Modules\Fleet\Application\Handler\ArchiveCarHandler;
use App\Modules\Fleet\Application\Handler\CreateCarHandler;
use App\Modules\Fleet\Application\Handler\DestroyCarHandler;
use App\Modules\Fleet\Application\Handler\GetCarHandler;
use App\Modules\Fleet\Application\Handler\ListCarsHandler;
use App\Modules\Fleet\Application\Handler\UpdateCarHandler;
use App\Modules\Fleet\Infrastructure\Persistence\PdoCarRepository;
use App\Modules\Fleet\Presentation\Http\FleetController;
use App\Shared\Domain\ValueObject\Money;
use App\Shared\Presentation\Http\Response\JsonResponder;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PDO;
use PHPUnit\Framework\TestCase;

final class FleetRoutesTest extends TestCase
{
    public function testCreateAndCheckCarsController(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE carstable (carID INTEGER PRIMARY KEY AUTOINCREMENT, car_brand TEXT NOT NULL, car_model TEXT NOT NULL, manu_year TEXT NULL, daily_rate NUMERIC NOT NULL, AC INTEGER NULL, seating_capacity INTEGER NOT NULL, plate_no TEXT NULL, isdeleted INTEGER NOT NULL DEFAULT 0)');
        $repository = new PdoCarRepository($pdo, Money::fromDecimal('200000.00'));
        $controller = new FleetController(new CreateCarHandler($repository), new ListCarsHandler($repository), new GetCarHandler($repository), new UpdateCarHandler($repository), new ArchiveCarHandler($repository), new DestroyCarHandler($repository), new JsonResponder());

        $createdResponse = $controller->create((new ServerRequest('POST', '/cars'))->withParsedBody(['car_brand' => 'Toyota', 'car_model' => 'Vios', 'manu_year' => '2020', 'daily_rate' => '1800.00', 'AC' => true, 'seating_capacity' => 5, 'plate_no' => 'ABC1234']), new Response());
        $checkingResponse = $controller->checking(new ServerRequest('GET', '/carchecking'), new Response());
        $created = json_decode((string) $createdResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $checking = json_decode((string) $checkingResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $createdResponse->getStatusCode());
        self::assertSame(1, $created['data']['carID']);
        self::assertCount(1, $checking['data']);
    }
}
