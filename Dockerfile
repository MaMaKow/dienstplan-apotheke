# This Dockerfile is not to be used in production!
# It serves for development and testing purposes only.
# Use the official PHP image with Apache
FROM php:8.0-apache
# Install dependencies for intl extension
RUN apt-get update && apt-get install -y \
    libicu-dev git\
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl
# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Set the working directory in the container
#WORKDIR /var/www/html
WORKDIR /var/www/html/apotheke/dienstplan-test
# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/apotheke/dienstplan-test
# remove container secrets
RUN rm -f /var/www/html/apotheke/dienstplan-test/.env
RUN git pull origin testing
# There is another version of selenium-refresh.php, that fetches fresh data from the
#   nextcloud to get the newest files under development.
#   This file here is used in the testing stage within a docker container.
#   The container is built with a fresh database on every startup.
WORKDIR /var/www/html/apotheke
COPY ./tests/selenium-refresh-not.php /var/www/html/apotheke/selenium-refresh.php

# Install any needed PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gettext calendar intl imap


# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]

