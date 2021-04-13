#!/bin/bash
# Dienstplan Auto Installer
scriptPath=`dirname $(realpath $0)`
# read the passwords from the config directory
# provides $pdrDbUserPassword
source $scriptPath/config/pdrDbUser.password
source $scriptPath/config/selenium_test_user.password

testDirectory=/var/www/html/development/testing/
mkdir -p $testDirectory
cd $testDirectory

rm -r --force $testDirectory/dienstplan-apotheke/
git clone --branch testing https://github.com/MaMaKow/dienstplan-apotheke.git
git -C dienstplan-apotheke/ pull origin development # get the newest commits from the development branch from github.
versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`

cp -r dienstplan-apotheke dienstplan-test-$versionString
