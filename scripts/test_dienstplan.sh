#!/bin/bash
#
# test_dienstplan.sh
#
# Vereinheitlichtes Test-/CI-CD-Skript für dienstplan-apotheke.
# Für unbeaufsichtigten Betrieb (z.B. nächtlicher Cronjob) ausgelegt:
# jeder Lauf schreibt ein vollständiges Log + eine kurze Statuszeile,
# damit man morgens an einer Stelle nachsehen kann, was ggf. kaputt ist.
#
#   --mode=quick     Schneller Test des aktuellen lokalen Codes.
#                     Kein Git-Pull, kein Push, kein Deploy.
#                     Quelle: das Projektverzeichnis, in dem dieses Skript
#                             selbst liegt (./scripts/test_dienstplan.sh
#                             -> Projekt-Root ist eine Ebene darüber).
#                     Arbeitet immer mit dem Code, der gerade da liegt
#                     (z.B. zuletzt per rsync hochgeladen) - unverändert.
#
#   --mode=pipeline   Vollständige CI/CD-Pipeline.
#                     Quelle: Git 'testing'-Branch (Merge-Check gegen 'master').
#                     Bei Erfolg: DB-Dump, Push testing->master, Live-Deploy.
#                     Läuft ohne weitere Rückfrage durch - 'testing' enthält
#                     laut Konvention nur bereits stabile Kandidaten.
#
# Beispiele:
#   ./test_dienstplan.sh --mode=quick
#   ./test_dienstplan.sh --mode=pipeline
#
# LOGS:
#   Pro Lauf ein Verzeichnis unter $log_root (siehe unten), z.B.:
#     .../pipeline_20260820_020001/full.log   <- kompletter Mitschnitt
#     .../pipeline_20260820_020001/web.log    <- nur bei Fehlschlag
#     .../pipeline_20260820_020001/db.log     <- nur bei Fehlschlag
#   Zentrale Übersicht (eine Zeile pro Lauf, auch für übersprungene Läufe):
#     $log_root/status.log
#   Quick-Modus schreibt zusätzlich weiterhin nach /var/log/selenium.log
#   (Altverhalten, unverändert).

usage() {
  cat <<EOF
Usage: $0 --mode=quick|pipeline

  --mode=quick      Schneller Test des aktuellen lokalen Codes.
                     Kein Git-Pull, kein Push, kein Deploy.
  --mode=pipeline    Vollständige CI/CD-Pipeline (Merge-Check testing->master,
                     bei Erfolg Push + Deploy).

Beispiele:
  $0 --mode=quick
  $0 --mode=pipeline
EOF
}

mode=""
for arg in "$@"; do
  case "$arg" in
  --mode=*) mode="${arg#*=}" ;;
  -h | --help)
    usage
    exit 0
    ;;
  *)
    echo "Unbekannte Option: $arg"
    usage
    exit 1
    ;;
  esac
done

if [[ "$mode" != "quick" && "$mode" != "pipeline" ]]; then
  echo "Fehler: --mode=quick oder --mode=pipeline ist erforderlich."
  usage
  exit 1
fi

# Determine the script's directory and the project root (scripts/ liegt
# direkt im Projekt-Root, also eine Ebene über diesem Skript).
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
project_root="$(cd "$script_dir/.." && pwd)"

lock_file="/tmp/test_dienstplan.lock"
quick_log="/var/log/selenium.log"
hostnameInstallTest="https://docker.martin-mandelkow.de"

# Zertifikate liegen fest in /home/git/scripts/ (dorthin kopiert
# copy_docker_certs.sh regelmäßig per Cron aus letsencrypt). Diese
# Quelle ist unabhängig vom Checkout-Ort des Skripts (macbook,
# homeoffice, ...), da alles auf demselben Host läuft.
cert_dir="/home/git/scripts"

# Log-Verzeichnis für vollständige Lauf-Mitschnitte + Status-Übersicht.
# Fällt auf /tmp zurück, falls /var/log nicht beschreibbar ist (z.B. wenn
# das Skript nicht als root läuft).
log_root="/var/log/dienstplan-test-runs"
mkdir -p "$log_root" 2>/dev/null || log_root="/tmp/dienstplan-test-runs"
mkdir -p "$log_root" 2>/dev/null || true
status_log="$log_root/status.log"

# Timeout für den mvn-Testlauf, damit ein hängender Test nicht den Lock
# für alle folgenden (nächtlichen) Läufe dauerhaft blockiert.
mvn_timeout="30m"

export JAVA_HOME=/usr/lib/jvm/default-java

write_status_line() {
  # Eine Zeile pro Lauf in $status_log - zum schnellen morgendlichen Überfliegen.
  {
    printf '%s mode=%s status=%s run_log=%s message="%s"\n' \
      "$(date '+%Y-%m-%d %H:%M:%S')" "$mode" "${result_status:-UNKNOWN}" \
      "${run_log:-}" "${result_message:-}"
  } >>"$status_log"
}

(
  flock -n 200 || {
    result_status="SKIPPED"
    result_message="Vorheriger Lauf noch aktiv (Lock $lock_file)"
    run_log=""
    write_status_line
    echo "Another instance is already running. Exiting."
    exit 1
  }

  set -e
  set -o pipefail

  # Read passwords and usernames as environment variables
  # (testRealUsername, testRealPassword, testRealPageUrl, ...)
  source ~/.bash_profile

  # Alte Lauf-Verzeichnisse aufräumen (älter als 14 Tage), damit der
  # Log-Ordner bei täglichen/nächtlichen Läufen nicht unbegrenzt wächst.
  find "$log_root" -mindepth 1 -maxdepth 1 -type d -mtime +14 -exec rm -rf {} \; 2>/dev/null || true

  run_id="${mode}_$(date '+%Y%m%d_%H%M%S')"
  run_dir="$log_root/$run_id"
  mkdir -p "$run_dir"
  run_log="$run_dir/full.log"

  # Ab hier: kompletter stdout/stderr-Mitschnitt des Laufs zusätzlich in
  # run_log, damit man morgens den ganzen Verlauf nachvollziehen kann,
  # nicht nur die letzte Bildschirmseite einer SSH-Sitzung.
  exec > >(tee -a "$run_log") 2>&1

  echo "===== Start $(date '+%Y-%m-%d %H:%M:%S') mode=$mode run_dir=$run_dir ====="

  result_status="UNKNOWN"
  result_message="Unerwarteter Abbruch (siehe $run_log)"
  web_container=""
  db_container=""
  project_dir=""

  capture_diagnostics() {
    set +e
    if [ -n "$web_container" ]; then
      docker logs "$web_container" >"$run_dir/web.log" 2>&1
    fi
    if [ -n "$db_container" ]; then
      docker logs "$db_container" >"$run_dir/db.log" 2>&1
    fi
    set -e
  }

  # Zentraler Fehlerausstieg: sichert Container-Logs, fährt die Umgebung
  # herunter und beendet das Skript mit gesetztem Status.
  fail_and_exit() {
    result_status="$1"
    result_message="$2"
    capture_diagnostics
    if [ -n "$project_dir" ] && [ -f "$project_dir/docker-compose.yml" ]; then
      #docker-compose -f "$project_dir/docker-compose.yml" down --volumes 2>&1 || true
      :
    fi
    exit 1
  }

  cleanup() {
    set +e # Fehler in cleanup erlauben
    if [ -n "$web_container" ]; then
      docker logs "$web_container" 2>&1 | grep -i "overtime\|error\|warning" | tail -20
      docker exec "$web_container" cat /var/www/html/apotheke/dienstplan-test/error.log
    fi
    # MailHog wird bewusst NICHT hier gestoppt: es ist Teil von
    # docker-compose und wird beim nächsten Lauf idempotent per
    # "docker-compose up -d" wieder hochgefahren.
    write_status_line
    echo "===== Ende $(date '+%Y-%m-%d %H:%M:%S') status=$result_status ====="
  }
  trap cleanup EXIT

  if [[ "$mode" == "pipeline" ]]; then
    current_user=$(whoami)
    if [ "git" != "$current_user" ]; then
      echo "Im Pipeline-Modus darf dieses Skript nur vom User 'git' ausgeführt werden."
      result_status="ERROR"
      result_message="Falscher User ($current_user statt git)"
      exit 1
    fi

    repo_dir="/home/git/repositories/dienstplan-apotheke-testing"
    rm -rf "$repo_dir"
    mkdir -p "$repo_dir"
    cd "$repo_dir"

    git clone git@github.com:MaMaKow/dienstplan-apotheke.git
    cd dienstplan-apotheke
    git fetch origin testing:testing
    git checkout testing
    git fetch --all

    diff_output=$(git diff origin/master..origin/testing)
    if [ -z "$diff_output" ]; then
      echo "No differences. Exiting."
      result_status="SKIPPED"
      result_message="Keine Unterschiede zwischen testing und master"
      exit 0
    fi

    if ! git merge --no-ff origin/master; then
      echo "Automatic merge not possible. Exiting."
      git merge --abort
      result_status="MERGE_CONFLICT"
      result_message="Merge testing<-master nicht möglich"
      exit 1
    fi
    echo "Merge with master completed successfully. Proceeding with the script."

    project_dir="$repo_dir/dienstplan-apotheke"
  else
    # quick mode: direkt im Projektverzeichnis arbeiten, in dem
    # scripts/test_dienstplan.sh liegt. Kein Klonen, kein Löschen.
    project_dir="$project_root"
    cd "$project_dir"
    current_branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo unbekannt)
    echo "Quick-Test: verwende lokalen Code in $project_dir (Branch: $current_branch)"
  fi

  composer dump-autoload
  composer install

  if [ ! -f "$project_dir/docker-compose.yml" ]; then
    echo "docker-compose.yml not found. Exiting."
    result_status="ERROR"
    result_message="docker-compose.yml nicht gefunden in $project_dir"
    exit 1
  fi

  echo "Kopiere Zertifikate für PDR (HTTPS erforderlich)"
  cp "$cert_dir/fullchain.pem" "$project_dir/upload/fullchain.pem"
  cp "$cert_dir/privkey.pem" "$project_dir/upload/privkey.pem"

  random_secure_web_port=8443
  random_db_name=$(
    tr -dc a-z </dev/urandom | head -c 64
    echo
  )
  random_user_name=$(
    tr -dc a-z </dev/urandom | head -c 32
    echo
  )
  random_user_passphrase=$(
    tr -dc A-Za-z0-9 </dev/urandom | head -c 32
    echo
  )
  random_root_passphrase=$(
    tr -dc A-Za-z0-9 </dev/urandom | head -c 32
    echo
  )

  cd "$project_dir"
  truncate -s 0 .env
  {
    echo "ENVIRONMENT=testing"
    echo "SECURE_WEB_PORT=$random_secure_web_port"
    echo "MYSQL_ROOT_PASSWORD=$random_root_passphrase"
    echo "MYSQL_DATABASE=$random_db_name"
    echo "MYSQL_USER=$random_user_name"
    echo "MYSQL_PASSWORD=$random_user_passphrase"
  } >>.env

  urlInstallTest="$hostnameInstallTest:$random_secure_web_port/apotheke"

  cat <<CONF >"$project_dir/tests/selenium/Configuration.properties"
testRealUsername=$testRealUsername
testRealPassword=$testRealPassword
testRealPageUrl=$testRealPageUrl
urlInstallTest=$urlInstallTest/
testPageUrl=$urlInstallTest/dienstplan-test/
pdrUserName=$random_user_name
pdrUserPassword=$random_user_passphrase
administratorEmail=$random_user_name@example.com
administratorEmployeeId=5
databaseUserName=root
databaseHostname=db
databasePassword=$random_root_passphrase
databaseName=$random_db_name
databasePort=3306
smtpHost=mailhog
smtpPort=1025
CONF

  echo "Start the selenium container"
  bash "$project_dir/scripts/restart_docker_container.sh"

  echo "Start/rebuild application containers (web, db, mailhog) via docker-compose"
  docker-compose -f "$project_dir/docker-compose.yml" down --volumes
  docker-compose -f "$project_dir/docker-compose.yml" build
  # idempotent - startet u.a. auch MailHog als Teil des Compose-Netzwerks
  docker-compose -f "$project_dir/docker-compose.yml" up -d

  # Container-IDs einmalig über docker-compose auflösen (robust gegenüber
  # unterschiedlichen Namensschemata je nach Compose-Version), statt feste
  # Namen wie "dienstplan-apotheke_web_1" anzunehmen.
  web_container=$(docker-compose -f "$project_dir/docker-compose.yml" ps -q web)
  db_container=$(docker-compose -f "$project_dir/docker-compose.yml" ps -q db)
  if [ -z "$web_container" ] || [ -z "$db_container" ]; then
    fail_and_exit "ERROR" "Web- oder DB-Container nach 'up -d' nicht gefunden"
  fi

  check_containers() {
    STATUS=$(docker-compose -f "$project_dir/docker-compose.yml" ps -q | xargs docker inspect -f '{{ .State.Running }}' 2>/dev/null)
    if [[ "$STATUS" == *"false"* ]] || [[ -z "$STATUS" ]]; then
      return 1
    fi
    # Container laufen zwar, das heißt aber nicht, dass MySQL bereits
    # Verbindungen annimmt (besonders nach frischem Volume). Deshalb
    # zusätzlich aktiv gegen die DB pingen und deren Exit-Code auswerten.
    if ! docker exec "$db_container" mysqladmin ping -h localhost -u root -p"$random_root_passphrase" --silent 2>/dev/null; then
      return 1
    fi
    return 0
  }

  echo "Waiting for containers to be up."
  number_of_times_containers_checked=0
  while ! check_containers; do
    echo -n "."
    ((number_of_times_containers_checked++)) || true
    if [ $number_of_times_containers_checked -gt 30 ]; then
      echo ""
      echo "Taking too long to wait for container. Exiting."
      fail_and_exit "ERROR" "Container/DB nach 30s nicht bereit"
    fi
    sleep 1
  done
  echo ""

  cd "$project_dir/tests/selenium/"

  echo "Test connection using curl:"
  curl -vvv "$urlInstallTest/dienstplan-test/"
  docker exec "$web_container" curl -s http://mailhog:8025/api/v2/messages | head -c 200

  # mvn-Exitcode explizit einfangen, damit er durch "tee" nicht verloren geht.
  # Zusätzlich mit Timeout, damit ein hängender Testlauf nicht den Lock für
  # alle folgenden (z.B. nächtlichen) Läufe blockiert.
  set +e
  timeout "$mvn_timeout" /usr/bin/mvn test | tee ./mvn.log
  mvn_exit=$?
  set -e
  echo -e "\a" # Bell sound!

  test_outcome=$(cat test-result 2>/dev/null || echo "UNKNOWN")

  if [[ "$mode" == "quick" ]]; then
    {
      echo "===== $(date '+%Y-%m-%d %H:%M:%S') Quick-Test ($project_dir, Branch: $current_branch) ====="
      echo "mvn exit code: $mvn_exit"
      echo "test-result:   $test_outcome"
      echo "voller Log:    $run_log"
    } >>"$quick_log"
    echo "Ergebnis wurde nach $quick_log geschrieben."
  fi

  if [ "$mvn_exit" -eq 124 ]; then
    fail_and_exit "TEST_TIMEOUT" "mvn test nach $mvn_timeout abgebrochen (timeout)"
  elif [ "$mvn_exit" -ne 0 ] || [ "$test_outcome" == "FAILED" ]; then
    echo "Selenium tests failed."
    fail_and_exit "TEST_FAILED" "mvn_exit=$mvn_exit test-result=$test_outcome"
  elif [ "$test_outcome" != "SUCCESS" ]; then
    echo "Unexpected result in test-result file: $test_outcome"
    fail_and_exit "ERROR" "Unerwartetes test-result: $test_outcome"
  fi

  echo "Selenium tests succeeded."

  if [[ "$mode" == "quick" ]]; then
    #docker-compose -f "$project_dir/docker-compose.yml" down --volumes
    echo "Quick-Test abgeschlossen."
    result_status="SUCCESS"
    result_message="Quick-Test erfolgreich"
    exit 0
  fi

  # ---- Ab hier nur im Pipeline-Modus: DB-Dump, Deploy, Push ----

  docker exec "$db_container" mysqldump -u root -p"$random_root_passphrase" "$random_db_name" >/tmp/db_dump.sql
  #docker-compose -f "$project_dir/docker-compose.yml" down --volumes
  bash "$project_dir/scripts/start-live-test-container.sh"

  cd "$project_dir"
  echo "Trying to push using branch 'testing' to 'master'"
  git push origin testing:master

  echo "CI/CD pipeline executed successfully."
  rm -rf "$repo_dir"

  result_status="SUCCESS"
  result_message="Tests ok, nach master gepusht und deployt"

) 200>"$lock_file"
