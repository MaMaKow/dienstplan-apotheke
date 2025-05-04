#!/bin/bash
# CI/CD
# In software development, CI/CD is the combined practice of continuous integration (CI for short)
# and Continuous Delivery (CD for short) or Continuous Deployment (CD for short).

# The task of this script is to test the current commit in the testing branch and, if successful, to move it to the main branch.
# git pull origin testing -> selenium tests (integration tests) -> git merge into master -> git push origin master -> cd production -> git pull origin master (update in production)

# setup directories
repo_dir="/home/git/repositories/dienstplan-apotheke-testing"
hostnameInstallTest="https://docker.martin-mandelkow.de"
export JAVA_HOME=/usr/lib/jvm/jre-11-openjdk/
ENVIRONMENT=testing # set ENVIRONMENT for the Dockerfile to testing

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
git checkout testing
git fetch --all
# Fetch latest changes from remote
git fetch origin

# Test if the merge with master branch will succeed
if ! git merge --no-ff origin/master; then
    echo "Automatic merge not possible. Exiting."
    git merge --abort
    exit 1
fi
echo "Merge with master completed successfully. Proceeding with the script."

# Check for differences between testing and master branches
diff_output=$(git diff origin/master..origin/testing)
if [ -z "$diff_output" ]; then
    echo "No differences between testing and master branches. Exiting."
    exit 0
fi
# Get valid certificate files
# PDR requires a connection via HTTPS. Certificates are therefore required.
# These must be provided in the same path as this script. They are copied from here into the docker container. 
cp $script_dir/fullchain.pem $repo_dir/dienstplan-apotheke/upload/fullchain.pem
cp $script_dir/privkey.pem $repo_dir/dienstplan-apotheke/upload/privkey.pem

# configure environment for docker container
# expose a random port and chosse random user names and passphrases
#random_secure_web_port=$((RANDOM % 65535 + 1024))
random_secure_web_port=8443
random_db_name=$(tr -dc a-z </dev/urandom | head -c 64; echo);
random_user_name=$(tr -dc a-z </dev/urandom | head -c 32; echo);
random_user_passphrase=$(tr -dc A-Za-z0-9 </dev/urandom | head -c 32; echo);
random_root_passphrase=$(tr -dc A-Za-z0-9 </dev/urandom | head -c 32; echo);
echo > .env # Clean up old data from .env file
echo SECURE_WEB_PORT=$random_secure_web_port >> .env
echo MYSQL_ROOT_PASSWORD=$random_root_passphrase >> .env
echo MYSQL_DATABASE=$random_db_name >> .env
echo MYSQL_USER=$random_user_name >>.env
echo MYSQL_PASSWORD=$random_user_passphrase >> .env
urlInstallTest=$hostnameInstallTest:$random_secure_web_port/apotheke

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
administratorEmail=$random_user_name@localhost
administratorEmployeeId=5
databaseUserName=root
databaseHostname=db
databasePassword=$random_root_passphrase
databaseName=$random_db_name
databasePort=3306
EOF
#cat $repo_dir/dienstplan-apotheke/tests/selenium/Configuration.properties
bash $repo_dir/dienstplan-apotheke/scripts/restart_docker_container.sh
#docker-compose build --no-cache
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" build
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" up -d
# Function to check if all containers are running
check_containers() {
  # Get the status of all containers
  STATUS=$(docker-compose ps -q | xargs docker inspect -f '{{ .State.Running }}' 2>/dev/null)

  # Check if any container is not running
  if [[ "$STATUS" == *"false"* ]] || [[ -z "$STATUS" ]]; then
    return 1
  else
    return 0
  fi
}

# Wait for all containers to be running
echo "Waiting for containers to be up."
while ! check_containers; do
    echo -n "."
    sleep 1  # Wait for 1 second before checking again
done
#sleep 30

# run selenium tests
# assuming the selenium tests are written to exit with a non-zero status on failure
cd "$repo_dir"/dienstplan-apotheke/tests/selenium/ || exit 
/usr/bin/mvn test | tee ./mvn.log
echo -e "\a" # Bell sound!
# cleanup the docker container
docker-compose -f "$repo_dir/dienstplan-apotheke/docker-compose.yml" down --volumes

test_outcome=$(cat test-result)
if [ "$test_outcome" == "FAILED" ]; then
    echo "Selenium tests failed."
    exit 1
elif [ "$test_outcome" == "SUCCESS" ]; then
    echo "Selenium tests succeeded."
else
    echo "Unexpected result in test-result file: $test_outcome"
    exit 1
fi

# if tests pass, merge into master and push
# Check the current path
echo "Current path:"
pwd

# Attempt to blank auth ssl (not needed if using PAT, usually used for SSH)
echo "Trying to authenticate using SSH"
ssh -T git@github.com

# Try pushing to GitHub using the branch 'testing' to 'master'
echo "Trying to push using branch 'testing' to 'master'"
git push origin testing:master

# Second attempt to push using a token
echo "Trying to authenticate with token and push"
git push https://$GIT_TOKEN@github.com/MaMaKow/dienstplan-apotheke.git testing:master

echo "CI/CD pipeline executed successfully."
# finally delete the testing directory
#rm -rf "$repo_dir"

