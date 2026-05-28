#!/bin/bash
# CI/CD
# In software development, CI/CD is the combined practice of continuous integration (CI for short)
# and Continuous Delivery (CD for short) or Continuous Deployment (CD for short).

# The task of this script is to test the current commit in the testing branch and, if successful, to move it to the main branch.
# git pull origin testing -> selenium tests (integration tests) -> git merge into master -> git push origin master -> cd production -> git pull origin master (update in production)
(
  flock -n 200 || { echo "Another instance is already running. Exiting."; exit 1; }
set -e
set -o pipefail
cleanup() {

    docker logs dienstplan-apotheke_web_1 2>&1 | grep -i "overtime\|error\|warning" | tail -20
    docker logs dienstplan-apotheke_mailhog_1
    docker exec dienstplan-apotheke_web_1 cat /var/www/html/apotheke/dienstplan-test/error.log 
    #docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes 2>/dev/null || true
    #rm -rf "$repo_dir"
}
trap cleanup EXIT
# setup directories
repo_dir="/home/git/repositories/dienstplan-apotheke-testing"
hostnameInstallTest="https://docker.martin-mandelkow.de"
export JAVA_HOME=/usr/lib/jvm/default-java

# Determine the script's directory
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# test if the user is "git"
current_user=$(whoami)
if [ "git" != "$current_user" ]; then
    echo "This script is supposed to only be run by user git."
    echo "Exiting"
    exit 1
fi
# Read passwords and usernames as environment variables
source ~/.bash_profile


rm -rf "$repo_dir"
mkdir -p "$repo_dir"
cd "$repo_dir" || exit
# clone repository and checkout testing branch
git clone git@github.com:MaMaKow/dienstplan-apotheke.git
cd dienstplan-apotheke || exit
git fetch origin testing:testing
git checkout testing
# Fetch latest changes from remote
git fetch --all

diff_output=$(git diff origin/master..origin/testing)
if [ -z "$diff_output" ]; then
    echo "No differences. Exiting."
    exit 0
fi
# Test if the merge with master branch will succeed
if ! git merge --no-ff origin/master; then
    echo "Automatic merge not possible. Exiting."
    git merge --abort
    exit 1
fi
echo "Merge with master completed successfully. Proceeding with the script."

# Get the 3rdparty classes via composer:
composer dump-autoload
composer install
#composer install --no-dev --optimize-autoloader

# Check if docker-compose file is existent
if [ ! -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" ]; then
    echo "docker-compose.yml not found. Exiting."
    exit 1
fi


echo HERE1
# Get valid certificate files
# PDR requires a connection via HTTPS. Certificates are therefore required.
# These must be provided in the same path as this script. They are copied from here into the docker container.
cp $script_dir/fullchain.pem $repo_dir/dienstplan-apotheke/upload/fullchain.pem
cp $script_dir/privkey.pem $repo_dir/dienstplan-apotheke/upload/privkey.pem
echo HERE2


# Install dependencies with composer
# First get composer:
#EXPECTED_HASH=$(php -r "copy('https://composer.github.io/installer.sig', 'php://stdout');")
#php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#ACTUAL_HASH=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")
#
#if [ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]; then
#  echo "ERROR: Invalid installer hash"
#  rm composer-setup.php
#  exit 1
#fi

# configure environment for docker container
# expose a random port and chosse random user names and passphrases
#random_secure_web_port=$((RANDOM % 65535 + 1024))
random_secure_web_port=8443
random_db_name=$(tr -dc a-z </dev/urandom | head -c 64; echo);
random_user_name=$(tr -dc a-z </dev/urandom | head -c 32; echo);
random_user_passphrase=$(tr -dc A-Za-z0-9 </dev/urandom | head -c 32; echo);
random_root_passphrase=$(tr -dc A-Za-z0-9 </dev/urandom | head -c 32; echo);
truncate -s 0 .env # Clean up old data from .env file
echo ENVIRONMENT=testing >> .env # set ENVIRONMENT for the Dockerfile to testing
echo SECURE_WEB_PORT=$random_secure_web_port >> .env
echo MYSQL_ROOT_PASSWORD=$random_root_passphrase >> .env
echo MYSQL_DATABASE=$random_db_name >> .env
echo MYSQL_USER=$random_user_name >>.env
echo MYSQL_PASSWORD=$random_user_passphrase >> .env
urlInstallTest=$hostnameInstallTest:$random_secure_web_port/apotheke
echo HERE3

# The databaseHostname is "db". This is the hostname of the mysql container as defined in the docker-compose.yml
# TODO: Use databaseUserName and passphrase from random instead of root
cat <<EOF > $repo_dir/dienstplan-apotheke/tests/selenium/Configuration.properties
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
EOF
echo "Start the selenium container"
docker stop $(docker ps -a --filter "name=mailhog" -q)
cat $repo_dir/dienstplan-apotheke/scripts/restart_docker_container.sh
bash $repo_dir/dienstplan-apotheke/scripts/restart_docker_container.sh # Selenium docker container

echo "Start the Webserver docker container to hold the dienstplan-apotheke application"
#docker-compose build --no-cache
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" build
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" up -d
# Function to check if all containers are running
check_containers() {
  # Get the status of all containers
  STATUS=$(docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" ps -q | xargs docker inspect -f '{{ .State.Running }}' 2>/dev/null)

  # Check if any container is not running
  if [[ "$STATUS" == *"false"* ]] || [[ -z "$STATUS" ]]; then
    return 1
  else
    return 0
  fi
}

# Wait for all containers to be running
echo "Waiting for containers to be up."
number_of_times_containers_checked=0;
while ! check_containers; do
    echo -n "."
    (( number_of_times_containers_checked++ )) || true
    if [ $number_of_times_containers_checked -gt 30 ]; then
	echo "Taking to long to wait for container. Exiting."
        exit 1;
    fi
    sleep 1  # Wait for 1 second before checking again
done
#sleep 30

# run selenium tests
# assuming the selenium tests are written to exit with a non-zero status on failure
cd "$repo_dir"/dienstplan-apotheke/tests/selenium/ || exit

echo "Test connection using curl:"
curl -vvv $urlInstallTest/dienstplan-test/
# Mailhog vom web-Container aus testen:
docker exec dienstplan-apotheke_web_1 curl -s http://mailhog:8025/api/v2/messages | head -c 200

/usr/bin/mvn test | tee ./mvn.log
echo -e "\a" # Bell sound!

test_outcome=$(cat test-result)
if [ "$test_outcome" == "FAILED" ]; then
    echo "Selenium tests failed."
    exit 1
elif [ "$test_outcome" == "SUCCESS" ]; then
    echo "Selenium tests succeeded."
else
    echo "Unexpected result in test-result file: $test_outcome"
    # cleanup the docker container in case of failure:
    docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes
    exit 1
fi
test_db_container=$(docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" ps -q db)
docker exec "$test_db_container" mysqldump -u root -p"$random_root_passphrase" "$random_db_name" > /tmp/db_dump.sql
# cleanup the docker container also in case of success:
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes
bash "$repo_dir/dienstplan-apotheke/scripts/start-live-test-container.sh"

# if tests pass, merge into master and push
# Check the current path
echo "Current path:"
pwd

# Attempt to blank auth ssl (not needed if using PAT, usually used for SSH)
#echo "Trying to authenticate using SSH"
# ssh -T git@github.com || true

# Try pushing to GitHub using the branch 'testing' to 'master'
echo "Trying to push using branch 'testing' to 'master'"
git push origin testing:master

# Second attempt to push using a token
#echo "Trying to authenticate with token and push"
#git push https://$GIT_TOKEN@github.com/MaMaKow/dienstplan-apotheke.git testing:master

echo "CI/CD pipeline executed successfully."
# finally delete the testing directory
rm -rf "$repo_dir"
) 200>/home/git/scripts/ci-cd.lock
