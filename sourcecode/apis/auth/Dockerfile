#
# base container, creates environment
#
FROM node:16-alpine AS base
WORKDIR /var/www/app
RUN apk --no-cache add build-base python3
RUN ln -s /usr/bin/python3 /usr/bin/python

#
# appbase container, contains app and dependencies
#
FROM base AS appbase
COPY package.json yarn.lock ./
RUN yarn install
COPY . .

#
# Test container
#
FROM appbase as test
ENV NODE_ENV=test
CMD [ "node", "node_modules/jest/bin/jest.js", "--runInBand", "--colors", "--verbose", "--forceExit", "--config=./jest.config.json" ]

#
# Run migrations
#
FROM appbase as migrations
CMD [ "yarn", "migrate" ]

#
# App container for running in production
#
FROM appbase as prod
ENV NODE_ENV=production

#
# Development
#
FROM appbase AS dev
RUN set -eux; \
    yarn global add wait-on; \
    apk add --no-cache \
        ca-certificates \
        curl \
    ;
COPY docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint
EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
