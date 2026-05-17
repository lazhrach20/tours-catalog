#!/usr/bin/env sh
# Wrapper used by Makefile — loads .env and runs docker compose from repo root.
set -e
cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
  echo "Missing .env — run: make env" >&2
  exit 1
fi

# shellcheck disable=SC1091
set -a
. ./.env
set +a

exec docker compose "$@"
