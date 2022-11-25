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
sourcePath=/var/www/html/nextcloud/data/Martin/files/Dokumente/Freizeit/Programmierung/git/dienstplan-apotheke/
#sourcePath=/var/www/html/apotheke/dienstplan-test/
destinationPath=/var/www/html/development/testing/dienstplan-apotheke/

echo Source:
echo $sourcePath
echo Destination:
echo $destinationPath
rsync -av --exclude='config/config.php' --exclude='error.log'  $sourcePath $destinationPath

versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`

cp -r dienstplan-apotheke dienstplan-test-$versionString
sudo chown -R apache:apache dienstplan-test-$versionString
