#!/bin/bash
set -e

# "Remember, there is a prepared shared downloads directory"
# /etc/tmpfiles.d/selenium.conf
# d /tmp/selenium                0777 git git -
# d /tmp/selenium/shared_downloads  0777 git git -
#
# "It has to be activated by:"
# systemd-tmpfiles --create /etc/tmpfiles.d/selenium.conf
# Sanity-check: shared downloads directory must exist (created via tmpfiles.d)
if [ ! -d "/tmp/selenium/shared_downloads" ]; then
    echo "ERROR: /tmp/selenium/shared_downloads does not exist."
    echo "Create file /etc/tmpfiles.d/selenium.conf"
    echo "Content:"
    echo "d /tmp/selenium                0777 git git -"
    echo "d /tmp/selenium/shared_downloads  0777 git git -"
    echo "Run as root: systemd-tmpfiles --create /etc/tmpfiles.d/selenium.conf"
    exit 1
fi

# Go to the compose directory and start Firefox
echo "Start Selenium container"
if [ -z "$(docker ps -aq --filter name=^dienstplan_selenium$)" ]; then
    docker run -d \
        -p 4444:4444 -p 7900:7900 -p 5900:5900 \
        -e SE_SESSION_TIMEOUT=900 \
        --shm-size="2g" \
        --name dienstplan_selenium \
        -v /tmp/selenium/shared_downloads:/home/seluser/Downloads \
        dienstplan_selenium
else
    docker restart dienstplan_selenium
fi

echo "Start MailHog (standalone)"
if [ -z "$(docker ps -aq --filter name=^mailhog$)" ]; then
    docker run -d -p 1025:1025 -p 8025:8025 --name mailhog mailhog/mailhog
else
    docker restart mailhog
fi

echo "Show docker status"
docker ps --filter name=dienstplan_selenium
docker ps --filter name=mailhog