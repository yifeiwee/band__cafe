#!/usr/bin/env bash
# Container entrypoint to ensure .env exists before Apache starts
set -euo pipefail

ENV_FILE="/var/www/html/.env"
TEMPLATE_FILE="/var/www/html/.env.example"

escape_sed() {
  printf '%s' "$1" | sed -e 's/[\/&]/\\&/g'
}

set_env_value() {
  local key="$1"
  local value="$2"
  local escaped_value
  escaped_value=$(escape_sed "$value")
  if [[ -f "$ENV_FILE" ]] && grep -q "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*$|${key}=${escaped_value}|" "$ENV_FILE"
  else
    echo "${key}=${value}" >> "$ENV_FILE"
  fi
}

ensure_env_file() {
  if [[ -f "$ENV_FILE" ]]; then
    return
  fi

  if [[ -f "$TEMPLATE_FILE" ]]; then
    cp "$TEMPLATE_FILE" "$ENV_FILE"
  else
    touch "$ENV_FILE"
  fi
}

populate_defaults() {
  set_env_value "DB_HOST" "${DB_HOST:-db}"
  set_env_value "DB_NAME" "${DB_NAME:-bandcafe_db}"
  set_env_value "DB_USER" "${DB_USER:-bandcafe_user}"
  set_env_value "DB_PASSWORD" "${DB_PASSWORD:-change_this_password}"
  set_env_value "DB_ROOT_PASSWORD" "${DB_ROOT_PASSWORD:-change_this_password}"
  set_env_value "APP_ENV" "${APP_ENV:-production}"
  set_env_value "APP_DEBUG" "${APP_DEBUG:-false}"
  set_env_value "SESSION_LIFETIME" "${SESSION_LIFETIME:-0}"
  set_env_value "SESSION_SECURE" "${SESSION_SECURE:-true}"
  set_env_value "SESSION_HTTPONLY" "${SESSION_HTTPONLY:-true}"
  set_env_value "SESSION_SAMESITE" "${SESSION_SAMESITE:-Strict}"
}

ensure_env_file
populate_defaults
chmod 640 "$ENV_FILE" || true

exec apache2-foreground "$@"
