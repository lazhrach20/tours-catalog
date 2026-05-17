# Tours Catalog — developer entry point (run from repository root)
.DEFAULT_GOAL := help

SHELL := /bin/sh
COMPOSE := docker compose
API_SERVICE := api
WEB_SERVICE := web
DB_SERVICE := db
EMBEDDINGS_SERVICE := embeddings

# Optional: docker compose.override.yml for dev bind mounts
ifneq (,$(wildcard docker-compose.override.yml))
  export COMPOSE_FILE := docker-compose.yml:docker-compose.override.yml
endif

.PHONY: help env setup build pull up dev down restart ps logs health \
        migrate migrate-fresh seed seed-if-empty fresh db-shell db-backup \
        api-shell artisan tinker cache-clear storage-link reindex-embeddings \
        web-shell web-install web-lint web-build \
        embeddings-up embeddings-logs embed-text \
        test test-api lint format ci clean clean-all urls

help: ## List targets (this help)
	@printf "\nTours Catalog — available make targets\n\n"
	@grep -E '^[a-zA-Z0-9_.-]+:.*##' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*## "}; {printf "  \033[36m%-22s\033[0m %s\n", $$1, $$2}'
	@printf "\nExamples:\n  make setup\n  make logs SVC=api\n  make artisan CMD=\"route:list\"\n  make embed-text TEXT=\"горный тур\"\n\n"

env: ## Copy .env.example → .env (never overwrites existing)
	@if [ -f .env ]; then \
		echo ".env already exists — skipped"; \
	else \
		cp .env.example .env; \
		echo "Created .env from .env.example"; \
	fi

setup: env build up ## First-time setup: env, build, up, migrate, seed
	@echo ""
	@echo "==> Waiting for services to become healthy..."
	@$(MAKE) health
	@echo ""
	@echo "==> Running migrations..."
	@$(MAKE) migrate
	@echo ""
	@echo "==> Seeding database..."
	@$(MAKE) seed
	@echo ""
	@echo "==> Setup complete"
	@$(MAKE) urls
	@echo ""
	@echo "NOTE: First embeddings start may take several minutes while the Hugging Face model downloads."

build: ## Build or rebuild Docker images
	$(COMPOSE) build

pull: ## Pull upstream base images
	$(COMPOSE) pull

up: env ## Start stack in background
	$(COMPOSE) up -d
	@echo ""
	@$(MAKE) urls
	@echo "Run 'make logs' to follow logs, or 'make health' to verify readiness."

dev: env ## Start stack in foreground (logs attached)
	$(COMPOSE) up

down: ## Stop containers
	$(COMPOSE) down

restart: down up ## Restart stack

ps: ## Show container status
	$(COMPOSE) ps

logs: ## Follow logs (optional: make logs SVC=api)
	@if [ -n "$(SVC)" ]; then \
		$(COMPOSE) logs -f --tail=200 $(SVC); \
	else \
		$(COMPOSE) logs -f --tail=200; \
	fi

health: ## Check db, api, embeddings, and web health
	@./scripts/healthcheck.sh

migrate: ## Run Laravel migrations (pending only)
	$(COMPOSE) exec -T $(API_SERVICE) php artisan migrate --force

migrate-fresh: ## Drop all tables and re-migrate (requires CONFIRM=1)
	@if [ "$(CONFIRM)" != "1" ]; then \
		echo "Refusing migrate-fresh without CONFIRM=1"; \
		exit 1; \
	fi
	$(COMPOSE) exec -T $(API_SERVICE) php artisan migrate:fresh --force

seed: ## Run database seeders
	$(COMPOSE) exec -T $(API_SERVICE) php artisan db:seed --force

seed-if-empty: ## Seed only when tours table is empty
	$(COMPOSE) exec -T $(API_SERVICE) php artisan catalog:seed-if-empty

fresh: ## Fresh DB + seed (dev; passes CONFIRM=1 to migrate-fresh)
	@$(MAKE) migrate-fresh CONFIRM=1
	@$(MAKE) seed

db-shell: ## Open psql in db container
	$(COMPOSE) exec $(DB_SERVICE) psql -U "$${DB_USERNAME:-tours}" -d "$${DB_DATABASE:-tours_catalog}"

db-backup: ## Dump database to ./storage/backups/
	@mkdir -p storage/backups
	$(COMPOSE) exec -T $(DB_SERVICE) pg_dump -U "$${DB_USERNAME:-tours}" "$${DB_DATABASE:-tours_catalog}" > "storage/backups/tours_$$(date +%Y%m%d_%H%M%S).sql"
	@echo "Backup written to storage/backups/"

api-shell: ## Interactive shell in api container
	$(COMPOSE) exec $(API_SERVICE) sh

artisan: ## Run artisan (make artisan CMD="route:list")
	@if [ -z "$(CMD)" ]; then \
		echo 'Usage: make artisan CMD="migrate:status"'; \
		exit 1; \
	fi
	$(COMPOSE) exec -T $(API_SERVICE) php artisan $(CMD)

tinker: ## Laravel tinker REPL
	$(COMPOSE) exec $(API_SERVICE) php artisan tinker

cache-clear: ## Clear Laravel caches
	$(COMPOSE) exec -T $(API_SERVICE) php artisan optimize:clear

storage-link: ## Create public storage symlink
	$(COMPOSE) exec -T $(API_SERVICE) php artisan storage:link

reindex-embeddings: ## Recompute embeddings for published tours
	$(COMPOSE) exec -T $(API_SERVICE) php artisan tours:reindex-embeddings

web-shell: ## Interactive shell in web container
	$(COMPOSE) exec $(WEB_SERVICE) sh

web-install: ## Install frontend dependencies (pnpm in apps/web)
	$(COMPOSE) exec $(WEB_SERVICE) sh -c "corepack enable pnpm && pnpm install"

web-lint: ## Run frontend lint/typecheck when configured
	$(COMPOSE) exec $(WEB_SERVICE) sh -c "corepack enable pnpm && pnpm run lint"

web-build: ## Production build of Vike app
	$(COMPOSE) exec $(WEB_SERVICE) sh -c "corepack enable pnpm && pnpm run build"

embeddings-up: env ## Start only db + embeddings (debug semantic search)
	$(COMPOSE) up -d $(DB_SERVICE) $(EMBEDDINGS_SERVICE)

embeddings-logs: ## Follow embeddings service logs
	$(COMPOSE) logs -f --tail=200 $(EMBEDDINGS_SERVICE)

embed-text: ## Smoke-test POST /embed (make embed-text TEXT="hello")
	@if [ -z "$(TEXT)" ]; then \
		echo 'Usage: make embed-text TEXT="sample tour description"'; \
		exit 1; \
	fi
	@$(COMPOSE) exec -T $(EMBEDDINGS_SERVICE) curl -fsS -X POST http://127.0.0.1:8000/embed \
		-H 'Content-Type: application/json' \
		-d "{\"text\":\"$(TEXT)\"}"

test: ## Run Laravel PHPUnit/Pest tests
	$(COMPOSE) exec -T $(API_SERVICE) php artisan test

test-api: test ## Alias for backend tests

lint: ## Run Pint + frontend lint when available
	-$(COMPOSE) exec -T $(API_SERVICE) sh -c 'if [ -f vendor/bin/pint ]; then vendor/bin/pint --test; else echo "pint not installed yet (Laravel scaffold pending)"; fi'
	-$(MAKE) web-lint

format: ## Auto-format PHP and frontend
	-$(COMPOSE) exec -T $(API_SERVICE) sh -c 'if [ -f vendor/bin/pint ]; then vendor/bin/pint; fi'
	-$(COMPOSE) exec $(WEB_SERVICE) sh -c "corepack enable pnpm && pnpm exec prettier -w . 2>/dev/null || true"

ci: lint test web-build ## Local CI: lint + test + web production build

clean: ## Stop stack and remove orphans (keeps named volumes)
	$(COMPOSE) down --remove-orphans

clean-all: ## Stop stack and remove volumes (requires CONFIRM=1)
	@if [ "$(CONFIRM)" != "1" ]; then \
		echo "Refusing clean-all without CONFIRM=1"; \
		exit 1; \
	fi
	$(COMPOSE) down -v --remove-orphans

urls: ## Print local service URLs
	@./scripts/urls.sh
