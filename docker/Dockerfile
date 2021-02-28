ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-apache

RUN apt-get update && \
  export DEBIAN_FRONTEND=noninteractive && \
  apt-get install --no-install-recommends -yqq git locales-all && \
  rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gettext && \
    docker-php-ext-configure calendar && \
    docker-php-ext-configure pdo_mysql && \
    docker-php-ext-install calendar gettext pdo_mysql

ARG TAG

RUN git clone https://github.com/MaMaKow/dienstplan-apotheke /var/www/html && \
  if [ "$TAG" ]; then cd /var/www/html; git checkout ${TAG}; fi && \
  chown -R www-data:www-data /var/www/html && \
  ln -s /dev/stderr /var/www/html/error.log

# TODO: Delete if in master. Apply the patch
ADD rootfs/fixes.patch /fixes.patch
RUN cd /var/www/html && git apply /fixes.patch

# Allow a reverse proxy
RUN echo "SetEnvIf x-forwarded-proto https HTTPS=on" > /etc/apache2/conf-enabled/reverse_proxy.conf

COPY rootfs/entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
