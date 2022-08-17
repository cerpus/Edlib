FROM node:16-alpine as base
WORKDIR /app
COPY . .
RUN yarn

FROM base as build
RUN yarn build --noninteractive

FROM build as prod
EXPOSE 80
CMD yarn start:prod

FROM node:16-alpine as dev
RUN set -eux; \
    apk add --no-cache \
        curl \
        ca-certificates \
    ;
COPY ./docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]
WORKDIR /app
EXPOSE 3000
