# Tours Catalog

Monorepo for a tours catalog with semantic search, Yandex Maps routes, and an admin panel. Stack: **Vue 3 + Vike** (web), **Laravel 11** (API), **PostgreSQL 16 + pgvector**, **FastAPI embeddings** (internal).

## Quick start

```bash
git clone <repo-url> tours-catalog
cd tours-catalog
make setup
```

Then open the storefront URL printed at the end (default [http://localhost:3000](http://localhost:3000)).

`make setup` runs: `env` → `build` → `up` → `migrate` → `seed`. For day-to-day work use `make up` or `make dev` (foreground logs).

All commands are listed in **`make help`**.

## Requirements

| Tool | Version / notes |
|------|-----------------|
| Docker Engine | 24+ with Compose v2 (`docker compose`) |
| Make | GNU Make 4+ |
| RAM | 8 GB+ recommended (embeddings model) |
| Windows | **WSL2** strongly recommended; alternatively Git Bash + Docker Desktop |

Optional: [pnpm](https://pnpm.io/) for root scripts (`pnpm run dev` delegates to `make dev`).

### Windows (WSL2)

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop/) with WSL2 backend.
2. Clone the repo inside your WSL distro (`\\wsl$\...`), not only on `C:\` via UNC paths.
3. Install Make: `sudo apt install make`
4. Run `make setup` from the repo root in WSL.

## Embeddings cold start

The `embeddings` service downloads a Hugging Face sentence-transformers model on first run. This can take **several minutes** and uses significant disk space (cached in the `embeddings_cache` Docker volume).

- `embeddings` is **not** published to your host — only `api` and other containers reach it at `EMBEDDINGS_URL` (default `http://embeddings:8000`).
- Watch progress: `make embeddings-logs`
- Smoke test: `make embed-text TEXT="горный тур по Кавказу"`

## Local development overrides

Copy the example override for hot reload bind mounts:

```bash
cp docker-compose.override.yml.example docker-compose.override.yml
```

Then `make up` or `make dev` mounts `apps/api`, `apps/web`, and `services/embeddings` into containers.

## Environment variables

Copy `.env.example` to `.env` with `make env`. Key groups:

| Group | Variables | Notes |
|-------|-----------|-------|
| Compose | `COMPOSE_PROJECT_NAME` | Docker project name prefix |
| Ports | `DB_PORT`, `API_PORT`, `WEB_PORT` | Host-published ports |
| Database | `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CONNECTION` | `DB_HOST=db` inside Compose |
| Laravel | `APP_NAME`, `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `APP_URL` | Run `php artisan key:generate` after first `make env` |
| CORS / SPA | `WEB_ORIGIN`, `SANCTUM_STATEFUL_DOMAINS` | Admin session auth from the web app |
| Frontend | `VITE_API_BASE_URL`, `VITE_YANDEX_MAPS_API_KEY` | Baked at web build time |
| Embeddings | `EMBEDDINGS_URL`, `EMBEDDINGS_MODEL`, `MOCK_EMBEDDINGS` | Internal only; set `MOCK_EMBEDDINGS=1` in CI for stub vectors |
| Search | `SEARCH_TOP_K`, `SEARCH_MIN_SCORE` | Semantic ranking limits |
| Maps | `YANDEX_MAPS_API_KEY` | Public storefront + admin route editor |
| LLM | `OPENAI_API_KEY`, `OPENAI_BASE_URL`, `OPENAI_MODEL` | Admin tour draft generation |
| Admin seed | `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Used by `make seed` |

Demo data: **9 published tours** (+ drafts/archived for admin) are created by `make seed` or automatically on first API start when the `tours` table is empty (`AUTO_SEED=1`, default). Re-seed anytime with `make seed` (idempotent).

Without Make (Windows / PowerShell):

```powershell
docker compose exec -T api php artisan db:seed --force
# or only when empty:
docker compose exec -T api php artisan catalog:seed-if-empty
```

After seeding, published tours are reindexed for semantic search when the embeddings service is reachable. If indexing is skipped (embeddings still starting), run `make reindex-embeddings` once the service is healthy.

Never commit `.env` or real secrets.

## Repository layout

```
apps/web/              # Vue 3 + Vike + Vite (storefront + admin)
apps/api/              # Laravel 11 API
services/embeddings/   # FastAPI + sentence-transformers (internal)
Makefile               # primary developer interface
docker-compose.yml
```

## Troubleshooting

| Issue | What to try |
|-------|-------------|
| Port already in use | Change `DB_PORT`, `API_PORT`, or `WEB_PORT` in `.env`, then `make restart` |
| `make migrate` fails | Laravel may not be scaffolded yet — complete backend setup tasks first |
| Empty catalog / «Туры не найдены» | Run `make seed` or `docker compose exec -T api php artisan db:seed --force` |
| Embeddings OOM | Increase Docker memory limit; use a smaller `EMBEDDINGS_MODEL` |
| Permission errors on `storage/` | `make api-shell` then fix ownership under `storage/` |

## CI

GitHub Actions runs the same pipeline as local CI (`.github/workflows/ci.yml`) with `MOCK_EMBEDDINGS=1` and `docker-compose.ci.yml`.

Run locally:

```bash
MOCK_EMBEDDINGS=1 COMPOSE_FILE=docker-compose.yml:docker-compose.ci.yml make up
make ci
```

`make ci` runs `lint`, `test` (PHPUnit in the `api` container), and `web-build`.

## License

See project assignment / organization policy.
