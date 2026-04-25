#!/bin/bash
set -e
# Get the directory where this script resides
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Go to the compose directory (relative to script)
COMPOSE_DIR="$SCRIPT_DIR/../tests/selenium"

# "Remember, there is a prepared shared downloads directory"
# /etc/tmpfiles.d/selenium.conf
# d /tmp/selenium                0777 git git -
# d /tmp/selenium/shared_downloads  0777 git git -
#
# "It has to be activated by:"
# systemd-tmpfiles --create /etc/tmpfiles.d/selenium.conf
# Sanity-check: shared downloads directory must exist (created via tmpfiles.d)
if [ ! -d "/tmp/selenium/shared_downloads" ]; then
    echo "ERROR: /tmp/selenium/shared_downloads does not exist."
    echo "Create file /etc/tmpfiles.d/selenium.conf"
    echo "Content:"
    echo "d /tmp/selenium                0777 git git -"
    echo "d /tmp/selenium/shared_downloads  0777 git git -"
    echo "Run as root: systemd-tmpfiles --create /etc/tmpfiles.d/selenium.conf"
    exit 1
fi

# Go to the compose directory and start Firefox + video recorder
cd "$COMPOSE_DIR"
echo "remove any existing containers from previous runs"
docker-compose down   # remove any existing containers from previous runs
echo "start new docker containers"
docker-compose up -d

echo "Start MailHog (standalone, not part of compose)"
if [ -z "$(docker ps -aq --filter name=^mailhog$)" ]; then
    docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog
else
    docker restart mailhog
fi
echo "Show docker status"
docker-compose ps
docker ps --filter name=mailhog