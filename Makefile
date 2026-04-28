.PHONY: help build up down restart logs clean migrate seed test

help:
	@echo "MicroApp Docker Commands"
	@echo "========================"
	@echo "make up              - Start all services"
	@echo "make down            - Stop all services"
	@echo "make restart         - Restart all services"
	@echo "make logs            - View logs (all services)"
	@echo "make build           - Build Docker images"
	@echo "make clean           - Remove all containers and volumes"
	@echo "make migrate         - Run database migrations"
	@echo "make seed            - Seed database"
	@echo "make cache-clear     - Clear Laravel cache"
	@echo "make queue-work      - View queue worker logs"
	@echo "make backend-shell   - Access backend container shell"
	@echo "make frontend-shell  - Access frontend container shell"
	@echo "make db-shell        - Access PostgreSQL shell"
	@echo "make redis-cli       - Access Redis CLI"

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose stop

restart:
	docker-compose restart

logs:
	docker-compose logs -f

logs-backend:
	docker-compose logs -f backend

logs-frontend:
	docker-compose logs -f frontend

logs-queue:
	docker-compose logs -f queue

logs-db:
	docker-compose logs -f postgres

clean:
	docker-compose down -v

migrate:
	docker-compose exec backend php artisan migrate

migrate-fresh:
	docker-compose exec backend php artisan migrate:refresh

seed:
	docker-compose exec backend php artisan db:seed

migrate-seed:
	docker-compose exec backend php artisan migrate:refresh --seed

cache-clear:
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan view:clear

test:
	docker-compose exec backend php artisan test

test-watch:
	docker-compose exec backend php artisan test --watch

backend-shell:
	docker-compose exec backend sh

frontend-shell:
	docker-compose exec frontend sh

db-shell:
	docker-compose exec postgres psql -U postgres -d MicroApp_db

redis-cli:
	docker-compose exec redis redis-cli

ps:
	docker-compose ps

install-backend-deps:
	docker-compose exec backend composer install

install-frontend-deps:
	docker-compose exec frontend npm ci

npm-install:
	docker-compose exec frontend npm install
