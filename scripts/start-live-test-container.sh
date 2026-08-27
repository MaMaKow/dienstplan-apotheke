#!/bin/bash
# setup directories
repo_dir="/home/git/repositories/dienstplan-apotheke-testing"

live_port=9443
live_dir="$repo_dir/live/dienstplan-apotheke"
mkdir -p "$live_dir"
cp -r "$repo_dir/dienstplan-apotheke/." "$live_dir"

random_live_db_name="live_${random_db_name}"
random_live_user_name=$(
  tr -dc a-z </dev/urandom | head -c 32
  echo
)
random_live_user_passphrase=$(
  tr -dc A-Za-z0-9 </dev/urandom | head -c 32
  echo
)
random_live_root_passphrase=$(
  tr -dc A-Za-z0-9 </dev/urandom | head -c 32
  echo
)

cd "$live_dir" || exit
echo SECURE_WEB_PORT=$live_port >.env
echo MYSQL_ROOT_PASSWORD=$random_live_root_passphrase >>.env
echo MYSQL_DATABASE=$random_live_db_name >>.env
echo MYSQL_USER=$random_live_user_name >>.env
echo MYSQL_PASSWORD=$random_live_user_passphrase >>.env

docker-compose -f docker-compose-live.yml down --volumes
docker-compose -f docker-compose-live.yml build
docker-compose -f docker-compose-live.yml up -d

live_db_container=$(docker-compose -f docker-compose-live.yml ps -q db)

docker cp /tmp/db_dump.sql "$live_db_container":/db_dump.sql
docker exec -i "$live_db_container" mysql -u root -p"$random_live_root_passphrase" "$random_live_db_name" </db_dump.sql
