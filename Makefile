.DEFAULT_GOAL := help

.PHONY: help up down restart build ps logs shell artisan composer migrate migrate-fresh seed fresh key test tinker npm node-up

help: ## Muestra esta ayuda
	@grep -E '^[a-zA-Z0-9_-]+:.*## ' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Levanta los contenedores (app, nginx, mysql, redis)
	docker compose up -d app nginx mysql redis

down: ## Detiene los contenedores
	docker compose down

restart: ## Reinicia los contenedores
	docker compose restart

build: ## Reconstruye las imágenes
	docker compose build

ps: ## Muestra el estado de los contenedores
	docker compose ps

logs: ## Sigue los logs de todos los servicios
	docker compose logs -f

shell: ## Abre un shell dentro del contenedor app
	docker compose exec app sh

artisan: ## Ejecuta artisan, usar: make artisan cmd="route:list"
	docker compose exec app php artisan $(cmd)

composer: ## Ejecuta composer, usar: make composer cmd="require foo/bar"
	docker compose exec app composer $(cmd)

migrate: ## Corre las migraciones
	docker compose exec app php artisan migrate

migrate-fresh: ## Recrea la base de datos desde cero
	docker compose exec app php artisan migrate:fresh

seed: ## Corre los seeders
	docker compose exec app php artisan db:seed

fresh: ## migrate:fresh + seed
	docker compose exec app php artisan migrate:fresh --seed

key: ## Genera la APP_KEY
	docker compose exec app php artisan key:generate

test: ## Corre los tests
	docker compose exec app php artisan test

tinker: ## Abre tinker
	docker compose exec app php artisan tinker

node-up: ## Levanta el servidor de Vite
	docker compose up -d node

npm: ## Ejecuta npm dentro del contenedor node, usar: make npm cmd="run build"
	docker compose run --rm node npm $(cmd)
