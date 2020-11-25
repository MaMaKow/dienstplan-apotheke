#!/usr/bin/env bash
if [ ! -f ".env" ]; then
  cat > .env <<EOL
DB_NAME=pdr
DB_USER=pdr
DB_PASS=$(cat /dev/urandom | tr -d -c "[:alnum:]" | head -c 25)
MYSQL_ROOT_PASSWORD=$(cat /dev/urandom | tr -d -c "[:alnum:]" | head -c 25)
EOL
fi
