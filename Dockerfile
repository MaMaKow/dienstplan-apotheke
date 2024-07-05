# This Dockerfile is not to be used in production!
# It serves for development and testing purposes only.
# Use the official PHP image with Apache
FROM php:8.0-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/apotheke/dienstplan-test

# Install any needed PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gettext

#RUN apt-get update && apt-get install -y \
#    libicu-dev \
#    libcurl4-openssl-dev \
#    libssl-dev \
#    libpng-dev \
#    libjpeg-dev \
#    libfreetype6-dev \
#    libbz2-dev \
#    libxslt1-dev \
#    libmcrypt-dev \
#    libzip-dev \
#    zlib1g-dev \
#    && docker-php-ext-install \
#    calendar \
#    ctype \
#    curl \
#    gettext \
#    imap \
#    intl \
#    mbstring \
#    openssl \
#    pdo \
#    pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]

