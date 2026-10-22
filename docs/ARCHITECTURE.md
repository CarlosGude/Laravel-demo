# Architecture decisions

This is a demo project. The task manager domain is intentionally small; the point is to show that a
set of engineering habits — layering, dependency inversion, enforced boundaries, a matching test
strategy — are not Symfony-specific, they're just good practice that happens to map onto whatever
framework is at hand. This document explains each decision and, for readers coming from Symfony, what
the nearest equivalent there is.

## 1. Organized by bounded context, not by technical type

Instead of Laravel's default `app/Models`, `app/Http/Controllers`, `app/Providers`, the code is split
first by domain module (`app/Task`, `app/User`), and only then by layer:

```
app/Task/
├── Domain/
├── Application/
└── Infrastructure/
```

**Why:** grouping by technical type scales badly — "all controllers" is not a unit anyone works in for a
whole day, "everything about tasks" is. It also makes module boundaries visible in the filesystem
instead of just in people's heads, and it's what lets each module be deleted or extracted wholesale.

**Symfony equivalent:** this is the same instinct as a bundle, or a `src/<Domain>` split under
Symfony Flex's bundle-less setup — grouping by business capability rather than by `Controller/`,
`Entity/`, `Repository/` folders.

## 2. Domain / Application / Infrastructure layering, per module

Each module has three layers:

- **Domain** — entities (`Task`, `User`, `Category`), value objects (`UserRole`, `TaskFilter`,
  `TaskSortField`), repository *interfaces*, and domain exceptions. Nothing here imports Laravel,
  Eloquent, or any other framework class.
- **Application** — use cases (`CreateTaskUseCase`, `LoginUserUseCase`, ...) that orchestrate domain
  objects through the repository interfaces, plus request/response DTOs. A use case has one public
  `execute()` method and no HTTP or persistence concerns.
- **Infrastructure** — everything that talks to the outside world: Eloquent models, repository
  *implementations*, HTTP controllers, routes, migrations, the password hasher implementation.

**Why:** the dependency only ever points inward (Infrastructure → Application → Domain). The domain
model can be read, tested, and reasoned about without a database, an HTTP request, or a service
container in sight. Swapping MySQL for Postgres, or Sanctum for a different auth mechanism, is an
Infrastructure-only change.

**Enforcement, not just convention:** this is checked on every run by [Deptrac](https://deptrac.github.io/)
(`deptrac.php`), with the rule that matters most being:

```php
// Domain must stay pure: no dependency on Application, Infrastructure or the framework.
Ruleset::forLayer($domain),
```

`composer deptrac` fails the build the moment a `Domain` class imports anything from `Infrastructure` or
from `Illuminate\*`. Layer discipline that isn't enforced by tooling erodes within a few PRs; this makes
the boundary a CI fact rather than a code-review opinion.

**Symfony equivalent:** this is hexagonal/clean architecture as commonly done with Symfony + Doctrine —
domain entities that know nothing about Doctrine's `#[ORM\Entity]` mapping (or mapped via separate XML/
YAML so the class stays clean), application services, and infrastructure adapters. Deptrac itself is a
Symfony-ecosystem tool used here unchanged — one of the few pieces of tooling that moved over as-is.

## 3. Eloquent models are not the domain entities

`TaskModel` (Infrastructure) and `Task` (Domain) are two different classes. The model knows how to
persist; the entity knows the business rules. They convert into each other explicitly:

```php
// TaskModel (Infrastructure)
public function toEntity(): Task { /* Eloquent attributes -> immutable Task */ }
public function fillFromEntity(Task $task): static { /* immutable Task -> Eloquent attributes */ }
```

Domain entities are `final readonly` classes with a private constructor and named factory methods
(`createNew()`, `createWithId()`, `withChanges()`), so every state transition is explicit and the object
is never left half-constructed:

```php
$task = Task::createNew(userId: $id, name: $title, description: $description);
// ... later
$task = $task->withChanges(name: $newTitle, description: $newDescription, completed: true, updatedAt: now());
```

**Why:** Eloquent models are built to be mutable, array-accessible, lazy-loading, framework-coupled
records — that's what makes them convenient for persistence, and exactly why they're the wrong shape for
business rules. Keeping them separate means a `Task` can be constructed in a unit test with no database
connection at all, and that renaming an Eloquent column never touches a use case's logic.

**Symfony equivalent:** Doctrine entities are closer to domain objects by default (no magic attribute
array, real typed properties), which is arguably Doctrine's biggest ergonomic edge over Eloquent for DDD.
Here the same separation is reconstructed by hand because Eloquent doesn't offer it for free — the trade
is more mapping boilerplate (`toEntity`/`fillFromEntity`) in exchange for keeping Eloquent's
conveniences (query builder, relationships, factories) for the parts of the app that actually want them.

## 4. Column-name enums instead of magic strings

```php
enum TaskColumn: string
{
    case UserId = 'user_id';
    case Name = 'name';
    // ...
}
```

Used everywhere a column name would otherwise be a bare string (`fillable`, `where()`, `orderBy()`).
**Why:** a typo in `'completd'` fails silently at the SQL layer; `TaskColumn::Completed` fails at
compile/static-analysis time and is renameable with IDE refactoring tools.

## 5. Repository interfaces live in Domain, implementations in Infrastructure

```php
// Domain
interface TaskRepositoryInterface { public function findAll(?TaskFilter $filter = null): TaskCollection; /* ... */ }

// Infrastructure
final class MySqlTaskRepository implements TaskRepositoryInterface { /* Eloquent queries */ }
```

Each module's `ServiceProvider` binds the two:

```php
// TaskServiceProvider::register()
$this->app->bind(TaskRepositoryInterface::class, MySqlTaskRepository::class);
```

**Why:** use cases depend on the interface, never on `MySqlTaskRepository` or Eloquent directly — this
is the Dependency Inversion Principle applied at the module boundary. It's also why Application-layer
use cases can be unit-tested with a Mockery mock of the interface instead of a database — no in-memory
fake implementation is maintained, since a mock expressed as "expect this call, return this value" is
less code to keep in sync than a second, hand-written implementation of the same interface.

The same pattern is used for the password hasher — `PasswordHasherInterface` in Domain,
`LaravelPasswordHasher` (wrapping `Hash::make`/`Hash::check`) in Infrastructure — so the hashing
algorithm is swappable and `LoginUserUseCase`/`RegisterUserUseCase` can be tested without touching
bcrypt.

**Symfony equivalent:** exactly the shape of Symfony's service container — an interface type-hinted in
a service, wired to a concrete implementation in `services.yaml` (or autowired by convention). Laravel's
`ServiceProvider::register()` binding an interface to a class is the same idea with a different syntax;
the module owning and registering its own bindings (rather than one global `AppServiceProvider` wiring
everything) mirrors a bundle's `services.yaml` scoping its own services.

## 6. Modules own their routes, migrations, and bindings

Each module's `ServiceProvider` loads its own migrations and the root `routes/api.php` just requires
each module's route file:

```php
// TaskServiceProvider::boot()
$this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

// routes/api.php
require_once __DIR__.'/../app/User/Infrastructure/Routes/api.php';
require_once __DIR__.'/../app/Task/Infrastructure/Routes/api.php';
```

**Why:** a module owns its own routes and migrations — deleting `app/Task` removes its routes and
migrations with it, instead of leaving orphaned entries in a central `routes/api.php` or
`database/migrations/`. This is one-directional, not full isolation: `Task` depends on `User`
(`RequestingUserFactory` reads `App\User\Domain\Entity\UserRole` and `App\User\Infrastructure\Models\UserModel`
to turn the authenticated request into a `RequestingUser`), because task ownership and admin checks are
meaningless without knowing who the user is. `User` has no dependency back on `Task`. Deleting `Task`
is safe; deleting `User` is not — that asymmetry matches the actual domain relationship (tasks belong
to users; users don't belong to tasks) and isn't itself a problem, but it does mean "modules are
extractable" should be read as "each module's routes/migrations/bindings are self-registering," not as
"every module is fully independent of every other." The
`migrations:modules` console command (`app/Console/Commands/ListModuleMigrationsCommand.php`) exists
specifically to give a single cross-module view back, since migrations are no longer in one folder.

**Symfony equivalent:** this is what a bundle gives you for free (its own `Resources/config/routes.yaml`
and `Resources/migrations`) — here it's assembled manually because plain Laravel doesn't have a bundle
concept, only a flat `routes/` and `database/migrations/`.

## 7. HTTP controllers talk to use cases through port models, not directly

```php
final readonly class CreateTaskController {
    public function __invoke(Request $request): JsonResponse {
        $portModel = CreateTaskPortModel::fromHttpRequest($request);
        $requestingUser = RequestingUserFactory::fromRequest($request);
        $task = $this->useCase->execute($portModel->toApplicationRequest(), $requestingUser);
        return response()->json(CreateTaskPortModel::toHttpResponse($task), 201);
    }
}
```

`CreateTaskPortModel` converts an HTTP `Request` into the Application-layer `TaskRequest` DTO, and the
`TaskResponse` DTO into a plain array for `response()->json()`. The use case itself never sees an
`Illuminate\Http\Request` or produces a `JsonResponse`.

**Why:** the same `CreateTaskUseCase` could be invoked from a console command, a queue job, or a GraphQL
resolver without change — the HTTP shape is an adapter (a "port" in hexagonal-architecture terms), not
the application's native language. It also means use cases are tested with plain DTOs, no
`Illuminate\Http\Request` mocking required.

**Symfony equivalent:** this is the same role a request/response DTO plays with Symfony's
`#[MapRequestPayload]` or a manual form/DTO mapping layer in front of a service — keeping the
controller a thin translation layer instead of where business logic lives.

## 8. Authorization lives in the Application layer, not in middleware

Ownership and role checks (`!$requestingUser->isAdmin && !$task->isOwnedBy($requestingUser->id)`) happen
inside `ListOneTaskUseCase` and `UpdateTaskUseCase`, not in a Laravel policy/gate or route middleware.
Admins get an unfiltered `TaskFilter` in `ListAllTaskUseCase`; everyone else is scoped to their own
`userId`.

**Why:** these are business rules ("a task belongs to its owner", "admins can see everything") that
apply regardless of the transport — the same rule needs to hold whether a request came in over HTTP, a
console command, or a future internal RPC call. Laravel policies are a fine tool, but they're an
HTTP-layer construct; putting the rule there would silently stop applying to any non-HTTP entry point.

## 9. Factory-name resolution is one global convention, not per-model overrides

```php
// AppServiceProvider::boot()
Factory::guessFactoryNamesUsing(function (string $modelName): string {
    $basename = Str::beforeLast(class_basename($modelName), 'Model');
    return 'Database\\Factories\\'.$basename.'Factory';
});
```

Because every Eloquent class here is suffixed `Model` (`TaskModel`, `UserModel`, `CategoryModel`) to
keep it visually distinct from the Domain entity of the same concept, Laravel's default
"strip nothing, append `Factory`" convention can't find `TaskFactory` from `TaskModel`. The fix is one
global resolver, not a `newFactory()` override copy-pasted onto every model.

**Why:** a per-model override is the kind of thing that's correct the first three times and forgotten
the fourth; a single naming-convention fix in one place can't be forgotten, and it keeps the Eloquent
models themselves free of anything that only exists for the benefit of tests.

## 10. Testing mirrors the architecture

```
tests/Unit/<Module>/Domain/...          — entities, value objects: no framework, no DB
tests/Unit/<Module>/Application/...     — use cases: Mockery mocks of repository interfaces, no DB
tests/Feature/<Module>/Infrastructure/... — controllers + repositories: real DB, real HTTP kernel
```

**Why:** this isn't a naming convention for its own sake — it's a direct consequence of the dependency
direction in §2. A `Domain` or `Application` test that needed a database would mean the layering had
already leaked. `Infrastructure` is deliberately the only layer whose tests are slow and need
`RefreshDatabase` / a real repository, because it's the only layer that's actually allowed to know a
database exists.

## 11. Static analysis and formatting are part of the architecture, not an afterthought

- **PHPStan (Larastan) at level 9** — the strictest level, including on Eloquent models via Larastan's
  Laravel-aware rules.
- **Deptrac** — see §2; the layer rules are the one check that specifically protects this document's
  decisions from drifting.
- **PHP CS Fixer**, `@PSR12` + `@PSR12:risky`, with import sorting and short array syntax enforced.

All three are wired as `composer` scripts (`composer stan`, `composer deptrac`, `composer cs`) so they
run identically locally and in CI, with no IDE-specific configuration required to reproduce a failure.

## 12. API docs generated from code, not maintained separately

Each controller carries its OpenAPI attributes directly (`#[OA\Post(...)]`), e.g.
`CreateTaskController`, alongside dedicated schema classes (`TaskSchema`, `TaskRequestSchema`,
`UserSchema`). `darkaonline/l5-swagger` scans `app/` and serves the result as Swagger UI at
`/api/docs`.

**Why:** documentation colocated with the route it describes is documentation that has a chance of
staying correct — a separate hand-maintained OpenAPI YAML file drifts from the code within a sprint.

**Symfony equivalent:** the same trade NelmioApiDocBundle or API Platform make — annotate the
controller/resource once, generate the spec, rather than hand-author it.

## 13. Public identifiers are UUIDs, not the database's auto-increment id

`Task` and `User` both carry two identifiers: an internal `?int $id` (the auto-increment primary
key, assigned by the database on insert) and a `string $uuid` (assigned by the domain itself, in
`createNew()`, before the entity is ever persisted). Routes, JSON responses and repository lookups
(`findByUuid()`) only ever deal in the uuid; the int id never leaves `Infrastructure` — not even the
`TaskResponse`/`UserResponse` DTOs see it.

**Why:** an auto-increment id leaks the row count and insertion order, and lets a client enumerate
resources by guessing the next integer. A uuid generated by the domain also means identity exists
the moment an entity is created, not only after a round-trip to the database — closer to how an
aggregate root is supposed to behave.

**Symfony equivalent:** the same reasoning as the `symfony/uid` component's `UuidV4`/`UuidV7` as the
exposed identifier, while Doctrine still uses an auto-increment or sequence-backed id internally.

## Known limitations

These aren't architectural decisions, they're gaps — listed here instead of left for a reader to find:

- **Sanctum tokens expire after `SANCTUM_TOKEN_EXPIRATION_MINUTES`** (default 1440, i.e. 24h), but
  there's still no refresh flow — a client must log in again once its token expires. Revocation beyond
  expiry is still limited to `LogoutUserController` deleting the current token.
- **`Task` depends on `User`** for authorization (see §6) — the two modules are not symmetrically
  independent.

## What this demo deliberately does *not* have

- No event sourcing, no CQRS, no message bus — the domain is simple enough that none of these would
  earn their complexity here. Layering and dependency inversion are kept because they pay for
  themselves even in a small app; the heavier patterns are left out because they wouldn't.
- No API versioning strategy, no rate limiting, no refresh tokens — out of scope for a demo whose point
  is architecture, not a production-ready auth system.
