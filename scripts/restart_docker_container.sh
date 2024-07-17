#!/bin/bash

# Define the image name
IMAGE_NAME="dienstplan_selenium"

# Find the container ID based on the image name
CONTAINER_ID=$(docker ps -q --filter "ancestor=$IMAGE_NAME")

mkdir -p /tmp/selenium/shared_downloads
chmod o+w /tmp/selenium/ -R
chmod o+w /tmp/selenium/shared_downloads -R

# Check if a container was found
if [ -z "$CONTAINER_ID" ]; then
    echo "No container found for image: $IMAGE_NAME"
    docker run -d -p 4444:4444 -p 7900:7900 -p 5900:5900 --shm-size="2g" -v /tmp/selenium/shared_downloads:/home/seluser/Downloads dienstplan_selenium
else
    # Stop the container
    docker stop "$CONTAINER_ID"
    # Start the container
    docker start "$CONTAINER_ID"
fi

# Display the status
docker ps --filter "id=$CONTAINER_ID"

