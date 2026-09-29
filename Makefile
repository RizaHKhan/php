.PHONY: help up down refresh build rebuild restart logs ps shell db-shell db-logs migrate seed db-status clean reset-db

COMPOSE := docker compose
APP_SERVICE := app
DB_SERVICE := db

help:
	@echo "Available targets:"
	@echo "  make up        Start the PHP app stack in the background"
	@echo "  make down      Stop and remove app containers"
	@echo "  make refresh   Recreate containers and rebuild images"
	@echo "  make build     Build service images"
	@echo "  make rebuild   Build images without cache"
	@echo "  make restart   Restart the stack"
	@echo "  make logs      Follow container logs"
	@echo "  make ps        Show container status"
	@echo "  make shell     Open a shell in the PHP app container"
	@echo "  make db-shell  Open psql in the Postgres container"
	@echo "  make db-logs   Follow Postgres logs"
	@echo "  make migrate   Apply pending database migrations"
	@echo "  make seed      Apply pending database seeders"
	@echo "  make db-status Show migration and seeder status"
	@echo "  make clean     Stop containers and remove orphans"
	@echo "  make reset-db  Remove containers and database volume"

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

refresh:
	$(COMPOSE) up -d --build --force-recreate

build:
	$(COMPOSE) build

rebuild:
	$(COMPOSE) build --no-cache

restart: down up

logs:
	$(COMPOSE) logs -f

ps:
	$(COMPOSE) ps

shell:
	$(COMPOSE) exec $(APP_SERVICE) sh

db-shell:
	$(COMPOSE) exec $(DB_SERVICE) psql -U app -d app

db-logs:
	$(COMPOSE) logs -f $(DB_SERVICE)

migrate:
	$(COMPOSE) exec $(APP_SERVICE) php bin/db.php migrate

seed:
	$(COMPOSE) exec $(APP_SERVICE) php bin/db.php seed

db-status:
	$(COMPOSE) exec $(APP_SERVICE) php bin/db.php status

clean:
	$(COMPOSE) down --remove-orphans

reset-db:
	$(COMPOSE) down -v --remove-orphans
