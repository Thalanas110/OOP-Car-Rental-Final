# Car Rental API

This repository is a PHP 8.3+ domain-driven modular monolith for the car-rental API. The refactor preserves the existing API endpoint paths and HTTP methods while isolating business rules from HTTP and PDO concerns.

Original work credit: the starting application was created by **@jeraldpangan**. This refactor preserves that API surface and credits the original author here as requested.

## Modules

- Identity: registration, login, opaque expiring bearer tokens, and password changes.
- Users: customer profiles and archive/delete operations.
- Fleet: cars, availability checks, and fleet lifecycle operations.
- Bookings: date-range validation, availability, pricing, and booking persistence.
- Billing: payments, totals, and VIP policy updates.

Each module is organized into Domain, Application, Infrastructure, and Presentation layers. Cross-module behavior uses application ports rather than direct module coupling.

## API and Swagger UI

The preserved endpoints are:

```text
POST   /login
POST   /useraccount
PATCH  /useraccount
GET    /users
POST   /users
GET    /users/{id}
PATCH  /users/{id}
DELETE /users/{id}
DELETE /destroyusers/{id}
GET    /cars
POST   /cars
GET    /cars/{id}
PATCH  /cars/{id}
DELETE /cars/{id}
GET    /carchecking
DELETE /destroycars/{id}
POST   /carbooking
POST   /billing
```

Swagger UI is served at [`/docs`](http://localhost:8080/docs), with the source document at [`/docs/openapi.yaml`](http://localhost:8080/docs/openapi.yaml).

## Local development

XAMPP is not required. The application uses PDO and reads its database connection from environment variables. Install dependencies and run the test suite with:

```bash
composer install
composer test
composer analyse
composer format:check
```

The built-in PHP server can be started with:

```bash
composer serve
```

The default local server is `http://127.0.0.1:8080`. Apply the SQL files in `database/migrations/` to a MariaDB/MySQL database before calling persistence-backed endpoints.

Required environment variables include `DB_DSN`, `DB_USER`, `DB_PASSWORD`, and `TOKEN_TTL`. Optional configuration includes `LUXURY_CAR_DAILY_RATE` and `VIP_POINTS_THRESHOLD`.

## Quality gates

The project uses PHPUnit for unit, HTTP, and persistence tests; PHPStan level 9 for static analysis; and PHP CS Fixer for formatting. GitHub Actions runs the same checks on every push and pull request.
