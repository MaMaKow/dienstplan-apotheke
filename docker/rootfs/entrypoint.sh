#!/usr/bin/env bash

#set -e

if [ ! "$DB_PASS" ]; then
  echo "No database password"
  exit 1
fi

exec "$@"
