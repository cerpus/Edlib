FROM php:7.2-fpm-alpine AS php_base
WORKDIR /app
# Install common deps
RUN apk add --no-cache --update --virtual .common-deps bash sudo rclone git
# Install phpize deps
RUN apk add --no-cache --update --virtual .phpize-deps $PHPIZE_DEPS
# Install php extensions available through docker-php-ext-install
RUN apk add icu-dev libxml2-dev libzip-dev gettext-dev
RUN docker-php-ext-install \
        pdo_mysql \
        opcache \
        intl \
        xmlrpc \
        bcmath \
        zip \
        mysqli \
        sockets \
        gettext \
        pcntl
# Install memcached
RUN apk add --no-cache --update --virtual .memcached-deps zlib-dev libmemcached-dev cyrus-sasl-dev;
RUN pecl install memcached;docker-php-ext-enable memcached;
# Install igbinary
RUN pecl install igbinary; docker-php-ext-enable igbinary;
# Install oauth
RUN apk add pcre-dev; pecl install oauth; docker-php-ext-enable oauth;
# Install redis
RUN pecl install redis; docker-php-ext-enable redis;
# copy the composer PHAR from the Composer image into the PHP image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader
COPY . /app
RUN composer dump-autoload --no-scripts --no-dev --optimize

RUN set -eux; \
    mkdir -v -p storage/app/public; \
    mkdir -v -p storage/app/storage; \
    mkdir -v -p storage/framework/cache/data; \
    mkdir -v -p storage/framework/sessions; \
    mkdir -v -p storage/framework/testing; \
    mkdir -v -p storage/framework/views; \
    mkdir -v -p storage/logs; \
    touch storage/logs/laravel.log; \
    php artisan vendor:publish --tag=public --no-ansi; \
    php artisan cerpus:install; \
    php artisan route:clear;
# Copy h5p-editor files from vendor folder to public folder
RUN set -eux; \
    cp -R /app/vendor/h5p/h5p-editor public/h5p-editor-php-library; \
    cp -R /app/vendor/h5p/h5p-core public/h5p-php-library;

COPY docker/php.ini $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/phpfpm-pool-www.conf /usr/local/etc/php-fpm.d/zz-docker.conf


FROM node:11-alpine AS jsbuild
WORKDIR /app
RUN npm config set unsafe-perm true
RUN apk add --no-cache alpine-sdk python make g++ bash
RUN npm i -g node-gyp
COPY package.json npm-shrinkwrap.json ./
RUN npm i
COPY webpack.config.js webpack.mix.js .babelrc ./
COPY --from=php_base /app/vendor ./vendor
COPY --from=php_base /app/resources ./resources
COPY --from=php_base /app/public ./public
RUN ls -l
RUN npm run production
RUN rm -rf node_modules


FROM php_base AS buildresult
COPY --from=php_base /app/ /app/
COPY --from=jsbuild /app/public /app/public
# Set correct file permission for app
RUN chown -R www-data:www-data /app
# Create symlink from app to shared storage
RUN set -eux; \
    ln -s /storage/h5plibs public/h5pstorage; \
    ln -s /storage/storage/app storage/app; \
    ln -s /storage/storage/logs storage/logs; \
    ln -s /storage/storage/framework storage/framework;


FROM buildresult as test
COPY docker/run-phpunit.sh /run-phpunit.sh
RUN chmod +x /run-phpunit.sh
CMD /run-phpunit.sh


FROM buildresult AS cron
COPY docker/laravel.schedule /laravel.schedule
RUN set -eux; crontab /laravel.schedule; rm /laravel.schedule; crontab -l
CMD [ "bash", "-c", "set -eux; crond -f" ]


FROM buildresult AS deploy
CMD [ "php", "/app/artisan", "migrate", "--force" ]


FROM buildresult AS init
COPY docker/run-init.sh /init.sh
RUN chmod +x /init.sh
CMD /init.sh


FROM buildresult AS horizonqueue
CMD [ "php", "/app/artisan", "horizon" ]


FROM buildresult AS phpfpm
EXPOSE 31004
CMD [ "php-fpm", "-R", "-F", "-O" ]


FROM buildresult AS phpfpm-dev
EXPOSE 31004
COPY docker/php/docker-entrypoint-dev.sh /docker-entrypoint-dev.sh
ENTRYPOINT ["/docker-entrypoint-dev.sh"]
CMD [ "php-fpm", "-R", "-F", "-O" ]


FROM nginx:1.19-alpine AS app
ENV PHP_FPM_HOST "localhost:31004"
COPY --from=buildresult /app/public /app/public
EXPOSE 8081
RUN apk add --no-cache bash
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/contentAuthor.conf.template /etc/nginx/templates/contentAuthor.conf.template
CMD ["nginx"]
