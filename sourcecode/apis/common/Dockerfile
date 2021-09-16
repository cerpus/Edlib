FROM php:8.0-fpm-alpine AS base

COPY --from=composer:2 /usr/bin/composer /usr/local/bin
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin
COPY docker/docker-entrypoint.sh /usr/local/bin

RUN set -eux; \
    apk add --no-cache zip; \
    install-php-extensions \
        intl \
        opcache \
        pdo_mysql \
        zip \
    ;

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]


FROM base AS prod

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini";

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr


FROM base AS dev

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini";
