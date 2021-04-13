#!/bin/bash
# Dienstplan Auto Installer
scriptPath=`dirname $(realpath $0)`

testDirectory=/var/www/html/development/testing/
cd $testDirectory
versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`
cd $testDirectory/dienstplan-test-$versionString


# Commit the results and push
git status
#git -C $testDirectory/dienstplan-test-$versionString/ add .
#git -C $testDirectory/dienstplan-test-$versionString/ commit -m "There have been automated tests."
#git -C $testDirectory/dienstplan-test-$versionString/ push
