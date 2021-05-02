#!/bin/bash
# Dienstplan Auto Installer
scriptPath=`dirname $(realpath $0)`
# read the passwords from the config directory
# provides $pdrDbUserPassword
source $scriptPath/config/pdrDbUser.password

testDirectory=/var/www/html/development/testing/
cd $testDirectory

versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`

# CLEAN UP
mysql -u pdrDbUser -p$pdrDbUserPassword -e "DROP DATABASE pdrTest_$versionString;"
mysql -u pdrDbUser -p$pdrDbUserPassword -e "DROP DATABASE pdrTest;"
rm -rf $testDirectory/dienstplan-test-$versionString
rm -rf $testDirectory/dienstplan-test*
