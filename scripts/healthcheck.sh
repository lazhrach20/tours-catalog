#!/usr/bin/env sh
set -e
cd "$(dirname "$0")/.."

if [ -f .env ]; then
  # shellcheck disable=SC1091
  . ./.env
fi

API_PORT="${API_PORT:-8080}"
WEB_PORT="${WEB_PORT:-3000}"
DB_PORT="${DB_PORT:-5432}"

fail=0

printf "Checking db (localhost:%s)... " "$DB_PORT"
if docker compose exec -T db pg_isready -U "${DB_USERNAME:-tours}" -d "${DB_DATABASE:-tours_catalog}" >/dev/null 2>&1; then
  echo "ok"
else
  echo "FAIL"
  fail=1
fi

printf "Checking api (http://localhost:%s/api/v1/health)... " "$API_PORT"
if curl -fsS "http://localhost:${API_PORT}/api/v1/health" >/dev/null 2>&1; then
  echo "ok"
else
  echo "FAIL"
  fail=1
fi

printf "Checking web (http://localhost:%s/health)... " "$WEB_PORT"
if curl -fsS "http://localhost:${WEB_PORT}/health" >/dev/null 2>&1; then
  echo "ok"
else
  echo "FAIL"
  fail=1
fi

printf "Checking embeddings (internal)... "
if docker compose exec -T embeddings curl -fsS http://127.0.0.1:8000/health >/dev/null 2>&1; then
  echo "ok"
else
  echo "FAIL"
  fail=1
fi

exit "$fail"
