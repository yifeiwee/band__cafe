#!/usr/bin/env bash
# Build & run helper that guarantees strong DB passwords exist in .env
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$PROJECT_ROOT/.env"
TEMPLATE_FILE="$PROJECT_ROOT/.env.example"

log() {
  printf "[docker-build] %s\n" "$1"
}

PYTHON_BIN=${PYTHON_BIN:-python3}
if ! command -v "$PYTHON_BIN" >/dev/null 2>&1; then
  if command -v python >/dev/null 2>&1; then
    PYTHON_BIN=python
  else
    log "Python is required to run this script. Install python3 and retry."
    exit 1
  fi
fi

ensure_env_file() {
  if [[ ! -f "$ENV_FILE" ]]; then
    if [[ -f "$TEMPLATE_FILE" ]]; then
      cp "$TEMPLATE_FILE" "$ENV_FILE"
      log "Created .env from .env.example"
    else
      log "Missing .env and .env.example files"
      exit 1
    fi
  fi
}

generate_password() {
  "$PYTHON_BIN" - <<'PY'
import secrets, string
alphabet = string.ascii_letters + string.digits + "!@#$%^&*()-_=+"
print(''.join(secrets.choice(alphabet) for _ in range(40)))
PY
}

set_env_value() {
  local key="$1" value="$2"
  "$PYTHON_BIN" - "$ENV_FILE" "$key" "$value" <<'PY'
import pathlib, sys
env_path = pathlib.Path(sys.argv[1])
key = sys.argv[2]
value = sys.argv[3]
if env_path.exists():
    lines = env_path.read_text().splitlines()
else:
    lines = []
updated = False
for idx, line in enumerate(lines):
    if line.startswith(f"{key}="):
        lines[idx] = f"{key}={value}"
        updated = True
        break
if not updated:
    lines.append(f"{key}={value}")
text = "\n".join(lines)
if text and not text.endswith("\n"):
    text += "\n"
env_path.write_text(text)
PY
}

needs_password() {
  local key="$1"
  "$PYTHON_BIN" - "$ENV_FILE" "$key" <<'PY'
import pathlib, sys
env_path = pathlib.Path(sys.argv[1])
key = sys.argv[2]
value = ""
if env_path.exists():
    for line in env_path.read_text().splitlines():
        if line.startswith(f"{key}="):
            value = line.split("=", 1)[1].strip()
            break
if (not value) or ("CHANGE_THIS" in value):
    sys.exit(0)
sys.exit(1)
PY
  return $?
}

ensure_passwords() {
  local key
  for key in DB_PASSWORD DB_ROOT_PASSWORD; do
    if needs_password "$key"; then
      local new_value
      new_value="$(generate_password)"
      set_env_value "$key" "$new_value"
      log "Generated secure value for $key"
    fi
  done
}

ensure_env_file
ensure_passwords

# Ensure .env is readable by Docker
chmod 644 "$ENV_FILE" 2>/dev/null || true

if [[ $# -eq 0 ]]; then
  set -- -d
fi

log "Using $(basename "$ENV_FILE") for docker compose"
cd "$PROJECT_ROOT"
docker compose up --build "$@"
