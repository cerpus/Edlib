#
# base container, creates environment
#
FROM node:16-alpine AS base
WORKDIR /var/www/app
RUN apk --no-cache add build-base ca-certificates python3 curl
RUN ln -s -f python3 /usr/bin/python

#
# appbase container, contains app and dependencies
#
FROM base AS appbase
COPY package.json yarn.lock ./
ENV NODE_ENV=production
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
EXPOSE 80
CMD [ "yarn", "start" ]

#
# Dev
#
FROM base AS dev
ENV NODE_EXTRA_CA_CERTS="/usr/local/share/ca-certificates/cacert.pem"
COPY ./docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]
