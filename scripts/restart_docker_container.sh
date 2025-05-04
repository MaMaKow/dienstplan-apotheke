#!/bin/bash

# Function to restart or run a container
manage_container() {
    local IMAGE_NAME="$1"
    local RUN_COMMAND="$2"

    # Find the container ID based on the image name
    local CONTAINER_ID=$(docker ps -q --filter "ancestor=$IMAGE_NAME")

    # Check if a container was found and restart or run it
    if [ -z "$CONTAINER_ID" ]; then
        echo "No container found for image: $IMAGE_NAME"
        eval "$RUN_COMMAND"
    else
        echo "Restarting container ID: $CONTAINER_ID"
        docker stop "$CONTAINER_ID"
        docker start "$CONTAINER_ID"
    fi

    # Display the status of the container
    docker ps --filter "id=$CONTAINER_ID"
}

# Prepare the shared downloads directory with appropriate permissions
mkdir -p /tmp/selenium/shared_downloads
chmod o+w /tmp/selenium/ -R
chmod o+w /tmp/selenium/shared_downloads -R

# Start/restart Selenium container
# -p 4444:4444 -> Exposes the Selenium WebDriver port, allowing your tests to connect to the Selenium server.
# -p 7900:7900 -> Exposes the VNC server port, which allows you to view the browser running your tests in real-time using a VNC viewer.
# -p 5900:5900 -> Another VNC server port, used by some configurations or can be an alternative VNC access point.
# --shm-size="2g" -> Increases the shared memory size to 2 GB, which is important for running browsers inside the container to avoid out-of-memory issues.
# -v /tmp/selenium/shared_downloads:/home/seluser/Downloads -> Mounts the shared downloads directory so that files downloaded during tests are accessible on your host machine.
manage_container "dienstplan_selenium" "docker run -d -p 4444:4444 -p 7900:7900 -p 5900:5900 --shm-size='2g' -v /tmp/selenium/shared_downloads:/home/seluser/Downloads dienstplan_selenium"

# Start/restart MailHog container
# A simple SMTP server that captures emails and provides a web interface to view them.
# the SMTP server starts on port 1025
# the HTTP server starts on port 8025
manage_container "mailhog/mailhog" "docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog"
