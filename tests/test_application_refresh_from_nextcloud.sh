#!/bin/bash
# Dienstplan Auto Installer
scriptPath=`dirname $(realpath $0)`
# read the passwords from the config directory
# provides $pdrDbUserPassword

testDirectory=/var/www/html/development/testing/
cd $testDirectory

sourcePath=/var/www/nextcloud-data/Martin/files/Dokumente/Freizeit/Programmierung/git/dienstplan-apotheke/
destinationPath=/var/www/html/development/testing/dienstplan-test/

echo Source:
echo $sourcePath
echo Destination:
echo $destinationPath
rsync -av --exclude='config/config.php' --exclude='error.log'  $sourcePath $destinationPath

sudo chown -R apache:apache dienstplan-test
