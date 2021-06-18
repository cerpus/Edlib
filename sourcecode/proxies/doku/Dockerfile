#
# base container, creates environment
#
FROM node:16 AS base
WORKDIR /var/www/app

#
# appbase container, contains app and dependencies
#
FROM node:16 AS appbase
COPY . .
RUN yarn install

#
# Test runner container
#
FROM appbase as test
WORKDIR /var/www/app
ENV JEST_REPORT_FILE /result/jest-report.json
CMD [ "bash", "-c", "yarn test; exitcode=$?; chown -R ${RUN_AS_UID:-0} /result/; exit $exitcode" ]

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
WORKDIR /var/www/app
RUN yarn global add nodemon
EXPOSE 80
CMD update-ca-certificates; yarn; yarn dev
