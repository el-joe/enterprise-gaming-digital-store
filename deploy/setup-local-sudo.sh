#!/usr/bin/env bash
# One-time LOCAL setup that needs root. Idempotent. Run:
#   sudo /var/www/enterprise-gaming-digital-store/deploy/setup-local-sudo.sh
#  1. enables proxy proxy_http proxy_wstunnel headers rewrite
#  2. replaces the old admin.boxcards.loc vhost (it pointed at /var/www/boxcards, which has no public/ folder)
#     with boxcards.loc -> this repository's backend/public
#  3. installs 000-n8n.boxcards.loc (http://n8n.boxcards.loc -> 127.0.0.1:5710)
#  4. adds boxcards.loc, admin.boxcards.loc and n8n.boxcards.loc to /etc/hosts if missing
#  5. configtest + reload Apache
#  6. (optional, pass --linger) keeps the user's systemd --user n8n service running without a login session
set -euo pipefail
[[ $EUID -eq 0 ]] || { echo "run with sudo" >&2; exit 1; }
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
AVAIL=/etc/apache2/sites-available

for f in boxcards.loc.conf 000-n8n.boxcards.loc.conf; do
  [[ -f "$DIR/apache/$f" ]] || { echo "missing $DIR/apache/$f" >&2; exit 1; }
done

a2enmod -q proxy proxy_http proxy_wstunnel headers rewrite

if [[ -e /etc/apache2/sites-enabled/admin.boxcards.loc.conf ]]; then
  a2dissite -q admin.boxcards.loc
  [[ -f "$AVAIL/admin.boxcards.loc.conf" ]] && mv "$AVAIL/admin.boxcards.loc.conf" "$AVAIL/admin.boxcards.loc.conf.disabled-$(date +%Y%m%d)"
  echo "disabled the old admin.boxcards.loc vhost (kept as a .disabled backup in $AVAIL)"
fi

install -m 0644 -o root -g root "$DIR/apache/boxcards.loc.conf" "$AVAIL/boxcards.loc.conf"
install -m 0644 -o root -g root "$DIR/apache/000-n8n.boxcards.loc.conf" "$AVAIL/000-n8n.boxcards.loc.conf"
a2ensite -q boxcards.loc 000-n8n.boxcards.loc

for host in boxcards.loc admin.boxcards.loc n8n.boxcards.loc; do
  grep -qE "^[[:space:]]*127\.0\.0\.1[[:space:]].*[[:space:]]${host//./\\.}([[:space:]]|$)" /etc/hosts || echo "127.0.0.1 $host" >> /etc/hosts
done

apache2ctl configtest
systemctl reload apache2
if [[ "${1:-}" == "--linger" && -n "${SUDO_USER:-}" ]]; then loginctl enable-linger "$SUDO_USER"; echo "linger enabled for $SUDO_USER"; fi

echo "OK: http://boxcards.loc  and  http://n8n.boxcards.loc -> 127.0.0.1:5710"
curl -s -o /dev/null -w "boxcards.loc/          -> HTTP %{http_code}\n" http://boxcards.loc/ || true
curl -s -o /dev/null -w "n8n.boxcards.loc/healthz -> HTTP %{http_code}\n" http://n8n.boxcards.loc/healthz || true
