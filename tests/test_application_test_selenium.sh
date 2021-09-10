#!/bin/bash
# Dienstplan Auto Installer
scriptPath=`dirname $(realpath $0)`
# read the passwords from the config directory
source $scriptPath/config/selenium_test_user.password

testDirectory=/var/www/html/development/testing/
cd $testDirectory
versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`

# RUN SOME TESTS
