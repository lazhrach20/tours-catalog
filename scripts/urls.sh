#!/usr/bin/env sh
cd "$(dirname "$0")/.."

if [ -f .env ]; then
  # shellcheck disable=SC1091
  . ./.env
fi

API_PORT="${API_PORT:-8080}"
WEB_PORT="${WEB_PORT:-3000}"
WEB_ORIGIN="${WEB_ORIGIN:-http://localhost:${WEB_PORT}}"
APP_URL="${APP_URL:-http://localhost:${API_PORT}}"

cat <<EOF

Service URLs
  Storefront : ${WEB_ORIGIN}
  API        : ${APP_URL}/api/v1
  API health : ${APP_URL}/api/v1/health
  Embeddings : ${EMBEDDINGS_URL:-http://embeddings:8000} (Docker network only)

Admin (after seed)
  Email      : ${ADMIN_EMAIL:-admin@example.com}
  Password   : (see ADMIN_PASSWORD in .env)

EOF
