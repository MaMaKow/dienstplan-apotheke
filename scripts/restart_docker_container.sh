#!/bin/bash

# Get the directory where this script resides
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Go to the compose directory (relative to script)
COMPOSE_DIR="$SCRIPT_DIR/../tests/selenium"

# Prepare shared downloads directory
mkdir -p /tmp/selenium/shared_downloads
chmod o+w /tmp/selenium/ -R
chmod o+w /tmp/selenium/shared_downloads -R

# Go to the compose directory and start Firefox + video recorder
cd "$COMPOSE_DIR"
docker-compose down   # remove any existing containers from previous runs
docker-compose up -d

# Start MailHog (standalone, not part of compose)
if [ -z "$(docker ps -q --filter ancestor=mailhog/mailhog)" ]; then
    docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog
else
    docker restart mailhog
fi

# Show status
docker-compose ps
docker ps --filter name=mailhog