# Car Rental DDD Modular Monolith Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Refactor the legacy PHP Car Rental API into a tested DDD modular monolith with preserved endpoint names, Swagger UI, secure application services, and at least 40 meaningful commits.

**Architecture:** Slim 4 and PHP-DI stay at the HTTP/composition edge. Identity, Users, Fleet, Bookings, and Billing are bounded modules with Domain, Application, Infrastructure, and Presentation layers. Existing PDO tables remain the initial persistence contract behind repository interfaces, while routes.php?request=... remains a compatibility adapter.

**Tech Stack:** PHP 8.3+, Composer, Slim 4, PHP-DI 7, Nyholm PSR-7, PDO MySQL/MariaDB, PHPUnit 11, PHPStan level 9, PHP-CS-Fixer, OpenAPI 3.1, Swagger UI, GitHub Actions, Docker Compose.

## Global Constraints

- Existing endpoint names and HTTP methods are preserved.
- The legacy routes.php?request=... convention remains supported.
- The new README explicitly credits @jeraldpangan for the original work.
- Swagger UI is available at /docs and uses docs/openapi/openapi.yaml.
- Every PHP file declares strict_types=1 and uses typed properties, parameters, and return values.
- Business logic does not live in controllers or route files.
- All SQL values use prepared statements; SQL errors and secrets never reach clients.
- Passwords use password_hash()/password_verify(); tokens are random, expiring, and configuration-backed.
- No focused, skipped, weakened, or swallowed tests are added.
- PHPStan level 9, formatting, OpenAPI validation, and the full relevant test suite are blocking gates.
- The implementation must contain at least 40 meaningful commits; the ledger below targets 50 implementation commits in addition to the committed design and plan.

## Planned File Map

~~~text
composer.json
composer.lock
.env.example
phpstan.neon
.php-cs-fixer.dist.php
phpunit.xml
Dockerfile
docker-compose.yml
public/index.php
public/docs/index.html
config/config.php
config/container.php
config/routes.php
config/openapi.php
database/migrations/001_initial_schema.sql
database/migrations/002_indexes_and_constraints.sql
database/seed/001_reference_data.sql
docs/openapi/openapi.yaml
README.md
.github/workflows/ci.yml
src/Shared/...
src/Modules/Identity/...
src/Modules/Users/...
src/Modules/Fleet/...
src/Modules/Bookings/...
src/Modules/Billing/...
tests/Unit/...
tests/Integration/...
tests/Http/...
tests/Support/...
~~~

Commit ledger: the design is commit 1 (f31dc80), this plan is commit 2, and Tasks 1–50 below are implementation commits 3–52. Every task ends with a focused commit and a fresh check.

---

### Task 1: Establish Composer and project metadata

**Files:**
- Create: composer.json
- Create: .gitignore
- Create: .env.example

**Interfaces:**
- Produces Composer PSR-4 autoloading for App\ from src/ and Tests\ from tests/.

- [ ] **Step 1: Write the failing install check**

~~~powershell
composer validate --strict
~~~

Expected before implementation: Composer reports that composer.json does not exist.

- [ ] **Step 2: Add the manifest**

~~~json
{
  "name": "car-rental/api",
  "type": "project",
  "require": {
    "php": ">=8.3",
    "php-di/php-di": "^7.0",
    "nyholm/psr7": "^1.8",
    "php-di/slim-bridge": "^3.4",
    "slim/slim": "^4.15",
    "firebase/php-jwt": "^6.11"
  },
  "require-dev": {
    "phpstan/phpstan": "^2.1",
    "phpunit/phpunit": "^11.5",
    "friendsofphp/php-cs-fixer": "^3.75",
    "symfony/yaml": "^7.3"
  },
  "autoload": { "psr-4": { "App\\": "src/" } },
  "autoload-dev": { "psr-4": { "Tests\\": "tests/" } },
  "scripts": {
    "test": "phpunit --testdox",
    "analyse": "phpstan analyse --level=9",
    "format:check": "php-cs-fixer check --diff",
    "format": "php-cs-fixer fix",
    "serve": "php -S 127.0.0.1:8080 -t public public/index.php"
  }
}
~~~

- [ ] **Step 3: Run the install and validation**

Run composer validate --strict; composer install --no-interaction. Expected: exit 0 and a generated composer.lock.

- [ ] **Step 4: Commit**

~~~powershell
git add composer.json composer.lock .gitignore .env.example
git commit -m "build: establish composer project"
~~~

### Task 2: Add environment and application configuration

**Files:**
- Create: config/config.php
- Test: tests/Unit/Config/ConfigTest.php

**Interfaces:** App\Config\Config::fromEnvironment(): self; Config::databaseDsn(): string.

- [ ] **Step 1: Write the failing test**

~~~php
public function testReadsDatabaseAndTokenSettings(): void
{
    $config = Config::fromEnvironment([
        'APP_ENV' => 'testing', 'DB_DSN' => 'sqlite::memory:',
        'DB_USER' => '', 'DB_PASSWORD' => '', 'JWT_SECRET' => 'test-secret',
        'TOKEN_TTL' => '3600',
    ]);

    self::assertSame('sqlite::memory:', $config->databaseDsn());
    self::assertSame(3600, $config->tokenTtlSeconds());
}
~~~

- [ ] **Step 2: Run the test to verify it fails**

Run vendor/bin/phpunit tests/Unit/Config/ConfigTest.php. Expected: class-not-found failure.

- [ ] **Step 3: Implement immutable typed configuration**

~~~php
final readonly class Config
{
    public function __construct(
        public string $environment,
        private string $dsn,
        public string $databaseUser,
        public string $databasePassword,
        public string $jwtSecret,
        private int $tokenTtl,
    ) {}

    /** @param array<string, string> $env */
    public static function fromEnvironment(array $env): self
    {
        return new self(
            $env['APP_ENV'] ?? 'production', $env['DB_DSN'] ?? 'mysql:host=127.0.0.1;dbname=oopapi2;charset=utf8mb4',
            $env['DB_USER'] ?? 'root', $env['DB_PASSWORD'] ?? '', $env['JWT_SECRET'] ?? '',
            (int) ($env['TOKEN_TTL'] ?? '3600'),
        );
    }

    public function databaseDsn(): string { return $this->dsn; }
    public function tokenTtlSeconds(): int { return $this->tokenTtl; }
}
~~~

- [ ] **Step 4: Run the test**

Run vendor/bin/phpunit tests/Unit/Config/ConfigTest.php. Expected: 1 test, 2 assertions, pass.

- [ ] **Step 5: Commit**

~~~powershell
git add config/config.php tests/Unit/Config/ConfigTest.php
git commit -m "feat: add typed application configuration"
~~~

### Task 3: Add the application entry point and container shell

**Files:** public/index.php, config/container.php, config/routes.php, tests/Unit/Bootstrap/ContainerTest.php.

**Interfaces:** App\Bootstrap\createContainer(): Container; App\Bootstrap\createApp(): App.

- [ ] **Step 1:** Write a test that resolves Config and PDO definitions from the container and run it; expected failure because bootstrap files do not exist.
- [ ] **Step 2:** Add createContainer() with PHP-DI definitions and createApp() with AppFactory::create().
- [ ] **Step 3:** Run vendor/bin/phpunit tests/Unit/Bootstrap/ContainerTest.php; expected pass with an in-memory PDO connection.
- [ ] **Step 4:** Commit git add public config tests/Unit/Bootstrap/ContainerTest.php; git commit -m "feat: add application bootstrap shell".

### Task 4: Add shared typed identifiers

**Files:** src/Shared/Domain/ValueObject/Identifier.php, src/Shared/Domain/ValueObject/UserId.php, src/Shared/Domain/ValueObject/CarId.php, src/Shared/Domain/ValueObject/BookingId.php, tests/Unit/Shared/IdentifierTest.php.

**Interfaces:** Identifier::fromInt(int): static, Identifier::toInt(): int.

- [ ] **Step 1:** Test zero/negative rejection and round-trip conversion; run the test and confirm red.
- [ ] **Step 2:** Implement readonly identifier classes with InvalidArgumentException for values below 1.
- [ ] **Step 3:** Run vendor/bin/phpunit tests/Unit/Shared/IdentifierTest.php; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Unit/Shared/IdentifierTest.php; git commit -m "feat: add typed domain identifiers".

### Task 5: Add Money and DateRange value objects

**Files:** src/Shared/Domain/ValueObject/Money.php, src/Shared/Domain/ValueObject/DateRange.php, tests/Unit/Shared/MoneyTest.php, tests/Unit/Shared/DateRangeTest.php.

**Interfaces:** Money::fromDecimal(string): self, Money::plus(self): self, Money::toDecimal(): string; DateRange::between(DateTimeImmutable, DateTimeImmutable): self, DateRange::overlaps(self): bool, DateRange::billableDays(): int.

- [ ] **Step 1:** Add tests for decimal precision, negative rejection, reversed dates, one-day minimum billing, and overlap edges; verify red.
- [ ] **Step 2:** Implement integer-minor-unit Money and half-open DateRange semantics.
- [ ] **Step 3:** Run both test files; expected all pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Unit/Shared/MoneyTest.php tests/Unit/Shared/DateRangeTest.php; git commit -m "feat: add money and date range value objects".

### Task 6: Add domain exceptions and safe error contracts

**Files:** src/Shared/Domain/Exception/DomainException.php, src/Shared/Domain/Exception/ValidationException.php, src/Shared/Domain/Exception/NotFoundException.php, src/Shared/Domain/Exception/ConflictException.php, src/Shared/Application/Error/ErrorResponse.php, tests/Unit/Shared/ErrorResponseTest.php.

**Interfaces:** DomainException::statusCode(): int; ErrorResponse::fromException(Throwable, string): array.

- [ ] **Step 1:** Test mappings for 400, 404, 409, 422, and 500 and assert that SQL text is absent; verify red.
- [ ] **Step 2:** Implement typed exceptions and a safe response DTO.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Unit/Shared/ErrorResponseTest.php; git commit -m "feat: add typed domain error contracts".

### Task 7: Add clock and transaction ports

**Files:** src/Shared/Domain/Service/Clock.php, src/Shared/Infrastructure/Time/SystemClock.php, src/Shared/Infrastructure/Time/FrozenClock.php, src/Shared/Application/Transaction/TransactionManager.php, tests/Unit/Shared/ClockTest.php.

**Interfaces:** Clock::now(): DateTimeImmutable; TransactionManager::run(callable): mixed.

- [ ] **Step 1:** Test FrozenClock determinism and transaction callback execution; verify red.
- [ ] **Step 2:** Implement the ports and in-memory test double.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Unit/Shared/ClockTest.php; git commit -m "feat: add shared clock and transaction ports".

### Task 8: Add PDO and transaction infrastructure

**Files:** src/Shared/Infrastructure/Persistence/PdoConnectionFactory.php, src/Shared/Infrastructure/Persistence/PdoTransactionManager.php, tests/Integration/Shared/PdoTransactionTest.php.

**Interfaces:** PdoConnectionFactory::create(Config): PDO; PdoTransactionManager::run(callable): mixed.

- [ ] **Step 1:** Test commit and rollback with sqlite::memory: and verify red.
- [ ] **Step 2:** Implement PDO error mode, associative fetch mode, disabled emulated prepares, and rollback on throwable.
- [ ] **Step 3:** Run the integration test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Integration/Shared/PdoTransactionTest.php; git commit -m "feat: add pdo transaction infrastructure".

### Task 9: Add HTTP JSON and request-ID middleware

**Files:** src/Shared/Presentation/Http/Middleware/JsonBodyMiddleware.php, src/Shared/Presentation/Http/Middleware/RequestIdMiddleware.php, tests/Http/Middleware/JsonMiddlewareTest.php.

**Interfaces:** PSR-15 process(ServerRequestInterface, RequestHandlerInterface): ResponseInterface.

- [ ] **Step 1:** Test JSON decoding, invalid JSON response 400, and request-ID propagation; verify red.
- [ ] **Step 2:** Implement middleware returning application/json and X-Request-Id.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Http/Middleware/JsonMiddlewareTest.php; git commit -m "feat: add json and request id middleware".

### Task 10: Add standard response serialization

**Files:** src/Shared/Presentation/Http/Response/JsonResponder.php, src/Shared/Presentation/Http/Response/ResponseEnvelope.php, tests/Unit/Shared/JsonResponderTest.php.

**Interfaces:** JsonResponder::success(array|null, int, array): ResponseInterface; JsonResponder::error(ErrorResponse): ResponseInterface.

- [ ] **Step 1:** Test status, content type, stable data/error/meta keys, and request ID; verify red.
- [ ] **Step 2:** Implement typed response mapping with JSON encoding exceptions converted to 500.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Unit/Shared/JsonResponderTest.php; git commit -m "feat: add stable json response envelope".

### Task 11: Add Identity domain account model

**Files:** src/Modules/Identity/Domain/Entity/Account.php, src/Modules/Identity/Domain/ValueObject/Email.php, src/Modules/Identity/Domain/ValueObject/PasswordHash.php, tests/Unit/Modules/Identity/AccountTest.php.

**Interfaces:** Account::register(UserId, Email, PasswordHash): self; Account::userId(): UserId; Account::email(): Email.

- [ ] **Step 1:** Test valid account creation, normalized email, and no plaintext password property; verify red.
- [ ] **Step 2:** Implement immutable account state and email validation.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity/Domain tests/Unit/Modules/Identity/AccountTest.php; git commit -m "feat: add identity account domain".

### Task 12: Add Identity repository ports and PDO adapter

**Files:** src/Modules/Identity/Domain/Repository/AccountRepository.php, src/Modules/Identity/Infrastructure/Persistence/PdoAccountRepository.php, tests/Unit/Modules/Identity/PdoAccountRepositoryTest.php.

**Interfaces:** findByEmail(Email): ?Account, findByUserId(UserId): ?Account, save(Account): void, replaceToken(UserId, string, DateTimeImmutable): void.

- [ ] **Step 1:** Test SQL parameter binding and hydration from a SQLite-compatible fixture; verify red.
- [ ] **Step 2:** Implement fixed SQL and explicit row hydration.
- [ ] **Step 3:** Run the repository test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity tests/Unit/Modules/Identity/PdoAccountRepositoryTest.php; git commit -m "feat: add identity account repository".

### Task 13: Add password hashing and token ports

**Files:** src/Modules/Identity/Application/Security/PasswordHasher.php, src/Modules/Identity/Infrastructure/Security/NativePasswordHasher.php, src/Modules/Identity/Application/Security/TokenIssuer.php, src/Modules/Identity/Infrastructure/Security/JwtTokenIssuer.php, tests/Unit/Modules/Identity/SecurityTest.php.

**Interfaces:** PasswordHasher::hash(string): string, verify(string, string): bool; TokenIssuer::issue(UserId, Email): Token.

- [ ] **Step 1:** Test password verification and token expiry/claims; verify red.
- [ ] **Step 2:** Implement native password hashing and Firebase JWT with configured secret and TTL.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity tests/Unit/Modules/Identity/SecurityTest.php; git commit -m "feat: add identity security services".

### Task 14: Add account registration use case

**Files:** src/Modules/Identity/Application/Command/RegisterAccount.php, src/Modules/Identity/Application/Command/RegisterAccountHandler.php, tests/Unit/Modules/Identity/RegisterAccountHandlerTest.php.

**Interfaces:** RegisterAccountHandler::__invoke(RegisterAccount): AccountView.

- [ ] **Step 1:** Test hashing, duplicate-email conflict, and repository save; verify red.
- [ ] **Step 2:** Implement the handler with RegisterAccount readonly DTO and injected ports.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity tests/Unit/Modules/Identity/RegisterAccountHandlerTest.php; git commit -m "feat: add account registration use case".

### Task 15: Add login use case

**Files:** src/Modules/Identity/Application/Command/Login.php, src/Modules/Identity/Application/Command/LoginHandler.php, tests/Unit/Modules/Identity/LoginHandlerTest.php.

**Interfaces:** LoginHandler::__invoke(Login): LoginView.

- [ ] **Step 1:** Test unknown email and wrong password both return 401, and valid login stores the issued token; verify red.
- [ ] **Step 2:** Implement the handler with constant-time password verification and safe error messages.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity tests/Unit/Modules/Identity/LoginHandlerTest.php; git commit -m "feat: add login use case".

### Task 16: Add authentication middleware

**Files:** src/Modules/Identity/Presentation/Http/Middleware/AuthenticationMiddleware.php, tests/Http/IdentityAuthenticationMiddlewareTest.php.

**Interfaces:** PSR-15 middleware; accepts Authorization: Bearer ... and the legacy token/header pair.

- [ ] **Step 1:** Test missing, malformed, expired, and valid credentials; verify red.
- [ ] **Step 2:** Implement token extraction, verification, request attribute authenticated_user_id, and 401 responses.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity tests/Http/IdentityAuthenticationMiddlewareTest.php; git commit -m "feat: add identity authentication middleware".

### Task 17: Add Identity HTTP controllers and routes

**Files:** src/Modules/Identity/Presentation/Http/IdentityController.php, config/routes.php, tests/Http/IdentityRoutesTest.php.

**Interfaces:** POST /login, POST /useraccount, and PATCH /useraccount map to handlers.

- [ ] **Step 1:** Write HTTP tests for successful login/registration and invalid input; verify red.
- [ ] **Step 2:** Implement controllers that only parse DTOs, invoke handlers, and call JsonResponder.
- [ ] **Step 3:** Run the route tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Identity config/routes.php tests/Http/IdentityRoutesTest.php; git commit -m "feat: expose identity endpoints".

### Task 18: Add Users domain model and validation

**Files:** src/Modules/Users/Domain/Entity/User.php, src/Modules/Users/Domain/ValueObject/DriverLicense.php, tests/Unit/Modules/Users/UserTest.php.

**Interfaces:** User::register(string, string, DriverLicense): self; archive(): void; updateProfile(string, string, DriverLicense): void.

- [ ] **Step 1:** Test required fields, license normalization, update, and archive state; verify red.
- [ ] **Step 2:** Implement typed entity and invariants.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Users/Domain tests/Unit/Modules/Users/UserTest.php; git commit -m "feat: add users domain model".

### Task 19: Add Users repository

**Files:** src/Modules/Users/Domain/Repository/UserRepository.php, src/Modules/Users/Infrastructure/Persistence/PdoUserRepository.php, tests/Unit/Modules/Users/UserRepositoryTest.php.

**Interfaces:** find(UserId): ?User, listActive(): list<User>, existsByDriverLicense(DriverLicense): bool, save(User): User, archive(UserId): void, delete(UserId): void.

- [ ] **Step 1:** Test active filtering, license lookup, archive, and parameterized ID access; verify red.
- [ ] **Step 2:** Implement repository SQL against userstable.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Users tests/Unit/Modules/Users/UserRepositoryTest.php; git commit -m "feat: add users repository".

### Task 20: Add Users application handlers

**Files:** src/Modules/Users/Application/Command/CreateUser.php, src/Modules/Users/Application/Command/UpdateUser.php, src/Modules/Users/Application/Command/ArchiveUser.php, src/Modules/Users/Application/Command/DestroyUser.php, src/Modules/Users/Application/Query/ListUsers.php, src/Modules/Users/Application/Query/GetUser.php, src/Modules/Users/Application/Handler/CreateUserHandler.php, src/Modules/Users/Application/Handler/UpdateUserHandler.php, src/Modules/Users/Application/Handler/ArchiveUserHandler.php, src/Modules/Users/Application/Handler/DestroyUserHandler.php, src/Modules/Users/Application/Handler/ListUsersHandler.php, src/Modules/Users/Application/Handler/GetUserHandler.php, tests/Unit/Modules/Users/UserHandlersTest.php.

**Interfaces:** handlers return typed UserView/list<UserView> and throw NotFoundException or ConflictException.

- [ ] **Step 1:** Write tests for create, duplicate license, get, update, list, archive, and destroy; verify red.
- [ ] **Step 2:** Implement handlers with repository DI and no HTTP dependencies.
- [ ] **Step 3:** Run vendor/bin/phpunit tests/Unit/Modules/Users; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Users tests/Unit/Modules/Users; git commit -m "feat: add users application handlers".

### Task 21: Add Users HTTP controller and routes

**Files:** src/Modules/Users/Presentation/Http/UserController.php, tests/Http/UsersRoutesTest.php, config/routes.php.

**Interfaces:** GET/POST/PATCH/DELETE users routes and {id} variants.

- [ ] **Step 1:** Add HTTP tests for all Users endpoint variants and assert auth on writes; verify red.
- [ ] **Step 2:** Implement the controller and route registrations.
- [ ] **Step 3:** Run the HTTP tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Users config/routes.php tests/Http/UsersRoutesTest.php; git commit -m "feat: expose users endpoints".

### Task 22: Add Fleet Car domain model

**Files:** src/Modules/Fleet/Domain/Entity/Car.php, src/Modules/Fleet/Domain/ValueObject/CarRate.php, tests/Unit/Modules/Fleet/CarTest.php.

**Interfaces:** Car::register(...), updateRateAndPlate(Money, string): void, archive(): void, isLuxury(Money): bool.

- [ ] **Step 1:** Test car field validation, rate updates, archive, and luxury threshold policy; verify red.
- [ ] **Step 2:** Implement the typed entity and configured luxury policy.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Fleet/Domain tests/Unit/Modules/Fleet/CarTest.php; git commit -m "feat: add fleet car domain".

### Task 23: Add Fleet repository and availability query

**Files:** src/Modules/Fleet/Domain/Repository/CarRepository.php, src/Modules/Fleet/Domain/Repository/AvailabilityReader.php, src/Modules/Fleet/Infrastructure/Persistence/PdoCarRepository.php, tests/Unit/Modules/Fleet/CarRepositoryTest.php.

**Interfaces:** find, listActive(bool $includeLuxury), save, archive, delete; AvailabilityReader::availableCars(?UserId): list<CarView>.

- [ ] **Step 1:** Test active-only reads, VIP luxury inclusion, and fixed ID parameters; verify red.
- [ ] **Step 2:** Implement repository queries against carstable and injected VIP access port.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Fleet tests/Unit/Modules/Fleet/CarRepositoryTest.php; git commit -m "feat: add fleet persistence and availability query".

### Task 24: Add Fleet application handlers

**Files:** src/Modules/Fleet/Application/Command/CreateCar.php, src/Modules/Fleet/Application/Command/UpdateCar.php, src/Modules/Fleet/Application/Command/ArchiveCar.php, src/Modules/Fleet/Application/Command/DestroyCar.php, src/Modules/Fleet/Application/Query/ListCars.php, src/Modules/Fleet/Application/Query/GetCar.php, src/Modules/Fleet/Application/Query/CarChecking.php, src/Modules/Fleet/Application/Handler/CreateCarHandler.php, src/Modules/Fleet/Application/Handler/UpdateCarHandler.php, src/Modules/Fleet/Application/Handler/ArchiveCarHandler.php, src/Modules/Fleet/Application/Handler/DestroyCarHandler.php, src/Modules/Fleet/Application/Handler/ListCarsHandler.php, src/Modules/Fleet/Application/Handler/GetCarHandler.php, src/Modules/Fleet/Application/Handler/CarCheckingHandler.php, tests/Unit/Modules/Fleet/FleetHandlersTest.php.

**Interfaces:** handlers return CarView or list<CarView> and use CarRepository only.

- [ ] **Step 1:** Test create/get/list/update/archive/destroy and missing-car behavior; verify red.
- [ ] **Step 2:** Implement handlers and DTO validation.
- [ ] **Step 3:** Run vendor/bin/phpunit tests/Unit/Modules/Fleet; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Fleet tests/Unit/Modules/Fleet; git commit -m "feat: add fleet application handlers".

### Task 25: Add Fleet HTTP controller and routes

**Files:** src/Modules/Fleet/Presentation/Http/FleetController.php, tests/Http/FleetRoutesTest.php, config/routes.php.

**Interfaces:** GET cars, GET cars/{id}, GET carchecking, POST cars, PATCH cars/{id}, DELETE cars/{id}, DELETE destroycars/{id}.

- [ ] **Step 1:** Add route tests for all Fleet endpoints and auth behavior; verify red.
- [ ] **Step 2:** Implement the controller and route registration.
- [ ] **Step 3:** Run the tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Fleet config/routes.php tests/Http/FleetRoutesTest.php; git commit -m "feat: expose fleet endpoints".

### Task 26: Add Booking domain model and overlap policy

**Files:** src/Modules/Bookings/Domain/Entity/Booking.php, src/Modules/Bookings/Domain/Service/BookingPolicy.php, tests/Unit/Modules/Bookings/BookingPolicyTest.php.

**Interfaces:** Booking::create(CarId, UserId, DateRange, Money): self; BookingPolicy::assertBookable(...).

- [ ] **Step 1:** Test reversed dates, overlap conflict, missing customer/car, luxury restriction, and total-cost calculation; verify red.
- [ ] **Step 2:** Implement the policy with explicit ports for customer VIP status and car rate.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Bookings/Domain tests/Unit/Modules/Bookings/BookingPolicyTest.php; git commit -m "feat: add booking domain policies".

### Task 27: Add Booking repository ports and PDO adapters

**Files:** src/Modules/Bookings/Domain/Repository/BookingRepository.php, src/Modules/Bookings/Domain/Repository/BookingAvailabilityReader.php, src/Modules/Bookings/Infrastructure/Persistence/PdoBookingRepository.php, tests/Unit/Modules/Bookings/BookingRepositoryTest.php.

**Interfaces:** hasOverlap(CarId, DateRange): bool, save(Booking): Booking, find(BookingId): ?Booking, listForUser(UserId): list<Booking>.

- [ ] **Step 1:** Test overlap SQL boundary behavior and hydration; verify red.
- [ ] **Step 2:** Implement parameterized queries and booking insert/read mapping.
- [ ] **Step 3:** Run the repository test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Bookings tests/Unit/Modules/Bookings; git commit -m "feat: add booking persistence".

### Task 28: Add Booking create use case

**Files:** src/Modules/Bookings/Application/Command/CreateBooking.php, CreateBookingHandler.php, tests/Unit/Modules/Bookings/CreateBookingHandlerTest.php.

**Interfaces:** CreateBookingHandler::__invoke(CreateBooking): BookingView.

- [ ] **Step 1:** Test valid booking, overlap 409, luxury restriction 403, and cost calculation; verify red.
- [ ] **Step 2:** Implement the handler with customer, car, and booking ports and a transaction manager.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Bookings tests/Unit/Modules/Bookings/CreateBookingHandlerTest.php; git commit -m "feat: add booking creation use case".

### Task 29: Add Booking HTTP controller and routes

**Files:** src/Modules/Bookings/Presentation/Http/BookingController.php, tests/Http/BookingRoutesTest.php, config/routes.php.

**Interfaces:** POST /carbooking maps to CreateBooking and reads authenticated user identity while accepting legacy userID input for compatibility validation.

- [ ] **Step 1:** Add HTTP tests for valid booking, overlap, and invalid date ranges; verify red.
- [ ] **Step 2:** Implement controller DTO mapping and route registration behind authentication.
- [ ] **Step 3:** Run the tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Bookings config/routes.php tests/Http/BookingRoutesTest.php; git commit -m "feat: expose booking endpoint".

### Task 30: Add Billing domain payment and VIP policy

**Files:** src/Modules/Billing/Domain/Entity/Payment.php, src/Modules/Billing/Domain/Service/VipPolicy.php, tests/Unit/Modules/Billing/VipPolicyTest.php.

**Interfaces:** Payment::record(BookingId, Money): self; VipPolicy::pointsAfter(Money, Money): Money; VipPolicy::hasAccess(Money): bool.

- [ ] **Step 1:** Test positive amount requirement, VIP threshold crossing, and exact decimal points; verify red.
- [ ] **Step 2:** Implement payment entity and configurable policy.
- [ ] **Step 3:** Run the tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Billing/Domain tests/Unit/Modules/Billing; git commit -m "feat: add billing domain policy".

### Task 31: Add Billing repositories and atomic payment transaction

**Files:** src/Modules/Billing/Domain/Repository/PaymentRepository.php, src/Modules/Billing/Application/Port/VipAccountPort.php, src/Modules/Billing/Infrastructure/Persistence/PdoPaymentRepository.php, src/Modules/Billing/Infrastructure/Persistence/PdoVipAccountAdapter.php, tests/Integration/Modules/Billing/BillingPersistenceTest.php.

**Interfaces:** save(Payment): Payment, bookingExists(BookingId): bool, addPoints(UserId, Money): Money, grantAccess(UserId): void.

- [ ] **Step 1:** Test payment insert, booking-not-found, points update, and rollback on adapter failure; verify red.
- [ ] **Step 2:** Implement fixed SQL and transaction orchestration.
- [ ] **Step 3:** Run the integration tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Billing tests/Unit/Modules/Billing tests/Integration/Modules/Billing; git commit -m "feat: add billing persistence and atomic updates".

### Task 32: Add Billing payment use case

**Files:** src/Modules/Billing/Application/Command/RecordPayment.php, src/Modules/Billing/Application/Command/RecordPaymentHandler.php, src/Modules/Billing/Application/Query/GetTotalPaid.php, src/Modules/Billing/Application/Query/GetTotalPaidHandler.php, tests/Unit/Modules/Billing/BillingHandlersTest.php.

**Interfaces:** RecordPaymentHandler::__invoke(RecordPayment): PaymentView; GetTotalPaidHandler::__invoke(UserId): Money.

- [ ] **Step 1:** Test success, missing booking 404, invalid amount 422, and total paid aggregation; verify red.
- [ ] **Step 2:** Implement handlers with transaction injection and no controller logic.
- [ ] **Step 3:** Run the tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Billing tests/Unit/Modules/Billing; git commit -m "feat: add billing application handlers".

### Task 33: Add Billing HTTP controller and routes

**Files:** src/Modules/Billing/Presentation/Http/BillingController.php, tests/Http/BillingRoutesTest.php, config/routes.php.

**Interfaces:** POST /billing maps to RecordPaymentHandler and authenticates the request.

- [ ] **Step 1:** Add HTTP tests for successful payment, missing booking, and safe error output; verify red.
- [ ] **Step 2:** Implement controller and route registration.
- [ ] **Step 3:** Run the tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules/Billing config/routes.php tests/Http/BillingRoutesTest.php; git commit -m "feat: expose billing endpoint".

### Task 34: Add cross-module application ports

**Files:** src/Modules/Identity/Application/Port/VipStatusReader.php, src/Modules/Fleet/Application/Port/CarRateReader.php, src/Modules/Users/Application/Port/UserExistenceReader.php, src/Modules/Identity/Infrastructure/Adapter/PdoVipStatusReader.php, src/Modules/Fleet/Infrastructure/Adapter/PdoCarRateReader.php, src/Modules/Users/Infrastructure/Adapter/PdoUserExistenceReader.php, tests/Unit/Modules/Ports/CrossModulePortContractTest.php.

**Interfaces:** isVip(UserId): bool, rateFor(CarId): Money, exists(UserId): bool.

- [ ] **Step 1:** Write contract tests using fake adapters; verify red.
- [ ] **Step 2:** Implement adapters that delegate to repositories without direct cross-module SQL.
- [ ] **Step 3:** Run contract tests; expected pass.
- [ ] **Step 4:** Commit git add src/Modules tests/Unit/Modules/Ports; git commit -m "refactor: formalize module application ports".

### Task 35: Add legacy request query adapter

**Files:** src/Shared/Presentation/Http/LegacyRequestPath.php, src/Shared/Presentation/Http/LegacyRouteAdapter.php, tests/Http/LegacyRouteAdapterTest.php.

**Interfaces:** LegacyRequestPath::fromRequest(ServerRequestInterface): list<string>; LegacyRouteAdapter::register(App): void.

- [ ] **Step 1:** Test routes.php?request=cars/7, missing request, and URL-decoded segments; verify red.
- [ ] **Step 2:** Implement the compatibility adapter that maps query segments to the same controllers as path routes.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Http/LegacyRouteAdapterTest.php; git commit -m "feat: preserve legacy request query endpoints".

### Task 36: Add centralized exception middleware

**Files:** src/Shared/Presentation/Http/Middleware/ExceptionMiddleware.php, tests/Http/ExceptionMiddlewareTest.php.

**Interfaces:** PSR-15 middleware maps Throwable using ErrorResponse::fromException().

- [ ] **Step 1:** Test domain errors, malformed JSON, and unexpected exceptions with no stack trace leakage; verify red.
- [ ] **Step 2:** Implement centralized logging plus safe JSON responses.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Http/ExceptionMiddlewareTest.php; git commit -m "feat: add centralized http error handling".

### Task 37: Add authorization policy middleware

**Files:** src/Shared/Presentation/Http/Middleware/AuthorizationMiddleware.php, src/Shared/Application/Auth/AuthorizationPolicy.php, tests/Http/AuthorizationMiddlewareTest.php.

**Interfaces:** policy checks authenticated user attributes for protected writes and admin-only destroy operations.

- [ ] **Step 1:** Test public login/registration, authenticated reads, protected writes, and forbidden destroy operations; verify red.
- [ ] **Step 2:** Implement route-scoped middleware without embedding policy branches in controllers.
- [ ] **Step 3:** Run tests; expected pass.
- [ ] **Step 4:** Commit git add src/Shared tests/Http/AuthorizationMiddlewareTest.php; git commit -m "feat: add route authorization policies".

### Task 38: Add complete route composition

**Files:** config/routes.php, config/container.php, public/index.php, tests/Http/RouteCompositionTest.php.

**Interfaces:** createApp() composes middleware in JSON → request ID → exception → authentication/authorization → route order.

- [ ] **Step 1:** Test route discovery for every preserved endpoint; verify red.
- [ ] **Step 2:** Register all module controllers, legacy adapter, middleware, and dependency definitions.
- [ ] **Step 3:** Run vendor/bin/phpunit tests/Http/RouteCompositionTest.php; expected pass.
- [ ] **Step 4:** Commit git add config public tests/Http/RouteCompositionTest.php; git commit -m "feat: compose modular api routes".

### Task 39: Add OpenAPI document foundation

**Files:** docs/openapi/openapi.yaml, config/openapi.php, tests/Docs/OpenApiDocumentTest.php.

**Interfaces:** OpenAPI 3.1 document defines bearerAuth, legacyAuth, common response schemas, and server URL.

- [ ] **Step 1:** Test that the document parses as YAML and contains the required component schemas; verify red.
- [ ] **Step 2:** Add the complete document skeleton and schema definitions for errors, users, cars, bookings, and payments.
- [ ] **Step 3:** Run the parser test; expected pass.
- [ ] **Step 4:** Commit git add docs/openapi config/openapi.php tests/Docs/OpenApiDocumentTest.php; git commit -m "docs: add openapi document foundation".

### Task 40: Document Identity, Users, and Fleet operations

**Files:** docs/openapi/openapi.yaml, tests/Docs/OpenApiEndpointCoverageTest.php.

**Interfaces:** OpenAPI paths include login, useraccount, users, cars, carchecking, request bodies, auth requirements, and status responses.

- [ ] **Step 1:** Test required path/method pairs and auth metadata; verify red.
- [ ] **Step 2:** Add the operation definitions and examples with no real passwords/tokens.
- [ ] **Step 3:** Run endpoint coverage; expected pass.
- [ ] **Step 4:** Commit git add docs/openapi tests/Docs/OpenApiEndpointCoverageTest.php; git commit -m "docs: document identity users and fleet endpoints".

### Task 41: Document Bookings, Billing, and destroy operations

**Files:** docs/openapi/openapi.yaml, tests/Docs/OpenApiEndpointCoverageTest.php.

**Interfaces:** OpenAPI paths include carbooking, billing, cars/users patch/delete, destroycars, and destroyusers.

- [ ] **Step 1:** Add failing assertions for every remaining endpoint and response code; verify red.
- [ ] **Step 2:** Add operation definitions, conflict schemas, and security requirements.
- [ ] **Step 3:** Run the coverage test; expected pass.
- [ ] **Step 4:** Commit git add docs/openapi tests/Docs/OpenApiEndpointCoverageTest.php; git commit -m "docs: document booking and billing endpoints".

### Task 42: Serve Swagger UI

**Files:** public/docs/index.html, public/docs/swagger-ui.css, public/docs/swagger-ui-bundle.js, tests/Http/SwaggerUiTest.php.

**Interfaces:** GET /docs serves Swagger UI configured with /docs/openapi.yaml; GET /docs/openapi.yaml serves the document.

- [ ] **Step 1:** Test HTML title, script reference, and OpenAPI document response; verify red.
- [ ] **Step 2:** Add pinned Swagger UI distribution assets and a CSP-safe initialization script.
- [ ] **Step 3:** Run the test; expected pass.
- [ ] **Step 4:** Commit git add public/docs tests/Http/SwaggerUiTest.php; git commit -m "feat: serve swagger ui documentation".

### Task 43: Add reproducible database migrations and seed data

**Files:** database/migrations/001_initial_schema.sql, database/migrations/002_indexes_and_constraints.sql, database/seed/001_reference_data.sql, tests/Integration/DatabaseSchemaTest.php.

**Interfaces:** migration scripts create compatible tables and indexes without importing plaintext credentials or live tokens.

- [ ] **Step 1:** Test migration execution on a disposable MariaDB service and assert required tables/indexes; verify red.
- [ ] **Step 2:** Add normalized schema SQL, foreign keys, overlap-supporting indexes, and sanitized reference seed data.
- [ ] **Step 3:** Run the schema test; expected pass.
- [ ] **Step 4:** Commit git add database tests/Integration/DatabaseSchemaTest.php; git commit -m "build: add database migrations and seed data".

### Task 44: Add endpoint compatibility matrix tests

**Files:** tests/Http/EndpointCompatibilityTest.php, tests/Support/ApiClient.php.

**Interfaces:** table-driven test covers all 16 preserved endpoint/method combinations through both path and query adapters.

- [ ] **Step 1:** Write the matrix with expected status classes and response keys; verify red for missing route mappings.
- [ ] **Step 2:** Implement the reusable test client and fixtures.
- [ ] **Step 3:** Run the full compatibility test; expected pass.
- [ ] **Step 4:** Commit git add tests/Http/EndpointCompatibilityTest.php tests/Support/ApiClient.php; git commit -m "test: cover preserved endpoint compatibility".

### Task 45: Add PHPStan level 9 configuration

**Files:** phpstan.neon, tests/StaticAnalysis/PhpStanConfigTest.php.

**Interfaces:** PHPStan scans src and uses strict level 9 with test bootstrap.

- [ ] **Step 1:** Run vendor/bin/phpstan analyse --level=9; expected initial failures identify missing types.
- [ ] **Step 2:** Add configuration and correct all reported types without suppressing application errors.
- [ ] **Step 3:** Run PHPStan; expected exit 0 with no ignored errors.
- [ ] **Step 4:** Commit git add phpstan.neon tests/StaticAnalysis/PhpStanConfigTest.php; git commit -m "build: enforce phpstan level nine".

### Task 46: Add PSR-12 formatting gate

**Files:** .php-cs-fixer.dist.php, tests/StaticAnalysis/FormattingConfigTest.php.

**Interfaces:** formatter checks src, config, public, and tests, excluding generated vendor assets.

- [ ] **Step 1:** Run vendor/bin/php-cs-fixer check --diff; expected failures on unformatted new code.
- [ ] **Step 2:** Add a PHP 8.3 PSR-12 configuration and format the source.
- [ ] **Step 3:** Run the formatter check; expected exit 0.
- [ ] **Step 4:** Commit git add .php-cs-fixer.dist.php tests/StaticAnalysis/FormattingConfigTest.php; git commit -m "style: enforce psr twelve formatting".

### Task 47: Add full PHPUnit configuration and test bootstrap

**Files:** phpunit.xml, tests/bootstrap.php, tests/Support/DatabaseFixture.php, tests/Support/FakeTokenIssuer.php.

**Interfaces:** composer test runs Unit, Integration, HTTP, and Docs suites without hidden skips.

- [ ] **Step 1:** Run composer test; expected failure because PHPUnit configuration is absent.
- [ ] **Step 2:** Add strict PHPUnit configuration, coverage source mapping, deterministic timezone, and fixtures.
- [ ] **Step 3:** Run composer test; expected all collected tests pass.
- [ ] **Step 4:** Commit git add phpunit.xml tests; git commit -m "test: configure complete phpunit suite".

### Task 48: Add CI workflow with MariaDB and quality gates

**Files:** .github/workflows/ci.yml.

**Interfaces:** workflow runs Composer validation, dependency install, unit tests, integration/HTTP tests against MariaDB, PHPStan, formatting, and OpenAPI validation.

- [ ] **Step 1:** Validate YAML syntax with ruby -e "require 'yaml'; YAML.load_file('.github/workflows/ci.yml')" or an available YAML parser; expected failure before file exists.
- [ ] **Step 2:** Add PHP 8.3 and 8.4 matrix jobs, MariaDB service health checks, lockfile-keyed Composer cache, named test commands, and artifact upload only on failure.
- [ ] **Step 3:** Run each workflow command locally in the same order; expected exit 0.
- [ ] **Step 4:** Commit git add .github/workflows/ci.yml; git commit -m "ci: add blocking php quality gates".

### Task 49: Add Docker runtime and local developer commands

**Files:** Dockerfile, docker-compose.yml, .dockerignore, Makefile.

**Interfaces:** docker compose up --build serves the API on port 8080 and MariaDB on an internal network.

- [ ] **Step 1:** Test docker compose config; expected failure before files exist.
- [ ] **Step 2:** Add non-root PHP runtime, health checks, environment wiring, migration command, and make test/analyse wrappers.
- [ ] **Step 3:** Run docker compose config and, when Docker is available, docker compose up --build --wait; expected valid config and healthy services.
- [ ] **Step 4:** Commit git add Dockerfile docker-compose.yml .dockerignore Makefile; git commit -m "build: add reproducible docker runtime".

### Task 50: Write the new README and remove legacy implementation

**Files:** README.md, OOPapi(1)/routes.php, OOPapi(1)/modules/Auth.php, OOPapi(1)/modules/Billing.php, OOPapi(1)/modules/Booking.php, OOPapi(1)/modules/Common.php, OOPapi(1)/modules/Delete.php, OOPapi(1)/modules/Get.php, OOPapi(1)/modules/Patch.php, OOPapi(1)/modules/Post.php, OOPapi(1)/config/database.php, OOPapi(1)/config/index.php, OOPapi(1)/index.php.

**Interfaces:** README documents setup, environment variables, migration, tests, endpoints, Swagger UI, architecture, security, and attribution.

- [ ] **Step 1:** Add a deletion-safety test/check that every preserved route is covered by tests/Http/EndpointCompatibilityTest.php; expected failure while legacy implementation remains the active entrypoint.
- [ ] **Step 2:** Make public/index.php the sole application entrypoint, remove obsolete direct-PDO legacy scripts, and write README text including: “Original Car Rental API work by @jeraldpangan; this repository contains the DDD modular-monolith refactor built on that foundation.”
- [ ] **Step 3:** Run composer test, composer analyse, composer format:check, composer validate --strict, OpenAPI validation, git diff --check, and the endpoint compatibility matrix; expected all exit 0.
- [ ] **Step 4:** Commit git add README.md OOPapi(1) public src tests; git commit -m "refactor: complete ddd modular monolith migration".

## Plan self-review

- The design's five bounded contexts are covered by Tasks 11–33.
- Preserved endpoint names and methods are covered by Tasks 17, 21, 25, 29, 33, 35, 38, and 44.
- Legacy query routing is covered by Tasks 35 and 44.
- Swagger UI and OpenAPI coverage are covered by Tasks 39–42.
- Security requirements are covered by Tasks 13, 16, 36, and 37.
- Transactions and VIP/payment invariants are covered by Tasks 7, 8, 26–33, and 43.
- PHPStan, formatting, PHPUnit, CI, and Docker quality gates are covered by Tasks 45–49.
- The README attribution requirement is covered by Task 50.
- The commit target is 52 total commits including the already committed design and plan; no task is an empty history-only increment.
- No unresolved placeholder instructions are present.
