FROM php:8.1-fpm-alpine AS base

WORKDIR /var/www/doku

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
COPY docker/php/zz-edlib.ini "$PHP_INI_DIR/"
COPY docker/php/docker-entrypoint.sh /usr/bin/

RUN set -eux; \
    apk add --no-cache git zip; \
    install-php-extensions \
        apcu \
        gmp \
        intl \
        opcache \
        pdo_mysql \
        sockets \
        zip \
    ; \
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    ;

COPY --from=composer:2 /usr/bin/composer /usr/bin/
COPY composer.json composer.lock ./

RUN set -eux; \
    composer install \
        --no-autoloader \
        --no-dev \
        --no-progress \
        --no-scripts; \
    composer clear-cache;

COPY . .

RUN set -eux; \
    composer install --no-dev; \
    chown -R www-data:www-data .


FROM base AS test

RUN composer install

COPY docker/php/run-phpunit.sh .

CMD ["./run-phpunit.sh"]


FROM node:16-alpine AS webpack

WORKDIR /var/www/doku

COPY package.json package-lock.json webpack.mix.js ./
COPY resources/ ./resources

RUN set -eux; \
    npm install; \
    npm run production; \
    rm -rf node_modules


FROM base AS prod

COPY docker/php/zz-edlib-prod.ini "$PHP_INI_DIR/"

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    composer install \
        --apcu-autoloader \
        --classmap-authoritative \
        --no-dev \
        --no-progress \
        --prefer-dist; \
    composer clear-cache;

ENV APP_ENV=production \
    APP_DEBUG=false

COPY --from=webpack /var/www/doku/public/* /var/www/doku/public/


FROM caddy:2-alpine AS web

ENV PHP_FPM_HOST "localhost:9000"

COPY --from=base /var/www/doku/public /var/www/doku/public
COPY --from=webpack /var/www/doku/public/* /var/www/doku/public
COPY docker/web/Caddyfile /etc/caddy/

VOLUME /config
VOLUME /data


FROM base AS deploy

CMD ["php", "artisan", "migrate", "--force"]


FROM base AS dev

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; \
    install-php-extensions xdebug;
