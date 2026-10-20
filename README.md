# Task Manager API

A small Task Manager REST API built with Laravel, used as a **framework-agnosticism demo**: the goal
isn't the to-do list itself, it's showing that architecture and engineering practice — hexagonal/DDD
layering, dependency inversion, static analysis, enforced boundaries, test strategy — transfer cleanly
between frameworks (in this case, from Symfony to Laravel).

See [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) for the full write-up of the decisions behind this
code and, for anyone coming from Symfony, an explicit mapping between the two ecosystems.

## What it does

- Email/password registration and login, issuing a [Sanctum](https://laravel.com/docs/sanctum) API
  token (`POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`).
- CRUD-ish task management scoped to the authenticated user (`GET /api/tasks`, `GET /api/tasks/{id}`,
  `POST /api/tasks`, `PUT /api/tasks/{id}`), with filtering (completion state, date range, category) and
  sorting.
- Tasks can be tagged with free-form categories, created on the fly per user.
- A simple `admin` / `user` role split — admins see every user's tasks, regular users only see their own.
- Self-documenting API via OpenAPI attributes, rendered with Swagger UI.

## Quick start (Docker)

The project ships with a full local environment: PHP-FPM 8.4, Nginx, MySQL 8.4, Redis, and a Node
container for Vite.

```bash
cp .env.example .env   # if it doesn't exist yet
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

The app is then available at `http://localhost:8000`, and the API docs at `http://localhost:8000/api/docs`.

**Services:**

| Service | Container      | Host port                    |
|---------|----------------|-------------------------------|
| App     | laravel-app    | — (php-fpm, behind nginx)      |
| Nginx   | laravel-nginx  | 8000                            |
| MySQL   | laravel-mysql  | 3306                            |
| Redis   | laravel-redis  | 6379                            |
| Node    | laravel-node   | 5173 (Vite dev server)          |

**Useful commands:**

```bash
docker compose exec app php artisan ...   # artisan
docker compose exec app composer ...      # composer
docker compose up -d node                 # start the Vite dev server
docker compose down                       # stop everything
docker compose down -v                    # stop and drop volumes (MySQL/Redis data)
```

A `Makefile` wraps the most common ones (`make help` to list them all):

```bash
make up                          # start containers
make artisan cmd="migrate"       # artisan migrate
make shell                       # shell inside the app container
make test                        # run the test suite
make down                        # stop
```

## Trying the API

A ready-to-import Postman collection lives in [`postman/`](https://github.com/CarlosGude/Laravel-demo/tree/main/postman): import both
`TaskManagerAPI.postman_collection.json` and `TaskManagerAPI.postman_environment.json`, select the
environment, then run **Auth → Register** (or **Login**) first — its test script stores the Sanctum
token automatically, so every other request authenticates itself. **Tasks → Create Task** likewise
stores the created task's id for **Get Task by Id** / **Update Task**.

## Quality tooling

```bash
composer test     # PHPUnit (Unit + Feature)
composer stan      # PHPStan / Larastan, level 9
composer deptrac    # verifies layer boundaries (Domain/Application/Infrastructure)
composer cs         # PHP CS Fixer, dry-run
composer cs-fix      # PHP CS Fixer, applies fixes
```

All four run in CI-equivalent form and are meant to be green at all times; `deptrac` in particular
fails the build if a commit introduces a dependency that breaks the architecture (e.g. a `Domain` class
reaching into Eloquent).

## Project layout

The codebase is organized by **bounded context** (`Task`, `User`), not by technical type. Each module is
internally layered Domain → Application → Infrastructure:

```
app/
├── Task/
│   ├── Domain/            # entities, value objects, repository interfaces — no framework code
│   ├── Application/       # use cases + request/response DTOs
│   └── Infrastructure/    # Eloquent models, repositories, HTTP controllers, routes, migrations
└── User/
    ├── Domain/
    ├── Application/
    └── Infrastructure/
```

Full rationale, trade-offs, and a Symfony-to-Laravel concept map live in
[`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Built with

Laravel 13, PHP 8.4+, MySQL, Sanctum, darkaonline/l5-swagger (OpenAPI), PHPStan/Larastan, Deptrac, PHP CS
Fixer, PHPUnit.
