.PHONY: help up down restart build logs logs-app logs-horizon shell shell-node tinker \
        install migrate migrate-fresh seed test test-coverage pint phpstan check \
        npm-install npm-dev npm-build npm-test npm-lint \
        db-reset horizon horizon-restart horizon-status horizon-pause horizon-continue fresh

# Default target
help:
	@echo "Contractly Development Commands"
	@echo ""
	@echo "Setup (run once):"
	@echo "  make init            First time setup (env + build + up)"
	@echo "  make setup           Full setup (env + build + up + install + migrate)"
	@echo "  make env             Create .env from .env.example (if not exists)"
	@echo "  make env-all         Create all .env files (root, backend, frontend)"
	@echo ""
	@echo "Docker:"
	@echo "  make up              Start all containers"
	@echo "  make down            Stop all containers"
	@echo "  make restart         Restart all containers"
	@echo "  make build           Rebuild Docker images"
	@echo "  make logs            View all container logs"
	@echo "  make logs-app        View PHP app logs"
	@echo "  make logs-horizon    View Horizon logs"
	@echo ""
	@echo "Shell Access:"
	@echo "  make shell           Open shell in PHP container"
	@echo "  make shell-node      Open shell in Node container"
	@echo "  make tinker          Open Laravel Tinker"
	@echo ""
	@echo "Backend (Laravel):"
	@echo "  make install         Install all dependencies"
	@echo "  make migrate         Run database migrations"
	@echo "  make migrate-fresh   Fresh migration (drops all tables)"
	@echo "  make seed            Run database seeders"
	@echo "  make fresh           Fresh migrate + seed"
	@echo "  make test            Run PHP tests"
	@echo "  make test-coverage   Run tests with coverage"
	@echo "  make pint            Format PHP code with Pint"
	@echo "  make phpstan         Run PHPStan static analysis"
	@echo "  make check           Run all checks (PHPStan + tests)"
	@echo ""
	@echo "Frontend (Vue):"
	@echo "  make npm-install     Install npm packages"
	@echo "  make npm-dev         Start Vite dev server"
	@echo "  make npm-build       Build for production"
	@echo "  make npm-test        Run Vitest"
	@echo "  make npm-lint        Lint TypeScript/Vue"
	@echo ""
	@echo "Database:"
	@echo "  make db-reset        Drop, migrate, and seed"
	@echo ""
	@echo "Horizon (Queue):"
	@echo "  make horizon         Start Horizon (foreground)"
	@echo "  make horizon-restart Restart Horizon container"
	@echo "  make horizon-status  Show Horizon status"
	@echo "  make horizon-pause   Pause job processing"
	@echo "  make horizon-continue Resume job processing"
	@echo ""
	@echo "  Dashboard: http://localhost/horizon"

# ============================================
# Docker Commands
# ============================================

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

logs-horizon:
	docker compose logs -f horizon

# ============================================
# Shell Access
# ============================================

shell:
	docker compose exec app sh

shell-node:
	docker compose exec node sh

tinker:
	docker compose exec app php artisan tinker

# ============================================
# Installation
# ============================================

install: composer-install npm-install key-generate

composer-install:
	docker compose exec app composer install

key-generate:
	docker compose exec app php artisan key:generate --ansi

# ============================================
# Backend Commands
# ============================================

migrate:
	docker compose exec app php artisan migrate

migrate-fresh:
	docker compose exec app php artisan migrate:fresh

seed:
	docker compose exec app php artisan db:seed

fresh:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app php artisan test

test-coverage:
	docker compose exec app php artisan test --coverage

pint:
	docker compose exec app ./vendor/bin/pint

phpstan:
	docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M

check:
	@echo "Running all checks..."
	@echo ""
	@echo "=== PHPStan ===" && docker compose exec -T app ./vendor/bin/phpstan analyse --memory-limit=512M
	@echo ""
	@echo "=== Backend Tests ===" && docker compose exec -T app php artisan test
	@echo ""
	@echo "=== Frontend Tests ===" && docker compose exec -T node npm run test -- --run
	@echo ""
	@echo "All checks passed!"

# ============================================
# Frontend Commands
# ============================================

npm-install:
	docker compose exec node npm install

npm-dev:
	docker compose exec node npm run dev

npm-build:
	docker compose exec node npm run build

npm-test:
	docker compose exec node npm run test

npm-lint:
	docker compose exec node npm run lint

# ============================================
# Database Commands
# ============================================

db-reset:
	docker compose exec app php artisan migrate:fresh --seed

# ============================================
# Horizon Commands
# ============================================

horizon:
	docker compose exec app php artisan horizon

horizon-restart:
	docker compose restart horizon

horizon-status:
	docker compose exec app php artisan horizon:status

horizon-pause:
	docker compose exec app php artisan horizon:pause

horizon-continue:
	docker compose exec app php artisan horizon:continue

horizon-terminate:
	docker compose exec app php artisan horizon:terminate

# ============================================
# Artisan Shortcuts
# ============================================

cache-clear:
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

optimize:
	docker compose exec app php artisan optimize

# ============================================
# Initial Setup (run once)
# ============================================

env:
	@if [ -f .env ]; then \
		echo ".env already exists, skipping..."; \
	else \
		cp .env.example .env; \
		echo ".env created from .env.example"; \
	fi

env-backend:
	@if [ -f backend/.env ]; then \
		echo "backend/.env already exists, skipping..."; \
	else \
		if [ -f backend/.env.example ]; then \
			cp backend/.env.example backend/.env; \
			echo "backend/.env created from backend/.env.example"; \
		else \
			echo "backend/.env.example not found, skipping..."; \
		fi \
	fi

env-frontend:
	@if [ -f frontend/.env ]; then \
		echo "frontend/.env already exists, skipping..."; \
	else \
		if [ -f frontend/.env.example ]; then \
			cp frontend/.env.example frontend/.env; \
			echo "frontend/.env created from frontend/.env.example"; \
		else \
			echo "frontend/.env.example not found, skipping..."; \
		fi \
	fi

env-all: env env-backend env-frontend

init: env-all build up
	@echo "Waiting for containers to start..."
	@sleep 5
	@echo ""
	@echo "Containers started!"
	@echo "  Frontend: http://localhost"
	@echo "  API:      http://localhost/api"
	@echo "  Mailhog:  http://localhost:8025"
	@echo ""
	@echo "Next steps:"
	@echo "  1. Initialize Laravel:  make laravel-init"
	@echo "  2. Initialize Vue:      make vue-init"

setup: env-all build up
	@echo "Waiting for containers to start..."
	@sleep 5
	@make install
	@make migrate
	@echo ""
	@echo "Setup complete!"
	@echo "  Frontend: http://localhost"
	@echo "  API:      http://localhost/api"
	@echo "  Mailhog:  http://localhost:8025"
