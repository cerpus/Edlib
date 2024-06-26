#
# base container, creates environment
#
FROM node:16-alpine AS base
WORKDIR /var/www/app
RUN apk --no-cache add --virtual .builds-deps build-base python3 curl
RUN ln -sf python3 /usr/bin/python

#
# appbase container, contains app and dependencies
#
FROM base AS appbase
COPY package.json yarn.lock ./
ENV NODE_ENV=production
RUN yarn install
COPY . .

#
# Test runner container
#
FROM appbase as test
WORKDIR /var/www/app
CMD [ "node", "node_modules/jest/bin/jest.js", "--runInBand", "--colors", "--verbose", "--forceExit", "--config=./jest.config.json" ]

#
# App container for running in production
#
FROM appbase as prod
EXPOSE 80
CMD [ "yarn", "start" ]

#
# Dev container for running dev with mounted app volume
#
FROM base as dev
ENV NODE_EXTRA_CA_CERTS="/usr/local/share/ca-certificates/cacert.pem"
WORKDIR /var/www/app
RUN yarn global add nodemon
EXPOSE 80
CMD update-ca-certificates; yarn; yarn dev
