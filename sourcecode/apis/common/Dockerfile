FROM php:8.0.14-fpm-alpine AS base

WORKDIR /var/www/edlibcommon

COPY --from=composer:2 /usr/bin/composer /usr/local/bin
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin
COPY docker/php/docker-entrypoint.sh /usr/local/bin
COPY docker/php/zz-edlib.ini "$PHP_INI_DIR/"
RUN echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/www.conf
COPY . .
COPY .env.example .

RUN set -eux; \
    apk add --no-cache zip bash; \
    install-php-extensions \
        gmp \
        intl \
        opcache \
        pdo_mysql \
        sockets \
        zip \
        redis \
    ; \
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    ; \
    chmod -R o+w \
        bootstrap/cache \
        storage \
    ;

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME="/tmp" \
    COMPOSER_HTACCESS_PROTECT=0 \
    COMPOSER_MEMORY_LIMIT=-1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]


FROM base AS prod

COPY docker/php/zz-edlib-prod.ini "$PHP_INI_DIR/"

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    composer install \
        --apcu-autoloader \
        --classmap-authoritative \
        --no-dev \
        --no-progress \
        --prefer-dist \
    ; \
    composer clear-cache;

ENV APP_ENV=production \
    APP_DEBUG=false


FROM nginx:1.19-alpine AS web
ENV PHP_FPM_HOST "localhost:9000"
COPY --from=base /var/www/edlibcommon/public /var/www/edlibcommon/public
COPY docker/nginx/common.conf.template /etc/nginx/templates/default.conf.template

FROM prod AS deploy
CMD [ "php", "artisan", "migrate", "--force" ]

FROM base AS dev

COPY docker/php/docker-entrypoint-dev.sh /usr/local/bin

ENTRYPOINT ["docker-entrypoint-dev.sh"]
CMD ["php-fpm"]

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; \
    install-php-extensions \
        xdebug \
    ; \
    composer install \
        --apcu-autoloader \
        --no-progress \
        --prefer-dist \
    ; \
    composer clear-cache;
