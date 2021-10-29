FROM ubuntu:20.04 AS base

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y && apt-get -y install sudo curl git apache2 libapache2-mod-php php-readline php-opcache php-xml php-sqlite3 php-pgsql php-memcached php-json php-intl php-igbinary php-gd php-xmlrpc php-mbstring php-mysql php-mongodb php-uuid php-geoip php-oauth php-zip php-bcmath npm php-curl unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN bash -c 'echo "Defaults !pam_session" >> /etc/sudoers'

FROM base AS app_base_env

RUN a2enmod rewrite
RUN a2enmod headers

COPY docker/apache-envvars /etc/apache2/envvars
COPY docker/apache-vhost.conf /etc/apache2/sites-enabled/app.conf
COPY docker/run-app.sh /

RUN chmod 0755 /run-app.sh

FROM app_base_env AS app_base

COPY . /app

WORKDIR /app

RUN composer install

RUN rm -f .env

FROM app_base_env AS app_dev

EXPOSE 8080
EXPOSE 8081

RUN set -eux; apt-get update; apt-get install -y php-xdebug

VOLUME /app

COPY docker/php-ini-dev.ini /etc/php/7.4/apache2/conf.d/99-extra-dev.ini

CMD ["bash", "-c", "set -eux; cd /app; composer install; php artisan migrate --force; chown -R www-data:www-data storage; /usr/sbin/apache2ctl -DFOREGROUND"]

FROM app_base AS deploy
CMD [ "php", "artisan", "migrate", "--force" ]

FROM app_base AS init
COPY docker/run-init.sh /run-init.sh
RUN chmod +x /run-init.sh
CMD [ "/run-init.sh" ]

FROM app_base AS phpunit
VOLUME /app/storage

COPY docker/run-phpunit.sh /run-phpunit.sh
RUN chmod +x /run-phpunit.sh
CMD [ "/run-phpunit.sh" ]

FROM app_base AS app_prod
EXPOSE 8080
EXPOSE 8081

VOLUME /app/storage

CMD /run-app.sh
