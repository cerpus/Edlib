FROM php:8.1-apache

RUN set -eux; \
    export DEBIAN_FRONTEND=noninteractive; \
    apt-get update; \
    apt-get install -y git; \
    rm -rf /var/lib/apt/lists/*;

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions \
        gd \
        intl \
        mysqli \
        opcache \
        zip \
    ; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    a2enmod remoteip rewrite;

VOLUME /var/www/html
RUN git clone -b "MOODLE_402_STABLE" https://github.com/moodle/moodle.git /var/www/html

COPY httpd.conf /etc/apache2/conf-enabled/

COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apachectl", "-D", "FOREGROUND"]
