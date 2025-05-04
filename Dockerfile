# This Dockerfile is not to be used in production!
# It serves for development and testing purposes only.
# Use the official PHP image with Apache
FROM php:8.0-apache

ARG ENVIRONMENT=production

# Create configuration file for Apache
RUN echo '<Directory /var/www/html>' \
     '\n    Options Indexes FollowSymLinks' \
     '\n    AllowOverride Limit Options' \
     '\n    Require all granted' \
     '\n</Directory>' > /etc/apache2/conf-available/directory-listing.conf

# Activate the configuration
RUN a2enconf directory-listing

# Optional: Make sure, that mod_dir is activated
RUN a2enmod dir

# Install necessary dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libmcrypt-dev \
    libxml2-dev \
    libkrb5-dev \
    libc-client-dev \
    libssl-dev \
    libkrb5-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
    libzip-dev \
    libonig-dev \
    git \
    unzip \
    mariadb-client vim \
    locales \
    && echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Docker PHP extensions
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap pdo pdo_mysql gettext calendar
# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Set the working directory in the container
#WORKDIR /var/www/html
WORKDIR /var/www/html/apotheke/dienstplan-test
# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/apotheke/dienstplan-test
# remove container secrets
RUN rm -f /var/www/html/apotheke/dienstplan-test/.env
RUN git config pull.rebase false
RUN git pull origin testing
# There is another version of selenium-refresh.php, that fetches fresh data from the
#   nextcloud to get the newest files under development.
#   This file here is used in the testing stage within a docker container.
#   The container is built with a fresh database on every startup.
WORKDIR /var/www/html/apotheke
COPY ./tests/selenium-refresh-not.php /var/www/html/apotheke/selenium-refresh.php
COPY ./tests/selenium-copy-not.php /var/www/html/apotheke/selenium-copy.php

# Copy SSL certificate files into the container
RUN mv /var/www/html/apotheke/dienstplan-test/upload/fullchain.pem /etc/ssl/certs/fullchain.pem
RUN mv /var/www/html/apotheke/dienstplan-test/upload/privkey.pem /etc/ssl/private/privkey.pem

# Enable Apache mod_rewrite
RUN a2enmod rewrite
RUN a2enmod ssl

# Create Apache SSL configuration file
RUN echo '<IfModule mod_ssl.c>' \
     '\n<VirtualHost _default_:443>' \
     '\n    ServerAdmin webmaster@localhost' \
     '\n    DocumentRoot /var/www/html' \
     '\n    ErrorLog ${APACHE_LOG_DIR}/error.log' \
     '\n    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
     '\n    SSLEngine on' \
     '\n    SSLCertificateFile /etc/ssl/certs/fullchain.pem' \
     '\n    SSLCertificateKeyFile /etc/ssl/private/privkey.pem' \
     '\n    <FilesMatch "\.(cgi|shtml|phtml|php)$">' \
     '\n        SSLOptions +StdEnvVars' \
     '\n    </FilesMatch>' \
     '\n    <Directory /usr/lib/cgi-bin>' \
     '\n        SSLOptions +StdEnvVars' \
     '\n    </Directory>' \
     '\n</VirtualHost>' \
     '\n</IfModule>' > /etc/apache2/sites-available/default-ssl.conf

# Enable the SSL site
RUN a2ensite default-ssl

# Set the correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 443
EXPOSE 443

# Start Apache in the foreground
CMD ["apache2-foreground"]

