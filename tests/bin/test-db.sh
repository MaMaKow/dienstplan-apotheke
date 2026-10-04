#!/bin/bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
project_root="$(cd "$script_dir/../.." && pwd)"
compose_file="$project_root/tests/docker-compose.test-db.yml"
project_name="dienstplan-phpunit-db"
root_password="phpunit_root_pw"

compose() {
  docker compose -p "$project_name" -f "$compose_file" "$@"
}

cmd="${1:-}"

case "$cmd" in
  up)
    echo "Starte frischen Wegwerf-MySQL-Container fuer PHPUnit..."
    compose down --volumes --remove-orphans >/dev/null 2>&1 || true
    compose up -d --force-recreate --renew-anon-volumes

    port=$(compose port test-db 3306 | cut -d: -f2)
    probe_name="${project_name}-probe"

    docker rm -f "$probe_name" >/dev/null 2>&1 || true
    docker run -d --rm --name "$probe_name" --network host mysql:8.0 sleep 300 >/dev/null
    trap 'docker rm -f "$probe_name" >/dev/null 2>&1 || true' RETURN

    echo -n "Warte auf MySQL auf 127.0.0.1:$port"
    tries=0
    until docker exec "$probe_name" \
            mysql -h 127.0.0.1 -P "$port" -uroot -p"$root_password" \
                  -e 'SELECT 1' >/dev/null 2>&1; do
        echo -n "."
        tries=$((tries + 1))
        if [ "$tries" -gt 60 ]; then
            echo ""
            echo "MySQL wurde nach ${tries}s nicht bereit. Abbruch." >&2
            compose logs test-db >&2
            exit 1
        fi
        sleep 1
    done
    echo ""

    echo "$port" > "$script_dir/.test-db-port"
    echo "MySQL bereit auf 127.0.0.1:$port"
    ;;
  down)
    compose down --volumes --remove-orphans
    rm -f "$script_dir/.test-db-port"
    ;;
  port)
    cat "$script_dir/.test-db-port"
    ;;
  *)
    echo "Usage: $0 {up|down|port}" >&2
    exit 1
    ;;
esac
