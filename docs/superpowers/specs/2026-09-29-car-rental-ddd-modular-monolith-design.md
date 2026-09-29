# Car Rental API DDD Modular Monolith Design

## Goal

Refactor the legacy PHP Car Rental API into a testable Domain-Driven Modular Monolith while preserving the existing API endpoint names and HTTP methods, adding Swagger UI documentation, strengthening security, and clearly crediting `@jeraldpangan` for the original work.

## Context and constraints

- The current repository is a plain PHP API without Composer, automated tests, static analysis, or GitHub Actions.
- The current application is concentrated in `OOPapi(1)`, with `routes.php`, module classes, direct PDO access, and SQL dumps.
- The local copy of `school/meatlens/botchabuster/backend` was not available for inspection, so the design uses the requested DDD modular-monolith principles directly.
- Existing endpoint names and HTTP methods are preserved. Response envelopes may be normalized because endpoint identity, not legacy response shape, is the compatibility requirement.
- The new README must credit `@jeraldpangan` for the original Car Rental API work.
- Swagger UI is the API documentation/test UI and must be available from the running application.
- The refactor must produce at least 40 meaningful, coherent Git commits; empty or artificial commits are not acceptable.

## Selected approach

Use Slim 4 at the HTTP edge, PHP-DI for dependency injection, PDO for persistence, Composer PSR-4 autoloading, and OpenAPI 3.1 with Swagger UI. Slim and the container are confined to composition and presentation concerns so the domain and application layers remain framework-independent.

Symfony 7 was rejected as unnecessarily heavy for the existing codebase. A framework-free PSR-15 implementation was rejected because it would require the project to own routing, middleware, error handling, and OpenAPI integration infrastructure that Slim already provides.

## Module boundaries

The source tree is organized under `src/` as a modular monolith:

```text
src/
  Modules/
    Identity/
      Domain/ Application/ Infrastructure/ Presentation/Http/
    Users/
      Domain/ Application/ Infrastructure/ Presentation/Http/
    Fleet/
      Domain/ Application/ Infrastructure/ Presentation/Http/
    Bookings/
      Domain/ Application/ Infrastructure/ Presentation/Http/
    Billing/
      Domain/ Application/ Infrastructure/ Presentation/Http/
  Shared/
    Domain/ Application/ Infrastructure/ Presentation/
```

Each module owns its domain entities, value objects, use cases, repository interfaces, repository implementations, request DTOs, controllers, and response mappers. The shared kernel contains only cross-cutting primitives such as typed IDs, money, date ranges, domain exceptions, clock abstractions, transaction boundaries, request IDs, and common response contracts.

Dependency direction is inward:

```text
Presentation → Application → Domain
Infrastructure ─────────────→ Domain interfaces
```

Modules do not query another module's tables directly. Cross-module behavior uses explicit application ports. For example, Bookings asks Identity for customer VIP status through a port, and Billing invokes the VIP-point policy through a defined application interface.

## Endpoint compatibility

The existing route names and methods remain supported:

| Method | Endpoint | Bounded context/use case |
|---|---|---|
| GET | `cars`, `cars/{id}` | Fleet car queries |
| GET | `users`, `users/{id}` | Users queries |
| GET | `carchecking` | Fleet availability query |
| POST | `cars` | Fleet create-car command |
| POST | `users` | Users create command |
| POST | `useraccount` | Identity account registration |
| POST | `login` | Identity login |
| POST | `carbooking` | Bookings create command |
| POST | `billing` | Billing payment command |
| PATCH | `cars/{id}` | Fleet update command |
| PATCH | `users/{id}` | Users update command |
| PATCH | `useraccount` | Identity password-change command |
| DELETE | `cars/{id}` | Fleet archive command |
| DELETE | `users/{id}` | Users archive command |
| DELETE | `destroycars/{id}` | Fleet administrative hard-delete command |
| DELETE | `destroyusers/{id}` | Users administrative hard-delete command |

The legacy `routes.php?request=...` convention remains supported. Equivalent path-based routes may be exposed by the new router, but the query-based compatibility form is guaranteed. Legacy `Authorization` and `X-Auth-User` headers remain accepted; standard bearer-token authentication is also supported.

Responses use a stable JSON envelope with `data`, `error`, and `meta` fields. HTTP status codes are meaningful and consistent; the legacy endpoint path is the compatibility contract, not the old PHP array shape.

## Domain and application behavior

The domain models the following invariants:

- Passwords are hashed with `password_hash()` and checked with `password_verify()`.
- Tokens are cryptographically random, signed or opaque, expiring, and stored without hardcoded secrets.
- IDs, email addresses, monetary amounts, driver licenses, and date ranges are validated through typed value objects or DTOs.
- Booking dates are ordered and a car cannot have overlapping active bookings.
- Luxury cars require the configured VIP threshold.
- Payments are recorded atomically with VIP-point updates and VIP-access promotion.
- Duplicate driver licenses are rejected as a conflict.
- Archived cars and users are excluded from normal queries.
- Database exceptions never leak SQL, credentials, stack traces, or stored tokens to clients.

Writes spanning multiple tables use a transaction boundary. Money is represented as a decimal value object and never calculated with floating-point arithmetic.

## Request and error flow

```text
HTTP request
  → CORS/JSON/request-ID middleware
  → authentication middleware when required
  → compatibility route adapter
  → typed request DTO
  → application command/query handler
  → domain entity or policy
  → repository port
  → PDO repository
  → response mapper
  → JSON response
```

Typed exceptions are mapped centrally:

| Status | Meaning |
|---:|---|
| 400 | Malformed or invalid input |
| 401 | Missing or invalid credentials |
| 403 | Authenticated but not permitted or VIP restriction |
| 404 | Resource not found |
| 409 | Booking overlap or duplicate driver license |
| 422 | Valid request with failed business validation |
| 500 | Unexpected infrastructure failure |

Client responses contain safe messages and a request ID. Server logs contain structured context while omitting passwords and raw tokens.

## Persistence strategy

The existing `accountstable`, `userstable`, `carstable`, `bookingtable`, and `billingtable` schema is retained as the initial compatibility schema. All SQL moves behind typed PDO repositories. Schema fixes and indexes are introduced through explicit, reproducible database migrations or migration SQL, with the existing dump retained as seed/reference data.

The repository layer uses prepared statements for all values and fixed SQL for all identifiers. Database connections, transaction management, and clock access are injected rather than accessed through global constants.

## Swagger UI

The API exposes Swagger UI at `/docs`, backed by a checked-in OpenAPI 3.1 document at `docs/openapi/openapi.yaml`. The document describes every preserved operation, request body, authentication scheme, successful response, validation error, conflict, and authorization failure. Swagger UI is an HTTP documentation/test surface and has no dependency on domain code.

## Verification strategy

The refactor follows test-first development:

- Unit tests cover value objects, entities, domain policies, and application handlers.
- Repository integration tests use a disposable MySQL/MariaDB database.
- HTTP tests cover every preserved endpoint, authentication failures, validation failures, booking overlap, VIP restrictions, payment/VIP transitions, and archive operations.
- OpenAPI validation confirms the documented operations and schemas.
- PHPStan level 9, PSR-12 formatting, dependency/security checks, and the full test suite are local quality gates.
- GitHub Actions runs installation, static analysis, unit/integration tests, and OpenAPI validation.

Target test execution lanes remain bounded below 110 seconds wherever parallelization is introduced. Existing coverage is preserved, and no skipped/focused tests or weakened assertions are permitted.

## Migration and commit strategy

Migration proceeds in independently testable increments:

1. Add the design, Composer, autoloading, configuration, and application shell.
2. Add shared kernel primitives, HTTP middleware, errors, and PDO infrastructure.
3. Migrate Identity and Users.
4. Migrate Fleet and availability queries.
5. Migrate Bookings and transactional conflict rules.
6. Migrate Billing and VIP promotion.
7. Add compatibility adapters, Swagger UI, README attribution, CI, and cleanup.
8. Remove legacy scripts only after endpoint compatibility tests pass.

The work is split into at least 40 meaningful commits organized around coherent, independently reviewable changes. Each commit is testable where practical and contains only files relevant to its concern.

## Attribution

The new README will state that `@jeraldpangan` created the original Car Rental API work and that this repository contains the architectural refactor built on that foundation.
