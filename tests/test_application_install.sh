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

versionString=`git -C dienstplan-apotheke describe --tags --long --abbrev=40 | tr '.-' '_'`

mysql -u pdrDbUser -p$pdrDbUserPassword -e "DROP DATABASE pdrTest_$versionString;"
mysql -u pdrDbUser -p$pdrDbUserPassword -e "CREATE DATABASE pdrTest_$versionString;"
# GRANT ALL PRIVILEGES ON `pdrTest\_%`.* TO 'pdrDbUser'@'localhost';

# Create all the database tables.
# We do this three times, because there are tables depending on each other with their keys.
# e.g. dienstplan can only be created after the following constraint is fulfilled:
# CONSTRAINT `Dienstplan_ibfk_1` FOREIGN KEY (`VK`) REFERENCES `employees` (`id`)
# TODO: Is there a more clever way to do this?
for sqlFile in `ls -t $testDirectory/dienstplan-test-$versionString/src/sql/*.sql`
do
    mysql -u pdrDbUser -p$pdrDbUserPassword pdrTest_$versionString < ${sqlFile}
done
for sqlFile in `ls -t $testDirectory/dienstplan-test-$versionString/src/sql/*.sql`
do
    mysql -u pdrDbUser -p$pdrDbUserPassword pdrTest_$versionString < ${sqlFile}
done
for sqlFile in `ls -t $testDirectory/dienstplan-test-$versionString/src/sql/*.sql`
do
    mysql -u pdrDbUser -p$pdrDbUserPassword pdrTest_$versionString < ${sqlFile}
done

# Fill data into the PHP config file
echo "<?php"                                                           > $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['database_password'] = '$pdrDbUserPassword';"                  >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['database_name'] = 'pdrTest_$versionString';"                  >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['database_user'] = 'pdrDbUser';"                               >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['application_name'] = 'Dienstplan Test Install';"              >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['LC_TIME'] = 'de_DE.utf8';"                                    >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['mb_internal_encoding'] = 'UTF-8';"                            >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['display_errors'] = 1;"                                        >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['log_errors'] = 1;"                                            >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['error_log'] = '/var/www/html/development/testing/error.log';" >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['error_reporting'] = E_ALL;"                                   >> $testDirectory/dienstplan-test-$versionString/config/config.php
echo "\$config['hide_disapproved'] = false;"                                  >> $testDirectory/dienstplan-test-$versionString/config/config.php

# TODO: We need to setup the test user!
selenium_test_userPasswordHash=`php -r "echo password_hash('$selenium_test_userPassword', PASSWORD_DEFAULT);"`
mysql -u pdrDbUser -p$pdrDbUserPassword pdrTest_$versionString -e 'INSERT INTO `employees` (`id`, `last_name`, `first_name`, `profession`)'" VALUES ('5', 'Mandelkow', 'Martin', 'Apotheker'); "
mysql -u pdrDbUser -p$pdrDbUserPassword pdrTest_$versionString -e 'INSERT INTO `users` (`employee_id`, `user_name`, `email`, `password`, `status`, `receive_emails_on_changed_roster`)'" VALUES ('5', 'selenium_test_user', 'selenium_test_user@martin-mandelkow.de', '$selenium_test_userPasswordHash', 'active', '1');"
