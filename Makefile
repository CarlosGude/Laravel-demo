.DEFAULT_GOAL := help

.PHONY: help up down restart build ps logs shell artisan composer migrate migrate-fresh seed fresh key make-test test tinker npm node-up

help: ## Show this help
	@grep -E '^[a-zA-Z0-9_-]+:.*## ' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Start the containers (app, nginx, mysql, redis)
	docker compose up -d app nginx mysql redis

down: ## Stop the containers
	docker compose down

restart: ## Restart the containers
	docker compose restart

build: ## Rebuild the images
	docker compose build

ps: ## Show container status
	docker compose ps

logs: ## Follow logs from all services
	docker compose logs -f

shell: ## Open a shell inside the app container
	docker compose exec app sh

artisan: ## Run artisan, usage: make artisan cmd="route:list"
	docker compose exec app php artisan $(cmd)

composer: ## Run composer, usage: make composer cmd="require foo/bar"
	docker compose exec app composer $(cmd)

migrate: ## Run migrations
	docker compose exec app php artisan migrate

migrate-fresh: ## Recreate the database from scratch
	docker compose exec app php artisan migrate:fresh

seed: ## Run the seeders
	docker compose exec app php artisan db:seed

fresh: ## migrate:fresh + seed
	docker compose exec app php artisan migrate:fresh --seed

key: ## Generate the APP_KEY
	docker compose exec app php artisan key:generate

make-test: ## Create a test file, usage: make make-test cmd="ExampleTest"
	docker compose exec app php artisan make:test $(cmd)

test: ## Run the tests
	docker compose exec app php artisan test

tinker: ## Open tinker
	docker compose exec app php artisan tinker

node-up: ## Start the Vite dev server
	docker compose up -d node

npm: ## Run npm inside the node container, usage: make npm cmd="run build"
	docker compose run --rm node npm $(cmd)
