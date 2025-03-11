FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        git \
        unzip

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN IPE_GD_WITHOUTAVIF=1 install-php-extensions \
        apcu \
        bcmath \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        redis \
        zip

COPY --from=composer/composer:2-bin /composer /usr/local/bin/

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HTACCESS_PROTECT=0 \
    COMPOSER_HOME="/tmp" \
    COMPOSER_MEMORY_LIMIT=-1

COPY docker/php/php.ini $PHP_INI_DIR/conf.d/99-custom.ini

RUN echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/www.conf;

COPY docker/php/startup.sh /usr/local/bin/

WORKDIR /app


FROM base AS composer_deps

COPY composer.json composer.lock ./

RUN composer install \
    --no-autoloader \
    --no-cache \
    --no-dev \
    --no-progress \
    --no-scripts


FROM node:18-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources resources
COPY public public
COPY vite.config.js .

RUN set -eux; \
    npm run build; \
    npm cache clean --force; \
    rm -rf node_modules


FROM composer_deps AS prod

COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY lang lang
COPY public public
COPY resources resources
COPY routes routes
COPY artisan .
COPY --from=composer_deps /app/composer.json /app/composer.lock .
COPY --from=composer_deps /app/vendor vendor
COPY --from=frontend /app/public public

ENV LOG_CHANNEL=stderr \
    APP_DEBUG=false \
    APP_ENV=production

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs; \
    ln -s /app/storage/app/public /app/public/storage; \
    chmod -R o+w \
        bootstrap/cache \
        storage; \
    composer dump-autoload --optimize --classmap-authoritative --apcu; \
    chown -R www-data:www-data .

VOLUME /app/storage

COPY docker/php/fpm-prod.conf "/usr/local/etc/php-fpm.d/zz-fpm-prod.conf"


FROM prod AS test

COPY phpunit.xml .env.testing .
COPY tests tests

ENV APP_ENV=testing \
    APP_DEBUG=true

RUN set -eux; \
    install-php-extensions \
        pcov \
    ; \
    composer install \
        --no-cache \
        --no-progress \
    ;

CMD ["vendor/bin/phpunit"]


FROM base AS dev

RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"; \
    install-php-extensions \
        pcov \
        xdebug \
    ;

COPY docker/php/docker-entrypoint-dev.sh /
COPY docker/php/php-dev.ini "$PHP_INI_DIR/conf.d/zz-php-dev.ini"
COPY docker/php/startup-dev.sh /usr/local/bin/

ENTRYPOINT ["/docker-entrypoint-dev.sh"]

CMD ["php-fpm"]


FROM nginx:1-alpine AS web

ENV PHP_FPM_HOST "localhost:9000"

COPY --from=prod /app/public /app/public
COPY docker/nginx/server_tokens.conf /etc/nginx/conf.d/
COPY docker/nginx/default.conf.template /etc/nginx/templates/
