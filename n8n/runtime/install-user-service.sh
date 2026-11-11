#!/usr/bin/env bash
# Local n8n for boxcards.shop as a systemd --user service (no sudo). Idempotent.
#   1. creates .env from .env.example with a random N8N_ENCRYPTION_KEY (only if .env is missing)
#   2. installs ~/.config/systemd/user/n8n-boxcards.service and starts it on 127.0.0.1:5710
set -euo pipefail
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
UNIT_DIR="${XDG_CONFIG_HOME:-$HOME/.config}/systemd/user"

[[ -x "$DIR/node_modules/.bin/n8n" ]] || { echo "n8n not installed: cd $DIR && npm ci" >&2; exit 1; }

if [[ ! -f "$DIR/.env" ]]; then
  umask 077
  key="$(openssl rand -hex 32)"
  sed "s#^N8N_ENCRYPTION_KEY=.*#N8N_ENCRYPTION_KEY=$key#" "$DIR/.env.example" >"$DIR/.env"
  echo "created $DIR/.env with a new N8N_ENCRYPTION_KEY (back it up)"
fi
chmod 600 "$DIR/.env"

mkdir -p "$UNIT_DIR"
sed "s#@N8N_DIR@#$DIR#g" "$DIR/deploy/n8n-boxcards.service" >"$UNIT_DIR/n8n-boxcards.service"
"$DIR/start.sh" --stop >/dev/null 2>&1 || true
systemctl --user daemon-reload
systemctl --user enable --now n8n-boxcards.service
systemctl --user --no-pager status n8n-boxcards.service | head -5 || true
if [[ "$(loginctl show-user "$USER" -p Linger --value 2>/dev/null)" != "yes" ]]; then
  echo
  echo "NOTE: linger is off for $USER, so n8n runs only while $USER is logged in."
  echo "      To keep it running at boot:  sudo loginctl enable-linger $USER"
fi
