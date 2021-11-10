#
# base container, creates environment
#
FROM node:16 AS base
WORKDIR /var/www/app

#
# appbase container, contains app and dependencies
#
FROM base AS appbase
COPY package.json yarn.lock ./
ENV NODE_ENV=production
RUN yarn install
COPY . .

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
# Dev container for running dev with mounted app volume
#
FROM base as dev
RUN yarn global add nodemon
CMD update-ca-certificates; yarn; yarn migrate; yarn dev
