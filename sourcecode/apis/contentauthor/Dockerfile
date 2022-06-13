FROM php:8.1-fpm-alpine AS php_base
ENV IPE_GD_WITHOUTAVIF=1

WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/bin/
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-custom.ini
RUN echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/www.conf
RUN apk add --no-cache bash sudo rclone git unzip
RUN install-php-extensions \
        bcmath \
        gettext \
        igbinary \
        intl \
        memcached \
        mysqli \
        oauth \
        opcache \
        pcntl \
        pdo_mysql \
        redis \
        sockets \
        xmlrpc \
        zip
COPY composer.json composer.lock ./
RUN composer install \
    --no-autoloader \
    --no-dev \
    --no-scripts
COPY . .
RUN mkdir -v -p \
            storage/app/public \
            storage/app/storage \
            storage/framework/cache/data \
            storage/framework/sessions \
            storage/framework/testing \
            storage/framework/views \
            storage/logs
RUN composer install \
        --no-dev
RUN cp -R /app/vendor/h5p/h5p-editor public/h5p-editor-php-library
RUN cp -R /app/vendor/h5p/h5p-core public/h5p-php-library
RUN chown -R www-data:www-data .


FROM php_base as test
COPY docker/run-phpunit.sh /run-phpunit.sh
RUN composer install
CMD /run-phpunit.sh

FROM node:16-alpine AS jsbuild
WORKDIR /app
RUN npm i -g npm node-gyp
COPY package.json package-lock.json ./
RUN npm i --legacy-peer-deps
COPY webpack.mix.js ./
COPY --from=php_base /app/vendor/ckeditor/ckeditor ./vendor/ckeditor/ckeditor
COPY --from=php_base /app/vendor/h5p ./vendor/h5p
COPY --from=php_base /app/resources ./resources
COPY --from=php_base /app/public ./public
RUN npm run production
RUN rm -rf node_modules


FROM php_base AS buildresult
COPY --from=jsbuild /app/public /app/public
RUN chown -R www-data:www-data /app/public


FROM buildresult AS deploy
CMD [ "php", "/app/artisan", "migrate", "--force" ]


FROM buildresult AS phpfpm
CMD [ "php-fpm", "-R", "-F", "-O" ]


FROM buildresult AS phpfpm-dev
RUN install-php-extensions \
        xdebug
COPY docker/php/docker-entrypoint-dev.sh /docker-entrypoint-dev.sh
ENTRYPOINT ["/docker-entrypoint-dev.sh"]
CMD [ "php-fpm", "-R", "-F", "-O" ]


FROM nginx:1.19-alpine AS app
ENV PHP_FPM_HOST "localhost:9000"
COPY --from=buildresult /app/public /app/public
RUN apk add --no-cache bash
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/contentAuthor.conf.template /etc/nginx/templates/contentAuthor.conf.template
CMD ["nginx"]
