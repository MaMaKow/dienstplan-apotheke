#!/usr/bin/env bash

echo "Checking out the master branch with git";
git checkout master;

echo "Pulling the current state from origin";
git pull;

echo "Performing maintenance tasks, including database updates";
php ./src/php/background_maintenance.php;